<?php


namespace App\Twig;


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
            new TwigFunction('encode_lesson', [$this, 'encodeLesson'])
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploadHelper->getPublicPath($path);
    }

    public function encodeLesson(Lesson $lesson): string
    {
        return $this->serializer->serialize($lesson, 'json', ['groups' => ['LESSON_DATA']]);
    }
}