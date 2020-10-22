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
        padding: 0;
    }

    .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>地址列表</span>
                <div style="float: right;margin-top: -7px">
                    <el-button type="primary" @click="edit" size="small">添加地址</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入收件人姓名或联系方式" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" @click="search" icon="el-icon-search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="100">
                </el-table-column>
                <el-table-column
                        label="收件人姓名"
                        width="120">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                        label="联系方式"
                        width="120">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.mobile}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                        label="详细地址" width="400">
                    <template slot-scope="scope">
                        <app-ellipsis :line="2">{{scope.row.address}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                        label="备注">
                    <template slot-scope="scope">
                        <app-ellipsis :line="2">{{scope.row.remark}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" size="small" type="text" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row, scope.$index)" type="text" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        hide-on-single-page
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
                keyword: null,
                pageCount: 0,
            };
        },
        methods: {
            search: function() {
                this.listLoading = true;
                let keyword = this.keyword;
                request({
                    params: {
                        r: 'mall/refund-address/index',
                        keyword: keyword
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        this.list = e.data.data.list;
                        this.pageCount = e.data.data.pagination.page_count;
                    }else{
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                }
                }).catch(e => {
                    this.listLoading = false;
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });        
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
                        r: 'mall/refund-address/index',
                        page: self.page
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'mall/refund-address/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'mall/refund-address/edit',
                    });
                }
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该地址, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/refund-address/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.list.splice(index, 1);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
