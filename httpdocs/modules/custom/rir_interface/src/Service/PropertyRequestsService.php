<?php
/**
 * Created by PhpStorm.
 * User: medard
 * Date: 07/02/2019
 * Time: 01:27
 */

namespace Drupal\rir_interface\Service;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\NodeInterface;

final class PropertyRequestsService
{
    protected EntityTypeManager $entityTypeManager;
  protected LoggerChannelInterface $logger;

  /**
   * PropertyRequestsService constructor.
   *
   * @param EntityTypeManager $entityTypeManager
   * @param LoggerChannelFactory $loggerChannelFactory
   */
    public function __construct(EntityTypeManager $entityTypeManager, LoggerChannelFactory $loggerChannelFactory)
    {
        $this->entityTypeManager = $entityTypeManager;
        $this->logger = $loggerChannelFactory->get('PropertyRequestsService');
    }

    public function loadPRsForAdvert(NodeInterface $advert): array
    {
        try {
            $storage = $this->entityTypeManager->getStorage('node');
            return $storage->loadByProperties(
                [
                    'type' => 'property_request',
                    'field_pr_proposed_properties' => $advert->id()
                ]);
        } catch (InvalidPluginDefinitionException $e) {
            $this->logger->error('Invalid plugin: ' . $e->getMessage());
        } catch (PluginNotFoundException $e) {
            $this->logger->error('Plugin not found: ' . $e->getMessage());
        }
        return [];
    }
}
