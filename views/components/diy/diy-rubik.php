<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/16
 * Time: 17:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$baseUrl = Yii::$app->request->baseUrl;
?>
<style>
    .diy-rubik .diy-component-preview {
        background: #fff;
    }

    .diy-rubik .block {
        border: 1px solid #e0e0e0;
        padding: 25px;
        margin: 0 50px 50px 0;
        zoom: 0.2;
        text-align: center;
        cursor: pointer;
    }

    .diy-rubik .block.active {
        border: 1px #5CB3FD solid;
    }

    .diy-rubik .rubik-list {
        width: 750px;
        height: 372px;
    }

    .diy-rubik .layout {
        width: 750px;
        position: relative;
    }

    .diy-rubik .layout .rubik {
        position: absolute;
        top: 0;
        left: 0;
        border: 1px dashed #c9c9c9;
        cursor: pointer;
        color: #c9c9c9;
        z-index: 11;
    }

    .diy-rubik .layout .rubik.active {
        border: 1px solid #5CB3FD
    }

    .diy-rubik .pic-upload {
        width: 100px;
        height: 100px;
        border: 1px dashed #5CB3FD;
        font-size: 50px;
        color: #5CB3FD
    }

    .diy-rubik .layout .rubik .delete {
        position: absolute;
        right: -16px;
        top: -16px;
        padding: 8px;
        z-index: 14;
    }

    .diy-rubik .el-input-group__append {
        background-color: #fff
    }
</style>
<template id="diy-rubik">
    <div class="diy-rubik">
        <div class="diy-component-preview">
            <div class="layout" :style="blockStyle(data.style)" @click="blockClick">
                <div class="rubik" style="width: 100%;height: 100%;" @mousemove="blockMove"
                     :style="active ? (isMove ? 'z-index: 12' : 'z-index: 10') : 'z-index: 16'"></div>
                <template v-for="(item, index) in cList">
                    <div class="rubik" flex="main:center cross:center"
                         @click.stop="selectRubik(index)" :class="rubik == index ? 'active' : ''"
                         :style="layoutRubik(index)">
                        <template v-if="data.style == 8">
                            <el-button class="delete" v-show="index == rubik && !isMove"
                                       size="mini" type="danger" icon="el-icon-close" circle
                                       @click.stop="deleteRubik(index)"></el-button>
                        </template>
                        <span v-if="!data.list[index].pic_url">{{rubikSize(index)}}</span>
                        <img v-else :src="data.list[index].pic_url" style="width: 100%;visibility: hidden;">
                    </div>
                    <div class="rubik" :style="emptyStyle(index)" v-if="data.style == 8"></div>
                    <img v-if="data.list[index].pic_url && data.style == 0" :src="data.list[index].pic_url"
                         style="width: 100%;visibility: hidden;display: block">
                </template>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-tabs v-model="activeName" type="card" @tab-click="handleClick">
                    <el-tab-pane label="图片样式" name="first">
                        <el-form-item label="样式">
                            <div flex="dir:left" style="flex-wrap: wrap;">
                                <div class="block" v-for="(item, index) in style_list"
                                     @click="selectStyle(index)" :class="blockActive(index)">
                                    <div class="rubik-list">
                                        <img :src="item.icon" style="width: 750px;height: 360px;">
                                    </div>
                                    <div style="font-size: 60px;margin: 30px">{{item.name}}</div>
                                </div>
                            </div>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="图片上传" name="second">
                        <el-form-item label="图片间隙" v-if="data.style > 0">
                            <el-input v-model="data.space" size="small" type="number" min="0" max="10">
                                <template slot="append">
                                    <span>px</span>
                                </template>
                            </el-input>
                        </el-form-item>
                        <template v-if="data.style == 8">
                            <el-form-item label="魔方宽度">
                                <el-input v-model="data.w" size="small" type="number" min="0" max="10">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="魔方高度">
                                <el-input v-model="data.h" size="small" type="number" min="0" max="10">
                                </el-input>
                            </el-form-item>
                        </template>
                        <template v-if="!data.list[rubik]">
                            <el-form-item label="图片上传">
                                <span>请先在左边选择图片位置</span>
                            </el-form-item>
                        </template>
                        <template v-else>
                            <el-card shadow="never">
                                <el-form-item label="图片上传">
                                    <app-attachment :multiple="false" :max="1" v-model="data.list[rubik].pic_url">
                                        <el-button size="mini">选择图片</el-button>
                                    </app-attachment>
                                    <app-gallery :multiple="false" width="100px" height="100px"
                                                 :url="data.list[rubik].pic_url"></app-gallery>
                                </el-form-item>
                                <el-form-item label="选择链接">
                                    <app-pick-link title="选择链接" @selected="selectLink">
                                        <el-input size="small" v-model="rubikLink()" :disabled="true">
                                            <template slot="append">
                                                <el-button>选择链接</el-button>
                                            </template>
                                        </el-input>
                                    </app-pick-link>
                                </el-form-item>
                            </el-card>
                        </template>
                        <el-form-item label="热区划分">
                            <app-hotspot :multiple="true" :pic-list="data.list" :hotspot-array="data.hotspot"
                                         width="750px" :height="data.height + 'px'" @confirm="selectHotspot"
                                         :is-link="true">
                                <el-button size="mini">划分热区</el-button>
                            </app-hotspot>
                        </el-form-item>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-rubik', {
        template: '#diy-rubik',
        props: {
            value: Object,
            active: Boolean
        },
        data() {
            return {
                data: {
                    style: -1,
                    space: 0,
                    height: 0,
                    w: 1,
                    h: 1,
                    list: [],
                    hotspot: [],
                },
                rubik: 0,
                init: true,
                activeName: 'first',
                link: '',
                img: '<?=$baseUrl?>/statics/img/mall/default_img.png',
                isMove: false,
                zoom: 2, // 缩放倍数
                style_list: [
                    {
                        name: '1张图',
                        height: 360,
                        w: 1,
                        h: 1,
                        list: [
                            {
                                w: 1,
                                h: 1,
                                x: 0,
                                y: 0,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-0.png',
                    },
                    {
                        name: '2张图',
                        height: 360,
                        w: 25,
                        h: 12,
                        list: [
                            {
                                w: 10,
                                h: 12,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 15,
                                h: 12,
                                x: 10,
                                y: 0,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-1.png',
                    },
                    {
                        name: '3张图',
                        height: 360,
                        w: 25,
                        h: 12,
                        list: [
                            {
                                w: 10,
                                h: 12,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 15,
                                h: 6,
                                x: 10,
                                y: 0,
                            },
                            {
                                w: 15,
                                h: 6,
                                x: 10,
                                y: 6,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-2.png',
                    },
                    {
                        name: '4张图',
                        height: 360,
                        w: 50,
                        h: 24,
                        list: [
                            {
                                w: 20,
                                h: 24,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 30,
                                h: 12,
                                x: 20,
                                y: 0,
                            },
                            {
                                w: 15,
                                h: 12,
                                x: 20,
                                y: 12,
                            },
                            {
                                w: 15,
                                h: 12,
                                x: 35,
                                y: 12,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-3.png',
                    },
                    {
                        name: '2张图平分',
                        height: 240,
                        w: 50,
                        h: 16,
                        list: [
                            {
                                w: 25,
                                h: 16,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 25,
                                h: 16,
                                x: 25,
                                y: 0,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-4.png',
                    },
                    {
                        name: '3张图平分',
                        height: 240,
                        w: 75,
                        h: 24,
                        list: [
                            {
                                w: 25,
                                h: 24,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 25,
                                h: 24,
                                x: 25,
                                y: 0,
                            },
                            {
                                w: 25,
                                h: 24,
                                x: 50,
                                y: 0,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-5.png',
                    },
                    {
                        name: '4张图左右平分',
                        height: 186,
                        w: 4,
                        h: 1,
                        list: [
                            {
                                w: 1,
                                h: 1,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 1,
                                h: 1,
                                x: 1,
                                y: 0,
                            },
                            {
                                w: 1,
                                h: 1,
                                x: 2,
                                y: 0,
                            },
                            {
                                w: 1,
                                h: 1,
                                x: 3,
                                y: 0,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-6.png',
                    },
                    {
                        name: '4张图上下平分',
                        height: 372,
                        w: 250,
                        h: 124,
                        list: [
                            {
                                w: 125,
                                h: 62,
                                x: 0,
                                y: 0,
                            },
                            {
                                w: 125,
                                h: 62,
                                x: 125,
                                y: 0,
                            },
                            {
                                w: 125,
                                h: 62,
                                x: 0,
                                y: 62,
                            },
                            {
                                w: 125,
                                h: 62,
                                x: 125,
                                y: 62,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-7.png',
                    },
                    {
                        name: '自定义魔方',
                        height: 372,
                        w: 375,
                        h: 186,
                        list: [
                            {},
                            {
                                w: 125,
                                h: 93,
                                x: 125,
                                y: 0,
                            },
                            {
                                w: 125,
                                h: 93,
                                x: 250,
                                y: 0,
                            },
                            {
                                w: 125,
                                h: 93,
                                x: 0,
                                y: 93,
                            },
                            {
                                w: 125,
                                h: 93,
                                x: 125,
                                y: 93,
                            },
                            {
                                w: 125,
                                h: 93,
                                x: 250,
                                y: 93,
                            },
                        ],
                        icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik-8.png',
                    },
                ]
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
                if (this.data.list.length > 0 && this.data.list[0].link) {
                    this.link = this.data.list[0].link
                }
            }
        },
        computed: {
            cList() {
                return this.data.list
            }
        },
        updated() {
            this.init = false;
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
            'data.w': {
                handler(newVal, oldVal) {
                    if (this.data.style == 8 && !this.init) {
                        this.data.list = [];
                    }
                    this.data.w = Math.max(newVal, 1);
                    if (this.data.style == 8) {
                        this.data.w = Math.min(this.data.w, 10)
                    }
                },
            },
            'data.h': {
                handler(newVal, oldVal) {
                    if (this.data.style == 8 && !this.init) {
                        this.data.list = [];
                    }
                    this.data.h = Math.max(newVal, 1);
                    if (this.data.style == 8) {
                        this.data.h = Math.min(this.data.h, 10)
                    }
                },
            },
            'data.space': {
                handler(newVal, oldVal) {
                    this.data.space = Math.min(Math.max(newVal, 0), 10);
                },
            },
            rubik(newVal, oldVal) {
                if (!this.isMove) {
                    this.data.list[newVal] ? this.data.list[newVal].zIndex = 12 : '';
                    this.data.list[oldVal] ? this.data.list[oldVal].zIndex = 11 : '';
                }
            },
            space(newVal, oldVal) {
                this.data.space = Math.max(newVal, 0);
                for (let i in this.data.list) {
                    let block = this.data.list[i];
                    block = this.getStyle(block, this.data);
                }
            }
        },
        methods: {
            // 魔方展示样式(preview)
            blockStyle(index) {
                if (index === 8) {
                    let per = 750 / this.data.w;
                    this.data.height = this.data.h * 750 / this.data.w;
                    return `height: ${this.data.height}px;background-image: url('${this.img}');background-size: ${per}px ${per}px;`;
                } else {
                    if (index === 0) {
                        this.data.height = 'auto';
                        return `height: ${this.data.list[0].pic_url ? 'auto' : '360px'}`;
                    }
                    return `height: ${this.style_list[index] ? this.style_list[index].height : 360}px`;
                }
            },
            // 图片展示样式选中(edit)
            blockActive(index) {
                return this.data.style === index ? 'active' : ''
            },
            // 选择图片展示样式(edit)
            selectStyle(index) {
                this.data.style = index;
                if (index === 8) {
                    this.data.list = [];
                    this.data.w = 1;
                    this.data.h = 1;
                    return;
                }
                let style = JSON.parse(JSON.stringify(this.style_list[index]));
                for (let i in style.list) {
                    style.list[i].link = {};
                }
                this.data.height = style.height;
                this.data.w = style.w;
                this.data.h = style.h;
                this.data.list = style.list;
                this.data.hotspot = [];
            },
            // 选中图片展示样式的展示(preview)
            layoutRubik(index) {
                let list = this.data.list;
                if (!list) {
                    return '';
                }
                let style = list[index];
                style = this.getStyle(style, this.data, true, true);

                if (style.pic_url) {
                    style.backgroundImage = `url('${style.pic_url}')`;
                    style.backgroundRepeat = 'no-repeat';
                    style.backgroundSize = 'cover';
                    style.backgroundPosition = 'center';
                }
                return style;
            },
            // 图片选中事件(preview)
            selectRubik(index) {
                if (this.data.list && this.data.list[this.rubik]) {
                    this.data.list[this.rubik].zIndex = 11;
                }
                this.rubik = index;
                this.isMove = false;
            },
            // 各个图片的尺寸标注(preview)
            rubikSize(index) {
                if (this.data.style <= -1) {
                    return '';
                }
                if (this.data.style == 0) {
                    return '宽度为750，不限高度';
                }
                let style = this.data;
                let object = style.list[index];
                return `${Math.ceil(object.w * 750 / style.w)}*${Math.ceil(object.h * 750 / style.w)}`;
            },
            // 页签切换(edit)
            handleClick(tab, event) {
                // console.log(tab, event);
            },
            // 链接选择(edit)
            selectLink(list) {
                if (this.rubik < 0) {
                    return '';
                }
                if (this.data.list.length <= 0) {
                    return '';
                }
                if (list.length <= 0) {
                    return '';
                }
                this.data.list[this.rubik].link = list[0];
                this.link = list[0];
            },
            // 热区选择(edit)
            selectHotspot(list) {
                this.data.hotspot = list;
            },
            // 自定义魔方点击事件(preview)
            blockClick(e) {
                if (this.data.style < 8) {
                    return;
                }
                if (this.isMove) {
                    this.isMove = false;
                    this.data.list[this.rubik] ? this.data.list[this.rubik].zIndex = 11 : '';
                } else {
                    this.isMove = true;
                    // 每一小格的宽度
                    let per = this.data.w > 0 && this.data.h > 0 ? 750 / this.data.w : 750;
                    // 每一小格宽度的百分比
                    let wPer = this.data.w > 0 ? 100 / this.data.w : 100;
                    // 每一小格高度的百分比
                    let hPer = this.data.h > 0 ? 100 / this.data.h : 100;
                    // x轴占多少格
                    let numberX = Math.floor(e.offsetX * this.zoom / per);
                    // y轴咱多少格
                    let numberY = Math.floor(e.offsetY * this.zoom / per);
                    for (let i in this.data.list) {
                        let _this = this.data.list[i];
                        if (numberX >= _this.x && numberX < _this.x + _this.w && numberY >= _this.y && numberY < _this.y + _this.h) {
                            this.isMove = false;
                            return;
                        }
                    }
                    let block = {
                        backgroundColor: '#fff',
                        numberX: numberX,
                        numberY: numberY,
                        x: numberX,
                        y: numberY,
                        w: 1,
                        h: 1,
                        link: {}
                    };
                    this.data.list.push(JSON.parse(JSON.stringify(block)));
                    this.rubik = this.data.list.length - 1;
                }
            },
            // 自定义魔方鼠标移动事件(preview)
            blockMove(e) {
                if (!this.isMove) {
                    return;
                }
                let per = this.data.w > 0 && this.data.h > 0 ? 750 / this.data.w : 750;
                let wPer = this.data.w > 0 ? 100 / this.data.w : 100;
                let hPer = this.data.h > 0 ? 100 / this.data.h : 100;
                let future = this.data.list[this.rubik];
                if (!future) {
                    return;
                }
                // x轴占多少格
                let numberX = Math.floor(e.offsetX * this.zoom / per);
                // y轴咱多少格
                let numberY = Math.floor(e.offsetY * this.zoom / per);
                let futureX = Math.min(numberX, future.numberX);
                let futureY = Math.min(numberY, future.numberY);
                let futureW = (Math.abs(numberX - future.numberX) + 1);
                let futureH = (Math.abs(numberY - future.numberY) + 1);
                for (let i in this.data.list) {
                    let _this = this.data.list[i];
                    if (i == this.rubik) {
                        continue;
                    }
                    if (futureX + futureW <= _this.x) {
                        continue;
                    } else if (futureX < _this.x && futureX + futureW > _this.x) {
                        if (futureH + futureY <= _this.y) {
                            continue;
                        } else if (futureY < _this.y && futureY + futureH > _this.y) {
                            futureH = _this.y - futureY;
                        } else if (futureY >= _this.y && futureY < _this.y + _this.h) {
                            futureW = _this.x - futureX;
                        } else {
                            continue;
                        }
                    } else if (futureX >= _this.x && futureX < _this.x + _this.w) {
                        if (futureY + futureH <= _this.y) {
                            continue;
                        } else if (futureY + futureH > _this.y && futureY < _this.y) {
                            futureH = _this.y - futureY;
                        } else if (futureY >= _this.y && futureY < _this.y + _this.h) {
                            return;
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
                if (futureW == 0) {
                    return;
                }
                if (futureH == 0) {
                    return;
                }
                future.x = futureX;
                future.y = futureY;
                future.w = futureW;
                future.h = futureH;
            },
            // 魔方删除事件(preview)
            deleteRubik(index) {
                this.data.list.splice(index, 1);
                this.rubik = 0;
                this.isMove = false;
            },
            // 通过占比获取图片的宽度、高度、左边距、上边距
            getStyle(block, list, isSpace = true, isPreview = false) {
                let width = block.w * 100 / list.w + '%';
                let height = block.h * 100 / list.h + '%';
                let left = block.x * 100 / list.w + '%';
                let top = block.y * 100 / list.h + '%';

                if (isSpace) {
                    let space = this.data.space;
                    let wMultiple = 0;
                    let hMultiple = 0;
                    if (block.w < list.w) {
                        wMultiple += 1;
                    }
                    if (block.h < list.h) {
                        hMultiple += 1;
                    }
                    if (block.x > 0) {
                        left = `calc(${left} + ${space}px)`;
                    }
                    if (block.y > 0) {
                        top = `calc(${top} + ${space}px)`;
                    }
                    if (block.x + block.w < list.w && block.x > 0) {
                        wMultiple += 1;
                    }
                    if (block.y + block.h < list.h && block.y > 0) {
                        hMultiple += 1;
                    }
                    width = `calc(${width} - ${space * wMultiple}px)`;
                    height = `calc(${height} - ${space * hMultiple}px)`;
                }
                if (this.data.style === 0 && isPreview && this.data.list[0].pic_url) {
                    height = 'auto';
                }

                block.width = width;
                block.height = height;
                block.left = left;
                block.top = top;
                return block;
            },
            emptyStyle(index) {
                let list = JSON.parse(JSON.stringify(this.data.list));
                if (!list) {
                    return '';
                }
                let style = list[index];
                style = this.getStyle(style, this.data, false, true);
                style.background = '#F7F7F7';
                style.zIndex = 9;
                style.border = 'none';
                return style;
            },
            rubikLink () {
                if (!this.data.list[this.rubik].link) {
                    Vue.set(this.data.list[this.rubik], 'link', {});
                }
                return this.data.list[this.rubik].link.name
            }
        }
    });
</script>
