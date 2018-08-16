<?php

namespace Drupal\embederator\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the default embederator (token replacing) formatter.
 *
 * @FieldFormatter(
 *   id = "embederator_default",
 *   module = "embederator",
 *   label = @Translation("Embederator"),
 *   field_types = {
 *     "string"
 *   }
 * );
 */
class EmbederatorFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /* @todo: inject these services */
    $token = \Drupal::service('token');
    $entity_manager = \Drupal::service('entity_type.manager');

    // get the embed type markup
    $entity = $items->getEntity();
    $embederator_type =  $entity_manager->getStorage('embederator_type')->load($entity->getType());
    $embed_pattern_field = $embederator_type->getMarkup();

    $elements = [];
    foreach ($items as $delta => $item) {
      $markup = $token->replace($embed_pattern_field['value'], ['embederator' => $entity]);
      $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => $markup,
          '#format' => $embed_pattern_field['format'],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // limit to embederator
    return (($field_definition->getProvider() == 'embederator') 
            && ($field_definition->getName() == 'embed_id'));
  }

}
