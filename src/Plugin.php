<?php

/**
 * @file
 * Contains \Netzstrategen\WooCommerceCheckoutAttributes\Plugin.
 */

namespace Netzstrategen\WooCommerceCheckoutAttributes;

/**
 * Main front-end functionality.
 */
class Plugin {

  /**
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'woocommerce-checkout-attributes';

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = self::PREFIX;

  /**
   * @var string
   */
  private static $baseUrl;

  /**
   * @implements init
   */
  public static function init() {
    if (is_admin()) {
      return;
    }

    // Changes and formats cart item data.
    add_action('woocommerce_get_item_data', __NAMESPACE__ . '\WooCommerce::woocommerce_get_item_data', 10, 2);
    // Adds product attributes to order emails.
    add_filter('woocommerce_display_item_meta', __NAMESPACE__ . '\WooCommerce::woocommerce_display_item_meta', 10, 3);
  }

  /**
   * Loads the plugin textdomain.
   */
  public static function loadTextdomain() {
    load_plugin_textdomain(static::L10N, FALSE, static::L10N . '/languages/');
  }

}
