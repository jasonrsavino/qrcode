<?php

namespace Drupal\qrcode\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\qrcode\Service\QRCodeGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for QR Code related pages.
 */
class QRCodeController extends ControllerBase {

  /**
   * The QR Code generator service.
   *
   * @var \Drupal\qrcode\Service\QRCodeGenerator
   */
  protected $qrcodeGenerator;

  /**
   * Constructs a QRCodeController object.
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
   * Displays a QR code preview for the given contents.
   *
   * @param string $contents
   *   The contents to encode in the QR code.
   *
   * @return array
   *   A render array for the QR code preview page.
   */
  public function preview($contents) {
    // URL decode the contents.
    $contents = urldecode($contents);

    // Validate contents.
    $errors = $this->qrcodeGenerator->validateContents($contents);
    if (!empty($errors)) {
      return [
        '#markup' => '<div class="messages messages--error">' .
        implode('<br>', $errors) .
        '</div>',
      ];
    }

    $build = [];

    $build['description'] = [
      '#markup' => '<div class="qr-code-preview-description"><p>' .
      $this->t('Preview of QR code for: <strong>@contents</strong>', ['@contents' => $contents]) .
      '</p></div>',
    ];

    $build['qr_code'] = $this->qrcodeGenerator->generateQRCode($contents);

    $build['info'] = [
      '#markup' => '<div class="qr-code-preview-info"><p>' .
      $this->t('Right-click on the QR code to save it as an image, or use the <a href="@generator">QR Code Generator</a> for more customization options.', [
        '@generator' => $this->urlGenerator()->generateFromRoute('qrcode.generator'),
      ]) .
      '</p></div>',
    ];

    return $build;
  }

}
