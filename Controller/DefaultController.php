<?php

namespace KRG\FileBundle\Controller;

use Gaufrette\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 * @package KRG\FileBundle\Controller
 */
class DefaultController extends Controller
{
    public function listAction(Request $request, $adapter)
    {

        /* @var $filesystem Filesystem */
        $filesystem = $this->get('knp_gaufrette.filesystem_map')
                                ->get($adapter);

        $files = array();

        foreach($filesystem->keys() as $key) {
            if ($filesystem->isDirectory($key)) {
                continue;
            }
            $file = $filesystem->get($key);
            $files[sha1($key)] = array(
                'key' => $key,
                'size' => $file->getSize(),
                'checksum' => $filesystem->checksum($key),
                'mimeType' => $filesystem->mimeType($key),
                'mtime' => $file->getMtime()
            );
        }

        return new JsonResponse($files);
    }
}