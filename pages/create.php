<?php
namespace WPAutoAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$articles = $db_handler->get_articles();
$functions = $db_handler->get_functions();

?>


<div class="wpaa-agent-list1">
    <div class="wpaa-panel-header">Agent List</div>
    <ul>
        <li><a href="#" data-agent_id="new">+ New Agent</a></li>
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
    <form id="wpaa_create_agent_form" method="post" enctype="multipart/form-data">
    <h1 id="wpaa_create_agent_title">Create Your Agent</h1>
    <h4>The WordPress Auto Agent is an AI-powered assistant that supports your customers by answering questions based on your knowledge articles and automating tasks through integration with popular WordPress plugins.</h4>
    
        <!-- Basic Info -->
        <h2>Agent Settings</h2>
        <input type="hidden" id="agent_id" name="agent_id" value="">
        <label for="name">Agent Name
          <span class="wpaa-tooltip">?
            <span class="wpaa-tooltip-text">It will appear to your customers in the chat panel.</span>
          </span>
        </label>
        <input type="text" id="name" name="name" required>

        <label for="greeting_message">Greeting Message
            <span class="wpaa-tooltip">?
                <span class="wpaa-tooltip-text">It will be sent to your customers in the chat panel when they start a new chat.</span>
            </span>
        </label>
        <textarea id="greeting_message" name="greeting_message" class="wpaa-textarea" required></textarea>

        <label for="instructions">Agent Instructions
            <span class="wpaa-tooltip">?
                <span class="wpaa-tooltip-text">It is guidelines provided to the AI agent to control how it behaves, responds to your customers' requests.</span>
            </span>
        </label>
        <div class="wpaa-example-container">
            <a href="#" class="wpaa-example-link" id="instructions_example1">Example 1</a>
            <a href="#" class="wpaa-example-link" id="instructions_example2">Example 2</a>
            <a href="#" class="wpaa-example-link" id="instructions_example3">Example 3</a>
        </div>
        <textarea id="instructions" name="instructions" class="wpaa-textarea" required></textarea>

        <label for="model">AI Model</label>
        <select id="model" name="model">
            <option value="gpt-4o">gpt-4o</option>
            <option value="gpt-4o-mini">gpt-4o-mini</option>
            <!-- Add more models as needed -->
        </select>

        <!-- Knowledge Base -->
        <h2>Knowledge Base</h2>
        <label>Select File
            <span class="wpaa-tooltip">?
                <span class="wpaa-tooltip-text">Each agent can only have one file for now. If you have multiple knowledge articles for one agent, please merge them online before uploading.</span>
            </span>
        </label>
        <a href="#" class="wpaa-kb-link" data-page="upload">Upload Your New Knowledge Article</a>


        <?php
        if ($articles) {
            foreach ($articles as $article) {
                ?>
                <div class="wpaa-row">
                    <input type="radio" id="article_<?php echo esc_attr($article->id); ?>" name="articles[]" value="<?php echo esc_attr($article->id); ?>">
                    <label for="article_<?php echo esc_attr($article->id); ?>">
                        <?php echo strtoupper(esc_html($article->file_name)); ?>
                    </label>
                </div>
                <?php
            }
        } else {
            ?>
            <div>No files uploaded yet.</div>
            <?php
        }
        ?>
        
        <!-- Function Enablement -->
        <h2>Function Enablement</h2>
        <label>Select Functions</label>
        
        <?php
    
        if ($functions) {
            foreach ($functions as $function) {
                ?>
                <div class="wpaa-row">
                    <input type="checkbox" id="function_<?php echo esc_attr($function->id); ?>" name="functions[]" value="<?php echo esc_attr($function->id); ?>">
                    <label for="function_<?php echo esc_attr($function->id); ?>">
                        <?php echo strtoupper(esc_html($function->name)); ?> : <?php echo esc_html($function->description); ?>
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

        <hr>
        <div class="wpaa-row-middle">
            <button type="submit" id="wpaa_create_agent_button">Create Agent</button>
        </div>
        <div class="wpaa-row-middle-bottom">
            <a href="#" class="wpaa-delete-agent-link" id="delete_agent_link" style="display:none;">Delete This Agent</a>
        </div>
    </form>
</div>