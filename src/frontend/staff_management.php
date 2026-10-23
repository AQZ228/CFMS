
<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- 基本設定 -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">
    <meta name="description" content="スタッフアカウント、権限、所属店舗を管理します">
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- CSRFトークン -->
    <meta name="csrf-token" content="">

    <!-- ファビコン -->
    <link rel="icon" href="/images/CFMS.ico" sizes="any">
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/CFMS.png">

    <!-- OGP設定 -->
    <meta property="og:title" content="スタッフ管理ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="管理者向けスタッフ管理ページ">

    <title>スタッフ管理ページ</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

<main class="container py-4">

    <div class="card shadow-sm">

        <!-- ヘッダー -->
        <div class="card-header bg-primary text-white p-3">
            <h1 class="h4 mb-1">スタッフ管理</h1>
            <p class="small mb-0">管理者用｜アカウント・権限・店舗管理</p>
        </div>

        <div class="card-body">

            <!-- 結果・エラーメッセージ -->
            <div
                id="messageArea"
                class="alert d-none"
                role="alert"
                aria-live="polite">
            </div>

            <!-- スタッフ一覧 -->
            <section class="mb-4">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                    <h2 class="h5 mb-0">スタッフ一覧</h2>

                    <button
                        type="button"
                        id="newStaffButton"
                        class="btn btn-primary">
                        ＋ 新規作成
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>スタッフID</th>
                                <th>スタッフ名</th>
                                <th>権限</th>
                                <th>所属店舗</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody id="staffList">
                            <tr>
                                <td colspan="5" class="text-center text-secondary">
                                    読み込み中...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </section>

            <hr>

            <!-- スタッフ新規作成・編集 -->
            <section id="staffFormSection">

                <h2 class="h5 mb-3" id="formTitle">
                    スタッフ新規作成
                </h2>

                <form id="staffForm">

                    <!-- CSRFトークン -->
                    <input type="hidden" name="csrf_token" value="">

                    <!-- スタッフID -->
                    <div class="mb-3">
                        <label for="staffId" class="form-label">
                            スタッフID *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="staffId"
                            name="staffId"
                            maxlength="100"
                            autocomplete="off"
                            required>

                        <div class="form-text">
                            編集時はスタッフIDを変更できません。
                        </div>
                    </div>

                    <!-- スタッフ名 -->
                    <div class="mb-3">
                        <label for="staffName" class="form-label">
                            スタッフ名 *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="staffName"
                            name="name"
                            maxlength="100"
                            required>
                    </div>

                    <!-- パスワード -->
                    <div class="mb-3">
                        <label for="staffPassword" class="form-label">
                            パスワード
                            <span id="passwordRequiredMark">*</span>
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="staffPassword"
                            name="password"
                            autocomplete="new-password">

                        <div class="form-text" id="passwordHelp">
                            新規作成時は必須です。編集時は変更する場合のみ入力してください。
                        </div>
                    </div>

                    <!-- 権限 -->
                    <fieldset class="mb-3">
                        <legend class="form-label fs-6">
                            権限 *
                        </legend>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="roleReception"
                                value="reception"
                                required>

                            <label class="form-check-label" for="roleReception">
                                受付
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="roleStore"
                                value="store">

                            <label class="form-check-label" for="roleStore">
                                店舗
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="rolePrize"
                                value="prize">

                            <label class="form-check-label" for="rolePrize">
                                景品担当
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="roleAdmin"
                                value="admin">

                            <label class="form-check-label" for="roleAdmin">
                                管理者
                            </label>
                        </div>
                    </fieldset>

                    <!-- 所属店舗 -->
                    <fieldset class="mb-4">
                        <legend class="form-label fs-6">
                            所属店舗
                        </legend>

                        <div id="storeCheckboxes">
                            <p class="text-secondary small">
                                店舗一覧を読み込んでいます...
                            </p>
                        </div>

                        <div class="form-text">
                            所属する店舗を複数選択できます。
                        </div>
                    </fieldset>

                    <!-- 保存・キャンセル -->
                    <div class="d-grid gap-2 d-sm-flex">

                        <button
                            type="submit"
                            id="saveButton"
                            class="btn btn-success flex-fill">
                            保存
                        </button>

                        <button
                            type="button"
                            id="cancelButton"
                            class="btn btn-outline-secondary flex-fill">
                            キャンセル
                        </button>

                    </div>

                </form>

                <!-- スタッフ削除 -->
                <form id="deleteForm" class="mt-3 d-none">
                    <input type="hidden" name="csrf_token" value="">

                    <button
                        type="submit"
                        id="deleteButton"
                        class="btn btn-danger w-100">
                        このスタッフを削除
                    </button>
                </form>

            </section>

        </div>

        <div class="card-footer text-secondary small">
            スタッフ管理ページ
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

    // ========================================
    // DOM要素
    // ========================================

    const staffList = document.getElementById('staffList');
    const staffForm = document.getElementById('staffForm');
    const deleteForm = document.getElementById('deleteForm');
    const messageArea = document.getElementById('messageArea');

    const staffIdInput = document.getElementById('staffId');
    const staffNameInput = document.getElementById('staffName');
    const staffPasswordInput = document.getElementById('staffPassword');

    const storeCheckboxes = document.getElementById('storeCheckboxes');

    const formTitle = document.getElementById('formTitle');
    const saveButton = document.getElementById('saveButton');
    const deleteButton = document.getElementById('deleteButton');

    const newStaffButton = document.getElementById('newStaffButton');
    const cancelButton = document.getElementById('cancelButton');

    // ========================================
    // 状態管理
    // ========================================

    let editing = false;
    let loadingStaffs = false;
    let saving = false;
    let deleting = false;
    let loadingStores = false;

    let staffs = [];
    let stores = [];

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
        const meta = document.querySelector(
            'meta[name="csrf-token"]'
        );

        if (meta && meta.content.trim() !== '') {
            return meta.content.trim();
        }

        const inputs = document.querySelectorAll(
            'input[name="csrf_token"]'
        );

        for (const input of inputs) {
            if (input.value.trim() !== '') {
                return input.value.trim();
            }
        }

        return '';
    }

    function requireCsrfToken() {
        const token = getCsrfToken();

        if (!token) {
            throw new Error(
                'CSRFトークンを取得できません。ページを再読み込みしてください。'
            );
        }

        return token;
    }

    // ========================================
    // APIレスポンス処理
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
    // 共通APIリクエスト
    // ========================================

    async function apiRequest(url, method = 'GET', payload = null) {
        const headers = {
            'Accept': 'application/json'
        };

        const options = {
            method: method,
            credentials: 'same-origin',
            headers: headers
        };

        if (payload !== null) {
            const csrfToken = requireCsrfToken();

            payload.csrfToken = csrfToken;

            headers['Content-Type'] = 'application/json';
            headers['X-CSRF-Token'] = csrfToken;

            options.body = JSON.stringify(payload);
        }

        const response = await fetch(url, options);

        return await readResponse(response);
    }

    // ========================================
    // 権限名変換
    // ========================================

    function roleLabel(role) {
        const roles = {
            reception: '受付',
            store: '店舗',
            prize: '景品担当',
            admin: '管理者'
        };

        return roles[role] ?? role ?? '-';
    }

    // ========================================
    // スタッフ一覧取得
    // GET /api/admin/staffs
    // ========================================

    async function loadStaffs() {
        if (loadingStaffs) return;

        loadingStaffs = true;

        staffList.replaceChildren();

        const row = document.createElement('tr');
        const cell = document.createElement('td');

        cell.colSpan = 5;
        cell.className = 'text-center';
        cell.textContent = '読み込み中...';

        row.appendChild(cell);
        staffList.appendChild(row);

        try {
            const data = await apiRequest('/api/admin/staffs');

            staffs = Array.isArray(data.staffs)
                ? data.staffs
                : [];

            renderStaffs();

        } catch (error) {
            staffList.replaceChildren();

            const errorRow = document.createElement('tr');
            const errorCell = document.createElement('td');

            errorCell.colSpan = 5;
            errorCell.className = 'text-danger text-center';
            errorCell.textContent = error.message;

            errorRow.appendChild(errorCell);
            staffList.appendChild(errorRow);

            showMessage(error.message, 'danger');

        } finally {
            loadingStaffs = false;
        }
    }

    // ========================================
    // スタッフ一覧表示
    // ========================================

    function renderStaffs() {
        staffList.replaceChildren();

        if (staffs.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');

            cell.colSpan = 5;
            cell.className = 'text-center text-secondary';
            cell.textContent = 'スタッフが登録されていません。';

            row.appendChild(cell);
            staffList.appendChild(row);

            return;
        }

        staffs.forEach((staff) => {
            const row = document.createElement('tr');

            const idCell = document.createElement('td');
            idCell.textContent = staff.staffId ?? staff.id ?? '-';

            const nameCell = document.createElement('td');
            nameCell.textContent = staff.name ?? '-';

            const roleCell = document.createElement('td');
            roleCell.textContent = roleLabel(staff.role);

            const storeCell = document.createElement('td');

            const storeIds = Array.isArray(staff.storeIds)
                ? staff.storeIds
                : [];

            const storeNames = storeIds.map((id) => {
                const store = stores.find(
                    (item) => String(item.id) === String(id)
                );

                return store
                    ? store.name
                    : `店舗ID:${id}`;
            });

            storeCell.textContent = storeNames.length > 0
                ? storeNames.join('、')
                : '未所属';

            const actionCell = document.createElement('td');

            const editButton = document.createElement('button');

            editButton.type = 'button';
            editButton.className = 'btn btn-sm btn-outline-primary';
            editButton.textContent = '編集';

            editButton.addEventListener('click', () => {
                editStaff(staff);
            });

            actionCell.appendChild(editButton);

            row.append(
                idCell,
                nameCell,
                roleCell,
                storeCell,
                actionCell
            );

            staffList.appendChild(row);
        });
    }

    // ========================================
    // 店舗一覧取得
    // GET /api/admin/stores
    // ========================================

    async function loadStores() {
        if (loadingStores) return;

        loadingStores = true;

        try {
            const data = await apiRequest('/api/admin/stores');

            stores = Array.isArray(data.stores)
                ? data.stores
                : [];

            renderStoreCheckboxes();
            renderStaffs();

        } catch (error) {
            stores = [];
            renderStoreCheckboxes();

            showMessage(
                `店舗一覧の取得に失敗しました：${error.message}`,
                'danger'
            );

        } finally {
            loadingStores = false;
        }
    }

    // ========================================
    // 所属店舗チェックボックス
    // ========================================

    function renderStoreCheckboxes(selectedIds = []) {
        storeCheckboxes.replaceChildren();

        if (stores.length === 0) {
            const message = document.createElement('p');

            message.className = 'text-secondary small';
            message.textContent =
                '店舗一覧を取得できません。店舗一覧APIを確認してください。';

            storeCheckboxes.appendChild(message);
            return;
        }

        stores.forEach((store) => {
            const wrapper = document.createElement('div');

            wrapper.className = 'form-check';

            const checkbox = document.createElement('input');

            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input';
            checkbox.name = 'storeIds[]';
            checkbox.value = store.id;
            checkbox.id = `store-${store.id}`;

            checkbox.checked = selectedIds.some(
                (id) => String(id) === String(store.id)
            );

            const label = document.createElement('label');

            label.className = 'form-check-label';
            label.htmlFor = checkbox.id;
            label.textContent = store.name ?? `店舗ID:${store.id}`;

            wrapper.append(checkbox, label);
            storeCheckboxes.appendChild(wrapper);
        });
    }

    // ========================================
    // 選択された店舗ID取得
    // ========================================

    function getSelectedStoreIds() {
        return Array.from(
            storeCheckboxes.querySelectorAll(
                'input[name="storeIds[]"]:checked'
            )
        ).map((input) => input.value);
    }

    // ========================================
    // フォーム初期化
    // ========================================

    function resetForm() {
        staffForm.reset();

        editing = false;

        staffIdInput.disabled = false;
        staffPasswordInput.required = true;

        document.getElementById(
            'passwordRequiredMark'
        ).classList.remove('d-none');

        formTitle.textContent = 'スタッフ新規作成';
        saveButton.textContent = '保存';

        deleteForm.classList.add('d-none');

        renderStoreCheckboxes();

        clearMessage();
    }

    // ========================================
    // 新規作成ボタン
    // ========================================

    newStaffButton.addEventListener('click', () => {
        resetForm();

        document.getElementById('staffFormSection')
            .scrollIntoView({ behavior: 'smooth' });
    });

    // ========================================
    // 編集
    // ========================================

    function editStaff(staff) {
        clearMessage();

        editing = true;

        staffIdInput.value = staff.staffId ?? staff.id ?? '';
        staffIdInput.disabled = true;

        staffNameInput.value = staff.name ?? '';
        staffPasswordInput.value = '';
        staffPasswordInput.required = false;

        const selectedRole = staff.role ?? 'reception';

        staffForm.querySelectorAll(
            'input[name="role"]'
        ).forEach((input) => {
            input.checked = input.value === selectedRole;
        });

        renderStoreCheckboxes(staff.storeIds ?? []);

        document.getElementById(
            'passwordRequiredMark'
        ).classList.add('d-none');

        formTitle.textContent = 'スタッフ編集';
        saveButton.textContent = '変更を保存';

        deleteForm.classList.remove('d-none');

        document.getElementById('staffFormSection')
            .scrollIntoView({ behavior: 'smooth' });
    }

    // ========================================
    // 保存
    // POST /api/admin/staff
    // ========================================

    staffForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (saving) return;

        const staffId = staffIdInput.value.trim();
        const name = staffNameInput.value.trim();
        const password = staffPasswordInput.value;

        const selectedRole = staffForm.querySelector(
            'input[name="role"]:checked'
        );

        if (!staffId || !name || !selectedRole) {
            showMessage(
                'スタッフID、スタッフ名、権限を入力してください。',
                'warning'
            );
            return;
        }

        if (!editing && !password) {
            showMessage(
                '新規作成時はパスワードが必須です。',
                'warning'
            );
            return;
        }

        if (!editing && stores.length === 0) {
            showMessage(
                '店舗一覧を取得できていないため、保存できません。',
                'warning'
            );
            return;
        }

        const payload = {
            staffId: staffId,
            name: name,
            role: selectedRole.value,
            storeIds: getSelectedStoreIds()
        };

        if (!editing || password !== '') {
            payload.password = password;
        }

        saving = true;
        saveButton.disabled = true;

        try {
            const data = await apiRequest(
                '/api/admin/staff',
                'POST',
                payload
            );

            resetForm();

            showMessage(
                data.message || 'スタッフ情報を保存しました。',
                'success'
            );

            await loadStaffs();

        } catch (error) {
            showMessage(error.message, 'danger');

        } finally {
            saving = false;
            saveButton.disabled = false;
        }
    });

    // ========================================
    // 削除
    // POST /api/admin/staff/delete
    // ========================================

    deleteForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (deleting) return;

        const staffId = staffIdInput.value.trim();

        if (!staffId) {
            showMessage(
                '削除対象のスタッフIDがありません。',
                'warning'
            );
            return;
        }

        const confirmed = window.confirm(
            `スタッフID「${staffId}」を削除しますか？`
        );

        if (!confirmed) return;

        deleting = true;
        deleteButton.disabled = true;

        try {
            const data = await apiRequest(
                '/api/admin/staff/delete',
                'POST',
                {
                    staffId: staffId
                }
            );

            resetForm();

            showMessage(
                data.message || 'スタッフを削除しました。',
                'success'
            );

            await loadStaffs();

        } catch (error) {
            showMessage(error.message, 'danger');

        } finally {
            deleting = false;
            deleteButton.disabled = false;
        }
    });

    // ========================================
    // キャンセル
    // ========================================

    cancelButton.addEventListener('click', () => {
        resetForm();
    });

    // ========================================
    // 初期表示
    // ========================================

    resetForm();

    loadStores();
    loadStaffs();

})();
</script>

</body>
</html>
