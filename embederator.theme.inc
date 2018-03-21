<?php

/**
 * @file
 * Preprocessors and theme functions of embederator module.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;

/*
 * Prepares variables for aggregator item templates.
 *
 * Default template: embederator.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An array of elements to display in view mode.
 */
function template_preprocess_embederator(&$variables) {
    $entity = $variables['elements']['#embederator'];
    
    $variables['embederator_type'] = Html::cleanCssIdentifier($entity->bundle());

    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
        $variables['content'][$key] = $variables['elements'][$key];
    }
}

/**
 * Prepares variables for list of available embderator type templates.
 *
 * Default template: embederator-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of embederator types.
 *
 * @see node_add_page()
 */
function template_preprocess_embederator_add_list(&$variables) {
    $variables['types'] = [];
    if (!empty($variables['content'])) {
      foreach ($variables['content'] as $type) {
        $variables['types'][$type->id()]['label'] = $type->label();
        $variables['types'][$type->id()]['url'] = \Drupal::url('embederator.add', ['embederator_type' => $type->id()]);
        $variables['types'][$type->id()]['description'] = [
            '#markup' => $type->getDescription(),
        ];
      }
    }
  }