<?php
namespace WPAutoAgent\Core;

class FunctionHandler {
    // Map function call names to handler methods
    public static $function_map = [
        'wpaa_track_order' => ['wpaa_track_order', 'internal_function_track_order'],
        'wpaa_sumbit_form' => ['wpaa_sumbit_form', 'internal_function_sumbit_form'],
        // Add more mappings here as you add new functions
    ];

    // Accepts an associative array of arguments
    public static function internal_function_track_order($args) {
        $order_id = $args['order_id'] ?? null;
        if (!$order_id) {
            return 'Missing order_id';
        }
        return 'Order status: Shipping';
    }

    public static function internal_function_sumbit_form($args) {
        $form_data = $args['form_data'] ?? null;
        if (!$form_data) {
            return 'Missing form_data';
        }
        return 'Form submitted successfully';
    }
} 