<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
Yii::$app->loadViewComponent('poster/app-style-multi-map');
?>
<style>
    .app-style-two .goods-image {
        height: 750px;
        width: 750px;
        overflow: hidden;
    }

    .app-style-two .end-bg {
        background-image: url("statics/img/mall/poster/icon/style-two-end.png");
        background-repeat: no-repeat;
        background-size: 100% 100%;
        height: 600px;
        width: 702px;
        position: relative;
        left: 24px;
        top: -40px;
        padding: 0 24px;
    }

    .app-style-two .end-bg .goods-name {
        font-size: 34px;
        color: #353535;
        padding-top: 40px;
    }

    .app-style-two .end-bg .price {
        padding-top: 50px;
        color: #ff4544;
    }

    .app-style-two .user {
        margin-top: 96px;
        margin-bottom: 15px;
    }

    .app-style-two .user img {
        height: 96px;
        width: 96px;
        border-radius: 50%;
        margin-left: 24px;
        display: block;
    }

    .app-style-two .user div {
        color: #353535;
        margin-left: 26px;
    }

    .app-style-two .remark {
        height: 52px;
        width: 100%;
        padding: 0 24px;
        color: #353535;
        background-color: #f1f1f1;
        font-size: 24px;
        border-radius: 30px;
        margin-bottom: 70px;
    }

    .app-style-two .remark img {
        height: 17px;
        width: 15px;
        margin-left: 10px;
        display: block;
    }

    .app-style-two .qrcode {
        height: 230px;
        width: 230px;
        display: block;
        margin-left: auto;
    }
</style>
<template id="app-style-two">
    <div class="app-style-two">
        <div class="goods-image">
            <app-style-multi-map v-model="typesetting"></app-style-multi-map>
        </div>
        <div class="end-bg">
            <div class="goods-name">商品名称|商品名称</div>
            <div class="price" flex="dir:left cross:bottom">
                <div style="font-size:32px;line-height: 1">￥</div>
                <div style="font-size:56px;line-height: 1">160</div>
            </div>
            <div flex="dir:left cross:center" style="margin-top: 90px">
                <div flex="dir:top">
                    <div class="user" flex="dir:left cross:center">
                        <img src="statics/img/mall/poster/default_head.png"></img>
                        <div>用户昵称</div>
                    </div>
                    <div class="remark" flex="dir-left main:center cross:center">
                        <div>长按识别小程序码进入</div>
                        <img src="statics/img/mall/poster/icon/three-arrow.png" alt="">
                    </div>
                </div>
                <img class="qrcode" src="statics/img/mall/poster/default_qr_code.png" alt="">
            </div>
        </div>
    </div>
</template>

<script>
    Vue.component('app-style-two', {
        template: '#app-style-two',
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
