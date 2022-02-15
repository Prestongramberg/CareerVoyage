<?php

namespace App\Form\Step\Feedback;

use App\Form\Feedback\BasicInfoFormType;
use App\Form\Feedback\FeedbackInfoFormType;
use App\Form\Feedback\SchoolInfoFormType;
use App\Form\Step\DynamicStepInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class SchoolInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step)
    {
        $config = [
            'label'        => 'School Info',
            'form_type'    => SchoolInfoFormType::class,
            'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use ($step, $request) {
                /** @var \App\Entity\Feedback $feedback */
                $feedback = $flow->getFormData();

                $flowTransition = $request->request->get('flow_feedback_transition', null);

                if ($estimatedCurrentStepNumber === 2 && in_array($feedback->getFeedbackProvider(), ['Student', 'Educator'], true)) {
                    return false;
                }

                if ($flowTransition === 'back' && $estimatedCurrentStepNumber === 3 && in_array($feedback->getFeedbackProvider(), ['Student', 'Educator'], true)) {
                    return false;
                }

                return !($estimatedCurrentStepNumber === $step && in_array($feedback->getFeedbackProvider(), ['Student', 'Educator'], true));
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['Default'],
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate()
    {
        return 'feedback/v2/partials/_school_info.html.twig';
    }

    public function getName()
    {
        return 'school_info_step';
    }

    public function getPageTitle()
    {
        return 'School Info';
    }

    public function getPageSlug()
    {
        return 'school-info';
    }

    public function onPostValidate()
    {
        // todo??
    }

}