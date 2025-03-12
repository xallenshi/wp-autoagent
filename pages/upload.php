<?php
namespace WPAutoAgent\Core;

$db_handler = new DBHandler();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $article = $db_handler->get_article_by_id($id);
}
?>

<h1>Upload Your Article</h1>
<h4>Upload your article to the knowledge base to enhance the AI Agent's ability to provide context-aware answers based on your specific knowledge.</h4>
<h4>For example, your product catalog, product manual, user guide, etc.</h4>
<form id="wpaa_upload_article_form" method="post" enctype="multipart/form-data">
    <input type="file" name="article_file" accept=".txt,.doc,.docx,.xls,.xlsx" required>
    <button type="submit">Upload</button>
</form>

<div id="wpaa_article_list" class="wrap">
    <?php
    $articles = $db_handler->get_articles();
    foreach ($articles as $article) {
        echo "<div class='wpaa_article_item'>{$article->file_name} at {$article->created_time}</div>";
    }
    ?>
</div>

