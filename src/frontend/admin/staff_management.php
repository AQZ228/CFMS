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
    <meta property="og:title" content="スタッフ管理ページ">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nkc.sanae.dev">
    <meta property="og:image" content="/images/CFMS.png">
    <meta property="og:image:alt" content="CFMS">
    <meta property="og:description" content="スタッフ情報を管理するページ">

    <title>スタッフ管理ページ</title>
    <meta name="description" content="スタッフ情報の登録・編集・削除を行います">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

    <main class="container py-4">

        <div class="card-header bg-primary text-white p-3">
            <h1 class="h4 mb-1">スタッフ管理</h1>
            <p class="small mb-0">管理者用｜スタッフ情報の登録・編集・削除</p>
        </div>

        <!-- メッセージ表示 -->
        <div id="result" class="mb-3" aria-live="polite"></div>

        <!-- スタッフ一覧 -->
        <section class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">スタッフ一覧</h2>
                <button type="button" class="btn btn-primary" id="newStaffBtn">
                    新規登録
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>スタッフID</th>
                                <th>名前</th>
                                <th>役割</th>
                                <th>担当店舗</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="staffList">
                            <tr>
                                <td colspan="5" class="text-center">
                                    スタッフ情報を読み込んでいます。
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- スタッフ登録・編集フォーム -->
        <section class="card shadow-sm mb-4" id="staffFormSection">
            <div class="card-header">
                <h2 class="h5 mb-0" id="formTitle">スタッフ登録</h2>
            </div>

            <div class="card-body">
                <form id="staffForm">

                    <!-- CSRFトークン -->
                    <input type="hidden" class="csrf_token" name="csrf_token" value="">

                    <!-- 編集対象スタッフID -->
                    <input type="hidden" id="originalStaffId">

                    <div class="mb-3">
                        <label for="staffName" class="form-label">
                            名前 <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="staffName"
                            name="name"
                            maxlength="100"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="staffPassword" class="form-label">
                            パスワード
                            <span class="text-danger" id="passwordRequiredMark">*</span>
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            id="staffPassword"
                            name="password"
                            autocomplete="new-password"
                            required>

                        <div class="form-text" id="passwordHelp">
                            新規登録時はパスワードが必須です。
                        </div>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label">
                            役割 <span class="text-danger">*</span>
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
                                店舗スタッフ
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

                    <fieldset class="mb-4">
                        <legend class="form-label">
                            所属店舗
                        </legend>

                        <div id="storeCheckboxes">
                            <p class="text-muted mb-0">
                                店舗情報を読み込んでいます。
                            </p>
                        </div>
                    </fieldset>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="saveStaffBtn">
                            登録する
                        </button>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            id="cancelBtn">
                            キャンセル
                        </button>
                    </div>

                </form>
            </div>
        </section>

        <!-- スタッフ削除フォーム -->
        <section class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">スタッフ削除</h2>
            </div>

            <div class="card-body">
                <form id="deleteForm">

                    <input
                        type="hidden"
                        class="csrf_token"
                        name="csrf_token"
                        value="">

                    <div class="mb-3">
                        <label for="deleteStaffId" class="form-label">
                            削除するスタッフID
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="deleteStaffId"
                            name="staffId"
                            min="1"
                            required>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-danger"
                        id="deleteStaffBtn">
                        スタッフを削除
                    </button>

                </form>
            </div>
        </section>

    </main>

    <script>
        const API_BASE_URL = '<?= getenv('API_URL') ?>';

        const STAFFS_API = `${API_BASE_URL}/admin/staffs.php`;
        const STAFF_API = `${API_BASE_URL}/admin/staff.php`;
        const STAFF_DELETE_API = `${API_BASE_URL}/admin/staff_delete.php`;
        const STORES_API = `${API_BASE_URL}/admin/stores.php`;

        let staffs = [];
        let stores = [];

        const roleNames = {
            reception: '受付',
            store: '店舗スタッフ',
            prize: '景品担当',
            admin: '管理者'
        };

        function renderResult(data) {
            const resultDiv = document.getElementById('result');

            resultDiv.replaceChildren();

            const alertDiv = document.createElement('div');

            alertDiv.className = data.success ?
                'alert alert-success' :
                'alert alert-danger';

            alertDiv.textContent = data.message ||
                (data.success ?
                    '処理が完了しました。' :
                    '処理に失敗しました。');

            resultDiv.appendChild(alertDiv);
        }

        function getCsrfToken() {
            return document.querySelector('.csrf_token')?.value ?? '';
        }

        async function waitForCsrfToken(timeoutMs = 10000) {
            const start = Date.now();

            while (Date.now() - start < timeoutMs) {
                const token = getCsrfToken();

                if (token) {
                    return token;
                }

                await new Promise(resolve => setTimeout(resolve, 100));
            }

            throw new Error('CSRFトークンの取得に失敗しました。');
        }

        async function postJson(url, body = {}) {
            const csrf_token = await waitForCsrfToken();

            const response = await fetch(url, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...body,
                    csrf_token
                })
            });

            if (!response.ok) {
                throw new Error('通信に失敗しました。');
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(
                    data.message || '処理に失敗しました。'
                );
            }

            return data;
        }

        async function loadStores() {
            const data = await postJson(STORES_API);

            stores = Array.isArray(data.stores) ?
                data.stores : [];

            renderStoreCheckboxes();
        }

        async function loadStaffs() {
            const data = await postJson(STAFFS_API);

            staffs = Array.isArray(data.staffs) ?
                data.staffs : [];

            renderStaffs();
        }

        function renderStaffs() {
            const staffList = document.getElementById('staffList');

            staffList.replaceChildren();

            if (staffs.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');

                td.colSpan = 5;
                td.className = 'text-center text-muted';
                td.textContent = 'スタッフが登録されていません。';

                tr.appendChild(td);
                staffList.appendChild(tr);

                return;
            }

            staffs.forEach(staff => {
                const tr = document.createElement('tr');

                const idTd = document.createElement('td');
                idTd.textContent = staff.id;

                const nameTd = document.createElement('td');
                nameTd.textContent = staff.name;

                const roleTd = document.createElement('td');
                roleTd.textContent =
                    roleNames[staff.role] || staff.role;

                const storeTd = document.createElement('td');

                const assignedStores = stores
                    .filter(store =>
                        (staff.storeIds || [])
                        .map(Number)
                        .includes(Number(store.id))
                    )
                    .map(store => store.name);

                storeTd.textContent =
                    assignedStores.length > 0 ?
                    assignedStores.join('、') :
                    '未設定';

                const actionTd = document.createElement('td');

                const group = document.createElement('div');
                group.className = 'd-flex flex-wrap gap-2';

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className =
                    'btn btn-sm btn-warning';

                editButton.textContent = '編集';

                editButton.addEventListener('click', () => {
                    editStaff(staff.id);
                });

                const deleteButton = document.createElement('button');

                deleteButton.type = 'button';
                deleteButton.className =
                    'btn btn-sm btn-danger';

                deleteButton.textContent = '削除';

                deleteButton.addEventListener('click', () => {
                    document.getElementById(
                        'deleteStaffId'
                    ).value = staff.id;

                    document.getElementById(
                        'deleteStaffId'
                    ).focus();
                });

                group.append(
                    editButton,
                    deleteButton
                );

                actionTd.appendChild(group);

                tr.append(
                    idTd,
                    nameTd,
                    roleTd,
                    storeTd,
                    actionTd
                );

                staffList.appendChild(tr);
            });
        }

        function renderStoreCheckboxes(selectedStoreIds = []) {
            const container = document.getElementById('storeCheckboxes');

            container.replaceChildren();

            const selected = selectedStoreIds.map(Number);

            if (stores.length === 0) {
                const p = document.createElement('p');

                p.className = 'text-muted mb-0';
                p.textContent = '店舗が登録されていません。';

                container.appendChild(p);
                return;
            }

            stores.forEach(store => {
                const wrapper = document.createElement('div');

                wrapper.className = 'form-check';

                const checkbox = document.createElement('input');

                checkbox.type = 'checkbox';
                checkbox.className =
                    'form-check-input store-checkbox';

                checkbox.id = `store-${store.id}`;
                checkbox.value = store.id;

                checkbox.checked =
                    selected.includes(Number(store.id));

                const label = document.createElement('label');

                label.className = 'form-check-label';
                label.htmlFor = checkbox.id;
                label.textContent = store.name;

                wrapper.append(
                    checkbox,
                    label
                );

                container.appendChild(wrapper);
            });
        }

        function getSelectedStoreIds() {
            return Array.from(
                document.querySelectorAll(
                    '.store-checkbox:checked'
                )
            ).map(item => Number(item.value));
        }

        function setPasswordRequirement(isEditing) {
            const passwordInput =
                document.getElementById('staffPassword');

            const requiredMark =
                document.getElementById(
                    'passwordRequiredMark'
                );

            const passwordHelp =
                document.getElementById('passwordHelp');

            passwordInput.required = !isEditing;

            requiredMark.classList.toggle(
                'd-none',
                isEditing
            );

            passwordHelp.textContent = isEditing ?
                'パスワードを変更しない場合は空欄にしてください。' :
                '新規登録時はパスワードが必須です。';
        }

        function resetForm() {
            document.getElementById('staffForm').reset();

            document.getElementById(
                'originalStaffId'
            ).value = '';

            document.getElementById(
                'formTitle'
            ).textContent = 'スタッフ登録';

            document.getElementById(
                'saveStaffBtn'
            ).textContent = '登録する';

            setPasswordRequirement(false);

            renderStoreCheckboxes();
        }

        function editStaff(staffId) {
            const staff = staffs.find(
                item => Number(item.id) === Number(staffId)
            );

            if (!staff) {
                renderResult({
                    success: false,
                    message: 'スタッフが見つかりません。'
                });
                return;
            }

            document.getElementById(
                'originalStaffId'
            ).value = staff.id;

            document.getElementById(
                'staffName'
            ).value = staff.name ?? '';

            document.getElementById(
                'staffPassword'
            ).value = '';

            const roleRadio = document.querySelector(
                `input[name="role"][value="${staff.role}"]`
            );

            if (roleRadio) {
                roleRadio.checked = true;
            }

            renderStoreCheckboxes(
                staff.storeIds || []
            );

            document.getElementById(
                'formTitle'
            ).textContent = 'スタッフ編集';

            document.getElementById(
                'saveStaffBtn'
            ).textContent = '更新する';

            setPasswordRequirement(true);

            document.getElementById(
                'staffFormSection'
            ).scrollIntoView({
                behavior: 'smooth'
            });
        }

        document.getElementById(
            'newStaffBtn'
        ).addEventListener('click', () => {
            resetForm();

            document.getElementById(
                'staffFormSection'
            ).scrollIntoView({
                behavior: 'smooth'
            });
        });

        document.getElementById(
            'cancelBtn'
        ).addEventListener(
            'click',
            resetForm
        );

        document.getElementById(
            'staffForm'
        ).addEventListener(
            'submit',
            async event => {

                event.preventDefault();

                const originalStaffId =
                    document.getElementById(
                        'originalStaffId'
                    ).value;

                const name =
                    document.getElementById(
                        'staffName'
                    ).value.trim();

                const password =
                    document.getElementById(
                        'staffPassword'
                    ).value;

                const role =
                    document.querySelector(
                        'input[name="role"]:checked'
                    )?.value;

                const storeIds =
                    getSelectedStoreIds();

                const isEditing =
                    originalStaffId !== '';

                if (!name || !role) {
                    renderResult({
                        success: false,
                        message: '必須項目を入力してください。'
                    });
                    return;
                }

                if (
                    !isEditing &&
                    password.trim() === ''
                ) {
                    renderResult({
                        success: false,
                        message: 'パスワードを入力してください。'
                    });
                    return;
                }

                const requestData = {
                    name,
                    role,
                    storeIds
                };

                if (isEditing) {
                    requestData.staffId =
                        Number(originalStaffId);
                }

                if (password.trim() !== '') {
                    requestData.password =
                        password;
                }

                const saveButton =
                    document.getElementById(
                        'saveStaffBtn'
                    );

                saveButton.disabled = true;

                try {

                    const result =
                        await postJson(
                            STAFF_API,
                            requestData
                        );

                    renderResult({
                        success: true,
                        message: result.message ||
                            (
                                isEditing ?
                                'スタッフ情報を更新しました。' :
                                'スタッフを登録しました。'
                            )
                    });

                    await loadStaffs();

                    resetForm();

                } catch (error) {

                    renderResult({
                        success: false,
                        message: error.message ||
                            '保存に失敗しました。'
                    });

                } finally {
                    saveButton.disabled = false;
                }
            }
        );

        document.getElementById(
            'deleteForm'
        ).addEventListener(
            'submit',
            async event => {

                event.preventDefault();

                const staffId =
                    document.getElementById(
                        'deleteStaffId'
                    ).value.trim();

                if (!staffId) {
                    renderResult({
                        success: false,
                        message: '削除対象を選択してください。'
                    });
                    return;
                }

                const staff = staffs.find(
                    item =>
                    Number(item.id) ===
                    Number(staffId)
                );

                if (
                    staff &&
                    !confirm(
                        `スタッフ「${staff.name}」を削除しますか？`
                    )
                ) {
                    return;
                }

                const deleteButton =
                    document.getElementById(
                        'deleteStaffBtn'
                    );

                deleteButton.disabled = true;

                try {

                    const result =
                        await postJson(
                            STAFF_DELETE_API, {
                                staffId: Number(staffId)
                            }
                        );

                    renderResult({
                        success: true,
                        message: result.message ||
                            'スタッフを削除しました。'
                    });

                    document.getElementById(
                        'deleteForm'
                    ).reset();

                    if (
                        document.getElementById(
                            'originalStaffId'
                        ).value === staffId
                    ) {
                        resetForm();
                    }

                    await loadStaffs();

                } catch (error) {

                    renderResult({
                        success: false,
                        message: error.message ||
                            '削除に失敗しました。'
                    });

                } finally {
                    deleteButton.disabled = false;
                }
            }
        );

        window.addEventListener(
            'load',
            async () => {

                try {

                    await waitForCsrfToken();

                    await loadStores();
                    await loadStaffs();

                    setPasswordRequirement(false);

                } catch (error) {

                    renderResult({
                        success: false,
                        message: error.message ||
                            '初期表示に失敗しました。'
                    });

                    console.error(error);
                }
            }
        );
    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>

</body>

</html>