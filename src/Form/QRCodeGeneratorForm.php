<?php

namespace Drupal\qrcode\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\qrcode\Service\QRCodeGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for generating QR codes.
 */
class QRCodeGeneratorForm extends FormBase {

  /**
   * The QR Code generator service.
   *
   * @var \Drupal\qrcode\Service\QRCodeGenerator
   */
  protected $qrcodeGenerator;

  /**
   * Constructs a QRCodeGeneratorForm object.
   *
   * @param \Drupal\qrcode\Service\QRCodeGenerator $qrcode_generator
   *   The QR Code generator service.
   */
  public function __construct(QRCodeGenerator $qrcode_generator) {
    $this->qrcodeGenerator = $qrcode_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('qrcode.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'qrcode_generator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'qrcode/qrcode.admin';

    $form['content'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('QR Code Content'),
    ];

    $form['content']['contents'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Contents'),
      '#description' => $this->t('Enter the text, URL, or other content to encode in the QR code.'),
      '#required' => TRUE,
      '#rows' => 3,
      '#placeholder' => 'https://example.com',
    ];

    $form['styling'] = [
      '#type' => 'details',
      '#title' => $this->t('Styling Options'),
      '#open' => FALSE,
    ];

    $form['styling']['module_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Module Color'),
      '#description' => $this->t('Color of the QR code modules (dark squares).'),
      '#default_value' => '#000000',
    ];

    $form['styling']['position_ring_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position Ring Color'),
      '#description' => $this->t('Color of the position indicator rings.'),
      '#default_value' => '#000000',
    ];

    $form['styling']['position_center_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position Center Color'),
      '#description' => $this->t('Color of the position indicator centers.'),
      '#default_value' => '#000000',
    ];

    $form['styling']['background_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background Color'),
      '#description' => $this->t('Background color of the QR code.'),
      '#default_value' => '#ffffff',
    ];

    $form['sizing'] = [
      '#type' => 'details',
      '#title' => $this->t('Size & Layout'),
      '#open' => FALSE,
    ];

    $form['sizing']['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Width of the QR code (e.g., 200px, 10em).'),
      '#default_value' => '200px',
      '#size' => 20,
    ];

    $form['sizing']['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Height of the QR code (e.g., 200px, 10em).'),
      '#default_value' => '200px',
      '#size' => 20,
    ];

    $form['sizing']['mask_x_to_y_ratio'] = [
      '#type' => 'number',
      '#title' => $this->t('Mask X to Y Ratio'),
      '#description' => $this->t('Aspect ratio for the QR code mask.'),
      '#default_value' => 1,
      '#min' => 0.1,
      '#max' => 10,
      '#step' => 0.1,
    ];

    $form['animation'] = [
      '#type' => 'details',
      '#title' => $this->t('Animation'),
      '#open' => FALSE,
    ];

    $form['animation']['animation_preset'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation Preset'),
      '#description' => $this->t('Choose an animation preset for the QR code.'),
      '#options' => $this->qrcodeGenerator->getAnimationPresets(),
      '#default_value' => '',
    ];

    $form['icon'] = [
      '#type' => 'details',
      '#title' => $this->t('Icon'),
      '#open' => FALSE,
    ];

    $form['icon']['icon_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon Path'),
      '#description' => $this->t('Path to the icon file to display in the center of QR codes. Leave empty to use the default icon (assets/icon.png). Path should be relative to the site root and must start with a leading slash (e.g., /sites/default/files/my-icon.png).'),
      '#default_value' => '',
      '#placeholder' => '/sites/default/files/icon.png',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate QR Code'),
      '#ajax' => [
        'callback' => '::generateQrCodeAjax',
        'wrapper' => 'qr-code-result',
        'effect' => 'fade',
      ],
    ];

    // Container for the generated QR code.
    $form['result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'qr-code-result'],
    ];

    // If form has been submitted, show the QR code.
    if ($form_state->getUserInput() && !empty($form_state->getValue('contents'))) {
      $options = [
        'module_color' => $form_state->getValue('module_color', '#000000'),
        'position_ring_color' => $form_state->getValue('position_ring_color', '#000000'),
        'position_center_color' => $form_state->getValue('position_center_color', '#000000'),
        'background_color' => $form_state->getValue('background_color', '#ffffff'),
        'width' => $form_state->getValue('width', '200px'),
        'height' => $form_state->getValue('height', '200px'),
        'mask_x_to_y_ratio' => $form_state->getValue('mask_x_to_y_ratio', 1),
        'animation' => $form_state->getValue('animation_preset', ''),
        'icon' => $form_state->getValue('icon_path', ''),
      ];

      $form['result']['qr_code'] = $this->qrcodeGenerator->generateQRCode(
        $form_state->getValue('contents'),
        $options
      );

      $form['result']['download_info'] = [
        '#type' => 'markup',
        '#markup' => '<div class="qr-code-download-info"><p>' .
        $this->t('Right-click on the QR code and select "Save image as..." to download it.') .
        '</p></div>',
      ];
    }

    return $form;
  }

  /**
   * Ajax callback for generating QR code.
   */
  public function generateQrCodeAjax(array &$form, FormStateInterface $form_state) {
    return $form['result'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $contents = $form_state->getValue('contents');

    // Validate contents.
    $errors = $this->qrcodeGenerator->validateContents($contents);
    if (!empty($errors)) {
      foreach ($errors as $error) {
        $form_state->setErrorByName('contents', $error);
      }
    }

    // Validate colors.
    $color_fields = [
      'module_color', 'position_ring_color',
      'position_center_color', 'background_color',
    ];

    foreach ($color_fields as $field) {
      $value = $form_state->getValue($field);
      if (!empty($value) && !$this->qrcodeGenerator->validateColor($value)) {
        $form_state->setErrorByName($field, $this->t('Invalid color format for @field.', ['@field' => $field]));
      }
    }

    // Validate dimensions.
    $dimension_fields = ['width', 'height'];
    foreach ($dimension_fields as $field) {
      $value = $form_state->getValue($field);
      if (!empty($value) && !preg_match('/^[\d.]+\s*(px|em|rem|%|vh|vw)$/', $value)) {
        $form_state->setErrorByName($field, $this->t('Invalid dimension format for @field. Use units like px, em, rem, %, vh, or vw.', ['@field' => $field]));
      }
    }

    // Validate icon path if provided.
    $icon_path = $form_state->getValue('icon_path');
    if (!empty($icon_path)) {
      // Check if it's a URL or a file path.
      if (!filter_var($icon_path, FILTER_VALIDATE_URL)) {
        // For site root paths, check if they start with a slash and if file exists.
        if (!str_starts_with($icon_path, '/')) {
          $form_state->setErrorByName('icon_path', $this->t('Icon path must start with a leading slash (e.g., /sites/default/files/icon.png).'));
        } else {
          $full_path = DRUPAL_ROOT . $icon_path;
          if (!file_exists($full_path)) {
            $form_state->setErrorByName('icon_path', $this->t('Icon file not found: @path', ['@path' => $icon_path]));
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form submission is handled by AJAX callback
    // This method is required by FormBase but can be empty for AJAX forms.
  }

}
