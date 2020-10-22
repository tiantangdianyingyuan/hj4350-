<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 200px;
        margin: 0 0 20px 20px;
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
        padding: 15px;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>结算记录</span>
                <div style="float: right;margin: -5px 0">
                    <app-export-dialog :field_list='export_list' :params="searchData"
                                       @selected="exportConfirm"></app-export-dialog>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item">
                <el-input size="small" placeholder="请输入搜索内容" v-model="keyword">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column label="订单号" prop="order_no"></el-table-column>
                <el-table-column label="订单金额" prop="total_pay_price"></el-table-column>
                <el-table-column label="商品信息">
                    <template slot-scope="scope">
                        <div v-for="dItem in scope.row.detail">
                            <app-ellipsis :line="1">
                                {{dItem.goods.goodsWarehouse.name}}
                            </app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="结算状态">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.mchOrder.is_transfer == 0" content="未结算"
                                    placement="top">
                            <img src="statics/img/mall/ing.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.mchOrder.is_transfer == 1"
                                    content="已结算" placement="top">
                            <img src="statics/img/mall/already.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" width="180" label="订单创建时间"></el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                    date: '',
                    start_date: '',
                    end_date: '',
                },
                date: '',
                keyword: '',
                form: [],
                pageCount: 0,
                listLoading: false,
                export_list: [],
            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.date = this.date;
            },
            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.searchData.start_date = '';
                    this.searchData.end_date = ''
                }
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/mch/order-close-log',
                        page: this.page,
                        date: this.date,
                        user_id: getQuery('user_id'),
                        keyword: this.keyword,
                        start_date: this.searchData.start_date,
                        end_date: this.searchData.end_date,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.export_list = e.data.data.export_list;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            selectDateTime(e) {
                if (e != null) {
                    this.searchData.start_date = e[0];
                    this.searchData.end_date = e[1];
                }
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
