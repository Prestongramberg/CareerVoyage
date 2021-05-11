<?php

namespace App\Model\Report\Dashboard\ExperienceParticipation\Student;

use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class ListOfExperiencesByStudent extends AbstractDashboard
{
    protected $header = 'Student experience count';

    protected $subHeader = '';

    protected $position = 4;

    protected $students = [];

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {

        foreach ($feedbackCollection as $feedback) {

            if(empty($feedback['dashboardType'])) {
                continue;
            }

            if ($feedback['dashboardType'] !== 'student_experience_participation') {
                continue;
            }

            if (empty($feedback['student'])) {
                continue;
            }

            if (empty($feedback['studentName'])) {
                continue;
            }

            if (empty($feedback['experience'])) {
                continue;
            }

            if(empty($this->students[$feedback['student']]['experience_count'])) {
                $this->students[$feedback['student']]['experience_count'] = 0;
            }

            $this->students[$feedback['student']]['experience_count']++;

            $this->students[$feedback['student']]['name'] = $feedback['studentName'];
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
        return 'report/dashboard/experience_participation/student/list_of_experiences_by_student.html.twig';
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