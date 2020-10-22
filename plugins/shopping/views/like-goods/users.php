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
        padding: 15px;
    }

    .table-body .el-table .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="listLoading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/shopping/mall/buy-order/index'})">想买好物圈</span></el-breadcrumb-item>
                <el-breadcrumb-item>想买人员列表编辑</el-breadcrumb-item>
            </el-breadcrumb>
            <el-button style="float: right; margin: -23px 0 0" type="primary" size="small" @click="getUsers">添加用户</el-button>
        </div>
        <div class="table-body">
            <div style="background-color: #fce9e6;width: 100%;border-color: #edd7d4;color: #e55640;border-radius: 2px;padding: 15px;margin-bottom: 20px;">
                <p>服务商&商家需要保证在导入数据时</p>
                <p>1.订单&商品数据是用户真实的操作产生的； 2.在适当的场景调用好物单提供的相关接口（这里指商品和订单的新增&删除）；
                    如果发现伪造数据/不当调用的情况，微信搜索平台将对服务商和商家采取惩罚措施。</p>
            </div>
            <el-form @submit.native.prevent="commonSearch" size="small" :inline="true" :model="search">
                <el-form-item style="margin-bottom: 0">
                    <div class="input-item">
                        <el-input clearable @clear='commonSearch' size="small" placeholder="请输入搜索内容" v-model="keyword">
                            <el-button slot="append" icon="el-icon-search" @click="commonSearch"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item style="margin-bottom: 0" v-if="multipleSelection.length != 0">
                    <el-button type="primary" @click="batchDestroyLikeUser" size="small">批量删除</el-button>
                </el-form-item>
            </el-form>
            <el-table v-loading="tableLoading" :data="list" border style="width: 100%;margin-bottom: 15px" @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="60"></el-table-column>
                <el-table-column prop="user.id" label="用户ID" width="80"></el-table-column>
                <el-table-column label="头像" width="250">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.user.userInfo.avatar"></app-image>
                    </template>
                </el-table-column>
                <el-table-column prop="user.nickname" label="昵称"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="destroy(scope.row.user.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div></div>
                <div>
                    <el-pagination
                            v-if="pageCount > 0"
                            @current-change="pagination"
                            background
                            layout="prev, pager, next, jumper"
                            :page-count="pageCount">
                    </el-pagination>
                </div>
            </div>
        </div>
        <el-dialog title="从想买好物圈删除" :visible.sync="progressVisible">
            <div style="margin: 10px 0;">
                总数{{multipleSelection.length}}条,失败{{progressErrorCount}}条。
            </div>
            <el-progress :text-inside="true" :stroke-width="18" :percentage="progressCount"></el-progress>
            <div style="text-align: right;margin-top: 20px;">
                <el-button type="success" :loading="btnLoading" @click="sendConfirm" size="small">确定</el-button>
            </div>
            <div style="margin-top: 20px;" v-for="(item,index) in progressErrors" :key="index">
                订单ID: {{item.id}},{{item.errmsg}}
            </div>

        </el-dialog>

        <el-dialog title="用户列表" :visible.sync="dialogUsersVisible">
            <template>
                <div style="display: flex">
                    <div class="input-item" style="margin-right: 10px">
                        <el-input clearable @clear='getUsers' @keyup.enter.native="getUsers" size="small" placeholder="请输入搜索内容" v-model="keyword">
                            <el-button slot="append" icon="el-icon-search" @click="getUsers"></el-button>
                        </el-input>
                    </div>
                    <div v-if="multipleSelectionUser.length > 0">
                        <el-button type="primary" @click="batchAddLikeUser" size="small">批量添加</el-button>
                    </div>
                </div>
                <el-table
                        v-loading="userTableLoading"
                        ref="multipleTable"
                        :data="users"
                        tooltip-effect="dark"
                        style="width: 100%"
                        @selection-change="handleSelectionUserChange">
                    <el-table-column
                            type="selection"
                            width="60">
                    </el-table-column>
                    <el-table-column
                            prop="id"
                            label="ID"
                            width="100">
                    </el-table-column>
                    <el-table-column
                            label="头像" width="200">
                        <template slot-scope="scope">
                            <app-image mode="aspectFill" :src="scope.row.userInfo.avatar"></app-image>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="nickname"
                            label="昵称">
                    </el-table-column>
                    <el-table-column
                            label="操作"
                            width="120">
                        <template slot-scope="scope">
                            <el-button @click="shopping(scope.row.id)" type="text" circle size="small">
                                <el-tooltip class="item" effect="dark" content="添加" placement="top">
                                    <img src="statics/img/mall/plus.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div flex="box:last cross:center" style="margin-top: 20px;">
                    <div></div>
                    <div>
                        <el-pagination
                                v-if="searchUserPageCount > 0"
                                @current-change="searchUserPagination"
                                background
                                layout="prev, pager, next, jumper"
                                :page-count="searchUserPageCount">
                        </el-pagination>
                    </div>
                </div>
            </template>
            <div style="text-align: right;margin-top: 20px;">
                <el-button type="primary" @click="dialogUsersVisible = false" size="small">确定</el-button>
            </div>
        </el-dialog>

        <el-dialog title="添加到想买好物圈" :visible.sync="progressVisible_2">
            <div style="margin: 10px 0;">
                总数{{multipleSelectionUser.length}}条,失败{{progressErrorCount_2}}条。
            </div>
            <el-progress :text-inside="true" :stroke-width="18" :percentage="progressCount_2"></el-progress>
            <div style="text-align: right;margin-top: 20px;">
                <el-button type="success" :loading="btnLoading" @click="sendConfirm" size="small">确定</el-button>
            </div>
            <div style="margin-top: 20px;" v-for="(item,index) in progressErrors_2" :key="index">
                订单ID: {{item.id}},{{item.errmsg}}
            </div>

        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: '',
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,

                progressVisible: false,
                progressErrors: [],
                progressCount: 0,
                progressErrorCount: 0, //总失败数
                progressSendCount: 0, //总条数
                btnLoading: false,
                tableLoading: false,

                progressVisible_2: false,
                progressErrors_2: [],
                progressCount_2: 0,
                progressErrorCount_2: 0, //总失败数
                progressSendCount_2: 0, //总条数

                multipleSelection: [],
                multipleSelectionUser: [],
                dialogUsersVisible: false,
                userTableLoading: false,
                users: [],
                keyword: '',
                searchUserPage: 1,
                searchUserPageCount: 0,
            };
        },
        created() {
            this.getList();
        },
        methods: {
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
                        r: 'plugin/shopping/mall/like-goods/users',
                        page: self.page,
                        search: self.search,
                        id: getQuery('id'),
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
            shopping(id) {
                let self = this;
                self.$confirm('加入想买好物圈, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.userTableLoading = true;
                    request({
                        params: {
                            r: 'plugin/shopping/mall/like-goods/add-like-user',
                        },
                        method: 'post',
                        data: {
                            user_id: id,
                            id: getQuery('id')
                        }
                    }).then(e => {
                        self.userTableLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                            self.getUsers();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            destroy(id) {
                let self = this;
                self.$confirm('从想买好物圈删除, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.tableLoading = true;
                    request({
                        params: {
                            r: 'plugin/shopping/mall/like-goods/destroy-like-user',
                        },
                        method: 'post',
                        data: {
                            user_id: id,
                            id: getQuery('id')
                        }
                    }).then(e => {
                        self.tableLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            // 搜索
            commonSearch() {
                this.getList();
            },
            // 完成确认
            sendConfirm() {
                this.btnLoading = true;
                navigateTo({
                    r: 'plugin/shopping/mall/like-goods/users',
                    id: getQuery('id')
                })
            },
            handleSelectionChange(val) {
                let self = this;
                self.multipleSelection = [];
                val.forEach(function (item) {
                    self.multipleSelection.push(item.user.id);
                })
            },
            handleSelectionUserChange(val) {
                let self = this;
                self.multipleSelectionUser = [];
                val.forEach(function (item) {
                    self.multipleSelectionUser.push(item.id);
                })
            },
            batchDestroyLikeUser() {
                let self = this;
                if (self.multipleSelection.length > 0) {
                    self.$confirm('从想买好物圈删除, 是否继续?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let count = 100;
                        let orderCount = self.multipleSelection.length;
                        let progressItem = (count / orderCount).toFixed(0);
                        self.progressVisible = true;
                        self.multipleSelection.forEach(function (item) {
                            request({
                                params: {
                                    r: 'plugin/shopping/mall/like-goods/destroy-like-user',
                                },
                                method: 'post',
                                data: {
                                    user_id: item,
                                    id: getQuery('id')
                                }
                            }).then(e => {
                                self.listLoading = false;
                                self.progressSendCount += 1;// 发送总数
                                if (e.data.code === 0) {
                                    self.progressCount = self.progressCount + parseInt(progressItem);
                                } else {
                                    self.progressErrorCount += 1;
                                    self.progressErrors.push({
                                        id: item,
                                        errmsg: e.data.msg
                                    })
                                }
                                if (self.progressSendCount == orderCount) {
                                    self.progressCount = 100;
                                }
                            }).catch(e => {
                                console.log(e);
                            });
                        })
                    }).catch(() => {
                        self.$message.info('已取消')
                    });
                } else {
                    self.$message.error('请勾选用户')
                }
            },
            batchAddLikeUser() {
                let self = this;
                if (self.multipleSelectionUser.length > 0) {
                    self.$confirm('加入想买好物圈, 是否继续?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let count = 100;
                        let userCount = self.multipleSelectionUser.length;
                        let progressItem = (count / userCount).toFixed(0);
                        self.progressVisible_2 = true;
                        self.multipleSelectionUser.forEach(function (item) {
                            request({
                                params: {
                                    r: 'plugin/shopping/mall/like-goods/add-like-user',
                                },
                                method: 'post',
                                data: {
                                    user_id: item,
                                    id: getQuery('id')
                                }
                            }).then(e => {
                                self.listLoading = false;
                                self.progressSendCount_2 += 1;// 发送总数
                                if (e.data.code === 0) {
                                    self.progressCount_2 = self.progressCount_2 + parseInt(progressItem);
                                } else {
                                    self.progressErrorCount_2 += 1;
                                    self.progressErrors_2.push({
                                        id: item,
                                        errmsg: e.data.msg
                                    })
                                }
                                if (self.progressSendCount_2 == userCount) {
                                    self.progressCount_2 = 100;
                                }
                            }).catch(e => {
                                console.log(e);
                            });
                        })
                    }).catch(() => {
                        self.$message.info('已取消')
                    });
                } else {
                    self.$message.error('请勾选用户')
                }
            },
            searchUserPagination(currentPage) {
                let self = this;
                self.searchUserPage = currentPage;
                self.getUsers();
            },
            getUsers() {
                let self = this;
                self.userTableLoading = true;
                self.dialogUsersVisible = true;
                request({
                    params: {
                        r: 'plugin/shopping/mall/like-goods/search-user',
                        keyword: self.keyword,
                        page: self.searchUserPage,
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.userTableLoading = false;
                    if (e.data.code == 0) {
                        self.users = e.data.data.list;
                        self.searchUserPageCount = e.data.data.pagination.page_count;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.userTableLoading = false;
                });
            },
        }
    });
</script>
