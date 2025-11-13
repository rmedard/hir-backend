<?php

namespace Drupal\rir_interface\Service;

use Drupal\Core\Field\FieldItemList;
use Drupal\node\Entity\Node;

final class ThemingService
{
  public function getPropertyTypePill(Node $advert): string
  {
    $colors = [
      'house' => 'primary',
      'apartment' => 'danger',
      'room' => 'warning',
      'shared_room' => 'warning',
      'building' => 'success',
      'commerce' => 'info',
      'other' => 'info',
      'warehouse' => 'light',
      'office' => 'secondary',
      'land' => 'dark'
    ];
    $propertyType = $advert->get('field_advert_property_type');
    $color = $colors[$propertyType->value];
    return '<span class="badge bg-' . $color . '-subtle text-' . $color . '-emphasis">' . $this->getListValue($propertyType) . '</span>';
  }

  public function getPropertyPricePill(Node $advert): string
  {
    $advertType = $advert->get('field_advert_type')->value;
    if ($advertType === 'auction') {
      $value = 'Auction';
    } else {
      $negotiable = boolval($advert->get('field_advert_price_negociable')->value);
      if ($negotiable) {
        $value = 'Price on request';
      } else {
        $price = number_format($advert->get('field_advert_price')->value);
        $currency = $this->getListValue($advert->get('field_advert_currency'));
        $payable = '';
        if (in_array($advertType, ['rent', 'short_rent'])) {
          $payable = match ($advert->get('field_advert_payment')->value) {
            'monthly' => '/month',
            'weekly' => '/week',
            'daily' => '/day',
            'nightly' => '/night',
            'yearly' => '/year',
            default => '',
          };
        }
        $value = $price . ' ' . $currency . '<small>'. $payable .'</small>';
      }
    }
    return '<span class="badge bg-light text-dark">' . $value .'</span>';
  }

  private function getListValue(FieldItemList $itemList) {
    $allowedValues = $itemList->getSetting('allowed_values');
    return $allowedValues[$itemList->value];
  }
}
