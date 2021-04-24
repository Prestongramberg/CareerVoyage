<?php

namespace App\Model\Report\Dashboard\TopicSatisfactionFeedback;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfPresentations extends AbstractDashboard
{
    protected $header = 'List Of Presentations';

    protected $subHeader = '';

    protected $position = 1;

    protected $numberOfSchools = 0;

    protected $numberOfCompanies = 0;

    protected $numberOfExperiences = 0;

    protected $numberOfFeedbackResponses = 0;

    protected $totalResponses = 0;

    protected $presentations = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['experience']['id'])) {
                continue;
            }

            $this->presentations[$feedback['experience']['id']] = [
                'topic' => $feedback['topic'],
                'presenter' => $feedback['presenter'],
                'school' => !empty($feedback['schoolNames']) ? implode(", ", $feedback['schoolNames']) : null
            ];
        }
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
        return 'report/dashboard/topic_satisfaction_feedback/list_of_presentations.html.twig';
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

    /**
     * @return array
     */
    public function getPresentations(): array
    {
        return $this->presentations;
    }
}