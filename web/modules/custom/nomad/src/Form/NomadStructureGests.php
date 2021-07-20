<?php

namespace Drupal\nomad\Form;

use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Contains form created in order to create list of cats, taking part in event.
 */
class NomadStructureGests extends FormBase {

  /**
   * Contains form created in order to create list of cats for in event.
   */
  public function getFormId() {
    return 'nomad_gests_form';
  }

  /**
   * Using build form function to create form for admin panel.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Getting destination array to create redirect.
    $value = $this->getDestinationArray();
    $root = $value['destination'];
    // Create connection, select the specific fields for the output.
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->fields('r', ['avatar', 'name', 'email',
      'phone_number', 'created', 'feedback',
      'image', 'id',
    ]);
    $select->orderBy('created', 'DESC');
    $entries = $select->execute()->fetchall();
    // Create function for load info from database.
    $content = [];
    $contents = $entries;
    $content['message'] = [
      '#markup' => $this->t('Below is a list af all gest that leaved their comments'),
    ];
    $headers = [
      t('avatar'),
      t('name'),
      t('email'),
      t('phone'),
      t('created'),
      t('comment'),
      t('image'),
      t('delete'),
      t('update'),
    ];
    // Encode stdClass to make it associative array.
    $rows = json_decode(json_encode($contents), TRUE);
    $tablerows = [];
    foreach ($rows as $entry) {
      $rowid = $entry['id'];
      // Adding fields for delete info in database.
      $form['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromUserInput("/admin/nomad/delete/$rowid?destination=$root"),
        '#attributes' => [
          'data-dialog-type' => 'modal',
          'class' => 'use-ajax button form-submit',
        ],
      ];
      // Adding fields for update info in database.
      $form['update'] = [
        '#type' => 'link',
        '#title' => $this->t('Update'),
        '#url' => Url::fromUserInput("/admin/nomad/update/$rowid?destination=$root"),
        '#attributes' => [
          'data-dialog-type' => 'modal',
          'class' => 'use-ajax button form-submit',
        ],
      ];
      $delete = \Drupal::service('renderer')->render($form['delete']);
      $update = \Drupal::service('renderer')->render($form['update']);
      // Check if there was uploaded avatar and image by user,
      // if not use defaults.
      $img_file = File::load($entry['image']);
      if (!$img_file == NULL) {
        $img_uri = $img_file->getFileUri();
        $image_variables = [
          '#theme' => 'image_style',
          '#style_name' => 'medium',
          '#alt' => "User's chosen image",
          '#title' => "User's chosen image",
          '#uri' => $img_uri,
        ];
      }
      else {
        $image_variables = [
          '#theme' => 'image_style',
          '#style_name' => 'medium',
          '#alt' => "User's chosen image",
          '#title' => "User's chosen image",
          '#uri' => '',
        ];
      }
      $avatar_file = File::load($entry['avatar']);
      if (!$avatar_file == NULL) {
        $avatar_uri = $avatar_file->getFileUri();
        $avatar_variables = [
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#alt' => "User's chosen image",
          '#title' => "User's chosen image",
          '#uri' => $avatar_uri,
        ];
      }
      else {
        $avatar_variables = [
          '#theme' => 'image',
          '#alt' => "User's chosen image",
          '#title' => "User's chosen image",
          '#style_name' => 'thumbnail',
          '#width' => '100px',
          '#height' => 'auto',
          '#uri' => '/' . drupal_get_path('module', 'nomad') . "/photos/default_avatar.jpg",
        ];
      }
      $gests_image = \Drupal::service('renderer')->render($image_variables);
      $gests_avatar = \Drupal::service('renderer')->render($avatar_variables);
      // Create render array(index not associative) for table select.
      $render = [];
      $date = date('d/m/Y H:i:s', $entry['created']);
      array_push($render, $gests_avatar, $entry['name'], $entry['email'],
        $entry['phone_number'], $date, $entry['feedback'], $gests_image,
        $delete, $update, $rowid);
      array_push($tablerows, $render);
    }
    // Create table with type table set in order to render all database out
    // put in table style with checkboxes.
    $content['table'] = [
      '#type' => 'tableselect',
      '#header' => $headers,
      '#options' => $tablerows,
      '#empty' => t('No entries available.'),
    ];
    $content['modal'] = [
      '#theme' => 'nomad_admin_modal',
    ];
    $content['#attached']['library'][] = 'nomad/gests-style';
    // Adding cancel button to abort send of all checked rows.
    $content['cancel'] = [
      '#type' => 'button',
      '#value' => t('Cancel'),
      '#attributes' => [
        'id' => 'abort',
      ],
    ];
    // Adding submit button to send all checked rows.
    $content['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Remove checked'),
      '#attributes' => [
        'id' => 'rem_multiple',
      ],
    ];
    return $content;
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Taking form indexes that returned when administrator
    // wants to delete multiple rows and taking id from fields
    // with it.
    $index = $form['table']['#value'];
    foreach ($index as $checked) {
      $id = $form['table']['#options'][$checked][9];
      // Creating database connection and select all the images and avatars
      // from database for check and delete.
      $db = \Drupal::service('database');
      $select = $db->select('nomad', 'r');
      $select->condition('id', $id);
      $select->fields('r', ['image']);
      $pick = $db->select('nomad', 'r');
      $pick->condition('id', $id);
      $pick->fields('r', ['avatar']);
      $file = $select->execute()->fetchAll();
      $icon_file = $pick->execute()->fetchAll();
      $img = json_decode(json_encode($file), TRUE);
      $avatar_icon = json_decode(json_encode($icon_file), TRUE);
      // Create check to figure out if the avatar or image where set by user,
      // in order to delete managed file and all dependencies when deleting row,
      // with idea to prevent database store unused trash images.
      if (isset($img[0]['image']) && $img[0]['image'] != 0) {
        $select->condition('image', $img[0]['image']);
        $filemanaged = File::load($img[0]['image']);
        $filemanaged->delete();
      }
      if (isset($avatar_icon[0]['avatar']) && $avatar_icon[0]['avatar'] != 0) {
        $select->condition('avatar', $avatar_icon[0]['avatar']);
        $filemanaged = File::load($avatar_icon[0]['avatar']);
        $filemanaged->delete();
        $query = \Drupal::database()->delete('nomad');
        $query->condition('id', $id);
        $query->execute();
        \Drupal::messenger()->addMessage($this->t("Selected gest has been removed successfully."), 'status', TRUE);
      }
      // If both image and avatar is not set by user,
      // delete only row from database.
      else {
        $query = \Drupal::database()->delete('nomad');
        $query->condition('id', $id);
        $query->execute();
        \Drupal::messenger()->addMessage($this->t("Selected gest has been removed successfully."), 'status', TRUE);
      }
    }

  }

}
