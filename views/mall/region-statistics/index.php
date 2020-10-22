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

    .sort-active {
        color: #3399ff;
    }

    .select {
        float: left;
        width: 160px;
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <app-header :url="url" :new-search="JSON.stringify(search)">区域代理</app-header>
        </div>
        <div class="table-body">
            <app-search
                    @search="searchList"
                    :new-search="search"
                    :is-show-platform="false"
                    :is-show-keyword="false"
                    :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
                    <template slot="other">
                        <div style="margin-left: 20px;" flex="dir:left cross:center">
                            <div style="margin-right: 10px;">选择省份</div>
                            <el-select size="small" v-model="search.province_id" @change="getList" class="select">
                                <el-option v-for="item in province" :key="item.id" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                    </template>
            </app-search>
        </div>
        <div class="table-body" style="padding: 20px">
            <el-table v-loading="loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="list">
                <el-table-column width="600" prop="level" label="代理级别">
                </el-table-column>
                <el-table-column prop="rate" label="分红比例">
                </el-table-column>
                <el-table-column prop="price" label="分红总金额(元)">
                </el-table-column>
                <el-table-column prop="num" label="分红订单总数">
                </el-table-column>
            </el-table>
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
                    name: null,
                    time: null,
                    province_id: '',
                },
                list: [],
                now: [],
                pagination: [],
                province: [],
                page: 1,
                prop: null
            };
        },
        methods: {
            // 获取省份
            getPlace() {
                let self = this;
                request({
                    params: {
                        r: 'district/index',
                        level: 1
                    },
                    method: 'get'
                }).then(function (e) {
                    if (e.data.code == 0) {
                        self.province = e.data.data.district;
                        self.search.province_id = 2;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                })
            },
            // 获取数据
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/region-statistics/index',
                        province_id: this.search.province_id,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        if(e.data.data.model != null) {
                            this.list = [
                                {level:'省代理',rate: '', num: '', price:''},
                                {level:'市代理',rate: '', num: '', price:''},
                                {level:'区/县代理',rate: '', num: '', price:''}
                            ]
                            this.list[0].rate = e.data.data.model.province_rate + '%';
                            this.list[1].rate = e.data.data.model.city_rate + '%';
                            this.list[2].rate = e.data.data.model.district_rate + '%';
                            this.list[0].num = e.data.data.model.province_count;
                            this.list[1].num = e.data.data.model.city_count;
                            this.list[2].num = e.data.data.model.district_count;
                            this.list[0].price = '￥' + e.data.data.model.province_money;
                            this.list[1].price = '￥' + e.data.data.model.city_money;
                            this.list[2].price = '￥' + e.data.data.model.district_money;
                        }else {
                            this.list = e.data.data.model;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            searchList(searchData,e) {
                this.search = searchData;
                if(e == 'clean') {
                    this.search.province_id = 2;
                }
                this.getList();
            },
        },
        created() {
            this.getList();
            this.getPlace();
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
