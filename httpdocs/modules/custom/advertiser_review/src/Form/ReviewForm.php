<?php

declare(strict_types=1);

namespace Drupal\advertiser_review\Form;

use Drupal;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use LogicException;

/**
 * Form controller for the review entity edit forms.
 */
final class ReviewForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['message']['widget'][0]['#allowed_formats'] = ['basic_html'];

    $current_route = Drupal::routeMatch()->getRouteName();
    if ($current_route === 'advertiser_review.add_form') {
      // Get the advertiser value that was pre-set
      $advertiser = $this->entity->get('advertiser')->entity;

      // Hide the field widget
      $form['advertiser']['widget']['#access'] = FALSE;

      // Add a display-only version
      if ($advertiser) {
        $form['advertiser_info'] = [
          '#type' => 'item',
          '#title' => $this->t('<h4>Your review for: </h4>'),
          '#markup' => '<h3 class="text-success">' . $advertiser->getTitle() . '</h3>',
          '#weight' => $form['advertiser']['#weight'],
        ];
      }
    }

    // Replace rating field with Font Awesome star interface
    if (isset($form['rate'])) {
      $form['rate']['widget'][0]['value']['#type'] = 'hidden';
      $form['rate']['widget'][0]['#suffix'] = '<div class="star-rating" data-rating="' . ($this->entity->get('rate')->value ?? 0) . '">
      <i class="star fas fa-star" data-value="1"></i>
      <i class="star fas fa-star" data-value="2"></i>
      <i class="star fas fa-star" data-value="3"></i>
      <i class="star fas fa-star" data-value="4"></i>
      <i class="star fas fa-star" data-value="5"></i>
    </div>';

      // Attach the star rating library
      $form['#attached']['library'][] = 'advertiser_review/star-rating';
    }

    // Change submit button text for new entities
    if ($this->entity->isNew()) {
      $form['actions']['submit']['#value'] = $this->t('Submit Review');
    } else {
      $form['actions']['submit']['#value'] = $this->t('Update Review');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->toLink()->toString()];
    $logger_args = [
      '%label' => $this->entity->label(),
      'link' => $this->entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New review %label has been created.', $message_args));
        $this->logger('advertiser_review')->notice('New review %label has been created.', $logger_args);
        /** @var \Drupal\node\Entity\Node $advertiser */
        $advertiser = $this->entity->get('advertiser')->entity;
        $redirectUrl = $advertiser->toUrl();
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The review %label has been updated.', $message_args));
        $this->logger('advertiser_review')->notice('The review %label has been updated.', $logger_args);
        $redirectUrl = $this->entity->toUrl();
        break;

      default:
        throw new LogicException('Could not save the entity.');
    }

    $form_state->setRedirectUrl($redirectUrl);

    return $result;
  }

}
