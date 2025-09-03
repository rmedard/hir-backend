<?php
/**
 * Created by PhpStorm.
 * User: medar
 * Date: 20/11/2017
 * Time: 23:03
 */

namespace Drupal\rir_interface\Plugin\Block;


use Drupal;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class HiRLikeButtonsBlock
 * @package Drupal\rir_interface\Plugin\Block
 * @Block(
 *   id = "hir_like_buttons_block",
 *   admin_label = @Translation("HiR Like Buttons Block"),
 *   category = @Translation("Custom RIR Blocks")
 * )
 */
class HiRLikeButtonsBlock extends BlockBase
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
  #[ArrayShape(['#theme' => "string"])]
  public function build(): array
  {
    return [
      '#theme' => 'hir_like_buttons'
    ];
  }
}
