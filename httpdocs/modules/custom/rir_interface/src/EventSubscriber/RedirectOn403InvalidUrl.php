<?php

namespace Drupal\rir_interface\EventSubscriber;


use Drupal;
use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RedirectOn403InvalidUrl extends HttpExceptionSubscriberBase
{

    protected function getHandledFormats(): array
    {
        return [];
    }

    public function on403(ExceptionEvent $event) {
        $path = $event->getRequest()->getRequestUri();
        if ($this->isInvalid($path)) {
            Drupal::logger('rir_interface')->warning('Invalid Url with /index.php detected.');
            $cleanRequestUri = trim($this->cleanPath($path));
            $cleanRequestUri = $cleanRequestUri === '' ? Drupal::config('system.site')->get('page.front') : $cleanRequestUri;
            $response = new TrustedRedirectResponse($cleanRequestUri, 302);
            $response->headers->set('X-Drupal-Route-Normalizer', 1);
            $event->setResponse($response);
        }
    }

    private function isInvalid($path): bool {
        return str_starts_with($path, '/index.php');
    }

    private function cleanPath($pathStr): string {
        if ($this->isInvalid($pathStr)) {
            return $this->cleanPath(substr_replace($pathStr, '', 0, 10));
        }
        return $pathStr;
    }
}
