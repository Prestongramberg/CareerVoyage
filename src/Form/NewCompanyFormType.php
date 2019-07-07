<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class NewCompanyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('address', TextType::class, [])
            ->add('briefCompanyDescription', TextareaType::class)
            ->add('primaryContact', TextType::class)
            ->add('companyLinkedinPage', TextType::class)
            ->add('phone', TextType::class)
            ->add('logo', FileType::class, [
                'label' => 'Logo',
                'constraints' => $this->logoImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'validation_groups' => ['CREATE'],
        ]);

        $resolver->setRequired('company');

    }

    /**
     * @param Company $company
     * @return array
     */
    private function logoImageConstraints($company) {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M',
                'groups'  => ['CREATE']
            ])
        ];

        if (!$company->getLogo()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a logo',
                'groups'  => ['CREATE']
            ]);
        }

        return $imageConstraints;
    }
}
