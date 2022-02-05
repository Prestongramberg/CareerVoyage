<?php

namespace App\Form\Step\Feedback;

use App\Form\Feedback\BasicInfoFormType;
use App\Form\Feedback\FeedbackInfoFormType;
use App\Form\Step\DynamicStepInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class FeedbackInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step) {

        $config = [
            'label' => 'Feedback Info',
            'form_type' => FeedbackInfoFormType::class,
            'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use($step) {

                return !($estimatedCurrentStepNumber === $step);
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['Default'],
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate() {
        return 'feedback/v2/partials/_feedback_info.html.twig';
    }

    public function getName() {
        return 'feedback_info_step';
    }

    public function getPageTitle() {
        return 'Feedback Info';
    }

    public function getPageSlug() {
        return 'feedback-info';
    }

    public function onPostValidate() {
        // todo??
    }
}