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

/* Customizer settings for checkout texts */

add_action( 'customize_register', function( $wp_customize ) {
    $wp_customize->add_section( 'checkout_texts', [
        'title'    => 'טקסטים בדף התשלום',
        'priority' => 200,
    ] );

    $wp_customize->add_setting( 'cancellation_policy_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'cancellation_policy_url', [
        'label'       => 'קישור למדיניות ביטולים (URL של PDF)',
        'description' => 'הדבק את ה-URL של קובץ ה-PDF מספריית המדיה',
        'section'     => 'checkout_texts',
        'type'        => 'url',
    ] );

    $wp_customize->add_setting( 'terms_checkbox_text', [
        'default'           => 'קראתי את [cancellation_policy]מדיניות הביטולים[/cancellation_policy] ואת [terms]תקנון האתר ומדיניות הפרטיות[/terms]',
        'sanitize_callback' => 'wp_kses_post',
    ] );
    $wp_customize->add_control( 'terms_checkbox_text', [
        'label'       => 'טקסט צ׳קבוקס תנאים',
        'description' => 'השתמש ב-[cancellation_policy]...[/cancellation_policy] לקישור מדיניות ביטולים ו-[terms]...[/terms] לקישור תקנון',
        'section'     => 'checkout_texts',
        'type'        => 'textarea',
    ] );

    $wp_customize->add_setting( 'marketing_consent_text', [
        'default'           => 'מאשר.ת קבלת חומר שיווקי מדפ״י במייל/וואטסאפ',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'marketing_consent_text', [
        'label'   => 'טקסט צ׳קבוקס הסכמה שיווקית',
        'section' => 'checkout_texts',
        'type'    => 'text',
    ] );
} );

/* Custom terms and conditions checkbox text */

add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', function( $text ) {
    $terms_page_id       = wc_get_page_id( 'terms' );
    $terms_url           = $terms_page_id ? get_permalink( $terms_page_id ) : '#';
    $cancellation_url    = get_theme_mod( 'cancellation_policy_url', '' );
    $template            = get_theme_mod(
        'terms_checkbox_text',
        'קראתי את [cancellation_policy]מדיניות הביטולים[/cancellation_policy] ואת [terms]תקנון האתר ומדיניות הפרטיות[/terms]'
    );

    $output = str_replace(
        [ '[cancellation_policy]', '[/cancellation_policy]' ],
        [ '<a href="' . esc_url( $cancellation_url ?: '#' ) . '" target="_blank">', '</a>' ],
        $template
    );
    $output = str_replace(
        [ '[terms]', '[/terms]' ],
        [ '<a href="' . esc_url( $terms_url ) . '">', '</a>' ],
        $output
    );

    return $output;
} );

/* Marketing consent checkbox */

add_action( 'woocommerce_review_order_before_submit', function() {
    $label = esc_html( get_theme_mod( 'marketing_consent_text', 'מאשר.ת קבלת חומר שיווקי מדפ״י במייל/וואטסאפ' ) );
    echo '<p class="form-row validate-required" id="marketing_consent_field">
        <label class="woocommerce-form__label checkbox">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="marketing_consent" id="marketing_consent" />
            <span>' . $label . '</span>&nbsp;<abbr class="required" title="שדה חובה">*</abbr>
        </label>
    </p>';
} );

add_action( 'woocommerce_checkout_process', function() {
    if ( empty( $_POST['marketing_consent'] ) ) {
        wc_add_notice( 'יש לאשר קבלת חומר שיווקי כדי להמשיך.', 'error' );
    }
} );

add_action( 'woocommerce_checkout_order_created', function( $order ) {
    if ( ! empty( $_POST['marketing_consent'] ) ) {
        $order->update_meta_data( '_marketing_consent', 'yes' );
        $order->save();
    }
} );

