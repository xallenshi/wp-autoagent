// Utility: Create an element with optional class and innerHTML
function createElement(tag, className = '', innerHTML = '') {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (innerHTML) el.innerHTML = innerHTML;
    return el;
}

// Utility: Scroll chat history to bottom
function scrollToBottom(el) {
    el.scrollTop = el.scrollHeight;
}

// Utility: Render a chat message (user or agent)
function renderMessage({ type, name, message, isError = false }) {
    const currentTime = new Date().toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const wrapper = createElement('div', `wpaa-chat-${type}`);
    
    // Create header row container
    const headerRow = createElement('div', `wpaa-chat-${type}-header`);
    
    // Create profile photo div
    //const photoDiv = createElement('div', `wpaa-chat-${type}-photo`);
    //const photoImg = createElement('img');
    //photoImg.src = type === 'agent' ? '/path/to/agent-avatar.png' : '/path/to/user-avatar.png';
    //photoDiv.appendChild(photoImg);
    
    const nameTimeContainer = createElement('div', `wpaa-chat-${type}-name-time`);
    const nameDiv = createElement('div', `wpaa-chat-${type}-name`, `<b>${name}</b>`);
    const timeDiv = createElement('div', `wpaa-chat-${type}-time`, currentTime);
    
    nameTimeContainer.appendChild(nameDiv);
    nameTimeContainer.appendChild(timeDiv);
    
    //headerRow.appendChild(photoDiv);
    headerRow.appendChild(nameTimeContainer);
    
    const msgDiv = createElement('div', `wpaa-chat-${type}-message${isError ? ' wpaa-error' : ''}`);
    const safeMessage = linkify(message);
    msgDiv.innerHTML = safeMessage;
    
    wrapper.appendChild(headerRow);
    wrapper.appendChild(msgDiv);
    return wrapper;
}

// Utility: Show/hide elements
function show(el) { el.style.display = 'block'; }
function hide(el) { el.style.display = 'none'; }
function flex(el) { el.style.display = 'flex'; }


function linkify(text) {
    const urlPattern = /\b(?:https?:\/\/|www\.)[^\s<>\]]+\b(?<!\.|,|;|:)/gi;
    let linked = text.replace(urlPattern, function(match) {
        let href = match;
        let displayUrl = match;
        if (!/^https?:\/\//i.test(href)) {
            href = 'https://' + href;
        }
        href = href.replace(/\/+$/, '');
        displayUrl = displayUrl.replace(/\/+$/, '');
        displayUrl = displayUrl.replace(/[.,;:!?]+$/, '');
        return `<a href="${href}" target="_blank" rel="noopener noreferrer">${displayUrl}</a>`;
    });
    // Unescape single-escaped \'
    linked = linked.replace(/\\'/g, "'");
    // Replace newlines with <br>
    return linked.replace(/\n/g, '<br>');
}


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
            const agentName = response.data.name;
            const agentGreetingMessage = response.data.greeting_message;

            window.agentId = agentId;
            window.agentName = agentName;
            window.agentGreetingMessage = agentGreetingMessage;

            // --- UI Elements ---
            const chatIcon = createElement('div', 'wpaa-chat-icon', `
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="currentColor"/>
                </svg>
            `);
            chatIcon.id = 'wpaa-chat-icon';
            
            const chatPopup = createElement('div', 'wpaa-chat-popup', `
                <div id="wpaa-chat-header" class="wpaa-chat-header">
                    <span>WP AutoAgent &copy; Xsolutions</span>
                    <button id="wpaa-chat-close-button" class="wpaa-chat-close-button">&times;</button>
                </div>
                <div id="wpaa-chat-history" class="wpaa-chat-history"></div>
                <div class="wpaa-chat-input-container">
                    <textarea id="wpaa-chat-input" placeholder="Type your message..."></textarea>
                    <button id="wpaa-chat-send-button">Send</button>
                </div>
            `);
            chatPopup.id = 'wpaa-chat-popup';

            // --- Append elements to body ---
            document.body.appendChild(chatIcon);
            document.body.appendChild(chatPopup);
            // Initially hide the chat popup
            hide(chatPopup);
            hide(chatIcon);
            
            // --- DOM References ---
            const sendButton = document.getElementById('wpaa-chat-send-button');
            const chatInput = document.getElementById('wpaa-chat-input');
            const chatHistory = document.getElementById('wpaa-chat-history');
            const closeButton = document.getElementById('wpaa-chat-close-button');

            window.sendButton = sendButton;
            window.chatInput = chatInput;
            window.chatHistory = chatHistory;
            window.closeButton = closeButton;
            
            // --- UI Events ---
            chatIcon.addEventListener('click', function() {
                show(chatPopup);
                scrollToBottom(chatHistory);
                hide(chatIcon);
                chatInput.focus();
            });
            closeButton.addEventListener('click', function() {
                hide(chatPopup);
                flex(chatIcon);
            });
            
            // Auto-resize textarea as user types
            chatInput.addEventListener('input', function() {
                chatInput.style.height = 'auto';
                chatInput.style.height = `${chatInput.scrollHeight}px`;
            });
            
            // Send message on button click or Enter (not Shift+Enter)
            sendButton.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // --- Chat Logic ---
            function sendMessage() {
                const userMessage = chatInput.value.trim();
                if (!userMessage) return;
                
                // Add user message
                chatHistory.appendChild(renderMessage({ type: 'user', name: 'You', message: userMessage }));
                scrollToBottom(chatHistory);
                chatInput.value = '';
                
                // Add loading indicator with typing animation delay
                const loadingMsg = renderMessage({ type: 'agent', name: agentName, message: 'Analyzing...', isError: false });
                setTimeout(() => {
                    loadingMsg.querySelector('.wpaa-chat-agent-message').classList.add('loading');
                    chatHistory.appendChild(loadingMsg);
                    scrollToBottom(chatHistory);
                }, 500);
                
                // Animate loading
                let dotCount = 0;
                const maxDots = 3;
                const baseText = 'Analyzing';
                const loadingDiv = loadingMsg.querySelector('.wpaa-chat-agent-message');
                const loadingInterval = setInterval(() => {
                    loadingDiv.innerHTML = baseText + '.'.repeat(dotCount);
                    dotCount = dotCount < maxDots ? dotCount + 1 : 1;
                }, 500);
                
                // AJAX: run agent
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
                        clearInterval(loadingInterval);
                        if (chatHistory.contains(loadingMsg)) {
                            chatHistory.removeChild(loadingMsg);
                        }
                        if (response.success) {
                            chatHistory.appendChild(renderMessage({ type: 'agent', name: agentName, message: response.data }));
                        } else {
                            chatHistory.appendChild(renderMessage({ type: 'agent', name: agentName, message: `<b>[System]</b> ${response.data}`, isError: true }));
                        }
                        scrollToBottom(chatHistory);
                    },
                    error: function() {
                        clearInterval(loadingInterval);
                        if (chatHistory.contains(loadingMsg)) {
                            chatHistory.removeChild(loadingMsg);
                        }
                        chatHistory.appendChild(renderMessage({ type: 'agent', name: agentName, message: '<b>[System]</b> An error occurred. Please try again later.', isError: true }));
                        scrollToBottom(chatHistory);
                    }
                });
            }

            // Copy theme styles to Chat Panel
            copyThemeStyles();
            
            // --- Load chat history from server ---
            const sessionId = wpaa_request_nonce.session_id;
            loadChatHistoryFromServer(agentId, agentName, agentGreetingMessage, sessionId, chatHistory, function() {
                flex(chatIcon);
                hide(chatPopup);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error checking page scope:', error);
        }
    });


    function loadChatHistoryFromServer(agentId, agentName, agentGreetingMessage, sessionId, chatHistoryElement, callback) {
        jQuery.ajax({
            url: wpaa_request_nonce.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_get_chat_history',
                nonce: wpaa_request_nonce.nonce,
                agent_id: agentId,
                session_id: sessionId
            },
            success: function(response) {
                chatHistoryElement.innerHTML = '';
                let hasHistory = false;
                if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                    hasHistory = true;
                    response.data.forEach(function(entry) {
                        if (entry.content) {
                            chatHistoryElement.appendChild(renderMessage({ type: 'user', name: 'You', message: entry.content }));
                        }
                        if (entry.response) {
                            chatHistoryElement.appendChild(renderMessage({ type: 'agent', name: agentName, message: entry.response }));
                        }
                    });
                }

                // If no history, show greeting
                if (!hasHistory) {
                    chatHistoryElement.appendChild(renderMessage({ type: 'agent', name: agentName, message: agentGreetingMessage }));
                    // Save the greeting message to the database for history display across sessions
                    jQuery.ajax({
                        url: wpaa_request_nonce.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpaa_save_conversation',
                            nonce: wpaa_request_nonce.nonce,
                            agent_id: agentId,
                            session_id: sessionId,
                            content: null,
                            api_msg: agentGreetingMessage
                        }
                    });
                }

                scrollToBottom(chatHistoryElement);
                if (typeof callback === 'function') callback();
            },
            error: function() {
                if (typeof callback === 'function') callback();
            }
        });
    }


    function copyThemeStyles() {
        $.ajax({
            url: wpaa_request_nonce.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_get_theme_color',
                nonce: wpaa_request_nonce.nonce
            },
            success: function(response) {
                //console.log(response);
                const $majorColor = response.data;
                const $chatHeader = $('#wpaa-chat-header');
                const $chatSendButton = $('#wpaa-chat-send-button');
                const $chatIcon = $('.wpaa-chat-icon');

                $chatHeader.css({
                    'background': $majorColor,
                });
                $chatSendButton.css({
                    'background': $majorColor,
                });
                $chatIcon.css({
                    'background': $majorColor,
                });
                
                return true;
                
            },
            error: function(response) {
                console.log(response);
            }
        });
    }


    

    
});
