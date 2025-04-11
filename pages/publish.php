<?php
namespace WPAutoAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$agents = $db_handler->get_agents();

$table_agent = Config::get_table_name('agent');
$pages = get_pages();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_pages']) && isset($_POST['agent_id'])) {
    $selected_pages = $_POST['selected_pages'];
    $agent_id = intval($_POST['agent_id']);
    
    // Convert selected pages to JSON
    $scope_json = json_encode($selected_pages);

    $result = $wpdb->update(
        $table_agent,
        ['scope' => $scope_json],
        ['id' => $agent_id]
    );

    if ($result !== false) {
        echo '<div class="updated"><p>Agent scope updated successfully!</p></div>';
    } else {
        echo '<div class="error"><p>Error updating agent scope.</p></div>';
    }
}
?>

<div class="wpaa-plugin-container">
    <form method="post">
    <h1>Publish Agent</h1>
    <h4>Select which pages you want the AI Agent to appear on.</h4>
    
        <!-- Agent Selection -->
        <h2>Select Agent</h2>
        <select name="agent_id" required>
            <option value="">Select an agent...</option>
            <?php foreach ($agents as $agent): ?>
                <option value="<?php echo esc_attr($agent->id); ?>"><?php echo esc_html($agent->name); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Page Selection -->
        <h2>Select Pages for Agent Scope</h2>
        <select name="selected_pages[]" multiple required style="height: 200px;">
            <?php foreach ($pages as $page): ?>
                <option value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Publish Agent</button>
    </form>
</div>