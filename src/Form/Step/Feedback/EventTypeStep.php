<?php

namespace App\Form\Step\Feedback;

use App\Entity\Feedback;
use App\Form\Feedback\EventTypeForm;
use App\Form\Step\DynamicStepInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Craue\FormFlowBundle\Form\Step;
use Symfony\Component\HttpFoundation\Request;

class EventTypeStep extends Step implements DynamicStepInterface
{

    public static function create(Request $request, $step) {

        $config = [
            'label' => 'Event Type',
            'form_type' => EventTypeForm::class,
            'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) use($step) {

                /** @var Feedback $feedback */
                $feedback = $flow->getFormData();

                /*if($signUp->hasQueryParameter('vid')) {
                    return true;
                }*/

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
        return 'feedback_event_type_step';
    }
}