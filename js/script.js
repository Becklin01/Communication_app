let messageLoadTimeout = null;
let lastRenderedMessageKey = '';

function sendMessage() {
    const messageText = document.getElementById('messageText');
    const sendButton = document.querySelector('.message-input button');

    if (!messageText || !sendButton) {
        return;
    }

    const message = messageText.value.trim();
    if (message === '') {
        alert('Please enter a message.');
        return;
    }

    sendButton.disabled = true;
    sendButton.textContent = 'Sending...';

    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            receiver_id: receiverId,
            message: message
        })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Unable to send message');
            }

            messageText.value = '';
            lastRenderedMessageKey = '';
            loadMessages();
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'Error sending message.');
        })
        .finally(() => {
            sendButton.disabled = false;
            sendButton.textContent = 'Send';
        });
}

function loadMessages() {
    const messagesArea = document.getElementById('messagesArea');
    if (!messagesArea) {
        return;
    }

    if (messageLoadTimeout) {
        clearTimeout(messageLoadTimeout);
    }

    messagesArea.classList.add('loading');

    fetch(`get_messages.php?other_user_id=${receiverId}&t=${Date.now()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Unable to load messages');
            }
            return response.json();
        })
        .then(messages => displayMessages(messages))
        .catch(error => {
            console.error(error);
        })
        .finally(() => {
            messagesArea.classList.remove('loading');
            messageLoadTimeout = window.setTimeout(loadMessages, 3000);
        });
}

function displayMessages(messages) {
    const messagesArea = document.getElementById('messagesArea');
    if (!messagesArea) {
        return;
    }

    const messageKey = JSON.stringify(messages.map(message => [message.id, message.created_at]));
    const shouldStickToBottom =
        messagesArea.scrollHeight - messagesArea.clientHeight <= messagesArea.scrollTop + 50;

    if (messageKey === lastRenderedMessageKey) {
        if (shouldStickToBottom) {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
        return;
    }

    lastRenderedMessageKey = messageKey;
    messagesArea.innerHTML = '';

    messages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${Number(message.sender_id) === Number(userId) ? 'sent' : 'received'}`;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = message.message;

        const infoDiv = document.createElement('div');
        infoDiv.className = 'message-info';
        const date = new Date(message.created_at.replace(' ', 'T'));
        const timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const dateString = date.toLocaleDateString();
        infoDiv.textContent = `${message.sender_name} - ${dateString} ${timeString}`;

        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(infoDiv);
        messagesArea.appendChild(messageDiv);
    });

    if (shouldStickToBottom || messages.length > 0) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const messageText = document.getElementById('messageText');
    if (messageText) {
        messageText.addEventListener('keypress', event => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });

        messageText.focus();
        loadMessages();
    }
});

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        if (messageLoadTimeout) {
            clearTimeout(messageLoadTimeout);
            messageLoadTimeout = null;
        }
        return;
    }

    loadMessages();
});
