
<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <meta name="description" content="スタッフ用の得点登録ページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- CSRFトークン -->
    <meta name="csrf-token" content="">

    <!-- ファビコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <!-- OGP設定 -->
    <meta property="og:title" content="得点登録ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="QRコードからユーザーを確認して得点を登録します">

    <title>得点登録ページ</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <!-- QRコード読み取りライブラリ -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- Bootstrap JavaScript -->
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

<main class="container py-4" style="max-width: 650px;">

    <div class="card shadow-sm">

        <!-- ヘッダー -->
        <div class="card-header bg-primary text-white p-3">
            <h1 class="h4 mb-1">得点登録</h1>
            <p class="mb-0 small">スタッフ用｜ゲームポイント管理</p>
        </div>

        <div class="card-body">

            <!-- メッセージ表示 -->
            <div
                id="messageArea"
                class="alert d-none"
                role="alert"
                aria-live="polite">
            </div>

            <!-- 店舗選択 -->
            <section class="mb-4">
                <label for="storeId" class="form-label fw-bold">
                    担当店舗 *
                </label>

                <select id="storeId" class="form-select" required>
                    <option value="">店舗を選択してください</option>
                    <option value="1">店舗A（モック）</option>
                    <option value="2">店舗B（モック）</option>
                </select>

                <div class="form-text">
                    現在はモックの店舗一覧です。
                    実際の運用では、ログイン中スタッフの権限に基づいて
                    サーバーから取得してください。
                </div>
            </section>

            <hr>

            <!-- QRコード読み取り -->
            <section class="mb-4">

                <h2 class="h5 mb-3">1. QRコード読み取り</h2>

                <div class="border rounded p-3 bg-white">

                    <!-- カメラ映像 -->
                    <div id="qrReader" class="mb-3 d-none"></div>

                    <!-- カメラ状態 -->
                    <div
                        id="cameraStatus"
                        class="alert alert-secondary py-2"
                        role="status"
                        aria-live="polite">
                        カメラを起動してQRコードを読み取ってください。
                    </div>

                    <!-- カメラ起動・停止 -->
                    <div class="d-grid gap-2 mb-3">

                        <button
                            type="button"
                            id="startCameraButton"
                            class="btn btn-primary">
                            カメラを起動する
                        </button>

                        <button
                            type="button"
                            id="stopCameraButton"
                            class="btn btn-outline-secondary d-none">
                            カメラを停止する
                        </button>

                    </div>

                    <div class="text-center text-secondary small mb-3">
                        または token を手入力
                    </div>

                    <!-- ユーザー情報取得フォーム -->
                    <form id="getUserForm">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="">

                        <div class="mb-3">
                            <label for="token" class="form-label">
                                QRコードのtoken *
                            </label>

                            <input
                                type="text"
                                id="token"
                                name="token"
                                class="form-control"
                                placeholder="tokenを入力してください"
                                autocomplete="off"
                                required>
                        </div>

                        <button
                            type="submit"
                            id="getUserButton"
                            class="btn btn-primary w-100">
                            ユーザー情報を取得
                        </button>

                    </form>

                </div>
            </section>

            <!-- ユーザー情報 -->
            <section id="userSection" class="mb-4 d-none">

                <h2 class="h5 mb-3">2. ユーザー情報</h2>

                <div class="card bg-light">
                    <div class="card-body">

                        <dl class="row mb-0">

                            <dt class="col-6">ユーザーID</dt>
                            <dd class="col-6" id="userId">-</dd>

                            <dt class="col-6">アカウント状態</dt>
                            <dd class="col-6" id="accountStatus">-</dd>

                            <dt class="col-6">獲得ポイント合計</dt>
                            <dd class="col-6">
                                <strong id="totalScore">0</strong> pt
                            </dd>

                            <dt class="col-6">店舗利用回数</dt>
                            <dd class="col-6">
                                <span id="usageCount">0</span>
                                /
                                <span id="usageLimit">3</span> 回
                            </dd>

                        </dl>

                    </div>
                </div>

                <!-- 得点履歴 -->
                <div class="mt-3">
                    <h3 class="h6">得点履歴</h3>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>登録日時</th>
                                    <th>店舗</th>
                                    <th class="text-end">ポイント</th>
                                </tr>
                            </thead>

                            <tbody id="scoreHistory">
                                <tr>
                                    <td
                                        colspan="3"
                                        class="text-center text-secondary">
                                        履歴はありません
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>

            <!-- 得点登録 -->
            <section id="scoreSection" class="d-none">

                <h2 class="h5 mb-3">3. 1プレー分の得点登録</h2>

                <div class="border rounded p-3 bg-white">

                    <form id="scoreForm">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="">

                        <div class="mb-3">

                            <label for="score" class="form-label">
                                獲得ポイント *
                            </label>

                            <input
                                type="number"
                                id="score"
                                name="score"
                                class="form-control"
                                min="0"
                                step="1"
                                placeholder="ポイントを入力してください"
                                required>

                            <div class="form-text">
                                1プレーにつき1回登録します。
                                ポイント数自体に上限はありません。
                            </div>

                        </div>

                        <button
                            type="submit"
                            id="registerButton"
                            class="btn btn-success w-100"
                            disabled>
                            得点を登録
                        </button>

                    </form>

                </div>

            </section>

        </div>

        <div class="card-footer text-secondary small">
            得点登録ページ
        </div>

    </div>

</main>

<!-- 共通フッター -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

<!-- CSRFトークンの自動埋め込み -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

<script>
(() => {
    'use strict';

    // ----------------------------------------
    // DOM要素
    // ----------------------------------------

    const getUserForm = document.getElementById('getUserForm');
    const scoreForm = document.getElementById('scoreForm');

    const storeSelect = document.getElementById('storeId');
    const tokenInput = document.getElementById('token');
    const scoreInput = document.getElementById('score');

    const messageArea = document.getElementById('messageArea');

    const userSection = document.getElementById('userSection');
    const scoreSection = document.getElementById('scoreSection');

    const qrReaderElement = document.getElementById('qrReader');
    const cameraStatus = document.getElementById('cameraStatus');

    const startCameraButton =
        document.getElementById('startCameraButton');

    const stopCameraButton =
        document.getElementById('stopCameraButton');

    const getUserButton =
        document.getElementById('getUserButton');

    const registerButton =
        document.getElementById('registerButton');

    const scoreHistory = document.getElementById('scoreHistory');

    // ----------------------------------------
    // 状態管理
    // ----------------------------------------

    let html5QrCode = null;
    let cameraRunning = false;
    let processingQr = false;
    let loadingUser = false;
    let registeringScore = false;

    let currentUser = null;
    let currentToken = '';
    let canAddScore = false;

    // ----------------------------------------
    // メッセージ表示
    // ----------------------------------------

    function showMessage(message, type = 'info') {
        messageArea.textContent = message;
        messageArea.className = `alert alert-${type}`;
    }

    function setCameraStatus(message, type = 'secondary') {
        cameraStatus.textContent = message;
        cameraStatus.className = `alert alert-${type} py-2`;
    }

    // ----------------------------------------
    // CSRFトークン取得
    // ----------------------------------------

    function getCsrfToken() {
        const meta = document.querySelector(
            'meta[name="csrf-token"]'
        );

        if (meta && meta.content.trim() !== '') {
            return meta.content.trim();
        }

        const input = document.querySelector(
            'input[name="csrf_token"]'
        );

        if (input && input.value.trim() !== '') {
            return input.value.trim();
        }

        return '';
    }

    // ----------------------------------------
    // APIリクエスト用CSRFチェック
    // ----------------------------------------

    function requireCsrfToken() {
        const csrfToken = getCsrfToken();

        if (!csrfToken) {
            throw new Error(
                'CSRFトークンを取得できません。ページを再読み込みしてください。'
            );
        }

        return csrfToken;
    }

    // ----------------------------------------
    // ユーザー情報をリセット
    // ----------------------------------------

    function hideUser() {
        currentUser = null;
        currentToken = '';
        canAddScore = false;

        userSection.classList.add('d-none');
        scoreSection.classList.add('d-none');

        scoreHistory.replaceChildren();

        registerButton.disabled = true;
    }

    // ----------------------------------------
    // APIレスポンス確認
    // ----------------------------------------

    async function readResponse(response) {
        let data;

        try {
            data = await response.json();
        } catch {
            throw new Error(
                'サーバーから正しいJSON応答がありません。'
            );
        }

        if (!response.ok || data.success !== true) {
            throw new Error(
                data.message ||
                `通信エラーが発生しました（${response.status}）。`
            );
        }

        return data;
    }

    // ----------------------------------------
    // 得点履歴表示
    // ----------------------------------------

    function displayHistory(scores) {
        scoreHistory.replaceChildren();

        if (!Array.isArray(scores) || scores.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');

            td.colSpan = 3;
            td.className = 'text-center text-secondary';
            td.textContent = '履歴はありません';

            tr.appendChild(td);
            scoreHistory.appendChild(tr);

            return;
        }

        scores.forEach((item) => {
            const tr = document.createElement('tr');

            const dateCell = document.createElement('td');
            dateCell.textContent =
                item.createdAt ?? item.registeredAt ?? '-';

            const storeCell = document.createElement('td');
            storeCell.textContent = item.storeName ?? '-';

            const scoreCell = document.createElement('td');
            scoreCell.className = 'text-end';

            const score = Number(item.score);

            scoreCell.textContent = Number.isFinite(score)
                ? score.toLocaleString('ja-JP')
                : '-';

            tr.append(dateCell, storeCell, scoreCell);
            scoreHistory.appendChild(tr);
        });
    }

    // ----------------------------------------
    // ユーザー情報表示
    // ----------------------------------------

    function displayUser(data) {
        const user = data.user;

        if (!user) {
            throw new Error(
                'ユーザー情報がレスポンスにありません。'
            );
        }

        currentUser = user;

        document.getElementById('userId').textContent =
            user.userId ?? user.id ?? '-';

        const isActive =
            user.isActive === true ||
            user.isActivated === true ||
            user.activated === true;

        const isEnded =
            user.isEnded === true ||
            user.ended === true;

        const statusElement =
            document.getElementById('accountStatus');

        if (isEnded) {
            statusElement.textContent = '終了済み';
            statusElement.className =
                'col-6 text-danger fw-bold';
        } else if (isActive) {
            statusElement.textContent = '有効';
            statusElement.className =
                'col-6 text-success fw-bold';
        } else {
            statusElement.textContent = '未アクティベート';
            statusElement.className =
                'col-6 text-danger fw-bold';
        }

        document.getElementById('totalScore').textContent =
            Number(data.totalScore ?? 0).toLocaleString('ja-JP');

        document.getElementById('usageCount').textContent =
            data.storeUsageCount ?? 0;

        document.getElementById('usageLimit').textContent =
            data.storeUsageLimit ?? 3;

        displayHistory(data.scores ?? []);

        userSection.classList.remove('d-none');
        scoreSection.classList.remove('d-none');

        canAddScore = data.canAddScore === true;

        registerButton.disabled =
            !canAddScore || registeringScore;

        if (!canAddScore) {
            showMessage(
                'このユーザーは得点登録できません。アカウント状態と店舗利用回数を確認してください。',
                'warning'
            );
        }
    }

    // ----------------------------------------
    // QRコードカメラ停止
    // ----------------------------------------

    async function stopQrCamera() {
        if (!html5QrCode || !cameraRunning) {
            cameraRunning = false;

            qrReaderElement.classList.add('d-none');
            startCameraButton.disabled = false;
            stopCameraButton.classList.add('d-none');

            return;
        }

        try {
            await html5QrCode.stop();
        } catch (error) {
            console.error('カメラ停止エラー:', error);
        } finally {
            cameraRunning = false;

            qrReaderElement.classList.add('d-none');
            startCameraButton.disabled = false;
            stopCameraButton.classList.add('d-none');
        }
    }

    // ----------------------------------------
    // ユーザー情報取得
    // POST /api/score/get
    // ----------------------------------------

    async function getUserInfo(token) {
        if (loadingUser) {
            return;
        }

        const storeId = storeSelect.value;
        const trimmedToken = token.trim();

        if (!storeId) {
            showMessage(
                '担当店舗を選択してください。',
                'warning'
            );
            return;
        }

        if (!trimmedToken) {
            showMessage(
                'QRコードのtokenを入力してください。',
                'warning'
            );
            return;
        }

        loadingUser = true;
        getUserButton.disabled = true;

        hideUser();

        try {
            const csrfToken = requireCsrfToken();

            const response = await fetch('/api/score/get', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    token: trimmedToken,
                    storeId: storeId,
                    csrfToken: csrfToken
                })
            });

            const data = await readResponse(response);

            currentToken = trimmedToken;

            displayUser(data);

            showMessage(
                'ユーザー情報を取得しました。',
                'success'
            );

        } catch (error) {
            hideUser();

            showMessage(
                error.message || 'ユーザー情報の取得に失敗しました。',
                'danger'
            );

        } finally {
            loadingUser = false;
            getUserButton.disabled = false;
        }
    }

    // ----------------------------------------
    // 手入力フォーム送信
    // ----------------------------------------

    getUserForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        await getUserInfo(tokenInput.value);
    });

    // ----------------------------------------
    // QRコード読み取りライブラリ起動
    // ----------------------------------------

    startCameraButton.addEventListener('click', async () => {
        if (cameraRunning) {
            return;
        }

        if (!storeSelect.value) {
            showMessage(
                '先に担当店舗を選択してください。',
                'warning'
            );
            return;
        }

        if (typeof Html5Qrcode === 'undefined') {
            setCameraStatus(
                'QRコード読み取りライブラリを読み込めません。ネットワーク接続を確認してください。',
                'danger'
            );
            return;
        }

        startCameraButton.disabled = true;

        setCameraStatus(
            'カメラを起動しています...',
            'info'
        );

        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('qrReader');
            }

            qrReaderElement.classList.remove('d-none');

            await html5QrCode.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                async (decodedText) => {
                    if (processingQr || loadingUser) {
                        return;
                    }

                    processingQr = true;

                    try {
                        const scannedToken = decodedText.trim();

                        if (!scannedToken) {
                            setCameraStatus(
                                'QRコードからtokenを取得できませんでした。',
                                'warning'
                            );
                            return;
                        }

                        tokenInput.value = scannedToken;

                        setCameraStatus(
                            '読み取り成功。ユーザー情報を取得しています。',
                            'success'
                        );

                        await stopQrCamera();

                        await getUserInfo(scannedToken);

                    } finally {
                        processingQr = false;
                    }
                },
                () => {
                    // QRコード未検出時は読み取りを続ける
                }
            );

            cameraRunning = true;

            startCameraButton.disabled = true;
            stopCameraButton.classList.remove('d-none');

            setCameraStatus(
                'カメラ起動中です。QRコードを枠内に合わせてください。',
                'success'
            );

        } catch (error) {
            console.error('カメラ起動エラー:', error);

            cameraRunning = false;

            qrReaderElement.classList.add('d-none');
            startCameraButton.disabled = false;
            stopCameraButton.classList.add('d-none');

            setCameraStatus(
                'カメラを起動できません。HTTPS接続、カメラの使用許可、他アプリのカメラ使用状況を確認してください。',
                'danger'
            );
        }
    });

    // ----------------------------------------
    // カメラ停止ボタン
    // ----------------------------------------

    stopCameraButton.addEventListener('click', async () => {
        await stopQrCamera();

        setCameraStatus(
            'カメラを停止しました。',
            'secondary'
        );
    });

    // ----------------------------------------
    // 得点登録
    // POST /api/score/add
    // ----------------------------------------

    scoreForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (registeringScore) {
            return;
        }

        if (!currentUser || !currentToken) {
            showMessage(
                '先にQRコードを読み取り、ユーザー情報を取得してください。',
                'warning'
            );
            return;
        }

        if (!canAddScore) {
            showMessage(
                'このユーザーは得点登録できません。',
                'warning'
            );
            return;
        }

        const rawScore = scoreInput.value;
        const score = Number(rawScore);

        if (
            rawScore.trim() === '' ||
            !Number.isSafeInteger(score) ||
            score < 0
        ) {
            showMessage(
                'ポイントは0以上の整数で入力してください。',
                'warning'
            );
            return;
        }

        if (!storeSelect.value) {
            showMessage(
                '担当店舗を選択してください。',
                'warning'
            );
            return;
        }

        registeringScore = true;
        registerButton.disabled = true;

        try {
            const csrfToken = requireCsrfToken();

            const response = await fetch('/api/score/add', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    token: currentToken,
                    storeId: storeSelect.value,
                    score: score,
                    csrfToken: csrfToken
                })
            });

            const data = await readResponse(response);

            showMessage(
                data.message || '得点を登録しました。',
                'success'
            );

            // 登録後、最新の情報を再取得する
            await getUserInfo(currentToken);

        } catch (error) {
            showMessage(
                error.message || '得点の登録に失敗しました。',
                'danger'
            );

        } finally {
            registeringScore = false;

            // 最新のAPI判定に基づいて登録ボタンを制御
            registerButton.disabled =
                !currentUser || !canAddScore;
        }
    });

    // ----------------------------------------
    // 店舗変更時
    // ----------------------------------------

    storeSelect.addEventListener('change', async () => {
        await stopQrCamera();

        hideUser();

        tokenInput.value = '';
        scoreInput.value = '';

        showMessage(
            '店舗を変更しました。QRコードを再読み取りしてください。',
            'info'
        );
    });

    // ----------------------------------------
    // ページ離脱時にカメラを停止
    // ----------------------------------------

    window.addEventListener('pagehide', () => {
        if (html5QrCode && cameraRunning) {
            html5QrCode.stop().catch((error) => {
                console.error('カメラ停止エラー:', error);
            });
        }
    });

})();
</script>

</body>
</html>
