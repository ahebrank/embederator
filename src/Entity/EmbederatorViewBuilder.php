<?php

namespace Drupal\embederator\Entity;

use Drupal\Core\Entity\EntityViewBuilder;

class EmbederatorViewBuilder extends EntityViewBuilder {
    /**
    * {@inheritdoc}
    */
    public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {

        /** @var \Drupal\embederator\EmbederatorInterface[] $entities */
        if (empty($entities)) {
            return;
        }
        parent::buildComponents($build, $entities, $displays, $view_mode);

        $token_service = \Drupal::token();
        $viewBuilder = $this->entityManager->getViewBuilder('embederator_type');

        foreach ($entities as $id => $entity) {
            /**
            * The embederator type.
            *
            * @var \Drupal\embederator\Entity\EmbederatorType
            */
            $embederator_type =  $this->entityManager->getStorage('embederator_type')->load($entity->bundle());
             // find/replace on the markup
            $field = $embederator_type->getMarkup();
            $markup = $token_service->replace($field['value'], array('embederator' => $entity));

            // inject as processed text
            $build[$id]['markup'] = [
                '#type' => 'processed_text',
                '#text' => $markup,
                '#format' => $field['format'],
            ];
        }
    }
}