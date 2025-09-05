<?php

declare(strict_types=1);

namespace Drupal\advertiser_review;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a review entity type.
 */
interface ReviewInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
