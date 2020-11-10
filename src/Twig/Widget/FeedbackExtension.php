<?php

namespace App\Twig\Widget;

use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\School;
use App\Entity\User;
use App\Repository\ExperienceRepository;
use App\Repository\FeedbackRepository;
use App\Util\FeedbackGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;

/**
 * Class FeedbackExtension
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
     * FeedbackExtension constructor.
     * @param Environment $twig
     * @param ExperienceRepository $experienceRepository
     * @param FeedbackRepository $feedbackRepository
     */
    public function __construct(
        Environment $twig,
        ExperienceRepository $experienceRepository,
        FeedbackRepository $feedbackRepository
    ) {
        $this->twig = $twig;
        $this->experienceRepository = $experienceRepository;
        $this->feedbackRepository = $feedbackRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('feedback_widget', [$this, 'feedbackWidget'])
        ];
    }

    /**
     * @param User|null $user
     * @param Experience|null $experience
     * @param School|null $school
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function feedbackWidget(User $user, Experience $experience = null, School $school = null)
    {
        if($school) {
            $experiences = $this->experienceRepository->fetchEntitiesBySchool($school);
        } else {
            $experiences = $experience ? [$experience] : $this->experienceRepository->findAll();
        }

        if(empty($experiences)) {
            return $this->twig->render('widget/feedback/not_found.html.twig', ['user' => $user]);
        }

        // todo pass in logged in user so you can do filtering off of it?
        $feedbackGenerator = new FeedbackGenerator($experiences, $user, $this->twig);

        $template = sprintf(
            'widget/feedback/%s/feedback.html.twig',
            str_replace(' ', '_', $user->friendlyRoleName()
            )
        );

        if($this->twig->getLoader()->exists($template)) {
            return $this->twig->render($template, ['feedbackGenerator' => $feedbackGenerator,]);
        }

        return $this->twig->render('widget/feedback/not_found.html.twig', ['user' => $user]);
    }
}
