<?php

namespace App\EntityListener;


use App\Entity\File;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileListener
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * FileListener constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     */
    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
    }

    /**
     * Make sure the file has a name before persisting
     *
     * @param File $file
     * @param LifecycleEventArgs $args
     */
    public function prePersist(File $file, LifecycleEventArgs $args)
    {

        $name = "Josh";
        // if the image is a copy then retrieve the original image before you modify the container and image path name
     /*   if($image->isCopy()) {
            $dataObject = $this->cdnAdapter->getFile($image->getPreCachedCDNPath(), $image->getContainer());
            $image->generateCopyPath();
            $image->setContainer($this->rackSpaceFilesContainer);
            try {
                $d = $dataObject->copy($this->rackSpaceFilesContainer . '/' . $image->getPreCachedCDNPath());
            } catch(\Exception $e) {
            }
        } else {
            $image->setContainer($this->rackSpaceFilesContainer);
            $image->preUpload();
        }*/

    }

    /**
     * Make sure the file has a name before updating
     *
     * @param File $file
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(File $file, PreUpdateEventArgs $args)
    {

        if($fileInfo = $this->fileUploader->upload($file)) {
            $name = "Josh";
            $file->setPath($fileInfo['@metadata']['effectiveUri']);
        }

        $name = "Josh";
        /*$image->setContainer($this->rackSpaceFilesContainer);
        $image->preUpload();*/
    }

    /**
     * Upload the file after persisting the entity
     *
     * @param File $file
     * @param LifecycleEventArgs $args
     */
    public function postPersist(File $file, LifecycleEventArgs $args)
    {

        $name = "JOsh";
       /* if(!$image->isCopy() && $image->getFile()) {
            $this->cdnAdapter->uploadFile($image->getPreCachedCDNPath(), fopen($image->getFile()->getRealPath(), 'r+'), $image->getContainer());
        }

        $path = $image->getContainer() . '/' . $image->getPath();


        $imageCacheCron = new ImageCachingCron();
        $imageCacheCron->setImage($image);
        $this->entityManager->persist($imageCacheCron);
        $this->entityManager->flush($imageCacheCron);*/

    }

    /**
     * Upload the file after updating the entity
     *
     * @param File $file
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(File $file, LifecycleEventArgs $args)
    {
        $name = "Josh";
    }
}