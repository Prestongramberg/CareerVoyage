<?php

namespace App\Form\Step\UserImport;

use App\Entity\UserImport;
use App\Form\Step\DynamicStepInterface;
use App\Form\UserImport\FileInfoFormType;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class FileInfoStep extends Step implements DynamicStepInterface
{
    public static function create(Request $request, $step) {

        $config = [
            'label' => 'File Info',
            'form_type' => FileInfoFormType::class,
            'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use($step, $request) {

                /** @var UserImport $userImport */
                $userImport = $flow->getFormData();

                $flowTransition = $request->request->get('flow_userImport_transition', null);

                if($userImport->getSkipColumnMappingStep() && $estimatedCurrentStepNumber === 2 && $flowTransition === 'back') {
                    return false;
                }

                return !($estimatedCurrentStepNumber === $step);
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['USER_IMPORT_FILE_INFO'],
                'allow_extra_fields' => true
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate() {
        return 'school/user_import/partials/_file_info.html.twig';
    }

    public function getName() {
        return 'file_info_step';
    }

    public function getPageTitle() {
        return 'File Info';
    }

    public function getPageSlug() {
        return 'file-info';
    }

    public function onPostValidate() {
        // todo??
    }
}