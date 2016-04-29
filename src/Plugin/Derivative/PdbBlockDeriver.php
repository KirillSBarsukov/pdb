<?php

/**
 * @file
 * Contains \Drupal\pdb\Plugin\Derivative\PdbBlockDeriver.
 */

namespace Drupal\pdb\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\pdb\ComponentDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a deriver for pdb blocks.
 */
class PdbBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The component discovery service.
   *
   * @var \Drupal\pdb\ComponentDiscoveryInterface
   */
  protected $componentDiscovery;

  /**
   * PdbBlockDeriver constructor.
   *
   * @param \Drupal\pdb\ComponentDiscoveryInterface $component_discovery
   *   The component discovery service.
   */
  public function __construct(ComponentDiscoveryInterface $component_discovery, ModuleHandlerInterface $module_handler, InfoParserInterface $info_parser) {
    $this->componentDiscovery = $component_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('pdb.component_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Get all custom blocks which should be rediscovered.
    $components = $this->componentDiscovery->getComponents();
    foreach ($components as $block_id => $block_info) {
      $this->derivatives[$block_id] = $base_plugin_definition;
      $this->derivatives[$block_id]['info'] = $block_info->info;
      $this->derivatives[$block_id]['admin_label'] = $block_info->info['name'];
      $this->derivatives[$block_id]['cache'] = DRUPAL_NO_CACHE;
      if (isset($block_info->info['contexts'])) {
        $this->derivatives[$block_id]['context'] = $this->createContexts($block_info->info['contexts']);
      }
    }
    return $this->derivatives;
  }

  /**
   * @param array $contexts
   *   Contexts as defined in component label.
   *
   * @return \Drupal\Core\Plugin\Context\ContextDefinition[]
   *   Array of context to be used by block module
   *   @todo where is this defined in block module
   */
  protected function createContexts(array $contexts) {
    $contextsDefinitions = [];
    if (isset($contexts['entity'])) {
      // @todo Check entity type exists and fail!
      $contextsDefinitions['entity'] = new ContextDefinition('entity:' . $contexts['entity']);
    }
    // @todo Dynamically handle unknown context definitions
    return $contextsDefinitions;
  }

}
