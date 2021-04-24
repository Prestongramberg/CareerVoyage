<?php

namespace App\Model\Report\Dashboard\TopicSatisfactionFeedback;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class Summary extends AbstractDashboard
{
    protected $header = '';

    protected $subHeader = '';

    protected $position = 0;

    protected $numberOfSchools = 0;

    protected $numberOfCompanies = 0;

    protected $numberOfExperiences = 0;

    protected $numberOfFeedbackResponses = 0;

    protected $totalResponses = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $schoolIds     = [];
        $companyIds    = [];
        $experienceIds = [];

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            $schools = $feedback['schools'] ?? [];
            $companies = $feedback['companies'] ?? [];

            foreach ($schools as $school) {
                $schoolIds[] = $school;
            }

            foreach ($companies as $company) {
                $companyIds[] = $company;
            }

            if (!empty(($experienceId = $feedback['experience']['id']))) {
                $experienceIds[] = $experienceId;
            }

            $this->totalResponses++;
        }

        $this->numberOfSchools     = count(array_unique($schoolIds));
        $this->numberOfCompanies   = count(array_unique($companyIds));
        $this->numberOfExperiences = count(array_unique($experienceIds));

        $this->header = $this->label = 'Summary';
    }

    public function render()
    {
        return json_encode([]);
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getSubHeader(): string
    {
        return $this->subHeader;
    }

    public function getFooter()
    {
        return '';
    }

    public function getTemplate()
    {
        return 'report/dashboard/topic_satisfaction_feedback/summary.html.twig';
    }

    public function getLocation()
    {
        return 'top';
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getNumberOfSchools(): int
    {
        return $this->numberOfSchools;
    }

    /**
     * @return int
     */
    public function getNumberOfCompanies(): int
    {
        return $this->numberOfCompanies;
    }

    /**
     * @return int
     */
    public function getNumberOfExperiences(): int
    {
        return $this->numberOfExperiences;
    }

    /**
     * @return int
     */
    public function getNumberOfFeedbackResponses(): int
    {
        return $this->numberOfFeedbackResponses;
    }

    /**
     * @return int
     */
    public function getTotalResponses(): int
    {
        return $this->totalResponses;
    }
}