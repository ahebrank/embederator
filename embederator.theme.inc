<?php

/**
 * @file
 * Preprocessors and theme functions of Aggregator module.
 */

use Drupal\Component\Utility\UrlHelper;
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
    
    $token_service = \Drupal::token();
        
    /**
    * The embederator type.
    *
    * @var \Drupal\embederator\Entity\EmbederatorType
    */
    $embederator_type =  \Drupal::entityTypeManager()->getStorage('embederator_type')->load($entity->bundle());

    // find/replace on the markup
    $orig_markup = $embederator_type->getMarkup();
    $markup = $token_service->replace($orig_markup, array('embederator' => $entity));

    $variables['embed'] = $markup;
    $variables['type'] = $entity->bundle();

    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
        $variables['content'][$key] = $variables['elements'][$key];
    }
}