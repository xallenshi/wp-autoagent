<?php
namespace WPAutoAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$agents = $db_handler->get_agents();

$table_agent = Config::get_table_name('agent');
$pages = get_pages();

?>

<div class="wpaa-agent-list2">
    <div class="wpaa-panel-header">Agent List</div>
    <ul>
        <?php
        // Get agents from database
        $agents = $db_handler->get_agents();
        if ($agents) {
            foreach ($agents as $agent) {
                ?>
                <li>
                    <a href="#" data-agent_id="<?php echo esc_attr($agent->id); ?>">
                        <?php echo esc_html($agent->name); ?>
                    </a>
                </li>
                <?php
            }
        }
        ?>
    </ul>
</div>

<div class="wpaa-plugin-container">
    <form method="post" id="wpaa-publish-agent-form">
    <h1>Publish Agent</h1>
    <h4>Select which pages you want the AI Agent to appear on.</h4>
    
        <!-- Agent Selection -->
        <h2>Select Agent</h2>
        <p><small>Please select an agent from the list above</small></p>
        <select name="agent_id" required id="agent-select" style="pointer-events: none; background-color: white; opacity: 1;">
            <option value="">Select an agent...</option>
            <?php foreach ($agents as $agent): ?>
                <option value="<?php echo esc_attr($agent->id); ?>"><?php echo esc_html($agent->name); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Page Selection -->
        <h2>Select Pages for Agent Scope</h2>
        <div class="wpaa-publish-scope-container">
            <div>
                <h3>Frontend Pages</h3>
                <p><small>Simply click an option to select it. Click again to unselect.</small></p>
                <select name="selected_pages[]" multiple size="10">
                    <?php foreach ($pages as $page): ?>
                        <option value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <h3>Admin Pages</h3>
                <p><small>Simply click an option to select it. Click again to unselect.</small></p>
                <select name="selected_admin_pages[]" multiple size="10">
                    <option value="index.php">Dashboard</option>
                    <option value="edit.php">Posts</option>
                    <option value="upload.php">Media Library</option>
                    <option value="edit.php?post_type=page">Pages</option>
                    <option value="edit-comments.php">Comments</option>
                    <option value="themes.php">Themes</option>
                    <option value="plugins.php">Plugins</option>
                    <option value="users.php">Users</option>
                    <option value="tools.php">Tools</option>
                    <option value="options-general.php">Settings</option>
                </select>
            </div>
        </div>

        <button type="submit" id="wpaa_publish_agent_button">Publish Agent</button>
        <label for="wpaa-agent-instructions">Same selected pages take the newest agent.</label>
    </form>
</div>