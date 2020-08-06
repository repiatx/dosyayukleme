<?php

namespace ifyazilim\FileUploader;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

use Psr\Http\Message\UploadedFileInterface;

class UploaderService implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * EN: Errors codes that could happen during upload.
     * TR: Dosya yükleme sırasında oluşabilecek hata kodları.
     * @var array
     */
    protected $messages = [
        1 => 'File size exceeds "upload_max_filesize" limit specified in "php.ini".',
        2 => 'File size exceeds MAX_FILE_SIZE specified in HTML Form.',
        3 => 'Only the some part of file has been uploaded.',
        4 => 'Nothing uploaded.',
        6 => 'No temp folder specified.',
        7 => 'Uploaded file could not saved on disk.',
        8 => 'One of the PHP extension has stop the file uploading process.'
    ];

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var FileInfo[]
     */
    private $fileInfos = [];

    /**
     * @param UploadedFileInterface[] $files
     * @param bool $throw
     *
     * @return static
     * @throws \RuntimeException
     */
    public static function parse($files, $throw = true)
    {
        $uploader = new static();

        // File uploading enabled in php.ini check.
        if (ini_get('file_uploads') == false) {

            $uploader->errors[] = 'You have to enable php\'s upload feature in PHP.ini.';

        } else {

            foreach ($files as $key1 => $file) {

                if (is_array($file)) {

                    // Multiple file upload request.

                    /**
                     * @var string $key2
                     * @var UploadedFileInterface $item
                     */
                    foreach ($file as $key2 => $item) {

                        // Check if there is a file.
                        if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                            continue;
                        }

                        $fileEntry = $uploader->uploadFile($file);

                        if ( ! $item) {
                            continue;
                        }
                        // Keep the file info.
                        $uploader->fileInfos[$key2] = $fileEntry;


                    }

                } else {

                    // One file upload request.

                    // Check if there is a file.
                    if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $fileEntry = $uploader->uploadFile($file);

                    // Keep the file info.
                    $uploader->fileInfos[$key1] = $fileEntry;


                }

            }

        }

        // If there is any error.
        if ($throw) {
            if ( ! empty($uploader->errors)) {
                throw new \RuntimeException($uploader->errors[0], 400);
            }
        }

        return $uploader;
    }

    private function uploadFile($file)
    {


        // Check if there is any problem with uploaded file.
        if ($file->getError() !== UPLOAD_ERR_OK) {

            // Save error.
            $this->errors[] = sprintf('%s: %s', $file->getClientFilename(), $this->messages[$file->getError()]);

            // Go on.
            return null;

        }

        $fileInfo = new FileInfo($file->file, $file->getClientFilename());

        // Check if uploaded file is empty.
        if ($fileInfo->getSize() === 0) {

            // Save error.
            $this->errors[] = sprintf('%s: is empty..', $fileInfo->getName());

            // Go on.
            return null;

        }

        // Check if extension is empty.
        if (empty($fileInfo->getExtension())) {

            // Save error.
            $this->errors[] = sprintf('%s: extension is empty.', $fileInfo->getName());

            // Go on.
            return null;

        }

        return $fileInfo;

    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->fileInfos);
    }

    public function offsetExists($offset)
    {
        return isset($this->fileInfos[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->fileInfos[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->fileInfos[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->fileInfos[$offset]);
    }

    public function count()
    {
        return count($this->fileInfos);
    }

    /**
     * @return FileInfo
     *
     * @throws \RuntimeException
     */
    public function next()
    {
        foreach ($this->fileInfos as $fileInfo) {
            return $fileInfo;
        }

        throw new \RuntimeException('File has not uploaded.');
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->fileInfos);
    }
}
