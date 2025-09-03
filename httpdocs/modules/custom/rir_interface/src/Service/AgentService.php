<?php

namespace Drupal\rir_interface\Service;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\NodeInterface;

/**
 * Created by PhpStorm.
 * User: reberme
 * Date: 20/09/2017
 * Time: 13:00
 */

class AgentService {

    protected EntityTypeManager $entityTypeManager;

    /**
     * AgentService constructor.
     *
     * @param EntityTypeManager $entityTypeManager
     */
    public function __construct(EntityTypeManager $entityTypeManager) {
        $this->entityTypeManager = $entityTypeManager;
    }

  /**
   * @param $agent_id
   * @param int $status Status of the adverts. Defaults to PUBLISHED
   *
   * @return array
   */
    public function loadAdverts($agent_id, int $status = NodeInterface::PUBLISHED): array
    {
        $data = [];
        try {
            $storage = $this->entityTypeManager->getStorage('node');
            $query = $storage->getQuery()->accessCheck()
                ->condition('type', 'advert')
                ->condition('status', $status)
                ->condition('field_advert_advertiser.target_id', $agent_id); // Could be field_advert_advertiser.entity.field_name (other than id)
            $advert_ids = $query->execute();
            $data = $storage->loadMultiple($advert_ids);
        } catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
            Drupal::logger('rir_interface')->error("Load adverts failed: " . $e->getMessage());
        }
        return $data;
    }

}
