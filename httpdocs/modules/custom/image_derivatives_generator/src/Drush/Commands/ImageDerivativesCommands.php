<?php

namespace Drupal\image_derivatives_generator\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drush\Commands\AutowireTrait;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;

/**
 * Image derivatives generator Drush commands.
 */
class ImageDerivativesCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Constructs a new ImageDerivativesCommands object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database
  ) {
    parent::__construct();
  }

  /**
   * Generate image derivatives for images used in specific view modes.
   */
  #[CLI\Command(name: 'image_derivatives_generator:generate', aliases: ['idg'])]
  #[CLI\Argument(name: 'style_name', description: 'The machine name of the image style')]
  #[CLI\Option(name: 'force', description: 'Force regeneration even if derivatives exist')]
  #[CLI\Option(name: 'limit', description: 'Limit the number of files to process')]
  #[CLI\Option(name: 'batch-size', description: 'Number of files to process per batch (default: 20)')]
  #[CLI\Option(name: 'bundle', description: 'Limit to specific content type (e.g., article, advert)')]
  #[CLI\Option(name: 'view-mode', description: 'Limit to specific view mode (e.g., teaser, full)')]
  #[CLI\Option(name: 'field', description: 'Limit to specific image field (e.g., field_image, field_banner)')]
  #[CLI\Option(name: 'all-files', description: 'Process all image files instead of only referenced ones')]
  #[CLI\Usage(name: 'image_derivatives_generator:generate advert_teaser', description: 'Generate derivatives for images used with advert_teaser style')]
  #[CLI\Usage(name: 'image_derivatives_generator:generate thumbnail --bundle=advert --view-mode=teaser', description: 'Generate for adverts in teaser view mode only')]
  #[CLI\Usage(name: 'image_derivatives_generator:generate large --field=field_banner', description: 'Generate only for field_banner images')]
  public function generate($style_name, $options = [
    'force' => false,
    'limit' => 0,
    'batch-size' => 20,
    'bundle' => '',
    'view-mode' => '',
    'field' => '',
    'all-files' => false
  ]): void {
    $style = $this->entityTypeManager->getStorage('image_style')->load($style_name);

    if (!$style) {
      $this->logger()->error(dt('Image style "@style" not found.', ['@style' => $style_name]));
      return;
    }

    // Get the file IDs based on what we want to process
    if ($options['all-files']) {
      $file_ids = $this->getAllImageFileIds($options);
    } else {
      $file_ids = $this->getReferencedImageFileIds($style_name, $options);
    }

    if (empty($file_ids)) {
      $this->logger()->warning(dt('No image files found matching the criteria.'));
      return;
    }

    // Apply limit if specified
    $files_to_process = $options['limit'] > 0 ? array_slice($file_ids, 0, $options['limit']) : $file_ids;
    $batch_size = max(1, min((int)$options['batch-size'], 50));

    $this->logger()->notice(dt('Found @total referenced image files. Processing @process files in batches of @batch for style "@style".', [
      '@total' => count($file_ids),
      '@process' => count($files_to_process),
      '@batch' => $batch_size,
      '@style' => $style_name
    ]));

    $this->processFileIds($files_to_process, $style, $batch_size, $options['force']);
  }

  /**
   * Get file IDs for images that are actually referenced by nodes using the image style.
   */
  private function getReferencedImageFileIds($style_name, $options = []): array {
    // Find display configurations that use this image style
    $displays = $this->findDisplaysUsingImageStyle($style_name, $options);

    if (empty($displays)) {
      $this->logger()->warning(dt('No display configurations found using image style "@style".', ['@style' => $style_name]));
      return [];
    }

    $this->logger()->notice(dt('Found @count display configurations using style "@style":', [
      '@count' => count($displays),
      '@style' => $style_name
    ]));

    foreach ($displays as $display) {
      $this->logger()->notice(dt('  - @entity_type.@bundle.@view_mode: @field', $display));
    }

    $file_ids = [];

    foreach ($displays as $display) {
      $entity_type = $display['entity_type'];
      $bundle = $display['bundle'];
      $field_name = $display['field_name'];

      // Get nodes of this bundle that have images in this field
      $query = $this->entityTypeManager->getStorage($entity_type)->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $bundle)
        ->exists($field_name);

      $entity_ids = $query->execute();

      if (empty($entity_ids)) {
        continue;
      }

      // Get file IDs from these entities
      $entities = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($entity_ids);

      foreach ($entities as $entity) {
        if ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
          foreach ($entity->get($field_name) as $item) {
            if (!empty($item->target_id)) {
              $file_ids[] = $item->target_id;
            }
          }
        }
      }

      // Clear entity cache to save memory
      $this->entityTypeManager->getStorage($entity_type)->resetCache($entity_ids);
    }

    return array_unique($file_ids);
  }

  /**
   * Find display configurations that use the specified image style.
   */
  private function findDisplaysUsingImageStyle($style_name, $options = []): array {
    $displays = [];
    $entity_display_storage = $this->entityTypeManager->getStorage('entity_view_display');

    // Get all entity view displays
    $display_ids = $entity_display_storage->getQuery()
      ->accessCheck(FALSE)
      ->execute();

    $entity_displays = $entity_display_storage->loadMultiple($display_ids);

    foreach ($entity_displays as $display) {
      $entity_type = $display->getTargetEntityTypeId();
      $bundle = $display->getTargetBundle();
      $view_mode = $display->getMode();

      // Apply filters if specified
      if (!empty($options['bundle']) && $bundle !== $options['bundle']) {
        continue;
      }

      if (!empty($options['view-mode']) && $view_mode !== $options['view-mode']) {
        continue;
      }

      // Check each component in this display
      $components = $display->getComponents();

      foreach ($components as $field_name => $component) {
        // Apply field filter if specified
        if (!empty($options['field']) && $field_name !== $options['field']) {
          continue;
        }

        // Check if this component uses our image style
        if (isset($component['type']) &&
          in_array($component['type'], ['image', 'responsive_image']) &&
          isset($component['settings']['image_style']) &&
          $component['settings']['image_style'] === $style_name) {

          $displays[] = [
            'entity_type' => $entity_type,
            'bundle' => $bundle,
            'view_mode' => $view_mode,
            'field_name' => $field_name,
          ];
        }
      }
    }

    return $displays;
  }

  /**
   * Get all image file IDs (fallback method).
   */
  private function getAllImageFileIds($options = []): array {
    $query = $this->entityTypeManager->getStorage('file')->getQuery()
      ->condition('filemime', [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp'
      ], 'IN')
      ->accessCheck(FALSE)
      ->sort('fid');

    return $query->execute();
  }

  /**
   * Process the file IDs in batches.
   */
  private function processFileIds(array $file_ids, $style, $batch_size, $force = false): void {
    $total = count($file_ids);
    $processed = 0;
    $success = 0;
    $failed = 0;
    $skipped = 0;

    // Process in batches
    $batches = array_chunk($file_ids, $batch_size);

    foreach ($batches as $batch_num => $batch_fids) {
      $files = $this->entityTypeManager->getStorage('file')->loadMultiple($batch_fids);

      foreach ($files as $file) {
        $original_uri = $file->getFileUri();
        $derivative_uri = $style->buildUri($original_uri);

        // Skip if exists and not forcing
        if (!$force && file_exists($derivative_uri)) {
          $skipped++;
        } else {
          try {
            if ($style->createDerivative($original_uri, $derivative_uri)) {
              $success++;
            } else {
              $failed++;
              $this->logger()->debug(dt('Failed to create derivative for: @uri', ['@uri' => $original_uri]));
            }
          } catch (\Exception $e) {
            $failed++;
            $this->logger()->error(dt('Exception creating derivative for @uri: @message', [
              '@uri' => $original_uri,
              '@message' => $e->getMessage()
            ]));
          }
        }

        $processed++;

        // Progress update every 25 files
        if ($processed % 25 == 0) {
          $percentage = round(($processed / $total) * 100, 1);
          $this->logger()->notice(dt('Progress: @processed/@total (@percentage%)', [
            '@processed' => $processed,
            '@total' => $total,
            '@percentage' => $percentage,
          ]));
        }
      }

      // Clear cache after each batch
      $this->entityTypeManager->getStorage('file')->resetCache($batch_fids);

      if (($batch_num + 1) % 5 == 0) {
        gc_collect_cycles();
      }
    }

    $this->logger()->success(dt('Generation completed! Total: @processed, Success: @success, Failed: @failed, Skipped: @skipped', [
      '@processed' => $processed,
      '@success' => $success,
      '@failed' => $failed,
      '@skipped' => $skipped,
    ]));
  }

  /**
   * Show which display configurations use a specific image style.
   */
  #[CLI\Command(name: 'image_derivatives_generator:show-usage', aliases: ['idsu'])]
  #[CLI\Argument(name: 'style_name', description: 'The machine name of the image style')]
  #[CLI\FieldLabels(labels: [
    'entity_type' => 'Entity Type',
    'bundle' => 'Bundle',
    'view_mode' => 'View Mode',
    'field_name' => 'Field',
    'image_count' => 'Images'
  ])]
  #[CLI\DefaultTableFields(fields: ['entity_type', 'bundle', 'view_mode', 'field_name', 'image_count'])]
  #[CLI\Usage(name: 'image_derivatives_generator:show-usage advert_teaser', description: 'Show where advert_teaser image style is used')]
  public function showUsage($style_name, $options = ['format' => 'table']): RowsOfFields {
    $displays = $this->findDisplaysUsingImageStyle($style_name);
    $rows = [];

    foreach ($displays as $display) {
      // Count images for this configuration
      $query = $this->entityTypeManager->getStorage($display['entity_type'])->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $display['bundle'])
        ->exists($display['field_name']);

      $count = $query->count()->execute();

      $rows[] = [
        'entity_type' => $display['entity_type'],
        'bundle' => $display['bundle'],
        'view_mode' => $display['view_mode'],
        'field_name' => $display['field_name'],
        'image_count' => $count,
      ];
    }

    return new RowsOfFields($rows);
  }
}
