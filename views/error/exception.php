<?php

/** @var \app\core\ErrorHandler $handler */

$result = $handler->getResult();
?>
<div class="title" style=""><?= $result['title'] ?></div>
<hr>
<?php foreach ($result['list'] as $item) : ?>
    <div class="line"><?= $item ?></div>
<?php endforeach; ?>
<hr>
<div class="line">Version: <?= app_version() ?></div>
<style>
body {
    margin: 0;
    padding: 20px;
    font-family: "Courier New";
}

hr {
    height: 0;
    border: 1px solid #e3e3e3;
    border-width: 0 0 1px 0;
    margin: 10px 0;
}

.title {
    color: #ff4544;
    font-weight: bold;
    font-size: 16px;
}

.line {
    color: #423636;
    font-size: 14px;
    margin: 2px 0;
}
</style>
