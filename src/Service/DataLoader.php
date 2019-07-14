<?php

namespace App\Service;

use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;


/**
 * Class DataLoader
 * @author Josh Crawmer <jcrawmer@smp.org>
 */
class DataLoader implements LoaderInterface
{

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var FileUploader
     */
    protected $fileUploader;

    /**
     * DataLoader constructor.
     * @param ImagineInterface $imagine
     * @param FileUploader $fileUploader
     */
    public function __construct(ImagineInterface $imagine, FileUploader $fileUploader)
    {
        $this->imagine = $imagine;
        $this->fileUploader = $fileUploader;
    }

    /**
     * Retrieve the Image represented by the given path.
     *
     * The path may be a file path on a filesystem, or any unique identifier among the storage engine implemented by this Loader.
     *
     * This method has 2 purposes
     *
     * 1. This method gets called prior to the caching to retrieve the original image to perform the caches on
     *
     * 2. If isStored($path, $filter) returns false in the Cache Resolver then Liip Imagine bundle will call this method to
     * see if this method can load a binary version of the image as a safe proof from the original spot the image was stored prior
     * to the caching
     *
     * @param mixed $path
     *
     * @return \Liip\ImagineBundle\Binary\BinaryInterface|string An image binary content
     */
    public function find($path)
    {

        // make sure the path is formatted properly with the container name and the image name
        /*if(strpos($path, '/') === false || count($pathArray = explode('/', $path)) < 2) {
            return false;
        }

        $container = $pathArray[0];
        $name = $pathArray[1];

        $object = $this->cdnAdapter->getFile(self::PRE_CACHED_PATH . $name, $container);

        if(!$object)
            return false;*/

        $path = (string) $path;

        return $this->imagine->load(file_get_contents($path));
    }
}