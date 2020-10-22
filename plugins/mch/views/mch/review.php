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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .table-body .el-form-item {
        margin-bottom: 0;
    }

    .el-alert {
        padding: 0;
        padding-left: 5px;
        padding-bottom: 5px;
    }

    .el-alert--info .el-alert__description {
        color: #606266;
    }

    .el-alert .el-button {
        margin-left: 20px;
    }
</style>

<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商户审核列表</span>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input  @keyup.enter.native="searchList" size="small" placeholder="请输入店铺名称" v-model="keyword" clearable @clear='searchList'>
                    <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                </el-input>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="待审核" name="first"></el-tab-pane>
                <el-tab-pane label="通过" name="second"></el-tab-pane>
                <el-tab-pane label="未通过" name="third"></el-tab-pane>
            </el-tabs>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="60">
                </el-table-column>
                <el-table-column
                        label="店铺信息" width="200">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <app-image width="25" height="25" :src="scope.row.store.cover_url"></app-image>
                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.store.name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="用户" width="200">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center" v-if="scope.row.user">
                            <el-tooltip class="item" effect="dark" v-if="scope.row.user.userInfo.platform == 'wxapp'"
                                        content="微信" placement="top">
                                <img style="margin-right: 10px" src="statics/img/mall/wx.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark"
                                        v-else-if="scope.row.user.userInfo.platform == 'aliapp'" content="支付宝"
                                        placement="top">
                                <img style="margin-right: 10px" src="statics/img/mall/ali.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark" v-else content="未知" placement="top">
                                <img style="margin-right: 10px" src="statics/img/mall/site.png" alt="">
                            </el-tooltip>
                            <app-image width="25" height="25" :src="scope.row.user.userInfo.avatar"></app-image>
                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.user.nickname}}
                            </app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="联系人" width='200'>
                    <template slot-scope="scope">
                        <div>
                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.realname}}
                            </app-ellipsis>
                            <app-ellipsis style="margin-left: 10px;" :line="1">电话:{{scope.row.mobile}}
                            </app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        v-if="activeName == 'second'"
                        label="入驻时间"
                        prop="review_time"
                        width="250">
                </el-table-column>
                <el-table-column
                        v-if="activeName == 'third'"
                        label="审核时间"
                        prop="review_time"
                        width="250">
                </el-table-column>
                <el-table-column
                        v-else
                        label="申请时间"
                        prop="created_at"
                        width="250">
                </el-table-column>
                <el-table-column
                        fixed="right"
                        label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" :content="scope.row.review_status == 0 ?'审核' : '详情'"
                                        placement="top">
                                <img src="statics/img/mall/order/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                activeName: 'first',
                review_status: 0,
                keyword: null
            };
        },
        methods: {
            searchList() {
                this.page = 1;
                this.getList();
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/review',
                        page: self.page,
                        review_status: self.review_status,
                        keyword: self.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/mch/mall/mch/edit',
                        id: id,
                        is_review: this.review_status == 0 ? 1 : 0,
                    });
                }
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/mch/mall/mch/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            switchStatus(scope, type) {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: scope.row.id,
                        switch_type: type,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(tab, event) {
                this.review_status = tab.index;
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
