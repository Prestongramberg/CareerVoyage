<?php


namespace App\Twig;


use App\Entity\CompanyResource;
use App\Entity\Lesson;
use App\Service\UploaderHelper;
use Symfony\Component\Serializer\SerializerInterface;
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * AppExtension constructor.
     * @param UploaderHelper $uploadHelper
     * @param SerializerInterface $serializer
     */
    public function __construct(UploaderHelper $uploadHelper, SerializerInterface $serializer)
    {
        $this->uploadHelper = $uploadHelper;
        $this->serializer = $serializer;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('encode_lesson', [$this, 'encodeLesson']),
            new TwigFunction('encode_company_resources', [$this, 'encodeCompanyResources'])
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploadHelper->getPublicPath($path);
    }

    public function encodeLesson(Lesson $object): string
    {
        return $this->serializer->serialize($object, 'json', ['groups' => ['LESSON_DATA']]);
    }

    public function encodeCompanyResources($companyResources): string
    {
        return $this->serializer->serialize($companyResources, 'json', ['groups' => ['COMPANY_RESOURCE']]);
    }
}