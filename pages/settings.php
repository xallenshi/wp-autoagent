<div class="sc-body">
    <div class="sc-title">WP AutoAgent Settings</div>
    <div id="sc_settings_menu">
        <ul>
            
            <li data-page="create">Manage Agent</li>
            <li data-page="upload">Knowledge Base</li>
            <li data-page="publish">Publish Agent</li>
            <li data-page="access">Access Key</li>
        </ul>
    </div>

    <!-- WP AutoAgent Content -->
    <div id="sc_settings_content">

        <div class="sc-page" id="sc_create" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/create.php'); ?>
        </div>

        <div class="sc-page" id="sc_upload" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/upload.php'); ?>
        </div>
        
        <div class="sc-page" id="sc_publish" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/publish.php'); ?>
        </div>
        <div class="sc-page" id="sc_access" style="display: none;">
            <?php include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/access.php'); ?>
        </div>
    </div>
</div>