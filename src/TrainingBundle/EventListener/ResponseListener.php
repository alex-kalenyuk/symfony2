<?php

namespace TrainingBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (
            $event->isMasterRequest() && $this->isItBlogRequest($event)
            && $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            $response = $event->getResponse();
            $content = $response->getContent();

            $pos = strripos($content, '<body>');
            $content = substr($content, 0, $pos)."You are authenticated".substr($content, $pos);
            $response->setContent($content);
        }
    }

    public function isItBlogRequest(FilterResponseEvent $event)
    {
        $blogRoute = $this->container->get('router')->getRouteCollection()->get('training_blog');

        return substr_count($event->getRequest()->getRequestUri(), $blogRoute->getPath()) > 0;
    }
}