<?php

namespace Drupal\rir_interface\Service;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\path_alias\PathAliasInterface;

final class PathAliasService {


  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   */
  public function __construct(LoggerChannelFactory $loggerChannelFactory) {
    $this->logger = $loggerChannelFactory->get('PathAliasService');
  }

  /**
   * @param \Drupal\path_alias\PathAliasInterface $pathAlias
   *
   * @return void
   */
  public function updateNodeRelativePath(PathAliasInterface $pathAlias): void {
    preg_match('/node\/(\d+)/', $pathAlias->getPath(), $matches);
    $id = count($matches) > 1 ? $matches[1] : 0;
    if (!isset($id) or !$id or $id == 0) {
      return;
    }
    $node = Node::load($id);
    if ($node instanceof NodeInterface) {
      try {
        switch ($node->bundle()) {
          case 'advert':
            $node
              ->set('field_advert_relative_path', $pathAlias->getAlias())
              ->save();
            break;
          case 'agent':
            $node
              ->set('field_agent_relative_path', $pathAlias->getAlias())
              ->save();
            break;
          case 'property_request':
            $node
              ->set('field_pr_relative_path', $pathAlias->getAlias())
              ->save();
            break;
        }
      } catch (EntityStorageException $e) {
        $this->logger->error('Updating node relative path failed: ' . $e->getMessage());
      }
    }
  }
}
