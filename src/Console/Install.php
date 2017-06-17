<?php namespace STS\Serverless\Console;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'aws-lambda:install';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads the Lambda PHP executable/libraries and installs them in the resources.';

    public function handle()
    {
        $tmpDir = $this->downloadPhp();
        $this->stageFiles($tmpDir);
        $this->reportVersions();
    }

    protected function reportVersions(): void
    {
        $versionText = str_replace('\n', '', file_get_contents(base_path('resources/lib/versions.json')));
        $versionText = str_replace(',}', '}', $versionText);
        $versions = collect(json_decode($versionText, true));
        $this->info('PHP Libraries for Lambda installed to the resources folder');
        $versions->each(
            function ($version, $name) {
                $this->info(sprintf("\t%s: %s", $name, $version));
            }
        );
    }

    /**
     * @param $tmpDir
     */
    protected function stageFiles($tmpDir): void
    {
        @mkdir(base_path('resources/bin'), 0777, true);
        @mkdir(base_path('resources/lib'), 0777, true);
        copy(sprintf('%s/bin/php-cgi', $tmpDir), base_path('resources/bin/php-cgi'));
        copyFolder(sprintf('%s/lib', $tmpDir), base_path('resources/lib'));
    }

    /**
     * @return \SplFileInfo
     */
    protected function downloadPhp(): \SplFileInfo
    {
        $tmpDir = \tempDir('serverless', true);
        $zipfile = sprintf('%s/php.tar.gz', $tmpDir);

        file_put_contents($zipfile,
            fopen(config('serverless.install.php_url'), 'r'));
        (new \PharData($zipfile))->decompress()->extractTo($tmpDir);

        return $tmpDir;
    }
}
