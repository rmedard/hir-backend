<?php

declare(strict_types=1);

namespace Drupal\advertiser_review\Entity;

use Drupal\advertiser_review\ReviewInterface;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the review entity class.
 *
 * @ContentEntityType(
 *   id = "review",
 *   label = @Translation("Review"),
 *   label_collection = @Translation("Reviews"),
 *   label_singular = @Translation("review"),
 *   label_plural = @Translation("reviews"),
 *   label_count = @PluralTranslation(
 *     singular = "@count reviews",
 *     plural = "@count reviews",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\advertiser_review\ReviewListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\advertiser_review\Access\ReviewAccessControlHandler",
 *     "action" = "Drupal\advertiser_review\ReviewAction",
 *     "form" = {
 *       "add" = "Drupal\advertiser_review\Form\ReviewForm",
 *       "edit" = "Drupal\advertiser_review\Form\ReviewForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "review",
 *   admin_permission = "administer review",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/review",
 *     "add-form" = "/review/add",
 *     "canonical" = "/review/{review}",
 *     "edit-form" = "/review/{review}/edit",
 *     "delete-form" = "/review/{review}/delete",
 *     "delete-multiple-form" = "/admin/content/review/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.review.settings",
 * )
 */
final class Review extends ContentEntityBase implements ReviewInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    // Name field - Name of the reviewer
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Your Name'))
      ->setDescription(t('The name of the person writing the review.'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Rate field - Rating from 0 to 5
    $fields['rate'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Rating'))
      ->setDescription(t('Rating from 0 to 5 stars.'))
      ->setRequired(TRUE)
      ->setSettings([
        'min' => 0,
        'max' => 5,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Message field - Review text
    $fields['message'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Review Message'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => -2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -2,
        'settings' => [
          'rows' => 6
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Advertiser field - Reference to advertiser node
    $fields['advertiser'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Advertiser'))
      ->setDescription(t('The advertiser being reviewed.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => ['agent' => 'agent'],
        'sort' => [
          'field' => 'title',
          'direction' => 'ASC',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => -1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => 'Select an advertiser',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(FALSE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the review was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the review was last edited.'));

    // Add path field
    $fields['path'] = BaseFieldDefinition::create('path')
      ->setLabel(t('URL alias'))
      ->setDescription(t('The path alias for this review.'))
      ->setRevisionable(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'path',
        'weight' => 30,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setComputed(TRUE);

    return $fields;
  }

}
