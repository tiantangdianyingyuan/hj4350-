<?php

Yii::$app->loadViewComponent("app-edit", __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template');
?>
<div id="app" v-cloak>
    <app-edit type="module" request-url="plugin/diy/mall/module/edit"></app-edit>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {};
        },
    });
</script>