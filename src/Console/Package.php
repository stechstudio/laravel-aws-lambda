<?php namespace STS\Serverless\Console;

use Carbon\Carbon;
use Composer\Composer;
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

    public function handle(): int
    {
        $fileList = $this->generateFileList();

        $zipFile = new Archive();
        $zipFile->addCollection($fileList, strlen(base_path()));

        $tmpDir = \tempDir('serverlessVendor', true);
        copy(base_path('composer.json'), sprintf('%s/composer.json', $tmpDir));
        $process = new Process(sprintf('%s install --no-dev', 'composer'));
        $process->setWorkingDirectory($tmpDir);
        $process->run();

        $vendorFileList = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tmpDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $vendorFileList = collect(iterator_to_array($vendorFileList));
        $zipFile->addCollection($vendorFileList, strlen($tmpDir));

        $archiveName = sprintf(
            '%s_%s_%s.zip',
            strtoupper(env('APP_NAME', 'default')),
            env('APP_VERSION', '0.0.1'),
            Carbon::now(env('APP_TIMEZONE', 'UTC'))->format('Y-m-d-H-i-s-u')
        );

        if (! is_dir(base_path('resources/dist'))) {
            mkdir(base_path('resources/dist'));
        }

        $zipFile->saveAndClose(base_path('resources/dist/'.$archiveName));
        $this->info('Created Distribution Package:');
        $this->warn("\tresources/dist/".$archiveName);
        return (0);
    }

    /**
     * @return array
     */
    protected function generateIgnorePatterns(): array
    {
        $ignoreList = [];

        foreach (config('serverless.packaging.ignore.directories') as $pattern) {
            $ignoreList[] = base_path($pattern);
        }

        return array_merge($ignoreList, config('serverless.packaging.ignore.files'));
    }


    /**
     * @return Collection
     */
    protected function generateFileList() : Collection
    {
        $fileList = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path(), \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $contents = collect(iterator_to_array($fileList));

        $contents = $contents->reject(
            function ($fileInfo, $path) {
                foreach ($this->generateIgnorePatterns() as $pattern) {
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
        );

        return $contents;
    }
}
