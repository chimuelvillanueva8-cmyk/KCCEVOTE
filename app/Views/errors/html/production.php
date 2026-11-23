<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">

    <title><?= lang('Errors.whoops') ?></title>

    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
    </style>
</head>
<body>

    <div class="container text-center">

        <h1 class="headline"><?= lang('Errors.whoops') ?></h1>

        <p class="lead"><?= lang('Errors.weHitASnag') ?></p>

        <?php if (isset($exception)): ?>
        <div style="margin: 30px auto; max-width: 700px; text-align: left; background: #fff3cd; color: #856404; padding: 20px; border-radius: 8px; border: 1px solid #ffeeba; font-family: monospace; font-size: 13px; line-height: 1.6; word-break: break-all;">
            <div style="font-weight: bold; font-size: 15px; margin-bottom: 8px; color: #721c24;">Application Diagnostic:</div>
            <strong>Type:</strong> <?= esc(get_class($exception)) ?><br>
            <strong>Message:</strong> <?= esc($exception->getMessage()) ?><br>
            <strong>File:</strong> <?= esc($exception->getFile()) ?> : <?= (int) $exception->getLine() ?><br>
        </div>
        <?php endif; ?>

    </div>

</body>

</html>
