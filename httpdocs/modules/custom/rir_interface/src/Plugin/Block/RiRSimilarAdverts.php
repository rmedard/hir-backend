<?php
/**
 * Created by PhpStorm.
 * User: medar
 * Date: 17/06/2018
 * Time: 20:53
 */

namespace Drupal\rir_interface\Plugin\Block;


use Drupal;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Class RiRSimilarAdverts
 * @package Drupal\rir_interface\Plugin\Block
 * @Block(
 *   id = "rir_similar_adverts",
 *   admin_label = @Translation("RiR Similar Adverts"),
 *   category = @Translation("Custom RIR Blocks")
 * )
 */
class RiRSimilarAdverts extends BlockBase
{

    /**
     * Builds and returns the renderable array for this block plugin.
     *
     * If a block should not be rendered because it has no content, then this
     * method must also ensure to return no content: it must then only return an
     * empty array, or an empty array with #cache set (with cacheability metadata
     * indicating the circumstances for it being empty).
     *
     * @return array
     *   A renderable array representing the content of the block.
     *
     * @see \Drupal\block\BlockViewBuilder
     */
    public function build(): array
    {
        $node = Drupal::routeMatch()->getParameter('node');
        $output = [];
        $output[]['#cache']['max-age'] = 0; //No cache. Very important.
        if ($node and $node instanceof NodeInterface and $node->bundle() == 'advert') {
          /**
           * @var \Drupal\rir_interface\Service\AdvertsService $advertsService;
           */
            $advertsService = Drupal::service('rir_interface.adverts_service');
            $output[] = [
                '#theme' => 'hir_similar_adverts',
                '#advertIds' => $advertsService->loadSimilarAdverts($node)
            ];
        }
        return $output;
    }
}
