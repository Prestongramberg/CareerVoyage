<?php

namespace App\Service;


use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

use Doctrine\ORM\EntityManager;
use Imagine\Image\ImagineInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Class ActivityImageCacheResolver
 * @package AppBundle\CDN\RackSpace\CacheResolver
 */
class ProfilePictureCacheResolver implements ResolverInterface
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
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * ProfilePictureCacheResolver constructor.
     * @param ImagineInterface $imagine
     * @param FileUploader $fileUploader
     * @param ParameterBagInterface $params
     */
    public function __construct(ImagineInterface $imagine, FileUploader $fileUploader, ParameterBagInterface $params)
    {
        $this->imagine = $imagine;
        $this->fileUploader = $fileUploader;
        $this->params = $params;
    }

    /**
     * This function performs a check to see whether or not the image exists
     * If this returns false then the DataLoader gets called.
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored($path, $filter)
    {
        return $this->fileUploader->photoExists('local/thumbnail/' . $path);
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * Always try to resolve the image from the path and host in the database so you don't have to make
     * a network request to the api. If you can't find it then make the api call.
     *
     * @param string $path The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The absolute URL of the cached image.
     *
     * @throws \Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
     */
    public function resolve($path, $filter)
    {
        return  $this->fileUploader->getPhoto('local/thumbnail/' . $path);
    }

    /**
     * Stores the content of the given binary.
     *
     * @param \Liip\ImagineBundle\Binary\BinaryInterface $binary The image binary to store.
     * @param string $path The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     * @return void
     */
    public function store(\Liip\ImagineBundle\Binary\BinaryInterface $binary, $path, $filter)
    {
        $pathsArray = explode("/", $path);
        $fileName = $pathsArray[count($pathsArray) - 1];

        $folderPath = sprintf("%s/public/local/thumbnail", $this->params->get('kernel.project_dir'));

        if (!is_dir($folderPath)) {
            // dir doesn't exist, make it
            mkdir($folderPath);
        }

        $fullPathToFile = sprintf("%s/%s", $folderPath, $fileName);

        file_put_contents($fullPathToFile, $binary->getContent());

        $this->fileUploader->uploadPhoto(new UploadedFile($fullPathToFile, $fileName), 'local/thumbnail/'.$fileName);
    }

    /**
     * @param string[] $paths The paths where the original files are expected to be.
     * @param string[] $filters The imagine filters in effect.
     * @return bool
     */
    public function remove(array $paths, array $filters)
    {
        if(strpos($paths[0], '/') === false || count($pathArray = explode('/', $paths[0])) < 2) {
            return false;
        }

        $container = $pathArray[0];
        $name = $pathArray[1];

        // don't let an environment other than production delete from a production container
        if(strpos($this->rackspaceFilesContainer, 'staging') !== false && strpos($container, 'staging') === false)
        {
            return false;
        }

        $object = $this->cdnAdapter->getFile($this->getVirtualPath() . $name, $container);

        if($object)
        {
            $object->delete();
        }
    }

}