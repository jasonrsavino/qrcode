# QR Code Views Integration for Shurly Module

The QR Code module now provides seamless integration with the Shurly URL shortener module through a custom Views field plugin.

## Features

When the Shurly module is enabled, the QR Code module automatically adds a new field option to Shurly-based Views called **"QR Code"**.

### Available QR Code Content Options

1. **Short URL (full URL)** - Default option that generates the complete short URL (e.g., https://example.com/abc123)
2. **Short path only** - Just the path portion (e.g., abc123)  
3. **Long URL (destination)** - The original long URL that the short URL redirects to

### Customization Options

The QR Code field provides extensive styling and behavior options:

#### QR Code Styling
- **Module color**: Color of the QR code modules (dark squares)
- **Position ring color**: Color of the position detection rings
- **Position center color**: Color of the position detection centers  
- **Background color**: Background color of the QR code

#### Size Options
- **Width**: Width of the QR code (e.g., 200px, 100%)
- **Height**: Height of the QR code (e.g., 200px, 100%)
- **Aspect ratio**: Aspect ratio of the QR code (1 = square)

#### Animation Effects
Choose from several animation presets:
- None (default)
- Fade In Top Down
- Fade In Center Out
- Materialize In
- Radial Ripple
- Radial Ripple In

## Usage Instructions

1. **Create or Edit a View** that uses the Shurly table as its base table
2. **Add a new field** to your view
3. **Select "QR Code"** from the available fields 
4. **Configure the field** with your preferred settings:
   - Choose what URL content to encode in the QR code
   - Customize the appearance (colors, size, animation)
5. **Save your view**

## Use Cases

This integration is perfect for:

- **URL sharing dashboards** - Display QR codes alongside shortened URLs for easy mobile scanning
- **Analytics views** - Show QR codes for high-performing short URLs
- **Administrative interfaces** - Quick QR code generation for any shortened URL
- **Public displays** - Show QR codes that visitors can scan to access shortened URLs
- **Reports and exports** - Include scannable QR codes in printed or digital reports

## Technical Details

- The plugin uses the existing QR Code generator service with all its features
- Automatically detects if Shurly module is available
- Supports all existing QR code styling and animation options
- Integrates seamlessly with Views configuration UI
- Respects Shurly's base URL configuration settings

## Requirements

- QR Code module (this module)
- Shurly module  
- Views module (included with Drupal core)

The integration is automatically available once both QR Code and Shurly modules are enabled - no additional configuration required!
