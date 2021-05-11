<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Volunteer;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class Summary extends AbstractDashboard
{
    protected $header = 'Summary';

    protected $subHeader = '';

    protected $position = 0;

    protected $totalRegistrations = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     * @param             $totalRegistrations
     * @param             $hasFilters
     */
    public function __construct(Traversable $feedbackCollection, $totalRegistrations, $hasFilters)
    {
        $registrations = [];

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['registration'])) {
                continue;
            }

            $registrations[] = $feedback['registration'];
        }

        if ($hasFilters) {
            $this->totalRegistrations = count(array_unique($registrations));
        } else {
            $this->totalRegistrations = $totalRegistrations;
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
        return 'report/dashboard/experience_participation/volunteer/summary.html.twig';
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

    public function getTotalRegistrations(): int
    {
        return $this->totalRegistrations;
    }
}