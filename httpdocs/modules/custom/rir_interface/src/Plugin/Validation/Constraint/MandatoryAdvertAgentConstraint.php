<?php

namespace Drupal\rir_interface\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides an AdvertAgent constraint.
 *
 * @Constraint(
 *   id = "MandatoryAdvertAgent",
 *   label = @Translation("AdvertAgent", context = "Validation"),
 *   type = "entity"
 * )
 *
 * @DCG
 * To apply this constraint, see https://www.drupal.org/docs/drupal-apis/entity-api/entity-validation-api/providing-a-custom-validation-constraint.
 */
class MandatoryAdvertAgentConstraint extends Constraint {

  public string $advertiserNotSpecified = 'The advertiser is mandatory when the publisher is a company or agency.';

  public function validatedBy(): string
  {
    return MandatoryAdvertAgentConstraintValidator::class;
  }
}
