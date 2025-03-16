jQuery(document).ready(function($) {

    // Send message on button click
    $('#wpaa-send-button').click(function() {
        sendMessage();
    });

    // Send message on Enter key press
    $('#wpaa-chat-input').keydown(function(event) {
        if (event.keyCode === 13) { // Enter key
            event.preventDefault(); // Prevent newline
            sendMessage();
        }
    });

    function sendMessage() {
        var message = $('#wpaa-chat-input').val();
        if (message.trim() === '') return;

        // Display the user's message
        var chatHistory = $('#wpaa-chat-history');
        chatHistory.append('<div class="wpaa-chat-user"><b>You:</b><br>' + message + '</div>');

        // Send AJAX request to the server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_run_agent',
                nonce: wpaa_request_nonce.nonce,
                agent_id: 1,
                thread_id: 1,
                instructions: message,
                content: message
            },
            success: function(response) {
                if (response.success) {
                    chatHistory.append('<div class="wpaa-chat-agent"><b>LLM:</b><br>' + response.data + '</div>');
                } else {
                    chatHistory.append('<div class="wpaa-chat-agent"><b>System:</b><br>' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                chatHistory.append('<div class="wpaa-chat-agent"><b>System:</b><br>' + error + '</div>');
            }
        });

        // Clear the input
        $('#wpaa-chat-input').val('');
    }
});
