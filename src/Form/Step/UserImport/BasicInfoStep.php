<?php

namespace App\Form\Step\UserImport;

use App\Form\Step\DynamicStepInterface;
use App\Form\UserImport\BasicInfoFormType;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class BasicInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step) {

        $config = [
            'label' => 'Basic Info',
            'form_type' => BasicInfoFormType::class,
            'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use($step, $request) {

                $flowTransition = $request->request->get('flow_feedback_transition', null);

                if ($estimatedCurrentStepNumber === 2 && $flowTransition === 'back') {
                    return false;
                }

                return !($estimatedCurrentStepNumber === $step);
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['USER_IMPORT_BASIC_INFO'],
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate() {
        return 'school/user_import/partials/_basic_info.html.twig';
    }

    public function getName() {
        return 'basic_info_step';
    }

    public function getPageTitle() {
        return 'Basic Info';
    }

    public function getPageSlug() {
        return 'basic-info';
    }

    public function onPostValidate() {
        // todo??
    }
}