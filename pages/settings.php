<div class="wpa-body">
    <div class="wpa-title"><img class="wpa-logo" src="<?php echo WP_AGENT_PLUGIN_URL . 'assets/img/logo.png'; ?>" alt="WP Agent"></div>

    <div id="wpa_setting_menu">
    <div class="wpa-panel-header"><span class="wpa-space">Settings</span></div>
        <ul>
            <li data-page="create">Manage Agent</li>
            <li data-page="publish">Publish Agent</li>
            <li data-page="key">Access Key</li>
        </ul>
    </div>

    <!-- WP Agent Content -->
    <div id="wpa_settings_content">

        <div class="wpa-page" id="wpa_create" style="display: none;">
            <?php include(WP_AGENT_PLUGIN_DIR . 'pages/create.php'); ?>
        </div>
        
        <div class="wpa-page" id="wpa_publish" style="display: none;">
            <?php include(WP_AGENT_PLUGIN_DIR . 'pages/publish.php'); ?>
        </div>

        <div class="wpa-page" id="wpa_key" style="display: none;">
            <?php include(WP_AGENT_PLUGIN_DIR . 'pages/key.php'); ?>
        </div>
    </div>
</div>