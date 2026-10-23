
<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <!-- ファビコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <!-- OGP設定 -->
    <meta property="og:title" content="店舗管理ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="得点付与対象の店舗を管理します">

    <!-- タイトル・説明 -->
    <title>店舗管理ページ</title>
    <meta name="description" content="管理者用の店舗・ブース管理ページ">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body class="bg-light">

<main class="container py-4">
    <div class="card shadow-sm">

        <!-- ページヘッダー -->
        <div class="card-header bg-primary text-white p-3">
            <h1 class="h4 mb-1">店舗管理</h1>
            <p class="small mb-0">管理者用｜店舗・ブースの登録と管理</p>
        </div>

        <div class="card-body">

            <!-- 結果・エラーメッセージ -->
            <div
                id="messageArea"
                class="alert d-none"
                role="alert"
                aria-live="polite"
            ></div>

            <!-- 店舗登録・更新フォーム -->
            <section class="mb-4">
                <h2 class="h5 mb-3" id="formTitle">店舗の新規登録</h2>

                <form id="storeForm">
                    <!-- CSRFトークン -->
                    <input type="hidden" name="csrf_token" value="">

                    <!-- 編集時の店舗ID -->
                    <input type="hidden" id="storeId" name="storeId" value="">

                    <div class="mb-3">
                        <label for="storeName" class="form-label fw-bold">
                            店舗名 *
                        </label>

                        <input
                            type="text"
                            id="storeName"
                            name="name"
                            class="form-control"
                            maxlength="100"
                            placeholder="店舗名・ブース名を入力してください"
                            autocomplete="off"
                            required
                        >

                        <div class="form-text">
                            店舗名は100文字以内で入力してください。
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button
                            type="submit"
                            id="saveButton"
                            class="btn btn-primary"
                        >
                            新規登録
                        </button>

                        <button
                            type="button"
                            id="cancelEditButton"
                            class="btn btn-outline-secondary d-none"
                        >
                            更新をキャンセル
                        </button>
                    </div>
                </form>
            </section>

            <hr>

            <!-- 店舗一覧 -->
            <section>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 mb-0">店舗一覧</h2>

                    <button
                        type="button"
                        id="reloadButton"
                        class="btn btn-outline-primary btn-sm"
                    >
                        再読み込み
                    </button>
                </div>

                <p class="text-secondary small">
                    登録されている店舗を確認・編集・削除できます。
                </p>

                <!-- 読み込み状態 -->
                <div
                    id="loadingArea"
                    class="text-center py-4 d-none"
                    role="status"
                >
                    店舗一覧を読み込んでいます...
                </div>

                <!-- 店舗がない場合 -->
                <div
                    id="emptyArea"
                    class="alert alert-secondary d-none"
                    role="status"
                >
                    登録されている店舗はありません。
                </div>

                <!-- 店舗一覧テーブル -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">店舗ID</th>
                                <th scope="col">店舗名</th>
                                <th scope="col">操作</th>
                            </tr>
                        </thead>

                        <tbody id="storeList">
                            <tr>
                                <td colspan="3" class="text-center text-secondary">
                                    読み込み中...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="card-footer text-secondary small">
            店舗管理ページ
        </div>
    </div>
</main>

<!-- 共通フッター：1回だけ読み込む -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

<!-- CSRFトークンの自動埋め込み：1回だけ読み込む -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

<script>
(() => {
    'use strict';

    // ========================================
    // DOM要素
    // ========================================

    const storeForm = document.getElementById('storeForm');
    const storeIdInput = document.getElementById('storeId');
    const storeNameInput = document.getElementById('storeName');
    const csrfInput = storeForm.querySelector('input[name="csrf_token"]');

    const formTitle = document.getElementById('formTitle');
    const saveButton = document.getElementById('saveButton');
    const cancelEditButton = document.getElementById('cancelEditButton');

    const reloadButton = document.getElementById('reloadButton');
    const storeList = document.getElementById('storeList');

    const messageArea = document.getElementById('messageArea');
    const loadingArea = document.getElementById('loadingArea');
    const emptyArea = document.getElementById('emptyArea');

    // ========================================
    // 状態管理
    // ========================================

    let stores = [];
    let loading = false;
    let saving = false;
    let deletingStoreId = null;

    // ========================================
    // メッセージ表示
    // ========================================

    function showMessage(message, type = 'info') {
        messageArea.textContent = message;
        messageArea.className = `alert alert-${type}`;
    }

    function clearMessage() {
        messageArea.textContent = '';
        messageArea.className = 'alert d-none';
    }

    // ========================================
    // CSRFトークン取得
    // ========================================

    function getCsrfToken() {
        if (csrfInput && csrfInput.value) {
            return csrfInput.value;
        }

        const meta = document.querySelector('meta[name="csrf-token"]');

        if (meta && meta.content) {
            return meta.content;
        }

        return '';
    }

    // ========================================
    // APIレスポンス確認
    // ========================================

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

    // ========================================
    // エラー表示用メッセージ
    // ========================================

    function getErrorMessage(error, fallback) {
        return error instanceof Error && error.message
            ? error.message
            : fallback;
    }

    // ========================================
    // 店舗一覧取得
    // GET /api/admin/stores
    // force=true の場合は保存・削除後の再取得を許可する
    // ========================================

    async function loadStores(force = false) {
        if (loading) {
            return;
        }

        if (!force && (saving || deletingStoreId !== null)) {
            return;
        }

        loading = true;
        reloadButton.disabled = true;

        loadingArea.classList.remove('d-none');
        emptyArea.classList.add('d-none');

        storeList.replaceChildren();

        const loadingRow = document.createElement('tr');
        const loadingCell = document.createElement('td');

        loadingCell.colSpan = 3;
        loadingCell.className = 'text-center text-secondary';
        loadingCell.textContent = '読み込み中...';

        loadingRow.appendChild(loadingCell);
        storeList.appendChild(loadingRow);

        try {
            const response = await fetch('/api/admin/stores', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await readResponse(response);

            if (!Array.isArray(data.stores)) {
                throw new Error('店舗一覧の形式が正しくありません。');
            }

            stores = data.stores;
            renderStores();

        } catch (error) {
            stores = [];
            storeList.replaceChildren();

            const tr = document.createElement('tr');
            const td = document.createElement('td');

            td.colSpan = 3;
            td.className = 'text-center text-danger';
            td.textContent = '店舗一覧を取得できませんでした。';

            tr.appendChild(td);
            storeList.appendChild(tr);

            showMessage(
                getErrorMessage(error, '店舗一覧の取得に失敗しました。'),
                'danger'
            );

        } finally {
            loading = false;
            loadingArea.classList.add('d-none');

            reloadButton.disabled =
                saving || deletingStoreId !== null;
        }
    }

    // ========================================
    // 店舗一覧表示
    // ========================================

    function renderStores() {
        storeList.replaceChildren();

        emptyArea.classList.toggle('d-none', stores.length !== 0);

        if (stores.length === 0) {
            return;
        }

        stores.forEach((store) => {
            const tr = document.createElement('tr');

            // 店舗ID
            const idCell = document.createElement('td');
            idCell.textContent = String(
                store.storeId ?? store.id ?? '-'
            );

            // 店舗名
            const nameCell = document.createElement('td');
            nameCell.textContent = store.name ?? '-';

            // 操作欄
            const actionCell = document.createElement('td');
            const buttonGroup = document.createElement('div');

            buttonGroup.className = 'd-grid gap-2';

            // 編集ボタン
            const editButton = document.createElement('button');

            editButton.type = 'button';
            editButton.className = 'btn btn-sm btn-outline-primary';
            editButton.textContent = '編集';
            editButton.disabled = saving || deletingStoreId !== null;

            editButton.addEventListener('click', () => {
                startEdit(store);
            });

            // 削除ボタン
            const deleteButton = document.createElement('button');

            deleteButton.type = 'button';
            deleteButton.className = 'btn btn-sm btn-outline-danger';
            deleteButton.textContent = '削除';
            deleteButton.disabled = saving || deletingStoreId !== null;

            deleteButton.addEventListener('click', () => {
                deleteStore(store);
            });

            buttonGroup.append(editButton, deleteButton);
            actionCell.appendChild(buttonGroup);

            tr.append(idCell, nameCell, actionCell);
            storeList.appendChild(tr);
        });
    }

    // ========================================
    // 新規登録フォームに戻す
    // CSRFトークンは reset() で消えないように保持する
    // ========================================

    function resetForm() {
        const csrfToken = getCsrfToken();

        storeForm.reset();

        storeIdInput.value = '';
        storeNameInput.value = '';

        if (csrfInput && csrfToken) {
            csrfInput.value = csrfToken;
        }

        formTitle.textContent = '店舗の新規登録';
        saveButton.textContent = '新規登録';

        cancelEditButton.classList.add('d-none');
        cancelEditButton.disabled = false;
    }

    // ========================================
    // 編集開始
    // ========================================

    function startEdit(store) {
        if (saving || loading || deletingStoreId !== null) {
            return;
        }

        const id = store.storeId ?? store.id;

        if (id === undefined || id === null || id === '') {
            showMessage(
                '店舗IDが取得できないため編集できません。',
                'danger'
            );
            return;
        }

        clearMessage();

        storeIdInput.value = String(id);
        storeNameInput.value = store.name ?? '';

        formTitle.textContent = '店舗情報の更新';
        saveButton.textContent = '更新する';

        cancelEditButton.classList.remove('d-none');

        storeNameInput.focus();

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // ========================================
    // 新規登録・更新
    // POST /api/admin/store
    // ========================================

    storeForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (saving || loading || deletingStoreId !== null) {
            return;
        }

        const name = storeNameInput.value.trim();
        const storeId = storeIdInput.value.trim();

        if (!name) {
            showMessage('店舗名を入力してください。', 'warning');
            storeNameInput.focus();
            return;
        }

        if (name.length > 100) {
            showMessage(
                '店舗名は100文字以内で入力してください。',
                'warning'
            );
            storeNameInput.focus();
            return;
        }

        const csrfToken = getCsrfToken();

        if (!csrfToken) {
            showMessage(
                'CSRFトークンを取得できません。ページを再読み込みしてください。',
                'danger'
            );
            return;
        }

        const isUpdate = storeId !== '';

        const requestBody = {
            name: name,
            csrfToken: csrfToken
        };

        if (isUpdate) {
            requestBody.storeId = storeId;
        }

        saving = true;
        saveButton.disabled = true;
        cancelEditButton.disabled = true;
        reloadButton.disabled = true;

        clearMessage();

        try {
            const response = await fetch('/api/admin/store', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(requestBody)
            });

            // HTTPステータスとsuccessを確認する。
            // data.storeの有無はAPI仕様に合わせて任意とする。
            await readResponse(response);

            showMessage(
                isUpdate
                    ? '店舗情報を更新しました。'
                    : '店舗を登録しました。',
                'success'
            );

            resetForm();

            // 保存中フラグを解除せずに強制再取得する
            await loadStores(true);

        } catch (error) {
            showMessage(
                getErrorMessage(error, '店舗の保存に失敗しました。'),
                'danger'
            );

        } finally {
            saving = false;
            saveButton.disabled = false;
            cancelEditButton.disabled = false;
            reloadButton.disabled = deletingStoreId !== null;
        }
    });

    // ========================================
    // 削除
    // POST /api/admin/store/delete
    // ========================================

    async function deleteStore(store) {
        if (saving || loading || deletingStoreId !== null) {
            return;
        }

        const storeId = store.storeId ?? store.id;

        if (storeId === undefined || storeId === null || storeId === '') {
            showMessage(
                '店舗IDが取得できないため削除できません。',
                'danger'
            );
            return;
        }

        const storeName = store.name ?? '名称未設定';

        const confirmed = window.confirm(
            `店舗「${storeName}」（ID: ${storeId}）を削除しますか？\n\n` +
            '得点履歴やスタッフとの紐付けがある場合、削除できないことがあります。'
        );

        if (!confirmed) {
            return;
        }

        const csrfToken = getCsrfToken();

        if (!csrfToken) {
            showMessage(
                'CSRFトークンを取得できません。ページを再読み込みしてください。',
                'danger'
            );
            return;
        }

        deletingStoreId = String(storeId);
        reloadButton.disabled = true;

        clearMessage();

        // 操作ボタンを一時的に無効化する
        renderStores();

        try {
            const response = await fetch('/api/admin/store/delete', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    storeId: storeId,
                    csrfToken: csrfToken
                })
            });

            await readResponse(response);

            showMessage('店舗を削除しました。', 'success');

            // 削除した店舗を編集中ならフォームをリセット
            if (storeIdInput.value === String(storeId)) {
                resetForm();
            }

            // 削除中フラグを解除せずに強制再取得する
            await loadStores(true);

        } catch (error) {
            showMessage(
                getErrorMessage(error, '店舗を削除できませんでした。'),
                'danger'
            );

        } finally {
            deletingStoreId = null;
            reloadButton.disabled = saving;
            renderStores();
        }
    }

    // ========================================
    // 更新キャンセル
    // ========================================

    cancelEditButton.addEventListener('click', () => {
        if (saving || deletingStoreId !== null) {
            return;
        }

        resetForm();
        clearMessage();
    });

    // ========================================
    // 再読み込み
    // ========================================

    reloadButton.addEventListener('click', async () => {
        if (saving || deletingStoreId !== null || loading) {
            return;
        }

        clearMessage();
        await loadStores();
    });

    // ========================================
    // 初期表示
    // ========================================

    loadStores();

})();
</script>

</body>
</html>
