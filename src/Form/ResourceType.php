<?php

namespace App\Form;

use App\Entity\Resource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, [
            'constraints' => [new NotBlank(['message' => 'Please enter a title for your resource',
                                            'groups' => ['RESOURCE'],
            ]),
            ],
        ])->add('description', TextareaType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a description for your resource',
                              'groups' => ['RESOURCE'],
                ]),
            ],
        ])->add('type', ChoiceType::class, [
            'placeholder' => 'Select a resource type',
            'constraints' => [
                new NotBlank(['message' => 'Please select a type for your resource',
                              'groups' => ['RESOURCE'],
                ]),
            ],
            'choices' => [
                'URL' => Resource::TYPE_URL,
                'File' => Resource::TYPE_FILE,
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var Resource $data */
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data->getType()) {
                return;
            }

            $this->modifyForm($form, $data->getType());
        });

        $builder->get('type')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $type = $event->getForm()->getData();

            if (!$type) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $type);
        });


    }

    private function modifyForm(FormInterface $form, $type)
    {

        if($form->has('file')) {
            $form->remove('file');
        }

        if($form->has('url')) {
            $form->remove('url');
        }

        if ($type === Resource::TYPE_FILE) {
            $form->add('file', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => false,
                'constraints' => [
                    new NotNull(['message' => 'Please select a file for your resource',
                                 'groups' => ['RESOURCE'],
                    ]),
                ]
            ]);
        }

        if ($type === Resource::TYPE_URL) {
            $form->add('url', TextType::class, [
                'attr' => [
                    'placeholder' => 'http://baseproshop.com/files/employee-handbook.pdf',
                ],
                'constraints' => [new NotNull(['message' => 'Please enter a URL for your resource',
                                               'groups' => ['RESOURCE'],
                ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}