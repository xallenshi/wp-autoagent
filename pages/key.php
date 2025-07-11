<?php
namespace WPAgent\Core;

if (!defined('ABSPATH')) {
    exit;
}

$db_handler = new DBHandler();
$global_setting = $db_handler->get_global_setting();
$access_key = isset($global_setting->access_key) ? $global_setting->access_key : '';

?>

<div class="wpa-plugin-container">
    <form method="post">
        <h2>Access Key</h2>
        <hr class="wpa-hr">
        <h4>The access key will be provided after you obtain the preferred plan (free plan is available). Please check out the <a href="https://wpa.xsolutions.com/pricing/" target="_blank">pricing page</a> for more details.</h4>
        <div class="form-field">
            <label for="access_key">Access Key
                <span class="wpa-tooltip">?
                    <span class="wpa-tooltip-text">
                    This access key is used to authenticate agents when interacting with the AI APIs. You can also use it on your other WordPress sites, and all sites will collectively consume the monthly request quota.
                    </span>
                </span>
            </label>
            <input type="text" id="access_key" name="access_key" value="<?php echo esc_attr($access_key); ?>" class="regular-text" required pattern="[a-zA-Z0-9\-]+">
        </div>
        <div class="wpa-row-middle">
            <button type="submit" id="wpa_save_key_button">Save Key</button>
        </div>
    </form>
</div>