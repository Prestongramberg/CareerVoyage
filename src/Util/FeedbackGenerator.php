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

            $possibleStudentRegistrations = [];

            // if the event hasn't happened yet then skip it
            if($experience->getStartDateAndTime() > new \DateTime()) {
                return false;
            }

            if($this->userContext->isSchoolAdministrator()) {

                /** @var SchoolAdministrator $schoolAdmin */
                $schoolAdmin = $this->userContext;

                foreach($schoolAdmin->getSchools() as $school) {
                    foreach($school->getStudentUsers() as $studentUser) {

                        $possibleStudentRegistrations[] = $studentUser->getId();
                    }
                }
            }

            if($this->userContext->isEducator()) {

                /** @var EducatorUser $educator */
                $educator = $this->userContext;

                if(!$educator->getSchool()) {
                    return false;
                }

                foreach($educator->getSchool()->getStudentUsers() as $studentUser) {
                    $possibleStudentRegistrations[] = $studentUser->getId();
                }
            }

            foreach($experience->getRegistrations() as $registration) {

                if(!$registration->getUser()) {
                    continue;
                }

                if(in_array($registration->getUser()->getId(), $possibleStudentRegistrations, true)) {
                    return true;
                }

                // only show the event if the context user has registered for it.
                if($registration->getUser()->getId() === $this->userContext->getId()) {
                    return true;
                }
            }

            return false;
        });


        $this->experiences = $experiences->filter(function(Experience $experience) {

            if($this->userContext->isSchoolAdministrator()) {
                return (
                    $experience instanceof StudentToMeetProfessionalExperience ||
                    $experience instanceof TeachLessonExperience ||
                    $experience instanceof SchoolExperience
                );
            }

            if($this->userContext->isEducator()) {
                return (
                    $experience instanceof StudentToMeetProfessionalExperience ||
                    $experience instanceof TeachLessonExperience
                );
            }

            if($this->userContext->isStudent()) {

                return $experience instanceof StudentToMeetProfessionalExperience;
            }

            if($this->userContext->isProfessional()) {
                return (
                    $experience instanceof StudentToMeetProfessionalExperience ||
                    $experience instanceof TeachLessonExperience ||
                    $experience instanceof CompanyExperience
                );
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

            if($this->userContext->isSchoolAdministrator()) {

                /** @var SchoolAdministrator $schoolAdmin */
                $schoolAdmin = $this->userContext;

                foreach($schoolAdmin->getSchools() as $school) {
                    foreach($school->getStudentUsers() as $studentUser) {
                        $possibleStudentRegistrations[] = $studentUser->getId();
                    }
                }
            }

            if($this->userContext->isEducator()) {

                /** @var EducatorUser $educator */
                $educator = $this->userContext;

                foreach($educator->getStudentUsers() as $studentUser) {
                    $possibleStudentRegistrations[] = $studentUser->getId();
                }
            }

            $filteredFeedback = $experience->getFeedback()->filter(function(Feedback $feedback) use($possibleStudentRegistrations) {

                // some of the legacy code still has direct feedback classes attached to events which we aren't using here
                if(get_class($feedback) === Feedback::class) {
                    return false;
                }

                if($feedback->getDeleted()) {
                    return false;
                }

                if(!$feedback->getUser()) {
                    return false;
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

                if($this->userContext->isProfessional()) {

                    return (
                        $feedback instanceof StudentReviewMeetProfessionalExperienceFeedback ||
                        $feedback instanceof StudentReviewTeachLessonExperienceFeedback ||
                        $feedback instanceof EducatorReviewTeachLessonExperienceFeedback ||
                        $feedback instanceof StudentReviewCompanyExperienceFeedback
                    );
                }

                if($feedback->getUser()->getId() === $this->userContext->getId()) {
                    return true;
                }

                if(in_array($feedback->getUser()->getId(), $possibleStudentRegistrations, true)) {
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
            return floor($cumulativeShowUp / $this->totalFeedback()) . '%';
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
            return floor($cumulativeInsight / $totalFeedback * 100) . '%';
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
            return floor($cumulativeEnjoyable / $totalFeedback * 100) . '%';
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
            return floor($learnSomethingNew / $this->totalFeedback() * 100) . '%';
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
            return floor($cumulativeOnTime / $this->totalFeedback());
        }

        return 0;
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
            return floor($cumulativePolite / $this->totalFeedback());
        }

        return 0;

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

            if(!$feedback->getLikelihoodToRecommendToFriend()) {
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
            return floor($cumulativeEngaged / $this->totalFeedback());
        }

        return 0;
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

        $template = sprintf(
            "widget/feedback/table_header/%s/%s.html.twig",
            str_replace(' ', '_', $this->userContext->friendlyRoleName()),
            $experience->getClassName()
        );

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

        $template = sprintf(
            "widget/feedback/table_body/%s/%s.html.twig",
            str_replace(' ', '_', $this->userContext->friendlyRoleName()),
            $experience->getClassName()
        );

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

        $template = sprintf(
            "widget/feedback/data_breakdown/%s/%s.html.twig",
            str_replace(' ', '_', $this->userContext->friendlyRoleName()),
            $experience->getClassName()
        );

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

        $template = "widget/feedback/data_breakdown/aggregate.html.twig";

        $html = $this->twig->render($template, ['feedbackGenerator' => $this]);

        $this->aggregate = false;

        return $html;
    }
}
