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

    .table-area {
        margin: 10px 0;
        display: flex;
        justify-content: space-between;
    }

    .table-area .el-card {
        width: 100%;
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

    .sort-active {
        color: #3399ff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <app-header :url="url" :new-search="JSON.stringify(search)">发放统计</app-header>
        </div>
        <div class="table-body">
            <app-search
                @to-search="toSearch"
                @search="searchList"
                :new-search="search"
                :is-show-keyword="false"
                :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
                <template v-if="false" slot="select">
                    <el-select size="small" popper-class="select-item" @change="getList" style="width: 160px" filterable
                               v-model="search.type">
                        <el-option label="优惠券" value="coupon"></el-option>
                        <el-option label="卡券" value="card"></el-option>
                    </el-select>
                </template>
            </app-search>
        </div>
        <div class="table-area">
            <el-card v-loading="loading" shadow="never">
                <div slot="header">
                    <span>总成交</span>
                </div>
                <div class="num-info">
                    <div class="num-info-item">
                        <div>{{all.all_num}}</div>
                        <div class="info-item-name">发放总数</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.use_num}}</div>
                        <div class="info-item-name">已使用总数</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.unuse_num}}</div>
                        <div class="info-item-name">未使用总数</div>
                    </div>
                    <div class="num-info-item">
                        <div>{{all.end_num}}</div>
                        <div class="info-item-name">已失效总数</div>
                    </div>
                </div>
            </el-card>
        </div>
        <div class="table-body" style="padding: 20px">
            <el-table v-loading="loading" @sort-change="changeSort" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="list">
                <el-table-column prop="date" label="日期">
                </el-table-column>
                <el-table-column prop="name" :label="search.type == 'coupon' ? '优惠券名称':'卡券名称'">
                </el-table-column>
                <el-table-column prop="all_num" label="发放数量" sortable='custom' :label-class-name="chooseProp == 'all_num' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column prop="use_num" :label="search.type == 'coupon' ? '已使用数量':'已使用次数'" sortable='custom' :label-class-name="chooseProp == 'use_num' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column prop="unuse_num" :label="search.type == 'coupon' ? '未使用数量':'未使用次数'" sortable='custom' :label-class-name="chooseProp == 'unuse_num' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column prop="end_num" :label="search.type == 'coupon' ? '已失效数量':'已失效次数'" sortable='custom' :label-class-name="chooseProp == 'end_num' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column label='操作'>
                    <template slot-scope="scope">
                        <div v-if="scope.row.log_num > 0">
                            <form target="_blank" :action="cardDetailUrl" method="post">
                                <div>
                                    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                    <input name="flag" type="hidden" value="EXPORT">
                                    <input v-for="(item,index) in search"
                                           :name="index"
                                           type="hidden"
                                           :value="item">
                                    <input name="date" type="hidden" :value="scope.row.date">
                                    <input name="card_id" type="hidden" :value="scope.row.card_id">
         
                                </div>
                                <button type="submit" size="mini" class="el-button el-button--primary el-button--small">导出核销详情</button>
                            </form>
                        </div>
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
                        layout="prev, pager, next"
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
                url: '<?= $urlManager->createUrl('mall/send-statistics/index')?>',
                cardDetailUrl: '<?= $urlManager->createUrl('mall/send-statistics/card-detail-export')?>',
                all: {
                    all_num: '0',
                    unuse_num: '0',
                    use_num: '0',
                    end_num: '0',
                },
                loading: false,
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 搜索内容
                search: {
                    name: null,
                    time: null,
                    platform: '',
                    order: null,
                    type: "<?= $_GET['type'] ?>",
                },
                list: [],
                pagination: [],
                page: 1,
                chooseProp: null,
            };
        },
        methods: {
            changeSort(column) {
                this.loading = true;
                if(column.order == "descending") {
                    this.search.order = column.prop + ' DESC'
                }else if (column.order == "ascending") {
                    this.search.order = column.prop + ' ASC'
                }else {
                    this.search.order = null
                }
                this.chooseProp = column.prop;
                this.getList();
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
                        r: 'mall/send-statistics/index',
                        name: this.search.name,
                        type: this.search.type,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        order: this.search.order,
                        platform: this.search.platform,
                        page: this.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.all = e.data.data.all_data;
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