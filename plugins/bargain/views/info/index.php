<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-info', __DIR__)
?>
<div id="app" v-cloak="">
    <app-info type="all"></app-info>
</div>
<script>
    const app = new Vue({
        el: '#app'
    });
</script>
