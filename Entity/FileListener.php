<?php

namespace KRG\FileBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use KRG\FileBundle\Filesystem\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileListener
{
    /**
     * @var FilesystemMap
     */
    private $filesystemMap;

    /**
     * @var array
     */
    private $files = array('persist' => array(), 'remove' => array());

    /**
     * FileListener constructor.
     * @param FilesystemMap $filesystemMap
     */
    public function __construct(FilesystemMap $filesystemMap)
    {
        $this->filesystemMap = $filesystemMap;
    }

    public function postLoad(LifecycleEventArgs $event) {
        /* @var FileInterface $file */
        $file = $event->getEntity();

        $file->setFilesystem($this->getFilesystem($file));
    }

    public function preFlush(PreFlushEventArgs $event) {

        $uow = $event->getEntityManager()->getUnitOfWork();

        foreach($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof FileInterface) {
                $this->onPreFlush($uow, $entity);
            }
        }

        foreach($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof FileInterface) {
                $this->onPreFlush($uow, $entity);
            }
        }

        foreach($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof FileInterface && $this->isFileUsedByOther($event->getEntityManager(), $entity)) {
                $this->files['remove'][] = $entity;
            }
        }
    }

    public function onPreFlush(UnitOfWork $uow, FileInterface $file) {
        if (($uploadedFile = $file->getPath()) instanceof UploadedFile) {
            $path = $this->getPath($file);
            $file->setPath($path);

            $this->files['persist'][] = array($file, $uploadedFile);

            $uow->propertyChanged($file, 'path', $uploadedFile, $path);
        }
    }

    public function onFlush(OnFlushEventArgs $event) {
        /* @var $uploadedFile UploadedFile */
        foreach ($this->files['persist'] as $idx => $data) {
            list($file, $uploadedFile) = $data;

            $filesystem = $this->getFilesystem($file);
            $adapter = $filesystem->getAdapter();

            if ($adapter instanceof AdapterInterface) {
                $adapter->upload($uploadedFile, $file->getPath());
            } else {
                $filesystem->write($file->getPath(), file_get_contents($uploadedFile->getPathname()), true);
                @unlink($uploadedFile->getPathname());
            }
            unset($this->files['persist'][$idx]);
        }
    }

    public function postFlush(PostFlushEventArgs $event) {
        /* @var $file FileInterface */
        foreach ($this->files['remove'] as $file) {
            $filesystem = $this->getFilesystem($file);
            $adapter = $filesystem->getAdapter();
            if ($adapter instanceof AdapterInterface) {
                $adapter->delete($file->getPath());
            }
        }
    }

    /**
     * @param FileInterface $file
     * @return \Gaufrette\Filesystem
     */
    private function getFilesystem(FileInterface $file)
    {
        return $this->filesystemMap->get($file->getAdapter());
    }

    private function getPath(FileInterface $file)
    {
        $extensionGuesser = new MimeTypeExtensionGuesser();
        $extension = $extensionGuesser->guess($file->getMimeType());

        $path = sprintf('%s%s', $file->getChecksum(), $file->getSize());

        return $extension ? sprintf('%s.%s', $path, $extension) : $path;
    }

    private function isFileUsedByOther(EntityManager $entityManager, FileInterface $file) {

        $repository = $entityManager->getRepository(FileInterface::class);

        $queryBuilder = $repository
                            ->createQueryBuilder('f')
                                ->select('count(f.id)')
                                ->where('f.path = :path AND f.size = :size AND f.adapter = :adapter AND f.checksum = :checksum AND f.mimeType = :mimeType')
                                ->setParameters(array(
                                    'path' => $file->getPath(),
                                    'size' => $file->getSize(),
                                    'adapter' => $file->getAdapter(),
                                    'checksum' => $file->getChecksum(),
                                    'mimeType' => $file->getMimeType()
                                ));

        return (int) $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }
}