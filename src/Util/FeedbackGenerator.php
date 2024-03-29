<?php

namespace App\Util;

use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\ProfessionalReviewStudentToMeetProfessionalFeedback;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\StudentReviewMeetProfessionalExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\TeachLessonExperience;
use App\Entity\CompanyExperience;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Twig\Environment;
use App\Entity\StudentToMeetProfessionalExperience;

/**
 * Class FeedbackGenerator
 * @package App\Util
 *
 */
class FeedbackGenerator implements \Iterator
{
    /**
     * @var Experience[]
     */
    private $experiences;

    /**
     * @var User $userContext
     */
    private $userContext;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var bool
     */
    private $cached = false;

    /**
     * @var int Stores the current traversal position. An iterator may have a
     * lot of other fields for storing iteration state, especially when it is
     * supposed to work with a particular kind of collection.
     */
    private $position = 0;

    /**
     * @var bool $aggregate
     */
    private $aggregate = false;

    /**
     * FeedbackGenerator constructor.
     * @param array $experiences
     * @param User $userContext
     * @param Environment $twig
     */
    public function __construct(array $experiences, User $userContext, Environment $twig)
    {
        $this->userContext = $userContext;
        $this->experiences = array_values($this->getExperiences($experiences));
        $this->twig = $twig;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->experiences[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function valid()
    {
        return isset($this->experiences[$this->position]);
    }

    /**
     * Determine which events to show based upon user role
     *
     * @param $experiences
     * @return Experience[]|array
     */
    private function getExperiences($experiences)
    {
        if($this->cached) {
            return $this->experiences;
        }

        $experiences = new ArrayCollection($experiences);
        $experiences = $experiences->filter(function (Experience $experience) {

            // todo remove this at some point. Possibly remove this whole function
            if($this->userContext->isSchoolAdministrator()) {
                return true;
            }

            // todo remove this as well? I don't think we need this at all.
            if($this->userContext->isProfessional()) {
                return true;
            }

            if($this->userContext->isEducator()) {
                return true;
            }

            return false;
        });

        $this->experiences = $experiences->filter(function(Experience $experience) {

            if($this->userContext->isSchoolAdministrator()) {
                return true;
            }

            if($this->userContext->isEducator()) {
                return true;
            }

            if($this->userContext->isStudent()) {
                return $experience instanceof StudentToMeetProfessionalExperience;
            }

            if($this->userContext->isProfessional()) {
                return true;
            }

            return false;

        });

        $this->cached = true;

        return $this->experiences->toArray();
    }

    /**
     * Determine which feedback to show based upon user role
     *
     * @param Experience $experience
     * @return array
     */
    public function getFeedback()
    {
        $experiences = $this->aggregate ? $this->experiences : [$this->current()];

        $feedbackResults = [];

        $possibleStudentRegistrations = [];
        foreach($experiences as $experience) {

            $filteredFeedback = $experience->getFeedback()->filter(function(Feedback $feedback) use($possibleStudentRegistrations) {

                // some of the legacy code still has direct feedback classes attached to events which we aren't using here
                /*if(get_class($feedback) === Feedback::class) {
                    return false;
                }*/

                if($feedback->getDeleted()) {
                    return false;
                }

                // TODO THIS NEEDS TO BE REMOVED AS SOME FEEDBACK WILL BE ANONYMOUS NOW.
            /*    if(!$feedback->getUser()) {
                    return false;
                }*/

                if($this->userContext->isSchoolAdministrator()) {
                    return true;
                }

                if($this->userContext->isProfessional()) {
                    return true;
                }

                if($this->userContext->isEducator()) {
                    return true;
                }

                if($this->userContext->isStudent()) {

                    return (
                        $feedback instanceof ProfessionalReviewStudentToMeetProfessionalFeedback &&
                        $feedback->getStudentToMeetProfessionalExperience() &&
                        $feedback->getStudentToMeetProfessionalExperience()->getOriginalRequest() &&
                        $feedback->getStudentToMeetProfessionalExperience()->getOriginalRequest()->getStudent() &&
                        $feedback->getStudentToMeetProfessionalExperience()->getOriginalRequest()->getStudent()->getId() === $this->userContext->getId()
                    );
                }

                if($feedback->getUser()->getId() === $this->userContext->getId()) {
                    return true;
                }

                return false;

            });

            $feedbackResults[] = $filteredFeedback->toArray();
        }

        if(!empty($feedbackResults)) {
            return array_merge(...$feedbackResults);
        }

        return [];
    }

    /**
     * @param Experience|null $event
     * @return int
     */
    public function cumulativeRating() {

        $cumulativeRating = 0;

        foreach($this->getFeedback() as $feedback) {

            $cumulativeRating += (int) $feedback->getRating();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return round($cumulativeRating / $totalFeedback, 1);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function cumulativeShowedUp() {

        $cumulativeShowUp = 0;

        foreach($this->getFeedback() as $feedback) {
            
            $cumulativeShowUp += (int) $feedback->getShowUp();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($cumulativeShowUp / $totalFeedback) * 100 ) . '%';
        }

        return "0%";
    }

    /**
     * @return int
     */
    public function cumulativeInsight() {

        $cumulativeInsight = 0;

        foreach($this->getFeedback() as $feedback) {

            $cumulativeInsight += (int) $feedback->getProvidedCareerInsight();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($cumulativeInsight / $totalFeedback) * 100) . '%';
        }

        return "0%";
    }

    public function cumulativeEnjoyable() {

        $cumulativeEnjoyable = 0;

        foreach($this->getFeedback() as $feedback) {

            $cumulativeEnjoyable += (int) $feedback->getWasEnjoyableAndEngaging();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($cumulativeEnjoyable / $totalFeedback) * 100) . '%';
        }

        return "0%";

    }

    public function cumulativeLearned() {

        $learnSomethingNew = 0;

        foreach($this->getFeedback() as $feedback) {

            $learnSomethingNew += (int) $feedback->getLearnSomethingNew();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($learnSomethingNew / $totalFeedback) * 100) . '%';
        }

        return "0%";

    }

    /**
     * @return int
     */
    public function cumulativeOnTime() {

        $cumulativeOnTime = 0;

        foreach($this->getFeedback() as $feedback) {

            $cumulativeOnTime += (int) $feedback->getWasOnTime();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($cumulativeOnTime / $totalFeedback) * 100 ). '%';
        }

        return '0%';
    }

    /**
     * @return int
     */
    public function cumulativePolite() {

        $cumulativePolite = 0;

        foreach($this->getFeedback() as $feedback) {

            $cumulativePolite += (int) $feedback->getPoliteAndProfessional();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($cumulativePolite / $totalFeedback) * 100).'%';
        }

        return '0%';

    }

    /**
     * @return int
     */
    public function cumulativePromoters() {

        $cumulativePromoters = 0;

        /** @var Feedback $feedback */
        foreach($this->getFeedback() as $feedback) {

            if($feedback->getLikelihoodToRecommendToFriend() > 8) {
                $cumulativePromoters++;
            }
        }

        return $cumulativePromoters;
    }

    /**
     * @return int
     */
    public function cumulativeDetractors() {

        $cumulativeDetractors = 0;

        /** @var Feedback $feedback */
        foreach($this->getFeedback() as $feedback) {

            if($feedback->getLikelihoodToRecommendToFriend() < 7) {
                $cumulativeDetractors++;
            }
        }

        return $cumulativeDetractors;

    }

    /**
     * @return int
     */
    public function cumulativePassives() {

        $cumulativePassives = 0;

        foreach($this->getFeedback() as $feedback) {

            if($feedback->getLikelihoodToRecommendToFriend() === 6 || $feedback->getLikelihoodToRecommendToFriend() === 7) {
                $cumulativePassives++;
            }
        }

        return $cumulativePassives;

    }

    /**
     * @return int
     */
    public function cumulativeEngaged() {

        $cumulativeEngaged = 0;

        foreach($this->getFeedback() as $feedback) {

            $cumulativeEngaged += (int) $feedback->getEngagedAndAskedQuestions();
        }

        $totalFeedback = $this->totalFeedback();

        if($totalFeedback > 0) {
            return floor( ($cumulativeEngaged / $totalFeedback) * 100).'%';
        }

        return '0%';
    }

    /**
     * @return int
     */
    public function totalFeedback() {

        return count($this->getFeedback());
    }

    public function npmScore() {

        $cumulativePromoters = $this->cumulativePromoters();
        $totalFeedback = $this->totalFeedback();
        $cumulativeDetractors = $this->cumulativeDetractors();

        if($totalFeedback > 0) {
            $npmScore =  round((($cumulativePromoters / $totalFeedback) - ($cumulativeDetractors / $totalFeedback)) * 100);
        } else {
            $npmScore = 0;
        }

        return $npmScore;
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderTableHeader() {

        $experience = $this->current();

        $template = "widget/feedback/table_header.html.twig";

        if($this->twig->getLoader()->exists($template)) {

            return $this->twig->render($template, []);
        }

        return '';
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderTableBody() {

        $experience = $this->current();

        $template = "widget/feedback/table_body.html.twig";

        if($this->twig->getLoader()->exists($template)) {
            return $this->twig->render($template, ['feedbacks' => $this->getFeedback()]);
        }

        return '';
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderDataBreakdown() {

        // TODO StudentToMeetProfessionalExperience can have different feedback classes.
        //  is there a way to render based off of that instead of the experience?
        $experience = $this->current();

        $template = "widget/feedback/data_breakdown.html.twig";

        if($this->twig->getLoader()->exists($template)) {
            return $this->twig->render($template, ['feedbackGenerator' => $this]);
        }

        return '';
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderAggregateDataBreakdown() {

        $this->aggregate = true;

        $template = "widget/feedback/aggregate.html.twig";

        $html = $this->twig->render($template, ['feedbackGenerator' => $this]);

        $this->aggregate = false;

        return $html;
    }
}
