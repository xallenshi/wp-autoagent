<?php
namespace WPAutoAgent\Core;

if (!defined('ABSPATH')) {
    exit;
}

$db_handler = new DBHandler();
$global_setting = $db_handler->get_global_setting();
$access_key = $global_setting->access_key;

?>

<div class="wpaa-plugin-container">
    <form method="post">
        <h1>Access Key</h1>
        <h4>The access key is used to authenticate the AI Agent when it is published.</h4>
        <div class="form-field">
            <label for="access_key">Access Key</label>
            <input type="text" id="access_key" name="access_key" value="<?php echo esc_attr($access_key); ?>" class="regular-text" required pattern="[a-zA-Z0-9\-]+">
        </div>
        <button type="submit" id="wpaa_save_key_button">Save Access Key</button>
        <p class="description">This is the access key for the AI Agent. It is used to authenticate the AI Agent when it is published.</p>
    </form>
</div>