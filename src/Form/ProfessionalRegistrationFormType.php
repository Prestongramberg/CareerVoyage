<?php

namespace App\Form;

use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ProfessionalRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('username')
            ->add('plainPassword', PasswordType::class, [])
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Select a primary industry..'
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'I know, it\'s silly, but you must agree to our terms.',
                        'groups'  => ['CREATE']
                    ])
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();
            if(!$data->getPrimaryIndustry()) {
                return;
            }
            $this->modifyForm($event->getForm(), $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            if(!$industry) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });
    }

    private function modifyForm(FormInterface $form, Industry $industry) {

        $form->add('secondaryIndustries', EntityType::class, [
            'class' => SecondaryIndustry::class,
            'query_builder' => function (EntityRepository $er) use ($industry) {
                return $er->createQueryBuilder('si')
                    ->where('si.primaryIndustry = :primaryIndustry')
                    ->setParameter('primaryIndustry', $industry->getId());
            },
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfessionalUser::class,
            'validation_groups' => function (FormInterface $form) {

                $skipValidation = $form->getConfig()->getOption('skip_validation');

                if($skipValidation) {
                    return [];
                }

                /** @var ProfessionalUser $data */
                $data = $form->getData();
                if(!$data->getPrimaryIndustry()) {
                    return ['CREATE'];
                }

                if($data->getPrimaryIndustry()) {
                    return ['CREATE', 'SECONDARY_INDUSTRY'];
                }

                return ['CREATE'];
            },
        ]);

        $resolver->setRequired(['skip_validation']);
    }
}
