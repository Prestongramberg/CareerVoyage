<?php

namespace App\Form\Feedback;

use App\Entity\CompanyExperience;
use App\Entity\Feedback;
use App\Entity\SchoolExperience;
use App\Repository\CompanyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class CompanyInfoFormType extends AbstractType implements DataMapperInterface
{

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @param CompanyRepository $companyRepository
     */
    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
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

        if (isset($forms['userCompany'])) {
            if ($viewData->getUserCompanyOther()) {
                $forms['userCompany']->setData('Other');
            } else {
                $forms['userCompany']->setData(
                    $viewData->getUserCompany() ? $viewData->getUserCompany()
                                                          ->getId() : null
                );
            }
        }

        if (isset($forms['userCompanyOther'])) {
            $forms['userCompanyOther']->setData($viewData->getUserCompanyOther());
        }
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var Feedback $viewData */

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if (isset($forms['userCompany'])) {
            if ($forms['userCompany']->getData() === 'Other' || empty($forms['userCompany']->getData())) {
                $viewData->setUserCompany(null);
            } elseif ($forms['userCompany']->getData()) {
                $company = $this->companyRepository->find($forms['userCompany']->getData());

                if ($company) {
                    $viewData->setUserCompany($company);
                }
            }
        }

        if (isset($forms['userCompanyOther'])) {
            $viewData->setUserCompanyOther($forms['userCompanyOther']->getData());
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);

        /** @var \App\Entity\Feedback $feedback */
        $feedback = $builder->getData();

        $companies = $this->companyRepository->findBy([], [
            'name' => 'ASC',
        ]);

        $companiesArray = [];

        foreach ($companies as $company) {
            $companiesArray[$company->getName()] = $company->getId();
        }

        $companiesArray['Other'] = 'Other';

        $builder->add('userCompany', ChoiceType::class, [
            'required'    => false,
            'placeholder' => 'Please Select',
            'multiple'    => false,
            'expanded'    => false,
            'choices'     => $companiesArray,
            'mapped'      => false,
        ]);

        $builder->get('userCompany')
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                    $userCompany = $event->getForm()
                                        ->getData();
                    $form       = $event->getForm()
                                        ->getParent();

                    if (!$form) {
                        return;
                    }

                    if (!$userCompany) {
                        return;
                    }

                    if ($userCompany === 'Other') {
                        if (!$form->has('userCompanyOther')) {
                            $form->add('userCompanyOther', TextType::class, [
                                'attr' => [
                                    'placeholder' => 'Company Name',
                                ],
                            ]);
                        }
                    } else {
                        if ($form->has('userCompanyOther')) {
                            $form->remove('userCompanyOther');
                        }
                    }
                });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Feedback $data */
            $data = $event->getData();
            $form = $event->getForm();

            if ($data->getUserCompanyOther()) {
                $form->add('userCompanyOther', TextType::class, [
                    'attr' => [
                        'placeholder' => 'Company Name',
                    ],
                ]);
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'companyInfo';
    }

}
