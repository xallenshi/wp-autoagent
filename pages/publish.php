<?php
namespace WPAutoAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$agents = $db_handler->get_agents();

$table_agent = Config::get_table_name('agent');
$pages = get_pages();

// Get admin pages
global $menu, $submenu;
if (empty($menu)) {
    do_action('admin_menu');
}

$admin_pages = [];
foreach ($menu as $item) {
    $slug = isset($item[2]) ? $item[2] : '';
    $title = isset($item[0]) ? wp_strip_all_tags($item[0]) : '';
    if ($slug && $title) {
        $admin_pages[] = [
            'slug' => $slug,
            'title' => $title,
        ];
        // Add submenus
        if (isset($submenu[$slug])) {
            foreach ($submenu[$slug] as $subitem) {
                $sub_slug = isset($subitem[2]) ? $subitem[2] : '';
                $sub_title = isset($subitem[0]) ? wp_strip_all_tags($subitem[0]) : '';
                if ($sub_slug && $sub_title) {
                    $admin_pages[] = [
                        'slug' => $sub_slug,
                        'title' => $title . ' → ' . $sub_title,
                    ];
                }
            }
        }
    }
}

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
    <h4>Publish your agents to the selected website admin pages and frontend pages.</h4>

    <label for="name">Selected Agent</label>
    <select name="agent_id" required id="agent-select">
        <option value="">Select an agent...</option>
        <?php foreach ($agents as $agent): ?>
            <option value="<?php echo esc_attr($agent->id); ?>"><?php echo esc_html($agent->name); ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Page Selection -->
    <h2>Agent Scope</h2>
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
                <?php foreach ($admin_pages as $page): ?>
                    <option value="<?php echo esc_attr($page['slug']); ?>">
                        <?php echo esc_html($page['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="wpaa-row-middle">
        <button type="submit" id="wpaa_publish_agent_button">Publish Agent</button>
    </div>
    </form>
</div>