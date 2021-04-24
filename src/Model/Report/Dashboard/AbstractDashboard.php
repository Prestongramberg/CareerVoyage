<?php

namespace App\Model\Report\Dashboard;

use App\Model\Report\Dashboard\Feedback\BarChart\LikelihoodToRecommendAFriend;
use App\Model\Report\Dashboard\Feedback\BarChart\PromoterNeutralDetractor;
use App\Model\Report\Dashboard\Feedback\PieChart\ExperienceEnjoyableAndEngaging;
use App\Model\Report\Dashboard\Feedback\BarChart\ExperienceRating;
use App\Model\Report\Dashboard\Feedback\PieChart\LearnedSomethingNew;
use App\Model\Report\Dashboard\Feedback\PieChart\ProvidedCareerInsight;
use App\Model\Report\Dashboard\Feedback\BarChart\StudentInterestInWorkingForCompany;
use App\Model\Report\Dashboard\Feedback\NpsScore;
use App\Model\Report\Dashboard\Feedback\Summary;

abstract class AbstractDashboard
{
    const PAGE_FEEDBACK            = 'PAGE_FEEDBACK';
    const PAGE_FEEDBACK_POSITION_1 = 'PAGE_FEEDBACK_POSITION_1';
    const PAGE_FEEDBACK_POSITION_2 = 'PAGE_FEEDBACK_POSITION_2';

    const DASHBOARD_EXPERIENCE_RATING                       = ExperienceRating::class;
    const DASHBOARD_STUDENT_INTEREST_IN_WORKING_FOR_COMPANY = StudentInterestInWorkingForCompany::class;
    const DASHBOARD_SUMMARY                                 = Summary::class;
    const DASHBOARD_NPS_SCORE                               = NpsScore::class;
    const DASHBOARD_EXPERIENCE_ENJOYABLE_AND_ENGAGING       = ExperienceEnjoyableAndEngaging::class;
    const DASHBOARD_LEARNED_SOMETHING_NEW                   = LearnedSomethingNew::class;
    const DASHBOARD_PROVIDED_CAREER_INSIGHT                 = ProvidedCareerInsight::class;
    const DASHBOARD_LIKELIHOOD_TO_RECOMMEND_A_FRIEND        = LikelihoodToRecommendAFriend::class;
    const DASHBOARD_PROMOTER_NEEUTRAL_DETRACTOR             = PromoterNeutralDetractor::class;

    abstract public function getHeader();

    abstract public function getSubheader();

    abstract public function getFooter();

    abstract public function render();

    abstract public function getTemplate();

    abstract public function getLocation();

    abstract public function getPosition();

    abstract public function setPosition($position);

    public function getName()
    {
        return (new \ReflectionClass($this))->getName();
    }

}