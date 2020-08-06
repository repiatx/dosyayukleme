<?php

namespace ifyazilim\FileUploader;

use SplFileInfo;
use Pekkis\MimeTypes\MimeTypes;

class FileInfo extends SplFileInfo
{
    /**
     * EN: Returns the Filename with extension.
     * TR: Dosyanın tam adı
     *
     * @example example.jpg
     *
     * @var string
     */
    protected $name;

    /**
     * EN: Returns the file extension.
     * TR: Dosya uzantısı
     *
     * @example gif
     *
     * @var string
     */
    protected $extension;

    /**
     * EN: Return the mime type of file.
     * TR: Dosya mime type bilgisi.
     *
     * @example image/jpeg
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
     *
     * @var string
     */
    protected $mimeType;

    /**
     * @param string $file EN: File save path.|| TR: dosyanın kaydedileceği yol
     * @param string $name EN: Filename ||TR: dosyanın adı
     */
    public function __construct($file, $name = null)
    {
        $mt = new MimeTypes();

        // assign the informations.
        $mimeType = $mt->resolveMimeType($file);
        $extension = $mt->mimeTypeToExtension($mimeType);

        $this->mimeType = $mimeType;
        $this->extension = $extension;

        parent::__construct($file);

        $this->name = is_null($name) ? $this->getFilename() : $name;
    }

    /**
     * EN: Returns the filename without extension.
     * TR: Uzantı olmadan dosyanın adını verir.
     *
     * @example example
     *
     * @param null $suffix
     * @return string
     */
    public function getBasename($suffix = null)
    {
        return parent::getBasename(is_null($suffix) ? ('.' . $this->getExtension()) : $suffix);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * EN: Check if file is image or not.
     * TR: Dosyanın resim mi değil mi karar verir.
     *
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->mimeType, [
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/x-png'
        ]);
    }

    public static function getMimeTypeByFile($file)
    {
        return (new MimeTypes())->resolveMimeType($file);
    }

    public static function getExtByFile($file)
    {
        $mt = new MimeTypes();

        // Test the informations.
        $mimeType = $mt->resolveMimeType($file);

        return $mt->mimeTypeToExtension($mimeType);
    }
}
