<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 14:12
 */
Yii::$app->loadViewComponent('order/app-search');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
    .like-a {
        color: #3399ff;
        cursor: pointer;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>团购订单</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <app-export-dialog action_url='index.php?r=plugin/community/mall/order/index' :field_list='export_list' :params="search" @selected="confirmSubmit">
                    </app-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">
            <app-search
                    @search="toSearch"
                    :tabs="tabs"
                    :is-show-order-type="false"
                    :is-show-platform="false"
                    :select-list="selectList"
                    :active-name="activeName">
                <template slot-scope="scope">
                    <div flex="dir:left cross:center">
                        <div class="label">活动名称</div>
                        <div>
                            <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入活动名称搜索"
                                      v-model="activity_name" clearable @clear="clearSearch">
                                <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                            </el-input>
                        </div>
                    </div>
                </template>
            </app-search>
            <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px;">
                <el-table-column label="订单号" prop="order_no" width="200">
                    <template slot-scope="scope">
                        <div class="like-a" @click="toOrder(scope.row)">{{scope.row.order_no}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="所属团长" prop="middleman_name">
                    <el-table-column label="手机号" prop="middleman_mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.middleman_name}}</div>
                            <div>{{scope.row.middleman_mobile}}</div>
                        </template>
                    </el-table-column>
                </el-table-column>
                <el-table-column label="买家昵称" prop="name">
                    <el-table-column label="买家手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.name}}</div>
                            <div>{{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>
                </el-table-column>
                <el-table-column label="活动名称" prop="activity_name"></el-table-column>
                <el-table-column label="支付金额(元)" prop="pay_price"></el-table-column>
                <el-table-column label="团长利润(元)" prop="profit_price"></el-table-column>
                <el-table-column label="订单状态" width="150" prop="time_status">
                    <template slot-scope="scope">
                        <el-tag size="small" type="warning" v-if="scope.row.cancel_status == 1">已取消</el-tag>
                        <el-tag size="small" type="warning" v-else-if="scope.row.cancel_status == 2">待处理</el-tag>
                        <el-tag size="small" type="warning" v-else-if="scope.row.is_sale == 1">已完成</el-tag>
                        <el-tag size="small" type="warning" v-else-if="scope.row.is_confirm == 1">已收货</el-tag>
                        <el-tag size="small" type="warning" v-else-if="scope.row.is_send == 1">待收货</el-tag>
                        <el-tag size="small" type="danger" v-else-if="scope.row.is_pay == 1">待发货</el-tag>
                        <el-tag size="small" type="danger" v-else-if="scope.row.is_pay == 0">未付款</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="利润结算状态" width="220" prop="time_status">
                    <template slot-scope="scope">
                        <div v-if="scope.row.cancel_status == 1">不结算</div>
                        <div v-else-if="scope.row.is_sale == 1">已结算</div>
                        <div v-else-if="scope.row.is_sale == 0">待结算</div>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
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
                export_list: [],
                search: {
                    keyword: '',
                    page: 1,
                    keyword_1: 'order_no'
                },
                activity_name: '',
                activeName: '-1',
                tabs: [
                    {value: '-1', name: '全部'},
                    {value: '0', name: '未付款'},
                    {value: '1', name: '待发货'},
                    {value: '2', name: '待收货'},
                    {value: '3', name: '已收货'},
                    {value: '4', name: '已完成'},
                    {value: '5', name: '待处理'},
                    {value: '6', name: '已取消'},
                ],
                selectList: [
                    {value: 'order_no', name: '订单号'},
                    {value: 'user_id', name: '团长ID'},
                    {value: 'middleman_name', name: '团长昵称'},
                    {value: 'middleman_mobile', name: '团长手机号'},
                    {value: 'mobile', name: '买家手机号'},
                    {value: 'name', name: '买家昵称'},
                ],
                isShowOrderType: false,
                pagination: null
            }
        },
        created() {
            this.loadData();
        },
        methods: {
            // 获取状态
            confirmSubmit() {
                this.search.status = this.activeName
            },
            toOrder(row) {
                this.$navigate({
                    r: 'mall/order/detail',
                    order_id: row.id
                });
            },
            clearSearch() {
                this.activity_name = '';
                this.loadData();
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/order/index',
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        status: this.search.status,
                        activity_name: this.activity_name,
                        keyword: this.search.keyword,
                        keyword_1: this.search.keyword_1,
                        page: this.page
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.export_list = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(currentPage) {
                let self = this;
                self.page = currentPage;
                self.loadData();
            },
            toSearch(e) {
                this.search = e;
                this.loadData();
            }
        }
    });
</script>