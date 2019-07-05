<?php

namespace App\EntityListener;

use App\Entity\Image;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageListener
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
     * @param Image $image
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Image $image, LifecycleEventArgs $args)
    {

        $image->preUpload();

        if($fileInfo = $this->fileUploader->upload($image)) {
            $image->setPath($fileInfo['@metadata']['effectiveUri']);
        }


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
     * @param Image $image
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Image $image, PreUpdateEventArgs $args)
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
     * @param Image $image
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Image $image, LifecycleEventArgs $args)
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
     * @param Image $image
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Image $image, LifecycleEventArgs $args)
    {
        $name = "Josh";
    }
}