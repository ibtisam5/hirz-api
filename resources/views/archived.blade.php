<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>المحادثات المؤرشفة | حِرْز</title>

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

        .page {
            width: min(1000px, 92%);
            margin: 0 auto;
            padding: 35px 0 50px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .brand {
            font-size: 34px;
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

        .header-card {
            background: #ffffff;
            border-radius: 26px;
            padding: 30px;
            box-shadow: 0 12px 35px rgba(0,0,0,.05);
            margin-bottom: 25px;
        }

        .header-card h1 {
            margin: 0 0 10px;
            font-size: 30px;
        }

        .header-card p {
            margin: 0;
            color: #7a746c;
            line-height: 1.8;
        }

        .archived-list {
            display: grid;
            gap: 14px;
        }

        .conversation-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 7px 22px rgba(0,0,0,.04);
        }

        .conversation-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .conversation-title {
            font-size: 19px;
            font-weight: bold;
        }

        .conversation-meta {
            color: #898177;
            font-size: 13px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .restore {
            border: none;
            background: #2d4a3e;
            color: white;
            padding: 10px 16px;
            border-radius: 11px;
            cursor: pointer;
        }

        .delete {
            border: 1px solid #d9caca;
            background: #fff;
            color: #a33;
            padding: 10px 16px;
            border-radius: 11px;
            cursor: pointer;
        }

        .empty {
            background: white;
            padding: 35px;
            border-radius: 20px;
            text-align: center;
            color: #777;
        }

        .status {
            text-align: center;
            padding: 20px;
            color: #777;
        }

        .error {
            color: #b3261e;
        }

        @media (max-width: 600px) {
            .conversation-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .actions {
                width: 100%;
            }

            .restore,
            .delete {
                flex: 1;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <div class="topbar">
        <div class="brand">حِرْز</div>

        <button
            class="back"
            onclick="window.location.href='/home'"
        >
            العودة للرئيسية
        </button>
    </div>

    <div class="header-card">
        <h1>المحادثات المؤرشفة</h1>

        <p>
            هنا تجد المحادثات التي اخترت أرشفتها.
            يمكنك استعادتها مرة أخرى أو حذفها نهائيًا.
        </p>
    </div>

    <div id="archivedList" class="archived-list">
        <div class="status">
            جاري تحميل المحادثات...
        </div>
    </div>

</div>

<script>
    const token = localStorage.getItem('hirz_token');

    const archivedList =
        document.getElementById('archivedList');

    if (!token) {
        window.location.href = '/';
    }


    function formatDate(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);

        return date.toLocaleDateString('ar-OM', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }


    async function loadArchived() {

        try {

            const response = await fetch(
                '/api/conversations/archived/list',
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

            const data = await response.json();

            archivedList.innerHTML = '';

            if (!data.conversations.length) {

                archivedList.innerHTML = `
                    <div class="empty">
                        لا توجد محادثات مؤرشفة حاليًا 🌿
                    </div>
                `;

                return;
            }

            data.conversations.forEach(conversation => {

                const card = document.createElement('div');

                card.className = 'conversation-card';

                card.innerHTML = `
                    <div class="conversation-top">

                        <div>
                            <div class="conversation-title">
                                ${conversation.title}
                            </div>

                            <div class="conversation-meta">
                                ${formatDate(conversation.updated_at)}
                            </div>
                        </div>

                    </div>

                    <div class="actions">

                        <button
                            class="restore"
                            data-id="${conversation.id}"
                        >
                            استعادة المحادثة
                        </button>

                        <button
                            class="delete"
                            data-id="${conversation.id}"
                        >
                            حذف نهائي
                        </button>

                    </div>
                `;

                archivedList.appendChild(card);
            });


            document
                .querySelectorAll('.restore')
                .forEach(button => {

                    button.addEventListener('click', () => {
                        restoreConversation(button.dataset.id);
                    });

                });


            document
                .querySelectorAll('.delete')
                .forEach(button => {

                    button.addEventListener('click', () => {
                        deleteConversation(button.dataset.id);
                    });

                });

        } catch (error) {

            archivedList.innerHTML = `
                <div class="status error">
                    تعذر تحميل المحادثات المؤرشفة.
                </div>
            `;

            console.error(error);
        }
    }


    async function restoreConversation(id) {

        const response = await fetch(
            `/api/conversations/${id}/restore`,
            {
                method: 'PATCH',

                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            }
        );

        const data = await response.json();

        if (!response.ok) {
            alert(
                data.message ||
                'تعذر استعادة المحادثة'
            );

            return;
        }

        loadArchived();
    }


    async function deleteConversation(id) {

        const confirmed = confirm(
            'سيتم حذف هذه المحادثة نهائيًا ولن تتمكن من استعادتها. هل أنت متأكد؟'
        );

        if (!confirmed) {
            return;
        }

        const response = await fetch(
            `/api/conversations/${id}`,
            {
                method: 'DELETE',

                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            }
        );

        const data = await response.json();

        if (!response.ok) {

            alert(
                data.message ||
                'تعذر حذف المحادثة'
            );

            return;
        }

        loadArchived();
    }


    loadArchived();

</script>

</body>
</html>
