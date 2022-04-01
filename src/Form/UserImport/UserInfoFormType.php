<?php

namespace App\Form\UserImport;

use App\Entity\UserImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var UserImport $userImport */
        $userImport = $builder->getData();

        // do nothing as we handle this page with react on the frontend
    }

    public function getBlockPrefix()
    {
        return 'userInfo';
    }

}
