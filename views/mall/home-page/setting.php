<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        background-size: cover;
        position: relative;
        font-size: .85rem;
        margin-right: 20px;
        z-index: 3;
    }

    .mobile-box .show-box {
        height: 606px;
        width: 378px;
        overflow: auto;
    }

    .show-box::-webkit-scrollbar { /*滚动条整体样式*/
        width: 1px; /*高宽分别对应横竖滚动条的尺寸*/
    }

    .module-box {
        height: auto;
        overflow: auto;
        padding: 5px 10px 20px;
    }

    .module-item-box {
        width: 44%;
        max-width: 160px;
        height: 125px;
        font-size: 16px;
        margin: 5px 10px;
        border: 1px solid #eeeeee;
    }

    .module-box .name-box {
        height: 70px;
    }

    .module-box .edit-box {
        height: 55px;
        border-top: 1px dotted #eeeeee;
        color: blue;
        cursor: pointer;
        padding: 0 20%;
    }

    .top-bar {
        width: 375px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .top-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 16px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .top-bar img {
        width: 378px;
        height: 64px;
    }

    .module-name {
        font-size: 12px;
        position: absolute;
        top: 0;
        width: 100%;
        height: 20px;
        background: rgba(0, 0, 0, .2);
        color: #ffffff;
        text-align: center;
    }

    .module-icon {
        position: absolute;
        top: 0;
        left: 0;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .module-icon-edit {
        left: 20px;
        top: 1px;
    }

    .button-item {
        padding: 9px 25px;
        position: absolute;
        bottom: -52px;
        left: 0;
    }

    .el-tabs__nav {
        transform: translateX(30px) !important;
    }

    .el-tabs__item {
        height: 60px;
        line-height: 60px;
    }

    .dialog-width .el-dialog {
        max-width: 700px;
    }

    .edit .el-dialog {
        position: fixed;
        top: 10%;
        left: 0;
        right: 0;
        margin: 0 auto;
        max-width: 500px;
        z-index: 10;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>首页布局设置</span>
            </div>
        </div>
        <div>
            <div style="display: flex;">
                <div class="mobile-box">
                    <div class="top-bar" flex="main:center cross:center">
                        <div>首页</div>
                    </div>
                    <div class="show-box">
                        <draggable v-model="list" id="box" :options="{group:'people'}">
                            <div v-for="(item, index) in list" style="position: relative;cursor: move;">
                                <img :style="{'width': '100%','height': ''+ item.height +''}"
                                     :src="modules[item.key].bg_url">
                                <div v-if="item.name" class="module-name">{{item.name}}</div>
                                <el-tooltip effect="dark" content="删除" placement="top">
                                    <img style="height: 20px;width: 20px;" @click="moduleDestroy(index)"
                                         :src="icon_destroy" class="module-icon">
                                </el-tooltip>
                                <el-tooltip effect="dark" content="编辑" placement="top">
                                    <img style="height: 20px;width: 20px;" @click="openDialogForm(item, index)"
                                         v-if="item.key === 'video'"
                                         :src="icon_edit"
                                         class="module-icon module-icon-edit">
                                </el-tooltip>
                            </div>
                        </draggable>
                    </div>
                </div>
                <el-card shadow="never" body-style="padding: 0"
                         style="height: 100%;position: relative;width: 100%;overflow: visible;margin-bottom: 70px">
                    <el-tabs v-model="activeName" @tab-click="handleClick">
                        <template v-for="item in option">
                            <el-tab-pane :label="item.name" :name="item.key">
                                <div class="module-box" flex="wrap:wrap">
                                    <div v-for="item in item.list" class="module-item-box" flex="dir:top">
                                        <div class="name-box" flex="main:center cross:center">{{item.name}}</div>
                                        <div v-if="item.key !== 'topic' && item.key !== 'notice' && item.key !== 'coupon' && item.key !== 'home_nav'"
                                             @click="moduleAdd(item,'add')" flex="main:center cross:center"
                                             class="edit-box">
                                            <div style="height: 33px;width: 33px;border-radius: 50%;background-color: #EEF9F1;color: #80C269;
                                        text-align: center;line-height: 33px;">
                                                <el-tooltip effect="dark" content="添加" placement="top">
                                                    <img src="statics/img/mall/plus.png" alt="">
                                                </el-tooltip>
                                            </div>
                                        </div>
                                        <div v-else flex="dir:left box:mean" class="edit-box">
                                            <div @click="moduleAdd(item,'add')" flex="main:center cross:center">
                                                <div style="height: 33px;width: 33px;border-radius: 50%;background-color: #EEF9F1;color: #80C269;
                                        text-align: center;line-height: 33px;">
                                                    <el-tooltip effect="dark" content="添加" placement="top">
                                                        <img src="statics/img/mall/plus.png" alt="">
                                                    </el-tooltip>
                                                </div>
                                            </div>
                                            <div @click="openDialogForm(item)" flex="main:center cross:center">
                                                <div style="height: 33px;width: 33px;border-radius: 50%;background-color: #EEF9F1;color: #409EFF;
                                        text-align: center;line-height: 33px;">
                                                    <el-tooltip effect="dark" content="编辑" placement="top">
                                                        <img src="statics/img/mall/edit.png" alt="">
                                                    </el-tooltip>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </el-tab-pane>
                        </template>
                    </el-tabs>
                    <el-button class="button-item" :loading="btnLoading" type="primary" @click="store" size="small">保存
                    </el-button>
                </el-card>
            </div>
        </div>


        <el-dialog class="edit" title="编辑" :visible.sync="dialogFormVisible"
                   :class="dialogForm.key === 'notice' ? 'dialog-width' : ''">
            <el-form :model="dialogForm" :rules="dialogRule" ref="dialogForm" label-width="150px" size="small">
                <template v-if="dialogForm.key === 'notice'">
                    <el-form-item label="公告名称" prop="notice_name">
                        <el-input v-model="dialogForm.notice_name"></el-input>
                    </el-form-item>
                    <el-form-item label="公告内容" prop="notice_content">
                        <el-input type="textarea" :row="3" v-model="dialogForm.notice_content"></el-input>
                    </el-form-item>
                    <el-form-item label="公告背景色" prop="notice_bg_color">
                        <el-color-picker
                                @active-change="noticeBgColorChange"
                                style="margin-left: 20px;"
                                color-format="rgb"
                                v-model="dialogForm.notice_bg_color"
                                :predefine="predefineColors">
                        </el-color-picker>
                    </el-form-item>
                    <el-form-item label="公告文字颜色" prop="notice_text_color">
                        <el-color-picker
                                @active-change="noticeTextColorChange"
                                style="margin-left: 20px;"
                                color-format="rgb"
                                v-model="dialogForm.notice_text_color"
                                :predefine="predefineColors">
                        </el-color-picker>
                    </el-form-item>
                    <el-form-item label="图标" prop="notice_url">
                        <app-attachment :multiple="false" :max="1" @selected="noticeUrl">
                            <el-tooltip effect="dark" content="建议尺寸36*36" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.notice_url">
                        </app-image>
                    </el-form-item>
                </template>

                <template v-if="dialogForm.key === 'topic'">
                    <el-form-item label="专题显示数量" prop="topic_num">
                        <el-select v-model="dialogForm.topic_num" placeholder="请选择">
                            <el-option
                                    v-for="item in topicOptions"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="dialogForm.topic_num == 1" label="图标(1个专题)" prop="topic_url">
                        <app-attachment :multiple="false" :max="1" @selected="topicUrl">
                            <el-tooltip effect="dark"
                                        content="建议尺寸104*32"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.topic_url">
                        </app-image>
                    </el-form-item>
                    <el-form-item v-if="dialogForm.topic_num == 2" label="图标(2个专题)" prop="topic_url_2">
                        <app-attachment :multiple="false" :max="1" @selected="topicUrl_2">
                            <el-tooltip effect="dark"
                                        content="'建议尺寸104*50"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.topic_url_2">
                        </app-image>
                    </el-form-item>
                    <el-form-item label="专题标签" prop="label_url">
                        <app-attachment :multiple="false" :max="1" @selected="labelUrl">
                            <el-tooltip effect="dark" content="建议尺寸54*28" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.label_url">
                        </app-image>
                    </el-form-item>

                </template>

                <template v-if="dialogForm.key === 'coupon'">
                    <el-form-item label="未领取图（满减券）" prop="coupon_not_url">
                        <app-attachment :multiple="false" :max="1" @selected="couponNotUrl">
                            <el-tooltip effect="dark" content="建议尺寸256*130" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.coupon_not_url">
                        </app-image>
                    </el-form-item>
                    <el-form-item label="已领取图" prop="coupon_url">
                        <app-attachment :multiple="false" :max="1" @selected="couponUrl">
                            <el-tooltip effect="dark" content="建议尺寸256*130" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.coupon_url">
                        </app-image>
                    </el-form-item>
                </template>
                <template v-if="dialogForm.key === 'video'">
                    <el-form-item label="视频文件" prop="video_url">
                        <el-input v-model="dialogForm.video_url" placeholder="请输入视频原地址或选择上传视频">
                            <template slot="append">
                                <app-attachment :multiple="false" :max="1" type="video" @selected="videoUrl">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <div slot="content">支持格式mp4;支持编码H.264;<br/>视频大小不能超过50 M</div>
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                            </template>
                        </el-input>
                        <a v-if="dialogForm.name != '视频'" target="_blank" :href="dialogForm.video_url">
                            {{dialogForm.name ? dialogForm.name : '视频链接'}}
                        </a>
                    </el-form-item>
                    <el-form-item label="封面图" prop="video_pic_url">
                        <app-attachment :multiple="false" :max="1" @selected="videoPicUrl">
                            <el-tooltip effect="dark" content="建议尺寸750*400" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.video_pic_url">
                        </app-image>
                    </el-form-item>
                </template>
                <template v-if="dialogForm.key === 'home_nav'">
                    <el-form-item label="图标一行显示个数">
                        <el-radio v-model="dialogForm.row_num" label="4">4个</el-radio>
                        <el-radio v-model="dialogForm.row_num" label="5">5个</el-radio>
                    </el-form-item>
                </template>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogSubmit('dialogForm')">确 定</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>

<script src="//cdn.jsdelivr.net/npm/sortablejs@1.8.3/Sortable.min.js"></script>
<!-- CDNJS :: Vue.Draggable (https://cdnjs.com/) -->
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'normal',
                option: [],
                mobile_bg: _baseUrl + '/statics/img/mall/mobile-background.png',
                icon_destroy: _baseUrl + '/statics/img/mall/home_block/icon-destroy.png',
                icon_edit: _baseUrl + '/statics/img/mall/home_block/icon-edit.png',
                list: [],
                listIndex: -1,
                btnLoading: false,
                cardLoading: false,

                dialogForm: {},
                dialogRule: {
                    notice_name: [
                        {required: true, message: '请输入公告名称', trigger: 'change'},
                    ],
                    notice_url: [
                        {required: true, message: '请选择公告图片', trigger: 'change'},
                    ],
                    notice_content: [
                        {required: true, message: '请选择公告内容', trigger: 'change'},
                    ],
                    notice_text_color: [
                        {required: true, message: '请选择文字颜色', trigger: 'change'},
                    ],
                    notice_bg_color: [
                        {required: true, message: '请选择背景颜色', trigger: 'change'},
                    ],
                    topic_url: [
                        {required: true, message: '请选择专题图标', trigger: 'change'},
                    ],
                    topic_url_2: [
                        {required: true, message: '请选择专题图标', trigger: 'change'},
                    ],
                    label_url: [
                        {required: true, message: '请选择专题标签', trigger: 'change'},
                    ],
                    coupon_not_url: [
                        {required: true, message: '请选择未领取图', trigger: 'change'},
                    ],
                    coupon_url: [
                        {required: true, message: '请选择已领取图标', trigger: 'change'},
                    ],
                    video_url: [
                        {required: true, message: '请选择视频文件', trigger: 'change'},
                    ],
                    video_pic_url: [
                        {required: true, message: '请选择视频封面图片', trigger: 'change'},
                    ],
                },
                dialogFormVisible: false,
                dialogFormType: '',
                modules: {
                    search: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/search-bg.png',
                    },
                    banner: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/banner-bg.png',
                    },
                    block: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/block-bg.png',
                    },
                    home_nav: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/home-nav-bg.png',
                    },
                    cat: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/cat-bg.png',
                    },
                    video: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/video-bg.png',
                    },
                    mch: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/yuyue-bg.png',
                    },
                    notice: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/notice-bg.png',
                    },
                    topic: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/topic-bg.png',
                    },
                    coupon: {
                        bg_url: _baseUrl + '/statics/img/mall/home_block/coupon-bg.png',
                    },
                },
                predefineColors: [
                    '#000',
                    '#fff',
                    '#888',
                    '#ff4544'
                ],

                topicOptions: [
                    {
                        label: '1个',
                        value: '1',
                    },
                    {
                        label: '2个',
                        value: '2',
                    },
                ],
            };
        },
        methods: {
            handleClick() {

            },

            store() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/home-page/setting'
                    },
                    method: 'post',
                    data: {
                        list: self.list,
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/home-page/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        let data = e.data.data.detail;
                        data.forEach(function (item, index) {
                            self.moduleAdd(item)
                        });
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getOption() {
                let self = this;
                request({
                    params: {
                        r: 'mall/home-page/option',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.option = e.data.data.list;
                        self.modules = e.data.data.modules;
                        self.getDetail();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            noticeUrl(e) {
                if (e.length) {
                    this.dialogForm.notice_url = e[0].url;
                    this.$refs.dialogForm.validateField('notice_url');
                }
            },
            topicUrl(e) {
                if (e.length) {
                    this.dialogForm.topic_url = e[0].url;
                    this.$refs.dialogForm.validateField('topic_url');
                }
            },
            topicUrl_2(e) {
                if (e.length) {
                    this.dialogForm.topic_url_2 = e[0].url;
                    this.$refs.dialogForm.validateField('topic_url_2');
                }
            },
            labelUrl(e) {
                if (e.length) {
                    this.dialogForm.label_url = e[0].url;
                    this.$refs.dialogForm.validateField('label_url');
                }
            },
            couponUrl(e) {
                if (e.length) {
                    this.dialogForm.coupon_url = e[0].url;
                    this.$refs.dialogForm.validateField('coupon_url');
                }
            },
            couponNotUrl(e) {
                if (e.length) {
                    this.dialogForm.coupon_not_url = e[0].url;
                    this.$refs.dialogForm.validateField('coupon_not_url');
                }
            },
            videoUrl(e) {
                if (e.length) {
                    this.list[this.listIndex].video_url = e[0].url;
                    this.list[this.listIndex].name = e[0].name;
                    this.$refs.dialogForm.validateField('video_url');
                }
            },
            videoPicUrl(e) {
                if (e.length) {
                    this.list[this.listIndex].video_pic_url = e[0].url;
                    this.$refs.dialogForm.validateField('video_pic_url');
                }
            },
            // 添加
            moduleAdd(item, obj) {
                if (this.modules[item['key']] && item['key'] === 'video') {
                    let obj = {};
                    obj = JSON.parse(JSON.stringify(item));
                    this.list.push(obj);
                } else if (this.modules[item['key']]) {
                    this.list.push(item);
                } else {
                    return;
                }
                if (obj) {
                    setTimeout(function () {
                        let div = document.getElementsByClassName('show-box')[0];
                        div.scrollTop = div.scrollHeight;
                    }, 500)
                }
            },
            moduleDestroy(index) {
                this.list.splice(index, 1);
            },
            // 编辑
            openDialogForm(item, index = -1) {
                let self = this;
                self.dialogFormVisible = true;
                self.listIndex = index;

                // 已经编辑过则获取编辑过的内容
                let sign = true;
                self.list.forEach(function (listItem, index) {
                    if (listItem.key === item.key && item.key !== 'video') {
                        sign = false;
                        self.option.forEach(function (oItem, oIndex) {
                            oItem.list.forEach(function (oItem_2, oIndex_2) {
                                if (oItem_2['key'] === item['key']) {
                                    self.option[oIndex]['list'][oIndex_2] = listItem;
                                    self.dialogForm = listItem
                                }
                            });
                        });
                    }
                });
                if (sign) {
                    self.dialogForm = item;
                }
            },
            // 弹框数据保存
            dialogSubmit(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.list.forEach(function (item, index) {
                            if (item.key === 'video') {
                                if (index === self.listIndex) {
                                    self.list[index] = self.dialogForm;
                                }
                            }
                            if (item.key === self.dialogForm.key && item.key !== 'video') {
                                self.list[index] = self.dialogForm;
                                self.option.forEach(function (oItem, oIndex) {
                                    if (oItem['key'] === item.key) {
                                        self.option[oIndex] = item;
                                    }
                                });
                            }
                        });
                        self.listIndex = -1;
                        self.dialogForm = {};
                        self.dialogFormVisible = false;
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 公告颜色选择
            noticeBgColorChange(e) {
                this.dialogForm.notice_bg_color = e;
            },
            noticeTextColorChange(e) {
                this.dialogForm.notice_text_color = e;
            },
        },
        mounted: function () {
            this.getOption();
        }
    });
</script>
