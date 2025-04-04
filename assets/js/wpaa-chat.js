document.addEventListener('DOMContentLoaded', function() {
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
            userDiv.textContent = userMessage;
            chatHistory.appendChild(userDiv);
            
            // Clear input field
            chatInput.value = '';
            
            // Here you would typically make an AJAX call to handle the message
            // and get a response from your AI agent
            // For now, we'll just scroll to the bottom
            chatHistory.scrollTop = chatHistory.scrollHeight;
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
