<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/16
 * Time: 11:03
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$static = Yii::$app->request->hostInfo . Yii::$app->request->baseUrl;
?>
<style>
    .app-hotspot .shadow {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0);
        border: 1px solid #2E9FFF;
    }

    .app-hotspot .hotspot {
        position: absolute;
        left: 0;
        top: 0;
        border: 1px dashed #5CB3FD;
        z-index: 100;
    }

    .app-hotspot .hotspot .close {
        position: absolute;
        right: -16px;
        top: -16px;
        z-index: 101
    }

    .app-hotspot .el-input {
        width: 70px;
    }

    .app-hotspot .right label {
        padding: 0 10px;
    }

    .app-hotspot .inside {
        position: relative;
        width: 750px;
        min-height: 1334px;
        background-color: #eee;
        zoom: 0.5;
    }

    .app-hotspot .pic {
        width: auto;
        height: auto;
        max-width: 750px;
        max-height: 1334px;
        transform: scale(0.5);
    }

    .app-hotspot .pic-list {
        position: relative;
    }

    .app-hotspot .pic-list div {
        position: absolute;
        left: 0;
        top: 0;
    }

    .app-hotspot {
        -moz-user-select: none;
        -khtml-user-select: none;
        user-select: none;
    }

    .app-hotspot .el-input-group__append {
        background-color: #fff;
    }
</style>
<template id="app-hotspot">
    <div class="app-hotspot">
        <div @click="dialogVisible = !dialogVisible" style="display: inline-block">
            <slot></slot>
        </div>
        <el-dialog append-to-body title="热区划分" :visible.sync="dialogVisible" :close-on-click-modal="false"
                   class="app-hotspot" width="900px">
            <div flex="dir:left box:first">
                <div class="inside" flex="dir:left main:center cross:center" ref="inside">
                    <div style="position: relative;" ref="box">
                        <div class="shadow" :style="{zIndex: zIndex}"
                             @click="click" @mousedown="mousedown" @mouseup="mouseup" @mousemove="mousemove"
                             @mouseout="mouseout "></div>
                        <div class="hotspot"
                             :style="{width:item.width+'px',height:item.height+'px',left:item.left+'px',
                             top:item.top+'px', maxWidth:'calc('+width+' - '+item.left+'px)',
                             maxHeight:'calc('+height+' - '+item.top+'px)', backgroundColor: index == key ? 'rgba(92, 179, 253, 0.2)' : ''}"
                             @click="select(key)" v-for="(item, key) in hotspotList"
                             :data-index="key">
                            <div class="close" @click.stop="del(key)" v-if="item.is_close">
                                <img style="width: 32px;height: 32px"
                                     src="<?= $static ?>/statics/img/mall/icon-close.png">
                            </div>
                        </div>
                        <div class="pic-list" :style="style" v-if="picList && picList.length > 0">
                            <template v-for="(item, index) in picList">
                            <div :style="item">
                                <img :src="item.pic_url" style="width: 100%;visibility: hidden;display: block;">
                            </div>
                                <img :src="item.pic_url" style="width: 100%;visibility: hidden;display: block;">
                            </template>
                        </div>
                        <div :style="style" flex="dir:left main:center cross:center" v-else>
                            <img style="width: auto;height: auto;max-width: 100%;max-height: 100%" :src="picUrl">
                        </div>
                    </div>
                </div>
                <el-form label-width="120px">
                    <div class="right" v-if="index > -1">
                        <el-form-item label="热区尺寸">
                            <el-row type="flex">
                                <el-col :span="12">
                                    <div flex="dir:left">
                                        <label>W</label>
                                        <el-input size="small" type="number" v-model.number="hotspotList[index].width" :max="maxWidth">
                                        </el-input>
                                        <label>px</label>
                                    </div>
                                </el-col>
                                <el-col :span="12">
                                    <div flex="dir:left">
                                        <label>H</label>
                                        <el-input size="small" type="number" v-model.number="hotspotList[index].height" :max="maxHeight">
                                        </el-input>
                                        <label>px</label>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-form-item>
                        <el-form-item label="热区位置">
                            <el-row type="flex">
                                <el-col :span="12">
                                    <div flex="dir:left">
                                        <label>X</label>
                                        <el-input size="small" type="number" v-model.number="hotspotList[index].left">
                                        </el-input>
                                        <label>px</label>
                                    </div>
                                </el-col>
                                <el-col :span="12">
                                    <div flex="dir:left">
                                        <label>Y</label>
                                        <el-input size="small" type="number" v-model.number="hotspotList[index].top">
                                        </el-input>
                                        <label>px</label>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-form-item>
                        <el-form-item label="热区链接" v-if="isLink">
                            <app-pick-link style="margin-left: 10px" title="选择链接" @selected="selectLink">
                                <el-input size="small" style="width: 100%;" :disabled="true" v-model="hotspotList[index].link.name">
                                    <template slot="append">
                                        <el-button>选择链接</el-button>
                                    </template>
                                </el-input>
                            </app-pick-link>
                        </el-form-item>
                        <el-form-item label="热区属性" v-if="mode == 'auth'">
                            <el-radio-group v-model="hotspotList[index].open_type">
                                <el-radio label="login" :disabled="radioDisabled('login')">登录按钮</el-radio>
                                <el-radio label="cancel" :disabled="radioDisabled('cancel')">不登录按钮</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </div>
                    <div v-if="index == -1 && hotspotList.length == 0" style="padding: 20px 40px;">请先在左侧蓝框内用鼠标划出热区范围</div>
                    <el-form-item v-else label="">
                        <el-button style="margin-left: 10px" type="primary" size="small" @click="confirm">保存</el-button>
                        <el-button size="small" @click="clearAll">重置</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-hotspot', {
        template: '#app-hotspot',
        props: {
            multiple: Boolean,
            picUrl: String,
            width: {
                type: String,
                default: '750px'
            },
            height: {
                type: String,
                default: '1334px'
            },
            hotspotArray: {
                type: Array,
                default: []
            },
            isLink: Boolean,
            picList: Array,
            max: Number,
            mode: String
        },
        data() {
            return {
                dialogVisible: false,
                is_mousedown: false,
                is_mousemove: false,
                is_close: false,
                hotspot: {
                    height: 0,
                    width: 0,
                    left: 0,
                    top: 0,
                    defaultX: 0,
                    defaultY: 0,
                    is_close: false,
                    link: '',
                    open_type: ''
                },
                hotspotList: [],
                index: -1,
                zIndex: 99,
            }
        },
        watch: {
            dialogVisible(oldVal, newVal) {
                this.hotspotList = JSON.parse(JSON.stringify(this.hotspotArray))
                this.index = -1;
            }
        },
        computed: {
            style() {
                return {
                    width: this.width,
                    height: this.height
                };
            },
            maxWidth() {
                return parseFloat(this.width) - this.hotspotList[this.index].left;
            },
            maxHeight() {
                return parseFloat(this.height) - this.hotspotList[this.index].top;
            },
        },
        methods: {
            click(e) {
                this.is_mousedown = false;
            },
            position(e) {
                function getElementPosition(e) {
                    var x = 0, y = 0;
                    while (e != null) {
                        x += e.offsetLeft;
                        y += e.offsetTop;
                        e = e.offsetParent;
                    }
                    return {x: x, y: y};
                }

                // 缩放比例
                let zoom = 2;
                // 获取图片距离浏览器的x/y（真实的尺寸，不受zoom属性影响）
                // let position = getElementPosition(this.$refs.box);
                // 获取父容器距离浏览器的x/y（真实的尺寸，不受zoom属性影响）
                // let inside = getElementPosition(this.$refs.inside);
                // 图片距离缩放容器左边和顶部
                // let picX = (position.x - inside.x) / zoom;
                // let picY = (position.y - inside.y) / zoom;
                // 鼠标点下时距离浏览器的x/y（需要计算zoom属性的影响）
                // position.x = e.clientX + (e.offsetX + picX) * (zoom - 1) - position.x;
                // position.y = e.clientY + (e.offsetY + picY) * (zoom - 1) - position.y;
                // position.x = (e.clientX - inside.x) * zoom - (position.x - inside.x);
                // position.y = (e.clientY - inside.y) * zoom - (position.y - inside.y);
                let position = {
                    x: e.offsetX * zoom,
                    y: e.offsetY * zoom
                };
                return position;
            },
            mousedown(e) {
                if (!this.multiple) {
                    if (this.hotspotList.length > 0) {
                        return;
                    }
                } else {
                    if (this.max && this.hotspotList.length === this.max) {
                        return ;
                    }
                }
                let position = this.position(e);
                this.is_mousedown = true;
                let hotspot = JSON.parse(JSON.stringify(this.hotspot));
                hotspot.left = position.x;
                hotspot.top = position.y;
                hotspot.defaultX = position.x;
                hotspot.defaultY = position.y;
                this.hotspotList.push(hotspot);
                this.index = this.hotspotList.length - 1;
                this.zIndex = 102;
            },
            mousemove(e) {
                if (this.is_mousedown) {
                    if (this.index === -1) {
                        return;
                    }
                    let position = this.position(e);
                    let hotspot = this.hotspotList[this.index];
                    this.hotspotList[this.index].left = Math.min(position.x, hotspot.defaultX);
                    this.hotspotList[this.index].top = Math.min(position.y, hotspot.defaultY);
                    this.hotspotList[this.index].width = Math.abs(hotspot.defaultX - position.x);
                    this.hotspotList[this.index].height = Math.abs(hotspot.defaultY - position.y);
                    this.is_mousemove = true;
                }
            },
            mouseout(e) {
                if (this.index === -1) {
                    return;
                }
                this.is_mousedown = false;
                this.hotspotList[this.index].is_close = true;
                this.is_mousemove = false;
                this.zIndex = 99;
            },
            mouseup(e) {
                if (this.index === -1) {
                    return;
                }
                this.is_mousedown = false;
                this.hotspotList[this.index].is_close = true;
                this.is_mousemove = false;
                this.zIndex = 99;
            },
            del(index) {
                this.hotspotList.splice(index, 1);
                this.index = -1;
            },
            select(index) {
                if (this.is_mousemove) {
                    return;
                }
                this.index = index;
            },
            clearAll() {
                this.index = -1;
                this.hotspotList = [];
            },
            selectLink(e) {
                if (this.index > -1 && this.hotspotList.length > 0) {
                    this.hotspotList[this.index].link = e[0]
                }
            },
            confirm() {
                this.dialogVisible = false;
                this.index = -1;
                this.$emit('confirm', this.hotspotList);
            },
            radioDisabled(name) {
                for (let i in this.hotspotList) {
                    if (this.hotspotList[i].open_type && this.hotspotList[i].open_type == name && i != this.index) {
                        return true;
                    }
                }
                return false;
            }
        }
    })
</script>
