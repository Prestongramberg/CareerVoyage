<?php

namespace App\Form\Feedback;

use App\Entity\CompanyExperience;
use App\Entity\SchoolExperience;
use App\Entity\TeachLessonExperience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotNull;

class FeedbackInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Entity\Feedback $feedback */
        $feedback = $builder->getData();

        if ($feedback->getExperience() instanceof SchoolExperience) {
            if ($feedback->getFeedbackProvider() === 'Student') {
                $builder->add('likelihoodToRecommendToFriend', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'How likely are you to recommend this school experience to a fellow student?',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('additionalFeedback', TextareaType::class, [
                    'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
                ]);
            }

            if ($feedback->getFeedbackProvider() === 'Educator') {
                $builder->add('likelihoodToRecommendToFriend', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'How likely are you to recommend this experience to your students?',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('additionalFeedback', TextareaType::class, [
                    'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
                ]);
            }

            if ($feedback->getFeedbackProvider() === 'Professional') {
                $builder->add('likelihoodToRecommendToFriend', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'How likely are you to recommend this school experience to a fellow professional?',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('additionalFeedback', TextareaType::class, [
                    'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
                ]);
            }
        }

        if ($feedback->getExperience() instanceof CompanyExperience) {
            if ($feedback->getFeedbackProvider() === 'Student') {
                $builder->add('interestWorkingForCompany', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'After this experience, my awareness of career opportunities at this company is',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                        new GreaterThan(['value' => 0, 'message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('likelihoodToRecommendToFriend', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'How likely are you to recommend this company experience to a fellow student?',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('additionalFeedback', TextareaType::class, [
                    'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
                ]);
            }

            if ($feedback->getFeedbackProvider() === 'Educator') {
                $builder->add('awarenessCareerOpportunities', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'After this experience, my awareness of career opportunities for students at this company is',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                        new GreaterThan(['value' => 0, 'message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('likelihoodToRecommendToFriend', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'How likely are you to recommend this experience to your students?',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('additionalFeedback', TextareaType::class, [
                    'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
                ]);
            }

            if ($feedback->getFeedbackProvider() === 'Professional') {
                $builder->add('likelihoodToRecommendToFriend', HiddenType::class, [
                    'error_bubbling' => false,
                    'label'          => 'How likely are you to recommend this school experience to a fellow professional?',
                    'constraints'    => [
                        new NotNull(['message' => 'Please choose a valid option.']),
                    ],
                ]);

                $builder->add('additionalFeedback', TextareaType::class, [
                    'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
                ]);
            }
        }

        if ($feedback->getExperience() instanceof TeachLessonExperience) {
            $builder->add('additionalFeedback', TextareaType::class, [
                'label' => 'Any specific thoughts, feedback to improve this experience? (Optional)',
            ]);
        }

        $builder->add('rating', HiddenType::class, [
            'error_bubbling' => false,
            'constraints'    => [
                new NotNull(['message' => 'Please choose a valid option.']),
            ],
        ]);

        if ($feedback->getFeedbackProvider() === 'Professional') {
            $builder->add('wasEnjoyableAndEngaging', HiddenType::class, [
                'empty_data'     => false,
                'error_bubbling' => false,
            ]);
        } else {
            $builder->add('providedCareerInsight', HiddenType::class, [
                'empty_data'     => false,
                'error_bubbling' => false,
            ]);

            $builder->add('wasEnjoyableAndEngaging', HiddenType::class, [
                'empty_data'     => false,
                'error_bubbling' => false,
            ]);

            $builder->add('learnSomethingNew', HiddenType::class, [
                "empty_data"     => false,
                'error_bubbling' => false,
            ]);
        }

    }

    public function getBlockPrefix()
    {
        return 'feedbackInfo';
    }

}
