<?php

namespace App\Model\Report\Dashboard\RegistrationSummary;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfStudents extends AbstractDashboard
{
    protected $header = 'Students Registered';

    protected $subHeader = '';

    protected $position = 7;

    protected $students = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if (empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] !== 'students_registered_on_platform') {
                continue;
            }

            if (empty($feedback['school'])) {
                continue;
            }

            if (empty($feedback['schoolName'])) {
                continue;
            }

            if (empty($this->students[$feedback['school']]['count'])) {
                $this->students[$feedback['school']]['count'] = 0;
            }

            $this->students[$feedback['school']]['count']++;
            $this->students[$feedback['school']]['name'] = $feedback['schoolName'];
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
        return 'report/dashboard/registration_summary/list_of_students.html.twig';
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

    public function getStudents(): array
    {
        return $this->students;
    }
}