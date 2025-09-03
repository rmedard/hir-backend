<?php

namespace Drupal\rir_interface\Service;

use Drupal\rir_interface\Utils\Constants;
use Drupal\taxonomy\Entity\Term;

class LocalityService
{
  public function getLocality(int $deepestTermId): array
  {
    $locality = [];
    $terms = $this->computeParents([], $deepestTermId);
    $divisions = $this->localityDivisions();
    foreach ($terms as $key => $term) {
      if ($term instanceof Term) {
        $division = $divisions[count($divisions) - count($terms) + $key];
        $locality[$division] = $term->getName();
      }
    }
    return $locality;
  }

  private function computeParents(array $terms, int $term_id): array
  {
    if ($term_id !== 0) {
      /**
       * @var \Drupal\taxonomy\TermInterface $term
       */
      $term = Term::load($term_id);
      $terms[] = $term;
      return $this->computeParents($terms, intval($term->parent->target_id));
    }
    return $terms;
  }

  private function localityDivisions(): array
  {
    return [0 => Constants::SECTOR, 1 => Constants::DISTRICT, 2 => Constants::PROVINCE];
  }
}
