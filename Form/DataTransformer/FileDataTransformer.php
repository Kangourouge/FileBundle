<?php

namespace KRG\FileBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Exception\TransformationFailedException;
use KRG\FileBundle\Entity\FileInterface;
use Gaufrette\Util;

class FileDataTransformer implements DataTransformerInterface
{
    public function transform($file)
    {
        if ($file instanceof FileInterface) {
            return $file;
        }
        return null;
    }

    public function reverseTransform($file)
    {
        if (is_string($file->getPath()) && strlen($file->getPath())) {
            return $file;
        } else if ($file->getPath() instanceof UploadedFile) {
            $pathname = $file->getPath()->getPathname();

            $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($pathname);

            /* @var $file FileInterface */
            $file->setChecksum(Util\Checksum::fromFile($pathname));
            $file->setMimeType($mimeType);
            $file->setSize(Util\Size::fromFile($pathname));

            $originalName = preg_replace('/^(.+)\.[a-z]+$/', '$1', $file->getPath()->getClientOriginalName());
            $file->setName($originalName);

            if (preg_match('/^image\/.+$/', $mimeType)) {
                if ($info = @getimagesize($pathname)){
                    list($width, $height) = $info;
                    $file->setWidth($width);
                    $file->setHeight($height);
                }
            }
            return $file;
        }

        return null;
    }
}

