<?php

namespace Drupal\nomad\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contains form created in order to change information in the book of gests.
 */
class Nomadformupdate extends FormBase {

  /**
   * Contains form machine id in order to create form of gests.
   */
  public function getFormId() {
    return 'nomad_update_form';
  }

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * Using build form function to create form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    // In build form create DB connection and fetch all values
    // of chosen row by id, in order to create comfortable defaults for user.
    $this->id = $id;
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->condition('id', $this->id);
    $select->fields('r', ['avatar', 'name', 'email', 'phone_number',
      'feedback', 'image', 'id',
    ]);
    $file = $select->execute()->fetchAll();
    $loaded = json_decode(json_encode($file), TRUE);
    $form['avatar'] = [
      '#title' => t('You can add your avatar here:'),
      '#type' => 'managed_file',
      '#default_value' => [$loaded[0]['avatar']],
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
        '#required' => TRUE,
      ],
      '#description' => t("The avatar image size must be less than 2MB."),
      '#upload_location' => 'public://photos',
      '#required' => FALSE,
    ];
    $form['name'] = [
      '#title' => t("Name:"),
      '#type' => 'textfield',
      '#size' => 100,
      '#description' => t("Your name, must contain at least 2 characters and maximum length is 100 characters, and
       can not contain any numbers, whitespaces, and symbols."),
      '#required' => TRUE,
      '#default_value' => $loaded[0]['name'],
      '#ajax' => [
        'callback' => '::validateNameAjax',
        'event' => 'change',
      ],
    ];
    $form['email'] = [
      '#title' => t('Email:'),
      '#type' => 'email',
      '#required' => TRUE,
      '#description' => t("Your email can contain only latin alphabet letters, 'at' sign, dash sign, underscore
       sign, and dots."),
      '#default_value' => $loaded[0]['email'],
      '#ajax' => [
        'callback' => '::validateEmailAjax',
        'event' => 'change',
      ],
    ];
    $form['phone_number'] = [
      '#title' => t('Phone number:'),
      '#type' => 'tel',
      '#size' => 15,
      '#required' => TRUE,
      '#description' => t("Your phone number can contain only numbers, and must not be longer than
       fifteen digits."),
      '#default_value' => $loaded[0]['phone_number'],
      '#ajax' => [
        'callback' => '::validateNumberAjax',
        'event' => 'change',
      ],
    ];
    $form['feedback'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Feedback:'),
      '#required' => TRUE,
      '#description' => t("Share your opinion with us."),
      '#default_value' => $loaded[0]['feedback'],
      '#ajax' => [
        'callback' => '::validateFeedbackAjax',
        'event' => 'change',
      ],
    ];
    $form['image'] = [
      '#title' => t('Add some image:'),
      '#type' => 'managed_file',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
        '#required' => TRUE,
      ],
      '#description' => t("The image size must be less than 5MB."),
      '#upload_location' => 'public://photos',
      '#default_value' => [$loaded[0]['image']],
      '#required' => FALSE,
    ];
    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Edit'),
    ];
    $form['cancel'] = [
      '#type' => 'button',
      '#value' => t('Cancel'),
      '#attributes' => [
        'onClick' => 'window.history.go(-1); return false;',
        'id' => 'reverse',
      ],
    ];
    $form['#attributes']['class'][] = 'guests_list_form';
    return $form;
  }

  /**
   * Using standart structure of build form to create validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('name');
    $emailvalue = $form_state->getValue('email');
    $phonevalue = $form_state->getValue('phone_number');
    if (!preg_match('/[A-Za-z]/', $value) || strlen($value) < 2 || strlen($value) > 100) {
      $form_state->setErrorByName('name', t('The name %name is not valid.', ['%name' => $value]));
    }
    if (filter_var($emailvalue, FILTER_VALIDATE_EMAIL) &&
      preg_match('/[#$%^&*()+=!\[\]\';,\/{}|":<>?~\\\\]/', $emailvalue)) {
      $form_state->setErrorByName('email', t('The email %email is not valid.', ['%email' => $emailvalue]));
    }
    if (!preg_match('/[0-9]/', $phonevalue)) {
      $form_state->setErrorByName('phone_number', t('The number %phone_number is not valid.', ['%phone_number' => $phonevalue]));
    }
    else {
      $this->messenger()->deleteAll();
    }
  }

  /**
   * Creating ajax validation for name field of form.
   */
  public function validateNameAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $value = $form_state->getValue('name');
    if ($value == '') {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='messages messages--error'>The name field is required.
    </div>"));
    }
    elseif (!preg_match('/^[A-Za-z]*$/', $value) || strlen($value) < 2 || strlen($value) > 100) {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='messages messages--error'>The name $value is not valid.
    </div>"));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='messages messages--status'>The name $value is correct.
</div>"));
      $this->messenger()->deleteAll();
    }
    return $response;
  }

  /**
   * Creating ajax validation for email field of form.
   */
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $emailvalue = $form_state->getValue('email');
    if ($emailvalue == '') {
      $response->addCommand(new HtmlCommand('#form-system-messages', "
<div class='messages messages--error'>Email field is required.
    </div>"));
    }
    elseif (!preg_match('/[#$%^&*()+=!\[\]\';,\/{}|":<>?~\\\\0-9]/', $emailvalue) &&
      filter_var($emailvalue, FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='messages messages--status'>Email $emailvalue is correct.
</div>"));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='messages messages--error'>Email $emailvalue is not valid.
    </div>"));
      $this->messenger()->deleteAll();
    }
    return $response;
  }

  /**
   * Creating ajax validation for phone number field of form.
   */
  public function validateNumberAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $phonevalue = $form_state->getValue('phone_number');
    if ($phonevalue == '') {
      $response->addCommand(new HtmlCommand('#form-system-messages', "
<div class='alert alert-dismissible fade show alert-danger'>The number field is required.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
    </div>"));
    }
    elseif (preg_match('/[0-9]/', $phonevalue)) {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-success'>The number $phonevalue is correct.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
</div>"));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-danger'>The number $phonevalue is not valid.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
    </div>"));
      $this->messenger()->deleteAll();
    }
    return $response;
  }

  /**
   * Creating ajax validation for form comment field.
   */
  public function validateFeedbackAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $feedbackvalue = $form_state->getValue('feedback');
    if ($feedbackvalue == '') {
      $response->addCommand(new HtmlCommand('#form-system-messages', "
<div class='alert alert-dismissible fade show alert-danger'>The comment field is required.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
    </div>"));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-success'>Thanks for adding your comment for us.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
</div>"));
      $this->messenger()->deleteAll();
    }
    return $response;
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // In order to delete files after changing data in
    // database table fetching chosen file id.
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->condition('id', $this->id);
    $select->fields('r', ['image']);
    $file = $select->execute()->fetchAll();
    $img = json_decode(json_encode($file), TRUE);
    $pick = $db->select('nomad', 'r');
    $pick->condition('id', $this->id);
    $pick->fields('r', ['avatar']);
    $file_avatar = $pick->execute()->fetchAll();
    $img_avatar = json_decode(json_encode($file_avatar), TRUE);
    // Performing update query to update info stored in database
    // based on fid.
    $image = $form_state->getValue('image');
    $image_fid = $image[0] ?? 0;
    if (!($image == NULL)) {
      $file = File::load($image_fid);
      $file->setPermanent();
      $file->save();
    }
    else {
      $form_state->setValue('image', ['0']);
    }
    $icon_avatar = $form_state->getValue('avatar');
    $icon_fid = $icon_avatar[0] ?? 0;
    if (!($icon_avatar == NULL)) {
      $icon = File::load($icon_fid);
      $icon->setPermanent();
      $icon->save();
    }
    else {
      $form_state->setValue('avatar', ['0']);
    }
    $query = \Drupal::database()->update('nomad')
      ->fields([
        'avatar' => $form_state->getValue('avatar')[0],
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone_number' => $form_state->getValue('phone_number'),
        'feedback' => $form_state->getValue('feedback'),
        'image' => $form_state->getValue('image')[0],
      ]);
    $query->condition('id', $this->id);
    $query->execute();
    // Check avatar and image values for they where set by user,
    // or it was set by default in order to delete old ones
    // if they where updated, to prevent storing trash.
    $filemanaged_fid = $img[0]['image'];
    $filemanaged = File::load($filemanaged_fid);
    if (!($filemanaged == NULL) && !($filemanaged_fid == $image_fid)) {
      $filemanaged->delete();
    }
    $img_avatar_fid = $img_avatar[0]['avatar'];
    $filemanaged_avatar = File::load($img_avatar_fid);
    if (!($filemanaged_avatar == NULL) && !($img_avatar_fid == $icon_fid)) {
      $filemanaged_avatar->delete();
    }
    \Drupal::messenger()->addMessage($this->t('Form changed successfully'), 'status', TRUE);
  }

}
