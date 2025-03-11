<?php
namespace WPAutoAgent\Core;

$db_handler = new DBHandler();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);
    $chunks = $db_handler->get_article_chunks($article_id);
}
?>

<h1>Upload Your Article</h1>
<h4>Upload your article to the knowledge base to enhance the AI Agent's ability to provide context-aware answers based on your specific knowledge.</h4>
<h4>For example, your product catalog, product manual, user guide, etc.</h4>
<form id="sc_upload_article_form" method="post" enctype="multipart/form-data">
    <input type="file" name="article_file" accept=".txt,.doc,.docx,.xls,.xlsx" required>
    <button type="submit">Upload</button>
</form>

<form method="post">
    <select name="article_id" id="sc_article_select" onchange="this.form.submit()">
        <option value="">Select an article</option>
        <?php
        $articles = $db_handler->get_articles();
        foreach ($articles as $article) {
            $selected = (isset($_POST['article_id']) && $_POST['article_id'] == $article->article_id) ? 'selected' : '';
            echo "<option value='{$article->article_id}' {$selected}>{$article->file_name} at {$article->created_time}</option>";
        }
        ?>
    </select>
</form>

<?php if (isset($chunks) && !empty($chunks)): ?>
    <div id="article_content">
        <h3>Article Content:</h3>
        <table>
            <thead>
                <tr>
                    <th>Chunk ID</th>
                    <th>Chunk Content</th>
                    <th>Token Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chunks as $chunk): ?>
                <tr>
                    <td><?php echo esc_html($chunk->chunk_id); ?></td>
                    <td><?php echo nl2br(esc_html($chunk->chunk_content)); ?></td>
                    <td><?php echo esc_html($chunk->token_count); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button id="submit_article">Submit Article to OpenAI</button>
    </div>
<?php endif; ?>



