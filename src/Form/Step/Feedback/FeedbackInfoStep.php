<?php

namespace App\Form\Step\Feedback;

use App\Form\Feedback\BasicInfoFormType;
use App\Form\Feedback\FeedbackInfoFormType;
use App\Form\Step\DynamicStepInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedbackInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step)
    {
        $config = [
            'label'        => 'Feedback Info',
            'form_type'    => FeedbackInfoFormType::class,
            'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use ($step, $request) {

                /** @var \App\Entity\Feedback $feedback */
                $feedback = $flow->getFormData();

                if ($estimatedCurrentStepNumber === 3 && in_array($feedback->getFeedbackProvider(), ['Student', 'Educator'], true)) {
                    return false;
                }

                $flowTransition = $request->request->get('flow_feedback_transition', null);

           /*     if ($estimatedCurrentStepNumber === 2 && $feedback->getFeedbackProvider() === 'Professional') {
                    return false;
                }

                if ($estimatedCurrentStepNumber === 3 && $feedback->getFeedbackProvider() === 'Professional') {
                    return false;
                }

                return true;*/

                return !($estimatedCurrentStepNumber === $step);
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['Default'],
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate()
    {
        return 'feedback/v2/partials/_feedback_info.html.twig';
    }

    public function getName()
    {
        return 'feedback_info_step';
    }

    public function getPageTitle()
    {
        return 'Feedback Info';
    }

    public function getPageSlug()
    {
        return 'feedback-info';
    }

    public function onPostValidate()
    {
        // todo??
    }

}