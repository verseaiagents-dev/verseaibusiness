document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('messageInput');
    const chatContent = document.getElementById('chatContent');
    const soundToggle = document.getElementById('soundToggle');
    const refreshChat = document.getElementById('refreshChat');
    let isSoundEnabled = true;

    // Sound toggle functionality
    soundToggle.addEventListener('click', function() {
        isSoundEnabled = !isSoundEnabled;
        this.style.opacity = isSoundEnabled ? '1' : '0.5';
    });

    // Refresh chat functionality
    refreshChat.addEventListener('click', function() {
        this.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            this.style.transform = 'none';
        }, 500);
    });

    // Message sending functionality
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            addMessage(this.value, 'user');
            this.value = '';
            
            // Simulate AI response after a short delay
            setTimeout(() => {
                addMessage('Thank you for your message. I\'m processing your request.', 'agent');
            }, 1000);
        }
    });

    function addMessage(text, type) {
        const messageGroup = document.createElement('div');
        messageGroup.className = `message-group ${type}`;

        if (type === 'agent') {
            // Add agent avatar for agent messages
            const avatar = document.createElement('div');
            avatar.className = 'agent-avatar';
            avatar.innerHTML = '<img src="https://via.placeholder.com/40" alt="Agent Avatar">';
            messageGroup.appendChild(avatar);
        }

        const message = document.createElement('div');
        message.className = `message ${type}-message`;
        message.innerHTML = `<p>${text}</p>`;
        messageGroup.appendChild(message);

        chatContent.appendChild(messageGroup);
        chatContent.scrollTop = chatContent.scrollHeight;

        // Play sound if enabled
        if (isSoundEnabled) {
            playMessageSound(type);
        }
    }

    function playMessageSound(type) {
        // Add sound implementation here
        console.log(`Playing ${type} message sound`);
    }

    // Initialize perfect scrollbar or any other enhancements
    initializeEnhancements();
});

function initializeEnhancements() {
    // Add any additional UI enhancements here
    console.log('UI enhancements initialized');
}
