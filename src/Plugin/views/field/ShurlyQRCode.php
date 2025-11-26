<?php

namespace Drupal\qrcode\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\qrcode\Service\QRCodeGenerator;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to present a QR code for Shurly short URLs.
 *
 * @ingroup views_field_handlers
 */
#[ViewsField("shurly_qr_code")]
class ShurlyQRCode extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The QR Code generator service.
   *
   * @var \Drupal\qrcode\Service\QRCodeGenerator
   */
  protected $qrcodeGenerator;

  /**
   * Constructs a ShurlyQRCode object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\qrcode\Service\QRCodeGenerator $qrcode_generator
   *   The QR Code generator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QRCodeGenerator $qrcode_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->qrcodeGenerator = $qrcode_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('qrcode.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->additional_fields['source'] = 'source';
    $this->additional_fields['destination'] = 'destination';
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['qr_content'] = ['default' => 'short_url'];
    $options['module_color'] = ['default' => '#000000'];
    $options['position_ring_color'] = ['default' => '#000000'];
    $options['position_center_color'] = ['default' => '#000000'];
    $options['background_color'] = ['default' => '#ffffff'];
    $options['width'] = ['default' => '200px'];
    $options['height'] = ['default' => '200px'];
    $options['mask_x_to_y_ratio'] = ['default' => '1'];
    $options['animation'] = ['default' => ''];
    $options['icon'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['qr_content'] = [
      '#type' => 'radios',
      '#title' => $this->t('QR Code content'),
      '#options' => [
        'short_url' => $this->t('Short URL (full URL)'),
        'short_path' => $this->t('Short path only'),
        'long_url' => $this->t('Long URL (destination)'),
      ],
      '#default_value' => $this->options['qr_content'],
      '#description' => $this->t('Select what URL should be encoded in the QR code.'),
    ];

    // QR Code styling options.
    $form['styling'] = [
      '#type' => 'details',
      '#title' => $this->t('QR Code styling'),
      '#open' => FALSE,
    ];

    $form['module_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Module color'),
      '#default_value' => $this->options['module_color'],
      '#description' => $this->t('Color of the QR code modules (dark squares).'),
      '#fieldset' => 'styling',
    ];

    $form['position_ring_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position ring color'),
      '#default_value' => $this->options['position_ring_color'],
      '#description' => $this->t('Color of the position detection rings.'),
      '#fieldset' => 'styling',
    ];

    $form['position_center_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Position center color'),
      '#default_value' => $this->options['position_center_color'],
      '#description' => $this->t('Color of the position detection centers.'),
      '#fieldset' => 'styling',
    ];

    $form['background_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background color'),
      '#default_value' => $this->options['background_color'],
      '#description' => $this->t('Background color of the QR code.'),
      '#fieldset' => 'styling',
    ];

    // Size options.
    $form['sizing'] = [
      '#type' => 'details',
      '#title' => $this->t('QR Code size'),
      '#open' => FALSE,
    ];

    $form['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $this->options['width'],
      '#description' => $this->t('Width of the QR code (e.g., 200px, 100%).'),
      '#size' => 10,
      '#fieldset' => 'sizing',
    ];

    $form['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $this->options['height'],
      '#description' => $this->t('Height of the QR code (e.g., 200px, 100%).'),
      '#size' => 10,
      '#fieldset' => 'sizing',
    ];

    $form['mask_x_to_y_ratio'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Aspect ratio'),
      '#default_value' => $this->options['mask_x_to_y_ratio'],
      '#description' => $this->t('Aspect ratio of the QR code (1 = square).'),
      '#size' => 5,
      '#fieldset' => 'sizing',
    ];

    // Animation options.
    $form['animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation'),
      '#options' => $this->qrcodeGenerator->getAnimationPresets(),
      '#default_value' => $this->options['animation'],
      '#description' => $this->t('Animation effect for the QR code appearance.'),
    ];

    // Icon options.
    $form['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon Path'),
      '#description' => $this->t('Path to the icon file to display in the center of QR codes. Leave empty to use the default icon (assets/icon.png). Path should be relative to the site root and must start with a leading slash (e.g., /sites/default/files/my-icon.png).'),
      '#default_value' => $this->options['icon'],
      '#placeholder' => '/sites/default/files/icon.png',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    $summary_parts = [];
    
    // Add QR content type to summary
    $content_options = [
      'short_url' => $this->t('Short URL (full URL)'),
      'short_path' => $this->t('Short path only'),
      'long_url' => $this->t('Long URL (destination)'),
    ];
    $summary_parts[] = $this->t('Content: @content', ['@content' => $content_options[$this->options['qr_content']]]);
    
    // Add size info to summary
    $summary_parts[] = $this->t('Size: @width x @height', [
      '@width' => $this->options['width'],
      '@height' => $this->options['height'],
    ]);
    
    // Add animation if set
    if (!empty($this->options['animation'])) {
      $presets = $this->qrcodeGenerator->getAnimationPresets();
      $summary_parts[] = $this->t('Animation: @animation', [
        '@animation' => $presets[$this->options['animation']] ?? $this->options['animation'],
      ]);
    }
    
    return implode(', ', $summary_parts);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $source = $this->getValue($values, 'source');
    $destination = $this->getValue($values, 'destination');

    // Determine what content to encode based on settings.
    switch ($this->options['qr_content']) {
      case 'short_path':
        $content = $source;
        break;

      case 'long_url':
        $content = $destination;
        break;

      case 'short_url':
      default:
        // Generate the full short URL using the _surl function if available.
        if (function_exists('_surl')) {
          $content = rawurldecode(_surl($source, ['absolute' => TRUE]));
        }
        else {
          // Fallback: construct URL manually if function not available.
          $config = \Drupal::config('shurly.settings');
          $shurly_base = trim($config->get('shurly_base') ?? '');
          if (empty($shurly_base)) {
            $shurly_base = \Drupal::request()->getSchemeAndHttpHost();
          }
          $content = $shurly_base . '/' . $source;
        }
        break;
    }

    // If content is empty, don't render anything.
    if (empty($content)) {
      return '';
    }

    // Generate QR code options from field settings.
    $qr_options = [
      'module_color' => $this->options['module_color'],
      'position_ring_color' => $this->options['position_ring_color'],
      'position_center_color' => $this->options['position_center_color'],
      'background_color' => $this->options['background_color'],
      'width' => $this->options['width'],
      'height' => $this->options['height'],
      'mask_x_to_y_ratio' => $this->options['mask_x_to_y_ratio'],
      'animation' => $this->options['animation'],
      'icon' => $this->options['icon'],
    ];

    // Generate and return the QR code render array.
    $qr_code = $this->qrcodeGenerator->generateQRCode($content, $qr_options);

    return $qr_code;
  }

}
