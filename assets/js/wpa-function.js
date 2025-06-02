// Handles the click event for #wpa-function1-button and defines sendMessage2
// This file assumes that agentId, agentName, chatHistory, chatInput, and renderMessage are available in the global scope or imported as needed.

function sendMessage2() {
    const userMessage = 'xxx, x@x.com';
    if (!userMessage) return;


    // Add user message
    chatHistory.appendChild(renderMessage({ type: 'user', name: 'You', message: userMessage }));
    scrollToBottom(chatHistory);
    chatInput.value = '';

    // Add loading indicator with typing animation delay
    const loadingMsg = renderMessage({ type: 'agent', name: agentName, message: 'Analyzing...', isError: false });
    setTimeout(() => {
        loadingMsg.querySelector('.wpa-chat-agent-message').classList.add('loading');
        chatHistory.appendChild(loadingMsg);
        scrollToBottom(chatHistory);
    }, 500);

    // Animate loading
    let dotCount = 0;
    const maxDots = 3;
    const baseText = 'Analyzing';
    const loadingDiv = loadingMsg.querySelector('.wpa-chat-agent-message');
    const loadingInterval = setInterval(() => {
        loadingDiv.innerHTML = baseText + '.'.repeat(dotCount);
        dotCount = dotCount < maxDots ? dotCount + 1 : 1;
    }, 500);

    // AJAX: run agent
    jQuery.ajax({
        url: wpa_request_nonce.ajaxurl,
        type: 'POST',
        data: {
            action: 'wpa_run_agent',
            nonce: wpa_request_nonce.nonce,
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

// Attach the click handler
document.addEventListener('DOMContentLoaded', function() {
    jQuery(document).on('click', '#wpa-function1-button', function(e) {
        e.preventDefault();
        console.log('function1Button clicked');
        sendMessage2();
    });
});

// Export for use elsewhere (if using modules)
if (typeof window !== 'undefined') {
    window.sendMessage2 = sendMessage2;
}
