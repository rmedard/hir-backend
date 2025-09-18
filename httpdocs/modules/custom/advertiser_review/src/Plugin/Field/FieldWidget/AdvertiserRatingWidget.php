<?php

declare(strict_types=1);

namespace Drupal\advertiser_review\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'advertiser_rating' field widget.
 *
 * @FieldWidget(
 *   id = "advertiser_rating",
 *   label = @Translation("Advertiser Rating"),
 *   field_types = {"advertiser_rating"},
 * )
 */
final class AdvertiserRatingWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {

    $element['average_rating'] = [
      '#type' => 'number',
      '#title' => t('Average Rating'),
      '#default_value' => isset($items[$delta]->average_rating) ? $items[$delta]->average_rating : 0,
      '#step' => 0.01,
      '#min' => 0,
      '#max' => 5,
      '#disabled' => TRUE,
      '#description' => t('This field is automatically calculated.'),
    ];

    $element['review_count'] = [
      '#type' => 'number',
      '#title' => t('Review Count'),
      '#default_value' => isset($items[$delta]->review_count) ? $items[$delta]->review_count : 0,
      '#min' => 0,
      '#disabled' => TRUE,
      '#description' => t('This field is automatically calculated.'),
    ];

    $element['#theme_wrappers'] = ['container', 'form_element'];
    $element['#attributes']['class'][] = 'container-inline';
    $element['#attributes']['class'][] = 'advertiser-rating-elements';
    $element['#attached']['library'][] = 'advertiser_review/advertiser_rating';

    return $element;
  }
}
