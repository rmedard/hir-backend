<?php
/**
 * Created by PhpStorm.
 * User: reberme
 * Date: 28/07/2017
 * Time: 16:59
 */

namespace Drupal\rir_interface\Plugin\Block;


use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Class CopyrightBlock
 * @package Drupal\rir_interface\Plugin\Block
 *
 * @Block(
 *     id = "copyright_block",
 *     admin_label = @Translation("Copyright block"),
 *     category = @Translation("Custom RIR Blocks")
 * )
 */
class CopyrightBlock extends BlockBase {

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
    public function build(): array {
        return [
          '#theme' => 'rir_copyright',
        ];
    }
}
