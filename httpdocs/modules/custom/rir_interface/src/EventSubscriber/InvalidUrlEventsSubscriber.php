<?php


namespace Drupal\rir_interface\EventSubscriber;


use Drupal;
use Drupal\Core\Routing\RequestHelper;
use Drupal\Core\Routing\TrustedRedirectResponse;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class InvalidUrlEventsSubscriber
 * @package Drupal\rir_interface\EventSubscriber
 */
class InvalidUrlEventsSubscriber implements EventSubscriberInterface
{

  /**
   * @return string[]
   */
  #[ArrayShape([KernelEvents::RESPONSE => "string"])]
  public static function getSubscribedEvents(): array
  {
    return [
      KernelEvents::RESPONSE => 'onInvalidRequestUrl'
    ];
  }

  /**
   * @param ResponseEvent $responseEvent
   */
  public function onInvalidRequestUrl(ResponseEvent $responseEvent)
  {
    if (!RequestHelper::isCleanUrl($responseEvent->getRequest())) {
      Drupal::logger('rir_interface')->warning('with invalid Url with /index.php detected.');
      $cleanRequestUri = trim($this->cleanPath($responseEvent->getRequest()->getRequestUri()));
      $cleanRequestUri = $cleanRequestUri === '' ? Drupal::config('system.site')->get('page.front') : $cleanRequestUri;
      $response = new TrustedRedirectResponse($cleanRequestUri, 302);
      $response->headers->set('X-Drupal-Route-Normalizer', 1);
      $responseEvent->setResponse($response);
    }
  }

  private function cleanPath($pathStr): string
  {
    if (str_starts_with($pathStr, '/index.php')) {
      return $this->cleanPath(substr_replace($pathStr, '', 0, 10));
    }
    return $pathStr;
  }
}
