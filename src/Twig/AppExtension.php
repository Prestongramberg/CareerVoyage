<?php


namespace App\Twig;


use App\Entity\CompanyResource;
use App\Entity\Company;
use App\Entity\Experience;
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
            new TwigFunction('encode_company', [$this, 'encodeCompany']),
            new TwigFunction('encode_company_resources', [$this, 'encodeCompanyResources']),
            new TwigFunction('pending_requests', [$this, 'pendingRequests']),
            new TwigFunction('encode_secondary_industries', [$this, 'encodeSecondaryIndustries']),
            new TwigFunction('validate_url', [$this, 'validateUrl']),
            new TwigFunction('excerpt_length', [$this, 'excerptLength'])
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

    public function encodeCompany(Company $object): string
    {
        return $this->serializer->serialize($object, 'json', ['groups' => ['RESULTS_PAGE']]);
    }

    public function encodeCompanyResources($companyResources): string
    {
        return $this->serializer->serialize($companyResources, 'json', ['groups' => ['COMPANY_RESOURCE']]);
    }

    public function encodeSecondaryIndustries(Experience $experience): string
    {
        return $this->serializer->serialize($experience->getSecondaryIndustries(), 'json', ['groups' => ['RESULTS_PAGE']]);
    }

    public function validateUrl( $url ) : string
    {
        $parsed = parse_url($url);
        if (empty($parsed['scheme'])) {
            $url = 'http://' . ltrim($url, '/');
        }
        return $url;
    }

    public function excerptLength( $content, $limit = 200 ) : string
    {
        if ( strlen( $content ) <= $limit) {
            return $content;
        } else {
            return substr( $content, 0, $limit) . '...';
        }
    }


    public function pendingRequests(User $user) {

        $requests = $this->requestRepository->findBy([
            'needsApprovalBy' => $user,
            'approved' => false
        ]);

        return count($requests);
    }
}