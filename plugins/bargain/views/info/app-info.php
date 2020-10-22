<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/17
 * Time: 15:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .goods-info {
        margin-top: 5px;
        position: relative;
    }

    .goods-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 400px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
    /*    background-color: #fff;*/
        border-left: 1px solid #DCDFE6;
    /*    width: 10%;*/
        padding: 0 15px;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .title {
        background-color: #F3F5F6;
        height: 40px;
        line-height: 40px;
        display: flex;
    }

    .title div {
        text-align: center;
    }

    .title+.el-card .el-card__body .el-card {
        border: 0;
    }

    .bargain-info {
    }

    .bargain-item-head {
        background-color: #F3F5F6;
        padding: 0;
    }

    .platform-img {
        margin-top: -2px;
        float: left;
        display: block;
        margin-right: 5px;
    }

    .price-info {
        flex-wrap: wrap;
        display: flex;
    }

    .item-center {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .price-info img {
        margin-right: 5px;
    }

    .price-info div {
        display: flex;
        margin-right: 4%;
        margin-bottom: 5px;
        align-items: center;
    }

    .price-info div:last-of-type {
        margin-right: 0;
    }

    .detail-item {
        height: 60px;
        padding: 0 15px;
        margin-bottom: 20px;
        font-size: 16px;
        line-height: 60px;
    }

    .detail-item span {
        display: inline-block;
        width: 50%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .load-more {
        height: 60px;
        width: 100%;
        text-align: center;
        line-height: 60px;
        font-size: 16px;
        color: #3399ff;
        cursor: pointer;
    }

    .el-dialog__body {
        padding-bottom: 10px;
    }

    .el-dialog {
        min-width: 600px;
    }
</style>
<link rel="stylesheet" href="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/css/style.css">

<template id="app-info" v-cloak>
    <el-card class="app-info" v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item v-if="type == 'single'">
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/mall/goods/index'})">
                        砍价活动
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>活动数据</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <!-- 状态选择 -->
            <el-tabs v-model="search.status" @tab-click="handleClick">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="砍价中" name="0"></el-tab-pane>
                <el-tab-pane label="砍价成功" name="1"></el-tab-pane>
                <el-tab-pane label="砍价失败" name="2"></el-tab-pane>
            </el-tabs>
            <el-form size="small" :inline="true" :model="search">
                <el-form-item style="display: none">
                    <el-input></el-input>
                </el-form-item>
                <el-form-item label="发起时间:">
                    <el-date-picker
                            class="item-box"
                            size="small"
                            @change="changeTime"
                            v-model="search.time"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <div class="input-item">

                        <el-input v-model="search.prop_value" placeholder="请输入搜索内容" clearable @clear="commonSearch"
                                  @keyup.enter.native="commonSearch">
                            <el-select style="width: 130px" slot="prepend" v-model="search.prop">
                                <el-option key="nickname" label="发起人昵称" value="nickname"></el-option>
                                <el-option key="user_id" label="发起人用户ID" value="user_id"></el-option>
                                <el-option key="name" label="商品名称" value="name" v-if="type == 'all'"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="commonSearch"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <template v-if="type == 'single'">
                <div flex="cross:center box:first" style="margin-bottom: 15px;width: 480px;" v-if="goods">
                    <div>商品信息:</div>
                    <div flex="dir:left box:first" style="border: 1px solid #eeeeee; padding: 10px;margin-left: 10px;">
                        <app-image :src="goods.cover_pic" width="60px" height="60px" style="margin-right: 15px;"></app-image>
                        <div>
                            <app-ellipsis :line="2">{{goods.name}}</app-ellipsis>
                        </div>
                    </div>
                </div>
            </template>
            <el-table :data="list" border>
                <el-table-column label="砍价信息">
                    <template slot-scope="scope">
                        <div class="bargain-info" flex="dir:left box:first">
                            <app-image :src="scope.row.goods.cover_pic" width="60px" height="60px" style="margin-right: 15px;" v-if="type == 'all'"></app-image>
                            <view v-else></view>
                            <div class="goods-info">
                                <div class="goods-name" v-if="type == 'all'">{{scope.row.goods.name}}</div>
                                <div class="price-info">
                                    <div>
                                        <el-tooltip class="item" effect="dark" content="售价" placement="top">
                                            <img src="statics/img/plugins/price.png" alt="">
                                        </el-tooltip>
                                        <span>￥{{scope.row.price}}</span>
                                    </div>
                                    <div>
                                        <el-tooltip class="item" effect="dark" content="最低价" placement="top">
                                            <img src="statics/img/plugins/low.png" alt="">
                                        </el-tooltip>
                                        <span>￥{{scope.row.min_price}}</span>
                                    </div>
                                    <div>
                                        <el-tooltip class="item" effect="dark" content="当前价" placement="top">
                                            <img src="statics/img/plugins/now.png" alt="">
                                        </el-tooltip>
                                        <span>￥{{scope.row.now_price}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="发起时间" width="180"></el-table-column>
                <el-table-column prop="status" label="状态" width="130">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status == 0">进行中</el-tag>
                        <el-tag v-if="scope.row.status == 1" type="success">砍价成功</el-tag>
                        <el-tag v-if="scope.row.status == 2" type="danger">砍价失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="join_list" label="参与详情">
                    <template slot-scope="scope">
                        <div class="item-center" style="justify-content: flex-start">
                            <app-image style="margin-right: 20px" :src="value.avatar" width="60px" height="60px" v-for="value in scope.row.join_list"></app-image>
                            <el-tooltip class="item" effect="dark" content="参与详情" v-if="scope.row.join_list.length != 0" placement="top">
                                <img style="cursor: pointer;height: 32px;width: 32px;" @click="openList(scope.row)" src="statics/img/mall/order/detail.png"></img>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div style="visibility: hidden">
                </div>
                <div>
                    <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :page-size="pagination.pageSize"
                        :current-page.sync="pagination.current_page"
                        :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
            <el-dialog title="参与详情" :visible.sync="dialogVisible" width="30%">
                <div v-for="value in detail_list" class="detail-item">
                    <app-image style="margin-right: 20px;float: left;" :src="value.avatar" width="60px" height="60px"></app-image>
                    <span>{{value.nickname}}</span>
                    <div style="float: right">砍了￥{{value.price}}</div>
                </div>
                <div @click="more" v-if="detail.length > 5 && clickMore" class="load-more">加载更多...</div>
            </el-dialog>
        </div>
    </el-card>
</template>
<script>
    Vue.component('app-info', {
        template: '#app-info',
        props: {
            type: String,
        },
        data() {
            return {
                clickMore: true,
                dialogVisible: false,
                search: {
                    r: 'plugin/bargain/mall/info/index',
                    keyword: '',
                    prop: 'nickname',
                    prop_value: '',
                    status: '-1',
                    page: 1,
                    id: '',
                    date_start: '',
                    date_end: '',
                    time: '',
                },
                list: [],
                detail: [],
                activeName: '0',
                listLoading: false,
                pagination: {},
                detail_list: [],
                goods: null,
            };
        },
        created() {
            if (getQuery('id')) {
                this.search.id = getQuery('id');
            }
            this.getList();
        },
        methods: {
            openList(row) {
                this.detail = row.user_list;
                this.detail_list = row.user_list.slice(0,5);
                this.clickMore = true;
                this.dialogVisible = !this.dialogVisible;
            },

            more() {
                this.clickMore = !this.clickMore;
                this.detail_list = this.detail;
            },

            pageChange(currentPage) {
                let self = this;
                self.search.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: self.search,
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.listLoading = false;
                        self.list = e.data.data.list;
                        for(let i = 0;i < self.list.length;i++) {
                            self.list[i].join_list = self.list[i].user_list.slice(0,3)
                        }
                        self.goods = e.data.data.goods;
                        self.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(e) {
                this.list = [];
                this.getList();
            },
            // 搜索
            commonSearch() {
                this.search.page = 1;
                this.getList();
            },
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.commonSearch();
            },
        }
    });
</script>

