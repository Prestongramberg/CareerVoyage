<?php


namespace App\Twig;


use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\RequestRepository;
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
     * @var RequestRepository
     */
    private $requestRepository;

    /**
     * AppExtension constructor.
     * @param UploaderHelper $uploadHelper
     * @param SerializerInterface $serializer
     * @param RequestRepository $requestRepository
     */
    public function __construct(
        UploaderHelper $uploadHelper,
        SerializerInterface $serializer,
        RequestRepository $requestRepository
    ) {
        $this->uploadHelper = $uploadHelper;
        $this->serializer = $serializer;
        $this->requestRepository = $requestRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('encode_lesson', [$this, 'encodeLesson']),
            new TwigFunction('pending_requests', [$this, 'pendingRequests'])
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

    public function pendingRequests(User $user) {

        $requests = $this->requestRepository->findBy([
            'needsApprovalBy' => $user,
            'approved' => false
        ]);

        return count($requests);
    }
}