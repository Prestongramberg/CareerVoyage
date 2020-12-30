<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\State;
use App\Entity\StateCoordinator;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

class SchoolAdminFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $site = $options['site'];

        /** @var User $user */
        $user = $options['user'];

        $builder->add('firstName', TextType::class)
                ->add('lastName', TextType::class)
                ->add('email', EmailType::class)
                ->add(
                    'schools', EntityType::class, [
                    'class'         => School::class,
                    'choice_label'  => 'name',
                    'expanded'      => true,
                    'multiple'      => true,
                    'choice_attr'   => function ($choice, $key, $value) {
                        return ['class' => 'uk-checkbox'];
                    },
                    'query_builder' => function (EntityRepository $er) use ($site, $user) {

                        if ($user->isRegionalCoordinator()) {

                            /** @var RegionalCoordinator $user $ids */
                            $regionIds = [$user->getRegion()->getId()];

                            return $er->createQueryBuilder('s')
                                      ->where('s.site = :site')
                                      ->andWhere('s.region IN (:ids)')
                                      ->setParameter('site', $site)
                                      ->setParameter('ids', $regionIds)
                                      ->orderBy('s.name', 'ASC');
                        } else {
                            // This should probably never happen as I'm pretty sure only regional coordinators can create school admins but just incase
                            return $er->createQueryBuilder('s')
                                      ->where('s.site = :site')
                                      ->setParameter('site', $site)
                                      ->orderBy('s.name', 'ASC');
                        }
                    },
                ]
                );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => SchoolAdministrator::class,
                'validation_groups' => ["INCOMPLETE_USER"],
            ]
        );

        $resolver->setRequired(
            [
                'site',
                'user',
            ]
        );
    }
}
