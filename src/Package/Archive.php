<?php namespace STS\Serverless\Package;

use Illuminate\Support\Collection;
use STS\Serverless\Exceptions\PackageException;
use ZipArchive;

class Archive
{
    /** @var  ZipArchive */
    protected $zipArchive;
    /**
     * @var string
     */
    private $path;

    /**
     * Number of Items added to the Zip
     * @var int
     */
    private $items = 0;

    public function __construct(string $path)
    {
        $this->zipArchive = new ZipArchive();
        $this->path = $path;
        $this->init();
    }

    /**
     * Initialize the Archive. Overwrite and create whatever was there.
     */
    public function init()
    {
        $res = $this->zipArchive->open($this->path, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            throw new PackageException($this->message($res), $res);
        }
    }

    /**
     * Adds all the entries in the collection to the Archive.
     * @param Collection $collection
     */
    public function addCollection(Collection $collection)
    {
        $collection->each(
            function ($data, $entryName) {
                if (is_file($data->get('path'))) {
                    $this->addFile($data->get('path'), $entryName);
                } else {
                    $this->addEmptyDir($entryName);
                }
                $this->setPermissions($entryName, $data->get('permissions'));
            }
        );
        $this->close();
        $this->open();
        return $this;
    }

    /**
     * Set the permissions on the item.
     * @param int $permissions
     */
    public function setPermissions(string $entryName, int $permissions)
    {
        $permissions = ($permissions & 0xffff) << 16;
        $this->zipArchive->setExternalAttributesName($entryName, \ZipArchive::OPSYS_UNIX, $permissions);
        return $this;
    }


    /**
     * Add a directory to the archive
     * @param $entryName
     *
     * @return $this
     */
    public function addEmptyDir($entryName): Archive
    {
        $res = $this->zipArchive->addEmptyDir($entryName);
        if ($res !== true) {
            throw new PackageException($this->message($res), $res);
        }
        return $this;
    }

    /**
     * Add a file to the archive
     * @param $path
     * @param $entryName
     *
     * @return $this
     */
    public function addFile($path, $entryName): Archive
    {
        $res = $this->zipArchive->addFile($path, $entryName);
        if ($res !== true) {
            throw new PackageException($this->message($res), $res);
        }
        return $this;
    }

    /**
     * Opens the archive.
     * @return $this
     */
    public function open(): Archive
    {
        $res = $this->zipArchive->open($this->path);
        if ($res !== true) {
            throw new PackageException($this->message($res), $res);
        }
        return $this;
    }

    /**
     * Close the archive and release files.
     * @return $this
     */
    public function close(): Archive
    {
        $res = $this->zipArchive->close();
        if ($res !== true) {
            throw new PackageException($this->message($res), $res);
        }
        return $this;
    }

    /**
     * Convert ZipArchive Codes to Human Readable Messages
     * @param $code
     * @return string
     */
    protected function message($code): string
    {
        switch ($code) {
            case 0:
                return 'No error';

            case 1:
                return 'Multi-disk zip archives not supported';

            case 2:
                return 'Renaming temporary file failed';

            case 3:
                return 'Closing zip archive failed';

            case 4:
                return 'Seek error';

            case 5:
                return 'Read error';

            case 6:
                return 'Write error';

            case 7:
                return 'CRC error';

            case 8:
                return 'Containing zip archive was closed';

            case 9:
                return 'No such file';

            case 10:
                return 'File already exists';

            case 11:
                return 'Can\'t open file';

            case 12:
                return 'Failure to create temporary file';

            case 13:
                return 'Zlib error';

            case 14:
                return 'Malloc failure';

            case 15:
                return 'Entry has been changed';

            case 16:
                return 'Compression method not supported';

            case 17:
                return 'Premature EOF';

            case 18:
                return 'Invalid argument';

            case 19:
                return 'Not a zip archive';

            case 20:
                return 'Internal error';

            case 21:
                return 'Zip archive inconsistent';

            case 22:
                return 'Can\'t remove file';

            case 23:
                return 'Entry has been deleted';

            default:
                return 'An unknown error has occurred('.intval($code).')';
        }
    }
}
