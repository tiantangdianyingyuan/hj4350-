<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/26
 * Time: 16:31
 */
?>
<style>
    .diy-banner .banner-container .banner-img {
        height: 100%;
        width: 100%;
        background-repeat: no-repeat;
        background-position: center;
    }

    .diy-banner .banner-container .banner-img-cover {
        background-size: cover;
    }

    .diy-banner .banner-container .banner-img-contain {
        background-size: contain;
    }

    .diy-banner .banner-edit-item {
        border: 1px solid #dcdfe6;
        padding: 5px;
        margin-bottom: 5px;
    }

    .diy-banner .pic-upload {
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

    .diy-banner .banner-edit-options {
        position: relative;
    }

    .diy-banner .banner-edit-options .el-button {
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

    .diy-banner .banner-style-item {
        width: 100px;
        border: 1px solid #ebeef5;
        cursor: pointer;
        padding: 5px;
        line-height: normal;
        text-align: center;
        color: #606266;
    }

    .diy-banner .banner-style-item + .banner-style-item {
        margin-left: 5px;
    }

    .diy-banner .banner-style-item.active {
        border-color: #00a0e9;
        color: #409EFF;
    }

    .diy-banner .banner-style-1,
    .diy-banner .banner-style-2 {
        display: block;
        height: 50px;
        margin: 0 auto 5px;
        position: relative;
    }

    .diy-banner .banner-style-1 {
        background: #e6f4ff;
    }

    .diy-banner .banner-style-2 > div {
        background: #e6f4ff;
        position: absolute;
        left: 0;
        top: 10%;
        height: 50px;
        width: 100%;
        z-index: 0;
        zoom: .75;
    }

    .diy-banner .banner-style-2 > div:last-child {
        left: 15%;
        zoom: 1;
        box-shadow: 0 0 5px rgba(0, 0, 0, .2);
        z-index: 1;
        width: 70%;
        top: 0;
    }

    .chooseLink .el-input-group__append {
        background-color: #fff;
    }
    .header {
        padding: 20px;
        border-bottom: 1px solid #ebeef5;
    }
    .edit-item {
        border:  1px solid #ebeef5;
        border-radius: 5px;
    }
    .edit-item > .body {
        padding:30px 70px 10px 0;

    }
    .banner-style-3 div{
        width: 7px;
        height: 50px;
        display: inline-block;
        margin-right: 4px;
        background: #e6f4ff;
    }
    .banner-style-3 div:last-child {
        margin-right:0;
    }
    .banner-style-4 {
        background: radial-gradient(#ffffff,#e6f4ff);
        display: block;
        height: 50px;
        margin: 0 auto 5px;
        position: relative;
    }
    .option-banner {
        position: relative;
    }
    .option-banner .pic {
        position:absolute;
        top:0;
        left:0;
        width: 100%;
        height: 100%;
    }
    .fadeOne-enter-active,.fadeOne-leave-active {
        transition: all .5s ease;
    }
    .fadeOne-enter-active,.fadeOne-leave{
        opacity: 1;
    }
    .fadeOne-enter,.fadeOne-leave-active {
        opacity: 0;
    }
    .fadeTwo-enter-active,.fadeTwo-leave-active {
        transition: all .75s ease;
    }
    .fadeTwo-enter-active,.fadeTwo-leave{
        opacity: 1;
    }
    .fadeTwo-enter,.fadeTwo-leave-active {
        opacity: 0;
    }
    .fadeThree-enter-active,.fadeThree-leave-active {
        transition: all 1s ease;
    }
    .fadeThree-enter-active,.fadeThree-leave{
        opacity: 1;
    }
    .fadeThree-enter,.fadeThree-leave-active {
        opacity: 0;
    }
     .option-banner .banner-img-cover {
        background-size: cover;
    }

     .option-banner .banner-img-contain {
        background-size: contain;
    }
    .u-swiper-indicator {
        padding: 0 24px;
        position: absolute;
        display: flex;
        width: 100%;
        z-index: 1;
    }
    .u-indicator-item-round-active {
        width: 34px !important;
        background-color: rgba(255, 255, 255, 0.8) !important;
    }
    .u-indicator-item-round {
        width: 14px;
        height: 14px;
        margin: 0 6px;
        border-radius: 20px;
        transition: all 0.5s;
        background-color: rgba(0, 0, 0, 0.3);
    }

    .u-indicator-item-rect {
        width: 26px;
        height: 8px;
        margin: 0 6px;
        transition: all 0.5s;
        background-color: rgba(0, 0, 0, 0.3);
    }

    .u-indicator-item-rect-active {
        background-color: rgba(255, 255, 255, 0.8);
    }
    .el-carousel__container .el-carousel__indicators,.el-carousel__indicators--horizontal {
        display: none;
    }
</style>
<template id="diy-banner">
    <div class="diy-banner">
        <div class="diy-component-preview">
            <div class="banner-container" :style="cContainerStyle" v-if="data.effect === 1 || typeof data.effect === 'undefined'">
                <el-carousel @change="changeSwiper" :height="data.height+'px'" :interval="data.interval" :autoplay="data.autoplay === 0 ? true : false" :type="data.style === 2?'card':''">
                    <el-carousel-item v-for="(banner,index) in data.banners" :key="index">
                        <div :class="'banner-img '+cBannerImgClass"
                             :style="'background-image: url('+banner.picUrl+');'"></div>
                    </el-carousel-item>
                </el-carousel>
                <div class="u-swiper-indicator"
                     :style="{
                        bottom: '12px',
                        justifyContent: 'center',
                        zIndex: 1000
                    }"
                >
                    <template v-if="data.mode == 'rect'">
                        <div class="u-indicator-item-rect"
                             :class="{ 'u-indicator-item-rect-active': index === num }"
                             v-for="(item, index) in data.banners"
                             :key="index"></div>
                    </template>
                    <template v-if="data.mode == 'round'">
                        <div class="u-indicator-item-round"
                             :class="{ 'u-indicator-item-round-active': index === num }"
                             v-for="(item, index) in data.banners"
                             :key="index"></div>
                    </template>
                </div>
            </div>
            <div style="position: relative">
                <transition-group v-if="data.effect === 2" :name="fade" class="banner-container option-banner" tag="div" :style="cContainerStyle">
                    <div v-for="(item, index) in data.banners" :key="index" class="pic"  v-show="index === num">
                        <div :class="'banner-img '+cBannerImgClass"
                             :style="'background-image: url('+item.picUrl+');'"></div>
                    </div>
                </transition-group >
                <div class="u-swiper-indicator"
                     :style="{
                        bottom: '12px',
                        justifyContent: 'center'
                    }"
                >
                    <template v-if="data.mode == 'rect'">
                        <div class="u-indicator-item-rect"
                             :class="{ 'u-indicator-item-rect-active': index === num }"
                             v-for="(item, index) in data.banners"
                             :key="index"></div>
                    </template>
                    <template v-if="data.mode == 'round'">
                        <div class="u-indicator-item-round"
                             :class="{ 'u-indicator-item-round-active': index === num }"
                             v-for="(item, index) in data.banners"
                             :key="index"></div>
                    </template>
                </div>
            </div>

        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="150px">
                <div class="edit-item">
                    <div class="header">
                        轮播图设置
                    </div>
                    <div class="body">
                        <el-form-item label="指示器">
                            <app-radio v-model="data.mode" label="rect">条状</app-radio>
                            <app-radio v-model="data.mode" label="round">圆点</app-radio>
                        </el-form-item>
                        <el-form-item label="样式">
                            <div flex="dir:left">
                                <div @click="data.style=1" class="banner-style-item" :class="data.style==1?'active':''">
                                    <div class="banner-style-1"></div>
                                    <div>样式1</div>
                                </div>
                                <div @click="data.style=2" v-if="data.effect !== 2" class="banner-style-item" :class="data.style==2?'active':''">
                                    <div class="banner-style-2" flex>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <div>样式2</div>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="填充方式">
                            <app-radio v-model="data.fill" :label="0">留白</app-radio>
                            <app-radio v-model="data.fill" :label="1">填充</app-radio>
                        </el-form-item>
                        <el-form-item label="高度">
                            <el-input size="small" v-model.number="data.height" min="10" type="number">
                                <template slot="append">px</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="拉取轮播图">
                            <el-button size="small" @click="selectBanner">选择</el-button>
                        </el-form-item>
                        <el-form-item label="轮播图">
                            <draggable class="goods-list" flex="dir:top" v-model="data.banners" ref="parentNode">
                                <div class="banner-edit-item drag-drop" v-for="(banner,index) in data.banners">
                                    <div class="banner-edit-options">
                                        <el-button @click="bannerItemDelete(index)" type="primary" icon="el-icon-delete"
                                                   style="top: -6px;right: -31px;"></el-button>
                                    </div>
                                    <div flex="box:first">
                                        <div>
                                            <app-image-upload width="750" :height="data.height" v-model="banner.picUrl"
                                                              style="margin-right: 5px;"></app-image-upload>
                                        </div>
                                        <div class="chooseLink">
                                            <div @click="pickLinkClick(index)">
                                                <el-input v-model="banner.url" placeholder="点击选择链接" readonly
                                                          size="small">
                                                    <app-pick-link slot="append" @selected="linkSelected">
                                                        <el-button size="small">选择链接</el-button>
                                                    </app-pick-link>
                                                </el-input>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </draggable>
                            <el-button size="small" @click="addBanner">添加轮播图</el-button>
                        </el-form-item>
                    </div>
                </div>
                <div class="edit-item" style="margin-top: 20px;">
                    <div class="header">轮播动画设置</div>
                    <div class="body">
                        <el-form-item label="轮播方式">
                            <app-radio v-model="data.autoplay" :label="0">自动播放</app-radio>
                            <app-radio v-model="data.autoplay" :label="1">手动播放</app-radio>
                        </el-form-item>
                        <el-form-item label="轮播动效">
                            <div flex="dir:left">
                                <div @click="data.effect=1" class="banner-style-item" :class="data.effect==1?'active':''">
                                    <div class="banner-style-1"></div>
                                    <div>水平轮播</div>
                                </div>
                                <div @click="data.effect=2" v-if="data.style === 1" class="banner-style-item" :class="data.effect==2?'active':''">
                                    <div class="banner-style-4" flex>
                                    </div>
                                    <div>淡入淡出</div>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="轮播图静止时间" v-if="data.autoplay === 0">
                            <app-radio v-model="data.interval" :label="1000">1s</app-radio>
                            <app-radio v-model="data.interval" :label="2000">2s</app-radio>
                            <app-radio v-model="data.interval" :label="3000">3s</app-radio>
                            <app-radio v-model="data.interval" :label="4000">4s</app-radio>
                            <app-radio v-model="data.interval" :label="5000">5s</app-radio>
                            <app-radio v-model="data.interval" :label="6000">6s</app-radio>
                            <app-radio v-model="data.interval" :label="7000">7s</app-radio>
                            <app-radio v-model="data.interval" :label="8000">8s</app-radio>
                            <app-radio v-model="data.interval" :label="9000">9s</app-radio>
                            <app-radio v-model="data.interval" :label="10000">10s</app-radio>
                        </el-form-item>
                        <el-form-item label="轮播速度">
                            <app-radio v-model="data.duration" :label="750">标准</app-radio>
                            <app-radio v-model="data.duration" :label="1000">较慢</app-radio>
                            <app-radio v-model="data.duration" :label="500">较快</app-radio>
                        </el-form-item>
                    </div>
                </div>
            </el-form>
        </div>
        <el-dialog title="轮播图" :visible.sync="dialogTableVisible">
            <el-table v-loading="listLoading" :data="bannerList" ref="multipleTable" @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column property="title" label="名称"></el-table-column>
                <el-table-column label="导航链接">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.page_url}}</app-ellipsis>
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
                <el-button type="primary" @click="updateBanner">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-banner', {
        template: '#diy-banner',
        props: {
            value: Object,
        },
        data() {
            return {
                currentBannerIndex: null,
                data: {
                    style: 1,
                    fill: 1,
                    height: 450,
                    banners: [],
                    mode: 'round',
                    autoplay: 0,
                    interval: 1000,
                    duration: 750,
                    effect: 1
                },
                dialogTableVisible: false,
                page: 1,
                pageCount: 0,
                bannerList: [],
                listLoading: false,
                multipleSelection: [],
                num: 0,
                fade: 'fadeOne',
                time: null
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)));
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cContainerStyle() {
                return `height:${this.data.height}px;width:100%`;
            },
            cBannerImgClass() {
                if (this.data.fill == 0) {
                    return 'banner-img-contain';
                }
                if (this.data.fill == 1) {
                    return 'banner-img-cover';
                }
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
            'data.effect': {
                handler(newVal, oldVal) {
                    clearInterval(this.time);
                    if (newVal === 2 && this.data.autoplay === 0) {
                        this.data.style = 1;
                        this.setTime();
                    } else if (newVal === 2 && this.data.autoplay === 1) {
                        this.data.style = 1;
                    }
                },
                immediate: true
            },
            'data.duration': {
                handler(newVal, oldVal) {
                    if (this.data.effect !== 2) return;
                    if (newVal === 500) {
                        this.fade = 'fadeOne';
                    } else  if (newVal === 750){
                        this.fade = 'fadeTwo';
                    } else if (newVal === 1000) {
                        this.fade = 'fadeThree';
                    }
                },
                immediate: true
            },
            'data.interval': {
                handler(newVal, oldVal) {
                    if (this.data.effect === 1) {
                        if (this.data.autoplay === 0) {
                            this.data.autoplay = 1;
                            setTimeout(() => {
                                this.data.autoplay = 0;
                            }, 0);
                        }

                    }
                    clearInterval(this.time);
                    if (this.data.effect !== 2) return;
                    this.setTime();
                },
                immediate: true
            },
            'data.autoplay': {
                handler(newVal, oldVal) {
                    clearInterval(this.time);
                    if (this.data.effect === 2 && newVal === 0) {
                        this.setTime();
                    }

                },
                immediate: true
            },
            'data.style': {
                handler(newVal) {
                    if (newVal === 2) {
                        if (this.data.effect === 2) {
                            this.data.effect = 1;
                        }
                    }
                }
            }
        },
        methods: {
            addBanner() {
                this.data.banners.push({
                    picUrl: '',
                    url: '',
                    openType: '',
                });
            },
            bannerItemDelete(index) {
                this.data.banners.splice(index, 1);
            },
            pickLinkClick(index) {
                this.currentBannerIndex = index;
            },
            linkSelected(list) {
                if (!list.length) {
                    return;
                }
                const link = list[0];
                if (this.currentBannerIndex !== null) {
                    this.data.banners[this.currentBannerIndex].openType = link.open_type;
                    this.data.banners[this.currentBannerIndex].url = link.new_link_url;
                    this.data.banners[this.currentBannerIndex].params = link.params ? link.params : [];
                    this.data.banners[this.currentBannerIndex].key = link.key ? link.key : '';
                    this.currentBannerIndex = null;
                }
            },
            selectBanner() {
                this.dialogTableVisible = true;
                this.getBannerList();
            },
            getBannerList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/mall-banner/index',
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.bannerList = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getBannerList();
            },
            handleSelectionChange(val) {
                let arr = [];
                val.forEach(function (item, index) {
                    arr.push({
                        picUrl: item.pic_url,
                        openType: item.open_type,
                        url: item.page_url,
                        params: item.params,
                        key: item.sign
                    })
                });
                this.multipleSelection = arr;
            },
            updateBanner() {
                console.log(this.multipleSelection)
                let self = this;
                self.multipleSelection.forEach(function (item, index) {
                    self.data.banners.push(item)
                });
                self.dialogTableVisible = false;
            },

            setTime() {
                this.time = setInterval(() => {
                    this.num = this.num+1;
                    if(this.num>=this.data.banners.length){
                        this.num=0;
                    }
                }, this.data.interval);
            },

            changeSwiper(e) {
                this.num = e;
            }
        }
    });
</script>
