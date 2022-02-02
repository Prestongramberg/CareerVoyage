<?php

namespace App\Form\Step\Feedback;

use App\Form\Feedback\BasicInfoFormType;
use App\Form\Step\DynamicStepInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class BasicInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step) {

        $config = [
            'label' => 'Basic Info',
            'form_type' => BasicInfoFormType::class,
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
        return 'security/partials/_join_nscs_form.html.twig';
    }

    public function getName() {
        return 'join_nscs_step';
    }

    public function getPageTitle() {
        return 'Get Started';
    }

    public function getPageSlug() {
        return 'get-started';
    }

    public function onPostValidate() {
        // todo??
    }
}