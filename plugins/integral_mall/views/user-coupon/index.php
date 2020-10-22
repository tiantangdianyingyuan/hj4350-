<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 260px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>用户兑换券</span>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input size="small" @keyup.enter.native="search" placeholder="请输入优惠券名称或用户名搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width='80' prop="id" label="ID"></el-table-column>
                <el-table-column width='300' prop="name" label="优惠券名称">
                    <template slot-scope="scope">
                        <span>{{scope.row.integralMallCoupon.coupon.name}}</span>
                    </template>
                </el-table-column>
                <el-table-column width='260' prop="nickname" label="用户">
                    <template slot-scope="scope">
                        <app-image :src="scope.row.user.userInfo.avatar"
                                   style="float: left;margin-right: 10px"
                                   height="50px" width="50px">
                        </app-image>
                        <span style="height: 50px;line-height: 50px;">{{scope.row.user.nickname}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="sub_price" label="优惠方式">
                    <template slot-scope="scope">
                        <div v-if="scope.row.integralMallCoupon.coupon.type == 2">满{{scope.row.integralMallCoupon.coupon.min_price}}元减{{scope.row.integralMallCoupon.coupon.sub_price}}元</div>
                        <div v-if="scope.row.integralMallCoupon.coupon.type == 1">{{scope.row.integralMallCoupon.coupon.discount}}折</div>
                        <div v-if="scope.row.integralMallCoupon.coupon.discount_limit && scope.row.integralMallCoupon.coupon.type == 1">优惠上限:{{scope.row.integralMallCoupon.coupon.discount_limit}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="sub_price" label="兑换积分+兑换金额">
                    <template slot-scope="scope">
                        <span>{{scope.row.integralMallCoupon.integral_num}}+￥{{scope.row.integralMallCoupon.price}}元</span>
                    </template>
                </el-table-column>
                <el-table-column width='330' prop="expire_type" label="有效时间">
                    <template slot-scope="scope">
                        {{scope.row.userCoupon.start_time}}至{{scope.row.userCoupon.end_time}}
                    </template>
                </el-table-column>
                <el-table-column width='120' prop="expire_type" label="状态">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.userCoupon.is_expired == 1" content="已过期" placement="top">
                            <img src="statics/img/mall/expired.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.userCoupon.is_use == 0" content="未使用" placement="top">
                            <img src="statics/img/mall/ing.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.userCoupon.is_use == 1" content="已使用" placement="top">
                            <img src="statics/img/mall/already.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination v-if="pagination"
                                   :page-size="pagination.pageSize" style="display: inline-block;float: right;"
                                   background @current-change="pageChange" layout="prev, pager, next, jumper"
                                   :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                list: [],
                keyword: "",
                pagination: null,
                params: { // get请求参数
                    r: 'plugin/integral_mall/mall/couponUser/index'
                }
            };
        },

        methods: {
            search() {
                this.params.page = 1;
                this.params.keyword = this.keyword;
                this.loadData();
            },
            //分页
            pageChange(page) {
                this.params.page = page;
                this.loadData();
            },
            // 根据参数获取请求信息
            loadData() {
                this.list = [];
                this.loading = true;
                request({
                    params: this.params,
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.listLoading = false;
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            }
        },
        created() {
            if(getQuery('coupon')){
                this.keyword = getQuery('coupon')
                this.params.keyword = this.keyword;
            }
            this.loadData();
        }
    })
</script>