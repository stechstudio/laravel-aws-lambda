<?php namespace STS\Serverless\Package;

use Illuminate\Support\Collection;
use PhpZip\ZipFile;
use PhpZip\Model\CentralDirectory;

class Archive extends ZipFile
{
    public function addCollection(Collection $fileList, int $prefixLength = 0): Archive
    {
        $fileList->each(
            function (\SplFileInfo $fileInfo, $path) use ($prefixLength) {
                if ($fileInfo->isDir()) {
                    return;
                }
                $entryName = substr($path, $prefixLength);
                $this->addFile($path, $entryName);
                $mode = sprintf('%o', 33279);
                $externalAttributes = (octdec($mode) & 0xffff) << 16;
                $this->centralDirectory
                    ->getModifiedEntry($entryName)
                    ->setExternalAttributes($externalAttributes);
            }
        );
        return $this;
    }

    public function saveAndClose(string $filename): void
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->saveAsFile($filename);
        $this->close();
    }
}
