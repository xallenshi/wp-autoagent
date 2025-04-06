document.addEventListener('DOMContentLoaded', function() {
    // Only show the chat icon on wp-autoagent admin pages
    const currentUrl = window.location.href;
    if (!currentUrl.includes('/wp-admin/admin.php?page=wp-autoagent') && !currentUrl.includes('/faq') && !currentUrl.includes('localhost:10003/faq/')) {
        return;
    }
    
    // Create chat icon element
    const chatIcon = document.createElement('div');
    chatIcon.id = 'wpaa-chat-icon';
    chatIcon.className = 'wpaa-chat-icon';
    chatIcon.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="currentColor"/>
        </svg>
    `;
    
    // Create chat popup element
    const chatPopup = document.createElement('div');
    chatPopup.id = 'wpaa-chat-popup';
    chatPopup.className = 'wpaa-chat-popup';
    chatPopup.innerHTML = `
        <div id="wpaa-chat-history" class="wpaa-chat-history"></div>
        <textarea id="wpaa-chat-input" placeholder="Type your message..."></textarea>
        <button id="wpaa-send-button">Send</button>
    `;
    
    // Append elements to body
    document.body.appendChild(chatIcon);
    document.body.appendChild(chatPopup);
    
    // Initially hide the chat popup
    chatPopup.style.display = 'none';
    
    // Toggle chat popup when icon is clicked
    chatIcon.addEventListener('click', function() {
        if (chatPopup.style.display === 'none') {
            chatPopup.style.display = 'block';
        } else {
            chatPopup.style.display = 'none';
        }
    });
    
    // Handle the send button click
    const sendButton = document.getElementById('wpaa-send-button');
    const chatInput = document.getElementById('wpaa-chat-input');
    const chatHistory = document.getElementById('wpaa-chat-history');
    
    sendButton.addEventListener('click', function() {
        const userMessage = chatInput.value.trim();
        if (userMessage) {
            // Add user message to chat history
            const userDiv = document.createElement('div');
            userDiv.className = 'wpaa-chat-user';
            userDiv.textContent = 'You: ' + userMessage;
            chatHistory.appendChild(userDiv);
            
            // Clear input field
            chatInput.value = '';
            
            // Show loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'wpaa-chat-agent wpaa-loading';
            loadingDiv.textContent = 'Typing...';
            chatHistory.appendChild(loadingDiv);
            
            // Scroll to bottom
            chatHistory.scrollTop = chatHistory.scrollHeight;
            
            // Make AJAX call to WordPress to run the agent
            jQuery.ajax({
                url: ajaxurl, // Fixed typo in ajaxUrl
                type: 'POST',
                data: {
                    action: 'wpaa_run_agent',
                    nonce: wpaa_request_nonce.nonce,
                    agent_id: '', // Added missing agent_id parameter
                    content: userMessage
                },
                success: function(response) {
                    // Remove loading indicator
                    chatHistory.removeChild(loadingDiv);
                    
                    // Create agent response element
                    const agentDiv = document.createElement('div');
                    agentDiv.className = 'wpaa-chat-agent';
                    
                    if (response.success) {
                        agentDiv.textContent = 'Agent: ' + response.data;
                    } else {
                        agentDiv.textContent = 'System: ' + response.data;
                        agentDiv.classList.add('wpaa-error');
                    }
                    
                    chatHistory.appendChild(agentDiv);
                    chatHistory.scrollTop = chatHistory.scrollHeight;
                },
                error: function(xhr, status, error) {
                    // Remove loading indicator
                    chatHistory.removeChild(loadingDiv);
                    
                    // Create error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'wpaa-chat-agent wpaa-error';
                    errorDiv.textContent = 'System: An error occurred while processing your request.';
                    
                    chatHistory.appendChild(errorDiv);
                    chatHistory.scrollTop = chatHistory.scrollHeight;
                }
            });
        }
    });
    
    // Allow sending message with Enter key
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendButton.click();
        }
    });
});
