<div class="wpaa-body">
    <div class="wpaa-title">WP AutoAgent Settings</div>
    <div id="wpaa_setting_menu">
        <ul>
            
            <li data-page="create">Manage Agent</li>
            <li data-page="upload">Knowledge Base</li>
            <li data-page="publish">Publish Agent</li>
            <li data-page="access">Access Key</li>
        </ul>
    </div>

    <!-- WP AutoAgent Content -->
    <div id="wpaa_settings_content">

        <div class="wpaa-page" id="wpaa_create" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/create.php'); ?>
        </div>

        <div class="wpaa-page" id="wpaa_upload" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/upload.php'); ?>
        </div>
        
        <div class="wpaa-page" id="wpaa_publish" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/publish.php'); ?>
        </div>
        <div class="wpaa-page" id="wpaa_access" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/access.php'); ?>
        </div>
    </div>
</div>