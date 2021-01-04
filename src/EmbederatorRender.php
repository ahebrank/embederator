<?php

namespace Drupal\embederator;

use GuzzleHttp\Client;
use Drupal\Core\Utility\Token;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Embed rendering functions.
 */
class EmbederatorRender {

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Token handling.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(Client $client, Token $token, ModuleHandlerInterface $module_handler) {
    $this->client = $client;
    $this->token = $token;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('token'),
      $container->get('module_handler')
    );
  }

  /**
   * Return markup for simple embed.
   */
  public function getEmbedMarkup($embederator_type, $entity, $settings) {
    $embed_pattern_field = $embederator_type->getMarkup();
    $markup = $this->token->replace($embed_pattern_field['value'], ['embederator' => $entity]);
    $context = [
      'embederator_type' => $embederator_type,
      'entity' => $entity,
      'settings' => $settings,
    ];

    $this->moduleHandler->alter('embederator_embed', $markup, $context);

    return $markup;
  }

  /**
   * Return server-side include markup.
   */
  public function getSsiMarkup($embederator_type, $entity, $settings) {
    $url_pattern = $embederator_type->getEmbedUrl();
    $url = $this->token->replace($url_pattern, ['embederator' => $entity]);
    $context = [
      'embederator_type' => $embederator_type,
      'entity' => $entity,
      'settings' => $settings,
    ];

    $this->moduleHandler->alter('embederator_url', $url, $context);

    try {
      $response = $this->client->request('GET', $url);
      $markup = (string) $response->getBody();
    }
    catch (Exception $e) {
      $markup = '<p>Unable to load ' . $url . '</p>';
    }

    $this->moduleHandler->alter('embederator_embed', $markup, $context);

    return $markup;
  }

  /**
   * Generate markup.
   */
  public function generateElement($markup) {
    return [
      '#type' => 'processed_text',
      '#text' => $markup,
      '#format' => 'full_html',
    ];
  }

}
