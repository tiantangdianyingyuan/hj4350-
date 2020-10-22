<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>买单设置</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" size="small"
                               @click="$navigate({r: 'plugin/scan_code_pay/mall/activity/edit'})">
                        添加活动
                    </el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入活动名称搜索" v-model="keyword" clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table class="table-info" :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="name" label="活动名称"></el-table-column>
                <el-table-column :formatter="DateFormatter" label="活动日期"></el-table-column>
                <el-table-column prop="status" label="状态(是否启用)" width="150">
                    <template slot-scope="scope">
                        <el-switch :active-value="1"
                                   :inactive-value="0"
                                   @change="switchDefault(scope.row)"
                                   v-model="scope.row.status">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column fixed="right"  label="操作" width="200">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text"
                                   @click="$navigate({r: 'plugin/scan_code_pay/mall/activity/edit',activity_id:scope.row.id})">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button type="text" size="mini" @click="destroy(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination hide-on-single-page @current-change="pageChange" background layout="prev, pager, next, jumper"
                               :page-size="pagination.pageSize" :total="pagination.total_count"></el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                keyword: '',
                listLoading: false,
                form: [],
                page: 1,
                pagination: {},
                pageCount: 0,
            };
        },
        methods: {
            DateFormatter(row) {
                return row.start_time + '~' + row.end_time;
            },
            switchDefault(row) {
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/activity/switch-status',
                    },
                    method: 'post',
                    data: {
                        activity_id: row.id,
                        status: row.status
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            //删除
            destroy: function (column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/scan_code_pay/mall/activity/destroy'
                        },
                        data: {activity_id: column.id},
                        method: 'post'
                    }).then(e => {
                        this.listLoading = false;
                        this.getList();
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },

            search() {
                this.page = 1;
                this.getList();
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/activity/index',
                        page: this.page,
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>