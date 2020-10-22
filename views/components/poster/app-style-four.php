<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
Yii::$app->loadViewComponent('poster/app-style-multi-map');
?>
<style>
    .app-style-four .user {
        margin-top: 35px;
        margin-bottom: 35px;
    }

    .app-style-four .user img {
        height: 90px;
        width: 90px;
        border-radius: 50%;
        margin-left: 24px;
        display: block;
    }

    .app-style-four .user div {
        background: #f1f1f1;
        padding: 0 24px;
        height: 54px;
        color: #4b4b4b;
        margin-left: 24px;
        border-radius: 30px;
    }

    .app-style-four .goods-image {
        height: 650px;
        width: 650px;
        margin-top: 60px;
    }

    .app-style-four .goods-name {
        font-size: 34px;
        padding-top: 26px;
        color: #353535;
    }

    .app-style-four .price {
        padding-top: 60px;
        color: #ff4544;
    }

    .app-style-four .qrcode img {
        height: 150px;
        width: 150px;
        margin-top: 25px;
    }

    .app-style-four .qrcode div {
        margin-top: 22px;
        margin-bottom: 26px;
        font-size: 24px;
        color: #999999;
    }

    .app-style-four .four-box {
        height: 1150px;
        background-color: #FFFFFF;
        margin: 0 auto;
        width: 702px;
        border-radius: 16px;
        padding: 0 24px;
    }
</style>
<template id="app-style-four">
    <div class="app-style-four">
        <div class="user" flex="dir:left cross:center">
            <img src="statics/img/mall/poster/default_head.png"></img>
            <div flex="dir:left cross:center main-center">
                用户昵称向您推荐一个好物
            </div>
        </div>
        <div class="four-box">
            <div class="goods-name">商品名称|商品名称</div>
            <div class="price" flex="dir:left cross:bottom">
                <div style="font-size:32px;line-height: 1">￥</div>
                <div style="font-size:56px;line-height: 1">160</div>
            </div>
            <div class="goods-image">
                <app-style-multi-map v-model="typesetting"></app-style-multi-map>
            </div>
            <div class="qrcode" flex="cross:center dir:top">
                <img src="statics/img/mall/poster/default_qr_code.png" alt="">
                <div>长按识别小程序码进入</div>
            </div>
        </div>
    </div>
</template>

<script>
    Vue.component('app-style-four', {
        template: '#app-style-four',
        props: {
            typesetting: {
                type: Number,
                default: 3
            },
        },
        data() {
            return {};
        },
        methods: {},
    });
</script>
