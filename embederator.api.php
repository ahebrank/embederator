<?php

/**
 * @file
 * Hooks provided by the Embederator module.
 */

/**
 * Alter server-side url embed.
 */
function hook_embederator_url_alter(&$url, $context) {
  if (strpos($url, 'tfaform.net')) {
    $url . '?return=' . urlencode('http://example.com');
  }
}

/**
 * Alter markup prior to render.
 */
function hook_embederator_embed_alter(&$html, $context) {
  $html = trim($html);
}

/**
 * Force lazyload behavior.
 */
function hook_embederator_lazyload_alter(&$lazyload, $context) {
  if ($context['entity']->id() == 'long_form') {
    $lazyload = FALSE;
  }
}
