<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Volunteer\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class VolunteersByExperienceType extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = [];

    protected $label = 'Bar Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Volunteer by experience type';

    protected $subHeader = '';

    protected $position = 12;

    protected $footer = '';

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $totalCount = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['professional'])) {
                continue;
            }

            if (empty($feedback['professionalName'])) {
                continue;
            }

            if(empty($feedback['experienceType'])) {
                $feedback['experienceType'] = 'Guest Instructor';
            }

            if (empty($this->data[$feedback['experienceType']])) {
                $this->data[$feedback['experienceType']] = 0;
            }

            $totalCount++;
            $this->data[$feedback['experienceType']]++;
        }

        // todo sort alphabetically?
        $this->labels = array_keys($this->data);
        $this->data = array_values($this->data);

        $this->subHeader = sprintf("Total: %s", $totalCount);

    }

    public function render()
    {
        return json_encode([
            'type' => $this->type,
            'options' => [
                'legend' => [
                    'display' => false,
                ],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'beginAtZero' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'data' => [
                'labels' => $this->labels,
                'datasets' => [
                    [
                        'backgroundColor' => $this->backgroundColor,
                        'borderColor' => $this->borderColor,
                        'data' => $this->data,
                    ],
                ],
            ],
        ]);
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
        return $this->footer;
    }

    public function getTemplate()
    {
        return 'report/dashboard/bar_chart.html.twig';
    }

    public function getLocation()
    {
        return 'bottom';
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }
}