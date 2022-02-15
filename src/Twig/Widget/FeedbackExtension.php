<?php

namespace App\Twig\Widget;

use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\User;
use App\Repository\ExperienceRepository;
use App\Repository\FeedbackRepository;
use App\Repository\SchoolExperienceRepository;
use App\Util\FeedbackGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;

/**
 * Class FeedbackExtension
 *
 * @package App\Twig\Widget
 */
class FeedbackExtension extends AbstractExtension
{

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ExperienceRepository $experienceRepository
     */
    private $experienceRepository;

    /**
     * @var FeedbackRepository $feedbackRepository
     */
    private $feedbackRepository;

    /**
     * @var SchoolExperienceRepository
     */
    private $schoolExperienceRepository;

    /**
     * @param  \Twig\Environment                           $twig
     * @param  \App\Repository\ExperienceRepository        $experienceRepository
     * @param  \App\Repository\FeedbackRepository          $feedbackRepository
     * @param  \App\Repository\SchoolExperienceRepository  $schoolExperienceRepository
     */
    public function __construct(Environment $twig, ExperienceRepository $experienceRepository, FeedbackRepository $feedbackRepository, SchoolExperienceRepository $schoolExperienceRepository)
    {
        $this->twig                       = $twig;
        $this->experienceRepository       = $experienceRepository;
        $this->feedbackRepository         = $feedbackRepository;
        $this->schoolExperienceRepository = $schoolExperienceRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('feedback_widget_v2', [$this, 'feedbackWidgetV2']),
        ];
    }

    /**
     * @param  array             $experiences
     * @param  \App\Entity\User  $userContext
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function feedbackWidgetV2(array $experiences, User $userContext)
    {
        // todo pass in logged in user so you can do filtering off of it?
        $feedbackGenerator = new FeedbackGenerator($experiences, $userContext, $this->twig);

        $template = 'widget/feedback/feedback.html.twig';

        return $this->twig->render($template, ['feedbackGenerator' => $feedbackGenerator]);
    }

}
