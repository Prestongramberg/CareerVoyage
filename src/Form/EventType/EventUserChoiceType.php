<?php

namespace App\Form\EventType;

use App\Form\DataTransformer\UserIdToEntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventUserChoiceType extends AbstractType
{
    /**
     * @var UserIdToEntityTransformer
     */
    private $transformer;

    /**
     * EventUserChoiceType constructor.
     *
     * @param UserIdToEntityTransformer $transformer
     */
    public function __construct(UserIdToEntityTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}