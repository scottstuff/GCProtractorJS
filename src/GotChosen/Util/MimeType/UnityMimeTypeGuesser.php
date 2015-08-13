<?php

namespace GotChosen\Util\MimeType;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * MimeTypeGuesser for uploaded Unity files so that we can get the right
 * mimetype into our uploads.
 *
 * @author jprivett
 */
class UnityMimeTypeGuesser implements MimeTypeGuesserInterface
{
    public function guess($path)
    {
        /**
         * Try to find the original file name of an uploaded file
         */
        if ( is_uploaded_file($path) ) {
            foreach ( $_FILES as $file ) {
                foreach ( $file['tmp_name'] as $form => $name ) {
                    if ( $name == $path ) {
                        $path = $file['name'][$form];
                    }
                }
            }
        }
        
        $info = new \SplFileInfo($path);
        
        if ( $info->getExtension() == 'unity3d' ) {
            return 'application/vnd.unity';
        }
        else {
            return null;
        }
    }
}
