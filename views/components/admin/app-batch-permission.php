<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-dialog-template');
?>
<style>
    .app-batch-permission {
        display: inline-block;
    }

    .app-batch-permission .permissions-list {
        margin-top: 20px;
    }

    .app-batch-permission .batch-remark {
        margin-top: 5px;
        color: #999999;
        font-size: 14px;
    }

    .app-batch-permission .batch-title {
        font-size: 18px;
    }

    .app-batch-permission .batch-box-left {
        width: 120px;
        border-right: 1px solid #e2e2e2;
        padding: 0 20px;
    }

    .app-batch-permission .batch-box-left div {
        padding: 5px 0;
        margin: 5px 0;
        cursor: pointer;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }

    .app-batch-permission .batch-div-active {
        background-color: #e2e2e2;
    }

    .app-batch-permission .el-dialog__body {
        padding: 15px 20px;
    }

    .app-batch-permission .batch-box-right {
        padding: 5px 20px;
    }

    .app-batch-permission .permissions-item {
        height: 24px;
        line-height: 24px;
        border-radius: 12px;
        padding: 0 12px;
        margin-right: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        color: #999999;
        background-color: #F7F7F7;
        display: inline-block;
        font-size: 12px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .app-batch-permission .permissions-item.active {
        background-color: #F5FAFF;
        color: #57ADFF;
    }

</style>

<template id="app-batch-permission">
    <div class="app-batch-permission">
        <el-button size="small" type="primary" @click="batchSetting">批量权限设置</el-button>
<!--        <el-button size="small" type="primary" @click="openGlobalPermission">全局权限设置</el-button>-->
        <el-dialog
                :visible.sync="dialogVisible"
                width="50%">
            <div slot="title">
                <div flex="dir:left">
                    <div class="batch-title">批量权限设置</div>
                </div>
                <!--                <div class="batch-remark">注：每次只能修改一项，修改后点击确定即可生效。如需修改多项，需多次操作。</div>-->
            </div>
            <div flex="dir:left box:first">
                <div class="batch-box-left" flex="dir:top">
                    <div v-for="(item, index) in baseBatchList"
                         :key='item.key'
                         :class="{'batch-div-active': currentBatch === item.key ? true : false}"
                         @click="currentBatch = item.key"
                         flex="main:center">
                        {{item.name}}
                    </div>
                </div>
                <div class="batch-box-right" v-loading="baseLoading">
                    <div v-if="currentBatch === 'base-permission'">
                        <el-checkbox :indeterminate="isBaseIndeterminate" v-model="baseCheckAll"
                                     @change="handleBaseCheckAllChange">全选
                        </el-checkbox>
                        <div class="permissions-list">
                            <div v-for="item in permissions" @click="handleBaseCheckedCitiesChange(item)"
                                 :key="item.name"
                                 class="permissions-item" :class="{active:item.isCheck}">
                                {{item.display_name}}
                            </div>
                        </div>
                    </div>
                    <div v-if="currentBatch === 'upload-permission'">
                        <el-checkbox :indeterminate="isUploadIndeterminate" v-model="uploadCheckAll"
                                     @change="handleUploadCheckAllChange">全选
                        </el-checkbox>
                        <div class="permissions-list">
                            <div v-for="item in storage" @click="handleUploadCheckedCitiesChange(item)" :key="item.name"
                                 class="permissions-item" :class="{active:item.isCheck}">
                                {{item.display_name}}
                            </div>
                        </div>
                    </div>
                    <div v-if="currentBatch === 'template-permission'">
                        <template v-if="templateShow">
                            <el-form ref="form" label-position="left" label-width="80px" size="small">
                                <el-form-item label="显示权限">
                                    <div flex>
                                        <el-button size="mini" type="text" style="margin-right: 10px;"
                                                   @click="templatePermission.show = true"
                                                   v-if="templatePermission.is_all == 0">添加模板
                                        </el-button>
                                        <el-checkbox v-model="templatePermission.is_all"
                                                     true-label="1" false-label="0">全部选择
                                        </el-checkbox>
                                    </div>
                                    <div v-if="templatePermission.is_all == 0">
                                        <el-tag v-for="(item, key) in templatePermission.list"
                                                :key="key"
                                                closable
                                                :disable-transitions="true"
                                                style="margin-right: 5px;margin-bottom: 5px"
                                                size="small"
                                                @close="templateDel(key)">
                                            {{item.name}}
                                        </el-tag>
                                    </div>
                                </el-form-item>
                                <el-form-item label="使用权限">
                                    <div flex>
                                        <el-button size="mini" type="text" style="margin-right: 10px;"
                                                   @click="templatePermission.show_use = true"
                                                   v-if="templatePermission.use_all == 0">添加模板
                                        </el-button>
                                        <el-checkbox v-model="templatePermission.use_all"
                                                     true-label="1" false-label="0">全部选择
                                        </el-checkbox>
                                    </div>
                                    <div v-if="templatePermission.use_all == 0">
                                        <el-tag v-for="(item, key) in templatePermission.use_list"
                                                :key="key"
                                                size="small"
                                                closable
                                                :disable-transitions="true"
                                                style="margin-right: 5px;margin-bottom: 5px"
                                                @close="useTemplateDel(key)">
                                            {{item.name}}
                                        </el-tag>
                                    </div>
                                </el-form-item>
                            </el-form>
                        </template>
                        <template v-else>
                            需设置此权限，请先在基础权限中选中DIY装修权限。
                        </template>
                    </div>
                </div>
            </div>
            <div slot="footer">
                <el-button size="small" @click="dialogVisible = false">取 消</el-button>
                <el-button size="small" :loading="btnLoading" type="primary" @click="dialogSubmit">确 定
                </el-button>
            </div>
        </el-dialog>
        <app-dialog-template :show="templatePermission.show" @selected="templateSelected" :status="1"
                             :selected="templateList"></app-dialog-template>
        <app-dialog-template :show="templatePermission.show_use" @selected="useTemplateSelected" :status="0"
                             :selected="useTemplateList"></app-dialog-template>

        <el-dialog title="全局权限设置" :visible.sync="globalDialogVisible">
            <el-form v-loading="globalLoading" label-position="left" label-width="180px" size="small">
                <el-form-item label="是否开启默认全局权限">
                    <el-switch
                            v-model="globalPermission.is_open"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item v-if="globalPermission.is_open" label="默认全局权限设定">
                    <el-radio v-model="globalPermission.permission_type" :label="1">有所有权限</el-radio>
                    <el-radio v-model="globalPermission.permission_type" :label="2">无权限</el-radio>
                </el-form-item>
            </el-form>
            <div slot="footer">
                <el-button size="small" @click="globalDialogVisible = false">取 消</el-button>
                <el-button size="small" :loading="btnLoading" type="primary" @click="saveGlobalPermission">确 定
                </el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-batch-permission', {
        template: '#app-batch-permission',
        props: {
            // 列表选中的数据
            chooseList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
        },
        data() {
            return {
                btnLoading: false,
                baseBatchList: [
                    {
                        name: '基础权限',
                        key: 'base-permission',// 唯一
                    },
                    {
                        name: '上传权限',
                        key: 'upload-permission',
                    },
                    {
                        name: '模板权限',
                        key: 'template-permission',
                    },
                ],
                dialogVisible: false,
                currentBatch: 'base-permission',
                // 基础权限
                baseLoading: false,
                permissions: [],
                isBaseIndeterminate: false,
                baseCheckAll: false,
                // 上传权限
                storage: [],
                isUploadIndeterminate: false,
                uploadCheckAll: false,
                // 模板权限
                templatePermission: {
                    show: false,
                    show_use: false,
                    list: [],
                    is_all: '0',
                    is_not_buy: '0',
                    use_all: '0',
                    use_list: [],
                },

                // 全局权限设置
                globalDialogVisible: false,
                globalPermission: {
                    is_open: 0,
                    permission_type: 1,
                },
                globalLoading: false,
            }
        },
        computed: {
            templateShow() {
                if (this.permissions) {
                    for (let i in this.permissions) {
                        if (this.permissions[i].name == 'diy' && this.permissions[i].isCheck) {
                            return true;
                        }
                    }
                }
                return false;
            },
            templateList() {
                let list = [];
                console.log(11)
                this.templatePermission.list.forEach(item => {
                    list.push(item.id);
                });
                return list;
            },
            useTemplateList() {
                let list = [];
                this.templatePermission.use_list.forEach(item => {
                    list.push(item.id);
                });
                return list;
            },
        },
        methods: {
            checkChooseList() {
                if (this.chooseList.length > 0) {
                    return true;
                }
                this.$message.warning('请先勾选要设置的账户');
                return false;
            },
            // 打开批量设置框
            batchSetting() {
                let self = this;
                if (!self.checkChooseList()) {
                    return false;
                }
                self.dialogVisible = true;
                self.getPermissions();
            },
            dialogSubmit() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'admin/user/batch-permission',
                    },
                    method: 'post',
                    data: {
                        form: self.getSubmitData()
                    },
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.dialogVisible = false;
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.btnLoading = false;
                });
            },
            getSubmitData() {
                let self = this;
                let secondaryPermissions = {
                    attachment: [],
                    template: {
                        is_all: 0,
                        use_all: 0,
                        list: [],
                        use_list: [],
                    }
                };
                self.storage.forEach(function (item, index) {
                    if (item.isCheck) {
                        secondaryPermissions.attachment.push(index + 1);
                    }
                });

                let basePermission = [];
                self.permissions.forEach(function (item) {
                    if (item.isCheck) {
                        basePermission.push(item.name)
                    }

                    if (item.name == 'diy') {
                        secondaryPermissions.template.is_all = self.templatePermission.is_all;
                        secondaryPermissions.template.use_all = self.templatePermission.use_all;
                        secondaryPermissions.template.list = self.templatePermission.list;
                        secondaryPermissions.template.use_list = self.templatePermission.use_list;
                    }
                });

                let chooseIdList = [];
                self.chooseList.forEach(function (item) {
                    chooseIdList.push(item.id)
                });

                return JSON.stringify({
                    chooseList: chooseIdList,
                    basePermission: basePermission,
                    secondaryPermissions: secondaryPermissions
                })
            },
            // 基础权限全选
            handleBaseCheckAllChange(val) {
                let self = this;
                self.permissions.forEach(function (item) {
                    item.isCheck = self.baseCheckAll;
                });
                self.isBaseIndeterminate = false;
            },
            handleBaseCheckedCitiesChange(permissionItem) {
                let checkedCount = 0;
                this.permissions.forEach(function (item) {
                    if (item.name === permissionItem.name) {
                        item.isCheck = !item.isCheck;
                    }
                    if (item.isCheck) {
                        checkedCount++
                    }
                });
                this.baseCheckAll = checkedCount === this.permissions.length;
                this.isBaseIndeterminate = checkedCount > 0 && checkedCount < this.permissions.length;
            },
            // 上传权限全选
            handleUploadCheckAllChange(val) {
                let self = this;
                self.storage.forEach(function (item) {
                    item.isCheck = self.uploadCheckAll;
                });
                self.isUploadIndeterminate = false;
            },
            handleUploadCheckedCitiesChange(storageItem) {
                let checkedCount = 0;
                this.storage.forEach(function (item) {
                    if (item.name === storageItem.name) {
                        item.isCheck = !item.isCheck;
                    }
                    if (item.isCheck) {
                        checkedCount++
                    }
                });
                this.uploadCheckAll = checkedCount === this.storage.length;
                this.isUploadIndeterminate = checkedCount > 0 && checkedCount < this.storage.length;
            },
            // 获取基础权限|上传权限
            getPermissions() {
                let self = this;
                self.baseLoading = true;
                request({
                    params: {
                        r: 'admin/user/permissions',
                        id: getQuery('id'),
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        let mall = e.data.data.permissions.mall;
                        self.permissions = [];
                        mall.forEach(function (item) {
                            self.permissions.push({
                                display_name: item.display_name,
                                name: item.name,
                                isCheck: false
                            })
                        });
                        let plugins = e.data.data.permissions.plugins;
                        plugins.forEach(function (item) {
                            self.permissions.push({
                                display_name: item.display_name,
                                name: item.name,
                                isCheck: false
                            })
                        });
                        // 上传权限
                        self.storage = [];
                        let storageList = e.data.data.storage;
                        for (let i in storageList) {
                            self.storage.push({
                                display_name: storageList[i],
                                name: storageList[i],
                                isCheck: false,
                            })
                        }
                        self.baseLoading = false;
                    }
                }).catch(e => {
                    self.baseLoading = false;
                });
            },
            // 模板权限
            templateSelected(data) {
                this.templatePermission.show = false;
                if (data) {
                    this.templatePermission.list = data;
                }
            },
            templateDel(key) {
                this.templatePermission.list.splice(key, 1);
            },
            useTemplateSelected(data) {
                this.templatePermission.show_use = false;
                if (data) {
                    this.templatePermission.use_list = data;
                }
            },
            useTemplateDel(key) {
                this.templatePermission.use_list.splice(key, 1);
            },
            // 全局权限设置
            openGlobalPermission() {
                this.globalDialogVisible = true;
                this.getGlobalPermission();
            },
            getGlobalPermission() {
                let self = this;
                self.globalLoading = true;
                request({
                    params: {
                        r: 'admin/user/get-global-permission',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.globalPermission = e.data.data.option;
                        self.globalLoading = false;
                    }
                }).catch(e => {
                    self.globalLoading = false;
                });
            },
            saveGlobalPermission() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'admin/user/save-global-permission',
                    },
                    method: 'post',
                    data: {
                        globalData: self.globalPermission
                    },
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.globalDialogVisible = false;
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.btnLoading = false;
                });
            }
        },
        created() {
        }
    })
</script>