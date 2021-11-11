<?php

namespace App\Form;

use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('eventType', ChoiceType::class, [
            'choices' => Feedback::$eventTypes,
            'placeholder' => 'Please select the event type',
            'required' => false,
            'expanded' => false,
            'multiple' => false,
        ]);

        /*$builder->add('rating', HiddenType::class, [
            'error_bubbling' => false,
        ])->add('providedCareerInsight', HiddenType::class, ['empty_data' => false])
                ->add('wasEnjoyableAndEngaging', HiddenType::class, ['empty_data' => false])
                ->add('learnSomethingNew', HiddenType::class, ["empty_data" => false])
                ->add('likelihoodToRecommendToFriend', HiddenType::class, [])
                ->add('additionalFeedback', TextareaType::class, [])
                ->add('deleted', HiddenType::class, ["empty_data" => false]);*/
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
