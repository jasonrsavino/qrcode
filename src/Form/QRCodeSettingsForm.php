<?php

namespace Drupal\qrcode\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\qrcode\Service\QRCodeGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a configuration form for QR Code settings.
 */
class QRCodeSettingsForm extends ConfigFormBase {

  /**
   * The QR Code generator service.
   *
   * @var \Drupal\qrcode\Service\QRCodeGenerator
   */
  protected $qrcodeGenerator;

  /**
   * Constructs a QRCodeSettingsForm object.
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
  protected function getEditableConfigNames() {
    return ['qrcode.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'qrcode_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('qrcode.settings');

    $form['defaults'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Default Settings'),
      '#description' => $this->t('These settings will be used as defaults when generating QR codes.'),
    ];

    $form['defaults']['default_module_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Module Color'),
      '#description' => $this->t('Default color for QR code modules (the dark squares).'),
      '#default_value' => $config->get('default_module_color') ?? '#000000',
    ];

    $form['defaults']['default_position_ring_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position Ring Color'),
      '#description' => $this->t('Default color for position indicator rings.'),
      '#default_value' => $config->get('default_position_ring_color') ?? '#000000',
    ];

    $form['defaults']['default_position_center_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position Center Color'),
      '#description' => $this->t('Default color for position indicator centers.'),
      '#default_value' => $config->get('default_position_center_color') ?? '#000000',
    ];

    $form['defaults']['default_background_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background Color'),
      '#description' => $this->t('Default background color for QR codes.'),
      '#default_value' => $config->get('default_background_color') ?? '#ffffff',
    ];

    $form['defaults']['default_animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Animation'),
      '#description' => $this->t('Default animation preset for QR codes.'),
      '#options' => $this->qrcodeGenerator->getAnimationPresets(),
      '#default_value' => $config->get('default_animation') ?? '',
    ];

    $form['defaults']['sizing'] = [
      '#type' => 'details',
      '#title' => $this->t('Size Settings'),
    ];

    $form['defaults']['sizing']['default_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Width'),
      '#description' => $this->t('Default width for QR codes (e.g., 200px, 10em).'),
      '#default_value' => $config->get('default_width') ?? '200px',
      '#size' => 20,
    ];

    $form['defaults']['sizing']['default_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Height'),
      '#description' => $this->t('Default height for QR codes (e.g., 200px, 10em).'),
      '#default_value' => $config->get('default_height') ?? '200px',
      '#size' => 20,
    ];

    $form['defaults']['sizing']['default_mask_x_to_y_ratio'] = [
      '#type' => 'number',
      '#title' => $this->t('Mask X to Y Ratio'),
      '#description' => $this->t('Default aspect ratio for the QR code mask.'),
      '#default_value' => $config->get('default_mask_x_to_y_ratio') ?? 1,
      '#min' => 0.1,
      '#max' => 10,
      '#step' => 0.1,
    ];

    // Preview section.
    $form['preview'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Preview'),
      '#description' => $this->t('Preview of QR code with current settings.'),
    ];

    $preview_options = [
      'module_color' => $form['defaults']['default_module_color']['#default_value'],
      'position_ring_color' => $form['defaults']['default_position_ring_color']['#default_value'],
      'position_center_color' => $form['defaults']['default_position_center_color']['#default_value'],
      'background_color' => $form['defaults']['default_background_color']['#default_value'],
      'animation' => $form['defaults']['default_animation']['#default_value'],
      'width' => $form['defaults']['sizing']['default_width']['#default_value'],
      'height' => $form['defaults']['sizing']['default_height']['#default_value'],
      'mask_x_to_y_ratio' => $form['defaults']['sizing']['default_mask_x_to_y_ratio']['#default_value'],
    ];

    $form['preview']['qr_preview'] = $this->qrcodeGenerator->generateQRCode(
      'https://drupal.org/project/qrcode',
      $preview_options
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate colors.
    $color_fields = [
      'default_module_color',
      'default_position_ring_color',
      'default_position_center_color',
      'default_background_color',
    ];

    foreach ($color_fields as $field) {
      $value = $form_state->getValue($field);
      if (!$this->qrcodeGenerator->validateColor($value)) {
        $form_state->setErrorByName($field, $this->t('Invalid color format for @field.', ['@field' => $field]));
      }
    }

    // Validate dimensions.
    $dimension_fields = ['default_width', 'default_height'];
    foreach ($dimension_fields as $field) {
      $value = $form_state->getValue($field);
      if (!preg_match('/^[\d.]+\s*(px|em|rem|%|vh|vw)$/', $value)) {
        $form_state->setErrorByName($field, $this->t('Invalid dimension format for @field. Use units like px, em, rem, %, vh, or vw.', ['@field' => $field]));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('qrcode.settings')
      ->set('default_module_color', $form_state->getValue('default_module_color'))
      ->set('default_position_ring_color', $form_state->getValue('default_position_ring_color'))
      ->set('default_position_center_color', $form_state->getValue('default_position_center_color'))
      ->set('default_background_color', $form_state->getValue('default_background_color'))
      ->set('default_animation', $form_state->getValue('default_animation'))
      ->set('default_width', $form_state->getValue('default_width'))
      ->set('default_height', $form_state->getValue('default_height'))
      ->set('default_mask_x_to_y_ratio', $form_state->getValue('default_mask_x_to_y_ratio'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
