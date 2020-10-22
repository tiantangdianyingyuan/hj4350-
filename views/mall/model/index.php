<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .item {
        width: 187.5px;
        height: 340px;
        position: relative;
        border: 1px solid #e2e2e2;
        border-radius: 5px;
        margin-left: 20px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .info {
        padding: 0 10px;
        position: absolute;
        height: 70px;
        width: 100%;
        bottom: 0;
        left: 0;
        z-index: 10;
        background-color: #fff;
    }

    .choose {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        width: 187.5px;
        height: 270px;
        z-index: 9;
        background-color: rgba(0, 0, 0, .6);
        padding-top: 70px;
    }

    .item:hover {
        margin-top: -10px;
        box-shadow: 0 4px 4px 4px #ECECEC;
    }

    .item:hover .choose{
        display: block;
    }

    .title {
        font-size: 20px;
        color: #353535;
        margin-bottom: 20px;
        padding-left: 20px;
    }

    .choose-btn {
        cursor: pointer;
        border-radius: 6px;
        height: 40px;
        line-height: 38px;
        width: 120px;
        margin: 20px auto;
        text-align: center;
        border: 1px solid #fff;
        color: #fff;
        font-size: 16px;
    }

    .choose-btn.use {
        border: 1px solid #3399ff;
        background-color: #3399ff;
    }

    .item-name {
        font-size: 16px;
        margin-bottom: 5px;
    }

    .info-about {
        color: rgb(144, 147, 153);
        margin-left: 5px;
    }

    .show-img {
        position: absolute;
        width: 187.5px;
        top: 0;
        left: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">
            <div>
                <span>模版中心</span>
            </div>
        </div>
        <div style="background-color: #fff;padding: 20px 0;">
            <div class="title">选择一个适合你的模版</div>
            <div flex="dir:left" style="flex-wrap: wrap">
                <div class="item" v-for="item in list">
                    <img class="show-img" :src="item.img" alt="">
                    <div class="choose">
                        <div @click="toUse(item)" class="choose-btn use" :loading="useLoading">加载模版</div>
                        <div @click="show(item)" class="choose-btn">预览模版</div>
                    </div>
                    <div class="info">
                        <div flex="cross:center" style="height: 70px;">
                            <div>
                                <div class="item-name">{{item.name}}</div>
                                <div flex="dir:left">
                                    <el-tag size="mini" type="danger" v-if="item.price> 0">收费</el-tag>
                                    <el-tag size="mini" type="primary" v-else>免费</el-tag>
                                    <div class="info-about">作者：{{item.author}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 24px;" flex="dir:right">
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block;"
                        background
                        :page-size="pagination.pageSize"
                        @current-change="getDetail"
                        layout="prev, pager, next, jumper"
                        :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>

        <el-dialog title="手机端预览" :visible.sync="dialogVisible" width="30%" :before-close="handleClose" v-if="template">
            <div style="height: 600px;overflow-y: auto;text-align: center">
                <img style="width: 375px;" :src="template.img" alt="">
            </div>
            <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogVisible = false;template=null">取 消</el-button>
            <el-button size="small" type="primary" @click="toUse(template)" :loading="useLoading">加载模版</el-button>
            </span>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                template: null,
                dialogVisible: false,
                loading: false,
                pagination: null,
                page: 1,
                keyword: '',
                useLoading: false,
            };
        },
        methods: {
            show(item) {
                this.dialogVisible = true;
                this.template = item;
            },
            handleClose() {
                this.template = null;
                this.dialogVisible = false;
            },
            toUse(item) {
                if (!item.is_use) {
                    this.$alert('没有该模板的使用权限，请联系管理员', '提示', {
                        type: 'warning'
                    });
                    return ;
                } else {
                    if (item.type == 'home') {
                        this.$confirm('选择加载的是首页布局的模板，会覆盖当前的首页布局们是否确定加载？', '提示', {
                            confirmButtonText: '确认',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.load(item);
                        })
                    } else {
                        this.load(item);
                    }
                }
            },
            load(item) {
                this.useLoading = true;
                request({
                    params: {
                        r: 'mall/model/loading',
                        template_id: item.id
                    }
                }).then(response => {
                    this.useLoading = false;
                    if (response.data.code == 0) {
                        this.$confirm('模板加载成功，是否前往编辑？', '提示', {
                            confirmButtonText: '确认',
                            cancelButtonText: '取消',
                            type: 'success'
                        }).then(() => {
                            if (item.type == 'diy') {
                                navigateTo({
                                    r: 'plugin/diy/mall/template/edit',
                                    id: response.data.data.id
                                },true);
                            } else {
                                navigateTo({
                                    r: 'mall/home-page/setting',
                                },true);
                            }
                        })
                    } else {
                        this.$alert(response.data.msg, '提示', {
                            type: 'warning'
                        });
                    }
                }).catch(response => {
                    this.useLoading = false;
                });
            },
            toBuy(id) {
                navigateTo({
                    r: 'mall/plugin/detail',
                    name: 'diy'
                });
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/model/index',
                        page: this.page,
                        keyword: this.keyword,
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code == 0) {
                        this.list = response.data.data.list;
                        this.pagination = response.data.data.pagination;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                });
            },
            getDetail(currentPage) {
                this.page = currentPage;
                this.loadData();
            },
        },
        mounted: function () {
            this.loadData();
        }
    });
</script>
