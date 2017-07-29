<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/16/2017
 * Time: 11:50 AM
 */

namespace AdSearchEngine\Core\WebAPI\EventSubscriber;


use AdSearchEngine\Core\WebAPI\Controller\Base\IntegrationAPIController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof IntegrationAPIController) {
            if (!$event->getRequest()->headers->has("AUTH_TOKEN"))
                throw new UnauthorizedHttpException("Token");
            $crawlerId =  $event->getRequest()->headers->get("AUTH_TOKEN");
            $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
            if(!preg_match($UUIDv4, $crawlerId))
                throw new UnauthorizedHttpException("Token");
            if (!$controller[0]->getContext()->getCrawlerSet()->Exists($crawlerId))
                throw new UnauthorizedHttpException("Token");
            $controller[0]->setCrawlerAuthToken($crawlerId);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}