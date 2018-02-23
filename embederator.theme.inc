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