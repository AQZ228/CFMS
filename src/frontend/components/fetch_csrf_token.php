<script src="/lib/js/fetch_csrf_token.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", async () => {
        await autoInjectCsrfToken("<?= getenv('API_URL') ?>");
    });
</script>