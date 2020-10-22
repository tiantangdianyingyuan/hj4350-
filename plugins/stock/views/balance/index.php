<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
?>
<style>
    .el-tabs__header {
        font-size: 16px;
    }
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .select {
        position: relative;
    }

    .select .el-input__inner {
        padding-left: 30px;
    }

    .select .date-icon {
        position: absolute;
        height: 100%;
        width: 25px;
        left: 5px;
        top: 0;
        color: #C0C4CC;
    }

    .select .date-icon .el-icon-date {
        line-height: 32px;
        height: 100%;
        width: 25px;
        text-align: center;
    }
    .el-select .el-input .el-icon-arrow-up {
        display: none;
    }
    .el-tag {
        border-color: #409EFF;
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>分红结算</span>
            <div style="float: right;margin-top: -5px;">
                <el-button type="primary" @click="toAdd" size="small">新增结算</el-button>
            </div>
        </div>
        <div class="table-body">
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div flex="dir:left cross:center">
                    <div style="margin-right: 10px;">结算时间</div>
                    <el-date-picker
                            size="small"
                            style="margin-right: 15px;"
                            v-model="year"
                            type="year"
                            @change="chooseYear"
                            format="yyyy年"
                            value-format="yyyy"
                            placeholder="选择年">
                    </el-date-picker>
                    <el-date-picker
                            size="small"
                            style="margin-right: 15px;"
                            v-model="month"
                            type="month"
                            :default-value="choose"
                            @change="chooseMonth"
                            format="M月"
                            value-format="M"
                            placeholder="选择月">
                    </el-date-picker>
                    <div class="select">
                        <el-select @change="changeWeek" size="small" clearable placeholder="选择周" v-model="week">
                            <el-option key="1" label="第一周" value="1"></el-option>
                            <el-option key="2" label="第二周" value="2"></el-option>
                            <el-option key="3" label="第三周" value="3"></el-option>
                            <el-option key="4" label="第四周" value="4"></el-option>
                        </el-select>
                        <div class="date-icon">
                            <i class="el-input__icon el-icon-date"></i>
                        </div>
                    </div>
                </div>
            </div>
            <el-table :data="list" border v-loading="loading" style="margin-bottom: 15px;">
                <el-table-column label="结算周期" width="400">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <el-tag size="small" v-if="scope.row.bonus_type == 1">按周</el-tag>
                            <el-tag size="small" v-else>按月</el-tag>
                            <div>{{scope.row.start_time}}~{{scope.row.end_time}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="订单数" prop="order_num"></el-table-column>
                <el-table-column label="分红金额" prop="price" width="350">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.bonus_price}}({{scope.row.bonus_rate}}%分红比例)</div>
                    </template>
                </el-table-column>
                <el-table-column label="股东数" prop="stock_num"></el-table-column>
                <el-table-column label="创建时间" prop="created_at" width="250"></el-table-column>
                <el-table-column label="操作" width="250px" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" size="mini" circle style="margin-top: 10px" @click.native="detail(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="查看详情" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    background :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                    :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                pagination: {},
                choose: '',
                loading: false,
                start_date: '',
                end_date: '',
                year: '',
                month: '',
                week: '',
                nowYear: '',
                nowMonth: '',
            };
        },
        created() {
            let date = new Date();
            this.nowYear = date.getFullYear();
            this.nowMonth = date.getMonth() + 1;
            this.loadData();
        },
        methods: {
            loadData(page) {
                this.loading = true;
                this.start_date = '';
                this.end_date = '';
                if((this.year != '' && this.year != null) || (this.month != '' && this.month != null) || (this.week != '' && this.week != null)) {
                    let firstMonth = this.month ? this.month : 1;
                    let lastMonth = this.month ? this.month : 12;
                    if(firstMonth < 10) {
                        firstMonth = '0'+firstMonth
                    }
                    if(lastMonth < 10) {
                        lastMonth = '0'+lastMonth
                    }
                    let firstDay = '01';
                    let lastDay = '31';
                    if(this.week == 1) {
                        firstDay = '01';
                        lastDay = '07';
                    }else if(this.week == 2) {
                        firstDay = '08';
                        lastDay = '14';
                    }else if(this.week == 3) {
                        firstDay = '15';
                        lastDay = '21';
                    }else if(this.week == 4) {
                        firstDay = '22';
                        lastDay = '31';
                    }
                    this.start_date = this.year + '-' + firstMonth + '-' + firstDay;
                    this.end_date = this.year + '-' + lastMonth + '-' + lastDay;
                }
                request({
                    params: {
                        r: 'plugin/stock/mall/balance/index',
                        page: page ? page : 1,
                        start_date: this.start_date,
                        end_date: this.end_date,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data;
                        this.pagination = e.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.loading = true;
                this.loadData(page);
            },
            chooseYear(e) {      
                this.choose = e;
                if(e != null && !(this.week != '' && this.week != null && this.month == null)) {
                    this.loadData();
                }else if(e == null && (this.week == null || this.week == '') && (this.month == null || this.month == '')) {
                    this.loadData();
                }
            },
            chooseMonth(e) {
                let that = this;
                if(e != null) {
                    if(that.year == '' || that.year == null) {
                        that.year = that.nowYear.toString();
                    }
                }
                if(that.week != '' && that.week != null && e == null) {
                    return false;
                }
                that.loadData();
            },
            changeWeek(e) {
                let that = this;
                if(e != null) {
                    if(that.year == '' || that.year == null) {
                        that.year = that.nowYear.toString();
                    }
                    if(that.month == '' || that.month == null) {
                        that.month = that.nowMonth.toString();
                    }
                    that.loadData();
                }
            },

            toAdd() {
                navigateTo({
                    r: 'plugin/stock/mall/balance/add'
                });
            },
            detail(id) {
                navigateTo({
                    r: 'plugin/stock/mall/balance/detail',
                    id: id
                });
            },
        }
    });
</script>
