<?php

namespace App\Form\Filter\Report\Dashboard;

use App\Entity\Feedback;
use App\Entity\Region;
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
 * Class VolunteerParticipationFilterType
 *
 * @package App\Form\Filter
 */
class VolunteerParticipationFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $feedback = $options['feedback'];
        /** @var User $user */
        $user = $options['user'];


        $builder->add('regionNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $this->getArrayFacet($feedback, 'regionNames', function () use ($user) {

                    if ($user->isRegionalCoordinator() && $region = $user->getRegion()) {
                        return [$region->getName()];
                    }

                    if ($user->isSchoolAdministrator()) {
                        $regions = [];
                        foreach ($user->getSchools() as $school) {
                            if ($region = $school->getRegion()) {
                                $regions[] = $region->getName();
                            }
                        }

                        return $regions;
                    }

                    return [];
                }),
            ]
        );

        $builder->add('schoolNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $this->getArrayFacet($feedback, 'schoolNames', function () use ($user) {

                    if ($user->isRegionalCoordinator() && $region = $user->getRegion()) {
                        $schools = [];
                        /** @var Region $region */
                        foreach ($region->getSchools() as $school) {
                            $schools[] = $school->getName();
                        }

                        return $schools;
                    }

                    if ($user->isSchoolAdministrator()) {
                        $schools = [];
                        foreach ($user->getSchools() as $school) {
                            $schools[] = $school->getName();
                        }

                        return $schools;
                    }

                    return [];
                }),
            ]
        );

        $builder->add('experienceType', Filters\ChoiceFilterType::class, [
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

    private function getScalarFacet(Traversable $cachedFeedback, $key, $includeOnly = null)
    {

        $results = $cachedFeedback
            ->where(function ($row) use ($key, $includeOnly) {

                if (empty($row[$key])) {
                    return false;
                }

                if (is_callable($includeOnly)) {
                    $includeOnly = $includeOnly();
                }

                if (!empty($includeOnly) && !in_array($row[$key], $includeOnly)) {
                    return false;
                }

                return true;
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
            $label = sprintf("%s (%s)", $result['key'], $result['count']);
            $value = $result['key'];

            $choices[$label] = $value;
        }

        return $choices;
    }

    private function getArrayFacet(Traversable $cachedFeedback, $key, $includeOnly)
    {

        $results = $cachedFeedback
            ->where(function ($row) use ($key) {

                return !empty($row[$key]);
            })->select(function ($row) use ($key) {
                return $row[$key];
            });

        if (empty($results->asArray())) {
            return [];
        }

        $results = array_merge(...$results->asArray());
        $results = Traversable::from($results);

        $results = $results->where(function ($value) use ($includeOnly) {

            if (is_callable($includeOnly)) {
                $includeOnly = $includeOnly();
            }

            if (!empty($includeOnly) && !in_array($value, $includeOnly)) {
                return false;
            }

            return true;
        })->groupBy(function ($value) {
            return $value;
        })->select(function (ITraversable $data) use ($key) {
            return ['key' => $data->last(), 'count' => $data->count()];
        })->orderByAscending(function ($row) {
            return $row['key'];
        });

        $choices = [];
        foreach ($results as $result) {
            $label = sprintf("%s (%s)", $result['key'], $result['count']);
            $value = $result['key'];

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
                'allow_extra_fields' => true,
                'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
            )
        );

        $resolver->setRequired(['feedback', 'user']);

    }
}