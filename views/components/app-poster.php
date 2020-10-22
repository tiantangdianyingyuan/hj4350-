<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .poster-mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }

    .poster-bg-box {
        position: relative;
        border: 2px solid #e2e3e3;
        width: 750px;
        height: 1334px;
        zoom: 0.5;
        overflow: hidden;
    }

    .poster-bg-pic {
        width: 100%;
        height: 100%;
        background-size: 100% 100%;
        background-position: center;
    }

    .title {
        padding: 15px 0;
        background-color: #f7f7f7;
        margin-bottom: 10px;
    }

    .component-item {
        width: 100px;
        height: 100px;
        cursor: pointer;
        position: relative;
        padding: 10px 0;
        border: 1px solid #e2e2e2;
        margin-right: 15px;
        margin-top: 15px;
        border-radius: 5px;
    }

    .component-item.active {
        border: 1px solid #7BBDFC;
    }

    .component-item-remove {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
        width: 28px;
        height: 28px;
    }

    .component-attributes-box {
        color: #ff4544;
    }

    .box-card {
        margin-top: 35px;
    }


    .poster-form-body {
        padding: 20px 35% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        width: 100%;
        height: 100%;
        position: relative;
        min-width: 640px;
    }

    .poster-button-item {
        padding: 9px 25px;
        position: absolute !important;
        bottom: -52px;
        left: 0;
    }

    .el-card, .el-tabs__content {
        overflow: visible;
    }

    .poster-del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }
</style>

<template id="app-poster">
    <el-form v-if="rule_form" :model="rule_form" :rules="rules" size="small" ref="rule_form" label-width="20%">
        <div style="display: flex;">
            <div class="poster-mobile-box" flex="dir:top">
                <div class="poster-bg-box">
                    <div class="poster-bg-pic" :style="{'background-image':'url('+rule_form.bg_pic.url+')'}">
                    </div>
                    <el-image
                            v-if="rule_form.pic && rule_form.pic.is_show == 1"
                            :style="{
                                        position: 'absolute',
                                        width: rule_form.pic.width + 'px',
                                        height: rule_form.pic.height + 'px',
                                        top: rule_form.pic.top + 'px',
                                        left: rule_form.pic.left + 'px'}"
                            src="statics/img/mall/poster/default_goods.jpg">
                    </el-image>

                    <el-image v-if="rule_form.head && rule_form.head.is_show == 1"
                               radius="50%"
                               :style="{
                                        position: 'absolute',
                                        width: rule_form.head.size + 'px',
                                        height: rule_form.head.size + 'px',
                                        top: rule_form.head.top + 'px',
                                        left: rule_form.head.left + 'px'}"
                               src="statics/img/mall/poster/default_head.png">
                    </el-image>
                    <el-image v-if="rule_form.qr_code && rule_form.qr_code.is_show == 1"
                               :radius="rule_form.qr_code.type == 1 ? '50%' : '0%'"
                               :style="{
                                         position: 'absolute',
                                         width: rule_form.qr_code.size + 'px',
                                         height: rule_form.qr_code.size + 'px',
                                         top: rule_form.qr_code.top + 'px',
                                         left: rule_form.qr_code.left + 'px'}"
                               src="statics/img/mall/poster/default_qr_code.png">
                    </el-image>

                    <el-image
                            v-if="rule_form.poster_bg && rule_form.poster_bg.is_show == 1"
                            :style="{
                                        position: 'absolute',
                                        width: rule_form.poster_bg.width + 'px',
                                        height: rule_form.poster_bg.height + 'px',
                                        top: rule_form.poster_bg.top + 'px',
                                        left: rule_form.poster_bg.left + 'px'}"
                            :src="rule_form.poster_bg.file_path">
                    </el-image>
                    <el-image v-if="rule_form.poster_bg_two && rule_form.poster_bg_two.is_show == 1"
                            :style="{
                                        position: 'absolute',
                                        width: rule_form.poster_bg_two.width + 'px',
                                        height: rule_form.poster_bg_two.height + 'px',
                                        top: rule_form.poster_bg_two.top + 'px',
                                        left: rule_form.poster_bg_two.left + 'px'}"
                            :src="rule_form.poster_bg_two.file_path">
                    </el-image>

                    <span v-if="rule_form.name && rule_form.name.is_show == 1"
                          :style="{
                                        position: 'absolute',
                                        top: rule_form.name.top + 'px',
                                        left: rule_form.name.left + 'px',
                                        fontSize: rule_form.name.font * 2 + 'px',
                                        color: rule_form.name.color}">
                        {{rule_form.name.text ? rule_form.name.text : '商品名称|商品名称'}}
                    </span>
                    <span v-if="rule_form.price && rule_form.price.is_show == 1"
                          :style="{
                                                    position: 'absolute',
                                                    top: rule_form.price.top + 'px',
                                                    left: rule_form.price.left + 'px',
                                                    fontSize: rule_form.price.font * 2 + 'px',
                                                    color: rule_form.price.color}">
                                        {{rule_form.price.text ? rule_form.price.text : '￥99.00'}}
                    </span>
                    <span v-if="rule_form.desc && rule_form.desc.is_show == 1"
                          v-html="rule_form.desc.text"
                          :style="{
                                                    width: rule_form.desc.width + 'px',
                                                    wordWrap: 'break-word',
                                                    wordBreak: 'normal',
                                                    position: 'absolute',
                                                    top: rule_form.desc.top + 'px',
                                                    left: rule_form.desc.left + 'px',
                                                    fontSize: rule_form.desc.font * 2 + 'px',
                                                    color: rule_form.desc.color}">
                    </span>
                    <slot name="desc-text-slot"></slot>
                    <span v-if="rule_form.nickname && rule_form.nickname.is_show == 1"
                          :style="{
                                                    position: 'absolute',
                                                    top: rule_form.nickname.top + 'px',
                                                    left: rule_form.nickname.left + 'px',
                                                    fontSize: rule_form.nickname.font * 2 + 'px',
                                                    color: rule_form.nickname.color}">
                                          {{rule_form.nickname.text}}
                    </span>

                    <span v-if="rule_form.time_str && rule_form.time_str.is_show == 1"
                          :style="{
                                                    position: 'absolute',
                                                    top: rule_form.time_str.top + 'px',
                                                    left: rule_form.time_str.left + 'px',
                                                    fontSize: rule_form.time_str.font * 2 + 'px',
                                                    color: rule_form.time_str.color}">
                                          {{rule_form.time_str.text ? rule_form.time_str.text : '08.16 14:00场'}}
                    </span>

                </div>
            </div>
            <div class="poster-form-body" flex="dir:top">
                <div flex="dir:left" style="margin-bottom: 15px">
                    <app-attachment :multiple="false" :max="1"
                                    v-model="rule_form.bg_pic.url">
                        <el-tooltip class="item"
                                    effect="dark"
                                    content="建议尺寸:750 * 1334"
                                    placement="top">
                            <el-button size="mini">
                                {{rule_form.bg_pic.url ? '更换背景图' : '添加背景图'}}
                            </el-button>
                        </el-tooltip>
                    </app-attachment>
                    <slot name="more-button">
                    </slot >
                    <el-button v-if="rule_form.bg_pic.url" @click="removeBgPic()"
                               style="margin-left: 10px;"
                               type="danger"
                               size="mini">
                        删除背景
                    </el-button>
                </div>
                <div flex="wrap:wrap">
                    <div v-for="(item,index) in goods_component"
                         @click="componentItemClick(index)"
                         class="component-item"
                         :class="goods_component_key == item.key ? 'active' : ''"
                         flex="dir:top cross:center main:center">
                        <img :src="item.icon_url">
                        <div>{{item.title}}</div>
                        <img v-if="test2(index)"
                             @click.stop="componentItemRemove(index)"
                             class="component-item-remove"
                             src="statics/img/mall/poster/icon_delete.png">
                    </div>
                </div>
                <el-card shadow="never" class="box-card" style="width: 100%;">
                    <div slot="header">
                        <span>{{title_desc}}设置</span>
                    </div>
                    <div>
                        <template v-if="goods_component_key == 'head'">
                            <el-form-item label="大小">
                                <el-slider
                                        :min=40
                                        :max=300
                                        v-model="rule_form.head.size"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.head.size)
                                        v-model="rule_form.head.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.head.size)
                                        v-model="rule_form.head.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'nickname'">
                            <el-form-item label="大小">
                                <el-slider
                                        :min=12
                                        :max=40
                                        v-model="rule_form.nickname.font"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.nickname.font)
                                        v-model="rule_form.nickname.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <slot name="step_poster_center"></slot>

                            <el-form-item label="左间距" v-if="rule_form.nickname.left == 0 || rule_form.nickname.left">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.nickname.font)
                                        v-model="rule_form.nickname.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="颜色">
                                <el-color-picker
                                        style="margin-left: 20px;"
                                        color-format="rgb"
                                        v-model="rule_form.nickname.color"
                                        :predefine="predefineColors">
                                </el-color-picker>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'name'">
                            <el-form-item label="大小">
                                <el-slider
                                        :min=12
                                        :max=30
                                        v-model="rule_form.name.font"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.name.font)
                                        v-model="rule_form.name.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.name.font)
                                        v-model="rule_form.name.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="颜色">
                                <el-color-picker
                                        style="margin-left: 20px;"
                                        color-format="rgb"
                                        v-model="rule_form.name.color"
                                        :predefine="predefineColors">
                                </el-color-picker>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'poster_bg_two'">
                            <el-form-item label="宽度">
                                <el-slider
                                        :min=0
                                        :max=750
                                        v-model="rule_form.poster_bg_two.width"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="高度">
                                <el-slider
                                        :min=0
                                        :max=1334
                                        v-model="rule_form.poster_bg_two.height"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.poster_bg_two.height)
                                        v-model="rule_form.poster_bg_two.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.poster_bg_two.width)
                                        v-model="rule_form.poster_bg_two.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>

                            <el-form-item label="选择图片">
                                <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                                @selected="posterBgTwoPic">
                                    <el-button size="mini">选择图标</el-button>
                                </app-attachment>
                                <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                    <app-attachment :multiple="false" :max="1"
                                                    @selected="posterBgTwoPic">
                                        <app-image mode="aspectFill"
                                                   width="80px"
                                                   height='80px'
                                                   :src="rule_form.poster_bg_two.file_path">
                                        </app-image>
                                    </app-attachment>
                                </div>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'price'">
                            <el-form-item label="大小">
                                <el-slider
                                        :min=12
                                        :max=30
                                        v-model="rule_form.price.font"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.price.font)
                                        v-model="rule_form.price.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.price.font)
                                        v-model="rule_form.price.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="颜色">
                                <el-color-picker
                                        style="margin-left: 20px;"
                                        color-format="rgb"
                                        v-model="rule_form.price.color"
                                        :predefine="predefineColors">
                                </el-color-picker>
                            </el-form-item>
                            <el-form-item label="删除线" v-if="rule_form.price.del_line !== undefined">
                                <el-switch v-model="rule_form.price.del_line"
                                           active-value="1"
                                           inactive-value="0"
                                > </el-switch>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'qr_code'">
                            <el-form-item label="样式">
                                <el-radio v-model="rule_form.qr_code.type" :label="1">圆形</el-radio>
                                <el-radio v-model="rule_form.qr_code.type" :label="2">方形</el-radio>
                            </el-form-item>
                            <el-form-item label="大小">
                                <el-slider
                                        :min=35
                                        :max=300
                                        v-model="rule_form.qr_code.size"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.qr_code.size)
                                        v-model="rule_form.qr_code.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.qr_code.size)
                                        v-model="rule_form.qr_code.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'desc'">
                            <el-form-item label="文本内容">
                                <el-input v-model="rule_form.desc.text"></el-input>
                            </el-form-item>
                            <el-form-item label="大小">
                                <el-slider
                                        :min=12
                                        :max=30
                                        v-model="rule_form.desc.font"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.desc.font)
                                        v-model="rule_form.desc.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.desc.font)
                                        v-model="rule_form.desc.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="文本宽度" v-if="rule_form.desc.width">
                                <el-slider
                                        :min=1
                                        :max=750
                                        v-model="rule_form.desc.width"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="颜色">
                                <el-color-picker
                                        style="margin-left: 20px;"
                                        color-format="rgb"
                                        v-model="rule_form.desc.color"
                                        :predefine="predefineColors">
                                </el-color-picker>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'pic'">
                            <el-form-item label="宽度">
                                <el-slider
                                        :min=0
                                        :max=750
                                        v-model="rule_form.pic.width"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="高度">
                                <el-slider
                                        :min=0
                                        :max=1334
                                        v-model="rule_form.pic.height"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.pic.height)
                                        v-model="rule_form.pic.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.pic.width)
                                        v-model="rule_form.pic.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <slot name="step_poster"></slot>
                        </template>

                        <template v-else-if="goods_component_key == 'poster_bg'">
                            <el-form-item label="宽度">
                                <el-slider
                                        :min=0
                                        :max=750
                                        v-model="rule_form.poster_bg.width"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="高度">
                                <el-slider
                                        :min=0
                                        :max=1334
                                        v-model="rule_form.poster_bg.height"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.poster_bg.height)
                                        v-model="rule_form.poster_bg.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.poster_bg.width)
                                        v-model="rule_form.poster_bg.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>

                            <el-form-item label="选择图片">
                                <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                                @selected="posterBgPic">
                                        <el-button size="mini">选择图标</el-button>
                                </app-attachment>
                                <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                    <app-attachment :multiple="false" :max="1"
                                                    @selected="posterBgPic">
                                        <app-image mode="aspectFill"
                                                   width="80px"
                                                   height='80px'
                                                   :src="rule_form.poster_bg.file_path">
                                        </app-image>
                                    </app-attachment>
                                </div>
                            </el-form-item>
                        </template>

                        <template v-else-if="goods_component_key == 'time_str'">
                            <el-form-item label="大小">
                                <el-slider
                                        :min=12
                                        :max=40
                                        v-model="rule_form.time_str.font"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="上间距">
                                <el-slider
                                        :min=0
                                        :max=1334-(rule_form.time_str.font)
                                        v-model="rule_form.time_str.top"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="左间距">
                                <el-slider
                                        :min=0
                                        :max=750-(rule_form.time_str.font)
                                        v-model="rule_form.time_str.left"
                                        show-input>
                                </el-slider>
                            </el-form-item>
                            <el-form-item label="颜色">
                                <el-color-picker
                                        style="margin-left: 20px;"
                                        color-format="rgb"
                                        v-model="rule_form.time_str.color"
                                        :predefine="predefineColors">
                                </el-color-picker>
                            </el-form-item>
                        </template>
                    </div>
                </el-card>
            </div>
        </div>
    </el-form>
</template>

<script>
    Vue.component('app-poster', {
        template: '#app-poster',
        props: {
            rule_form: Object,
            goods_component: Array, //数据
        },
        data() {
            return {
                rules: {},
                predefineColors: [
                    '#000',
                    '#fff',
                    '#888',
                    '#ff4544'
                ],
                btnLoading: false,
                title_desc: '',
                goods_component_key: null,
                cardLoading: false,
            };
        },
        computed: {
            // 控制显示的内容
            test2() {
                return function (index) {
                    let info = this.rule_form[this.goods_component[index].key];
                    if (info && info.is_show == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
        },
        methods: {
            posterBgTwoPic(e) {
                if (e.length) {
                    this.rule_form.poster_bg_two.file_path = e[0].url;
                }
            },
            removePosterBgTwoPic() {
                this.rule_form.poster_bg_two.file_path = '';
            },

            posterBgPic(e) {
                if (e.length) {
                    this.rule_form.poster_bg.file_path = e[0].url;
                }
            },
            removePosterBgPic() {
                this.rule_form.poster_bg.file_path = '';
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
            // 移除背景图片
            removeBgPic() {
                this.rule_form.bg_pic.url = '';
            },
            // 添加组件
            componentItemClick(index) {
                this.goods_component[index].is_active = true;
                this.rule_form[this.goods_component[index].key].is_show = '1';
                this.goods_component_key = this.goods_component[index].key;
                this.title_desc = this.goods_component[index].title;
            },
            // 移除组件
            componentItemRemove(index) {
                this.goods_component[index].is_active = false;
                this.rule_form[this.goods_component[index].key].is_show = '0';
                this.goods_component_key = '';
            }
        },
        updated: function () {
            if (!this.goods_component_key && this.goods_component && this.goods_component.length) {
                this.goods_component_key = this.goods_component[0].key;
                this.title_desc = this.goods_component[0].title;
            }
        }
    });
</script>
