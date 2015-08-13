<?php

namespace GotChosen\Util\Upload;

class CustomUploader extends AbstractUploader
{
    public function setMimeTypes(array $mimeTypes)
    {
        $this->allowedMimeTypes = $mimeTypes;
    }
}
