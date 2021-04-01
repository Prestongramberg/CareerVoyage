<?php

namespace App\Form\Filter\Report\Dashboard;

use App\Repository\FeedbackRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class SchoolFilterType
 *
 * @package App\Form\Filter
 */
class SchoolFilterType extends AbstractType
{
    public const CACHE_KEY = 'filter.report.dashboard.feedback_filters';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var FeedbackRepository
     */
    private $feedbackRepository;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * ProfessionalFilterType constructor.
     *
     * @param RequestStack       $requestStack
     * @param FeedbackRepository $feedbackRepository
     * @param string             $cacheDirectory
     */
    public function __construct(RequestStack $requestStack, FeedbackRepository $feedbackRepository,
                                string $cacheDirectory
    ) {
        $this->requestStack       = $requestStack;
        $this->feedbackRepository = $feedbackRepository;
        $this->cacheDirectory     = $cacheDirectory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cache = new FilesystemAdapter('pintex', 3600, $this->cacheDirectory);

        /*$filters = $cache->get(self::CACHE_KEY, function (ItemInterface $item) {

            return $this->feedbackRepository->getFilters();
        });*/

        $filters = $this->feedbackRepository->getFilters();

        $builder->add('regionNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['region_name'],
            ]
        );

        $builder->add('schoolNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['school_name'],
            ]
        );

        $builder->get('regionNames')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) use($filters) {


            $name = "test";

            $regionName = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();

            if(!$regionName) {
                return;
            }

            if(!$form) {
                return;
            }

            if($form->has('schoolNames')) {
                $form->remove('schoolNames');
            }

            $schoolNames = [];
            foreach($filters['school_name'] as $schoolName) {
                $test = "hi";
            }

            $form->add('schoolNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $filters['school_name'],
                ]
            );




            /*            $industry = $event->getForm()->getData();
                        $form     = $event->getForm()->getParent();

                        if (!$form) {
                            return;
                        }

                        if (!$industry) {
                            if ($form->has('secondaryIndustries')) {
                                $form->remove('secondaryIndustries');
                            }

                            return;
                        }

                        $this->modifyForm($event->getForm()->getParent(), $industry);*/
        });

        $builder->get('schoolNames')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $name = "test";

            $schoolName = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();
            $data       = $form->getData();


            /*            $industry = $event->getForm()->getData();
                        $form     = $event->getForm()->getParent();

                        if (!$form) {
                            return;
                        }

                        if (!$industry) {
                            if ($form->has('secondaryIndustries')) {
                                $form->remove('secondaryIndustries');
                            }

                            return;
                        }

                        $this->modifyForm($event->getForm()->getParent(), $industry);*/
        });

    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array (
                'csrf_protection' => false,
                'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
            )
        );
    }
}