<?php

namespace Drupal\embederator\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the proxy_entity entity edit forms.
 *
 * @ingroup embederator
 */
class EmbederatorForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    // Make the label field required.
    $form['label']['widget'][0]['value']['#required'] = TRUE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Send back to the collection page.
    $form_state->setRedirect('entity.embederator.collection');
    $entity = $this->getEntity();
    $entity->save();
  }

}
