<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );

/* add remove cart buttons */

function ns_woocommerce_checkout_remove_item( $product_name, $cart_item, $cart_item_key ) {
if ( is_checkout() ) {
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

    $remove_link = apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
        '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">×</a>',
        esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
        __( 'Remove this item', 'woocommerce' ),
        esc_attr( $product_id ),
        esc_attr( $_product->get_sku() )
    ), $cart_item_key );

    return '<span>' . $remove_link . '</span> <span>' . $product_name . '</span>';
}

return $product_name;
}
add_filter( 'woocommerce_cart_item_name', 'ns_woocommerce_checkout_remove_item', 10, 3 );

/* Custom terms and conditions checkbox text */

add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', function( $text ) {
    $terms_page_id = wc_get_page_id( 'terms' );
    $terms_url     = $terms_page_id ? get_permalink( $terms_page_id ) : '#';
    return 'קראתי את <a href="' . esc_url( $terms_url ) . '">תקנון האתר ומדיניות הפרטיות</a>';
} );

