<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
Yii::$app->loadViewComponent('poster/app-style-multi-map');
?>
<style>
    .app-style-one .user {
        margin-top: 96px;
        margin-bottom: 64px;
    }

    .app-style-one .user img {
        height: 90px;
        width: 90px;
        border-radius: 50%;
        margin-left: 24px;
        display: block;
    }

    .app-style-one .user div {
        background: #f1f1f1;
        padding: 0 24px;
        height: 54px;
        color: #4b4b4b;
        margin-left: 24px;
        border-radius: 30px;
    }

    .app-style-one .goods-image {
        margin: 0 auto;
        height: 702px;
        width: 702px;
        overflow: hidden;
        border-radius: 16px 16px 0 0;
    }

    .app-style-one .style-bg {
        height: 310px;
        margin: 0 auto;
        width: 702px;
        padding: 0 28px;
        background: #ffffff;
        border-radius: 0 0 16px 16px;
    }

    .app-style-one .style-bg .goods-name {
        padding: 28px 0;
        font-size: 34px;
        color: #353535;
    }

    .app-style-one .style-bg .price {
        padding-top: 50px;
        color: #ff4544;
    }

    .app-style-one .style-bg .remark {
        margin-top: 28px;
        color: #999999;
        font-size: 28px;
    }

    .app-style-one .style-bg img {
        height: 230px;
        width: 230px;
        margin-left: auto;
        display: block;
        border-radius: 50%;
    }
</style>
<template id="app-style-one">
    <div class="app-style-one">
        <div class="user" flex="dir:left cross:center">
            <img src="statics/img/mall/poster/default_head.png"></img>
            <div flex="dir:left cross:center main-center">
                用户昵称向您推荐一个好物
            </div>
        </div>
        <div class="goods-image">
            <app-style-multi-map v-model="typesetting"></app-style-multi-map>
        </div>
        <div class="style-bg" flex="dir:left cross:center">
            <div flex="dir:top main:center">
                <div class="goods-name">商品名称|商品名称</div>
                <div class="price" flex="dir:left cross:bottom">
                    <div style="font-size:32px;line-height: 1">￥</div>
                    <div style="font-size:56px;line-height: 1">160</div>
                </div>
                <div class="remark">长按识别小程序码进入</div>
            </div>
            <img src="statics/img/mall/poster/default_qr_code.png" alt="">
        </div>
    </div>
</template>
<script>
    Vue.component('app-style-one', {
        template: '#app-style-one',
        props: {
            typesetting: {
                type: Number,
                default: 1
            },
        },
        data() {
            return {};
        },
        methods: {},
    });
</script>
