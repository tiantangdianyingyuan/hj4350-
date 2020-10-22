<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-goods-list');
?>
<style>
</style>
<div id="app" v-cloak>
    <app-goods-list
            ref="goodsList"
            :is-show-batch-button="false"
            goods_url="plugin/booking/mall/goods/index"
            edit_goods_url='plugin/booking/mall/goods/edit'
            :is-show-express="false">
    </app-goods-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
    });
</script>
