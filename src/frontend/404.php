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
    <meta property="og:title" content="404 - ページが見つかりません" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://nkc.sanae.dev" />
    <meta property="og:image" content="/images/CFMS.png" />
    <meta property="og:image:alt" content="CFMS" />
    <meta property="og:description" content="404エラーページの説明" />

    <!-- タイトル・説明分 -->
    <title>404 - ページが見つかりません</title>
    <meta name="description" content="404エラーページの説明">

    <!-- bootstrap読み込み -->
    <link rel="stylesheet" href="/lib/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <script src="/lib/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- メインコンテンツ -->
    <main class="py-5 px-2 text-center">
        <div class="container">
            <h1>お探しのページは見つかりませんでした。</h1>
            <p>申し訳ありませんが、指定されたURLのページは存在しないか、削除された可能性があります。</p>
            <a href="/user/user_main.php" class="btn btn-primary">トップページに戻る</a>
            <a href="/staff/staff_login.php" class="btn btn-secondary">スタッフページに戻る</a>
        </div>
    </main>

    <!-- フッター読み込み -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>
</body>

</html>