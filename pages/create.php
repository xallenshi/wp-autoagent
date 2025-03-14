<?php
namespace WPAutoAgent\Core;

$db_handler = new DBHandler();
$articles = $db_handler->get_articles();

?>

<div class="wpaa-plugin-container">
    <form id="wpaa_create_agent_form" method="post" enctype="multipart/form-data">
    <h1>Create Your AI Agent</h1>
    <h4>The AI Agent is a large language model driven assistant assist your customers with a collection of tools and instructions in answering questions and automating tasks.</h4>
    
        <!-- Basic Info -->
        <h2>Basic Info</h2>
        <label for="name">AI Agent Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="instructions">Agent Instructions:</label>
        <textarea id="instructions" name="instructions" required></textarea>

        <label for="model">Model:</label>
        <select id="model" name="model">
            <option value="gpt-4o">gpt-4o</option>
            <option value="gpt-4o-mini">gpt-4o-mini</option>
            <!-- Add more models as needed -->
        </select>

        <!-- Knowledge Base -->
        <h2>Knowledge Base</h2>
        <label>Select Files:</label>
        <?php
        
        
        if ($articles) {
            foreach ($articles as $article) {
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <input type="checkbox" id="file_<?php echo esc_attr($article->id); ?>" name="files[]" value="<?php echo esc_attr($article->id); ?>">
                    <label for="file_<?php echo esc_attr($article->id); ?>" ><?php echo esc_html($article->file_name); ?></label>
                </div>
                <?php
            }
        } else {
            ?>
            <div>No files uploaded yet.</div>
            <?php
        }
        ?>
        <!-- Add more files as needed -->

        <!-- Function Enablement -->
        <h2>Function Enablement</h2>
        <label>Select Functions:</label>
        <div>
            <input type="checkbox" id="function1" name="functions[]" value="Submit Forminator Form">
            <label for="function1">Submit Forminator Form</label>
        </div>
        <div>
            <input type="checkbox" id="function2" name="functions[]" value="Subscribe to MailPoet Maillist">
            <label for="function2">Subscribe to MailPoet Maillist</label>
        </div>
        <div>
            <input type="checkbox" id="function3" name="functions[]" value="Track WooCommerce Order">
            <label for="function3">Track WooCommerce Order</label>
        </div>
        <!-- Add more functions as needed -->

        <button type="submit">Create AI Assistant</button>
    </form>
</div>