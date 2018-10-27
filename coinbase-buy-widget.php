<?php
/**
 * Plugin Name: Coinbase Buy Widget
 * Plugin URI: https://github.com/nurikabe/coinbase-buy-widget
 * Description: Quick integration of the Coinbase "Buy Widget" for WordPress
 * Version: 0.2
 * Author: Evan Owens
 * Author URI: https://github.com/nurikabe
 * Text Domain: coinbase-buy-widget
 * License: GPLv2 or later
 */

class Coinbase_Buy_Widget extends WP_Widget
{
	public function __construct() {
	    $options = array(
		    'customize_selective_refresh' => true,
            'description' => 'Displays the Buy Widget from Coinbase'
        );
		parent::__construct( 'coinbase_buy_widget', __( 'Coinbase Buy Widget', 'coinbase-buy-widget' ), $options );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_init() {
		register_setting( 'coinbase_buy_widget', 'coinbase_code' );
		add_settings_section( 'coinbase-buy-widget-keys', '', array($this, 'section_description_cb'), 'coinbase_buy_widget' );
		add_settings_field( 'coinbase_code', __('Code', 'coinbase-buy-widget'), array($this, 'field_cb'), 'coinbase_buy_widget', 'coinbase-buy-widget-keys', 'coinbase_code' );
	}

	public function admin_menu() {
		add_options_page( 'Coinbase Buy Widget', 'Coinbase Buy Widget', 'manage_options', 'coinbase-buy-widget-options', array($this, 'options_page') );
	}

	public function field_cb( $name ) {
		// Cannot register multiple settings via callback
		//register_setting( 'coinbase_buy_widget', $name );
		echo '<input type="text" name="'.$name.'" value="'.get_option($name).'" />';
	}

	public function section_description_cb() {
		echo '<p><a href="https://developers.coinbase.com/docs/buy-widget#setting-up-the-buy-widget" target="_blank">Contact Coinbase</a> to receive the authentication code for your Buy Widget</p>';
	}

	public function options_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Coinbase Buy Widget Options', 'coinbase-buy-widget' ) ?></h2>
			<form action="options.php" method="post">
				<?php
				// Output security fields
				settings_fields( 'coinbase_buy_widget' );
				// Output setting sections and their fields for the given *page*
				do_settings_sections( 'coinbase_buy_widget' );
				// Standard WordPress button
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Display the widget
	public function widget( $args, $instance ) {

		extract( $args );

		// Check the widget options
		$title  = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$textarea = isset( $instance['textarea'] ) ? $instance['textarea'] : '';
		$address = isset( $instance['destination_address'] ) ? $instance['destination_address'] : '';
        $amount = isset( $instance['amount'] ) ? $instance['amount'] : '';
		$currency = isset( $instance['currency'] ) ? $instance['currency'] : '';
		$cryptocurrency = isset( $instance['cryptocurrency'] ) ? $instance['cryptocurrency'] : '';

		// WordPress core before_widget hook (always include )
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Display widget title if defined
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		// Display textarea field
		if ( $textarea ) {
			echo '<p>' . $textarea . '</p>';
		}

		?>
        <a class="coinbase-widget"
           id="coinbase_widget"
           data-address="<?php echo $address ?>"
           data-amount="<?php echo $amount ?>"
           data-code="<?php echo get_option('coinbase_code') ?>"
           data-currency="<?php echo $currency ?>"
           data-crypto_currency="<?php echo $cryptocurrency ?>"
           href="">Buy bitcoin using Coinbase</a>
        <script type="text/javascript" id="coinbase_widget_loader" class="coinbase-widget-async-loader">
            document.currentScript.src = '';
            (function() {
                function asyncLoad() {
                    var s = document.createElement('script');
                    s.type = 'text/javascript';
                    s.async = true;
                    var theUrl = "https://buy.coinbase.com/static/widget.js";
                    s.src = theUrl+ (theUrl.indexOf("?") >= 0 ? '&' : '?') + 'ref=' + encodeURIComponent(window.location.href);
                    var embedder = document.getElementById('coinbase_widget_loader');
                    embedder.parentNode.insertBefore(s, embedder);
                }
                if (window.attachEvent) {
                    window.attachEvent('onload', asyncLoad);
                } else {
                    window.addEventListener('load', asyncLoad, false);
                }
            })();
        </script>
		<?php

		// WordPress core after_widget hook (always include )
		echo $after_widget;
	}

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
			'title' => '',
			'textarea' => '',
			'destination_address' => '',
			'amount' => '',
			'currency' => '',
			'cryptocurrency' => '',
		);

		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'coinbase-buy-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>

		<?php // Textarea Field ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'textarea' ) ); ?>"><?php _e( 'Text above buy button:', 'coinbase-buy-widget' ); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'textarea' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'textarea' ) ); ?>"><?php echo wp_kses_post( $textarea ); ?></textarea>
        </p>

		<?php // Destination Address ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'destination_address' ) ); ?>"><?php _e( 'Destination address:', 'coinbase-buy-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'destination_address' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'destination_address' ) ); ?>" type="text" value="<?php echo esc_attr( $destination_address ); ?>" />
        </p>

		<?php // Amount ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>"><?php _e( 'Default fiat amount:', 'coinbase-buy-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount' ) ); ?>" type="text" value="<?php echo esc_attr( $amount ); ?>" />
        </p>

        <p>
            <?php // Currency ?>
            <div style="float: left; margin-right: 1em;">
                <label for="<?php echo esc_attr( $this->get_field_id( 'currency' ) ); ?>"><?php _e( 'Currency:', 'coinbase-buy-widget' ); ?></label><br/>
                <input size="3" id="<?php echo esc_attr( $this->get_field_id( 'currency' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'currency' ) ); ?>" type="text" value="<?php echo esc_attr( $currency ); ?>" />
            </div>

            <?php // Cryptocurrency ?>
            <div>
                <label for="<?php echo esc_attr( $this->get_field_id( 'cryptocurrency' ) ); ?>"><?php _e( 'Crypto:', 'coinbase-buy-widget' ); ?></label><br/>
                <select id="<?php echo esc_attr( $this->get_field_id( 'cryptocurrency' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cryptocurrency' ) ); ?>">
                    <option value="BTC"<?php echo 'BTC' == $cryptocurrency ? ' selected' : '' ?>>Bitcoin</option>
                    <option value="BCH"<?php echo 'BCH' == $cryptocurrency ? ' selected' : '' ?>>Bitcoin Cash</option>
                    <option value="ETH"<?php echo 'ETH' == $cryptocurrency ? ' selected' : '' ?>>Ethereum</option>
                    <option value="LTC"<?php echo 'LTC' == $cryptocurrency ? ' selected' : '' ?>>Litecoin</option>
                </select>
            </div>
        </p>
    <?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['textarea'] = isset( $new_instance['textarea'] ) ? wp_kses_post( $new_instance['textarea'] ) : '';
		$instance['destination_address'] = isset( $new_instance['destination_address'] ) ? wp_strip_all_tags( $new_instance['destination_address'] ) : '';
		$instance['amount'] = isset( $new_instance['amount'] ) ? wp_strip_all_tags( $new_instance['amount'] ) : '';
		$instance['currency'] = isset( $new_instance['currency'] ) ? wp_strip_all_tags( $new_instance['currency'] ) : '';
		$instance['cryptocurrency'] = isset( $new_instance['cryptocurrency'] ) ? wp_strip_all_tags( $new_instance['cryptocurrency'] ) : '';
		return $instance;
	}
}

add_action( 'widgets_init', function() { register_widget( 'Coinbase_Buy_Widget' ); });
