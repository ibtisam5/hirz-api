<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>تسجيل الدخول | حِرْز</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f0e8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            margin: 0;
            font-size: 42px;
            color: #2d4a3e;
        }

        .logo p {
            color: #777;
            margin-top: 8px;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
        }

        input:focus {
            border-color: #6f8f7f;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #2d4a3e;
            color: white;
            font-size: 17px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.92;
        }

        .message {
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
        }

        .error {
            color: #b3261e;
        }

        .success {
            color: #2d6a4f;
        }
    </style>
</head>

<body>

<div class="login-card">

    <div class="logo">
        <h1>حِرْز</h1>
        <p>مساحة آمنة لقلبك</p>
    </div>

    <div class="field">
        <label for="email">البريد الإلكتروني</label>
        <input
            type="email"
            id="email"
            placeholder="example@email.com"
        >
    </div>

    <div class="field">
        <label for="password">كلمة المرور</label>
        <input
            type="password"
            id="password"
            placeholder="••••••••"
        >
    </div>

    <button id="loginButton">
        تسجيل الدخول
    </button>

    <div id="message" class="message"></div>

</div>

<script>
    const loginButton = document.getElementById('loginButton');
    const messageBox = document.getElementById('message');

    loginButton.addEventListener('click', async () => {

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        messageBox.textContent = '';

        try {

            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email,
                    password
                })
            });

            const data = await response.json();

            if (!response.ok) {
                messageBox.className = 'message error';
                messageBox.textContent =
                    data.message || 'تعذر تسجيل الدخول';
                return;
            }

            localStorage.setItem('hirz_token', data.token);

messageBox.className = 'message success';
messageBox.textContent = 'تم تسجيل الدخول بنجاح';

setTimeout(() => {
    window.location.href = '/home';
}, 700);

        } catch (error) {

            messageBox.className = 'message error';
            messageBox.textContent =
                'حدث خطأ في الاتصال بالخادم';

            console.error(error);
        }

    });
</script>

</body>
</html>
