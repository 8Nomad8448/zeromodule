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
    // Creatin database connection and select all the images from database
    // in order to create check(count) how many rows using image with same file
    // id.
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->condition('id', $this->id);
    $select->fields('r', ['image']);
    $file = $select->execute()->fetchAll();
    $img = json_decode(json_encode($file), TRUE);
    if (isset($img[0]['image'])) {
      $select->condition('image', $img[0]['image']);
      $filemanaged = File::load($img[0]['image']);
      $select = $db->select('nomad', 'r');
      $select->condition('image', $img[0]['image']);
      $select->fields('r', ['image']);
      $check = $select->execute()->fetchAll();
      $check = count($check);
      // If current fid used in table more than once than deleting only row but
      // not managed file and it's dependencies.
      if ($check == 1) {
        $filemanaged->delete();
      }
      $query = \Drupal::database()->delete('nomad');
      $query->condition('id', $this->id);
      $query->execute();
      \Drupal::messenger()->addMessage($this->t("Selected gest has been removed successfully."), 'status', TRUE);
    }
    else {
      \Drupal::messenger()->addMessage($this->t("Selected gest has been removed already."), 'status', TRUE);
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
