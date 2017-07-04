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
        $archiveName = $this->generateArchiveName();
        $this->info('Creating Archive');
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

        $contents = collect(iterator_to_array($fileList));

        $contents = $contents->reject(
            /** Reject anything in our ignore list */
            function ($fileInfo, $path) {
                foreach (config('serverless.packaging.ignore') as $pattern) {
                    // Try directory path matching first
                    $result = strpos($fileInfo->getPathInfo(), $pattern);
                    if ($result !== false) {
                        return true;
                    }
                    // Then Try filename matching
                    if ($fileInfo->getBasename() === basename($pattern)) {
                        return true;
                    }
                }
                return false;
            }
        )->mapWithKeys(
            function (\SplFileInfo $fileInfo, $path) use ($basePath) {
                $key = ltrim(substr($path, strlen($basePath)), '/');

                $perms = $fileInfo->isDir() ?
                    FilePermissionCalculator::fromStringRepresentation('-rwxrwxrwx')->getDecimal() :
                    FilePermissionCalculator::fromStringRepresentation('-r--r--r--')->getDecimal();

                if (in_array($key, config('serverless.packaging.executables'))) {
                    $perms = FilePermissionCalculator::fromStringRepresentation('-rwxrwxrwx')->getDecimal();
                }
                return [ $key =>
                    collect([
                        'path' => $fileInfo->getRealPath(),
                        'permissions' => $perms
                    ])
                ];
            }
        );


        return $contents;
    }

    /**
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

    protected function prepareDistDir(): void
    {
        if (! is_dir(base_path('resources/dist'))) {
            mkdir(base_path('resources/dist'));
        }
    }
}
