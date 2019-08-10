<?php

namespace App\Util;


use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait FormHelper
{
    /**
     * This method takes an array of properties you want to make immutable for a form. That way no matter what the user
     * changes the form values to they can never actually update the values
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @param array $immutableFields
     */
    private function setupImmutableFields(FormBuilderInterface $builder, array $options, array $immutableFields) {

        foreach($immutableFields as $immutableField) {
            $builder->get($immutableField)
                ->addModelTransformer(new CallbackTransformer(
                    function ($value) {
                        return $value;
                    },
                    function ($value) use($options, $immutableField) {
                        $originalObject = $options['data'];
                        $callable = "get" . ucfirst($immutableField);
                        if(is_callable(array($originalObject, $callable))){
                            return $originalObject->$callable();
                        }
                        throw new NotFoundHttpException(sprintf("Method %s not found in class %s", $callable, get_class($originalObject)));
                    }
                ));

            $builder->get($immutableField)
                ->addViewTransformer(new CallbackTransformer(
                    function ($value) {
                        return $value;
                    },
                    function ($value) use($options, $immutableField) {
                        $originalObject = $options['data'];
                        $callable = "get" . ucfirst($immutableField);
                        if(is_callable(array($originalObject, $callable))){
                            return $originalObject->$callable();
                        }
                        throw new NotFoundHttpException(sprintf("Method %s not found in class %s", $callable, get_class($originalObject)));
                    }
                ));


        }
    }
}