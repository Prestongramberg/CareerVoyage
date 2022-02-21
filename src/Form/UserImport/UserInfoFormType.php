<?php

namespace App\Form\UserImport;

use App\Entity\UserImport;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class UserInfoFormType extends AbstractType implements DataMapperInterface
{

    public function mapDataToForms($viewData, $forms): void
    {
        /** @var UserImport $viewData */

        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof UserImport) {
            throw new UnexpectedTypeException($viewData, UserImport::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if(isset($forms['userItems'])) {
            $forms['userItems']->setData(new ArrayCollection($viewData->getUsers()));
        }
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var UserImport $viewData */

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

        // todo going to need your own custom data mapper here

        $builder->add('userItems', CollectionType::class, [
            // each entry in the array will be an "email" field
            'entry_type' => UserFormType::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'userInfo';
    }

}
