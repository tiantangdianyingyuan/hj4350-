<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

?>
<style>
    .app-style-multi-map {
        height: 100%;
        width: 100%;
        overflow: hidden;
    }

    .app-style-multi-map > div {
        flex-wrap: wrap;
    }

    .app-style-multi-map .el-image {
        display: block;
    }

    .app-style-multi-map .goods-one, .goods-two, .goods-three, .goods-four {
        height: 100%;
        width: 100%;
        position: relative;
    }

    .app-style-multi-map .goods-one .el-image {
        height: 100%;
        width: 100%;
    }

    .app-style-multi-map .goods-two .el-image:nth-child(1) {
        height: 50%;
        width: 100%;
    }

    .app-style-multi-map .goods-two .el-image:nth-child(2) {
        height: 50%;
        width: 100%;
    }

    .app-style-multi-map .goods-three .el-image:nth-child(1) {
        height: 50%;
        width: 100%;
    }

    .app-style-multi-map .goods-three .el-image:nth-child(2) {
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-three .el-image:nth-child(3) {
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-four .el-image:nth-child(1) {
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-four .el-image:nth-child(2) {
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-four .el-image:nth-child(3) {
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-four .el-image:nth-child(4) {
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-five {
        height: 100%;
        width: 100%;
        position: relative;
    }

    .app-style-multi-map .goods-five .el-image:nth-child(1) {
        position: absolute;
        top: 0;
        left: 0;
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-five .el-image:nth-child(2) {
        position: absolute;
        top: 50%;
        left: 0;
        height: 50%;
        width: 50%;
    }

    .app-style-multi-map .goods-five .el-image:nth-child(3) {
        position: absolute;
        top: 0;
        left: 50%;
        height: 33.333%;
        width: 50%;
    }

    .app-style-multi-map .goods-five .el-image:nth-child(4) {
        position: absolute;
        top: 33.33%;
        left: 50%;
        height: 33.333%;
        width: 50%;
    }

    .app-style-multi-map .goods-five .el-image:nth-child(5) {
        position: absolute;
        top: 66.66%;
        left: 50%;
        height: 33.333%;
        width: 50%;
    }
</style>
<template id="app-style-multi-map">
    <div class="app-style-multi-map">
        <div v-if="value ==1" class="goods-one" flex="dir:left">
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
        </div>
        <div v-if="value ==2" class="goods-two" flex="dir:left">
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
        </div>
        <div v-if="value ==3" class="goods-three" flex="dir:left">
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
        </div>
        <div v-if="value ==4" class="goods-four" flex="dir:left">
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
        </div>
        <div v-if="value ==5" class="goods-five" flex="dir:left">
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
            <el-image fit="cover" src="statics/img/mall/poster/admin/goods-url.png"></el-image>
        </div>
    </div>
</template>

<script>
    Vue.component('app-style-multi-map', {
        template: '#app-style-multi-map',
        props: {
            value: {
                type: Number,
                default: 1
            },
            sign: String
        },
        data() {
            return {};
        },
        methods: {},
    });
</script>
