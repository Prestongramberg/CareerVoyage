<?php

namespace App\Form\Request;

use App\Entity\AdminUser;
use App\Entity\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SelectSuggestedDatesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Request $request */
        $request = $options['request'];

        $notification = $request->getNotification();

        $setDates = (
            !empty($notification['suggested_dates']['date_option_one']) &&
            !empty($notification['suggested_dates']['date_option_two']) &&
            !empty($notification['suggested_dates']['date_option_three'])
        );

        if(!$setDates) {
            return;
        }

        $suggestedDates = $notification['suggested_dates'];

        $builder->add('dateOptionOne', SubmitType::class, [
            'label' => $suggestedDates['date_option_one'],
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);

        $builder->add('dateOptionTwo', SubmitType::class, [
            'label' => $suggestedDates['date_option_two'],
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);

        $builder->add('dateOptionThree', SubmitType::class, [
            'label' => $suggestedDates['date_option_three'],
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['request']);
    }
}
