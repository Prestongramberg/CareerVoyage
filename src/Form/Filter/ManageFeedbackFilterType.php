<?php

namespace App\Form\Filter;

use App\Entity\Experience;
use App\Entity\User;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ManageFeedbackFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filterableExperiences = $options['filterableExperiences'];

        $builder->add('title', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);

        $builder->add('startDateAndTime', Filters\DateRangeFilterType::class, [
                'left_date_options'  => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5'  => false,
                ],
                'right_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5'  => false,
                ],
            ]);

        $builder->add('experience', Filters\EntityFilterType::class, [
                'class'        => Experience::class,
                'choice_label' => 'title',
                'expanded'     => false,
                'multiple'     => false,
                'choices'      => $filterableExperiences,
                'placeholder'  => 'FILTER BY EXPERIENCE',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    /** @var Experience $experience */
                    $experience = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('e.id = :experienceId')
                                 ->setParameter('experienceId', $experience->getId());

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()
                                                 ->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]);

        $builder->add('hasFeedback', Filters\BooleanFilterType::class, [
                'placeholder'  => 'Events with/without feedback',
                'choices' => [
                  'Events with feedback' => 'y',
                  'Events without feedback' => 'n',
                ],
                'label'        => 'Has Feedback',
                'expanded' => true,
                'multiple' => false,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $hasFeedback = $values['value'] === 'y';

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    if ($hasFeedback) {
                        $queryBuilder->innerJoin('e.feedback', 'f')
                                     ->andWhere('f.id IS NOT NULL');
                    } else {
                        $queryBuilder->leftJoin('e.feedback', 'f')
                                     ->andWhere('f.id IS NULL');
                    }

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    return $newFilterQuery->getExpr();
                },
            ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
                'validation_groups'  => ['filtering'] // avoid NotBlank() constraint-related message
            ]);

        $resolver->setRequired('filterableExperiences');
    }

}