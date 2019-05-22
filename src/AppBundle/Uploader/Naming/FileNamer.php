<?php

namespace AppBundle\Uploader\Naming;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;

class FileNamer implements NamerInterface
{

    public function __construct()
    {

    }

    /**
     * Rename the file to put it into the tenant's folder
     * @param FileInterface $file
     * @return string
     */
    public function name(FileInterface $file)
    {
        return 'core/logos/'.uniqid().'-'.$file->getClientOriginalName();
    }
}