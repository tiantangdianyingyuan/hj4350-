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
        :tabs="tabs"
        goods_url="plugin/gift/mall/goods/index"
        edit_goods_url='plugin/gift/mall/goods/edit'
        edit_goods_status_url='plugin/gift/mall/goods/switch-status'>
    </app-goods-list>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                tabs: [
                    {
                        name: '全部',
                        value: '-1'
                    },
                    {
                        name: '销售中',
                        value: '1'
                    },
                    {
                        name: '下架中',
                        value: '0'
                    },
                    {
                        name: '售罄',
                        value: '2'
                    },
                ]
            }
        },
        methods: {}
    });
</script>
