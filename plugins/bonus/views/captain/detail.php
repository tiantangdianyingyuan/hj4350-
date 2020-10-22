<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-goods');
?>
<style>
    .info-item div {
        height: 20px;
        line-height: 20px;
    }

    .title {
        font-size: 16px;
        margin-bottom: 20px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
        margin-top: 10px;
    }

    .input-item {
        display: inline-block;
        width: 350px;
        margin-bottom: 20px;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
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
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/bonus/mall/captain/index'})">队长管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>队员列表</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-card shadow="never">
            <div class="title">队长信息</div>
            <div flex="dir:left cross:center">
                <div flex="dir:left cross:center" style="height: 70px;border-right: 1px dashed #e2e2e2;width: 500px">
                    <div>
                        <img style="height: 70px;width: 70px;margin: 0 25px;" :src="captain.avatar" alt="">
                    </div>
                    <div class="info-item">
                        <div>昵称：{{captain.nickname}}</div>
                        <div>姓名：{{captain.name}}</div>
                        <div>手机号：{{captain.mobile}}</div>
                    </div>
                </div>
                <div style="margin-left: 60px">
                    <div v-if="captain.level">
                        <span style="font-size: 14px">{{captain.level.name}}</span>
                        <el-button type="text" circle @click="changeLevel">
                            <img src="statics/img/mall/order/edit.png" alt="">
                        </el-button>
                    </div>
                    <div>队员：{{captain.all_member}}人</div>
                </div>
            </div>
        </el-card>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="toSearch">
                    <el-select size="small" v-model="type" slot="prepend" class="select">
                        <el-option key="3" label="用户ID" value="3"></el-option>
                        <el-option key="1" label="昵称" value="1"></el-option>
                        <el-option key="2" label="手机号" value="2"></el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>
            <el-table :data="list" border>
                <el-table-column prop="user_id" width="100" label="ID"></el-table-column>
                <el-table-column label="基本信息">
                    <template slot-scope="scope">
                        <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.avatar"></app-image>
                        <div style="height: 90px;line-height: 90px;">{{scope.row.nickname}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机号"></el-table-column>
                <el-table-column prop="bonus_price" label="贡献分红金额"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button circle size="small" type="text"
                                   @click="$navigate({r:'plugin/bonus/mall/order/index', nickname:scope.row.nickname, captain_id: captain_id,name: captain.name})">
                            <el-tooltip class="item" effect="dark" content="订单" placement="top">
                                <img src="statics/img/mall/share/order.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        :page-size="pagination.pageSize"
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog title="修改队长等级" :visible.sync="toChange" width="30%">
        <el-form>
            <el-form-item label="队长等级">
                <el-select size="small" style="width: 70%;" v-model="value" placeholder="请选择">
                    <el-option label="无" value="0"></el-option>
                    <el-option
                        v-for="item in member"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                    </el-option>
                </el-select>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="toChange = false">取 消</el-button>
            <el-button size="small" type="primary" @click="changeSubmit" :loading="contentBtnLoading">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                member: [],
                toChange: false,
                contentBtnLoading: false,
                captain: {},
                pagination: null,
                keyword: '',
                loading: false,
                captain_id: null,
                value: '',
                type: '3'
            };
        },
        created() {
            this.captain_id = getQuery('captain_id');
            this.getList();
            this.getMember();
        },
        methods: {
            getMember() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/bonus/mall/members/all-member'
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.member = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            changeLevel() {
                this.toChange = true;
                this.value = +this.captain.level.id;
            },
            changeSubmit() {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/bonus/mall/captain/level'
                    },
                    data: {
                        user_id: this.captain.user_id,
                        level: this.value,
                    },
                    method: 'post',
                }).then(e => {
                    this.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        this.toChange = false;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page){
                this.list = []
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/bonus/mall/order/team-bonus',
                        captain_id: this.captain_id,
                        keyword_1: this.type,
                        keyword: this.keyword,
                        page: page
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.captain = e.data.data.captain;
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.pageCount = e.data.data.pagination.page_count;
                    }
                })
            },
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/bonus/mall/order/team-bonus'
                    },
                    data: {
                        captain_id: this.captain_id,
                        keyword_1: this.type,
                        keyword: this.keyword,
                    },
                    method: 'post'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.captain = e.data.data.captain;
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.pageCount = e.data.data.pagination.page_count;
                    }else {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定',
                            callback: action => {
                                window.history.go(-1);
                            }
                        });
                    }
                })
            },
            toSearch() {
                this.list = [];
                this.getList();
            }
        }
    });
</script>
