<?php

namespace Drupal\embederator\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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
    // @TODO: inject these services.
    $entity_manager = \Drupal::service('entity_type.manager');
    $renderer = \Drupal::service('embederator.render');
    $settings = $this->getSettings();

    // Get the embed type markup.
    $entity = $items->getEntity();
    $type = $entity->getType();
    $embederator_type = $entity_manager->getStorage('embederator_type')->load($type);

    // Hook context.
    $context = [
      'embederator_type' => $embederator_type,
      'entity' => $entity,
      'settings' => $settings,
    ];

    $elements = [];

    // Determine laziness.
    $loadstyle = $this->getSetting('loadstyle');
    if ($loadstyle == 'noquery') {
      // Unset lazyload if query parameters are present.
      $qp = \Drupal::request()->query->all();
      $loadstyle = count($qp) ? '' : 'lazy';
    }

    // Allow modification of lazylaod per embed.
    \Drupal::moduleHandler()->alter('embederator_lazyload', $loadstyle, $context);

    foreach ($items as $delta => $item) {
      if ($loadstyle == 'lazy') {
        $elements[$delta] = [
          '#type' => 'inline_template',
          '#template' => '<div data-embederator-lazyload="' . $entity->id() . '" data-embederator-type="' . $type . '" data-embederator-settings="' . urlencode(json_encode($settings)) . '">Loading...</div>',
          '#attached' => [
            'library' => [
              'embederator/lazyload',
            ],
          ],
        ];
      }
      elseif ($loadstyle == 'iframe') {
        $path = '/embederator/lazyload/' . $entity->id() . '/' . urlencode(json_encode($settings));
        $url = Url::fromUserInput($path);
        $initial_height = $this->getSetting('initial_height') ?? '500';
        $url_string = $url->toString();
        // Inject any query parameters into the src from the outer page.
        $qparams = \Drupal::request()->query->all();
        unset($qparams['q']);
        if ($qparams) {
          $url_string .= '?' . http_build_query($qparams);
        }
        $elements[$delta] = [
          '#type' => 'inline_template',
          '#template' => '<iframe data-embederator-iframe-proxy="' . $entity->id() . '" data-embederator-type="' . $type . '" title="Embederator iframe of type ' . $type . '" class="embederator-iframe-proxy" src="' . $url_string . '" height="' . $initial_height . '">Loading...</iframe>',
          '#attached' => [
            'library' => [
              'embederator/iframe',
            ],
          ],
        ];
      }
      else {
        if ($embederator_type->getUseSsi()) {
          $markup = $renderer->getSsiMarkup($embederator_type, $entity, $settings);
        }
        else {
          $markup = $renderer->getEmbedMarkup($embederator_type, $entity, $settings);
        }
        $elements[$delta] = $renderer->generateElement($markup);
      }
    }

    if ($this->getSetting('nullify_cache')) {
      $elements['#cache']['max-age'] = 0;
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

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('append_unique_id')) {
      $summary[] = $this->t('Append unique hash to form DOM IDs.');
    }
    if ($this->getSetting('nullify_cache')) {
      $summary[] = $this->t('Zero the cache.');
    }
    if ($lazy = $this->getSetting('loadstyle')) {
      $summary[] = $this->t('Load embed options: %lazy', ['%lazy' => $lazy]);
      if (($lazy == 'iframe') && ($height = $this->getSetting('initial_height'))) {
        $summary[] = $this->t('Iframe initial height: %height', ['%height' => $height]);
      }
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'append_unique_id' => FALSE,
      'nullify_cache' => FALSE,
      'loadstyle' => '',
      'initial_height' => 500,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['append_unique_id'] = [
      '#title' => $this->t('Append unique hash to form input DOM IDs'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('append_unique_id'),
    ];

    $element['nullify_cache'] = [
      '#title' => $this->t('Force 0 max-age cache'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('nullify_cache'),
    ];

    $element['loadstyle'] = [
      '#title' => $this->t('Embed load options'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('loadstyle'),
      '#options' => [
        '' => $this->t('None'),
        'lazy' => $this->t('Lazy load'),
        'noquery' => $this->t('Lazy if no query params'),
        'iframe' => $this->t('Iframe proxy'),
      ],
    ];

    $element['initial_height'] = [
      '#title' => $this->t('Iframe initial height'),
      '#type' => 'number',
      '#default_value' => $this->getSetting('initial_height'),
      '#states' => [
        'visible' => [
          'select[name$="[loadstyle]"]' => ['value' => 'iframe'],
        ],
      ],
    ];

    return $element;
  }

}
