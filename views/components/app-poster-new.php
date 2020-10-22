<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .app-poster-new {

    }

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


        width: 150px;
        del-height: calc(140px + 10px);
        margin: 5px;
        padding: 0 10px;
    }

    .poster-style-box.active {
        border: 1px solid red;
    }

    .poster-style-box > img {
        height: 100px;
        width: 100%;
    }

    .event-step {
        pointer-events: none !important;
    }


    .poster-pic-box {
        cursor: pointer;
        display: inline-block;
        border: 1px solid #E2E2E2;
        border-radius: 3px;

        width: 122px;
        margin: 5px;
        padding: 0 10px;
        /*height: 180px;*/
    }

    .poster-pic-box.active {
        border: 1px solid red;
    }

    .poster-pic-box .grid {
        flex-wrap: wrap;
        height: 100px;
        width: 100%;
        background: lightblue;
        border: 1px solid #00ffff;
    }
</style>
<template id="app-poster-new">
    <div class="app-poster-new">
        <div flex="xdir:left">
            <div class="preview">
                <div class="box">
                    <el-avatar src="https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png"></el-avatar>
                </div>
            </div>
            <div class="select">
                <div style="background: #ffffff;padding: 30px 0;">
                    <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="20%">
                        <el-form-item label="海报样式" prop="poster_style">
                            <el-checkbox-group v-model="ruleForm.poster_style" style="width: 800px">
                                <div @click="changeStyle(`1`)"
                                     :class="[`poster-style-box`, `${ruleForm.poster_style.indexOf(`1`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="1">样式一</el-checkbox>
                                    <img src="http://t.cn/A6zsnUiE" alt=""/>
                                </div>
                                <div @click="changeStyle(`2`)"
                                     :class="[`poster-style-box`, `${ruleForm.poster_style.indexOf(`2`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="2">样式二</el-checkbox>
                                    <img src="http://t.cn/A6zsnUiE" alt=""/>
                                </div>
                                <div @click="changeStyle(`3`)"
                                     :class="[`poster-style-box`, `${ruleForm.poster_style.indexOf(`3`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="3">样式三</el-checkbox>
                                    <img src="http://t.cn/A6zsnUiE" alt=""/>
                                </div>
                                <div @click="changeStyle(`4`)"
                                     :class="[`poster-style-box`, `${ruleForm.poster_style.indexOf(`4`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="4">样式四</el-checkbox>
                                    <img src="http://t.cn/A6zsnUiE" alt=""/>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label="商品图数量" prop="image_style">
                            <el-checkbox-group v-model="ruleForm.image_style" style="width: 500px">
                                <div @click="changePic(`1`)"
                                     :class="[`poster-pic-box`, `${ruleForm.image_style.indexOf(`1`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="1">一张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width: 100%"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`2`)"
                                     :class="[`poster-pic-box`, `${ruleForm.image_style.indexOf(`2`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="2">二张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:100%;border-bottom: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width:100%;"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`3`)"
                                     :class="[`poster-pic-box`, `${ruleForm.image_style.indexOf(`3`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="3">三张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:100%;border-bottom: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width: 50%;border-right: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width: 50%"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`4`)"
                                     :class="[`poster-pic-box`, `${ruleForm.image_style.indexOf(`4`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="4">四张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:50%;border-right: 1px solid #00ffff;border-bottom: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width:50%;border-bottom: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width:50%;border-right: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width:50%"></div>
                                    </div>
                                </div>
                                <div @click="changePic(`5`)"
                                     :class="[`poster-pic-box`, `${ruleForm.image_style.indexOf(`5`) === -1 ? ``:`active`}`]">
                                    <el-checkbox class="event-step" label="5">五张</el-checkbox>
                                    <div flex="dir:left" class="grid">
                                        <div style="height: 50%;width:50%;border-right: 1px solid #00ffff;border-bottom:1px solid #00ffff"></div>
                                        <div style="height: 33.3%;width: 50%;border-bottom: 1px solid #00ffff"></div>
                                        <div style="height: 50%;width: 50%;border-right: 1px solid #00ffff"></div>
                                        <div style="height: 16.6%;width: 50%;border-bottom: 1px solid #00ffff"></div>
                                        <div style="height: 33.3%;width: 50%"></div>
                                    </div>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-form>
                </div>
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    Vue.component('app-poster-new', {
        template: '#app-poster-new',
        props: {
            value: Object,
            default() {
                return {
                    poster_style: [],
                    image_style: [],
                }
            }
        },
        data() {
            return {
                ruleForm: this.value,
                rule_form: {},
                rules: {},
            };
        },


        methods: {
            changeStyle(style) {
                const index = this.ruleForm.poster_style.indexOf(style);
                if (index === -1) {
                    this.ruleForm.poster_style.splice(index, 0, style);
                } else {
                    this.ruleForm.poster_style.splice(index, 1)
                }
            },
            changePic(style) {
                const index = this.ruleForm.image_style.indexOf(style);
                if (index === -1) {
                    this.ruleForm.image_style.splice(index, 0, style);
                } else {
                    this.ruleForm.image_style.splice(index, 1)
                }
            }
        },
    });
</script>
