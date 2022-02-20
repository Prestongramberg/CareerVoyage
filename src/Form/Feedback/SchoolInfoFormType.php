<?php

namespace App\Form\Feedback;

use App\Entity\CompanyExperience;
use App\Entity\Feedback;
use App\Entity\SchoolExperience;
use App\Repository\SchoolRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SchoolInfoFormType extends AbstractType implements DataMapperInterface
{

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @param  SchoolRepository  $schoolRepository
     */
    public function __construct(SchoolRepository $schoolRepository)
    {
        $this->schoolRepository = $schoolRepository;
    }

    public function mapDataToForms($viewData, $forms): void
    {
        /** @var SchoolExperience|CompanyExperience $viewData */

        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Feedback) {
            throw new UnexpectedTypeException($viewData, Feedback::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if(isset($forms['userSchool'])) {
            if($viewData->getUserSchoolOther()) {
                $forms['userSchool']->setData('Other');
            } else {
                $forms['userSchool']->setData($viewData->getUserSchool() ? $viewData->getUserSchool()->getId() : null);
            }
        }

        if(isset($forms['userSchoolOther'])) {
            $forms['userSchoolOther']->setData($viewData->getUserSchoolOther());
        }
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var Feedback $viewData */

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if(isset($forms['userSchool'])) {

            if($forms['userSchool']->getData() === 'Other' || empty($forms['userSchool']->getData())) {
                $viewData->setUserSchool(null);
            } elseif ($forms['userSchool']->getData()) {

                $school = $this->schoolRepository->find($forms['userSchool']->getData());

                if($school) {
                    $viewData->setUserSchool($school);
                }
            }
        }

        if(isset($forms['userSchoolOther'])) {
            $viewData->setUserSchoolOther($forms['userSchoolOther']->getData());
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);

        /** @var \App\Entity\Feedback $feedback */
        $feedback = $builder->getData();

        $schools = $this->schoolRepository->findBy([], [
            'name' => 'ASC',
        ]);

        $schoolsArray = [];

        foreach ($schools as $school) {
            $schoolsArray[$school->getName()] = $school->getId();
        }

        $schoolsArray['Other'] = 'Other';

        $builder->add('userSchool', ChoiceType::class, [
            'required'    => false,
            'placeholder' => 'Please Select',
            'multiple'    => false,
            'expanded'    => false,
            'choices'     => $schoolsArray,
            'mapped'      => false,
        ]);

        $builder->get('userSchool')
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                    $userSchool = $event->getForm()
                                        ->getData();
                    $form       = $event->getForm()
                                        ->getParent();

                    if (!$form) {
                        return;
                    }

                    if (!$userSchool) {
                        return;
                    }

                    if ($userSchool === 'Other') {
                        if (!$form->has('userSchoolOther')) {
                            $form->add('userSchoolOther', TextType::class, [
                                'attr' => [
                                    'placeholder' => 'School Name',
                                ],
                            ]);
                        }
                    } else {
                        if ($form->has('userSchoolOther')) {
                            $form->remove('userSchoolOther');
                        }
                    }
                });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var Feedback $data */
            $data = $event->getData();
            $form = $event->getForm();

            if($data->getUserSchoolOther()) {
                $form->add('userSchoolOther', TextType::class, [
                    'attr' => [
                        'placeholder' => 'School Name',
                    ],
                ]);
            }

        });

    }

    public function getBlockPrefix()
    {
        return 'schoolInfo';
    }

}
