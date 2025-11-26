<?php

namespace Drupal\qrcode\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\qrcode\Service\QRCodeGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'qrcode' formatter.
 *
 * @FieldFormatter(
 *   id = "qrcode",
 *   label = @Translation("QR Code"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "link"
 *   }
 * )
 */
class QRCodeFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The QR Code generator service.
   *
   * @var \Drupal\qrcode\Service\QRCodeGenerator
   */
  protected $qrcodeGenerator;

  /**
   * Constructs a QRCodeFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\qrcode\Service\QRCodeGenerator $qrcode_generator
   *   The QR Code generator service.
   */
  public function __construct($plugin_id, $plugin_definition, $field_definition, array $settings, $label, $view_mode, array $third_party_settings, QRCodeGenerator $qrcode_generator) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->qrcodeGenerator = $qrcode_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('qrcode.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'module_color' => '#000000',
      'position_ring_color' => '#000000',
      'position_center_color' => '#000000',
      'background_color' => '#ffffff',
      'width' => '200px',
      'height' => '200px',
      'mask_x_to_y_ratio' => '1',
      'animation' => '',
      'icon' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['styling'] = [
      '#type' => 'details',
      '#title' => $this->t('Styling'),
      '#open' => TRUE,
    ];

    $elements['styling']['module_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Module Color'),
      '#description' => $this->t('Color of the QR code modules (dark squares).'),
      '#default_value' => $this->getSetting('module_color'),
    ];

    $elements['styling']['position_ring_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position Ring Color'),
      '#description' => $this->t('Color of the position indicator rings.'),
      '#default_value' => $this->getSetting('position_ring_color'),
    ];

    $elements['styling']['position_center_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position Center Color'),
      '#description' => $this->t('Color of the position indicator centers.'),
      '#default_value' => $this->getSetting('position_center_color'),
    ];

    $elements['styling']['background_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background Color'),
      '#description' => $this->t('Background color of the QR code.'),
      '#default_value' => $this->getSetting('background_color'),
    ];

    $elements['sizing'] = [
      '#type' => 'details',
      '#title' => $this->t('Size & Layout'),
      '#open' => FALSE,
    ];

    $elements['sizing']['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Width of the QR code (e.g., 200px, 10em).'),
      '#default_value' => $this->getSetting('width'),
      '#size' => 20,
    ];

    $elements['sizing']['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Height of the QR code (e.g., 200px, 10em).'),
      '#default_value' => $this->getSetting('height'),
      '#size' => 20,
    ];

    $elements['sizing']['mask_x_to_y_ratio'] = [
      '#type' => 'number',
      '#title' => $this->t('Mask X to Y Ratio'),
      '#description' => $this->t('Aspect ratio for the QR code mask.'),
      '#default_value' => $this->getSetting('mask_x_to_y_ratio'),
      '#min' => 0.1,
      '#max' => 10,
      '#step' => 0.1,
    ];

    $elements['animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation'),
      '#description' => $this->t('Animation preset for the QR code.'),
      '#options' => $this->qrcodeGenerator->getAnimationPresets(),
      '#default_value' => $this->getSetting('animation'),
    ];

    $elements['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon Path'),
      '#description' => $this->t('Path to the icon file to display in the center of QR codes. Leave empty to use the default icon (assets/icon.png). Path should be relative to the site root and must start with a leading slash (e.g., /sites/default/files/my-icon.png).'),
      '#default_value' => $this->getSetting('icon'),
      '#placeholder' => '/sites/default/files/icon.png',
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Size: @width x @height', [
      '@width' => $this->getSetting('width'),
      '@height' => $this->getSetting('height'),
    ]);

    $animation = $this->getSetting('animation');
    if (!empty($animation)) {
      $presets = $this->qrcodeGenerator->getAnimationPresets();
      $summary[] = $this->t('Animation: @animation', [
        '@animation' => $presets[$animation] ?? $animation,
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $content = $this->extractContent($item);

      if (empty($content)) {
        continue;
      }

      $options = [
        'module_color' => $this->getSetting('module_color'),
        'position_ring_color' => $this->getSetting('position_ring_color'),
        'position_center_color' => $this->getSetting('position_center_color'),
        'background_color' => $this->getSetting('background_color'),
        'width' => $this->getSetting('width'),
        'height' => $this->getSetting('height'),
        'mask_x_to_y_ratio' => $this->getSetting('mask_x_to_y_ratio'),
        'animation' => $this->getSetting('animation'),
        'icon' => $this->getSetting('icon'),
      ];

      $qr_code = $this->qrcodeGenerator->generateQRCode($content, $options);

      $elements[$delta] = $qr_code;
    }

    return $elements;
  }

  /**
   * Extract content from field item based on field type.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return string
   *   The extracted content.
   */
  protected function extractContent($item) {
    $field_type = $this->fieldDefinition->getType();

    switch ($field_type) {
      case 'link':
        return $item->uri ?? '';

      case 'text_with_summary':
        return $item->value ?? '';

      default:
        return $item->value ?? '';
    }
  }

}
