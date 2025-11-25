# QR Code Module

This module provides QR code generation functionality for Drupal using the [bitjson/qr-code](https://github.com/bitjson/qr-code) JavaScript library.

## Purpose

The QR Code module allows you to:
- Generate QR codes from text, URLs, and other field content
- Display QR codes as field formatters for various field types
- Customize QR code appearance with colors, sizing, and animations

## Features

- **Field Formatter**: Convert text, string, and link fields into QR codes
- **Standalone Generator**: Create QR codes through a dedicated form interface
- **Customizable Styling**: Configure colors, dimensions, and visual effects
- **Animation Support**: Apply various animation presets to QR codes
- **CDN Integration**: Automatic loading from unpkg.com CDN
- **Theme Integration**: Uses Drupal's theme system for consistent styling

## Dependencies

### Required
- **Drupal Core**: 10.0 or higher
- **Core Field Module**: For field formatter functionality
- **Core Form Module**: For configuration forms

### JavaScript Library
The module uses the bitjson/qr-code JavaScript library, automatically loaded from unpkg.com CDN.

## Installation

1. Download and place the module in `modules/custom/qrcode/`
2. Enable the module through the admin interface or via Drush:
   ```bash
   drush en qrcode
   ```
3. Configure settings at `/admin/config/media/qrcode`

The module automatically loads the required JavaScript library from the CDN, so no additional setup is required.

## Setting Up Field Display

### Adding QR Code Formatter to a Field

1. **Navigate to Field Display Settings**:
   - Go to your content type: `/admin/structure/types/manage/[content-type]/display`
   - Or view mode: `/admin/structure/display-modes/view`

2. **Select QR Code Formatter**:
   - Find the field you want to display as a QR code
   - Click the dropdown under "Format"
   - Select "QR Code" from the available formatters

3. **Configure Formatter Settings**:
   - Click the gear icon next to the QR Code formatter
   - Customize the following options:

#### Styling Options
- **Module Color**: Color of QR code modules (dark squares) - default #000000
- **Position Ring Color**: Color of position indicator rings - default #000000  
- **Position Center Color**: Color of position indicator centers - default #000000
- **Background Color**: Background color of the QR code - default #ffffff

#### Size & Layout
- **Width**: QR code width (e.g., 200px, 10em) - default 200px
- **Height**: QR code height (e.g., 200px, 10em) - default 200px
- **Mask X to Y Ratio**: Aspect ratio for QR code mask - default 1

#### Display Options
- **Show Original Text**: Display the field value alongside the QR code
- **Text Position**: Position text above, below, left, or right of QR code
- **Animation**: Choose from available animation presets

#### Link Field Options
For link fields, additional options include:
- **Link Target**: Open links in same window or new tab

### Supported Field Types

The QR Code formatter works with:
- **String fields** (`string`, `string_long`)
- **Text fields** (`text`, `text_long`, `text_with_summary`)
- **Link fields** (`link`)

### Example Usage

**For a URL field**:
1. Create a "Website" link field on your content type
2. Set the display formatter to "QR Code"
3. Configure colors and size as desired
4. Users can scan the QR code to visit the website

**For a contact field**:
1. Create a "Phone Number" text field
2. Use QR Code formatter with "Show original text" enabled
3. Position text below the QR code for easy reading

## Configuration

### Global Settings

Access global settings at `/admin/config/media/qrcode`:

#### Default Settings
Configure default values for all QR codes:
- **Colors**: Module, position ring, position center, and background colors
- **Dimensions**: Default width and height
- **Animation**: Default animation preset
- **Mask Ratio**: Default aspect ratio

### Permissions

The module provides these permissions:
- **Configure QR Code settings**: Access to global configuration
- **Generate QR codes**: Use the standalone QR code generator

## Troubleshooting

### QR Code Not Displaying
1. Check browser console for JavaScript errors
2. Verify CDN access
3. Ensure field has content to encode

### Styling Issues
1. Check CSS conflicts with theme styles
2. Verify color values are valid hex codes
3. Test with default styling first

### Performance Considerations
- CDN loading provides automatic updates and good performance
- Consider caching strategies for heavy QR code usage
- No local files to maintain or update

## Support

For issues and feature requests related to this Drupal module, please use the project's issue tracker or contact the module maintainer.

For information about the underlying QR code JavaScript library, including advanced configuration options and API documentation, see the [bitjson/qr-code repository README](https://github.com/bitjson/qr-code/blob/main/README.md).
