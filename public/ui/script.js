document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('messageInput');
    const chatContent = document.getElementById('chatContent');
    const soundToggle = document.getElementById('soundToggle');
    const refreshChat = document.getElementById('refreshChat');
    const voiceButton = document.getElementById('voiceButton');
    const speechStatus = document.getElementById('speechStatus');
    
    let isSoundEnabled = true;
    let isRecording = false;
    let recognition = null;
    let silenceTimer = null;

    // Speech Recognition Setup
    function initializeSpeechRecognition() {
        // Check if browser supports speech recognition
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.warn('Speech recognition not supported in this browser');
            voiceButton.style.display = 'none';
            return false;
        }

        // Initialize speech recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.lang = 'tr-TR'; // Türkçe için, değiştirilebilir

        // Speech recognition events
        recognition.onstart = function() {
            isRecording = true;
            voiceButton.classList.add('recording');
            speechStatus.textContent = 'Dinleniyor...';
            speechStatus.classList.add('active');
        };

        recognition.onresult = function(event) {
            let finalTranscript = '';
            let interimTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interimTranscript += transcript;
                }
            }

            // Show interim results
            if (interimTranscript) {
                messageInput.value = interimTranscript;
                
                // Reset silence timer when user is speaking
                if (silenceTimer) {
                    clearTimeout(silenceTimer);
                }
            }

            // Process final result
            if (finalTranscript) {
                messageInput.value = finalTranscript;
                
                // Start silence timer - wait 1.5 seconds before stopping
                if (silenceTimer) {
                    clearTimeout(silenceTimer);
                }
                
                silenceTimer = setTimeout(() => {
                    if (isRecording) {
                        recognition.stop();
                    }
                }, 1500); // 1.5 saniye bekle
            }
        };

        recognition.onerror = function(event) {
            if (event.error === 'aborted') {
                // Kullanıcı manuel olarak durdurdu, hiçbir şey gösterme
                speechStatus.classList.remove('active');
                speechStatus.textContent = '';
                return;
            }
            console.error('Speech recognition error:', event.error);
            speechStatus.textContent = 'Hata: ' + event.error;
            setTimeout(() => {
                speechStatus.classList.remove('active');
                speechStatus.textContent = '';
            }, 2000);
        };

        recognition.onend = function() {
            isRecording = false;
            voiceButton.classList.remove('recording');
            speechStatus.classList.remove('active');
            
            if (silenceTimer) {
                clearTimeout(silenceTimer);
            }
        };

        return true;
    }

    // Voice button click handler
    voiceButton.addEventListener('click', function() {
        if (!recognition) {
            if (!initializeSpeechRecognition()) {
                alert('Bu tarayıcıda ses tanıma desteklenmiyor.');
                return;
            }
        }

        if (isRecording) {
            recognition.stop();
        } else {
            recognition.start();
        }
    });

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

    // Initialize speech recognition
    initializeSpeechRecognition();

    // Initialize perfect scrollbar or any other enhancements
    initializeEnhancements();
});

function initializeEnhancements() {
    // Add any additional UI enhancements here
    console.log('UI enhancements initialized');
}
