<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
Yii::$app->loadViewComponent('poster/app-style-one');
Yii::$app->loadViewComponent('poster/app-style-two');
Yii::$app->loadViewComponent('poster/app-style-three');
Yii::$app->loadViewComponent('poster/app-style-four');

?>
<style>
    .app-poster-new .preview {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }

    .app-poster-new .preview .box {
        position: relative;
        border: 2px solid #e2e3e3;
        width: 750px;
        height: 1334px;
        zoom: 0.5;
        overflow: hidden;
    }

    .app-poster-new .select {
        width: 100%;
        height: 100%;
    }

    .poster-style-box {
        cursor: pointer;
        display: inline-block;
        border: 1px solid #E2E2E2;
        border-radius: 3px;

        height: 245px;
        width: 160px;
        margin: 5px;
        padding: 0 5px;
    }

    .poster-style-box.active {
        border: 1px solid #409eff;
    }

    .poster-style-box > img {
        height: 200px;
        width: 150px;
    }

    .event-step {
        margin-left: 5px;
        pointer-events: none !important;
    }

    .poster-pic-box {
        cursor: pointer;
        display: inline-block;
        border: 1px solid #E2E2E2;
        border-radius: 3px;

        height: 147px;
        width: 112px;
        margin: 5px;
        padding: 0 5px;
    }

    .poster-pic-box.active {
        border: 1px solid #409eff;
    }

    .poster-pic-box .grid {
        flex-wrap: wrap;
        height: 100px;
        width: 100%;
        background: #E6F4FF;
        border: 1px solid #b8b8b8;
    }

    .bottom-div {
        border-top: 1px solid #E3E3E3;
        position: fixed;
        bottom: 0;
        background-color: #ffffff;
        z-index: 999;
        padding: 10px;
        width: 62.1%;
    }
</style>
<template id="app-poster-new">
    <div class="app-poster-new">
        <div flex="dir:left">
            <div class="preview">
                <div class="box">
                    <span v-if="show_style == 1">
                        <app-style-one :typesetting="show_typesetting"></app-style-one>
                    </span>
                    <span v-if="show_style == 2">
                        <app-style-two :typesetting="show_typesetting"></app-style-two>
                    </span>
                    <span v-if="show_style == 3">
                        <app-style-three :typesetting="show_typesetting"></app-style-three>
                    </span>
                    <span v-if="show_style == 4">
                        <app-style-four :typesetting="show_typesetting"></app-style-four>
                    </span>
                </div>
            </div>
            <div class="select">
                <div style="background: #ffffff;padding: 30px 0 50px 0;">
                    <el-form :model="value" :rules="rules" size="small" ref="value" label-width="15%">
                        <el-form-item label="海报样式" prop="poster_style">
                            <el-checkbox-group v-model="value.poster_style" style="max-width: 500px">
                                <div @click="changeStyle(`1`)"
                                     :class="[`poster-style-box`, `${value.poster_style.indexOf(`1`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="1">样式一</el-checkbox>
                                    <img src="statics/img/mall/poster/admin/1.png" alt=""/>
                                </div>
                                <div @click="changeStyle(`2`)"
                                     :class="[`poster-style-box`, `${value.poster_style.indexOf(`2`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="2">样式二</el-checkbox>
                                    <img src="statics/img/mall/poster/admin/2.png" alt=""/>
                                </div>
                                <div @click="changeStyle(`3`)"
                                     :class="[`poster-style-box`, `${value.poster_style.indexOf(`3`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="3">样式三</el-checkbox>
                                    <img src="statics/img/mall/poster/admin/3.png" alt=""/>
                                </div>
                                <div @click="changeStyle(`4`)"
                                     :class="[`poster-style-box`, `${value.poster_style.indexOf(`4`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="4">样式四</el-checkbox>
                                    <img src="statics/img/mall/poster/admin/4.png" alt=""/>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label="商品图数量" prop="image_style">
                            <el-checkbox-group v-model="value.image_style" style="width: 450px">
                                <div @click="changePic(`1`)"
                                     :class="[`poster-pic-box`, `${value.image_style.indexOf(`1`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="1">一张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width: 100%"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`2`)"
                                     :class="[`poster-pic-box`, `${value.image_style.indexOf(`2`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="2">二张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:100%;border-bottom: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width:100%;"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`3`)"
                                     :class="[`poster-pic-box`, `${value.image_style.indexOf(`3`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="3">三张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:100%;border-bottom: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width: 50%;border-right: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width: 50%"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`4`)"
                                     :class="[`poster-pic-box`, `${value.image_style.indexOf(`4`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="4">四张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:50%;border-right: 1px solid #b8b8b8;border-bottom: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width:50%;border-bottom: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width:50%;border-right: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width:50%"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`5`)"
                                     :class="[`poster-pic-box`, `${value.image_style.indexOf(`5`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="5">五张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:50%;border-right: 1px solid #b8b8b8;border-bottom:1px solid #b8b8b8"></div>
                                        <div style="height: 33.3%;width: 50%;border-bottom: 1px solid #b8b8b8"></div>
                                        <div style="height: 50%;width: 50%;border-right: 1px solid #b8b8b8"></div>
                                        <div style="height: 16.6%;width: 50%;border-bottom: 1px solid #b8b8b8"></div>
                                        <div style="height: 33.3%;width: 50%"></div>
                                    </div>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                        <slot name="other"></slot>
                    </el-form>
                    <div class="bottom-div" flex="cross:center">
                        <slot></slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    Vue.component('app-poster-new', {
        template: '#app-poster-new',
        props: {
            value: Object,
        },
        data() {
            return {
                rules: {
                    poster_style: [
                        {
                            required: true, type: 'array', validator: (rule, value, callback) => {
                                if (this.value.poster_style instanceof Array && this.value.poster_style.length > 0) {
                                    callback();
                                }
                                callback('至少选择一个海报样式');
                            }
                        }
                    ],
                    image_style: [
                        {
                            required: true, type: 'array', validator: (rule, value, callback) => {
                                if (this.value.image_style instanceof Array && this.value.image_style.length > 0) {
                                    callback();
                                }
                                callback('至少选择一个商品图数量');
                            }
                        }
                    ],
                },
                show_typesetting: 1,
                show_style: 1,
            };
        },
        methods: {
            changeStyle(style) {
                const index = this.value.poster_style.indexOf(style);
                if (index === -1) {
                    this.value.poster_style.splice(index, 0, style);
                } else {
                    this.value.poster_style.splice(index, 1)
                }
                this.show_style = parseInt(style);
            },
            changePic(style) {
                const index = this.value.image_style.indexOf(style);
                if (index === -1) {
                    this.value.image_style.splice(index, 0, style);
                } else {
                    this.value.image_style.splice(index, 1)
                }
                this.show_typesetting = parseInt(style);
            }
        },
    });
</script>
