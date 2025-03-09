<?php
namespace WPAutoAgent\Core;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form data here
    $assistant_name = $_POST['assistant_name'] ?? '';
    $instruction = $_POST['instruction'] ?? '';
    $model = $_POST['model'] ?? '';
    $selected_files = $_POST['files'] ?? [];
    $selected_functions = $_POST['functions'] ?? [];
    // Add logic to handle the creation of the AI Assistant
}
?>

<div class="sc-plugin-container">
    <form id="sc_create_ai_assistant_form" method="post">
    <h1>Create Your AI Agent</h1>
    <h4>The AI Agent is a large language model driven assistant assist your customers with a collection of tools and instructions in answering questions and automating tasks.</h4>
    
        <!-- Basic Info -->
        <h2>Basic Info</h2>
        <label for="agent_name">AI Agent Name:</label>
        <input type="text" id="agent_name" name="agent_name" required>

        <label for="agent_instruction">Agent Instruction:</label>
        <textarea id="agent_instruction" name="agent_instruction" required></textarea>

        <label for="model">Model:</label>
        <select id="model" name="model">
            <option value="gpt-40">gpt-40</option>
            <option value="gpt-mini">gpt-mini</option>
            <!-- Add more models as needed -->
        </select>

        <!-- Knowledge Base -->
        <h2>Knowledge Base</h2>
        <label>Select Files:</label>
        <div style="display: flex; align-items: center; margin-bottom: 10px;">
            <input type="checkbox" id="file1" name="files[]" value="file1">
            <label for="file1" style="margin-left: 8px;">File 1</label>
        </div>
        <div style="display: flex; align-items: center; margin-bottom: 10px;">
            <input type="checkbox" id="file2" name="files[]" value="file2">
            <label for="file2" style="margin-left: 8px;">File 2</label>
        </div>
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