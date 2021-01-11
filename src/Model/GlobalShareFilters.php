<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class GlobalShareFilters
 *
 * @package App\Model
 */
class GlobalShareFilters
{
    const ITEMS_PER_PAGE = 500;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $userRoles = [];

    /**
     * @var array
     */
    private $volunteerRoles = [];

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var string
     */
    private $search = '';

    /**
     * @var array
     */
    private $companies = [];

    /**
     * @var string
     */
    private $interestSearch = '';

    /**
     * @var array
     */
    private $companyAdmins = [];

    /**
     * @var array
     */
    private $primaryIndustries = [];

    /**
     * @var array
     */
    private $secondaryIndustries = [];

    /**
     * @var array
     */
    private $coursesTaught = [];

    /**
     * @var array
     */
    private $schools = [];

    public function __construct(Request $request = null)
    {
        if(!$request) {
            return;
        }

        $this->request = $request;
        $this->search  = $request->query->get('search');
        $search        = $request->request->get('search');

        $this->setUserRoles($search['user_roles'])
             ->setVolunteerRoles($search['roles'])
             ->setCompanies($search['companies'])
             ->setCompanyAdmins($search['company_admins'])
             ->setInterestSearch($search['interests'])
             ->setPage($request->query->get('page', 1))
             ->setPrimaryIndustries($search['primary_industries'])
             ->setSecondaryIndustries($search['secondary_industries'])
             ->setCoursesTaught($search['courses_taught'])
             ->setSearch($request->query->get('search', ''))
             ->setSchools($search['schools']);
    }

    public function hasFilterByProfessional()
    {
        return in_array('professional', $this->userRoles);
    }

    public function hasFilterByEducator()
    {
        return in_array('educator', $this->userRoles);
    }

    public function hasFilterByStudent()
    {
        return in_array('student', $this->userRoles);
    }

    public function hasFilterBySchoolAdministrator()
    {
        return in_array('school_administrator', $this->userRoles);
    }

    public function hasFilterByCompanyAdministrator()
    {
        return in_array('company_administrator', $this->userRoles);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return GlobalShareFilters
     */
    public function setRequest(Request $request): GlobalShareFilters
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserRoles(): array
    {
        return $this->userRoles;
    }

    /**
     * @param array $userRoles
     *
     * @return GlobalShareFilters
     */
    public function setUserRoles(array $userRoles): GlobalShareFilters
    {
        foreach ($userRoles as $userRole) {
            $this->userRoles[] = $userRole['value'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getVolunteerRoles(): array
    {
        return $this->volunteerRoles;
    }

    /**
     * @param array $volunteerRoles
     *
     * @return GlobalShareFilters
     */
    public function setVolunteerRoles(array $volunteerRoles): GlobalShareFilters
    {
        foreach ($volunteerRoles as $role) {
            $this->volunteerRoles[] = $role['value'];
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return GlobalShareFilters
     */
    public function setPage(int $page): GlobalShareFilters
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     *
     * @return GlobalShareFilters
     */
    public function setSearch(string $search): GlobalShareFilters
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return array
     */
    public function getCompanies(): array
    {
        return $this->companies;
    }

    /**
     * @param array $companies
     *
     * @return GlobalShareFilters
     */
    public function setCompanies(array $companies): GlobalShareFilters
    {
        foreach ($companies as $company) {
            $this->companies[] = $company['value'];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getInterestSearch(): string
    {
        return $this->interestSearch;
    }

    /**
     * @param string $interestSearch
     *
     * @return GlobalShareFilters
     */
    public function setInterestSearch(string $interestSearch): GlobalShareFilters
    {
        $this->interestSearch = $interestSearch;

        return $this;
    }

    /**
     * @return array
     */
    public function getCompanyAdmins(): array
    {
        return $this->companyAdmins;
    }

    /**
     * @param array $companyAdmins
     *
     * @return GlobalShareFilters
     */
    public function setCompanyAdmins(array $companyAdmins): GlobalShareFilters
    {
        foreach ($companyAdmins as $companyAdmin) {
            $this->companyAdmins[] = $companyAdmin['value'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPrimaryIndustries(): array
    {
        return $this->primaryIndustries;
    }

    /**
     * @param array $primaryIndustries
     *
     * @return GlobalShareFilters
     */
    public function setPrimaryIndustries(array $primaryIndustries): GlobalShareFilters
    {
        foreach ($primaryIndustries as $primaryIndustry) {
            $this->primaryIndustries[] = $primaryIndustry['value'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSecondaryIndustries(): array
    {
        return $this->secondaryIndustries;
    }

    /**
     * @param array $secondaryIndustries
     *
     * @return GlobalShareFilters
     */
    public function setSecondaryIndustries(array $secondaryIndustries): GlobalShareFilters
    {
        foreach ($secondaryIndustries as $secondaryIndustry) {
            $this->secondaryIndustries[] = $secondaryIndustry['value'];
        }

        return $this;
    }

    public function getOffset()
    {

        $page = $this->getPage();

        return ($page - 1) * self::ITEMS_PER_PAGE;
    }

    /**
     * @return array
     */
    public function getCoursesTaught(): array
    {
        return $this->coursesTaught;
    }

    /**
     * @param array $coursesTaught
     *
     * @return GlobalShareFilters
     */
    public function setCoursesTaught(array $coursesTaught): GlobalShareFilters
    {
        foreach ($coursesTaught as $courseTaught) {
            $this->coursesTaught[] = $courseTaught['value'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSchools(): array
    {
        return $this->schools;
    }

    /**
     * @param array $schools
     *
     * @return GlobalShareFilters
     */
    public function setSchools(array $schools): GlobalShareFilters
    {
        foreach ($schools as $school) {
            $this->schools[] = $school['value'];
        }

        return $this;
    }
}