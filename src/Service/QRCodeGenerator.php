<?php

namespace Drupal\qrcode\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service for generating QR codes using the bitjson/qr-code library.
 */
class QRCodeGenerator {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Available animation presets.
   *
   * @var array
   */
  protected $animations = [
    '' => 'None',
    'FadeInTopDown' => 'Fade In Top Down',
    'FadeInCenterOut' => 'Fade In Center Out',
    'MaterializeIn' => 'Materialize In',
    'RadialRipple' => 'Radial Ripple',
    'RadialRippleIn' => 'Radial Ripple In',
  ];

  /**
   * Constructs a QRCodeGenerator object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('qrcode');
  }

  /**
   * Generate QR code render array.
   *
   * @param string $contents
   *   The contents to encode in the QR code.
   * @param array $options
   *   Optional array of QR code options including:
   *   - module_color: Color of QR code modules (default: #000000)
   *   - position_ring_color: Color of position rings (default: #000000)
   *   - position_center_color: Color of position centers (default: #000000)
   *   - mask_x_to_y_ratio: Aspect ratio for mask (default: 1)
   *   - animation: Animation preset name (default: '')
   *   - width: Width of QR code (default: 200px)
   *   - height: Height of QR code (default: 200px)
   *   - background_color: Background color (default: #ffffff)
   *   - icon: Optional icon HTML (default: '')
   *   - attributes: Additional HTML attributes (default: [])
   *
   * @return array
   *   A render array for the QR code.
   */
  public function generateQrCode($contents, array $options = []) {
    $config = $this->configFactory->get('qrcode.settings');

    // Set defaults from configuration.
    $defaults = [
      'module_color' => $config->get('default_module_color') ?? '#000000',
      'position_ring_color' => $config->get('default_position_ring_color') ?? '#000000',
      'position_center_color' => $config->get('default_position_center_color') ?? '#000000',
      'mask_x_to_y_ratio' => $config->get('default_mask_x_to_y_ratio') ?? '1',
      'animation' => $config->get('default_animation') ?? '',
      'width' => $config->get('default_width') ?? '200px',
      'height' => $config->get('default_height') ?? '200px',
      'background_color' => $config->get('default_background_color') ?? '#ffffff',
      'icon' => $this->getIconPath($config->get('default_icon')),
      'attributes' => [],
      'qr_id' => 'qr-' . uniqid(),
    ];

    // Merge options with defaults.
    $options = array_merge($defaults, $options);

    // Validate contents.
    if (empty($contents)) {
      $this->logger->warning('Attempted to generate QR code with empty contents.');
      return [
        '#markup' => $this->t('No content provided for QR code generation.'),
      ];
    }

    return [
      '#theme' => 'qrcode_display',
      '#contents' => $contents,
      '#module_color' => $options['module_color'],
      '#position_ring_color' => $options['position_ring_color'],
      '#position_center_color' => $options['position_center_color'],
      '#mask_x_to_y_ratio' => $options['mask_x_to_y_ratio'],
      '#animation' => $options['animation'],
      '#width' => $options['width'],
      '#height' => $options['height'],
      '#background_color' => $options['background_color'],
      '#icon' => $options['icon'],
      '#attributes' => $options['attributes'],
      '#qr_id' => $options['qr_id'],
    ];
  }

  /**
   * Get available animation presets.
   *
   * @return array
   *   Array of animation presets keyed by machine name.
   */
  public function getAnimationPresets() {
    return $this->animations;
  }

  /**
   * Validate QR code contents.
   *
   * @param string $contents
   *   The contents to validate.
   *
   * @return array
   *   Array of validation errors, empty if valid.
   */
  public function validateContents($contents) {
    $errors = [];

    if (empty($contents)) {
      $errors[] = $this->t('Contents cannot be empty.');
    }

    // Check length (QR codes have practical limits)
    if (strlen($contents) > 4296) {
      $errors[] = $this->t('Contents are too long. Maximum length is 4296 characters.');
    }

    return $errors;
  }

  /**
   * Validate color values.
   *
   * @param string $color
   *   The color value to validate.
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function validateColor($color) {
    // Accept hex colors (#123456 or #123)
    if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
      return TRUE;
    }

    // Accept RGB/RGBA colors.
    if (preg_match('/^rgba?\([\d\s,%.]+\)$/', $color)) {
      return TRUE;
    }

    // Accept CSS color names (basic validation)
    $css_colors = [
      'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange', 'purple',
      'pink', 'brown', 'gray', 'grey', 'transparent',
    ];

    return in_array(strtolower($color), $css_colors);
  }

  /**
   * Get the icon path, using default if none specified.
   *
   * @param string|null $icon_path
   *   The configured icon path or null.
   *
   * @return string
   *   The icon path to use.
   */
  protected function getIconPath($icon_path) {
    // If no icon path specified, use the default.
    if (empty($icon_path)) {
      $module_path = \Drupal::service('extension.list.module')->getPath('qrcode');
      $icon = '/' . $module_path . '/assets/icon.png';
      return $icon;
    }

    // If it's already a URL, return as-is.
    if (filter_var($icon_path, FILTER_VALIDATE_URL)) {
      return $icon_path;
    }

    // For all other paths, treat them as relative to site root.
    // Paths should start with a leading slash.
    return $icon_path;
  }

}
