<?php defined('YII_ENV') or exit('Access Denied');
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author xay
 * @link http://www.zjhejiang.com/
 */
?>
<style>
    .app-banner-scrollbar {
        height: 500px;
    }

    .app-banner-scrollbar > .el-scrollbar__wrap {
        overflow: scroll;
        overflow-x: auto;
        overflow-y: auto;
    }

    .app-banner-end {
        text-align: right;
        margin-top: 20px;
    }

    .app-banner-end div {
        height: 34px;
        text-align: center;
        margin-top: 20px;
    }

    .app-banner-title {
        padding: 1rem 5px;
        font-size: 12px;
        width: 190px;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }

    .app-banner-list {
        display: inline-block;
        padding-right: 5px;
        height: 280px;
        width: 250px;
        margin-right: 20px;
        cursor: pointer;
    }

    .app-banner-header {
        margin-top: -15px;
        line-height: 50px;
        height: 50px;
    }

    .app-banner-item {
        display: inline-block;
        border: 4px solid #e1e1e1;
        margin: 5px;
        cursor: pointer;
    }

    .app-banner-item:hover {
        border-color: #b7b7b7;
    }

    .app-banner-item.checked {
        border-color: #5bb94c;
    }

    .app-banner-item-image {
        display: inline-block;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .15);
        cursor: pointer;
        position: relative;
        float: left;
        width: 200px;
        margin: 5px 5px;
        border-radius: 5px;
        height: 217px;
    }

    .app-banner-item-image .app-attachment-img {
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }

    .app-banner-item-image .status {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 99;
        display: none;
    }

    .app-banner-item-image .app-banner-dialog-btn {
        position:absolute;
        bottom:80px;
        right:20px;
        padding: 0;
        display: none;
    }

    .app-banner-item-image:hover .app-banner-dialog-btn {
        display: block;
    }

    .app-banner-item-image.checked .status {
        display: block;
    }
    .app-banner-form-button {
        background: #FFFFFF;
    }

    .app-banner-item-image:hover {
        box-shadow: 0 0 0 1px rgba(84, 200, 255, 0.41);
    }

    .app-banner-item-image.checked,
    .app-banner-item-image.selected {
        box-shadow: 0 0 0 1px #409EFF;
    }

    .app-banner-item-image.checked .app-attachment-active-icon,
    .app-banner-item-image.selected .app-attachment-active-icon {
        opacity: 1;
    }

    .app-banner-text {
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .app-banner-form-body {
        padding: 20px 0;
        background-color: #fff;
        padding-bottom: 20px;
    }

    .app-banner-button-item {
        padding: 9px 25px;
    }

    .app-banner-list-item {
        height: 280px;
        width: 250px;
    }

    .app-banner-add-icon {
        font-size: 62px;
        color: #C0C4CC;
        margin-top: 90px;
        margin-bottom: 20px;
    }

    .app-banner-list-item .el-button {
        display: none;
    }

    .app-banner-list-item:hover {
        border: 1px solid #409EFF;
    }

    .app-banner-list-item:hover .el-button {
        display: block;
    }
</style>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/Sortable.min.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<template id="app-banner">
    <div class="app-banner">
        <el-dialog title="选择轮播图" :visible.sync="dialogVisible" @opened="dialogOpened" :close-on-click-modal="false"
                   width="718px" append-to-body style="z-index: 999;">
            <!-- 轮播图编辑 -->
            <el-dialog title="轮播图编辑" :visible.sync="bannerVisible" append-to-body>
                <el-form :model="bannerForm" label-width="100px" :rules="bannerFormRules" ref="bannerForm">
                    <el-form-item label="标题" prop="title">
                        <el-input size="small" v-model="bannerForm.title" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="跳转链接" prop="page_url">
                        <el-input size="small" v-model="bannerForm.page_url" class="input-with-select" autocomplete="off">
                            <app-pick-link slot="append" @selected="selectPageUrl">
                                <el-button size="mini">选择链接</el-button>
                            </app-pick-link>
                        </el-input>
                    </el-form-item>

                    <el-form-item label="图片" prop="pic_url">
                        <app-attachment title="选择图片" @selected="singlePicUrl">
                            <el-tooltip v-if="url == 'plugin/step/mall/banner/index'" class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 190"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                            <el-tooltip v-else-if="url == 'plugin/pintuan/mall/banner/index'" class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 230"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                            <el-tooltip v-else-if="url == 'plugin/bargain/mall/index/banner-store'" class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 280"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                            <el-tooltip v-else-if="url == 'plugin/lottery/mall/banner/index'" class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 280"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                            <el-tooltip v-else-if="url == 'plugin/integral_mall/mall/banner/index'" class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 190"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                            <el-tooltip v-else class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 350"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width='80px' height='80px' mode="aspectFill" :src="bannerForm.pic_url"></app-image>
                    </el-form-item>
                    <el-form-item style="margin-bottom: 0">
                        <el-button style="float: right;width: 80px;margin-left: 20px" size="small" type="primary" @click="bannerSubmit" :loading="bannerLoading">提交</el-button>
                        <el-button style="float: right;width: 80px;margin-left: 20px" size="small" @click="bannerVisible = false">取消</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>
            <div class="app-banner-header">
                <div style="float:right">
                    <template v-if="showEditBlock">
                        <el-button @click="showEditBlock=false" size="mini">退出编辑</el-button>
                        <el-button @click="bannerDestroy" size="mini" type="danger">删除</el-button>
                    </template>
                    <el-button v-else @click="showEditBlock=true" size="mini">开启编辑</el-button>
                </div>
            </div>
            <el-card shadow="never">
                <el-scrollbar class="app-banner-scrollbar" v-loading="loading">
                    <div v-if="showEditBlock==true" class="app-banner-item-image" style="height: 217px;text-align: center;" @click="bannerEdit">
                        <i class="el-icon-plus app-banner-add-icon" style="margin-top: 60px"></i>
                        <div style="color: #C0C4CC;font-size: 16px">添加轮播图</div>
                    </div>
                    <div v-for="(item, index) in banners" :key="index"
                         :class="'app-banner-item-image'+((item.checked)?' checked':'')" @click="pickerChange(item)">
                        <img class="app-attachment-img" :src="item.pic_url" style="width: 100%;height: 150px;">
                        <div style="padding:15px;font-size:12px;height: 63px">
                            <div class="app-banner-text">标题：{{item.title}}</div>
                            <div class="app-banner-text">路径：{{item.page_url}}</div>
                        </div>
                        <el-button v-if="showEditBlock" style="right:75px;" class="app-banner-dialog-btn" size="small" circle type="text" @click.stop="selectItem(item)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="showEditBlock" style="" class="app-banner-dialog-btn" size="small" circle type="text" @click.stop="bannerDestroy(item)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <img class="status" src="statics/img/mall/choose.png" alt="">
                    </div>
                </el-scrollbar>
                <div style="padding: 5px;text-align: center">
                    <el-button v-if="noMore" type="text" :disabled="true">已无更多</el-button>
                    <el-button v-else type="text" @click="handleLoadMore" :loading="loadMore">加载更多</el-button>
                </div>
            </el-card>
            <div class="app-banner-end">
                <el-button @click="confirm" :disabled="checkedAttachments.length == 0" style="width: 80px;" size="small" type="primary">选定</el-button>
            </div>
        </el-dialog>
        <el-card shadow="never" style="border:0" :body-style="title ? `background-color: #f3f3f3;padding: 10px 0 0;`:`background-color: #f3f3f3;padding: 0 0;`" v-loading="listLoading">
            <div v-if="title" slot="header" class="clearfix">
                <span>轮播图管理</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="editItem" size="small">编辑</el-button>
                </div>
            </div>
            <div class="text item">
                <el-form label-width="20px" size="small" class="demo-ruleForm">
                    <div class="app-banner-form-body">                   
                    <el-form-item>
                            <div class="app-banner-list" style="float: left;">
                                <el-card  class="app-banner-list-item" :body-style="{ padding: '0px' }" shadow="never" style="text-align: center;">
                                    <div @click="dialogVisible = !dialogVisible">
                                        <i class="el-icon-plus app-banner-add-icon"></i>
                                        <div style="color: #C0C4CC;font-size: 16px">添加轮播图</div>
                                    </div>
                                </el-card>
                            </div>
                            <draggable v-model="list" :options="{animation: 200}">
                                <div v-for="(item, index) in list" class="app-banner-list">
                                    <el-card class="app-banner-list-item" :body-style="{ padding: '0px' }" shadow="never">
                                        <div style="position: relative">
                                            <img style="height:190px;width:250px;display:block;" :src="item.pic_url" alt=''>
                                            <el-button style="position:absolute;bottom:20px;right:20px;padding: 0" size="small" circle type="text" @click="listDestroy(index)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </div>
                                        <div style="height:90px;padding:10px;font-size:12px;border-bottom:1px solid rgba(0,0,0,.125)">
                                            <div class="app-banner-text">标题：{{item.title}}</div>
                                            <div class="app-banner-text">路径：{{item.page_url}}</div>
                                        </div>
                                    </el-card>
                                </div>
                            </draggable>
                        </el-form-item>
                    </div>
                    <el-form-item class="app-banner-form-button">
                        <el-button class="button-item" type="primary" @click="onSubmit" size="small">保存</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-banner', {
        template: '#app-banner',
        props: {
            url: String,
            title: {
                type: Boolean,
                default: true
            },
            submit_url: String
        },
        data() {
            return {
                noMore: false,
                loadMore: false,
                showEditBlock: false,

                bannerForm: {},
                list: [],
                bannerLoading: false,
                bannerVisible: false,
                bannerFormRules: {
                    title: [
                        {min: 1, max: 30, message: "标题长度在1-30个字符内"},
                    ],
                    sort: [
                        {required: false, pattern: /^[1-9]\d{0,8}$/, message: '排序必须在9位正整数内'}
                    ],
                    pic_url: [
                        {required: true, message: '图片不能为空', trigger: ['blur', 'change']},
                    ]
                },
                page: 1,
                dialogVisible: false,
                loading: true,
                banners: [],
                checkedAttachments: [],
                listLoading: false,
            };
        },
        created() {
            this.getList();
        },
        methods: {
            listDestroy(index) {
                this.$confirm('确认删除该轮播图吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.list.splice(index, 1);
                    this.onSubmit(1);
                }).catch(() => {

                })
            },
            selectPageUrl(e) {
                let self = this;
                e.forEach(function (item, index) {
                    self.bannerForm.page_url = item.new_link_url;
                    self.bannerForm.open_type = item.open_type;
                    self.bannerForm.params = item.params ? item.params : [];
                    self.bannerForm.sign = item.key ? item.key : '';
                })
            },
            singlePicUrl(e) {
                if (e.length) {
                    this.bannerForm.pic_url = e[0].url;
                    this.$refs.bannerForm.validateField('pic_url');
                }
            },
            bannerEdit() {
                this.bannerForm = {
                    page_url: '',
                    pic_url: '',
                    title: '',
                };
                this.bannerVisible = true;
            },

            bannerDestroy(row) {
                let para;
                if(row.id > -1) {
                    para = [row.id];
                }else {
                    para = this.checkedAttachments.map((item, index, arr) => {
                        return item['id']
                    });
                    if (!para || Object.keys(para).length == 0) {
                        this.$message.error("请至少选择一项");
                        return;
                    }
                }
                this.$confirm('确认删除该选记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/banner/destroy',
                        },
                        data: {
                            ids: para,
                        },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.checkedAttachments = [];
                            this.page = 1;
                            this.listFormat(para);
                            this.loadList({});
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                });
            },
            listFormat(para){
                let newList = [];
                let oldList = JSON.parse(JSON.stringify(this.list));
                oldList.forEach(v => {
                    if(v.id.indexOf(para) === -1)
                        newList.push(v);
                })
                this.list = newList;
            },
            bannerSubmit() {
                this.$refs.bannerForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.bannerForm);
                        this.bannerLoading = true;
                        request({
                            params: {
                                r: 'mall/banner/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code == 0) {
                                this.bannerVisible = false;
                                this.checkedAttachments = [];
                                this.loadList({});
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            ;
                            this.bannerLoading = false;
                        }).catch(e => {
                            this.bannerLoading = false;
                        });
                    }
                });
            },

            handleLoadMore() {
                if (this.noMore) {
                    return;
                }
                this.page++;
                this.loadMore = true;
                request({
                    params: {
                        r: 'mall/banner/index',
                        page: this.page
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data.list.length == 0) {
                            this.noMore = true;
                        }
                        this.banners = this.banners.concat(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loadMore = false;
                }).catch({});
            },
            dialogOpened() {
                if (!this.banners || !this.banners.length) {
                    this.loadList({});
                }
            },
            loadList(params) {
                this.loading = true;
                params['r'] = 'mall/banner/index';
                request({
                    params: params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.banners = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    ;
                    this.loading = false;
                }).catch();
            },

            editItem(item) {
                let that = this;
                this.dialogVisible = true;
                setTimeout(function(){
                    resize();
                    that.bannerVisible = true;
                },0)             
            },

            selectItem(item) {
                this.bannerForm = item;
                this.bannerVisible = true;
            },
            pickerChange(item) {
                if (item.checked) {
                    item.checked = false;
                    for (let i in this.checkedAttachments) {
                        if (item.id === this.checkedAttachments[i].id) this.checkedAttachments.splice(i, 1);
                    }
                } else {
                    item.checked = true;
                    this.checkedAttachments.push(item);
                }
                this.banners = JSON.parse(JSON.stringify(this.banners));

            },
            confirm() {
                this.list = this.list.concat(this.checkedAttachments)
                this.dialogVisible = false;
                this.banners = [];
                this.checkedAttachments = [];
            },
            //获取列表
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: this.url,
                        type: 1,
                        page: this.page,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            onSubmit(number) {
                let data = this.list.map((item, index, arr) => {
                    return item['id']
                });
                request({
                    params: {
                        r: this.submit_url
                    },
                    data: {
                        ids: data
                    },
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        if(number == 1) {
                            this.$message.success('删除成功');
                        }else {
                            this.$message.success(e.data.msg);
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
        },
    });
</script>