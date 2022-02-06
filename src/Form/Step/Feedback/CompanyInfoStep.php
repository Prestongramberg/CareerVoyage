<?php

namespace App\Form\Step\Feedback;

use App\Form\Feedback\BasicInfoFormType;
use App\Form\Feedback\CompanyInfoFormType;
use App\Form\Feedback\FeedbackInfoFormType;
use App\Form\Feedback\SchoolInfoFormType;
use App\Form\Step\DynamicStepInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class CompanyInfoStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step) {

        $config = [
            'label' => 'Company Info',
            'form_type' => CompanyInfoFormType::class,
            'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use($step, $request) {
                /** @var \App\Entity\Feedback $feedback */
                $feedback = $flow->getFormData();

                $flowTransition = $request->request->get('flow_feedback_transition', null);

                if($estimatedCurrentStepNumber === 2 && $feedback->getFeedbackProvider() === 'Professional') {
                    return false;
                }

                return !($estimatedCurrentStepNumber === $step && $feedback->getFeedbackProvider() === 'Professional');
            },
            'form_options' => [
                'validation_groups' => $request->request->has('changeableField') ? [] : ['Default'],
            ],
        ];

        return parent::createFromConfig($step, $config);
    }

    public function getTemplate() {
        return 'feedback/v2/partials/_company_info.html.twig';
    }

    public function getName() {
        return 'company_info_step';
    }

    public function getPageTitle() {
        return 'Company Info';
    }

    public function getPageSlug() {
        return 'company-info';
    }

    public function onPostValidate() {
        // todo??
    }
}