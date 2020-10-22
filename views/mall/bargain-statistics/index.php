<?php defined('YII_ENV') or exit('Access Denied');
$url = Yii::$app->urlManager->createUrl(Yii::$app->controller->route);
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

    .info-item-name {
        font-size: 14px;
        color: #92959B;
    }

    .select-item {
        border: 1px solid #3399ff;
        margin-top: -1px !important;
    }

    .el-popper .popper__arrow, .el-popper .popper__arrow::after {
        display: none;
    }

    .el-select-dropdown__item.hover, .el-select-dropdown__item:hover {
        background-color: #3399ff;
        color: #fff;
    }

    .sort-active {
        color: #3399ff;
    }
    .t-omit {
        display: block;
        white-space: nowrap;
        width: 80%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <app-header :url="url" :new-search="JSON.stringify(search)">砍价</app-header>
        </div>
        <div class="table-body">
            <app-search
                    @to-search="toSearch"
                    @search="searchList"
                    :new-search="search"
                    :is-show-platform="false"
                    placeholder="请输入商品名称搜索"
                    :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
            </app-search>
        </div>
        <div class="table-body" style="padding: 20px">
            <el-table @sort-change="changeSort"
                      :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '10px 0',fontWeight: '400'}"
                      :data="list">
                <el-table-column prop="name" width="330" label="商品名称">
                    <template slot-scope="scope">
                        <app-image style="margin-right: 10px;float: left;" :src="scope.row.cover_pic" width="32px"
                                   height="32px">
                        </app-image>
                        <div class="t-omit" style="margin-top: -3px;">{{scope.row.name}}</div>
                        <div style="font-size: 12px;color: #92959B;margin-top: -8px">{{scope.row.attr_groups}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="min_price" label="活动价格">
                </el-table-column>
                <el-table-column width="100" prop="initiator" label="发起人数"
                                 :label-class-name="prop == 'initiator' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column width="100" prop="participant" label="参与人数"
                                 :label-class-name="prop == 'participant' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column prop="min_price_goods" label="砍到最低商品数">
                </el-table-column>
                <el-table-column prop="underway" label="进行中活动数">
                </el-table-column>
                <el-table-column prop="success" label="成功活动数">
                </el-table-column>
                <el-table-column prop="fail" label="失败活动数">
                </el-table-column>
                <el-table-column width="100" prop="payment_people" label="支付人数"
                                 :label-class-name="prop == 'payment_people' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column width="100" prop="payment_num" label="支付件数"
                                 :label-class-name="prop == 'payment_num' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column width="100" prop="payment_amount" label="支付金额"
                                 :label-class-name="prop == 'payment_amount' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column prop="status" label="状态">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == '未开始'" style="color: #FFA360">{{scope.row.status}}</span>
                        <span v-if="scope.row.status == '进行中'" style="color: #4BC282">{{scope.row.status}}</span>
                        <span v-if="scope.row.status == '已结束'" style="color: #FF8585">{{scope.row.status}}</span>
                    </template>
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
                url: '<?= $url ?>',
                loading: false,
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 搜索内容
                search: {
                    time: null,
                    name: null,
                    platform: '',
                    order: null,
                },
                list: [],
                pagination: [],
                page: 1,
                prop: null
            };
        },
        methods: {
            changeSort(column) {
                this.loading = true;
                if (column.order == "descending") {
                    this.search.order = column.prop + ' DESC'
                } else if (column.order == "ascending") {
                    this.search.order = column.prop + ' ASC'
                } else {
                    this.search.order = null
                }
                this.prop = column.prop;
                this.getList();
            },
            // 切页
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            // 获取数据
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/bargain-statistics/index',
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        name: this.search.name,
                        order: this.search.order,
                        platform: this.search.platform,
                        page: this.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
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