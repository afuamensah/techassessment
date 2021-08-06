<?php
    add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
    function my_theme_enqueue_styles() {
        wp_enqueue_style( 'child-style', get_stylesheet_uri(),
            array( 'parenthandle' ), 
            wp_get_theme()->get('Version') // this only works if you have Version in the style header
        );
    }

    add_action( 'woocommerce_after_shop_loop_item', 'show_attributes', 5 );
    function show_attributes() {
        global $product;
        $attrs = $product->get_attributes();
        foreach($attrs as $x => $x_value) {
            $attr = $product->get_attribute($x);
            $label = wc_attribute_label($x);
            echo '<span style="font-size: 16px;"><b>'.$label.':</b> '.$attr.'</span>';
        }
    }
?>