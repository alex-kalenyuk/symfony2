<?php

namespace TrainingBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $uri = $event->getRequest()->getRequestUri();
            if (substr_count($uri, 'app/example')) {
                $response = new Response();
                $response->setContent("Request was intercepted by Event Listener");
                $event->setResponse($response);
            }
        }
    }
}