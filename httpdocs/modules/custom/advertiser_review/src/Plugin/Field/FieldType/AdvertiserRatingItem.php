<?php

declare(strict_types=1);

namespace Drupal\advertiser_review\Plugin\Field\FieldType;

use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'advertiser_rating' field type.
 *
 * @FieldType(
 *   id = "advertiser_rating",
 *   label = @Translation("Advertiser Rating"),
 *   description = @Translation("A field that stores average rating and review count."),
 *   default_widget = "advertiser_rating",
 *   default_formatter = "advertiser_rating_default",
 * )
 */
final class AdvertiserRatingItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isEmpty(): bool {
    return $this->get('average_rating')->getValue() === NULL && $this->get('review_count')->getValue() === NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {

    $properties['average_rating'] = DataDefinition::create('float')
      ->setLabel(t('Average Rating'));
    $properties['review_count'] = DataDefinition::create('integer')
      ->setLabel(t('Review Count'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {

    $columns = [
      'average_rating' => [
        'type' => 'float',
        'size' => 'normal',
      ],
      'review_count' => [
        'type' => 'int',
        'size' => 'normal',
      ],
    ];

    return [
      'columns' => $columns,
      // @DCG Add indexes here if necessary.
    ];
  }

}
