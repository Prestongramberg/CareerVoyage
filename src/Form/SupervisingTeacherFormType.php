<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\School;
use App\Util\FormHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotNull;

class SupervisingTeacherFormType extends AbstractType
{
    use FormHelper;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var School $school */
        $school = $options['school'];

        $builder->add('supervisingTeachers', EntityType::class, [
            'class' => EducatorUser::class,
            'multiple' => true,
            'expanded' => false,
            'choice_label' => 'fullName',
            'placeholder' => 'Supervising Teacher',
            'query_builder' => function (EntityRepository $er) use ($school) {
                return $er->createQueryBuilder('e')
                          ->innerJoin('e.school', 's')
                          ->where('s.id = :schoolId')
                          ->setParameter('schoolId', $school->getId())
                          ->orderBy('e.firstName', 'ASC');
            },
            'constraints' => [new Count(['min' => 1,
                                         'minMessage' => 'Please choose at least one supervising teacher',
                                         'groups' => ['DEFAULT'],
            ]),
            ]
        ]);

        $builder->add('strategy', ChoiceType::class, [
            'choices' => [
                'Replace' => 'replace',
                'Merge' => 'merge',
            ],
            'data' => 'replace',
            'expanded' => true,
            'multiple' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['school'])
                 ->setDefaults([
                     'validation_groups' => ['DEFAULT'],
                 ]);
    }
}
