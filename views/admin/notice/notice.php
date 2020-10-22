<?php

defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('admin/app-notice');

?>
<div id="app" v-cloak>
    <app-notice></app-notice>
</div>
<script>
    const app = new Vue({
        el: '#app',
    })
</script>
