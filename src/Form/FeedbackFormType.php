<?php

namespace App\Form;

use App\Entity\AdminUser;
use App\Entity\Feedback;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class FeedbackFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rating', HiddenType::class, [
        ])->add('providedCareerInsight', ChoiceType::class, [
            'choices'  => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'multiple' => false,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-radio'];
            },
        ])->add('wasEnjoyableAndEngaging', ChoiceType::class, [
            'choices'  => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'multiple' => false,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-radio'];
            },
        ])->add('learnSomethingNew', ChoiceType::class, [
            'choices'  => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'multiple' => false,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-radio'];
            },
        ])->add('likelihoodToRecommendToFriend', RangeType::class, [
            'attr'  => [
                'min' => 0,
	            'max' => 10
            ],
        ])->add('additionalFeedback', TextareaType::class, []);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }
}
