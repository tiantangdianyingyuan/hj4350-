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

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>所售类目</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加类目</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-form @submit.native.prevent="searchList" size="small" :inline="true" :model="search">
                <el-form-item>
                    <div class="input-item">
                        <el-input size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable
                                  @clear='searchList'>
                            <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
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
                        prop="id"
                        label="ID"
                        width="100">
                </el-table-column>
                <el-table-column
                        label="名称">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                        label="排序"
                        prop="sort"
                        width="150">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.sort}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="sort"
                                      autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                       icon="el-icon-error"
                                       circle @click="quit()"></el-button>
                            <el-button class="change-success" type="text"
                                       style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="store(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="是否显示"
                        width="120">
                    <template slot-scope="scope">
                        <el-switch
                                @change="switchStatus(scope)"
                                v-model="scope.row.status"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作"
                        fixed="right"
                        width="180">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
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
        <el-dialog :title="title" :visible.sync="dialogVisible">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item label="名称" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="请输入名称"></el-input>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input type="number" v-model="ruleForm.sort" placeholder="请输入排序"></el-input>
                </el-form-item>
                <el-form-item label="是否显示" prop="status">
                    <el-switch
                            v-model="ruleForm.status"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" type="primary" @click="store('ruleForm')">保存</el-button>
            </span>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                dialogVisible: false,
                page: 1,
                pageCount: 0,
                id: null,
                sort: 0,
                title: '添加类目',
                search: {
                    keyword: '',
                },
                ruleForm: {
                    name: '',
                    sort: 100,
                    status: '1',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入类目名称', trigger: 'blur'},
                    ],
                    sort: [
                        {required: true, message: '请输入类目排序', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '请选择是否显示', trigger: 'change'},
                    ],
                },
            };
        },
        methods: {
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
                this.ruleForm = row;
            },
            quit() {
                this.id = null;
            },
            store(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: 'plugin/mch/mall/common-cat/edit'
                    },
                    method: 'post',
                    data: {
                        form: self.ruleForm,
                    }
                }).then(e => {
                    self.btnLoading = false;
                if (e.data.code == 0) {
                    self.$message.success(e.data.msg);
                    self.id = null;
                    self.dialogVisible = false;
                    self.getList();
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                    self.$message.error(e.data.msg);
                self.btnLoading = false;
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
                        r: 'plugin/mch/mall/common-cat/index',
                        page: self.page,
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                self.list = e.data.data.list;
                self.pageCount = e.data.data.pagination.page_count;
            }).
                catch(e => {
                    console.log(e);
            });
            },
            edit(id) {
                let self = this;
                if (id > 0) {
                    self.title = '编辑类目';
                    request({
                        params: {
                            r: 'plugin/mch/mall/common-cat/edit',
                            id: id
                        },
                        method: 'get',
                    }).then(e => {
                        self.dialogVisible = true;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                        console.log(e);
                });
                } else {
                    self.title = '添加类目';
                    self.dialogVisible = true;
                    self.ruleForm = {
                        name: '',
                        sort: 100,
                        status: '1',
                    }
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
                        r: 'plugin/mch/mall/common-cat/destroy',
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
            switchStatus(scope) {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/common-cat/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: scope.row.id,
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
            searchList() {
                this.page = 1;
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
