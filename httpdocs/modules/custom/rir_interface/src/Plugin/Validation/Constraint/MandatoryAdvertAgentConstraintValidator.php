<?php

namespace Drupal\rir_interface\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the AdvertAgent constraint.
 */
class MandatoryAdvertAgentConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint): void
  {
    if ($entity instanceof NodeInterface && $entity->bundle() == 'advert') {
      $publishedByAgent = boolval($entity->get('field_advert_is_agent')->value);
      $noAgentSpecified = $entity->get('field_advert_advertiser')->isEmpty();
      $isInvalidAdvert = $publishedByAgent && $noAgentSpecified;
      if ($isInvalidAdvert && $constraint instanceof MandatoryAdvertAgentConstraint) {
        $this->context->buildViolation($constraint->advertiserNotSpecified)
          ->atPath('field_advert_advertiser')
          ->addViolation();
      }
    }

  }

}
