<?php

namespace Drupal\hir_publisher\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\node\NodeInterface;
use Exception;

/**
 * Created by PhpStorm.
 * User: medard
 * Date: 22.09.17
 * Time: 08:51
 */

/**
 * Class UnpublisherQueueWorker
 *
 * @package Drupal\hir_publisher\Plugin\QueueWorker
 * @QueueWorker(
 *  id = "unpublisher_processor",
 *  title = "UnPublisher Queue Worker",
 *  cron = {"time" = 90}
 * )
 */
class UnPublisherQueueWorker extends QueueWorkerBase {

    /**
     * Works on a single queue item.
     *
     * @param mixed $data
     *   The data that was passed to
     *   \Drupal\Core\Queue\QueueInterface::createItem() when the item was queued.
     *
     * @throws RequeueException
     *   Processing is not yet finished. This will allow another process to claim
     *   the item immediately.
     * @throws Exception
     *   A QueueWorker plugin may throw an exception to indicate there was a
     *   problem. The cron process will log the exception, and leave the item in
     *   the queue to be processed again later.
     * @throws SuspendQueueException
     *   More specifically, a SuspendQueueException should be thrown when a
     *   QueueWorker plugin is aware that the problem will affect all subsequent
     *   workers of its queue. For example, a callback that makes HTTP requests
     *   may find that the remote server is not responding. The cron process will
     *   behave as with a normal Exception, and in addition will not attempt to
     *   process further items from the current item's queue during the current
     *   cron run.
     *
     * @see \Drupal\Core\Cron::processQueues()
     */
    public function processItem($data) {
      if ($data instanceof NodeInterface) {
        $data->setUnpublished();
        $data->save();
        Drupal::logger('hir_publisher')->notice(t('Advert ID: @advert_id unpublished after expiration.', ['@advert_id' => $data->id()]));
      }
    }
}
