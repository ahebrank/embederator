<?php

namespace Drupal\embederator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\embederator\Entity\Embederator;
use Drupal\embederator\EmbederatorRender;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;

/**
 * Return a render for the lazyload.
 */
class AjaxRender extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Embed render utilities.
   *
   * @var Drupal\embederator\EmbederatorRender
   */
  protected $embedRenderer;

  /**
   * Core renderer.
   *
   * @var Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Inject dependencies.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, EmbederatorRender $embed_renderer, RendererInterface $renderer) {
    $this->entityManager = $entity_manager;
    $this->embedRenderer = $embed_renderer;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('embederator.render'),
      $container->get('renderer')
    );
  }

  /**
   * Render the element.
   */
  public function render(Embederator $embederator, string $settings_json) {
    $settings = [];
    if ($settings_json) {
      $settings = json_decode(urldecode($settings_json), TRUE);
    }
    $embederator_type = $this->entityManager->getStorage('embederator_type')->load($embederator->getType());

    if ($embederator_type->getUseSsi()) {
      $markup = $this->embedRenderer->getSsiMarkup($embederator_type, $embederator, $settings);
    }
    else {
      $markup = $this->embedRenderer->getEmbedMarkup($embederator_type, $embederator, $settings);
    }

    // Attach iframe contentwindow listener.
    $loadstyle = $settings['loadstyle'] ?? '';
    if ($loadstyle == 'iframe') {
      $base_url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
      $markup .= "\n" . '<script src="' . $base_url . drupal_get_path('module', 'embederator') . '/js/iframeResizer.contentWindow.min.js"></script>';
    }

    $build = $this->embedRenderer->generateElement($markup);
    $rendered = $this->renderer->renderRoot($build);

    $response = new Response();
    $response->setContent($rendered);
    return $response;
  }

}
