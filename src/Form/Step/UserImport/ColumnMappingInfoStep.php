<?php

namespace App\Form\Step\UserImport;

use App\Entity\UserImport;
use App\Form\Step\DynamicStepInterface;
use App\Form\UserImport\ColumnMappingInfoFormType;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class ColumnMappingInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step) {

        $config = [
            'label' => 'Column Mapping Info',
            'form_type' => ColumnMappingInfoFormType::class,
            'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use($step, $request) {

                /** @var UserImport $userImport */
                $userImport = $flow->getFormData();

                $flowTransition = $request->request->get('flow_feedback_transition', null);

              /*  if($estimatedCurrentStepNumber === 2 && $feedback->getFeedbackProvider() === 'Professional') {
                    return false;
                }*/

                /*return !($estimatedCurrentStepNumber === $step && $feedback->getFeedbackProvider() === 'Professional');*/

                return !($estimatedCurrentStepNumber === $step);
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['USER_IMPORT_COLUMN_MAPPING_INFO'],
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate() {
        return 'school/user_import/partials/_column_mapping_info.html.twig';
    }

    public function getName() {
        return 'column_mapping_info_step';
    }

    public function getPageTitle() {
        return 'Column Mapping Info';
    }

    public function getPageSlug() {
        return 'column-mapping-info';
    }

    public function onPostValidate() {
        // todo??
    }
}