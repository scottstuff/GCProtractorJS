<?php

namespace GotChosen\Util\Upload;

class PhotoUploader extends AbstractUploader
{
    protected $allowedMimeTypes = ['image/jpeg', 'image/png'];
}
