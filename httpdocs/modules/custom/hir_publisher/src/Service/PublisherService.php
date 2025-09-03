<?php
/**
 * Created by PhpStorm.
 * User: reberme
 * Date: 22/09/2017
 * Time: 12:00
 */

namespace Drupal\hir_publisher\Service;


use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;
use function count;

class PublisherService
{

    protected $entityTypeManager;

    /**
     * PublisherService constructor.
     *
     * @param EntityTypeManager $entityTypeManager
     */
    public function __construct(EntityTypeManagerInterface $entityTypeManager)
    {
        $this->entityTypeManager = $entityTypeManager;
    }

    public function loadExpiredAdverts($date): array
    {
        try {
            $storage = $this->entityTypeManager->getStorage('node');
            $query = $storage->getQuery()->accessCheck(FALSE)
                ->condition('type', 'advert')
                ->condition('status', NodeInterface::PUBLISHED)
                ->condition('field_advert_expirydate', $date, '<');
            $expired_adverts_ids = $query->execute();
            if (isset($expired_adverts_ids) and count($expired_adverts_ids) > 0) {
                return array_values($storage->loadMultiple($expired_adverts_ids));
            } else {
                Drupal::logger('hir_publisher')->debug('No expired adverts found');
            }
        } catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
            Drupal::logger('hir_publisher')->error($e->getMessage());
        }
        return array();
    }

    public function loadNonMappedPropertyRequests(): array
    {
        try {
            $connection = Drupal::database();
            $selectQuery = $connection->select('webform_submission', 'ws')
                ->fields('ws', array('sid'));
            $selectQuery->where('webform_id = \'property_request_form\'');
            $selectQuery->addTag('is_pr_mapped');
            $ids = $selectQuery->execute()->fetchCol();
            if (isset($ids) && count($ids) > 0) {
                $storage = $this->entityTypeManager->getStorage('webform_submission');
                return $storage->loadMultiple($ids);
            } else {
                Drupal::logger('hir_publisher')
                    ->info('No non-mapped submissions found');
            }
        } catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
            Drupal::logger('hir_publisher')->error($e->getMessage());
        }
        return array();
    }

    public function unPublishExpiredPropertyRequests() {
        try {
            $storage = $this->entityTypeManager->getStorage('node');
            $now = new DrupalDateTime('now');
            $query = $storage->getQuery()
                ->condition('type', 'property_request')
                ->condition('status', NodeInterface::PUBLISHED)
                ->condition('field_pr_expiry_date', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '<');
            $prIds = $query->execute();
            if (isset($prIds) && count($prIds) > 0) {
              $prs = $storage->loadMultiple($prIds);
              if (count($prs) > 0) {
                foreach ($prs as $prId => $pr){
                  if ($pr instanceof NodeInterface) {
                    $pr->setUnpublished();
                    $pr->save();
                    Drupal::logger('hir_publisher')->notice(t('PR ID: @pr_id unpublished after expiration.', ['@pr_id' => $prId]));
                  }
                }
              }
            }
        } catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
            Drupal::logger('hir_publisher')->error($e->getMessage());
        }
    }
}
