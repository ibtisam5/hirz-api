<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>الرئيسية | حِرْز</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f0e8;
            color: #24352e;
        }

        .container {
            width: min(1100px, 92%);
            margin: 0 auto;
            padding: 45px 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .brand {
            font-size: 34px;
            font-weight: bold;
            color: #2d4a3e;
        }

        .logout {
            border: 1px solid #d8d2c7;
            background: transparent;
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            color: #555;
        }

        .hero {
            background: #ffffff;
            border-radius: 28px;
            padding: 38px;
            box-shadow: 0 14px 45px rgba(0,0,0,.06);
        }

        .welcome {
            color: #7b756c;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 34px;
        }

        .subtitle {
            color: #777;
            margin-bottom: 28px;
        }

        .emotions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 25px;
        }

        .emotion {
            border: 1px solid #ddd8cf;
            background: #faf8f4;
            padding: 15px;
            border-radius: 16px;
            cursor: pointer;
            font-size: 16px;
            transition: .2s;
        }

        .emotion:hover,
        .emotion.active {
            background: #2d4a3e;
            color: white;
            border-color: #2d4a3e;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            resize: vertical;
            padding: 16px;
            border: 1px solid #ddd8cf;
            border-radius: 16px;
            font-size: 16px;
            font-family: inherit;
            outline: none;
        }

        textarea:focus {
            border-color: #6f8f7f;
        }

        .start {
            width: 100%;
            border: none;
            margin-top: 18px;
            padding: 16px;
            border-radius: 16px;
            background: #2d4a3e;
            color: white;
            font-size: 17px;
            cursor: pointer;
        }

        .start:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .message {
            text-align: center;
            margin-top: 15px;
        }

        .error {
            color: #b3261e;
        }

        .section-title {
            margin: 35px 0 18px;
            font-size: 23px;
        }

        .conversations {
            display: grid;
            gap: 12px;
        }

        .conversation-card {
            background: white;
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,.04);
            cursor: pointer;
        }

        .conversation-title {
            font-weight: bold;
            margin-bottom: 7px;
        }

        .conversation-preview {
            color: #777;
            font-size: 14px;
            line-height: 1.7;
        }

        @media (max-width: 600px) {
            .hero {
                padding: 25px 20px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<div class="container">

   <div class="topbar">

    <div class="brand">حِرْز</div>

    <div style="display:flex; gap:10px;">

        <button
            class="logout"
            onclick="window.location.href='/archived'"
        >
            الأرشيف
        </button>

        <button
            class="logout"
            id="logoutButton"
        >
            تسجيل الخروج
        </button>

    </div>

</div>

    <div class="hero">

        <div class="welcome" id="welcome">
            أهلًا بك
        </div>

        <h1>كيف تشعر الآن؟</h1>

        <div class="subtitle">
            اختر الشعور الأقرب إليك، ثم أخبر حِرْز بما تمر به.
        </div>

        <div class="emotions">
            <button class="emotion" data-emotion="قلق">قلق</button>
            <button class="emotion" data-emotion="خوف">خوف</button>
            <button class="emotion" data-emotion="حزن">حزن</button>
            <button class="emotion" data-emotion="ضغط">ضغط</button>
            <button class="emotion" data-emotion="وحدة">وحدة</button>
            <button class="emotion" data-emotion="تردد">تردد</button>
            <button class="emotion" data-emotion="امتنان">امتنان</button>
        </div>

        <textarea
            id="context"
            placeholder="ما الذي جعلك تشعر بهذا الشعور؟ يمكنك الكتابة بطريقتك..."
        ></textarea>

        <button class="start" id="startButton" disabled>
            ابدأ مع حِرْز
        </button>

        <div id="message" class="message"></div>

    </div>

    <h2 class="section-title">
        محادثاتك السابقة
    </h2>

    <div id="conversations" class="conversations">
        جاري التحميل...
    </div>

</div>

<script>
    const token = localStorage.getItem('hirz_token');

    if (!token) {
        window.location.href = '/';
    }

    let selectedEmotion = null;

    const emotionButtons = document.querySelectorAll('.emotion');
    const startButton = document.getElementById('startButton');
    const contextInput = document.getElementById('context');
    const messageBox = document.getElementById('message');


    emotionButtons.forEach(button => {
        button.addEventListener('click', () => {

            emotionButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            button.classList.add('active');

            selectedEmotion = button.dataset.emotion;

            startButton.disabled = false;
        });
    });


    async function loadUser() {

        const response = await fetch('/api/me', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('hirz_token');
            window.location.href = '/';
            return;
        }

        const data = await response.json();

        document.getElementById('welcome').textContent =
            `أهلًا ${data.user.name} 🌿`;
    }


    async function loadConversations() {

        const response = await fetch('/api/conversations', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        const container = document.getElementById('conversations');

        if (!data.conversations.length) {
            container.innerHTML =
                '<div class="conversation-card">لا توجد محادثات بعد.</div>';
            return;
        }

        container.innerHTML = '';

        data.conversations.forEach(conversation => {

            const card = document.createElement('div');

            card.className = 'conversation-card';

            const preview =
                conversation.last_message?.content
                ?? 'لم تبدأ الرسائل بعد';

            card.innerHTML = `
                <div class="conversation-title">
                    ${conversation.title}
                </div>

                <div class="conversation-preview">
                    ${preview}
                </div>
            `;

            card.addEventListener('click', () => {
                window.location.href =
                    `/conversation/${conversation.id}`;
            });

            container.appendChild(card);
        });
    }


    startButton.addEventListener('click', async () => {

        messageBox.textContent = '';

        const response = await fetch('/api/conversations', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },

            body: JSON.stringify({
                emotion: selectedEmotion,
                context: contextInput.value || null
            })
        });

        const data = await response.json();

        if (!response.ok) {
            messageBox.className = 'message error';
            messageBox.textContent =
                data.message || 'تعذر إنشاء المحادثة';
            return;
        }

        window.location.href =
            `/conversation/${data.conversation.id}`;
    });


    document
        .getElementById('logoutButton')
        .addEventListener('click', async () => {

            await fetch('/api/logout', {

                method: 'POST',

                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            localStorage.removeItem('hirz_token');

            window.location.href = '/';
        });


    loadUser();
    loadConversations();

</script>

</body>
</html>
