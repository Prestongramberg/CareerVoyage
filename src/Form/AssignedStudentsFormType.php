<?php

namespace App\Form;

use App\Entity\School;
use App\Entity\StudentUser;
use App\Util\FormHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class AssignedStudentsFormType extends AbstractType
{
    use FormHelper;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var School $school */
        $school = $options['school'];

        $builder->add('assignedStudents', EntityType::class, [
            'class' => StudentUser::class,
            'multiple' => true,
            'expanded' => false,
            'choice_label' => 'fullName',
            'placeholder' => 'Assigned Students',
            'query_builder' => function (EntityRepository $er) use ($school) {
                return $er->createQueryBuilder('s')
                          ->innerJoin('s.school', 'sc')
                          ->where('sc.id = :schoolId')
                          ->setParameter('schoolId', $school->getId())
                          ->orderBy('s.lastName', 'ASC');
            },
            'constraints' => [new Count(['min' => 1,
                                         'minMessage' => 'Please choose at least one student',
                                         'groups' => ['DEFAULT'],
            ]),
            ]
        ]);

        $builder->add('strategy', ChoiceType::class, [
            'choices' => [
                'Replace' => 'replace',
                'Add' => 'merge',
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
