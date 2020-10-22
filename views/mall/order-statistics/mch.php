<?php defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
Yii::$app->loadViewComponent('statistics/app-search');
Yii::$app->loadViewComponent('statistics/app-header');
?>
<style>
    .el-tabs__nav-wrap::after {
        height: 1px;
    }

    .table-body {
        background-color: #fff;
        position: relative;
        margin-bottom: 10px;
        border: 1px solid #EBEEF5;
    }

    .table-body .search .el-tabs {
        margin-left: 10px;
    }

    .table-body .search .el-tabs__nav-scroll {
        width: 120px;
        margin-left: 30px;
    }

    .table-body .search .el-tabs__header {
        margin-bottom: 0;
    }

    .table-body .search .el-tabs__item {
        height: 32px;
        line-height: 32px;
    }

    .table-body .search .el-form-item {
        margin-bottom: 0
    }

    .table-body .search .clean {
        color: #92959B;
        margin-left: 20px;
        cursor: pointer;
        font-size: 15px;
    }

    .table-area {
        margin: 10px 0;
        display: flex;
        justify-content: space-between;
    }

    .table-area .el-card {
        width: 49.5%;
        color: #303133;
    }

    .num-info {
        display: flex;
        width: 100%;
        height: 60px;
        font-size: 24px;
        color: #303133;
        margin: 20px 0;
    }

    .num-info .num-info-item {
        text-align: center;
        flex-grow:  1;
        border-left: 1px dashed #EFF1F7;
    }

    .num-info .num-info-item:first-of-type {
        border-left: 0;
    }

    .info-item-name {
        font-size: 14px;
        color: #92959B;
    }
    
    .select-item {
        border: 1px solid #3399ff;
        margin-top: -1px!important;
    }

    .el-popper .popper__arrow, .el-popper .popper__arrow::after {
        display: none;
    }

    .el-select-dropdown__item.hover, .el-select-dropdown__item:hover {
        background-color: #3399ff;
        color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <app-header :url="url" :new-search="JSON.stringify(search)">多商户</app-header>
        </div>
        <div class="table-body">
            <app-search
                    @to-search="toSearch"
                    @search="searchList"
                    :new-search="search"
                    :is-show-keyword="false"
                    :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
                <template slot="select">
                    <div>
                        <el-select size="small" popper-class="select-item" @change="tabMch" style="width: 160px" filterable v-model="search.mch_id" placeholder="请输入搜索内容">
                            <el-option v-for="item in mch_list" :key="item.id" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                    </div>
                </template>
            </app-search>
        </div>
        <div class="table-area">
            <el-card shadow="never">
                <div slot="header">
                    <span>总成交</span>
                </div>
                <div class="num-info">
                    <div class="num-info-item">
                        <div>{{all.goods_num}}</div>
                        <div class="info-item-name">付款件数/件</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.user_num}}</div>
                        <div class="info-item-name">付款人数/人</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.total_pay_price}}</div>
                        <div class="info-item-name">付款金额/元</div>
                    </div>
                </div>
            </el-card>
            <el-card shadow="never">
                <div slot="header">
                    <span>今日实时成交</span>
                </div>
                <div class="num-info">
                    <div class="num-info-item">
                        <div>{{now.goods_num}}</div>
                        <div class="info-item-name">付款件数/件</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{now.user_num}}</div>
                        <div class="info-item-name">付款人数/人</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{now.total_pay_price}}</div>
                        <div class="info-item-name">付款金额/元</div>
                    </div>
                </div>
            </el-card>
        </div>
        <div class="table-body" style="padding: 20px">
            <el-tabs v-model="search.status" @tab-click="tab_order">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未完成订单" name="0"></el-tab-pane>
                <el-tab-pane label="已完成订单" name="1"></el-tab-pane>
                <el-tab-pane label="已取消订单" name="2"></el-tab-pane>
                <el-tab-pane label="售后中订单" name="3"></el-tab-pane>
                <el-tab-pane label="已完成售后订单" name="4"></el-tab-pane>
            </el-tabs>
            <el-table v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="list">
                <el-table-column prop="time" label="日期">
                </el-table-column>
                <el-table-column prop="user_num" label="付款人数">
                </el-table-column>
                <el-table-column prop="order_num" label="付款订单数">
                </el-table-column>
                <el-table-column prop="total_pay_price" label="付款金额">
                </el-table-column>
                <el-table-column prop="goods_num" label="付款件数">
                </el-table-column>
            </el-table>
            <div style="margin-top: 10px;" flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;float: right;"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper"
                            :current-page="pagination.current_page"
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
                url: '<?= $urlManager->createUrl('mall/order-statistics/index')?>',
                loading: false,
                list_loading: false,
                mch_name:'',
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 搜索内容
                search: {
                    mch_id: null,
                    time: null,
                    platform: '',
                    status: '-1',
                },
                all: [],
                list: [],
                mch_list: [],
                now: [],
                pagination: [],
                page: 1,
            };
        },
        methods: {
            tabMch() {
                for(let i = 0; i< this.mch_list.length;i++) {
                    if(this.mch_list[i].id == this.search.mch_id) {
                        this.mch_name = this.mch_list[i].name
                    }
                }
                this.getList();
            },
            // 切页
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            // 获取数据
            getList(flag) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/order-statistics/index',
                        status: this.search.status,
                        mch_id: this.search.mch_id,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        platform: this.search.platform,
                        page: this.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.mch_list = e.data.data.mch_list;
                        this.now = e.data.data.now;
                        this.pagination = e.data.data.pagination;
                        this.all = e.data.data.all;
                    }
                }).catch(e => {
                    this.loading = false;
                });             
            },
            // 切换订单状态
            tab_order() {
                this.list_loading = true;
                request({
                    params: {
                        r: 'mall/order-statistics/index',
                    },
                    data: {
                        status: this.search.status,
                        mch_id: this.search.mch_id,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        platform: this.search.platform,
                    },
                    method: 'post',
                }).then(e => {
                    this.list_loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.list_loading = false;
                });
            },
            toSearch(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },
            searchList(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },
        },
        created() {
            this.loading = true;
            request({
                params: {
                    r: 'mall/order-statistics/index',
                },
                data: {
                    status: this.search.status,
                    mch_id: this.search.mch_id,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    platform: this.search.platform
                },
                method: 'post',
            }).then(e => {
                if (e.data.code === 0) {
                    this.mch_list = e.data.data.mch_list;
                    this.search.mch_id = this.mch_list[0].id;
                    this.mch_name = this.mch_list[0].name;
                    this.getList();
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                this.loading = false;
            });
            let date = new Date();
            let timestamp = date.getTime();
            let seperator1 = "-";
            let year = date.getFullYear();
            let nowMonth = date.getMonth() + 1;
            let strDate = date.getDate();
            if (nowMonth >= 1 && nowMonth <= 9) {
                nowMonth = "0" + nowMonth;
            }
            if (strDate >= 0 && strDate <= 9) {
                strDate = "0" + strDate;
            }
            this.today = year + seperator1 + nowMonth + seperator1 + strDate;
            let week = new Date(timestamp - 6 * 24 * 3600 * 1000)
            let weekYear = week.getFullYear();
            let weekMonth = week.getMonth() + 1;
            let weekStrDate = week.getDate();
            if (weekMonth >= 1 && weekMonth <= 9) {
                weekMonth = "0" + weekMonth;
            }
            if (weekStrDate >= 0 && weekStrDate <= 9) {
                weekStrDate = "0" + weekStrDate;
            }
            this.weekDay = weekYear + seperator1 + weekMonth + seperator1 + weekStrDate;
            let month = new Date(timestamp - 29 * 24 * 3600 * 1000);
            let monthYear = month.getFullYear();
            let monthMonth = month.getMonth() + 1;
            let monthStrDate = month.getDate();
            if (monthMonth >= 1 && monthMonth <= 9) {
                monthMonth = "0" + monthMonth;
            }
            if (monthStrDate >= 0 && monthStrDate <= 9) {
                monthStrDate = "0" + monthStrDate;
            }
            this.monthDay = monthYear + seperator1 + monthMonth + seperator1 + monthStrDate;
        }
    })
</script>