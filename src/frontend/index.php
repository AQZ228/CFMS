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
        body { -webkit-text-size-adjust: 100%; }
        #qrCode img, #qrCode canvas { display: block; max-width: 100%; height: auto; }
        #userToken { overflow-wrap: anywhere; word-break: break-word; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
        .history-table th, .history-table td { vertical-align: middle; }
        .btn { touch-action: manipulation; }
    </style>
</head>
<body>


    <main class="container py-3 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">マイページ</h1>
                <p class="text-secondary mb-0">ポイントと利用履歴を確認できます。</p>
            </div>
            <span id="accountStatus" class="badge text-bg-secondary">確認中...</span>
        </div>

        <div id="alertArea" class="mb-3" aria-live="polite"></div>

        <section id="generateSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
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
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">所持ポイント</h2>
                            <div class="fs-1 fw-bold text-primary lh-sm"><span id="totalScore">0</span><span class="fs-5 ms-2">pt</span></div>
                            <p class="text-secondary small mt-2 mb-0">これまでに獲得したポイントの合計です。</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
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
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">受付用QRコード</h2>
                            <p class="small text-secondary">スタッフに提示してください。QRコードにはユーザー識別用トークンが含まれます。</p>
                            <div id="qrCode" class="d-flex align-items-center justify-content-center border rounded-3 bg-white p-3 mb-3">
                                <span class="text-secondary small">QRコードを準備しています...</span>
                            </div>
                            <div class="small text-secondary mb-1">トークン</div>
                            <div id="userToken" class="token-value border rounded bg-light p-2 small">-</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h2 class="h5 fw-bold mb-0">ポイント獲得履歴</h2>
                                <button id="refreshButton" class="btn btn-outline-secondary btn-sm" type="button">
                                    更新
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
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

    <!-- template.phpと同じCSRF自動埋め込み方式 -->
    <input type="hidden" class="csrf_token" name="csrf_token" value="">
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

    <script>
        (() => {
        // CommentAnchor: API_SETTINGS
        // API_URLは環境変数で設定します。未設定の場合は同一オリジンを使用します。
        const API_BASE_URL = <?= json_encode(rtrim((string) (getenv('API_URL') ?: ''), '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const API_ENDPOINTS = {
            generate: '/api/user/generate',
            info: '/api/user/info',
            heartbeat: '/api/user/heartbeat'
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

            // 先にテキストとして取得し、JSON以外のエラーページでもHTTPステータスを保持します。
            const responseText = await response.text();
            let responseData = null;
            if (responseText) {
                try {
                    responseData = JSON.parse(responseText);
                } catch (error) {
                    if (!response.ok) {
                        throw new Error(`通信に失敗しました（HTTP ${response.status}）。`);
                    }
                    throw new Error(`サーバーから正しいJSONレスポンスを受信できませんでした（HTTP ${response.status}）。`);
                }
            }

            if (!response.ok || responseData?.success === false) {
                const message = responseData?.message;
                throw new Error(message || `通信に失敗しました（HTTP ${response.status}）。`);
            }

            if (responseData === null) {
                throw new Error(`サーバーから空のレスポンスを受信しました（HTTP ${response.status}）。`);
            }

            return responseData.data ?? responseData;
        }

        /**
         * CommentAnchor: CSRF_TOKEN
         * template.phpと同じ方式で、fetch_csrf_token.phpが埋め込むhiddenフィールドから取得します。
         * CSRF取得用コンポーネントはこのスクリプトより前にincludeし、初期化時に値を読み取ります。
         */
                async function loadCsrfToken() {
            if (document.readyState === 'loading') {
                await new Promise((resolve) => {
                    document.addEventListener('DOMContentLoaded', resolve, { once: true });
                });
            }

            const csrfInput = document.querySelector('.csrf_token');
            if (!csrfInput) {
                throw new Error('CSRFトークン用の要素が見つかりません。');
            }

            // 非同期でのトークン注入を最大5秒間待機します
            let attempts = 0;
            while (!csrfInput.value.trim() && attempts < 50) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                attempts++;
            }

            csrfToken = csrfInput.value.trim();
            if (!csrfToken) {
                throw new Error('CSRFトークンを取得できませんでした。fetch_csrf_token.phpの埋め込み処理を確認してください。');
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
                const t1 = first.createdAt ? new Date(first.createdAt).getTime() : 0;
                const t2 = second.createdAt ? new Date(second.createdAt).getTime() : 0;
                const time1 = Number.isNaN(t1) ? 0 : t1;
                const time2 = Number.isNaN(t2) ? 0 : t2;
                return time2 - time1;
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
            });        }

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
                await loadUserInfo();
                try {
                    await loadCsrfToken();
                    if (!isFinished) {
                        await sendHeartbeat();
                        heartbeatTimer = window.setInterval(sendHeartbeat, 60 * 1000);
                    }
                } catch (error) {
                    console.warn('CSRFトークンの取得に失敗したため、一部の機能が制限されます:', error.message);
                }
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

        window.addEventListener('beforeunload', () => {
            if (heartbeatTimer !== null) {
                window.clearInterval(heartbeatTimer);
            }
        });

        initializePage();
        })();
    </script>
</body>
</html>
