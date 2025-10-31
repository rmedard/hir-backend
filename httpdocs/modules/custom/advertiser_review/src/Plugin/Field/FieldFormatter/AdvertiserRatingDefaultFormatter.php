<?php

declare(strict_types=1);

namespace Drupal\advertiser_review\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'advertiser_rating_default' formatter.
 *
 * @FieldFormatter(
 *   id = "advertiser_rating_default",
 *   label = @Translation("Default"),
 *   field_types = {"advertiser_rating"},
 * )
 */
final class AdvertiserRatingDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
        'display_format' => 'stars_and_text',
        'show_count' => TRUE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element['display_format'] = [
      '#title' => t('Display format'),
      '#type' => 'select',
      '#options' => [
        'stars_and_text' => t('Stars with text'),
        'stars_only' => t('Stars only'),
        'text_only' => t('Text only'),
      ],
      '#default_value' => $this->getSetting('display_format'),
    ];

    $element['show_count'] = [
      '#title' => t('Show review count'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('show_count'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];
    $summary[] = t('Display format: @format', ['@format' => $this->getSetting('display_format')]);
    $summary[] = t('Show count: @show', ['@show' => $this->getSetting('show_count') ? 'Yes' : 'No']);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }
    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   The textual output generated.
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function viewValue(FieldItemInterface $item): TranslatableMarkup|string {
    $average_rating_str = $item->get('average_rating')->getValue();
    $review_count_str = $item->get('review_count')->getValue();
    $format = $this->getSetting('display_format');
    $show_count = $this->getSetting('show_count');

    if ($average_rating_str === NULL || $review_count_str === NULL) {
      return t('No reviews yet');
    }

    $average_rating = (float) $average_rating_str;
    $review_count = (int) $review_count_str;

    $stars = '';

    // Generate stars
    if ($format !== 'text_only') {
      $full_stars = floor($average_rating);
      $half_star = ($average_rating - $full_stars) >= 0.5 ? 1 : 0;
      $empty_stars = (int) (5 - $full_stars - $half_star);

      $stars = str_repeat('<i class="fas fa-star text-warning"></i>', (int) $full_stars);
      if ($half_star) {
        $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
      }
      $stars .= str_repeat('<i class="far fa-star text-muted"></i>', $empty_stars);
    }

    switch ($format) {
      case 'stars_only':
        $output = '<span class="agent-rating-stars">' . $stars . '</span>';
        break;

      case 'text_only':
        $output = '<span class="agent-rating-text">' . number_format($average_rating, 1) . '/5';
        if ($show_count) {
          $output .= ' (' . $review_count . ' ' . \Drupal::translation()->formatPlural($review_count, 'review', 'reviews') . ')';
        }
        $output .= '</span>';
        break;

      default: // stars_and_text
        $output = '<span class="agent-rating-display">';
        $output .= '<span class="agent-rating-stars">' . $stars . '</span> ';
        $output .= '<span class="agent-rating-text">' . number_format($average_rating, 1) . '/5';
        if ($show_count) {
          $output .= ' (' . $review_count . ' ' . \Drupal::translation()->formatPlural($review_count, 'review', 'reviews') . ')';
        }
        $output .= '</span>';
        $output .= '</span>';
        break;
    }

    return $output;
  }
}
