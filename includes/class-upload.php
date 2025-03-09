<?php
namespace WPAutoAgent\Core;

use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;

class Upload {
    private $table_article;
    private $table_article_chunk;

    public function __construct() {
        $this->table_article = Config::get_table_name('article');
        $this->table_article_chunk = Config::get_table_name('article_chunk');
        add_action('wp_ajax_wp_autoagent_settings_upload', array($this, 'wp_autoagent_settings_upload'));
    }

    public function wp_autoagent_settings_upload() {
        if (!check_ajax_referer('wp_autoagent_settings', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_FILES['article_file']) || $_FILES['article_file']['size'] == 0) {
            wp_send_json_error('No file uploaded or file is empty.');
            return;
        }

        $file = $_FILES['article_file'];

        // Save header info to article table and get article_id
        $article_id = $this->save_article($file);
        if (!$article_id) {
            wp_send_json_error('Failed to save article header info.');
            return;
        }

        $chunks = $this->chunk_file($file);
        foreach ($chunks as $chunk) {
            // Validate chunk content
            if ($this->is_valid_chunk($chunk)) {
                $result = $this->save_chunk($chunk, $article_id);
            } else {
                continue;
            }
        }

        $api_result = $this->embed_chunks($chunks);

        if (!$api_result) {
            wp_send_json_error('Failed to embed chunks.');
        } else {
            wp_send_json_success('Article uploaded and processed successfully.');
        }
    }

    private function chunk_file($file) {
        $chunks = [];
        $file_type = $file['type'];

        switch ($file_type) {
            case 'text/plain':
                // Process plain text files
                $file_contents = file_get_contents($file['tmp_name']);
                $chunks = explode("\n\n", $file_contents);
                break;

            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                // Process Word documents
                $chunks = $this->chunk_word_document($file['tmp_name']);
                break;

            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                // Process Excel files
                $chunks = $this->chunk_excel_document($file['tmp_name']);
                break;

            // Add more cases for other file types if needed

            default:
                // Handle unsupported file types
                error_log("Unsupported file type: " . $file_type);
                break;
        }

        return $chunks;
    }

    private function chunk_word_document($file_path) {
        $chunks = [];

        // Load the Word document
        $phpWord = WordIOFactory::load($file_path);

        // Extract the text from the document
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n\n";
                }
            }
        }

        // Split the text into chunks based on double newlines
        $chunks = explode("\n\n", $text);
        return $chunks;
    }

    private function chunk_excel_document($file_path) {
        $chunks = [];

        // Load the Excel file
        $spreadsheet = SpreadsheetIOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();

        // Iterate through each row in the worksheet
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells, even if they are empty

            $row_data = [];
            foreach ($cellIterator as $cell) {
                $row_data[] = $cell->getValue();
            }

            // Convert the row data to a string and add it to the chunks array
            $chunks[] = implode("\t", $row_data); // Use tab as a delimiter between cell values
        }

        return $chunks;
    }

    private function save_article($file) {
        global $wpdb;

        $result = $wpdb->insert($this->table_article, array(
            'file_type' => $file['type'],
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'created_time' => current_time('mysql'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id; // Return the article_id
    }

    private function save_chunk($chunk, $article_id) {
        global $wpdb;

        $result = $wpdb->insert($this->table_article_chunk, array(
            'article_id' => $article_id,
            'chunk_content' => $chunk,
            'token_count' => $this->count_tokens($chunk),
        ));

        if ($result === false) {
            error_log('Database chunk insert error: ' . $wpdb->last_error);
            return false;
        }

        return true;
    }

    private function embed_chunks($chunks) {
        $api = new API();
        $response = $api->api_embedding_request($chunks);

        if (is_wp_error($response) || (is_array($response) && isset($response['error']))) {
            error_log('API error in embed_chunks: ' . (is_wp_error($response) ? $response->get_error_message() : $response['error']));
            return false;
        } else {
            error_log('API response: ' . json_encode($response));
            return true;
        }
    }

    private function count_tokens($chunk) {
        // Simple whitespace tokenization
        $tokens = preg_split('/\s+/', trim($chunk));
        return count($tokens);
    }

    private function is_valid_chunk($chunk) {
        $trimmed_chunk = trim($chunk);
        //error_log("Chunk: " . $trimmed_chunk);

        // Check if the chunk is empty or whitespace-only
        if (empty($trimmed_chunk)) {
            error_log("Chunk is empty or whitespace-only.");
            return false;
        }

        // Check if the chunk starts with an uppercase letter and ends with a sentence-ending punctuation
        if (!preg_match('/^[A-Z].*?[.!?](\s|$)/u', $trimmed_chunk)) {
            error_log("Chunk does not match the regex.");
            return false;
        }

        // Check if the chunk has a minimum length (e.g., 10 characters)
        if (strlen($trimmed_chunk) < 10) {
            error_log("Chunk is shorter than 10 characters.");
            return false;
        }

        return true;
    }
}
