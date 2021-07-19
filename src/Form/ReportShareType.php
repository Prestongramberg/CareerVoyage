<?php

namespace App\Form;

use App\Entity\Region;
use App\Entity\ReportShare;
use App\Entity\School;
use App\Entity\User;
use App\Form\EventType\EventUserChoiceType;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportShareType
 *
 * @package App\Form\Property
 */
class ReportShareType extends AbstractType
{

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ReportShareType constructor.
     *
     * @param SchoolRepository $schoolRepository
     * @param UserRepository   $userRepository
     */
    public function __construct(SchoolRepository $schoolRepository, UserRepository $userRepository)
    {
        $this->schoolRepository = $schoolRepository;
        $this->userRepository   = $userRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('userRole', ChoiceType::class, [
            'expanded' => false,
            'multiple' => false,
            'choices' => User::$reportPermissionUserRoleChoices,
            'required' => false,
        ]);

        $builder->add('regions', EntityType::class, [
            'class' => Region::class,
            'expanded' => false,
            'multiple' => true,
            'choice_label' => 'friendlyName',
        ]);

        $builder->add('schools', EntityType::class, [
            'class' => School::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => false,
        ]);

        $builder->add('users', EventUserChoiceType::class, [
            'expanded' => false,
            'multiple' => true,
            'choice_value' => function (?User $entity) {
                return $entity ? $entity->getId() : '';
            },
            'choice_label' => 'fullName',
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var ReportShare $data */
            $data = $event->getData();

            if (!$data) {
                return;
            }

            if (!$form = $event->getForm()) {
                return;
            }

            $choices = [];
            foreach ($data->getUsers() as $user) {
                $choices[] = $user;
            }

            if(empty($choices)) {
                return;
            }

            if($form->has('users')) {
                $form->remove('users');
            }

            $form->add('users', EventUserChoiceType::class, [
                'expanded' => false,
                'multiple' => true,
                'choices' => $choices,
                'choice_value' => function (?User $entity) {
                    return $entity ? $entity->getId() : '';
                },
                'choice_label' => 'fullName',
            ]);

        });

        $builder->get('regions')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $regions = $event->getForm()->getData();

            if (empty($regions) || $regions->count() === 0) {
                return;
            }

            $form = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if ($form->has('schools')) {
                $form->remove('schools');
            }

            $regions = $regions->toArray();

            $regionIds = array_map(function (Region $region) {
                return $region->getId();
            }, $regions);


            if (!empty($regionIds)) {
                $schools = $this->schoolRepository->findBy([
                    'region' => $regionIds,
                ], ['name' => 'ASC']);
            } else {
                $schools = $this->schoolRepository->findBy([], ['name' => 'ASC']);
            }

            $form->add('schools', EntityType::class, [
                'class' => School::class,
                'choices' => $schools,
                'choice_label' => 'name',
                'placeholder' => 'Schools',
                'multiple' => true,
                'expanded' => false,
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
            ]);

        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (empty($data['users'])) {
                return;
            }

            $userIds = $data['users'];

            $choices = $this->userRepository->findBy([
                'id' => $userIds,
            ]);

            if ($form->has('users')) {
                $form->remove('users');
            }

            $form->add('users', EventUserChoiceType::class, [
                'expanded' => false,
                'multiple' => true,
                'choices' => $choices,
                'choice_value' => function (?User $entity) {
                    return $entity ? $entity->getId() : '';
                },
                'choice_label' => 'fullName',
            ]);

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReportShare::class,
        ])->setRequired([]);
    }
}