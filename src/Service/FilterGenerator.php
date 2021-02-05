<?php

namespace App\Service;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Model\GlobalShareFilters;
use App\Repository\EducatorUserRepository;
use App\Repository\IndustryRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\RolesWillingToFulfillRepository;
use App\Repository\SchoolAdministratorRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\SiteAdminUserRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StudentUserRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class FilterGenerator
{
    const INDUSTRY_FILTER           = 'INDUSTRY_FILTER';
    const SECONDARY_INDUSTRY_FILTER = 'SECONDARY_INDUSTRY_FILTER';
    const EVENT_TYPE_FILTER         = 'EVENT_TYPE_FILTER';

    /**
     * @var array
     */
    private $availableFilters = [
        self::INDUSTRY_FILTER,
        self::SECONDARY_INDUSTRY_FILTER,
        self::EVENT_TYPE_FILTER,
    ];

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var IndustryRepository
     */
    private $industryRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * @var RolesWillingToFulfillRepository
     */
    private $rolesWillingToFullfillRepository;

    /**
     * FilterGenerator constructor.
     *
     * @param RequestStack                    $requestStack
     * @param IndustryRepository              $industryRepository
     * @param SerializerInterface             $serializer
     * @param SecondaryIndustryRepository     $secondaryIndustryRepository
     * @param RolesWillingToFulfillRepository $rolesWillingToFullfillRepository
     */
    public function __construct(
        RequestStack $requestStack, IndustryRepository $industryRepository,
        SerializerInterface $serializer, SecondaryIndustryRepository $secondaryIndustryRepository,
        RolesWillingToFulfillRepository $rolesWillingToFullfillRepository
    ) {
        $this->requestStack                     = $requestStack;
        $this->industryRepository               = $industryRepository;
        $this->serializer                       = $serializer;
        $this->secondaryIndustryRepository      = $secondaryIndustryRepository;
        $this->rolesWillingToFullfillRepository = $rolesWillingToFullfillRepository;
    }

    public function generate($filters = [], $bustCache = false)
    {
        $data = [
            'industries'          => [],
            'secondaryIndustries' => [],
            'eventTypes'          => [],
        ];

        foreach ($filters as $filter) {

            if (!in_array($filter, $this->availableFilters, true)) {
                continue;
            }

            switch ($filter) {
                case self::INDUSTRY_FILTER:

                    $industries = $this->industryRepository->findAll();

                    $json               = $this->serializer->serialize($industries, 'json', ['groups' => ['EXPERIENCE_DATA']]);
                    $data['industries'] = json_decode($json, true);

                    break;
                case self::SECONDARY_INDUSTRY_FILTER:

                    if ($industry = $this->getQueryParam('industry')) {
                        $secondaryIndustries = $this->secondaryIndustryRepository->findBy(
                            [
                                'primaryIndustry' => $industry,
                            ]
                        );
                    } else {
                        $secondaryIndustries = $this->secondaryIndustryRepository->findAll();
                    }

                    $json                        = $this->serializer->serialize($secondaryIndustries, 'json', ['groups' => ['EXPERIENCE_DATA']]);
                    $data['secondaryIndustries'] = json_decode($json, true);

                    break;
                case self::EVENT_TYPE_FILTER:

                    $rolesWillingToFullfill = $this->rolesWillingToFullfillRepository->findAll();

                    $json       = $this->serializer->serialize($rolesWillingToFullfill, 'json', ['groups' => ['ALL_USER_DATA']]);
                    $eventTypes = json_decode($json, true);

                    $schoolEvents  = [];
                    $companyEvents = [];
                    $otherEvents   = [];

                    foreach ($eventTypes as $eventType) {

                        if ($eventType['inSchoolEventDropdown']) {
                            $schoolEvents[] = $eventType;
                        } elseif ($eventType['inEventDropdown']) {
                            $companyEvents[] = $eventType;
                        } else {
                            $otherEvents[] = $eventType;
                        }
                    }

                    $data['eventTypes'] = [
                        'schoolEvents'  => $schoolEvents,
                        'companyEvents' => $companyEvents,
                        'otherEvents'   => $otherEvents,
                    ];

                    break;
            }
        }

        return $data;
    }

    /**
     * @return Request
     */
    private function getRequest()
    {

        if ($this->requestStack->getCurrentRequest() !== null) {
            return $this->requestStack->getCurrentRequest();
        }

        return new Request();
    }

    private function getQueryParam($param)
    {

        $request = $this->getRequest();

        return $request->query->get($param, null);

    }
}