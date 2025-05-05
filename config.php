<?php
namespace WPAutoAgent\Core;

// Ensure this file is not accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define the API URL constant
if (!defined('WPAA_API_EMBEDDING_URL')) {
    define('WPAA_API_EMBEDDING_URL', 'https://u5guaifnaqk4emxngldlwes6ni0zzgwy.lambda-url.ap-southeast-2.on.aws/');
}

if (!defined('WPAA_CHAT_HISTORY_RANGE')) {
    define('WPAA_CHAT_HISTORY_RANGE', 300);
}

// You can add more configuration constants here as needed