<?php
namespace WPAutoAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$agents = $db_handler->get_agents();

$table_agent = Config::get_table_name('agent');
$pages = get_pages();



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agent_id']) && (isset($_POST['selected_pages']) || isset($_POST['selected_admin_pages']))) {
    $agent_id = intval($_POST['agent_id']);
    $selected_pages = isset($_POST['selected_pages']) ? $_POST['selected_pages'] : array();
    $selected_admin_pages = isset($_POST['selected_admin_pages']) ? $_POST['selected_admin_pages'] : array();

    // Convert selected pages to JSON
    $scope_json = json_encode(array_merge($selected_pages, $selected_admin_pages));

    $result = $wpdb->update(
        $table_agent,
        ['scope' => $scope_json],
        ['id' => $agent_id]
    );


    $publish = new Publish();
    $publish->set_theme_color();


    if ($result !== false) {
        echo '<div class="updated"><p>Agent scope updated successfully!</p></div>';
    } else {
        echo '<div class="error"><p>Error updating agent scope.</p></div>';
    }

    ;

}




?>

<div class="wpaa-agent-list2">
    <h2>Your AI Agents</h2>
    <ul>
        <?php
        // Get agents from database
        $agents = $db_handler->get_agents();
        if ($agents) {
            foreach ($agents as $agent) {
                ?>
                <li>
                    <a href="#" class="agent-item" data-agent_id="<?php echo esc_attr($agent->id); ?>">
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
    <form method="post">
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

        <button type="submit">Publish Agent</button>
        <label for="wpaa-agent-instructions">Same selected pages take the newest agent.</label>
    </form>
</div>