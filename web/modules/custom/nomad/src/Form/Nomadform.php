<?php

namespace Drupal\nomad\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Contains form created in order to create list of gests, that leave comments.
 */
class Nomadform extends FormBase {

  /**
   * Contains form created in order to create list of gests.
   */
  public function getFormId() {
    return 'nomad_name_form';
  }

  /**
   * Using build form function to create.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['avatar'] = [
      '#title' => t('You can add your avatar here:'),
      '#type' => 'managed_file',
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
      '#size' => 101,
      '#description' => t("Your name, must contain at least 2 characters and maximum length is 100 characters, and
       can not contain any numbers, whitespaces, and symbols."),
      '#required' => TRUE,
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
      '#ajax' => [
        'callback' => '::validateNumberAjax',
        'event' => 'change',
      ],
    ];
    $form['feedback'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Feedback:'),
      '#required' => TRUE,
      '#size' => 10000,
      '#description' => t("Share your opinion with us."),
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
      '#required' => FALSE,
    ];
    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add comment'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click',
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
        "<div class='alert alert-dismissible fade show alert-danger'>The name field is required.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
    </div>"));
    }
    elseif (!preg_match('/^[A-Za-z]*$/', $value) || strlen($value) < 2 || strlen($value) > 100) {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-danger'>The name $value is not valid.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
    </div>"));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-success'>The name $value is correct.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
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
<div class='alert alert-dismissible fade show alert-danger'>Email field is required.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
    </div>"));
    }
    elseif (!preg_match('/[#$%^&*()+=!\[\]\';,\/{}|":<>?~\\\\0-9]/', $emailvalue) &&
      filter_var($emailvalue, FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-success'>Email $emailvalue is correct.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
</div>"));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages',
        "<div class='alert alert-dismissible fade show alert-danger'>Email $emailvalue is not valid.
<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
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
   * Adding ajax form submit for form.
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => $this->messenger()->all(),
      '#status_headings' => [
        'status' => t('Status message'),
        'error' => t('Error message'),
        'warning' => t('Warning message'),
      ],
    ];
    $messages = \Drupal::service('renderer')->render($message);
    $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    $url = Url::fromRoute('nomad.content');
    $command = new RedirectCommand($url->toString());
    $ajax_response->addCommand($command);
    return $ajax_response;
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $image = $form_state->getValue('image');
    $avatar = $form_state->getValue('avatar');
    if (!($avatar == NULL)) {
      $fileava = File::load($avatar[0]);
      $fileava->setPermanent();
      $fileava->save();
    }
    else {
      $form_state->setValue('avatar', ['0']);
    }
    if (!($image == NULL)) {
      $fileimg = File::load($image[0]);
      $fileimg->setPermanent();
      $fileimg->save();
    }
    else {
      $form_state->setValue('image', ['0']);
    }
    $data = \Drupal::service('database')->insert('nomad')
      ->fields([
        'avatar' => $form_state->getValue('avatar')[0],
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone_number' => $form_state->getValue('phone_number'),
        'created' => time(),
        'feedback' => $form_state->getValue('feedback'),
        'image' => $form_state->getValue('image')[0],
      ])

      ->execute();

    \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
  }

}
