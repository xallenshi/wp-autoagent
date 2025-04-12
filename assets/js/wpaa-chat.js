jQuery(document).ready(function($) {
    // Capture the current page slug
    const currentPageSlug = window.location.pathname + window.location.search;

    // Check if current page is in scope via AJAX
    $.ajax({
        url: wpaa_request_nonce.ajaxurl,
        type: 'POST',
        data: {
            action: 'wpaa_check_agent_scope',
            nonce: wpaa_request_nonce.nonce,
            current_url: window.location.href,
            page_slug: currentPageSlug
        },
        success: function(response) {
            if (!response.success) {
                console.log('No agent found for this page.');
                return;
            }
            
            const agentId = response.data.agent_id;
            console.log('Agent ID: ' + agentId);
            
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
                <div id="wpaa-chat-header" class="wpaa-chat-header">
                    <span>Chat Assistant</span>
                    <button id="wpaa-chat-close-button" class="wpaa-chat-close-button">&times;</button>
                </div>
                <div id="wpaa-chat-history" class="wpaa-chat-history"></div>
                <div class="wpaa-chat-input-container">
                    <textarea id="wpaa-chat-input" placeholder="Type your message..."></textarea>
                    <button id="wpaa-chat-send-button">Send</button>
                </div>
            `;
            
            // Append elements to body
            document.body.appendChild(chatIcon);
            document.body.appendChild(chatPopup);
            
            // Initially hide the chat popup
            chatPopup.style.display = 'none';
            
            // Toggle chat popup when icon is clicked
            chatIcon.addEventListener('click', function() {
                chatPopup.style.display = 'block';
                chatIcon.style.display = 'none';
            });

            // Close chat when close button clicked
            document.getElementById('wpaa-chat-close-button').addEventListener('click', function() {
                chatPopup.style.display = 'none';
                chatIcon.style.display = 'block';
            });
            
            // Handle the send button click
            const sendButton = document.getElementById('wpaa-chat-send-button');
            const chatInput = document.getElementById('wpaa-chat-input');
            const chatHistory = document.getElementById('wpaa-chat-history');
            
            function sendMessage() {
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
                    loadingDiv.textContent = 'Thinking...';
                    chatHistory.appendChild(loadingDiv);
                    
                    // Scroll to bottom
                    chatHistory.scrollTop = chatHistory.scrollHeight;
                    
                    // Make AJAX call to WordPress to run the agent
                    $.ajax({
                        url: wpaa_request_nonce.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpaa_run_agent',
                            nonce: wpaa_request_nonce.nonce,
                            agent_id: agentId,
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
                            errorDiv.textContent = 'System: An error occurred. Please try again later.';
                            
                            chatHistory.appendChild(errorDiv);
                            chatHistory.scrollTop = chatHistory.scrollHeight;
                        }
                    });
                }
            }

            // Send message on button click
            sendButton.addEventListener('click', sendMessage);
            
            // Send message on Enter key (but allow Shift+Enter for new lines)
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Auto-resize textarea as user types (max 150px height)
            chatInput.addEventListener('input', function() {
                this.style.height = 'auto';
                const maxHeight = 150;
                const newHeight = Math.min(this.scrollHeight, maxHeight);
                this.style.height = newHeight + 'px';
            });

        },
        error: function(xhr, status, error) {
            console.error('Error checking page scope:', error);
        }
    });
});
