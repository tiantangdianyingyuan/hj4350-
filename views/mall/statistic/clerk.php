<?php defined('YII_ENV') or exit('Access Denied'); ?>
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

</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>核销员业绩</span>
        </div>
        <div class="table-body">
	        <el-form class="search" size="small" :inline="true" style="padding: 20px" :model="search">
                <!-- 搜索框 -->
                <el-form-item>
                    <el-select @change="tabMch" style="width: 160px" filterable v-model="search.mch" placeholder="请输入搜索内容">
                        <el-option label="全部" value="0"></el-option>
                        <el-option v-for="item in store_list" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                </el-form-item>
                <!-- 时间选择框 -->
            	<el-form-item>
	                <el-date-picker
                            style="width: 400px"
	                        v-model="search.time"
	                        type="daterange"
	                        value-format="yyyy-MM-dd"
	                        range-separator="至"
	                        start-placeholder="开始日期"
	                        end-placeholder="结束日期">
	                </el-date-picker>
	            </el-form-item>
            	<el-form-item>
	              	<el-tabs v-model="activeName" @tab-click="tab_total">
			   			<el-tab-pane label="7日" name="week"></el-tab-pane>
			    		<el-tab-pane label="30日" name="month"></el-tab-pane>
				  	</el-tabs>
				</el-form-item>
                <el-form-item>
                    <div class="clean" @click="clean">清空筛选</div>
                </el-form-item>
                <el-form-item style="float: right">
                    <el-button @click="exportForm" type="primary">导出报表</el-button>
                </el-form-item>
	        </el-form>
        </div>
        <div class="table-area">
            <el-card shadow="never">
                <div slot="header">
                    <span>总业绩</span>
                </div>
                <div class="num-info">
                    <div class="num-info-item">
                        <div>{{all.goods_num}}</div>
                        <div class="info-item-name">核销订单数/个</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.user_num}}</div>
                        <div class="info-item-name">核销件数/件</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.total_pay_price}}</div>
                        <div class="info-item-name">核销金额/元</div>
                    </div>
                </div>
            </el-card>
            <el-card shadow="never">
                <div slot="header">
                    <span>今日实时业绩</span>
                </div>
                <div class="num-info">
                    <div class="num-info-item">
                        <div>{{now.goods_num}}</div>
                        <div class="info-item-name">核销订单数/个</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{now.user_num}}</div>
                        <div class="info-item-name">核销件数/件</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{now.total_pay_price}}</div>
                        <div class="info-item-name">核销金额/元</div>
                    </div>
                </div>
            </el-card>
        </div>
        <div class="table-body" style="padding: 20px">
            <el-tabs v-model="orderSataus" @tab-click="tab_order">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未完成订单" name="0"></el-tab-pane>
                <el-tab-pane label="已完成订单" name="1"></el-tab-pane>
                <el-tab-pane label="已取消订单" name="2"></el-tab-pane>
                <el-tab-pane label="售后中订单" name="3"></el-tab-pane>
                <el-tab-pane label="已完成售后订单" name="4"></el-tab-pane>
            </el-tabs>
            <el-table v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '2px 0',fontWeight: '400'}" :data="list">
                <el-table-column prop="user_num" label="ID">
                </el-table-column>
                <el-table-column prop="user_num" label="核销人昵称">
                    <template slot-scope="scope">
                        <app-image style="margin-right: 10px;float: left;"  width="32px" height="32px">
                        </app-image>
                        <span style="height: 32px;line-height: 32px;display: inline-block">{{scope.row.user_num}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="order_num" label="联系方式">
                </el-table-column>
                <el-table-column prop="total_pay_price" label="核销订单数量" sortable>
                </el-table-column>
                <el-table-column prop="goods_num" label="核销卡券数量" sortable>
                </el-table-column>
                <el-table-column prop="goods_num" label="累计金额" sortable>
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
                loading: false,
                list_loading: false,
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 搜索内容
                search: {
                    mch: null,
                    time: null
                },
                activeName: null,
                all: [],
                export_list: [],
                list: [],
                now: [],
                pagination: [],
                page: 1,
                orderSataus: '-1',
                store_list: [],
                store_id: null,
                date_start: null,
                date_end: null,
                flag: null,
                fields: null
            };
        },
        methods: {
            tabMch() {
                this.getList();
            },
            // 导出
            exportForm() {

            },
            // 切页
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            // 获取数据
        	getList() {
        		this.loading = true;
                request({
                    params: {
                        r: 'mall/order-statistics/index',
                        page: this.page,
                        status: this.status,
                        store_id: this.search.mch,
                        date_start: this.date_start,
                        date_end: this.date_end,
                        flag: this.flag,
                        fields: this.fields
                    },
                    method: 'get',
                }).then(e => {
        			this.loading = false;
                    if (e.data.code == 0) {
                        this.all = e.data.data.all;
                        this.export_list = e.data.data.export_list;
                        this.list = e.data.data.list;
                        this.now = e.data.data.now;
                        this.pagination = e.data.data.pagination;
                        this.store_list = e.data.data.store_list;
                    } else {
                        this.$message.error(e.data.msg);
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
                        page: 1,
                        status: this.orderSataus,
                        date_start: this.date_start,
                        date_end: this.date_end,
                    },
                    method: 'post',
                }).then(e => {
                    this.list_loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.list_loading = false;
                });
        	},

            changeTime() {
                if(this.search.time) {
                    this.date_start = this.search.time[0];
                    this.date_end = this.search.time[1];
                }else {
                    this.date_start = [];
                    this.date_end = [];
                }
                this.getList();
            },
            // 选择时间区间
            tab_total() {
                if(this.activeName == 'week') {
                    this.date_start = this.weekDay;
                    this.date_end = this.today;
                    this.search.time = [this.weekDay, this.today]
                }
                if(this.activeName == 'month') {
                    this.date_start = this.monthDay;
                    this.date_end = this.today;
                    this.search.time = [this.monthDay, this.today]
                }
                this.getList();
            },
            clean() {
                this.search = {
                    mch: null,
                    time: null
                }
                this.date_start = null;
                this.date_end = null;
                this.activeName = null;
                this.getList();       
            }
        },
        created() {
        	this.getList();
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