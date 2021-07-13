<?php

namespace Drupal\nomad\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extending ControllerBase for creating our form.
 */
class NomadadminController extends ControllerBase {
  /**
   * Marking variable for dependency injection use.
   *
   * @var \Component\DependencyInjection\ContainerInterface
   */
  protected $formbuild;

  /**
   * Using form-builder to create a from pulled with dependency injection.
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formbuild = $container->get('form_builder');
    return $instance;
  }

  /**
   * Getting before created form, from Nomadform.php.
   */
  public function myform() {
    $nomadform = $this->formbuild->getForm('Drupal\nomad\Form\NomadStructureGests');
    return $nomadform;
  }

  /**
   * Created function for load info from database.
   */
  public function report() {

    $content['form'] = $this->myform();

    return [
      '#theme' => 'nomad_admin_gest_list',
      '#form' => $content['form'],
      '#title' => $this->t("Hello! You can add here a photo of your cat."),
      '#markup' => $this->t('Below is a list af all pets that taking part in competition of domestic cats'),
    ];
  }

}
