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
        margin-right: 40px;
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

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .el-table .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    #app .copy .el-input-group__append {
        background-color: #409EFF;
        border-color: #409EFF;
        padding: 0 10px;
    }

    #app .table-body .copybtn {
        color: #fff;
        padding: 0 30px;
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

    .el-alert__content {
        display: flex;
        align-items: center;
    }

    .table-body .el-alert__title {
        margin-top: 5px;
        font-weight: 400;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>用户列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加用户</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-alert
                    style="margin-bottom:20px;"
                    type="info"
                    title="员工登录入口链接："
                    :closable="false">
                <template>
                    <span id="target">{{loginRoute}}</span>
                    <el-button id="copy_btn"
                               data-clipboard-action="copy"
                               data-clipboard-target="#target"
                               size="mini">复制链接
                    </el-button>
                </template>
            </el-alert>
            <el-form inline @submit.native.prevent="search">
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="search" size="small" placeholder="请输入用户昵称" v-model="keyword" clearable @clear="search">
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        fixed
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="nickname"
                        label="昵称">
                </el-table-column>
                <el-table-column
                        prop="username"
                        label="用户名">
                </el-table-column>
                <el-table-column
                        prop="created_at"
                        label="添加日期"
                        width="220">
                </el-table-column>
                <el-table-column
                        label="操作"
                        fixed="right"
                        width="260">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row.id)" size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="editPassword(scope.row.id)" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="修改密码" placement="top">
                                <img src="statics/img/mall/change.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="destroy(scope.row.id, scope.$index)" size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="dir:right" style="margin-top: 20px;">
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
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                keyword: '',
                btnLoading: false,
                loginRoute: '',
            };
        },
        methods: {
            search: function() {
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
                        r: 'mall/role-user/index',
                        page: self.page,
                        keyword: self.keyword
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
                        r: 'mall/role-user/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'mall/role-user/edit',
                    });
                }
            },
            destroy(id, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/role-user/destroy',
                        },
                        method: 'post',
                        data: {
                            id: id,
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
            editPassword(id) {
                let self = this;
                self.$prompt('请输入新密码', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputPattern: /\S+/,
                    inputErrorMessage: '请输入新密码',
                    inputType: 'password',
                }).then(({value}) => {
                    request({
                        params: {
                            r: 'mall/role-user/edit-password',
                        },
                        method: 'post',
                        data: {
                            id: id,
                            password: value
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            // 员工登录入口
            route() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/role-user/route'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.loginRoute = e.data.data.url;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getList();
            this.route();
        }
    });

    var clipboard = new Clipboard('#copy_btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });
</script>
