
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">
    <meta property="og:title" content="受付アカウント有効化">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:description" content="受付スタッフがユーザーを有効化するページ">

    <title>受付アカウント有効化 | CFMS</title>
    <meta name="description" content="QRコードを読み取り、ユーザーのアカウントを有効化します。">

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <!-- QRコード読み取りライブラリ -->
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body class="bg-light">

<main class="container py-4">

    <h1 class="h3 text-center mb-3">受付アカウント有効化</h1>

    <p class="text-center text-secondary mb-4">
        支払いを確認してからアカウントを有効化してください。
    </p>

    <!-- 処理結果 -->
    <div id="result" aria-live="polite"></div>

    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-body p-4">

            <form id="activateForm">

                <!-- template.phpと同じCSRFトークン取得方法 -->
                <input type="hidden"
                       class="csrf_token"
                       name="csrf_token"
                       value="">

                <h2 class="h5 mb-3">QRコード読み取り</h2>

                <p class="text-secondary small">
                    ユーザーのQRコードをカメラにかざしてください。
                    読み取ったトークンは入力欄に反映されます。
                </p>

                <!-- QRコード読み取り領域 -->
                <div id="qr-reader"
                     class="mb-3"
                     aria-label="QRコード読み取り画面"></div>

                <div class="d-grid gap-2 mb-4">
                    <button type="button"
                            id="startCameraButton"
                            class="btn btn-primary">
                        カメラを起動する
                    </button>

                    <button type="button"
                            id="stopCameraButton"
                            class="btn btn-outline-secondary"
                            disabled>
                        カメラを停止する
                    </button>
                </div>

                <!-- トークン -->
                <div class="mb-4">
                    <label for="token" class="form-label">
                        トークン <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           class="form-control"
                           id="token"
                           name="token"
                           placeholder="QRコードを読み取るか手動入力"
                           autocomplete="off"
                           required>

                    <div class="form-text">
                        QRコードが読み取れない場合は手動入力できます。
                    </div>
                </div>

                <!-- ユーザー情報 -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h2 class="h6 mb-3">対象ユーザー情報</h2>

                        <div class="mb-3">
                            <label for="userId" class="form-label">
                                ユーザーID
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="userId"
                                   value="未確認"
                                   readonly>
                        </div>

                        <div>
                            <label for="accountStatus" class="form-label">
                                アカウント状態
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="accountStatus"
                                   value="未確認"
                                   readonly>
                        </div>
                    </div>
                </div>

                <!-- ユーザー照会 -->
                <div class="d-grid gap-2 mb-3">
                    <button type="button"
                            id="verifyUserButton"
                            class="btn btn-outline-primary">
                        ユーザーを確認する
                    </button>
                </div>

                <!-- 有効化 -->
                <div class="d-grid gap-2">
                    <button type="submit"
                            id="activateButton"
                            class="btn btn-success btn-lg"
                            disabled>
                        アクティベートする
                    </button>

                    <button type="reset"
                            id="resetButton"
                            class="btn btn-outline-secondary">
                        入力をリセット
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- 注意事項 -->
    <div class="alert alert-info mx-auto mt-4"
         style="max-width: 600px;">

        <h2 class="h6">受付時の注意事項</h2>

        <ul class="mb-0">
            <li>料金の支払いを確認してから操作してください。</li>
            <li>QRコードから読み取ったユーザーを確認してください。</li>
            <li>既に有効化済みのユーザーは再度有効化できません。</li>
            <li>アカウント終了済みのユーザーは操作できません。</li>
        </ul>
    </div>

</main>

<!-- フッター -->
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php';
?>

<!-- CSRFトークンの自動埋め込み -->
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php';
?>

<script>
    // 画面要素
    const activateForm = document.getElementById('activateForm');
    const tokenInput = document.getElementById('token');
    const userIdInput = document.getElementById('userId');
    const accountStatusInput = document.getElementById('accountStatus');
    const resultDiv = document.getElementById('result');

    const startCameraButton = document.getElementById('startCameraButton');
    const stopCameraButton = document.getElementById('stopCameraButton');
    const verifyUserButton = document.getElementById('verifyUserButton');
    const activateButton = document.getElementById('activateButton');

    // template.phpと同じAPI_URL環境変数を使用
    const API_BASE_URL = <?= json_encode(
        rtrim(getenv('API_URL') ?: '', '/'),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;

    // QRコード読み取り用
    let qrScanner = null;
    let isCameraRunning = false;
    let isUserVerified = false;
    let isProcessing = false;

    /**
     * 結果を表示する
     * innerHTMLを使わず、安全にメッセージを表示する
     */
    function renderResult(success, message) {
        resultDiv.replaceChildren();

        const alertDiv = document.createElement('div');

        alertDiv.className = success
            ? 'alert alert-success'
            : 'alert alert-danger';

        alertDiv.setAttribute('role', 'alert');
        alertDiv.textContent = message;

        resultDiv.appendChild(alertDiv);
    }

    /**
     * CSRFトークンを取得する
     * template.phpと同じhiddenフィールドを利用する
     */
    function getCsrfToken() {
        const csrfInput = activateForm.querySelector(
            '.csrf_token'
        );

        return csrfInput ? csrfInput.value.trim() : '';
    }

    /**
     * JSON形式でPOSTする共通処理
     */
    async function postJson(endpoint, payload) {
        if (!API_BASE_URL) {
            throw new Error(
                'API_URLが設定されていません。環境変数を確認してください。'
            );
        }

        const response = await fetch(
            API_BASE_URL + endpoint,
            {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            }
        );

        // JSON以外のエラーレスポンスにも対応
        let data;

        try {
            data = await response.json();
        } catch (error) {
            throw new Error(
                'サーバーから正しいJSONレスポンスが返されませんでした。'
            );
        }

        if (!response.ok || !data.success) {
            throw new Error(
                data.message || 'サーバーでエラーが発生しました。'
            );
        }

        return data;
    }

    /**
     * ユーザー確認状態をリセットする
     */
    function resetUserInfo() {
        isUserVerified = false;

        userIdInput.value = '未確認';
        accountStatusInput.value = '未確認';

        activateButton.disabled = true;
    }

    /**
     * CSRFトークンの取得完了を確認する
     */
    function validateCsrfToken() {
        const csrfToken = getCsrfToken();

        if (!csrfToken) {
            renderResult(
                false,
                'CSRFトークンを取得できません。ページを再読み込みしてください。'
            );

            return null;
        }

        return csrfToken;
    }

    /**
     * QRコード読み取り成功時
     */
    async function onQrCodeScanned(decodedText) {
        if (isProcessing) {
            return;
        }

        const scannedToken = decodedText.trim();

        if (!scannedToken) {
            renderResult(false, 'QRコードにトークンがありません。');
            return;
        }

        isProcessing = true;

        try {
            await stopCamera();

            tokenInput.value = scannedToken;

            resetUserInfo();

            renderResult(
                true,
                'QRコードを読み取りました。ユーザーを確認してください。'
            );
        } finally {
            isProcessing = false;
        }
    }

    /**
     * カメラを起動する
     */
    async function startCamera() {
        if (isCameraRunning || isProcessing) {
            return;
        }

        if (typeof Html5Qrcode === 'undefined') {
            renderResult(
                false,
                'QRコード読み取りライブラリを読み込めませんでした。'
            );
            return;
        }

        startCameraButton.disabled = true;

        try {
            qrScanner = new Html5Qrcode('qr-reader');

            await qrScanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    },
                    aspectRatio: 1.0
                },
                onQrCodeScanned,
                function () {
                    // QRコード未検出時はスキャンを続ける
                }
            );

            isCameraRunning = true;
            stopCameraButton.disabled = false;

            renderResult(
                true,
                'カメラを起動しました。QRコードをかざしてください。'
            );
        } catch (error) {
            if (qrScanner) {
                try {
                    await qrScanner.stop();
                } catch (stopError) {
                    // 起動失敗時は停止処理を続行する
                }

                try {
                    qrScanner.clear();
                } catch (clearError) {
                    // 初期化が完了していない場合は無視する
                }
            }

            qrScanner = null;
            isCameraRunning = false;

            renderResult(
                false,
                'カメラを起動できません。カメラの使用許可とHTTPS接続を確認してください。'
            );
        } finally {
            startCameraButton.disabled = false;
        }
    }

    /**
     * カメラを停止する
     */
    async function stopCamera() {
        if (!qrScanner) {
            isCameraRunning = false;
            startCameraButton.disabled = false;
            stopCameraButton.disabled = true;
            return;
        }

        try {
            if (isCameraRunning) {
                await qrScanner.stop();
            }
        } catch (error) {
            console.error('カメラ停止エラー:', error);
        } finally {
            try {
                qrScanner.clear();
            } catch (error) {
                // 既にクリアされている場合は無視する
            }

            qrScanner = null;
            isCameraRunning = false;

            startCameraButton.disabled = false;
            stopCameraButton.disabled = true;
        }
    }

    startCameraButton.addEventListener('click', startCamera);
    stopCameraButton.addEventListener('click', stopCamera);

    /**
     * トークンが変更されたら再確認が必要
     */
    tokenInput.addEventListener('input', function () {
        resetUserInfo();
        resultDiv.replaceChildren();
    });

    /**
     * ユーザー照会
     *
     * 想定API:
     * POST /user/lookup
     *
     * 入力:
     * token, csrf_token
     *
     * 出力:
     * success, userId, isActive, message
     */
    verifyUserButton.addEventListener('click', async function () {
        if (isProcessing) {
            return;
        }

        const token = tokenInput.value.trim();

        if (!token) {
            renderResult(false, 'トークンを入力してください。');
            return;
        }

        const csrfToken = validateCsrfToken();

        if (!csrfToken) {
            return;
        }

        isProcessing = true;
        verifyUserButton.disabled = true;

        try {
            const data = await postJson('/user/lookup', {
                token: token,
                csrf_token: csrfToken
            });

            // APIレスポンスを検証
            if (typeof data.isActive !== 'boolean') {
                throw new Error(
                    'アカウント状態を正しく確認できませんでした。'
                );
            }

            userIdInput.value = data.userId ?? '不明';

            if (data.isActive === true) {
                accountStatusInput.value = '有効';
                isUserVerified = false;
                activateButton.disabled = true;

                renderResult(
                    false,
                    'このユーザーは既に有効化されています。'
                );

                return;
            }

            // API側で終了済みユーザーを拒否すること
            accountStatusInput.value = '未有効化';
            isUserVerified = true;
            activateButton.disabled = false;

            renderResult(
                true,
                'ユーザーを確認しました。支払い確認後に有効化してください。'
            );
        } catch (error) {
            resetUserInfo();

            renderResult(
                false,
                error.message || 'ユーザー照会に失敗しました。'
            );
        } finally {
            isProcessing = false;
            verifyUserButton.disabled = false;
        }
    });

    /**
     * アカウント有効化
     *
     * 想定API:
     * POST /user/activate
     *
     * 入力:
     * token, csrf_token
     *
     * 出力:
     * success, userId, isActive, message
     */
    activateForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (isProcessing) {
            return;
        }

        const token = tokenInput.value.trim();

        if (!token) {
            renderResult(false, 'トークンを入力してください。');
            return;
        }

        if (!isUserVerified) {
            renderResult(
                false,
                '先にユーザーを確認してください。'
            );
            return;
        }

        const csrfToken = validateCsrfToken();

        if (!csrfToken) {
            return;
        }

        isProcessing = true;
        activateButton.disabled = true;
        activateButton.textContent = '処理中...';

        try {
            const data = await postJson('/user/activate', {
                token: token,
                csrf_token: csrfToken
            });

            userIdInput.value = data.userId ?? '不明';

            if (data.isActive !== true) {
                throw new Error(
                    '有効化後のアカウント状態を確認できませんでした。'
                );
            }

            accountStatusInput.value = '有効';
            isUserVerified = false;

            renderResult(
                true,
                data.message || 'アカウントを有効化しました。'
            );
        } catch (error) {
            resetUserInfo();

            renderResult(
                false,
                error.message || '通信エラーが発生しました。'
            );
        } finally {
            isProcessing = false;
            activateButton.textContent = 'アクティベートする';
            activateButton.disabled = !isUserVerified;
        }
    });

    /**
     * フォームリセット
     */
    activateForm.addEventListener('reset', function () {
        stopCamera();

        resetUserInfo();
        resultDiv.replaceChildren();
    });

    /**
     * ページを離れる際にカメラを停止する
     */
    window.addEventListener('pagehide', function () {
        if (qrScanner && isCameraRunning) {
            qrScanner.stop().catch(function (error) {
                console.error('カメラ停止エラー:', error);
            });
        }
    });
</script>

</body>
</html>
