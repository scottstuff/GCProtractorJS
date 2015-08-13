<?php

namespace GotChosen\Util\Upload;

class GameUploader extends AbstractUploader
{
    protected $allowedMimeTypes = [
        'application/x-shockwave-flash',
        'application/vnd.unity'
    ];
}
