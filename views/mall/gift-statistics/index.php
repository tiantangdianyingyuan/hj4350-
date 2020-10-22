<?php defined('YII_ENV') or exit('Access Denied');
$url = Yii::$app->urlManager->createUrl(Yii::$app->controller->route)
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
        margin-top: -1px!important;
    }

    .el-popper .popper__arrow, .el-popper .popper__arrow::after {
        display: none;
    }

    .el-select-dropdown__item.hover, .el-select-dropdown__item:hover {
        background-color: #3399ff;
        color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0;
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
        padding: 15px;
    }

    .sort-active {
        color: #3399ff;
    }
    .name {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
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
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>社交送礼</span>
        </div>
        <div class="table-body" style="display:flex;justify-content: space-between;align-items: center;padding-right: 20px">
            <el-form class="search" size="small" :inline="true" style="padding: 20px" :model="search">

                <!-- 时间选择框 -->
                <el-form-item>
                    <el-date-picker
                        @change="changeTime"
                        style="width: 365px"
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
                    <div class="input-item">
                        <el-input @keyup.enter.native="searchKeyword" size="small" placeholder="输入商品名称搜索" v-model="search.mch">
                            <el-button slot="append" icon="el-icon-search" @click="searchKeyword"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item>
                    <div class="clean" @click="clean">清空筛选</div>
                </el-form-item>
            </el-form>
            <form target="_blank" :action="url" method="post">
                <div>
                    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <input name="flag" type="hidden" value="EXPORT">
                    <input name="date_start" type="hidden" :value="date_start">
                    <input name="date_end" type="hidden" :value="date_end">
                    <input name="name" type="hidden" :value="search.mch">
                    <input name="order" type="hidden" :value="order">
                </div>
                <div flex="dir:right" style="">
                    <button type="submit" class="el-button el-button--primary el-button--small">导出报表</button>
                </div>
            </form>
        </div>
        <div class="table-body" style="padding: 20px">
            <el-table @sort-change="changeSort" v-loading="loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="list">
                <el-table-column width="600" prop="goods" label="商品名称">
                    <template slot-scope="scope">
                        <app-image style="margin-right: 10px;float: left;" :src="scope.row.cover_pic"  width="32px" height="32px">
                        </app-image>
                        <span class="name t-omit" style="height: 32px;line-height: 32px;display: inline-block">{{scope.row.name}}</span>
                        <span style="height: 32px;line-height: 32px;display: inline-block">{{scope.row.attr}}</span>
                    </template>
                </el-table-column>


                <el-table-column prop="goods_num" label="支付件数" :label-class-name="prop == 'goods_num' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column prop="total_price" label="支付金额" :label-class-name="prop == 'total_price' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column prop="user_num" label="支付人数" :label-class-name="prop == 'user_num' ? 'sort-active': ''" sortable='custom'>
                </el-table-column>
                <el-table-column prop="convert_num" label="领取件数" :label-class-name="prop == 'convert_num' ? 'sort-active': ''" >
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
                    mch: null,
                    time: null,
                    platform: '',
                },
                activeName: null,
                list: [],
                now: [],
                pagination: [],
                page: 1,
                date_start: null,
                date_end: null,
                order: null,
                prop: null
            };
        },
        methods: {
            changeSort(column) {
                this.loading = true;
                if(column.order == "descending") {
                    this.order = column.prop + ' DESC'
                }else if (column.order == "ascending") {
                    this.order = column.prop + ' ASC'
                }else {
                    this.order = null
                }
                this.prop = column.prop;
                this.getList();
            },

            searchKeyword() {
                this.getList();
            },

            tabMch() {
                this.getList();
            },
            // 切页
            pageChange(currentPage) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/gift-statistics/index',
                        page: currentPage,
                        name: this.search.mch,
                        order: this.order,
                        date_start: this.date_start,
                        date_end: this.date_end,
                        platform: this.search.platform,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 获取数据
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/gift-statistics/index',
                    },
                    data: {
                        name: this.search.mch,
                        order: this.order,
                        date_start: this.date_start,
                        date_end: this.date_end,
                        platform: this.search.platform
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
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
                this.activeName = null;
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
                    time: null,
                    platform: '',
                };
                this.date_start = null;
                this.date_end = null;
                this.activeName = null;
                this.getList();
            },
            toSearch() {
                this.page = 1;
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
            let week = new Date(timestamp - 6 * 24 * 3600 * 1000);
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