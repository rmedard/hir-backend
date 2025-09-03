<?php
/**
 * Created by PhpStorm.
 * User: medard
 * Date: 30.07.17
 * Time: 16:11
 */

namespace Drupal\rir_interface\Plugin\Block;


use Drupal;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Class RiRRealTimeFigures
 *
 * @package Drupal\rir_interface\Plugin\Block
 * @Block(
 *   id = "rir_realtime_block",
 *   admin_label = @Translation("RiR Realtime Figures Block"),
 *   category = @Translation("Custom RIR Blocks")
 * )
 */
class RiRRealTimeFigures extends BlockBase {

    /**
     * Builds and returns the renderable array for this block plugin.
     *
     * If a block should not be rendered because it has no content, then this
     * method must also ensure to return no content: it must then only return
     * an
     * empty array, or an empty array with #cache set (with cacheability
     * metadata indicating the circumstances for it being empty).
     *
     * @return array
     *   A renderable array representing the content of the block.
     *
     * @see \Drupal\block\BlockViewBuilder
     */
    public function build(): array
    {
        $rent = Drupal::entityQuery('node')
          ->accessCheck(false)
          ->condition('type', 'advert')
          ->condition('field_advert_type', 'rent')
          ->count();
        $rent_count = $rent->execute();

        $sale = Drupal::entityQuery('node')
          ->accessCheck(false)
          ->condition('type', 'advert')
          ->condition('field_advert_type', 'buy')
          ->count();
        $sale_count = $sale->execute();

        $auction = Drupal::entityQuery('node')
          ->accessCheck(false)
          ->condition('type', 'advert')
          ->condition('field_advert_type', 'auction')
          ->count();
        $auction_count = $auction->execute();

        $agents = Drupal::entityQuery('node')
          ->accessCheck(false)
          ->condition('type', 'agent')
          ->count();
        $agents_count = $agents->execute();

        $prs = Drupal::entityQuery('node')
          ->accessCheck(false)
            ->condition('type', 'property_request')
            ->count();
        $prs_count = $prs->execute();

        $output = [];
        $output[]['#cache']['max-age'] = 0;
        $output[] = [
          '#theme' => 'rir_realtime',
          '#rent' => $rent_count,
          '#sale' => $sale_count,
          '#agents' => $agents_count,
          '#auctions' => $auction_count,
          '#prs' => $prs_count,
        ];
        return $output;
    }
}
