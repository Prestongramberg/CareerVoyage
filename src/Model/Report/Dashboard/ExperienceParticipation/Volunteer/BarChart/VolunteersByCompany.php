<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Volunteer\BarChart;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class VolunteersByCompany extends AbstractDashboard
{
    protected $type = 'bar';

    protected $labels = [];

    protected $label = 'Bar Chart';

    protected $data = [];

    protected $backgroundColor = 'rgb(255, 99, 132)';

    protected $borderColor = 'rgb(255, 99, 132)';

    protected $header = 'Volunteer by company';

    protected $subHeader = '';

    protected $position = 1;

    protected $footer = '';

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $professionals = [];

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['professional'])) {
                continue;
            }

            if (empty($feedback['professionalName'])) {
                continue;
            }

            if (empty($this->data[$feedback['companyName']])) {
                $this->data[$feedback['companyName']] = 0;
            }

            $professionals[] = $feedback['professional'];
            $this->data[$feedback['companyName']]++;

        }

        $this->labels = array_keys($this->data);
        $this->data = array_values($this->data);

        $this->subHeader = sprintf("Total: %s", count(array_unique($professionals)));

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
        return 'full-bottom';
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