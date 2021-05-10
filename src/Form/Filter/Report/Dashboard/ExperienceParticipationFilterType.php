<?php

namespace App\Form\Filter\Report\Dashboard;

use App\Entity\Feedback;
use App\Entity\User;
use App\Repository\FeedbackRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Pinq\ITraversable;
use Pinq\Traversable;

/**
 * Class ExperienceParticipationFilterType
 *
 * @package App\Form\Filter
 */
class ExperienceParticipationFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $feedback = $options['feedback'];
        /** @var User $user */
        $user = $options['user'];

        $builder->add('participationType', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $this->getScalarFacet($feedback, 'participationType'),
            ]
        );

        $builder->add('schoolNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $this->getArrayFacet($feedback, 'schoolNames'),
            ]
        );

        $builder->add('regionNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $this->getArrayFacet($feedback, 'regionNames'),
            ]
        );

        $builder->add('participationExperience', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $this->getScalarFacet($feedback, 'experienceType'),
            ]
        );

        $builder->add('registrationDate', Filters\DateRangeFilterType::class, [
                'left_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                ],
                'right_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                ],
            ]
        );

    }

    private function getScalarFacet(Traversable $cachedFeedback, $key)
    {

        $results = $cachedFeedback
            ->where(function ($row) use ($key) {
                return $row[$key] !== null && !empty($row[$key]);
            })
            ->groupBy(function ($row) use ($key) {
                return $row[$key];
            })->select(function (ITraversable $data) use ($key) {
                return ['key' => $data->last()[$key], 'count' => $data->count()];
            })->orderByAscending(function ($row) {
                return $row['key'];
            });

        $choices = [];
        foreach ($results as $result) {
            $label           = sprintf("%s (%s)", $result['key'], $result['count']);
            $value           = $result['key'];
            $choices[$label] = $value;
        }

        return $choices;
    }

    private function getArrayFacet(Traversable $cachedFeedback, $key)
    {

        $results = $cachedFeedback
            ->where(function ($row) use ($key) {
                return !empty($row[$key]);
            })->select(function ($row) use ($key) {
                return $row[$key];
            });

        if(empty($results->asArray())) {
            return [];
        }

        $results = array_merge(...$results->asArray());
        $results   = Traversable::from($results);

        $results = $results->groupBy(function ($value) {
            return $value;
        })->select(function (ITraversable $data) use ($key) {
            return ['key' => $data->last(), 'count' => $data->count()];
        })->orderByAscending(function ($row) {
            return $row['key'];
        });

        $choices = [];
        foreach ($results as $result) {
            $label           = sprintf("%s (%s)", $result['key'], $result['count']);
            $value           = $result['key'];
            $choices[$label] = $value;
        }

        return $choices;
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array (
                'csrf_protection' => false,
                'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
            )
        );

        $resolver->setRequired(['feedback', 'user']);

    }
}