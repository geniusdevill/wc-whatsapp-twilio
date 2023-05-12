<?php
/**
 * Plugin Name: WooCommerce WhatsApp Notifications
 * Description: Send WhatsApp messages whenever a new order is created in WooCommerce.
 * Version: 1.0
 * Author: Hassan Ejaz
 * Author URI: https://brandbees.net
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('wc_whatsapp_notifications_send_message')) {
    function wc_whatsapp_notifications_send_message($order_id) {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Load Twilio SDK
        require_once plugin_dir_path(__FILE__) . 'Twilio/autoload.php';

        // Twilio credentials from your Twilio account
        $account_sid = 'your_account_sid';
        $auth_token = 'your_auth_token';
        $twilio_whatsapp_number = 'your_twilio_whatsapp_number';

        $twilio = new Twilio\Rest\Client($account_sid, $auth_token);

        // Customer details
        $customer_phone = $order->get_billing_phone();
        $customer_whatsapp_number = 'whatsapp:' . $customer_phone;

        // Message details
        $message_body = 'Hello ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ', thank you for your order (ID: ' . $order_id . '). Your order has been received and is now being processed.';

        try {
            $twilio->messages->create(
                $customer_whatsapp_number,
                [
                    'from' => 'whatsapp:' . $twilio_whatsapp_number,
                    'body' => $message_body,
                ]
            );
        } catch (Exception $e) {
            error_log('WhatsApp message sending failed: ' . $e->getMessage());
        }
    }
}

add_action('woocommerce_checkout_order_processed', 'wc_whatsapp_notifications_send_message', 10, 1);

