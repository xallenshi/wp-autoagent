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
                console.log('No agent published for this page.');
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
                chatIcon.style.display = 'flex';
            });
            
            // Handle the send button click
            const sendButton = document.getElementById('wpaa-chat-send-button');
            const chatInput = document.getElementById('wpaa-chat-input');
            const chatHistory = document.getElementById('wpaa-chat-history');
            
            function sendMessage() {
                const userMessage = chatInput.value.trim();
                if (userMessage) {
                    // Add user message to chat history
                    const userWrapper = document.createElement('div');
                    userWrapper.className = 'wpaa-chat-user';

                    const userNameDiv = document.createElement('div');
                    userNameDiv.className = 'wpaa-chat-user-name';
                    userNameDiv.innerHTML = '<b>You:</b>';

                    const userDiv = document.createElement('div');
                    userDiv.className = 'wpaa-chat-user-message';
                    userDiv.innerHTML = userMessage;

                    userWrapper.appendChild(userNameDiv);
                    userWrapper.appendChild(userDiv);
                    chatHistory.appendChild(userWrapper);
                    
                    // Clear input field
                    chatInput.value = '';
                    
                    // Show loading indicator (same structure as agent message)
                    const loadingWrapper = document.createElement('div');
                    loadingWrapper.className = 'wpaa-chat-agent';

                    const loadingNameDiv = document.createElement('div');
                    loadingNameDiv.className = 'wpaa-chat-agent-name';
                    loadingNameDiv.innerHTML = '<b>Agent:</b>';

                    const loadingMsgDiv = document.createElement('div');
                    loadingMsgDiv.className = 'wpaa-chat-agent-message loading';
                    loadingMsgDiv.innerHTML = 'Analyzing...';

                    loadingWrapper.appendChild(loadingNameDiv);
                    loadingWrapper.appendChild(loadingMsgDiv);
                    chatHistory.appendChild(loadingWrapper);
                    
                    // Animate the loading message
                    let dotCount = 0;
                    const maxDots = 3;
                    const baseText = 'Analyzing';
                    const loadingInterval = setInterval(() => {
                        loadingMsgDiv.innerHTML = baseText + '.'.repeat(dotCount);
                        dotCount = dotCount < maxDots ? dotCount + 1 : 1;
                    }, 500);
                    
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
                            clearInterval(loadingInterval);
                            chatHistory.removeChild(loadingWrapper);
                            
                            // Create agent response wrapper
                            const agentWrapper = document.createElement('div');
                            agentWrapper.className = 'wpaa-chat-agent';

                            // Create agent name element
                            const agentNameDiv = document.createElement('div');
                            agentNameDiv.className = 'wpaa-chat-agent-name';
                            agentNameDiv.innerHTML = '<b>Agent:</b>';

                            // Create agent message bubble
                            const agentDiv = document.createElement('div');
                            agentDiv.className = 'wpaa-chat-agent-message';
                            
                            if (response.success) {
                                agentDiv.innerHTML = response.data;
                            } else {
                                agentDiv.innerHTML = '<b>System:</b><br>' + response.data;
                                agentDiv.classList.add('wpaa-error');
                            }

                            // Append name and message to wrapper
                            agentWrapper.appendChild(agentNameDiv);
                            agentWrapper.appendChild(agentDiv);
                            
                            // Append wrapper to chat history
                            chatHistory.appendChild(agentWrapper);
                            chatHistory.scrollTop = chatHistory.scrollHeight;
                        },
                        error: function(xhr, status, error) {
                            // Remove loading indicator
                            clearInterval(loadingInterval);
                            chatHistory.removeChild(loadingWrapper);
                            
                            // Create error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'wpaa-chat-agent-message error';
                            errorDiv.innerHTML = '<b>System:</b> An error occurred. Please try again later.';
                            
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

            // Auto-resize textarea as user types
            const autoResizeTextarea = () => {
                chatInput.style.height = 'auto'; // Reset height first
                chatInput.style.height = `${chatInput.scrollHeight}px`;
            };

            chatInput.addEventListener('input', autoResizeTextarea);

        },
        error: function(xhr, status, error) {
            console.error('Error checking page scope:', error);
        }
    });
});
