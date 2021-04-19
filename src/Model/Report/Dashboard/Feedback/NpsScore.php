<?php

namespace App\Model\Report\Dashboard\Feedback;

use App\Entity\Feedback;
use App\Model\Collection\FeedbackCollection;
use App\Model\Report\Dashboard\AbstractDashboard;
use Pinq\ITraversable;
use Pinq\Traversable;

class NpsScore extends AbstractDashboard
{
    protected $header = '';

    protected $subHeader = '';

    protected $position = 7;

    protected $totalResponses = 0;

    protected $npsScore = 0;

    /**
     * BarChart constructor.
     *
     * @param Traversable $feedbackCollection
     */
    public function __construct(Traversable $feedbackCollection)
    {
        $cumulativePromoters  = 0;
        $cumulativeDetractors = 0;

        /** @var Feedback $feedback */
        foreach ($feedbackCollection as $feedback) {

            if($feedback['likelihoodToRecommendToFriend'] === null) {
                continue;
            }

            if ($feedback['likelihoodToRecommendToFriend'] > 8) {
                $cumulativePromoters++;
            }

            if ($feedback['likelihoodToRecommendToFriend'] < 7) {
                $cumulativeDetractors++;
            }

            $this->totalResponses++;
        }

        if ($this->totalResponses > 0) {
            $this->npsScore = round((($cumulativePromoters / $this->totalResponses) - ($cumulativeDetractors / $this->totalResponses)) * 100);
        }

        $this->subHeader = sprintf("(%s Responses)", $this->totalResponses);

        $this->header = 'NPS Score';
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
        return 'report/dashboard/feedback/nps.html.twig';
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

    public function getNpsScore(): int
    {
        return $this->npsScore;
    }

    public function getTotalResponses(): int
    {
        return $this->totalResponses;
    }
}