<?php

Yii::$app->loadViewComponent("app-edit", __DIR__);
?>
<div id="app" v-cloak>
    <app-edit></app-edit>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {};
        },
    });
</script>