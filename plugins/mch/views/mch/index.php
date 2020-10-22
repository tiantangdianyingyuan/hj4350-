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
    .el-tooltip__popper{max-width: 400px}
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商户列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加商户</el-button>
                </div>
            </div>
        </div>

        <div class="table-body">
            <el-alert
                    style="margin-bottom:20px;"
                    type="info"
                    title="入驻商户PC端登录网址："
                    :closable="false">
                <template>
                    <span id="target">{{loginRoute}}</span>
                    <el-button v-if="loginRoute" id="copy_btn"
                               data-clipboard-action="copy"
                               data-clipboard-target="#target"
                               size="mini">复制链接
                    </el-button>
                </template>
            </el-alert>
            <el-form @submit.native.prevent="searchList" size="small" :inline="true" :model="search">
                <el-form-item>
                    <div class="input-item">
                        <el-input  @keyup.enter.native="searchList" size="small" placeholder="请输入店铺名/用户名搜索" v-model="search.keyword" clearable
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
                        width="60">
                </el-table-column>
                <el-table-column
                        :show-overflow-tooltip="true"
                        label="店铺信息" width="200">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <app-image width="25" height="25" :src="scope.row.store.cover_url"></app-image>
                            <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.store.name}}</div>
<!--                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.store.name}}</app-ellipsis>-->
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
                            <div style="margin-left: 10px;width: 115px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.user.nickname}}</div>
<!--                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.user.nickname}}-->
<!--                            </app-ellipsis>-->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="联系人" width="200">
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
                        label="排序"
                        prop="sort"
                        width="300">
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
                                       icon="el-icon-success" circle @click="change(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="入驻时间"
                        prop="review_time"
                        width="220">
                </el-table-column>
                <el-table-column
                        label="开业"
                        width="80">
                    <template slot-scope="scope">
                        <el-switch
                                @change="switchStatus(scope, 'status')"
                                v-model="scope.row.status"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                        label="好店推荐"
                        width="80">
                    <template slot-scope="scope">
                        <el-switch
                                @change="switchStatus(scope, 'is_recommend')"
                                v-model="scope.row.is_recommend"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                    fixed="right"
                    width="250"
                    label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="$navigate({r: 'plugin/mch/mall/mch/mall-setting', mch_id: scope.row.id})"
                                   type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="设置" placement="top">
                                <img src="statics/img/plugins/setting.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="updatePasswordDialog(scope.row.id)" type="text" size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="修改密码" placement="top">
                                <img src="statics/img/mall/change.png" alt="">
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

            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
            </div>
        </div>

        <el-dialog title="修改密码" :visible.sync="dialogFormVisible" width="30%">
            <el-form size="small" @submit.native.prevent="" :model="form" :rules="passwordRules" ref="form">
                <el-form-item label="新密码" prop="password">
                    <el-input type="password" v-model="form.password" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="updatePassword('form')">确 定</el-button>
            </div>
        </el-dialog>
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
                loginRoute: '',

                search: {
                    keyword: '',
                },
                dialogFormVisible: false,
                form: {
                    password: '',
                },
                passwordRules: {
                    password: [
                        {required: true, message: '请输入新密码', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                mch_id: 0,
                id: null,
                sort: 0,
            };
        },
        methods: {
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },

            quit() {
                this.id = null;
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
                        r: 'plugin/mch/mall/mch/index',
                        page: self.page,
                        keyword: self.search.keyword
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
                    });
                } else {
                    navigateTo({
                        r: 'plugin/mch/mall/mch/edit',
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
            searchList() {
                this.page = 1;
                this.getList();
            },
            // 员工登录入口
            route() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/route'
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
            updatePasswordDialog(id) {
                this.dialogFormVisible = true;
                this.mch_id = id;
            },
            updatePassword(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.form.id = self.mch_id;
                        request({
                            params: {
                                r: 'plugin/mch/mall/mch/update-password'
                            },
                            method: 'post',
                            data: {
                                form: self.form,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.dialogFormVisible = false;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            change(e) {
                let self = this;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/edit-sort'
                    },
                    method: 'post',
                    data: {
                        sort: this.sort,
                        id: this.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        this.id = null
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
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
