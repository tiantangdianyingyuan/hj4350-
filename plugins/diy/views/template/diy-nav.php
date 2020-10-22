<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/26
 * Time: 10:26
 */
Yii::$app->loadViewComponent('diy/diy-bg');
Yii::$app->loadViewComponent('diy/diy-nav-select');
?>
<style>
    .diy-nav .nav-container {
        min-height: 90px;
        width: 100%;
        overflow-x: auto;
    }

    .diy-nav .nav-item {
        text-align: center;
        font-size: 24px;
        margin: 0 8px;
    }

    .diy-nav .nav-item:first-of-type {
        margin-left: 8px;
    }

    .diy-nav .nav-item > div {
        height: 25px;
        line-height: 25px;
    }

    .diy-nav .nav-item .bg {
        height: 100%;
        display: inline;
    }

    .diy-nav .nav-item .bg > div {
        padding-top: 100%;
        height: 0;
        background-repeat: no-repeat;
        background-size: 100% 100%;

        display: block;
        width: 100%;
        /*height: 100%;*/
        margin: 0 auto 5px auto;
    }

    .diy-nav .edit-nav-item {
        border: 1px solid #e2e2e2;
        line-height: normal;
        padding: 5px;
        margin-bottom: 5px;
        cursor: move;
    }

    .diy-nav .nav-icon-upload {
        display: block;
        width: 65px;
        height: 65px;
        line-height: 65px;
        border: 1px dashed #8bc4ff;
        color: #8bc4ff;
        background: #f9f9f9;
        cursor: pointer;
        background-size: 100% 100%;
        font-size: 28px;
        text-align: center;
        vertical-align: middle;
    }

    .diy-nav .nav-edit-options {
        position: relative;
    }

    .diy-nav .nav-edit-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .diy-nav .about-text {
        color: #909399;
        font-size: 12px;
        margin-top: -10px;
    }



    .diy-nav .t-omit {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        text-overflow: clip;
    }

</style>
<style>
    .diy-nav .nav-fixed {
        padding: 0 0 24px;
    }

    .diy-nav .nav-fixed.nav-fixed-text {
        padding-bottom: calc(32px - 36px + 32px);
    }

    .diy-nav .nav-list {
        padding-top: 28px;
        width: 100px;
    }

    .diy-nav .nav-list.nav-list-text {
        padding-top: 0;
    }

    .diy-nav .nav-image {
        height: 88px;
        width: 88px;
        display: block;
    }

    .diy-nav .nav-name {
        font-size: 24px;
        color: #353535;
        line-height: 1;
        padding-top: 16px;
        padding-bottom: calc(32px - 28px);
        display: block;
        white-space: nowrap;
        /*width: 100%;*/
        /*overflow: hidden;*/
        /*text-overflow: clip;*/
        text-align: center;
    }

    .diy-nav .nav-name.nav-name-text {
        padding-top: 32px;
        padding-bottom: calc(36px - 32px);
    }


    .diy-nav .nav-name.nav-name-alone {
        padding: calc(32px - 28px) 0;
    }

    .diy-nav .indicator {
        height: 10px;
        margin-top: calc(16px - 24px);
        padding-bottom: 12px;
        width: 100%;
    }

    .diy-nav .indicator .rectangle {
        border-radius: 6px;
        height: 8px;
        background-color: #BCBCBC;
        position: relative;
    }

    .diy-nav .indicator .rectangle .active {
        position: absolute;
        top: 0;
        width: 44px;
    }

    .diy-nav .indicator .rectangletwo {
        height: 8px;
        margin: 0 2px;
        width: 24px;
        border-radius: 16px;
    }

    .diy-nav .indicator .circle {
        height: 8px;
        margin: 0 5px;
        width: 8px;
        border-radius: 50%;
    }

    .diy-nav .nav-alone {
        padding: 28px 0;
        white-space: nowrap;
    }

    .diy-nav .box-list {
        text-align: center;
        height: 100%;
        flex: 0 0 auto;
    }

    .diy-nav .el-carousel__container {
        height: 100%;
    }
</style>
<template id="diy-nav">
    <div class="diy-nav">
        <div class="diy-component-preview">
            <div class="nav-container" :style="cContainerStyle">
                <!-- 固定样式 -->
                <div v-if="data.navType === 'fixed'" flex="dir:top" class="nav-fixed"
                     :class="{'nav-fixed-text' : data.modeType === 'text'}">
                    <div v-for="(navs, index1) in newNavs" :key="index1" flex="dir:left">
                        <div v-for="(nav, index2) in navs" :key="index2"
                             :style="{marginRight: index2 === navs.length - 1 ? 'auto' : '0',marginLeft: 'auto'}">
                            <div flex="dir:top main:center cross:center" class="nav-list"
                                 :class="{'nav-list-text' : data.modeType === 'text'}">
                                <image v-if="data.modeType === 'img' && !nav.temp" class="nav-image"
                                       :src="nav.icon ? nav.icon : 'statics/img/mall/default_img.png'"></image>
                                <div v-if="nav.name" class="nav-name" :style="{color: data.color}"
                                     :class="{'nav-name-text': data.modeType === 'text'}">
                                    {{nav.name}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 单行滑动 -->
                <div v-if="data.navType === 'alone'" flex style="overflow:auto">
                    <div flex="dir:left" class="nav-alone" style="overflow:visible">
                        <div flex="dir:top main:center cross:center" v-for="(nav, index) in data.navs" :key="index" :style="[formatB(index)]" class="box-list">
                            <image v-if="data.modeType === 'img' && !nav.temp"
                                   :style="{height: '88px',width: '88px'}"
                                   :src="nav.icon ? nav.icon : 'statics/img/mall/default_img.png'"></image>
                            <div v-if="nav.name" class="nav-name" :style="{color: data.color}"
                                 :class="{'nav-name-alone': data.modeType === 'text'}">{{nav.name}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 多行滑动 -->
                <div v-if="data.navType === 'multi'">
                    <el-carousel @change="changeSwiper"
                                 :autoplay="false"
                                 :style="{height: multiswiper}"
                                 indicator-position="none"
                                 :loop="false"
                                 ref="carousel"
                                 style="overflow-y: hidden"
                    >
                        <template v-for="newNavs in muitiNavs">
                            <el-carousel-item :style="{height: multiswiper}">
                                <div ref="navswiper" flex="dir:top" class="nav-fixed"
                                     :class="{'nav-fixed-text' : data.modeType === 'text'}">
                                    <div v-for="(navs, index1) in newNavs" :key="index1" flex="dir:left">
                                        <div v-for="(nav, index2) in navs" :key="index2"
                                             :style="{marginRight: index2 === navs.length - 1 ? 'auto' : '0',marginLeft: 'auto'}">
                                            <div flex="dir:top main:center cross:center" class="nav-list"
                                                 :class="{'nav-list-text' : data.modeType === 'text'}">
                                                <image v-if="data.modeType === 'img' && !nav.temp" class="nav-image"
                                                       :src="nav.icon ? nav.icon : 'statics/img/mall/default_img.png'"></image>
                                                <div v-if="nav.name" class="nav-name" :style="{color: data.color}"
                                                     :class="{'nav-name-text': data.modeType === 'text'}">
                                                    {{nav.name}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </el-carousel-item>
                        </template>
                    </el-carousel>
                    <!-- 指示点 -->
                    <div flex="main:center cross:center" class="indicator">
                        <template v-if="data.swiperType === 'rectangle'">
                            <div flex="dir:left">
                                <div v-for="(i,index) in muitiNavs" :key="index" class="rectangletwo"
                                     :style="{backgroundColor: current === index ? data.swiperColor : data.swiperNoColor}">
                                </div>
                            </div>
                            <!--<div :style="{width: `${muitiNavs.length * 44}px`}" class="rectangle">-->
                            <!--    <div class="rectangle active"-->
                            <!--         :style="{left: `${current * 44}px`,backgroundColor: data.swiperColor}"-->
                            <!--    ></div>-->
                            <!--</div>-->
                        </template>
                        <template v-if="data.swiperType === 'circle'">
                            <div flex="dir:left">
                                <div v-for="(i,index) in muitiNavs" :key="index" class="circle"
                                     :style="{backgroundColor: current === index ? data.swiperColor : data.swiperNoColor}">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px">
                <el-form-item label="选择模块">
                    <div flex="dir:left">
                        <div flex="dir:top cross:center main:center"
                             style="cursor: pointer;border-radius:3px"
                             @click="data.modeType = 'img'"
                             :style="{border: `1px solid ${data.modeType == 'img' ? '#409EFF':'#DDDDDD'}`}">
                            <div style="width: 100px;height: 48px" flex="cross:center main:center">
                                <img src="statics/img/mall/diy/picnav_img.png" alt="" style="width: 100%;height: 100%">
                            </div>
                            <div :style="{color: `${data.modeType == 'img' ? '#409EFF':'#666666'}`}">图片导航</div>
                        </div>
                        <div flex="dir:top cross:center main:center"
                             style="cursor: pointer;border-radius:3px;margin-left: 16px"
                             @click="data.modeType = 'text'"
                             :style="{border: `1px solid ${data.modeType == 'text' ? '#409EFF':'#DDDDDD'}`}">
                            <div style="width: 100px;height: 48px" flex="cross:center main:center">
                                <img src="statics/img/mall/diy/fontnav_img.png" alt="" style="width: 100%;height: 36px">
                            </div>
                            <div :style="{color: `${data.modeType == 'text' ? '#409EFF':'#666666'}`}">文字导航</div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="滑动设置">
                    <app-radio v-model="data.navType" label="fixed">固定</app-radio>
                    <app-radio v-model="data.navType" label="alone">单行滑动</app-radio>
                    <app-radio v-model="data.navType" label="multi">多行滑动</app-radio>
                </el-form-item>
                <el-form-item label="每行个数" v-if="data.navType === 'fixed' || data.navType === 'multi'">
                    <el-select v-model="data.columns" placeholder="请选择">
                        <el-option label="三个导航" :value="3"></el-option>
                        <el-option label="四个导航" :value="4"></el-option>
                        <el-option label="五个导航" :value="5"></el-option>
                        <el-option label="六个导航" :value="6"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="一屏行数" v-if="data.navType === 'multi'">
                    <el-select v-model="data.lineNum" placeholder="请选择">
                        <el-option label="二行" :value="2"></el-option>
                        <el-option label="三行" :value="3"></el-option>
                        <el-option label="四行" :value="4"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="分页器样式" v-if="data.navType === 'multi'">
                    <div flex="dir:left">
                        <div flex="dir:top cross:center main:center"
                             style="cursor: pointer;border-radius:3px"
                             @click="data.swiperType = 'circle'"
                             :style="{border: `1px solid ${data.swiperType == 'circle' ? '#409EFF':'#DDDDDD'}`}">
                            <div style="width: 100px;height: 48px" flex="cross:center main:center">
                                <img src="statics/img/mall/diy/img_circle.png" alt=""
                                     style="width: 54px;height: 10px">
                            </div>
                            <div :style="{color: `${data.swiperType == 'circle' ? '#409EFF':'#666666'}`}">圆点样式</div>
                        </div>
                        <div flex="dir:top cross:center main:center"
                             style="cursor: pointer;border-radius:3px;margin-left: 16px"
                             @click="data.swiperType = 'rectangle'"
                             :style="{border: `1px solid ${data.swiperType == 'rectangle' ? '#409EFF':'#DDDDDD'}`}">
                            <div style="width: 100px;height: 48px" flex="cross:center main:center">
                                <img src="statics/img/mall/diy/img_strip.png" alt=""
                                     style="width: 54px;height: 10px">
                            </div>
                            <div :style="{color: `${data.swiperType == 'rectangle' ? '#409EFF':'#666666'}`}">条状样式
                            </div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="分页器主色" v-if="data.navType === 'multi'">
                    <el-color-picker @change="(row) => {row == null ? data.swiperColor = '#FFFFFF' : ''}" size="small"
                                     v-model="data.swiperColor"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;"
                              v-model="data.swiperColor"></el-input>
                </el-form-item>
                <el-form-item label="分页器辅色" v-if="data.navType === 'multi'">
                    <el-color-picker @change="(row) => {row == null ? data.swiperNoColor = '#FFFFFF' : ''}" size="small"
                                     v-model="data.swiperNoColor"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;"
                              v-model="data.swiperNoColor"></el-input>
                </el-form-item>
                <el-form-item label="一屏显示" v-if="data.navType === 'alone'">
                    <el-select v-model="data.aloneNum" placeholder="请选择">
                        <el-option label="三个导航" :value="3"></el-option>
                        <el-option label="四个导航" :value="4"></el-option>
                        <el-option label="五个导航" :value="5"></el-option>
                        <el-option label="六个导航" :value="6"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="背景颜色">
                    <el-color-picker @change="(row) => {row == null ? data.backgroundColor = '#FFFFFF' : ''}"
                                     size="small" v-model="data.backgroundColor"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;"
                              v-model="data.backgroundColor"></el-input>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker @change="(row) => {row == null ? data.color = '#353535' : ''}" size="small"
                                     v-model="data.color"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.color"></el-input>
                </el-form-item>
                <el-form-item label="获取商城导航">
                    <diy-nav-select @change="navChange">
                        <el-button size="small">获取</el-button>
                    </diy-nav-select>
                </el-form-item>
                <el-form-item label="导航图标">
                    <draggable class="goods-list" style="width: 355px" flex="dir:top" v-model="data.navs"
                               ref="parentNode" :options="{filter:'.item-drag',preventOnFilter:false}">
                        <div v-for="(nav,index) in data.navs" class="edit-nav-item drag-drop"
                             v-if="(pageIndex * 10 <= index + 10) && (index < pageIndex * 10)">
                            <div class="nav-edit-options">
                                <el-button @click="navItemDelete(index)"
                                           type="primary"
                                           icon="el-icon-delete"
                                           style="top: -6px;right: -31px;"></el-button>
                            </div>
                            <div flex="dir:left box:first cross:center">
                                <div style="flex-grow: 0" v-if="data.modeType === `img`">
                                    <app-image-upload style="margin-right: 5px;" v-model="nav.icon" width="88"
                                                      height="88"></app-image-upload>
                                </div>
                                <div style="flex-grow: 1;max-width: 100%">
                                    <el-input class="item-drag" v-model="nav.name" placeholder="请输入名称，最多输入5个字"
                                              size="small"
                                              style="margin-bottom: 5px" maxlength="5"></el-input>
                                    <div flex="dir:left cross:center" v-if="nav.url">
                                        <div style="line-height:32px;width:auto;border:1px solid rgba(238,238,238,1);padding:0 10px;border-radius:4px;color: #666666;font-size: 14px"
                                             v-text="formatLabel(nav)" class="t-omit"></div>
                                        <app-pick-link slot="append" @selected="linkSelected">
                                            <el-button @click="pickLinkClick(index)" style="margin-left: 9px;padding: 0"
                                                       type="text">修改
                                            </el-button>
                                        </app-pick-link>
                                    </div>
                                    <div v-else @click="pickLinkClick(index)">
                                        <el-input v-model="nav.url" placeholder="点击选择链接" readonly
                                                  size="small" disabled>
                                            <app-pick-link slot="append" @selected="linkSelected">
                                                <el-button size="small">选择链接</el-button>
                                            </app-pick-link>
                                        </el-input>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </draggable>
                    <!--工具条 批量操作和分页-->
                    <div :span="24" style="width: 365px">
                        <el-button size="small" type="primary"
                                   @click="addNav">
                            +添加一个导航
                        </el-button>
                        <el-pagination
                                background
                                layout="prev, pager, next, jumper"
                                @current-change="navCurrentChange"
                                :current-page.sync="pageIndex"
                                :page-size="10"
                                :total="data.navs.length"
                                style="float:right;padding-top:5px"
                                v-if="data.navs.length">
                        </el-pagination>
                    </div>

                    <div style="color: #666666;font-size: 12px;">拖拽可改变顺序</div>
                </el-form-item>
            </el-form>
            <el-dialog title="导航链接" :visible.sync="dialogTableVisible">
                <el-table v-loading="listLoading" :data="navList" ref="multipleTable"
                          @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column property="name" label="导航名称"></el-table-column>
                    <el-table-column label="导航链接">
                        <template slot-scope="scope">
                            <app-ellipsis :line="1">{{scope.row.url}}</app-ellipsis>
                        </template>
                    </el-table-column>
                </el-table>
                <div style="text-align: right;margin: 20px 0;">
                    <el-pagination
                            @current-change="pagination"
                            background
                            layout="prev, pager, next, jumper"
                            :page-count="pageCount">
                    </el-pagination>
                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="dialogTableVisible = false">取 消</el-button>
                    <el-button type="primary" @click="updateNav">确 定</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-nav', {
        template: '#diy-nav',
        props: {
            value: Object
        },
        data() {
            return {
                currentEditNavIndex: null,
                data: {
                    navType: 'fixed',
                    aloneNum: 3,
                    lineNum: 2,
                    swiperType: 'circle',
                    swiperColor: '#409EFF',
                    swiperNoColor: '#a9a9a9',

                    color: '#353535',
                    rows: 1,
                    columns: 4,
                    scroll: true,
                    navs: [{
                        icon: '',
                        name: '导航一',
                        url: '',
                        openType: '',
                        labelType: '',
                        labelName: '',
                    }, {
                        icon: '',
                        name: '导航二',
                        url: '',
                        openType: '',
                        labelType: '',
                        labelName: '',
                    }, {
                        icon: '',
                        name: '导航三',
                        url: '',
                        openType: '',
                        labelType: '',
                        labelName: '',
                    }, {
                        icon: '',
                        name: '导航四',
                        url: '',
                        openType: '',
                        labelType: '',
                        labelName: '',
                    }],
                    showImg: false,
                    backgroundColor: '#ffffff',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    modeType: 'img',
                },
                position: 'center center',
                repeat: 'no-repeat',
                dialogTableVisible: false,
                page: 1,
                pageCount: 0,
                navList: [],
                listLoading: false,
                showBackImg: true,
                multipleSelection: [],
                current: 0,
                pageIndex: 1,
                multiswiper: '80px',

                defaultInfo: {
                    imageHeight: 88,
                    left: 32,
                    limit: 30,
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
            }
        },
        computed: {
            aloneWidth() {
                return '100px';
                let aloneNum = Number(this.data.aloneNum);
                let width = 0;
                if (aloneNum < 5) {
                    width = this.defaultInfo.imageHeight;
                } else {
                    width = (746 - this.defaultInfo.left - (aloneNum - 1) * this.defaultInfo.limit) / (aloneNum * 2 - 1) * 2;
                }
                return width + 'px';
            },
            formatB() {
                return (index) => {
                    let aloneNum = Number(this.data.aloneNum);
                    let extra = {width: this.aloneWidth};
                    if (aloneNum < 5) {
                        extra = Object.assign(extra, {
                            marginLeft : `${(750 - parseFloat(this.aloneWidth) * aloneNum) / (aloneNum + 1)}px`,
                            // 'margin-left': `${(746 - this.defaultInfo.left * 2 - this.defaultInfo.imageHeight * aloneNum) / (aloneNum - 1) / 2}px`,
                            // 'margin-right': `${(746 - this.defaultInfo.left * 2 - this.defaultInfo.imageHeight * aloneNum) / (aloneNum - 1) / 2}px`,
                        })
                    } else {
                        extra = Object.assign(extra, {
                            marginLeft: `${(750 - parseFloat(this.aloneWidth) * aloneNum + parseFloat(this.aloneWidth) / 2) / aloneNum}px`,
                            //'margin': `0 15px`,
                        })
                    }
                    if (index === 0) {
                        // extra = {
                        //     'width': this.aloneWidth,
                        //     'marginLeft': '32px',
                        //     'marginRight': extra.marginRight,
                        // }
                    }
                    if (index === this.data.navs.length - 1) {
                        extra = {
                            'marginLeft': extra.marginLeft,
                            'marginRight': extra.marginLeft,
                        }
                    }
                    return extra;
                }
            },
            newNavs() {
                let arr = this.addNull();
                return this.group(arr, this.data.columns)
            },
            muitiNavs() {
                let arr = this.addNull();
                arr = this.group(arr, this.data.columns);
                arr = this.group(arr, this.data.lineNum);
                return arr;
            },

            cContainerStyle() {
                return `background-color:${this.data.backgroundColor};overflow-x:${this.data.scroll ? 'auto' : 'hidden'};del-background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`;
            },
            cStyle() {
                let width = (this.cNavGroups.length ? this.cNavGroups.length : 1) * 750;
                return `width:${width}px;min-height:100px`;
            },
            cNavGroups() {
                const navGroups = [];
                const groupNavCount = this.data.rows * this.data.columns;
                for (let i in this.data.navs) {
                    const groupIndex = parseInt(i / groupNavCount);
                    if (!navGroups[groupIndex]) {
                        navGroups[groupIndex] = [];
                    }
                    navGroups[groupIndex].push(this.data.navs[i]);
                }
                return navGroups;
            },
            cNavStyle() {
                let scroll = this.data.scroll;
                let grp = 10;
                if (scroll) {
                    return `${(746 - grp * Number(this.data.columns)) / Number(this.data.columns)}px`;
                } else {
                    return `${(746 - grp * this.data.navs.length) / this.data.navs.length}px`;
                }
                return `auto`;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal);
                    setTimeout(() => {
                        let height = this.$refs.navswiper;
                        if (height && height[0]) {
                            this.multiswiper = `${height[0].offsetHeight}px`;
                        }
                    })
                },
            },
            /*muitiNavs: {
                deep: true,
                handler(newVal, oldVal) {
                    setTimeout(() => {
                        let height = this.$refs.navswiper;
                        if (height && height[0]) {
                            this.multiswiper = `${height[0].offsetHeight}px`;
                        }
                    })
                },
            },*/
        },
        methods: {
            navChange(e) {
                if (!e || e.length === 0) {
                    return;
                }
                let arr = [];
                e.forEach(item => {
                    console.log(item);
                    arr.push({
                        icon: item.icon_url,
                        name: item.name.substring(0,5),
                        url: item.url,
                        openType: item.open_type,
                        params: JSON.parse(item.params),
                        labelType: 'mall-add',
                        labelName: '',
                        key: item.sign,
                    })
                })
                this.data.navs = this.data.navs.concat(arr);
            },
            navCurrentChange(page) {
                this.pageIndex = page;
            },
            changeSwiper: function (current) {
                this.current = current;
            },
            group: function (array, subGroupLength) {
                subGroupLength = parseInt(subGroupLength);
                let index = 0;
                let newArray = [];
                while (index < array.length) {
                    newArray.push(array.slice(index, index += subGroupLength));
                }
                return newArray;
            },
            addNull: function () {
                let columns = this.data.columns;
                let length = this.data.navs.length;
                let addNum = length % columns === 0 ? 0 : columns - length % columns;

                return this.data.navs.concat(new Array(addNum).fill({
                    'url': '',
                    'openType': '',
                    'icon': '',
                    'name': '',
                    'temp': true,
                }))
            },
            formatLabel(column) {
                let types = {
                    base: '基础',
                    order: '订单',
                    share: '营销',
                    marketing: '营销',
                    plugin: '插件',
                    diy: '微页面',
                };
                if (column.labelType === 'mall-add') {
                    return column.url;
                } else if (column.labelType && types[column.labelType]) {
                    return types[column.labelType] + ' > ' + column.labelName;
                } else {
                    //todo 各个插件确少type字段
                    return '插件' + ' > ' + column.labelName;
                }
            },
            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            addNav() {
                this.data.navs.push({
                    icon: '',
                    name: '',
                    url: '',
                    openType: '',
                    labelType: '',
                    params: '',
                    labelName: '',
                    key: '',
                });
            },
            navItemDelete(index) {
                this.data.navs.splice(index, 1);
            },
            linkSelected(list, params) {
                if (!list.length) {
                    return;
                }
                const link = list[0];
                if (this.currentEditNavIndex !== null) {
                    this.data.navs[this.currentEditNavIndex].labelType = link.type;
                    this.data.navs[this.currentEditNavIndex].labelName = link.name;
                    this.data.navs[this.currentEditNavIndex].openType = link.open_type;
                    this.data.navs[this.currentEditNavIndex].url = link.new_link_url;
                    this.data.navs[this.currentEditNavIndex].params = link.params;
                    this.data.navs[this.currentEditNavIndex].key = link.key ? link.key : '';
                    this.currentEditNavIndex = null;
                }
            },
            pickLinkClick(index) {
                this.currentEditNavIndex = index;
            },
            selectNav() {
                this.dialogTableVisible = true;
                this.getNavList();
            },
            getNavList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/home-nav/index',
                        page: self.page,
                        limit: 10
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.navList = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getNavList();
            },
            handleSelectionChange(val) {
                let arr = [];
                val.forEach(function (item, index) {
                    arr.push({
                        icon: item.icon_url,
                        name: item.name,
                        openType: item.open_type,
                        url: item.url,
                        key: item.sign,
                        labelType: item.name,
                        labelName: item.type,
                    })
                });
                this.multipleSelection = arr;
            },
            updateNav() {
                console.log(this.multipleSelection)
                let self = this;
                self.multipleSelection.forEach(function (item, index) {
                    self.data.navs.push(item)
                });
                self.dialogTableVisible = false;
            }
        }
    });
</script>