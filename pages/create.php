<?php
namespace WPAutoAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$articles = $db_handler->get_articles();
$functions = $db_handler->get_functions();

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
        <h2>Only 1 vector store allowed for now</h2>
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
        
        <?php
    
        if ($functions) {
            foreach ($functions as $function) {
                ?>
                <div>
                    <input type="checkbox" id="<?php echo esc_attr($function->id); ?>" name="functions[]" value="<?php echo esc_attr($function->id); ?>">
                    <label for="function_<?php echo esc_attr($function->id); ?>">
                        <?php echo esc_html($function->name); ?> - <?php echo esc_html($function->description); ?>
                    </label>
                </div>
                <?php
            }
        } else {
            ?>
            <div>No functions available.</div>
            <?php
        }
        ?>

        <button type="submit">Create AI Assistant</button>
    </form>
</div>