<?php

namespace Drupal\embederator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\Token;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Misc global utilities.
 */
class EmbederatorUtilities {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Token handling.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Token $token) {
    $this->entityTypeManager = $entity_type_manager;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('token')
    );
  }

  /**
   * Append a preview element to the form.
   */
  public function addFormPreview(&$form, $entity, $markup) {
    if ($markup && $entity) {
      // Add token identifier.
      foreach (Element::children($form) as $key) {
        $form[$key]['#attributes']['data-embederator-token'] = '[embederator:' . $key . ']';
      }

      $dom_id = "embederator_" . $entity->id() . '__preview';
      $form['preview'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Embed markup'),
        '#attributes' => [
          'class' => [
            'embederator__preview-wrapper',
          ],
        ],
        'html' => [
          '#markup' => '<div id="' . $dom_id . '" class="embederator__preview"><div class="embederator__preview--unhighlighted">' . Html::escape($markup) . '</div></div>',
        ],
        '#weight' => 999,
      ];

      $form['#attributes']['class'][] = 'embederator-token-form';
      $form['#attached']['library'][] = 'embederator/preview';
    }
  }

  /**
   * Append parsing features.
   */
  public function addFormParser(&$form, $markup) {
    if (isset($form['preview'])) {
      $form['preview']['parse'] = [
        '#weight' => -99,
        '#type' => 'container',
        'paste_launch' => [
          '#type' => 'markup',
          '#markup' => '<a class="embederator__paste-launch" href="#">Parse pasted embed code</a>',
        ],
        'paste_box' => [
          '#type' => 'textarea',
          '#attributes' => [
            'class' => [
              'embederator__hidden',
              'embederator__paste-box',
            ],
            'placeholder' => $markup,
          ],
        ],
      ];
      $form['#attached']['library'][] = 'embederator/parse';
    }
  }

  /**
   * Add form helpers.
   */
  public function customizeForm(&$form, FormStateInterface $form_state) {
    list($entity, $bundle_id) = $this->getEntityConfig($form, $form_state);
    $markup = $this->getPreview($bundle_id, $form_state);

    $this->addFormPreview($form, $entity, $markup);
    $this->addFormParser($form, $markup);
  }

  /**
   * Build the preview from current form values.
   */
  public function getPreview($bundle_id, $form_state) {
    $bundle_config = $this->entityTypeManager->getStorage('embederator_type')->load($bundle_id);
    if ($bundle_config) {
      $markup = $bundle_config->getMarkupHtml();
      // $vals = $form_state->getValues();
      // $vals['type'] = $bundle_id;
      // $entity = $this->entityTypeManager->getStorage('embederator')->create($vals);
      // return $this->token->replace($markup, ['embederator' => $entity], ['sanitize' => FALSE]);.
      return $markup;
    }
    return NULL;
  }

  /**
   * Return entity and bundle_id.
   */
  public function getEntityConfig($form, $form_state) {
    if (isset($form['#bundle'])) {
      $bundle_id = $form['#bundle'];
      $entity = $form['#default_value'];
    }
    else {
      $entity = $form_state->getFormObject()->getEntity();
      $bundle_id = $entity->bundle();
    }
    return [$entity, $bundle_id];
  }

}
