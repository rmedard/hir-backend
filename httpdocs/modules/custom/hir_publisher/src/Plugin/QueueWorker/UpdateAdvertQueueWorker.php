<?php

namespace Drupal\hir_publisher\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermStorageInterface;

/**
 * @package Drupal\hir_publisher\Plugin\QueueWorker
 * @QueueWorker(
 *  id = "update_adverts_processor",
 *  title = "Update Adverts Queue Worker",
 *  cron = {"time" = 600}
 * )
 */
class UpdateAdvertQueueWorker extends QueueWorkerBase
{

  public function processItem($data)
  {
    $advert = Node::load($data);
    $current_district = $advert->get('field_advert_district')->entity;
    if ($current_district instanceof Term) {
      $termStorage = Drupal::entityTypeManager()->getStorage('taxonomy_term');
      if ($termStorage instanceof TermStorageInterface) {
        $new_district = $termStorage
          ->loadByProperties(['vid' => 'sectors', 'name' => $current_district->getName()]);
        $new_district = reset($new_district);
        if ($new_district instanceof Term) {
          $new_sector = $new_district->id();
          $current_sector = trim($advert->get('field_advert_sector')->value);
          $sectors = $termStorage->loadChildren($new_district->id(), 'sectors');
          foreach ($sectors as $sector) {
            if (strtolower($sector->getName()) === strtolower($current_sector)) {
              $new_sector = $sector->id();
              break;
            }
          }
          $advert->set('field_advert_locality', $new_sector);
          $advert->save();
          Drupal::logger('Updator')->info('Updated Advert ' . $data);
        }
      }
    }
  }
}
