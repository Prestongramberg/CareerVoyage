<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\User;
use App\Entity\UserImportUser;
use App\Repository\SchoolRepository;
use App\Repository\StudentUserRepository;
use App\Repository\UserImportRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ManageStudentsFilterType extends AbstractType
{

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var UserImportRepository
     */
    private $userImportRepository;

    /**
     * @param  \App\Repository\StudentUserRepository  $studentUserRepository
     * @param  \App\Repository\SchoolRepository       $schoolRepository
     * @param  \App\Repository\UserImportRepository   $userImportRepository
     */
    public function __construct(StudentUserRepository $studentUserRepository, SchoolRepository $schoolRepository, UserImportRepository $userImportRepository)
    {
        $this->studentUserRepository = $studentUserRepository;
        $this->schoolRepository      = $schoolRepository;
        $this->userImportRepository  = $userImportRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $schoolIds     = $options['schoolIds'];
        $eventRegister = $options['eventRegister'];

        $graduationYears = $this->studentUserRepository->getGraduationYears($schoolIds);
        $userImports = $this->userImportRepository->findBySchoolIds($schoolIds);

        $userImportChoices = [];
        $userImportChoicesMap = [];
        foreach($userImports as $userImport) {
            $userImportChoices[] = $userImport->getId();

            $usersFinishedBeingImported = $userImport->getUserImportUsers()->filter(function(UserImportUser $userImportUser) {
                return $userImportUser->getIsImported();
            });

            $userImportChoicesMap[$userImport->getId()] =
                sprintf("%s - (%s/%s users)",
                    $userImport->getCreatedAt()->format("m/d/Y"),
                    $usersFinishedBeingImported->count(),
                    count($userImport->getUserImportUsers()));
        }

        $graduationYears = array_map(function ($gy) {
            return $gy['graduatingYear'];
        }, $graduationYears);

        $graduationYears = array_combine($graduationYears, $graduationYears);

        $builder->add('firstName', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
        $builder->add('lastName', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
        $builder->add('email', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
        $builder->add('username', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);

        $builder->add('educatorUsers', Filters\EntityFilterType::class, [
            'class'         => EducatorUser::class,
            'query_builder' => function (EntityRepository $er) use ($schoolIds) {
                $queryBuilder = $er->createQueryBuilder('eu')
                                   ->addOrderBy('eu.lastName', 'ASC');

                if (!empty($schoolIds)) {
                    $queryBuilder->andWhere('eu.school IN (:schools)')
                                 ->setParameter('schools', $schoolIds);
                }

                return $queryBuilder;
            },
            'choice_label'  => 'fullNameReversed',
            'placeholder'   => '-- Filter By Supervising Educator --',
            'expanded'      => false,
            'multiple'      => false,
            'required'      => false,
        ]);

        $builder->add('createdAt', Filters\ChoiceFilterType::class, [
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                $userImportId = $values['value'];

                $queryBuilder = $filterQuery->getQueryBuilder();

                // not sure if there is a school join already happening so we will alias it s2 to prevent alias clash
                $queryBuilder->innerJoin('u.userImportUser', 'uiu')
                             ->innerJoin('uiu.userImport', 'ui')
                             ->andWhere('ui.id = :userImportId')
                             ->setParameter('userImportId', $userImportId);

                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
            'placeholder'   => '-- Filter By Import --',
            'expanded'      => false,
            'multiple'      => false,
            'required'      => false,
            'mapped'        => false,
            'choices'       => $userImportChoices,
            'choice_label' => function ($value, $key, $index) use($userImportChoicesMap) {
                return $userImportChoicesMap[$value];
            }
        ]);

        $builder->add('graduatingYear', ChoiceType::class, [

            'choices'     => $graduationYears,
            'placeholder' => '-- Filter by Graduating Year --',
            'expanded'    => false,
            'multiple'    => false,
            'required'    => false,
        ]);

        if ($eventRegister) {
            $builder->add('registrationStatus', Filters\ChoiceFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) use ($eventRegister) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $status = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    if ($status === 'registered') {
                        $queryBuilder->innerJoin('u.registrations', 'r')
                                     ->andWhere('r.experience = :experienceId')
                                     ->setParameter('experienceId', $eventRegister->getId());
                    } elseif ($status === 'unregistered') {
                        $queryBuilder->leftJoin('u.registrations', 'r', "WITH", "r.experience = :experienceId")
                                     ->andWhere('r.id IS NULL')
                                     ->setParameter('experienceId', $eventRegister->getId());

                        //->setParameter('experienceId', $eventRegister->getId());
                        //$queryBuilder->andWhere('u.city IS NULL');
                    }

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    return $newFilterQuery->getExpr();
                },
                'expanded'     => false,
                'multiple'     => false,
                'placeholder'  => '-- Filter by Registration Status --',
                'required'     => false,
                //'mapped'   => false,
                'choices'      => [
                    'Registered'   => 'registered',
                    'Unregistered' => 'unregistered',
                ],
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'schoolIds'         => [],
            'csrf_protection'   => false,
            'eventRegister'     => null,
            'validation_groups' => ['filtering'] // avoid NotBlank() constraint-related message
        ])
                 ->setRequired('filter_type');
    }

}