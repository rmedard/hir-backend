<?php
/**
 * Created by PhpStorm.
 * User: medar
 * Date: 17/06/2018
 * Time: 21:00
 */

namespace Drupal\rir_interface\Service;


use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\NodeInterface;

class AdvertsService
{

    protected EntityTypeManager $entityTypeManager;

    /**
     * AdvertsService constructor.
     * @param EntityTypeManager $entityTypeManager
     */
    public function __construct(EntityTypeManager $entityTypeManager)
    {
        $this->entityTypeManager = $entityTypeManager;
    }

    public function loadSimilarAdverts(NodeInterface $advert_node): array
    {
        $advertIds = array();
        try {
            $storage = $this->entityTypeManager->getStorage('node');
            $query = $storage->getQuery()->accessCheck()->range(0, 5)
                ->condition('type', 'advert')
                ->condition('status', NodeInterface::PUBLISHED)
                ->condition('field_advert_type', $advert_node->get('field_advert_type')->value)
//                ->condition('field_advert_district.target_id', $advert_node->get('field_advert_district')->target_id)
            ;

            if ($advert_node->get('field_advert_type')->value != 'auction' and
                $advert_node->get('field_advert_price_negociable')->value == '0') {
                $price = intval($advert_node->get('field_price_in_rwf')->value);
                $min_price = intval($price - ($price * 0.1));
                $max_price = intval($price + ($price * 0.1));
                $query = $query->condition('field_price_in_rwf', array($min_price, $max_price), 'BETWEEN');
            }
            $advertsIds = $query->execute();
            if (!empty($advertsIds)) {
                $advertIds = array_diff($advertsIds, [$advert_node->id()]);
            }
        } catch (InvalidPluginDefinitionException $e) {
            Drupal::logger('rir_interface')->error('Invalid plugin: ' . $e->getMessage());
        } catch (PluginNotFoundException $e) {
            Drupal::logger('rir_interface')->error('Plugin not found: ' . $e->getMessage());
        }
        return $advertIds;
    }

    public function setProposedAdvertOnPR($advertId, $prId): void {
        try {
            $storage = $this->entityTypeManager->getStorage('node');
            $pr = $storage->load($prId);
            if (isset($pr) && $pr instanceof NodeInterface && $pr->bundle() == 'property_request') {
                foreach ($pr->field_pr_proposed_properties->referencedEntities() as $advert) {
                    if ($advert instanceof EntityInterface) {
                        if ($advert->id() == $advertId) {
                            Drupal::logger('rir_interface')
                                ->error('Unable to set proposed advert because already set');
                            return;
                        }
                    }
                }

                $pr->field_pr_proposed_properties[] = ['target_id' => $advertId];
                $pr->save();
                Drupal::logger('rir_interface')
                    ->info(t('Advert @id proposed to PR @prId', array('@id' => $advertId, '@prId' => $prId)));
            }
        } catch (InvalidPluginDefinitionException $e) {
            Drupal::logger('rir_interface')->error('Invalid plugin: ' . $e->getMessage());
        } catch (PluginNotFoundException $e) {
            Drupal::logger('rir_interface')->error('Plugin not found: ' . $e->getMessage());
        } catch (EntityStorageException $e) {
            Drupal::logger('rir_interface')->error('Entity storage exception: ' . $e->getMessage());
        }
    }
}
