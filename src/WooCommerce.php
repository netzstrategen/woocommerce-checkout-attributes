<?php

/**
 * @file
 * Contains \Netzstrategen\WooCommerceCheckoutAttributes\WooCommerce.
 */

namespace Netzstrategen\WooCommerceCheckoutAttributes;

class WooCommerce {

  /**
   * Changes and formats cart item data.
   *
   * @implements woocommerce_get_item_data
   */
  public static function woocommerce_get_item_data($data, $cartItem) {
    $product = ($cartItem['variation_id']) ? wc_get_product($cartItem['variation_id']) : wc_get_product($cartItem['product_id']);
    // Add product data (SKU, dimensions and weight) and attributes.
    // Note: we displays parent attributes for production variations.
    $data = array_merge(static::getProductData($product), $data, static::getProductAttributes($product));
    return $data;
  }

  /**
   * Adds product attributes to order emails.
   *
   * @implements woocommerce_display_item_meta
   */
  public static function woocommerce_display_item_meta($html, $item, $args) {
    $strings = [];
    $product = $item->get_product();
    $attributes = array_merge(static::getProductData($product), static::getProductAttributes($product));
    foreach ($attributes as $attribute) {
      $strings[] = '<strong class="wc-item-meta-label">' . $attribute['name'] . ':</strong> ' . $attribute['value'];
    }
    if ($strings) {
      $html .= $args['before'] . implode($args['separator'], $strings) . $args['after'];
    }
    return $html;
  }

  /**
   * Retrieves basic data (SKU, dimensions and weight) for a given product.
   *
   * @param WC_Product $product
   *   Product for which data has to be retrieved.
   *
   * @return array
   *   Set of product data including weight, dimensions and SKU.
   */
  public static function getProductData(\WC_Product $product) {
    $productData = [];
    // Adds sku to the cart item data.
    if ($sku = $product->get_sku()) {
      $productData[] = [
        'name' => __('SKU', 'woocommerce'),
        'value' => $sku,
      ];
    }
    // Adds dimensions to the cart item data.
    if ($dimensions_value = array_filter($product->get_dimensions(FALSE))) {
      $productData[] = [
        'name' => __('Dimensions', 'woocommerce'),
        'value' => wc_format_dimensions($dimensions_value),
      ];
    }
    // Adds weight to the cart item data.
    if ($weight_value = $product->get_weight()) {
      $productData[] = [
        'name' => __('Weight', 'woocommerce'),
        'value' => $weight_value . ' kg',
      ];
    }
    return $productData;
  }

  /**
   * Retrieves the attributes of a given product.
   *
   * @param WC_Product $product
   *   Product for which attributes should be retrieved.
   * @param string $separator
   *   Separator between multiple values of an attribute.
   *
   * @return array
   *   List of attributes of the product.
   */
  public static function getProductAttributes(\WC_Product $product) {
    $data = [];
    if ($parent_id = $product->get_parent_id()) {
      $product = wc_get_product($parent_id);
    }
    $attributes = $product->get_attributes();
    foreach ($attributes as $attribute) {
      if ($attribute['is_taxonomy'] && $attribute['is_visible'] === 1) {
        $terms = wp_get_post_terms($product->get_id(), $attribute['name'], 'all');
        if (empty($terms)) {
          continue;
        }
        $taxonomy = $terms[0]->taxonomy;
        $taxonomy_object = get_taxonomy($taxonomy);
        $taxonomy_label = '';
        if (isset($taxonomy_object->labels->name)) {
          $taxonomy_label = str_replace(__('Product ', Plugin::L10N), '', $taxonomy_object->labels->name);
        }
        $data[] = [
          'name' => $taxonomy_label,
          'value' => implode(', ', wp_list_pluck($terms, 'name')),
        ];
      }
    }
    return $data;
  }

}
