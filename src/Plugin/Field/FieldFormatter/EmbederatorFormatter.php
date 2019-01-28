<?php

namespace Drupal\embederator\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
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
    $client = \Drupal::service('http_client');

    // Get the embed type markup.
    $entity = $items->getEntity();
    $embederator_type = $entity_manager->getStorage('embederator_type')->load($entity->getType());

    if ($embederator_type->getUseSsi()) {
      $url_pattern = $embederator_type->getEmbedUrl();
      $elements = [];
      foreach ($items as $delta => $item) {
        $url = $token->replace($url_pattern, ['embederator' => $entity]);
        // hook_embederator_url_alter(&$url, $embederator_type, $entity).
        \Drupal::moduleHandler()->alter('embederator_url', $url, $embederator_type, $entity);
        try {
          $response = $client->request('GET', $url);
          $markup = (string) $response->getBody();
        }
        catch (Exception $e) {
          $markup = '<p>Unable to load ' . $url . '</p>';
        }
        // hook_embederator_embed_alter(&$html, $embederator_type, $entity).
        \Drupal::moduleHandler()->alter('embederator_embed', $markup, $embederator_type, $entity);
        $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => $markup,
          '#format' => 'full_html',
        ];
      }
    }
    else {
      $embed_pattern_field = $embederator_type->getMarkup();

      $elements = [];
      foreach ($items as $delta => $item) {
        $markup = $token->replace($embed_pattern_field['value'], ['embederator' => $entity]);
        // hook_embederator_embed_alter(&$html, $embederator_type, $entity).
        \Drupal::moduleHandler()->alter('embederator_embed', $markup, $embederator_type, $entity);
        $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => $markup,
          '#format' => $embed_pattern_field['format'],
        ];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // Limit to embederator.
    return (method_exists($field_definition, 'getProvider')
            && ($field_definition->getProvider() == 'embederator')
            && method_exists($field_definition, 'getName')
            && ($field_definition->getName() == 'embed_id'));
  }

}
