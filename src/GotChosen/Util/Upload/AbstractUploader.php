<?php

namespace GotChosen\Util\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\Filesystem;

abstract class AbstractUploader
{
    protected $allowedMimeTypes = [];
    protected $filesystem;
    
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    
    public function upload(UploadedFile $file)
    {
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(sprintf('Files of type %s are not allowed.', $file->getMimeType()));
        }
        
        $filename = sprintf('%s/%s/%s/%s.%s', date('Y'), date('m'), date('d'), uniqid(), $file->getClientOriginalExtension());
        
        $adapter = $this->filesystem->getAdapter();
        $adapter->setMetadata($filename, array('contentType' => $file->getMimeType()));
        $adapter->write($filename, file_get_contents($file->getPathname()));
        
        return $adapter->getUrl($filename);
    }

    public static function extractKey($url, $bucket)
    {
        $path = parse_url($url, PHP_URL_PATH);
        return substr($path, 2 + strlen($bucket)); // just in case "/$bucket/" appears elsewhere
    }
}
