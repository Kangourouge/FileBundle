<?php

namespace KRG\FileBundle\Filesystem\Adapter;

use Gaufrette\Adapter\Local as BaseLocal;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Local extends BaseLocal implements AdapterInterface
{
    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param $key
     * @return string
     */
    public function getPath($key) {
        return $this->computePath($key);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param $key
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function upload(UploadedFile $uploadedFile, $key)
    {
        $uploadedFile->move($this->directory, $key);
    }

    /**
     * @param $key
     * @return string
     */
    public function getUrl($key) {
        return preg_replace('/.*\/web/', '', $this->computePath($key));
    }
}