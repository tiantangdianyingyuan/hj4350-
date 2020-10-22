<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/15 9:47
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 200px;
        margin-right: 20px;
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

    #app .table-body .el-table .el-button {
        border-radius: 16px;
    }

    .create {
        height: 36px;
        line-height: 36px;
        float: right;
        color: #BCBCBC;
        margin-left: 20px;
    }

    .name {
        cursor: pointer;
        color: #49A9FF;
    }

    .el-input-group__append {
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0;">
        <div slot="header" style="margin-top: 20px;" v-if="!isInd">
            <div>
                <span>小程序管理</span>
            </div>
        </div>
        <div class="table-body">
            <div style="display: flex;margin-bottom: 20px;">
                <div class="input-item">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入商城名称搜索" type="text" clearable
                              @clear="search"
                              v-model="searchForm.keyword">
                        <el-button slot="append" @click="search" icon="el-icon-search"></el-button>
                    </el-input>
                </div>
                <div v-if="isInd">
                    <el-button type="primary"
                               size="small"
                               @click="showCreateMallDialog()">添加小程序商城
                    </el-button>
                    <div class="create">
                        可创建小程序商城数量
                        <span style="color:#49A9FF;padding: 0 5px;">
                            <b>{{adminInfo.app_max_count == -1 ? '无限制' : adminInfo.app_max_count}}</b>
                        </span>
                    </div>
                </div>
                <span v-else flex="cross:center">商城列表</span>
            </div>


            <el-table v-loading="searchLoading" border :data="list" style="margin-bottom: 20px">
                <el-table-column prop="id" label="ID" width="60"></el-table-column>
                <el-table-column prop="name" label="商城名称">
                    <template slot-scope="scope">
                        <el-button v-if="isInd && scope.row.is_disable == '0'"
                                   @click="toEnter(scope.row)"
                                   type="text">{{scope.row.name}}
                        </el-button>
                        <el-button v-else type="text" :disabled="true">{{scope.row.name}}</el-button>
                        <div>
                            <span>{{scope.row.user.nickname}}</span>
                            <span style="color: #909399">{{scope.row.user.username}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="site" label="数据统计">
                    <template slot="header" slot-scope="scope">
                        <el-tooltip effect="dark" content="数据统计每10分钟刷新一次" placement="top">
                            <span>数据统计<i class="el-icon-question"></i></span>
                        </el-tooltip>
                    </template>
                    <template slot-scope="scope">
                        <div>用户数: {{scope.row.count_data.user_count}}</div>
                        <div>订单数: {{scope.row.count_data.order_count}}</div>
                    </template>
                </el-table-column>
                <el-table-column width="170" prop="expired_at_text" label="有效期"></el-table-column>
                <el-table-column label="操作" width="380">
                    <template slot-scope="scope">
                        <el-button plain size="mini" type="info" @click="edit(scope.row)">编辑</el-button>

                        <el-popover v-if="isInd" placement="top" style="margin: 0 10px;"
                                    v-model="scope.row.recyclePopoverVisible">
                            <div style="margin-bottom: 10px">是否确认放入回收站？</div>
                            <div style="text-align: right">
                                <el-button size="mini"
                                           type="primary"
                                           @click="scope.row.recyclePopoverVisible = false">取消
                                </el-button>
                                <el-button size="mini" :loading="toRecycleLoading"
                                           @click="toRecycle(scope.row)">确认
                                </el-button>
                            </div>
                            <el-button plain size="mini" type="info" slot="reference">回收
                            </el-button>
                        </el-popover>

                        <el-button plain size="mini" type="info" @click="showCopyrightDialog(scope.row)"
                                   v-if="showCopyright">版权
                        </el-button>

                        <el-popover placement="top" style="margin: 0 10px;"
                                    v-model="scope.row.ableStatusPopoverVisible">
                            <div style="margin-bottom: 10px">确认{{scope.row.is_disable == 0 ? '禁用' : '启用'}}商城？</div>
                            <div style="text-align: right">
                                <el-button size="mini" type="primary"
                                           @click="scope.row.ableStatusPopoverVisible = false">取消
                                </el-button>
                                <el-button size="mini" :loading="switchAbleStatusLoading"
                                           @click="switchAbleStatus(scope.row)">确认
                                </el-button>
                            </div>
                            <el-button plain size="mini" type="info" slot="reference">
                                {{scope.row.is_disable == 0 ? '禁用' : '启用'}}
                            </el-button>
                        </el-popover>

                        <el-button v-if="isSuperAdmin" plain size="mini" type="info"
                                   @click="showChangeOwnerDialog(scope.row)">迁移
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <el-pagination
                    style="text-align: right"
                    v-if="pagination"
                    background
                    :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next"
                    :total="pagination.totalCount">
            </el-pagination>
        </div>
    </el-card>

    <!-- 创建商城 -->
    <el-dialog title="创建商城" :visible.sync="createMallDialogVisible" width="40%" :close-on-click-modal="false">
        <el-form label-width="100px" size="small" :model="createMallForm" :rules="createMallRules" ref="createMallForm">
            <el-form-item label="商城名称" prop="name">
                <el-input type="text" size="small" v-model="createMallForm.name" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="商城有效期" prop="expired_at" ref="expired_at">
                <el-date-picker type="datetime" v-if="isCheckExpired" :disabled="true"></el-date-picker>
                <el-date-picker v-else
                                :disabled="isCheckExpired"
                                type="datetime"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                placeholder="选择日期"
                                v-model="createMallForm.expired_at">
                </el-date-picker>
                <el-checkbox v-model="isCheckExpired" @change="checkExpiredAt">永久</el-checkbox>
            </el-form-item>
            <el-form-item style="text-align: right">
                <el-button size="small" @click="createMallDialogVisible = false">取消</el-button>
                <el-button size="small" :loading="createMallSubmitLoading" type="primary"
                           @click="createMallSubmit('createMallForm')">确定
                </el-button>
            </el-form-item>
        </el-form>
    </el-dialog>

    <!-- 修改版权 -->
    <el-dialog title="修改版权" :visible.sync="copyrightDialogVisible" v-if="copyrightDialogVisible">
        <el-form label-width="130px" v-if="copyrightForm" :model="copyrightForm">
            <el-form-item label="底部版权文字">
                <el-input style="width: 70%" type="textarea" autosize size="small" v-model="copyrightForm.description"
                          autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="底部版权图标">
                <app-attachment v-model="copyrightForm.pic_url" :simple="true">
                    <el-tooltip class="item" effect="dark" content="建议尺寸:160*50" placement="top">
                        <el-button size="small">选择图片</el-button>
                    </el-tooltip>
                </app-attachment>
                <app-image width="80px" height="80px" mode="aspectFill" :src="copyrightForm.pic_url"></app-image>
            </el-form-item>
            <el-form-item label="底部版权链接">
                <el-input style="width: 70%" placeholder="请选择链接" size="small" disabled v-model="copyrightForm.link_url">
                    <app-pick-link slot="append" :mall-id="copyrightForm.mall_id" @selected="selectAdvertUrl">
                        <el-button size="mini">选择链接</el-button>
                    </app-pick-link>
                </el-input>
            </el-form-item>
            <el-form-item style="text-align: right">
                <el-button size="small" @click="copyrightDialogVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click="copyrightSubmit()" :loading="copyrightSubmitLoading">确定
                </el-button>
            </el-form-item>
        </el-form>
    </el-dialog>

    <!-- 迁移 -->
    <el-dialog title="选择所属账户" :visible.sync="changeOwnerDialogVisible" width="30%">
        <div style="margin-bottom: 10px;">当前迁移的商城：<span style="color: #ff4544">{{currentChangeOwnerMall.name}}</span>
        </div>
        <div class="input-item">
            <el-input size="small" @keyup.enter.native="loadAdminList" placeholder="请输入搜索内容" type="text" clearable @clear="loadAdminList" v-model="keyword">
                <el-button slot="append" @click="loadAdminList" icon="el-icon-search"></el-button>
            </el-input>
        </div>
        <el-table v-loading="adminListLoading" :data="adminList" style="margin-bottom: 20px;">
            <el-table-column align="center" prop="id" label="id"></el-table-column>
            <el-table-column align="center" prop="nickname" label="用户名"></el-table-column>
            <el-table-column align="center" label="操作">
                <template slot-scope="scope">
                    <el-button :loading="scope.row.loading" plain size="mini" type="primary"
                               @click="changeToOwner(scope.row)">选择
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                v-if="adminListPagination"
                style="text-align: center"
                background
                @current-change="adminListPageChange"
                layout="prev, pager, next"
                :page-count="adminListPagination.page_count">
        </el-pagination>
    </el-dialog>

</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                isInd: _isInd,
                isAdmin: _isAdmin,
                isSuperAdmin: _isSuperAdmin,
                createMallDialogVisible: false,
                keyword: '',
                createMallForm: {
                    id: null,
                    name: '',
                    expired_at: '',
                },
                isCheckExpired: false,
                isExpiredDisabled: false,
                createMallRules: {
                    name: [
                        {required: true, message: '请填写商城名称。'},
                    ],
                    expired_at: [
                        {required: true, message: '请选择商城有效期'},
                    ]
                },
                createMallSubmitLoading: false,
                adminInfo: {},
                searchForm: {
                    keyword: '',
                },
                searchLoading: false,
                list: [],
                pagination: null,
                toRecycleLoading: false,
                copyrightDialogVisible: false,
                copyrightForm: null,
                copyrightSubmitLoading: false,
                switchAbleStatusLoading: false,
                changeOwnerDialogVisible: false,
                currentChangeOwnerMall: {
                    name: ''
                },
                adminListForm: {
                    page: 0,
                },
                adminListLoading: false,
                adminList: null,
                adminListPagination: null,
                mallListPage: 0,
            };
        },
        created() {
            this.loadList({});
        },
        methods: {
            showCreateMallDialog() {
                this.createMallDialogVisible = true;
                this.createMallForm.name = '';
                this.createMallForm.expired_at = '';
                this.createMallForm.id = null;
            },
            createMallSubmit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.createMallSubmitLoading = true;
                        this.$refs['expired_at'].clearValidate();
                        this.$request({
                            params: {
                                r: 'admin/mall/create',
                            },
                            method: 'post',
                            data: this.createMallForm,
                        }).then(e => {
                            this.createMallSubmitLoading = false;
                            if (e.data.code === 0) {
                                this.createMallDialogVisible = false;
                                this.$message.success(e.data.msg);
                                this.loadList({
                                    page: this.page
                                })
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                    }
                });
            },
            loadList(params) {
                params['r'] = 'admin/mall/index';
                params['keyword'] = this.searchForm.keyword;
                params['is_recycle'] = this.searchForm.isRecycle;
                params['user_id'] = getQuery('user_id');
                params['page'] = this.mallListPage;
                this.searchLoading = true;
                this.$request({
                    params: params,
                }).then(e => {
                    this.searchLoading = false;
                    if (e.data.code === 0) {
                        for (let i in e.data.data.list) {
                            e.data.data.list[i].recyclePopoverVisible = false;
                            e.data.data.list[i].ableStatusPopoverVisible = false;
                        }
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.adminInfo = e.data.data.admin_info;
                        this.showCopyright = e.data.data.showCopyright
                    }
                }).catch(e => {
                });
            },
            search() {
                this.loadList({});
            },
            pageChange(page) {
                this.mallListPage = page;
                this.loadList({
                    page: page,
                });
            },
            toEnter(row) {
                this.clearMenuStorage();
                this.$navigate({
                    r: 'admin/mall/entry',
                    id: row.id,
                    pic_url: null
                });
            },
            clearMenuStorage() {
                localStorage.removeItem('_OPENED_MENU_1_ID');
                localStorage.removeItem('_OPENED_MENU_2_ID');
                localStorage.removeItem('_OPENED_MENU_3_ID');
                localStorage.removeItem('_UNFOLD_ID_1');
                localStorage.removeItem('_UNFOLD_ID_2');
            },
            toRecycle(row) {
                this.toRecycleLoading = true;
                this.$request({
                    params: {
                        r: 'admin/mall/update',
                    },
                    method: 'post',
                    data: {
                        is_recycle: 1,
                        id: row.id
                    },
                }).then(e => {
                    this.toRecycleLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        row.recyclePopoverVisible = false;
                        this.loadList({});
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            showCopyrightDialog(row) {
                if (row.copyright) {
                    this.copyrightForm = row.copyright;
                    this.copyrightForm.mall_id = row.id;
                } else {
                    this.copyrightForm = {
                        description: '',
                        link_url: '',
                        pic_url: null,
                        type: 1,
                        mobile: '',
                        mall_id: row.id,
                        link: ''
                    };
                }
                this.copyrightDialogVisible = true;
            },
            copyrightSubmit() {
                this.copyrightSubmitLoading = true;
                this.copyrightForm['id'] = this.copyrightForm.mall_id;
                this.$request({
                    params: {
                        r: 'admin/mall/set-copyright',
                    },
                    method: 'post',
                    data: this.copyrightForm,
                }).then(e => {
                    this.copyrightSubmitLoading = false;
                    if (e.data.code === 0) {
                        this.copyrightDialogVisible = false;
                        this.$message.success(e.data.msg);
                        this.loadList({});
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            switchAbleStatus(row) {
                this.switchAbleStatusLoading = true;
                this.$request({
                    params: {
                        r: 'admin/mall/disable',
                        id: row.id,
                        status: 1
                    },
                    method: 'get'
                }).then(e => {
                    this.switchAbleStatusLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        row.ableStatusPopoverVisible = false;
                        this.loadList({});
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            selectAdvertUrl(e) {
                let self = this;
                let link_url;
                e.forEach(function (item, index) {
                    link_url = item.new_link_url;
                });
                this.copyrightForm.link = e[0];
                this.copyrightForm.link_url = link_url;
            },
            showChangeOwnerDialog(row) {
                this.currentChangeOwnerMall = row;
                this.changeOwnerDialogVisible = true;
                if (!this.adminList) {
                    this.loadAdminList();
                }
            },
            loadAdminList() {
                this.adminListLoading = true;
                this.$request({
                    params: {
                        r: 'admin/user/index',
                        page: this.adminListForm.page,
                        keyword: this.keyword,
                        is_super_admin: 1,
                    }
                }).then(e => {
                    this.adminListLoading = false;
                    if (e.data.code === 0) {
                        for (let i in e.data.data.list) {
                            e.data.data.list[i].loading = false;
                        }
                        this.adminList = e.data.data.list;
                        this.adminListPagination = e.data.data.pagination;
                    } else {
                    }
                }).catch(e => {
                });
            },
            adminListPageChange(page) {
                this.adminListForm.page = page;
                this.loadAdminList();
            },
            changeToOwner(row) {
                const content = '确认将`' + this.currentChangeOwnerMall.name + '`迁移至`' + row.username + '`账户下?';
                this.$confirm(content, '提示').then(e => {
                    row.loading = true;
                    this.$request({
                        params: {
                            r: "admin/mall/removal",
                            user_id: row.id,
                            mall_id: this.currentChangeOwnerMall.id,
                        },
                    }).then(e => {
                        row.loading = false;
                        if (e.data.code === 0) {
                            this.changeOwnerDialogVisible = false;
                            this.$message.success(e.data.msg);
                            this.loadList({});
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                }).catch(e => {
                });
            },
            checkExpiredAt(value) {
                this.isCheckExpired = value;
                if (value) {
                    this.createMallForm.expired_at = '0000-00-00 00:00:00'
                } else {
                    this.createMallForm.expired_at = ''
                }
                this.$refs['expired_at'].clearValidate();
            },
            edit(row) {
                this.createMallDialogVisible = true;
                this.createMallForm.id = row.id;
                this.createMallForm.name = row.name;
                if (row.expired_at == '0000-00-00 00:00:00') {
                    this.createMallForm.expired_at = row.expired_at;
                    this.isCheckExpired = true;
                } else {
                    this.createMallForm.expired_at = row.expired_at;
                }
            }
        }
    });
</script>
