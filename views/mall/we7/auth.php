<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-dialog-template');
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

    .check-title {
        background-color: #F3F5F6;
        width: 100%;
        padding: 0 20px;
    }

    .check-list {
        display: flex;
        flex-wrap: wrap;
        padding: 0 20px;
    }

    .check-list .el-checkbox {
        width: 145px;
    }

    .el-checkbox {
        height: 50px;
        line-height: 50px;
    }

    .window {
        border: 1px solid #EBEEF5;
    }

    .check-title .el-checkbox__label {
        font-size: 16px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>权限管理</span>
            </div>
        </div>
        <div class="table-body">
            <el-form @submit.native.prevent size="small" inline :model="search">
                <el-form-item>
                    <div class="input-item">
                        <el-input size="small" placeholder="请输入搜索内容" clearable @clear="clearInput"
                                  v-model="search.keyword">
                            <el-button slot="append" @click="commonSearch" icon="el-icon-search"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item v-if="multipleSelection.length > 0">
                    <el-button type="primary" size="small" @click="batch">批量设置权限</el-button>
                </el-form-item>
                <el-form-item v-if="status && multipleSelection.length < 1">
                    <template slot='label'>
                        <span>子账户默认权限</span>
                        <el-tooltip effect="dark" content="仅适用于新添加的账户从未设置过权限的账户"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <div>
                        <el-radio v-model="status" @change="updateStatus" label="0">默认无权限</el-radio>
                        <el-radio v-model="status" @change="updateStatus" label="1">默认有所有权限</el-radio>
                    </div>
                </el-form-item>
            </el-form>
            <div>
                <el-table
                        v-loading="listLoading"
                        :data="list"
                        border
                        ref="multipleTable"
                        @selection-change="handleSelectionChange">
                    <el-table-column
                            type="selection"
                            width="55">
                    </el-table-column>
                    <el-table-column
                            fixed
                            prop="id"
                            label="ID"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            prop="username"
                            label="用户名"
                            width="120">
                    </el-table-column>
                    <el-table-column
                            prop="created_at"
                            label="注册时间">
                    </el-table-column>
                    <el-table-column
                            label="操作"
                            width="280">
                        <template slot-scope="scope">
                            <el-button @click="edit(scope.row)" type="primary" size="mini">
                                {{permissionText(scope.row)}}
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div flex="box:last cross:center" style="margin-top: 20px;">
                    <div>
                        <el-button style="visibility: hidden" plain type="primary" size="small" @click="batch">批量设置权限
                        </el-button>
                    </div>
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
        </div>
    </el-card>

    <el-dialog
            title="添加权限"
            :visible.sync="dialogVisible"
            width="50%"
            @click="dialogVisible = false">
        <div class="window">
            <el-checkbox class="check-title" v-model="is_default" :true-label="1" :false-label="0">
                默认权限
                <el-tooltip class="item" effect="dark" content="使用默认权限，则下面的设置无效" placement="top">
                    <el-icon class="el-icon-warning">
                    </el-icon>
                </el-tooltip>
            </el-checkbox>
            <template v-if="is_default == 0">
                <el-checkbox class="check-title" :indeterminate="mallIndeterminate" v-model="checkMall"
                             @change="handleCheckMallChange">基础权限
                </el-checkbox>
                <el-checkbox-group class="check-list" v-model="checkedMallPermissions" @change="handleCheckedMallChange">
                    <el-checkbox v-for="item in permissions.mall" :label="item.name" :key="item.id">
                        {{item.display_name}}
                    </el-checkbox>
                </el-checkbox-group>
                <el-checkbox class="check-title" :indeterminate="pluginsIndeterminate" v-model="checkPlugins"
                             @change="handleCheckPluginsChange">插件权限
                </el-checkbox>
                <el-checkbox-group class="check-list" v-model="checkedPluginsPermissions"
                                   @change="handleCheckedPluginsChange">
                    <el-checkbox v-for="item in permissions.plugins" :label="item.name" :key="item.id">
                        {{item.display_name}}
                    </el-checkbox>
                </el-checkbox-group>
                <template v-if="storageShow()">
                    <el-checkbox class="check-title" :indeterminate="storageIndeterminate()"
                                 v-model="checkStorage"
                                 @change="storageCheckAll">上传权限
                    </el-checkbox>
                    <el-checkbox-group class="check-list" v-model="secondary_permissions.attachment"
                                       @change="storageCheck">
                        <el-checkbox v-for="(item, key) in storage" :label="key" :key="item">
                            {{item}}
                        </el-checkbox>
                    </el-checkbox-group>
                </template>
                <template v-if="templateShow()">
                    <div class="check-title" style="height: 50px;line-height: 50px">模板权限</div>
                    <div style="padding: 10px 20px;">
                        <div style="margin-right: 10px;">显示权限</div>
                        <div flex>
                            <el-button size="mini" type="text" style="margin-right: 10px;" @click="show = true"
                                       v-if="secondary_permissions.template.is_all == 0">添加模板</el-button>
                            <el-checkbox v-model="secondary_permissions.template.is_all"
                                         true-label="1" false-label="0">全部选择</el-checkbox>
                        </div>
                        <div v-if="secondary_permissions.template.is_all == 0">
                            <el-tag v-for="(item, key) in secondary_permissions.template.list" :key="key"
                                    closable :disable-transitions="true" style="margin-right: 5px;margin-bottom: 5px"
                                    @close="templateDel(key)">{{item.name}}
                            </el-tag>
                        </div>
                        <div style="margin-right: 10px;margin-top: 10px;">使用权限</div>
                        <div flex>
                            <el-button size="mini" type="text" style="margin-right: 10px;" @click="show_use = true"
                                       v-if="secondary_permissions.template.use_all == 0">添加模板</el-button>
                            <el-checkbox v-model="secondary_permissions.template.use_all"
                                         true-label="1" false-label="0">全部选择</el-checkbox>
                        </div>
                        <div v-if="secondary_permissions.template.use_all == 0">
                            <el-tag v-for="(item, key) in secondary_permissions.template.use_list" :key="key"
                                    closable :disable-transitions="true" style="margin-right: 5px;margin-bottom: 5px"
                                    @close="useTemplateDel(key)">{{item.name}}
                            </el-tag>
                        </div>
                    </div>
                    <app-dialog-template :show="show" @selected="templateSelected" :status="1"
                                         :selected="templateList"></app-dialog-template>
                    <app-dialog-template :show="show_use" @selected="useTemplateSelected" :status="0"
                                         :selected="useTemplateList"></app-dialog-template>
                </template>
            </template>
        </div>
        <span slot="footer" class="dialog-footer">
    <el-button size="small" @click="dialogVisible = false">取 消</el-button>
    <el-button size="small" :loading="btnLoading" type="primary" @click="updatePermission">保存</el-button>
  </span>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                btnLoading: false,
                page: 1,
                pageCount: 0,
                dialogVisible: false,
                search: {
                    keyword: ''
                },

                checkMall: false,
                checkPlugins: false,
                checkedPermissions: [],
                checkedMallPermissions: [],
                checkedPluginsPermissions: [],
                permissions: {
                    mall: [],
                    plugins: [],
                },
                mallIndeterminate: false,
                pluginsIndeterminate: false,
                user: {},

                status: '0',
                multipleSelection: [],
                is_default: 0,
                secondary_permissions: {
                    attachment: ["1", "2", "3", "4"],
                    template: {
                        is_all: 0,
                        use_all: 0,
                        list: [],
                        use_list: [],
                    }
                },
                storage: [],
                checkStorage: true,
                show: false,
                show_use: false,
            };
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
                        r: 'mall/we7/auth',
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.listLoading = false;
                        self.list = e.data.data.list;
                        self.status = e.data.data.status;
                        self.pageCount = e.data.data.pagination.page_count;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
            edit(row) {
                let self = this;
                self.dialogVisible = true;
                self.user = row;
                self.checkedMallPermissions = row.adminInfo.permissions.mall.length ? row.adminInfo.permissions.mall : [];
                self.checkedPluginsPermissions = row.adminInfo.permissions.plugins.length ? row.adminInfo.permissions.plugins : [];
                self.is_default = row.adminInfo.is_default == 1 ? 1 : 0;
                this.secondary_permissions = row.secondary_permissions;
                self.handleCheckedMallChange(self.checkedMallPermissions);
                self.handleCheckedPluginsChange(self.checkedPluginsPermissions);
                self.$refs.multipleTable.clearSelection();
            },
            handleCheckMallChange(val) {
                let checkedArr = [];
                if (val) {
                    this.permissions.mall.forEach(function (item, index) {
                        checkedArr.push(item.name);
                    });
                }
                this.checkedMallPermissions = checkedArr;
                this.mallIndeterminate = false;
            },
            handleCheckPluginsChange(val) {
                let checkedArr = [];
                if (val) {
                    this.permissions.plugins.forEach(function (item, index) {
                        checkedArr.push(item.name);
                    });
                }
                this.checkedPluginsPermissions = checkedArr;
                this.pluginsIndeterminate = false;
            },
            handleCheckedMallChange(value) {
                let checkedCount = value.length;
                this.checkMall = checkedCount === this.permissions.mall.length;
                this.mallIndeterminate = checkedCount > 0 && checkedCount < this.permissions.mall.length;
            },
            handleCheckedPluginsChange(value) {
                let checkedCount = value.length;
                this.checkPlugins = checkedCount === this.permissions.plugins.length;
                this.pluginsIndeterminate = checkedCount > 0 && checkedCount < this.permissions.plugins.length;
            },
            getPermissions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/we7/permissions',
                        id: getQuery('id'),
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.permissions = e.data.data.permissions;
                        self.storage = e.data.data.storage;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
            // 更新权限
            updatePermission() {
                let self = this;
                self.btnLoading = true;
                self.checkedPermissions = this.checkedMallPermissions.concat(this.checkedPluginsPermissions);
                if (self.user.id > 0) {
                    request({
                        params: {
                            r: 'mall/we7/permissions',
                        },
                        method: 'post',
                        data: {
                            user_id: self.user.id,
                            permissions: self.checkedPermissions,
                            is_default: self.is_default,
                            secondary_permissions: self.secondary_permissions
                        }
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.dialogVisible = false;
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    });
                } else {
                    self.batchUpdatePermissions();
                }
            },
            // 搜索
            commonSearch() {
                this.getList();
            },
            // 更新子账户默认权限状态
            updateStatus() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/we7/status',
                    },
                    method: 'post',
                    data: {
                        status: self.status
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
            // 多选操作
            handleSelectionChange(val) {
                let userIds = [];
                if (val) {
                    val.forEach(function (item, index) {
                        userIds.push(item.id);
                    });
                }
                this.multipleSelection = userIds;
            },
            // 批量设置权限
            batch() {
                let self = this;
                if (self.multipleSelection.length < 1) {
                    self.$message.warning('请先选择用户');
                    return;
                }
                self.user = {};
                self.handleCheckedMallChange(self.checkedPermissions = []);
                self.handleCheckedPluginsChange(self.checkedPermissions = []);
                self.dialogVisible = true;
            },
            // 批量执行更新权限
            batchUpdatePermissions() {
                let self = this;
                self.btnLoading = true;
                self.checkedPermissions = this.checkedMallPermissions.concat(this.checkedPluginsPermissions);
                request({
                    params: {
                        r: 'mall/we7/batch-update-permissions',
                    },
                    method: 'post',
                    data: {
                        user_ids: self.multipleSelection,
                        permissions: self.checkedPermissions,
                        is_default: self.is_default,
                        secondary_permissions: self.secondary_permissions
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                        self.dialogVisible = false;
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
            clearInput() {
                this.getList();
            },
            permissionText(user) {
                let total = this.permissions.mall.length + this.permissions.plugins.length;
                let own = user.adminInfo.permissions_num;
                if (user.adminInfo.is_default == 1) {
                    if (this.status == 1) {
                        own = total;
                    } else {
                        own = 0;
                    }
                }
                return `权限管理(` + own + `/` + total + `)`;
            },
            storageShow() {
                for (let i in this.checkedMallPermissions) {
                    if (this.checkedMallPermissions[i] == 'attachment') {
                        return true;
                    }
                }
                return false;
            },
            storageCheckAll() {
                let arr = [];
                for (let i in this.storage) {
                    arr.push(i);
                }
                if (arr.length == this.secondary_permissions.attachment.length) {
                    this.secondary_permissions.attachment = [];
                    this.checkStorage = false;
                } else {
                    this.secondary_permissions.attachment = arr;
                    this.checkStorage = true;
                }
            },
            storageCheck(value) {
                let arr = [];
                for (let i in this.storage) {
                    arr.push(i);
                }
                if (this.secondary_permissions.attachment.length == arr.length) {
                    this.checkStorage = true;
                }
                this.checkStorage = false;
            },
            storageIndeterminate() {
                let arr = [];
                for (let i in this.storage) {
                    arr.push(i);
                }
                if (this.secondary_permissions.attachment.length > 0 && this.secondary_permissions.attachment.length < arr.length) {
                    return true;
                }
                if (this.secondary_permissions.attachment.length == arr.length) {
                    return false;
                }
                return false;
            },
            templateShow() {
                for (let i in this.checkedPluginsPermissions) {
                    if (this.checkedPluginsPermissions[i] == 'diy') {
                        return true;
                    }
                }
                return false;
            },
            templateSelected(data) {
                this.show = false;
                if (data) {
                    this.secondary_permissions.template.list = data;
                }
            },
            templateDel(key) {
                this.secondary_permissions.template.list.splice(key, 1);
            },
            useTemplateSelected(data) {
                this.show_use = false;
                if (data) {
                    this.secondary_permissions.template.use_list = data;
                }
            },
            useTemplateDel(key) {
                this.secondary_permissions.template.use_list.splice(key, 1);
            },
        },
        mounted: function () {
            this.getList();
            this.getPermissions();
        },
        computed: {
            templateList() {
                if (typeof this.secondary_permissions.template == 'undefined') {
                    return [];
                }
                let list = [];
                this.secondary_permissions.template.list.forEach(item => {
                    list.push(item.id);
                });
                return list;
            },
            useTemplateList() {
                if (typeof this.secondary_permissions.template == 'undefined') {
                    return [];
                }
                let list = [];
                this.secondary_permissions.template.use_list.forEach(item => {
                    list.push(item.id);
                });
                return list;
            },
        }
    });
</script>
