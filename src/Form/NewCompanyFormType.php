<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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

class NewCompanyFormType extends AbstractType
{
    /**
     * @var SecondaryIndustryRepository $secondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * NewCompanyFormType constructor.
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(SecondaryIndustryRepository $secondaryIndustryRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('name', TextType::class, [])
            ->add('website', TextType::class, [])
            ->add('phone', TextType::class)
            ->add('emailAddress', TextType::class)
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Select a Primary Industry'
            ])
            ->add('shortDescription', TextareaType::class, [])
            ->add('description', TextareaType::class)
            ->add('thumbnailImage', FileType::class, [
                'label' => 'Thumbnail image',
                'constraints' => $this->thumbnailImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
            ])
            ->add('featuredImage', FileType::class, [
                'label' => 'Featured image',
                'constraints' => $this->featuredImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
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
            'data_class' => Company::class,

        ]);

        $resolver->setRequired('company');
    }

    /**
     * @param Company $company
     * @return array
     */
    private function thumbnailImageConstraints($company) {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M',
                'groups'  => ['CREATE']
            ])
        ];

        if (!$company->getThumbnailImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a thumbnail image',
                'groups'  => ['CREATE']
            ]);
        }

        return $imageConstraints;
    }


    /**
     * @param Company $company
     * @return array
     */
    private function featuredImageConstraints($company) {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M',
                'groups'  => ['EDIT']
            ])
        ];

        if (!$company->getFeaturedImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a featured image',
                'groups'  => ['EDIT']
            ]);
        }

        return $imageConstraints;
    }
}
