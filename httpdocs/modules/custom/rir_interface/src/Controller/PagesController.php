<?php
/**
 * Created by PhpStorm.
 * User: reberme
 * Date: 09/11/2017
 * Time: 17:21
 */

namespace Drupal\rir_interface\Controller;


use Drupal\Core\Controller\ControllerBase;
use JetBrains\PhpStorm\ArrayShape;

class PagesController extends ControllerBase
{
  /**
   * @return string[]
   */
  #[ArrayShape(['#theme' => "string"])]
  public function hirManagementPage(): array
  {
    return array(
      '#theme' => 'hir_management_page'
    );
  }
}
