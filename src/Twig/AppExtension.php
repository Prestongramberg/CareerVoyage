<?php


namespace App\Twig;


use App\Service\UploaderHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    /**
     * @var UploaderHelper
     */
    private $uploadHelper;

    /**
     * AppExtension constructor.
     * @param UploaderHelper $uploadHelper
     */
    public function __construct(UploaderHelper $uploadHelper)
    {
        $this->uploadHelper = $uploadHelper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath'])
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploadHelper->getPublicPath($path);
    }
}