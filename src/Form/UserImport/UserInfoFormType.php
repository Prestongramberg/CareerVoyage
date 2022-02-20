<?php

namespace App\Form\UserImport;

use App\Entity\RolesWillingToFulfill;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotNull;

class UserInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getBlockPrefix()
    {
        return 'userInfo';
    }

}
