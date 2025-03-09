<?php
namespace WPAutoAgent\Core;

if (!defined('ABSPATH')) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_key_nonce']) && wp_verify_nonce($_POST['access_key_nonce'], 'save_access_key')) {
    $access_key = sanitize_text_field($_POST['access_key']);

    // Validate the access key (only letters and numbers allowed)
    if (preg_match('/^[a-zA-Z0-9]+$/', $access_key)) {
        update_option('wp_autoagent_access_key', $access_key);
        $message = 'Access key saved successfully.';
    } else {
        $error = 'Invalid access key. Only letters and numbers are allowed.';
    }
}

// Retrieve the current access key
$current_access_key = get_option('wp_autoagent_access_key', '');
?>

<div class="wrap">
    <h1>Access Key</h1>
    <?php if (isset($message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="form-field">
            <label for="access_key">Access Key</label>
            <input type="text" id="access_key" name="access_key" value="<?php echo esc_attr($current_access_key); ?>" class="regular-text" required pattern="[a-zA-Z0-9]+">
            <p class="description">Enter your access key. Only letters and numbers are allowed.</p>
        </div>
        <?php wp_nonce_field('save_access_key', 'access_key_nonce'); ?>
        <?php submit_button('Save Access Key'); ?>
    </form>
</div>