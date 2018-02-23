<?php

namespace Drupal\embederator\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\embederator\EmbederatorTypeInterface;

/**
 * Defines the embederator_type entity. A configuration entity used to manage
 * bundles for the embederator entity.
 *
 * @ConfigEntityType(
 *   id = "embederator_type",
 *   label = @Translation("Embederator Type"),
 *   bundle_of = "embederator",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "embederator_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "embed_markup"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\embederator\Entity\Controller\EmbederatorTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\embederator\Form\EmbederatorTypeForm",
 *       "add" = "Drupal\embederator\Form\EmbederatorTypeForm",
 *       "edit" = "Drupal\embederator\Form\EmbederatorTypeForm",
 *       "delete" = "Drupal\embederator\Form\EmbederatorTypeDeleteForm",
 *     }
 *   },
 *   admin_permission = "administer embederator types",
 *   links = {
 *     "canonical" = "/admin/structure/embederator_type/{embederator_type}",
 *     "add-form" = "/admin/structure/embederator_type/add",
 *     "edit-form" = "/admin/structure/embederator_type/{embederator_type}/edit",
 *     "delete-form" = "/admin/structure/embederator_type/{embederator_type}/delete",
 *     "collection" = "/admin/structure/embederator_type",
 *   }
 * )
 */
class EmbederatorType extends ConfigEntityBundleBase implements EmbederatorTypeInterface {
  /**
   * The machine name of the practical type.
   *
   * @var string
   */
  protected $id;
  /**
   * The human-readable name of the practical type.
   *
   * @var string
   */
  protected $label;

  
  /**
   * A brief description of the practical type.
   *
   * @var string
   */
  protected $description;

  /**
   * Markup skeleton for the embed.
   *
   * @var string
   */
  protected $embed_markup;
  
  
  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }
  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  public function getMarkup() {
      return $this->embed_markup;
  }

  public function setMarkup($markup) {
      $this->embed_markup = $markup;
      return $this;
  }
}