<?php

namespace KRG\FileBundle\Filesystem\Adapter;

use Gaufrette\Adapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AdapterInterface extends Adapter
{
    /**
     * @param UploadedFile $uploadedFile
     * @param $key
     * @return mixed
     */
    public function upload(UploadedFile $uploadedFile, $key);

    /**
     * @param $key
     * @return string
     */
    public function getUrl($key);
}