<?php
/**
 * @file user_main.php
 * @brief ユーザーメインページ
 *
 * @details
 * CommentAnchor: USER_MAIN_PAGE
 * ユーザー情報、所持ポイント、利用履歴を表示します。
 * APIとの通信はJavaScriptのfetchで非同期に行います。
 */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CFMS ユーザーメインページ">
    <meta property="og:title" content="ユーザーメインページ">
    <meta property="og:description" content="所持ポイントと利用履歴を確認できます。">
    <meta property="og:type" content="website">
    <title>ユーザーメインページ | CFMS</title>
    <link rel="icon" href="/assets/favicon.ico">
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <style>
        :root {
            --cfms-primary: #2457a7;
            --cfms-background: #f4f7fb;
            --cfms-card-radius: 1rem;
        }

        body {
            background: var(--cfms-background);
            color: #202938;
            -webkit-text-size-adjust: 100%;
        }

        .navbar-cfms { background: var(--cfms-primary); }
        .page-container { max-width: 1080px; }

        .summary-card {
            border: 0;
            border-radius: var(--cfms-card-radius);
            box-shadow: 0 0.25rem 1rem rgb(20 40 80 / 7%);
            min-width: 0;
        }

        .point-value {
            color: var(--cfms-primary);
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -.04em;
            overflow-wrap: anywhere;
        }

        .token-value {
            overflow-wrap: anywhere;
            word-break: break-word;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        #qrCode {
            display: flex;
            min-height: 180px;
            align-items: center;
            justify-content: center;
        }

        #qrCode img,
        #qrCode canvas {
            display: block;
            max-width: 100%;
            height: auto;
        }

        .history-table th,
        .history-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .btn { touch-action: manipulation; }
        .loading-overlay { opacity: .65; pointer-events: none; }

        /* スマートフォン向け */
        @media (max-width: 767.98px) {
            .navbar .container {
                min-height: 56px;
                padding-left: 16px;
                padding-right: 16px;
            }

            .navbar-brand { font-size: 1.2rem; }
            .navbar-text { font-size: .8rem; }

            main.page-container {
                padding: 1rem 12px 1.5rem !important;
            }

            main > .d-flex:first-child {
                align-items: flex-start !important;
                margin-bottom: 1rem !important;
            }

            main > .d-flex:first-child h1 {
                font-size: 1.45rem;
            }

            #accountStatus {
                flex-shrink: 0;
                margin-top: 3px;
                font-size: .7rem;
            }

            .summary-card {
                border-radius: 16px;
                margin-bottom: 0;
            }

            .summary-card .card-body {
                padding: 1rem !important;
            }

            #userContent > .row {
                --bs-gutter-x: .75rem;
                --bs-gutter-y: .75rem;
                margin-bottom: .75rem !important;
            }

            #userContent .card h2 {
                font-size: 1rem;
            }

            /* ポイントを最初に、ひと目で見える大きさで表示 */
            #totalScore {
                font-size: 2.6rem;
            }

            .point-value .fs-5 {
                font-size: 1rem !important;
            }

            /* QRコードは中央に大きく配置し、提示しやすくする */
            #qrCode {
                min-height: 210px;
                padding: 1rem !important;
                border-radius: 14px !important;
            }

            #qrCode img,
            #qrCode canvas {
                width: min(210px, 100%) !important;
                height: auto !important;
            }

            #userToken {
                font-size: .75rem !important;
                line-height: 1.5;
            }

            #copyTokenButton,
            #generateButton,
            #refreshButton {
                min-height: 44px;
            }

            #copyTokenButton {
                width: 100%;
            }

            /* 詳細情報はラベルと値の間隔を詰めて読みやすく */
            #userContent dl.row {
                --bs-gutter-x: .5rem;
                --bs-gutter-y: .35rem;
                font-size: .875rem;
            }

            #userContent dl.row dt,
            #userContent dl.row dd {
                margin-bottom: .25rem;
                overflow-wrap: anywhere;
            }

            /* 履歴は横スクロール表ではなく、1件ずつのカード表示 */
            .history-table thead {
                display: none;
            }

            .history-table,
            .history-table tbody,
            .history-table tr,
            .history-table td {
                display: block;
                width: 100%;
            }

            .history-table tbody tr {
                padding: .75rem 0;
                border-bottom: 1px solid #e9edf4;
            }

            .history-table tbody tr:last-child {
                border-bottom: 0;
            }

            .history-table tbody td {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 12px;
                padding: .25rem 0;
                border: 0;
                white-space: normal;
                text-align: right !important;
                font-size: .8rem;
                overflow-wrap: anywhere;
            }

            .history-table tbody td::before {
                content: attr(data-label);
                flex: 0 0 auto;
                color: #6c757d;
                font-size: .75rem;
                text-align: left;
            }

            .history-table tbody td[colspan] {
                display: block;
                text-align: center !important;
                padding: 1rem .25rem;
            }

            .history-table tbody td[colspan]::before {
                content: none;
            }

            #finishedNotice {
                font-size: .875rem;
                border-radius: 12px;
            }

            #generateSection .card-body {
                padding: 1.25rem !important;
            }

            #generateButton {
                width: 100%;
                white-space: normal;
            }
        }

        @media (max-width: 380px) {
            .navbar-text { max-width: 145px; white-space: normal; text-align: right; line-height: 1.2; }
            main > .d-flex:first-child h1 { font-size: 1.3rem; }
            #totalScore { font-size: 2.25rem; }
            .history-table tbody td { gap: 8px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-cfms">
        <div class="container page-container">
            <a class="navbar-brand fw-bold" href="/index.php">CFMS</a>
            <span class="navbar-text text-white">ユーザーメインページ</span>
        </div>
    </nav>

    <main class="container page-container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">マイページ</h1>
                <p class="text-secondary mb-0">ポイントと利用履歴を確認できます。</p>
            </div>
            <span id="accountStatus" class="badge text-bg-secondary">確認中...</span>
        </div>

        <div id="alertArea" class="mb-3" aria-live="polite"></div>

        <section id="generateSection" class="card summary-card mb-4 d-none">
            <div class="card-body p-4 text-center">
                <h2 class="h5 fw-bold">ユーザー登録</h2>
                <p class="text-secondary">まだユーザー情報がありません。ユーザー情報を発行してください。</p>
                <button id="generateButton" class="btn btn-primary btn-lg" type="button">
                    ユーザー情報を発行する
                </button>
            </div>
        </section>

        <section id="userContent" class="d-none">
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-7">
                    <div class="card summary-card h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">所持ポイント</h2>
                            <div class="point-value"><span id="totalScore">0</span><span class="fs-5 ms-2">pt</span></div>
                            <p class="text-secondary small mt-2 mb-0">これまでに獲得したポイントの合計です。</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="card summary-card h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">ユーザー情報</h2>
                            <dl class="row mb-0">
                                <dt class="col-4">ユーザーID</dt>
                                <dd id="userId" class="col-8">-</dd>
                                <dt class="col-4">利用状態</dt>
                                <dd id="userState" class="col-8">-</dd>
                                <dt class="col-4">発行日時</dt>
                                <dd id="createdAt" class="col-8">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-5">
                    <div class="card summary-card h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">受付用QRコード</h2>
                            <p class="small text-secondary">スタッフに提示してください。QRコードにはユーザー識別用トークンが含まれます。</p>
                            <div id="qrCode" class="border rounded bg-white p-3 mb-3">
                                <span class="text-secondary small">QRコードを準備しています...</span>
                            </div>
                            <div class="small text-secondary mb-1">トークン</div>
                            <div id="userToken" class="token-value border rounded bg-light p-2 small">-</div>
                            <button id="copyTokenButton" class="btn btn-outline-primary btn-sm mt-2" type="button">
                                トークンをコピー
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="card summary-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h2 class="h5 fw-bold mb-0">ポイント獲得履歴</h2>
                                <button id="refreshButton" class="btn btn-outline-secondary btn-sm" type="button">
                                    更新
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover history-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">日時</th>
                                            <th scope="col">店舗</th>
                                            <th scope="col" class="text-end">獲得ポイント</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scoreHistory">
                                        <tr><td colspan="3" class="text-center text-secondary py-4">読み込み中...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="finishedNotice" class="alert alert-warning d-none" role="alert">
                このユーザーアカウントは終了しています。ポイントの追加などの操作はできません。
            </div>
        </section>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- CommentAnchor: QR_CODE_LIBRARY
         QRCode.jsを /lib/qrcode.min.js に配置してください。
         ライブラリがない場合もトークン文字列は表示されます。 -->
    <script src="/lib/qrcode.min.js"></script>
    <script>
        // CommentAnchor: API_SETTINGS
        // API_URLは環境変数で設定します。未設定の場合は同一オリジンを使用します。
        const API_BASE_URL = <?= json_encode(rtrim((string) (getenv('API_URL') ?: ''), '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const API_ENDPOINTS = {
            generate: '/api/user/generate',
            info: '/api/user/info',
            heartbeat: '/api/user/heartbeat',
            csrfToken: '/components/fetch_csrf_token.php'
        };

        let csrfToken = '';
        let currentUserToken = '';
        let heartbeatTimer = null;
        let isFinished = false;

        /**
         * CommentAnchor: API_REQUEST
         * APIレスポンスの共通形式 { success, message, data } を処理します。
         */
        async function requestApi(endpoint, options = {}) {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                credentials: 'include',
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...(options.headers || {})
                }
            });

            let responseData;
            try {
                responseData = await response.json();
            } catch (error) {
                throw new Error('サーバーから正しいJSONレスポンスを受信できませんでした。');
            }

            if (!response.ok || responseData.success === false) {
                throw new Error(responseData.message || `通信に失敗しました（HTTP ${response.status}）。`);
            }

            return responseData.data ?? responseData;
        }

        /**
         * CommentAnchor: CSRF_TOKEN
         * CSRFトークン取得エンドポイントのレスポンス形式に合わせて値を取得します。
         */
        async function loadCsrfToken() {
            const tokenData = await requestApi(API_ENDPOINTS.csrfToken, {
                method: 'GET',
                headers: {}
            });

            csrfToken = tokenData.csrf_token || tokenData.csrfToken || '';
            if (!csrfToken) {
                throw new Error('CSRFトークンを取得できませんでした。');
            }
        }

        function showAlert(message, type = 'danger') {
            const alertArea = document.getElementById('alertArea');
            alertArea.replaceChildren();

            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.setAttribute('role', 'alert');
            alert.textContent = message;

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close';
            closeButton.setAttribute('data-bs-dismiss', 'alert');
            closeButton.setAttribute('aria-label', '閉じる');
            alert.appendChild(closeButton);
            alertArea.appendChild(alert);
        }

        function setBusy(button, busy, busyText = '処理中...') {
            if (!button.dataset.originalText) {
                button.dataset.originalText = button.textContent.trim();
            }
            button.disabled = busy;
            button.textContent = busy ? busyText : button.dataset.originalText;
        }

        function formatDate(dateValue) {
            if (!dateValue) {
                return '-';
            }

            const date = new Date(dateValue);
            if (Number.isNaN(date.getTime())) {
                return String(dateValue);
            }

            return new Intl.DateTimeFormat('ja-JP', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        }

        function renderHistory(scores) {
            const scoreHistory = document.getElementById('scoreHistory');
            scoreHistory.replaceChildren();

            if (!Array.isArray(scores) || scores.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 3;
                cell.className = 'text-center text-secondary py-4';
                cell.textContent = 'ポイント獲得履歴はありません。';
                row.appendChild(cell);
                scoreHistory.appendChild(row);
                return;
            }

            // 新しい履歴を先に表示します。
            [...scores].sort((first, second) => {
                return new Date(second.createdAt) - new Date(first.createdAt);
            }).forEach((score) => {
                const row = document.createElement('tr');

                const dateCell = document.createElement('td');
                dateCell.textContent = formatDate(score.createdAt);
                dateCell.setAttribute('data-label', '日時');

                const storeCell = document.createElement('td');
                storeCell.textContent = score.storeName || (score.storeId ? `店舗ID: ${score.storeId}` : '-');
                storeCell.setAttribute('data-label', '店舗');

                const pointCell = document.createElement('td');
                pointCell.className = 'text-end fw-semibold';
                pointCell.textContent = `+${Number(score.score || 0).toLocaleString('ja-JP')} pt`;
                pointCell.setAttribute('data-label', '獲得ポイント');

                row.append(dateCell, storeCell, pointCell);
                scoreHistory.appendChild(row);
            });
        }

        function renderQrCode(token) {
            const qrCode = document.getElementById('qrCode');
            qrCode.replaceChildren();

            if (typeof QRCode === 'undefined') {
                const fallback = document.createElement('p');
                fallback.className = 'text-secondary small text-center mb-0';
                fallback.textContent = 'QRコードライブラリが見つかりません。トークン文字列をスタッフに提示してください。';
                qrCode.appendChild(fallback);
                return;
            }

            new QRCode(qrCode, {
                text: token,
                width: 180,
                height: 180,
                correctLevel: QRCode.CorrectLevel.M
            });
        }

        function renderUserInfo(userInfo) {
            document.getElementById('userContent').classList.remove('d-none');
            document.getElementById('generateSection').classList.add('d-none');

            document.getElementById('userId').textContent = userInfo.userId ?? '-';
            document.getElementById('createdAt').textContent = formatDate(userInfo.createdAt);
            document.getElementById('totalScore').textContent = Number(userInfo.totalScore || 0).toLocaleString('ja-JP');

            currentUserToken = userInfo.token || '';
            document.getElementById('userToken').textContent = currentUserToken || 'トークンがありません';
            if (currentUserToken) {
                renderQrCode(currentUserToken);
            } else {
                document.getElementById('qrCode').textContent = 'トークンがありません。スタッフにお問い合わせください。';
            }

            isFinished = Boolean(userInfo.finishedAt);
            const isActive = Boolean(userInfo.isActive ?? userInfo.is_active);
            const status = document.getElementById('accountStatus');
            const userState = document.getElementById('userState');

            if (isFinished) {
                status.className = 'badge text-bg-dark';
                status.textContent = 'アカウント終了';
                userState.textContent = '終了';
                document.getElementById('finishedNotice').classList.remove('d-none');
            } else if (isActive) {
                status.className = 'badge text-bg-success';
                status.textContent = '利用可能';
                userState.textContent = '有効';
                document.getElementById('finishedNotice').classList.add('d-none');
            } else {
                status.className = 'badge text-bg-warning';
                status.textContent = '受付前';
                userState.textContent = '未有効化';
                document.getElementById('finishedNotice').classList.add('d-none');
            }

            document.getElementById('copyTokenButton').disabled = !currentUserToken;
            renderHistory(userInfo.scores || []);
        }

        /**
         * CommentAnchor: LOAD_USER_INFO
         * 現在のユーザー情報と獲得履歴を取得します。
         */
        async function loadUserInfo() {
            const userInfo = await requestApi(API_ENDPOINTS.info, { method: 'GET' });

            // API側でユーザー未発行を判定した場合は発行ボタンを表示します。
            if (userInfo.exists === false || userInfo.userId == null) {
                document.getElementById('generateSection').classList.remove('d-none');
                document.getElementById('userContent').classList.add('d-none');
                document.getElementById('accountStatus').className = 'badge text-bg-secondary';
                document.getElementById('accountStatus').textContent = '未発行';
                return;
            }

            renderUserInfo(userInfo);
        }

        /**
         * CommentAnchor: GENERATE_USER
         * ユーザー情報を発行します。重複発行の防止はサーバー側でも必須です。
         */
        async function generateUser() {
            const button = document.getElementById('generateButton');
            setBusy(button, true);

            try {
                const result = await requestApi(API_ENDPOINTS.generate, {
                    method: 'POST',
                    body: JSON.stringify({ csrfToken })
                });

                showAlert(result.message || 'ユーザー情報を発行しました。', 'success');
                await loadUserInfo();
            } catch (error) {
                showAlert(error.message || 'ユーザー情報の発行に失敗しました。');
            } finally {
                setBusy(button, false);
            }
        }

        /**
         * CommentAnchor: HEARTBEAT
         * 1分ごとに応答を送信し、サーバー側の最終応答時刻を更新します。
         */
        async function sendHeartbeat() {
            if (document.hidden || isFinished) {
                return;
            }

            try {
                await requestApi(API_ENDPOINTS.heartbeat, {
                    method: 'POST',
                    body: JSON.stringify({ csrfToken })
                });
            } catch (error) {
                // 一時的な通信失敗でページ操作を止めず、次回の送信を試みます。
                console.warn('Heartbeat failed:', error.message);
            }
        }

        async function initializePage() {
            try {
                await loadCsrfToken();
                await loadUserInfo();
                await sendHeartbeat();
                heartbeatTimer = window.setInterval(sendHeartbeat, 60 * 1000);
            } catch (error) {
                showAlert(error.message || 'ページの初期化に失敗しました。');
                document.getElementById('accountStatus').textContent = '取得失敗';
            }
        }

        document.getElementById('generateButton').addEventListener('click', generateUser);
        document.getElementById('refreshButton').addEventListener('click', async () => {
            const button = document.getElementById('refreshButton');
            setBusy(button, true, '更新中...');
            try {
                await loadUserInfo();
                showAlert('情報を更新しました。', 'success');
            } catch (error) {
                showAlert(error.message || '情報の更新に失敗しました。');
            } finally {
                setBusy(button, false);
            }
        });

        document.getElementById('copyTokenButton').addEventListener('click', async () => {
            if (!currentUserToken) {
                return;
            }

            try {
                await navigator.clipboard.writeText(currentUserToken);
                showAlert('トークンをコピーしました。', 'success');
            } catch (error) {
                showAlert('コピーできませんでした。トークンを選択してコピーしてください。', 'warning');
            }
        });

        window.addEventListener('beforeunload', () => {
            if (heartbeatTimer !== null) {
                window.clearInterval(heartbeatTimer);
            }
        });

        initializePage();
    </script>
</body>
</html>
