<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>المحادثة | حِرْز</title>

    <style>
        .chat-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.chat-actions button {
    border: 1px solid #d8d2c7;
    background: #fff;
    padding: 9px 14px;
    border-radius: 10px;
    cursor: pointer;
}

#deleteButton {
    color: #a33;
}
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f0e8;
            color: #24352e;
        }

        .page {
            width: min(1000px, 94%);
            margin: 0 auto;
            padding: 30px 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .brand {
            font-size: 32px;
            font-weight: bold;
            color: #2d4a3e;
        }

        .back {
            border: 1px solid #d8d2c7;
            background: transparent;
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;
            color: #555;
        }

        .chat-card {
            background: white;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 14px 45px rgba(0,0,0,.06);
        }

        .chat-header {
            padding-bottom: 20px;
            border-bottom: 1px solid #eee9e1;
            margin-bottom: 25px;
        }

        .chat-header h1 {
            margin: 0;
            font-size: 28px;
        }

        .chat-header p {
            margin: 8px 0 0;
            color: #7c756c;
        }

        .messages {
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-height: 420px;
            max-height: 560px;
            overflow-y: auto;
            padding: 5px 4px 25px;
        }

        .message-row {
            display: flex;
        }

        .message-row.user {
            justify-content: flex-start;
        }

        .message-row.assistant {
            justify-content: flex-end;
        }

        .bubble {
            max-width: 78%;
            padding: 15px 18px;
            border-radius: 20px;
            line-height: 1.9;
            white-space: pre-wrap;
        }

        .user .bubble {
            background: #2d4a3e;
            color: white;
            border-bottom-right-radius: 6px;
        }

        .assistant .bubble {
            background: #f5f2ec;
            color: #263a31;
            border-bottom-left-radius: 6px;
        }

        .source {
            margin-top: 11px;
            padding-top: 10px;
            border-top: 1px solid rgba(0,0,0,.08);
            font-size: 13px;
            color: #777167;
        }

        .composer {
            border-top: 1px solid #eee9e1;
            padding-top: 20px;
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        textarea {
            flex: 1;
            min-height: 58px;
            max-height: 150px;
            resize: vertical;
            border: 1px solid #ddd8cf;
            border-radius: 16px;
            padding: 14px 16px;
            font-family: inherit;
            font-size: 15px;
            outline: none;
        }

        textarea:focus {
            border-color: #789186;
        }

        .send {
            min-width: 100px;
            height: 58px;
            border: none;
            border-radius: 16px;
            background: #2d4a3e;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        .send:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .status {
            text-align: center;
            color: #777;
            padding: 16px;
        }

        .error {
            color: #b3261e;
        }

        @media (max-width: 650px) {
            .chat-card {
                padding: 18px;
            }

            .bubble {
                max-width: 90%;
            }

            .composer {
                flex-direction: column;
            }

            .send {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <div class="topbar">
        <div class="brand">حِرْز</div>

        <button class="back" onclick="window.location.href='/home'">
            العودة للرئيسية
        </button>
    </div>
<div class="chat-card">

    <div class="chat-header">

        <h1 id="conversationTitle">المحادثة</h1>
        <p id="conversationContext"></p>

        <div class="chat-actions">
            <button id="archiveButton">أرشفة</button>
            <button id="deleteButton">حذف</button>
        </div>

    </div>

    <div id="messages" class="messages">
        <div class="status">
            جاري تحميل المحادثة...
        </div>
    </div>

    <div class="composer">

        <textarea
            id="messageInput"
            placeholder="اكتب ما تشعر به..."
        ></textarea>

        <button id="sendButton" class="send">
            إرسال
        </button>

    </div>

    <div id="statusMessage" class="status"></div>

</div>

</div>

<script>
    const token = localStorage.getItem('hirz_token');
    const conversationId = @json($conversationId);

    const messagesContainer = document.getElementById('messages');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const statusMessage = document.getElementById('statusMessage');
const archiveButton = document.getElementById('archiveButton');
const deleteButton = document.getElementById('deleteButton');

archiveButton.addEventListener('click', async () => {

    const response = await fetch(
        `/api/conversations/${conversationId}/archive`,
        {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        }
    );

    const data = await response.json();

    if (response.ok) {
        window.location.href = '/home';
    } else {
        alert(data.message || 'تعذر أرشفة المحادثة');
    }
});

deleteButton.addEventListener('click', async () => {

    const confirmed = confirm(
        'يجب أرشفة المحادثة أولًا قبل حذفها نهائيًا. هل تريد المتابعة؟'
    );

    if (!confirmed) return;

    const response = await fetch(
        `/api/conversations/${conversationId}`,
        {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        }
    );

    const data = await response.json();

    if (response.ok) {
        window.location.href = '/home';
    } else {
        alert(data.message || 'تعذر حذف المحادثة');
    }
});
    if (!token) {
        window.location.href = '/';
    }


    function addMessage(message) {

        const row = document.createElement('div');
        row.className = `message-row ${message.role}`;

        const bubble = document.createElement('div');
        bubble.className = 'bubble';

        const text = document.createElement('div');
        text.textContent = message.content;

        bubble.appendChild(text);

        if (message.source) {

            const source = document.createElement('div');
            source.className = 'source';

            source.textContent =
                `المصدر: ${message.source.reference}`;

            bubble.appendChild(source);
        }

        row.appendChild(bubble);
        messagesContainer.appendChild(row);
    }


    function scrollToBottom() {
        messagesContainer.scrollTop =
            messagesContainer.scrollHeight;
    }


    async function loadConversation() {

        try {

            const response = await fetch(
                `/api/conversations/${conversationId}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                }
            );

            if (response.status === 401) {
                localStorage.removeItem('hirz_token');
                window.location.href = '/';
                return;
            }

            if (response.status === 403 || response.status === 404) {
                window.location.href = '/home';
                return;
            }

            const data = await response.json();

            document.getElementById('conversationTitle').textContent =
                data.conversation.title;

            document.getElementById('conversationContext').textContent =
                data.conversation.context || '';

            messagesContainer.innerHTML = '';

            if (!data.messages.length) {

                messagesContainer.innerHTML = `
                    <div class="status">
                        ابدأ بالحديث مع حِرْز عمّا تشعر به 🌿
                    </div>
                `;

                return;
            }

            data.messages.forEach(message => {
                addMessage(message);
            });

            scrollToBottom();

        } catch (error) {

            messagesContainer.innerHTML = `
                <div class="status error">
                    تعذر تحميل المحادثة.
                </div>
            `;

            console.error(error);
        }
    }


    async function sendMessage() {

        const content = messageInput.value.trim();

        if (!content) {
            return;
        }

        sendButton.disabled = true;
        messageInput.disabled = true;

        statusMessage.textContent =
            'حِرْز يكتب لك...';

        try {

            const response = await fetch(
                `/api/conversations/${conversationId}/messages`,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },

                    body: JSON.stringify({
                        content: content
                    })
                }
            );

            const data = await response.json();

            if (!response.ok) {
                statusMessage.className = 'status error';
                statusMessage.textContent =
                    data.message || 'تعذر إرسال الرسالة';
                return;
            }

            const emptyStatus =
                messagesContainer.querySelector('.status');

            if (emptyStatus) {
                messagesContainer.innerHTML = '';
            }

            addMessage({
                role: 'user',
                content: data.user_message.content,
                source: null
            });

            addMessage({
                role: 'assistant',
                content: data.reply.text,
                source: data.reply.source
            });

            messageInput.value = '';

            statusMessage.className = 'status';
            statusMessage.textContent = '';

            scrollToBottom();

        } catch (error) {

            statusMessage.className = 'status error';
            statusMessage.textContent =
                'حدث خطأ في الاتصال بالخادم';

            console.error(error);

        } finally {

            sendButton.disabled = false;
            messageInput.disabled = false;
            messageInput.focus();
        }
    }


    sendButton.addEventListener('click', sendMessage);


    messageInput.addEventListener('keydown', event => {

        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }

    });


    loadConversation();

</script>

</body>
</html>
