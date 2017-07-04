<?php namespace STS\Serverless\Console;

use Carbon\Carbon;
use Composer\Composer;
use GisoStallenberg\FilePermissionCalculator\FilePermissionCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use STS\Serverless\Package\Archive;
use Symfony\Component\Process\Process;

class Package extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'aws-lambda:package';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package (zip) the application in preparation for deployment.';

    /**
     * Command Entry Point
     * @return int  The exit code for the command
     */
    public function handle(): int
    {
        $this->info('Creating Archive');
        $archiveName = $this->generateArchiveName();
        $package = new Archive(base_path($archiveName));

        $this->info("\tGenerating Project Files List");
        $projectFileList = $this->getFileCollection(base_path());
        $this->info("\tGenerating Vendor Files List");
        $vendorFileList = $this->collectComposerLibraries();

        $this->info("\tAdding Files to Archive");
        $package->addCollection($projectFileList)->addCollection($vendorFileList)->close();

        $this->info("\tCreated Distribution Package:");
        $this->warn("\tresources/dist/".$archiveName);
        return (0);
    }

    /**
     * Works from a base directory and add all files that are not blacklisted.
     * @return Collection
     */
    protected function getFileCollection(string $basePath) : Collection
    {
        $fileList = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        return collect(iterator_to_array($fileList))->reject(
                function (\SplFileInfo $fileInfo, string $path) {
                    return $this->ignore($fileInfo, $path);
                }
                )->mapWithKeys(
                function (\SplFileInfo $fileInfo, string $path) use ($basePath) {
                    return $this->transform($fileInfo, $path, $basePath);
                }
                );
    }

    /**
     * Transforms the iterator list into something usable for Archiving
     * @param \SplFileInfo $fileInfo
     * @param string $path
     *
     * @return array
     */
    protected function transform(\SplFileInfo $fileInfo, string $path, string $basePath): array
    {
        $key = ltrim(substr($path, strlen($basePath)), '/');

        /** The $key will be path inside the archive from the archive root. */
        return [ $key =>
                     collect([
                         'path' => $fileInfo->getRealPath(),
                         'permissions' => $this->getPermissions($fileInfo, $key)
                     ])
        ];
    }

    /**
     * Determins whether to ignore the file or path
     *
     * @param \SplFileInfo $fileInfo
     * @param string $path
     *
     * @return bool
     */
    protected function ignore(\SplFileInfo $fileInfo, string $path): bool
    {
        foreach (config('serverless.packaging.ignore') as $pattern) {
            if (strpos($fileInfo->getPathInfo(), $pattern) !== false ||
                $fileInfo->getBasename() === basename($pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @param $key
     *
     * @return int
     */
    protected function getPermissions(\SplFileInfo $fileInfo, $key): int
    {
        $perms = $fileInfo->isDir() ?
            /** Directories get read/execute */
            FilePermissionCalculator::fromStringRepresentation('-r-xr-xr-x')->getDecimal() :
            /** Every file defaults to read only (you can't write to the lambda package dir structure) */
            FilePermissionCalculator::fromStringRepresentation('-r--r--r--')->getDecimal();

        /** If it is a configured Executable though, let us make it 555 as well.  */
        if (in_array($key, config('serverless.packaging.executables'))) {
            $perms = FilePermissionCalculator::fromStringRepresentation('-r-xr-xr-x')->getDecimal();
        }

        return $perms;
    }


    /**
     * We create a temporary directory to deploy composer vendor libraries too w/out and development libraries
     * We will deploy that.
     * @return array
     */
    protected function collectComposerLibraries(): Collection
    {
        $tmpDir = \tempDir('serverlessVendor', true);
        copy(base_path('composer.json'), sprintf('%s/composer.json', $tmpDir));

        $process = new Process(sprintf('%s install --no-dev', 'composer'));
        $process->setWorkingDirectory($tmpDir);
        $process->run();

        return $this->getFileCollection($tmpDir);
    }

    /**
     * Standardize the generation of the archive name.
     * @return string
     */
    protected function generateArchiveName(): string
    {
        $archiveName = sprintf(
            'resources/dist/%s_%s_%s.zip',
            strtoupper(env('APP_NAME', 'default')),
            env('APP_VERSION', '0.0.1'),
            Carbon::now(env('APP_TIMEZONE', 'UTC'))->format('Y-m-d-H-i-s-u')
        );

        return $archiveName;
    }
}
