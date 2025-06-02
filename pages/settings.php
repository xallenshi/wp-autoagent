<div class="wpaa-body">
    <div class="wpaa-title"><img class="wpaa-logo" src="<?php echo WP_AUTOAGENT_PLUGIN_URL . 'assets/img/logo.png'; ?>" alt="WP AutoAgent"></div>

    <div id="wpaa_setting_menu">
    <div class="wpaa-panel-header"><span class="wpaa-space">Settings</span></div>
        <ul>
            <li data-page="create">Manage Agent</li>
            <li data-page="publish">Publish Agent</li>
            <li data-page="key">Access Key</li>
        </ul>
    </div>

    <!-- WP AutoAgent Content -->
    <div id="wpaa_settings_content">

        <div class="wpaa-page" id="wpaa_create" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/create.php'); ?>
        </div>
        
        <div class="wpaa-page" id="wpaa_publish" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/publish.php'); ?>
        </div>

        <div class="wpaa-page" id="wpaa_key" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/key.php'); ?>
        </div>
    </div>
</div>