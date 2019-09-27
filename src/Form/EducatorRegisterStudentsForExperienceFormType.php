<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\StudentUserRepository;
use App\Util\FormHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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

class EducatorRegisterStudentsForExperienceFormType extends AbstractType
{
    use FormHelper;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * EducatorRegisterStudentsForExperienceFormType constructor.
     * @param StudentUserRepository $studentUserRepository
     */
    public function __construct(StudentUserRepository $studentUserRepository)
    {
        $this->studentUserRepository = $studentUserRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EducatorUser $educator */
        $educator = $options['educator'];
        $builder->add('studentUsers', EntityType::class, [
            'class' => StudentUser::class,
            'multiple' => true,
            'expanded' => true,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
            'choice_label' => function (StudentUser $student) {
                return $student->getFullName();
            },
            'query_builder' => function (EntityRepository $er) use ($educator) {
                return $er->createQueryBuilder('s')
                    ->innerJoin('s.educatorUsers','eu')
                    ->where('eu.id = :educatorUser')
                    ->setParameter('educatorUser', $educator->getId())
                    ->orderBy('s.firstName', 'ASC');
            }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['educator']);
    }
}
