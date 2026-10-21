<!DOCTYPE html>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="乙成隼士,松本浩成,永島愛貴,塩川海響,星川誠弥,中島悠翔">

    <link rel="icon" href="/images/CFMS.ico" sizes="any" />
    <link rel="icon" href="/images/CFMS.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="/images/CFMS.png" />
    <meta name="keywords" content="nkc,名古屋工学院専門学校,文化祭">

    <!-- OGPタグ -->
    <meta property="og:title" content="サンプルサイト" /> <!-- TODO: サイトタイトルを変更する -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://nkc.sanae.dev" />
    <meta property="og:image" content="/images/CFMS.png" />
    <meta property="og:image:alt" content="" />
    <meta property="og:description" content="サンプルサイトの説明" /> <!-- TODO: サイト説明を変更する -->

    <!-- タイトル・説明分 -->
    <title>Templateページ</title> <!-- TODO: ページタイトルを変更する -->
    <meta name="description" content="templateページの説明"> <!-- TODO: ページ説明を変更する -->


    <!-- bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <!-- csrfトークンの設定 -->
    <script src="/lib/js/fetch_csrf_token.js"></script>
</head>

<body>
    <!-- メインコンテンツ -->
    <main>
        <div class="container">
            <h1>Templateページ</h1> <!-- TODO: ページタイトルを変更する -->
            <p>ここにコンテンツを追加してください。</p> <!-- TODO: コンテンツを追加する -->
        </div>

        <!-- サンプルフォーム -->
        <div class="container mt-4">
            <h2>サンプルフォーム</h2>
            <form id="sampleForm">
                <!-- CSRFトークンを埋め込むためのhiddenフィールド -->
                <input type="hidden" class="csrf_token" name="csrf_token" value="">

                <div class="mb-3">
                    <label for="message" class="form-label">メッセージ</label>
                    <input type="text" class="form-control" id="message" name="message" required>
                </div>
                <div class="mb-3">
                    <label for="age" class="form-label">年齢</label>
                    <input type="number" class="form-control" id="age" name="age" required>
                </div>
                <button type="submit" class="btn btn-primary">送信</button>
            </form>
            <div id="result" class="mt-3"></div>
        </div>
    </main>

    <!-- サンプルフォームの送信処理 -->
    <script>
        function renderResult(data) {
            const resultDiv = document.getElementById('result');
            if (data.success) {
                resultDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                console.log('サーバーからのデータ:', data.data);
            } else {
                resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        }
        document.getElementById('sampleForm').addEventListener('submit', function(event) {
            event.preventDefault(); // フォームのデフォルトの送信を防ぐ

            const message = document.getElementById('message').value;
            const age = document.getElementById('age').value;
            const csrf_token = document.querySelector('.csrf_token').value;

            fetch('<?= getenv('API_URL') ?>/template/template.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message,
                        age,
                        csrf_token
                    })
                })
                .then(response => response.json())
                .then(data => renderResult(data))
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>

    <!-- CSRFトークンの自動埋め込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/fetch_csrf_token.php'; ?>
</body>

</html>