<?php

namespace Drupal\nomad\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class NomadConfirmDel extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * Using build form function to get id from url by slug.
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit delete function, to confirm delete of the selected row.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Creating database connection and select all the images and avatars
    // from database for check and delete.
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->condition('id', $this->id);
    $select->fields('r', ['image', 'avatar']);
    $file = $select->execute()->fetchAll();
    $img = json_decode(json_encode($file), TRUE);
    // Create check to figure out if the avatar or image where set by user,
    // in order to delete managed file and all dependencies when deleting row,
    // with idea to prevent database store unused trash images.
    if (isset($img[0]['avatar']) && !($img[0]['avatar'] == '0')) {
      $select->condition('image', $img[0]['avatar']);
      $filemanaged = File::load($img[0]['avatar']);
      $filemanaged->delete();
    }
    if (isset($img[0]['image']) && !($img[0]['image'] == '0')) {
      $select->condition('image', $img[0]['image']);
      $filemanaged = File::load($img[0]['image']);
      $filemanaged->delete();
      $query = \Drupal::database()->delete('nomad');
      $query->condition('id', $this->id);
      $query->execute();
      \Drupal::messenger()->addMessage($this->t("Selected gest has been removed successfully."), 'status', TRUE);
    }
    // If both image and avatar is not set by user,
    // delete only row from database.
    else {
      $query = \Drupal::database()->delete('nomad');
      $query->condition('id', $this->id);
      $query->execute();
      \Drupal::messenger()->addMessage($this->t("Selected gest has been removed successfully."), 'status', TRUE);
    }
  }

  /**
   * Contains form created machine id.
   */
  public function getFormId() : string {
    return "confirm_delete_form";
  }

  /**
   * Function for return to module main page after cancel delete of a row.
   */
  public function getCancelUrl() {
    return new Url('nomad.content');
  }

  /**
   * Returns text that will be seen by admin when deleting row.
   */
  public function getQuestion() {
    return $this->t('Do you want to delete gest?');
  }

}
