<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class VerbListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $request = $event->getRequest();

        $modifyMethodOnFly = (
            $request->getMethod() === 'GET' &&
            $request->query->has('_method')
        );

        if($modifyMethodOnFly) {
            $request->setMethod( $request->query->get('_method'));
        }
    }
}