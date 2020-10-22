<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-dialog-template');
?>

<style>
    .common-width {
        width: 300px;
    }

    .el-card__header {
        height: 60px;
        line-height: 60px;
        padding: 0 20px;
    }

    .el-form-item__label {
        position: relative;
        padding-left: 20px;
        color: #999999;
        font-size: 13px;
    }

    .is-required .el-form-item__label::before {
        content: '' !important;
        background-color: #ff5c5c;
        width: 6px;
        height: 6px;
        border-radius: 3px;
        position: absolute;
        top: 50%;
        margin-top: -3px;
        left: 0;
    }

    .common-width .el-input__inner {
        height: 35px;
        line-height: 35px;
        border-radius: 8px;
    }

    .form .el-form-item {
        margin-bottom: 25px;
        position: relative;
    }

    .form {
        display: flex;
        justify-content: center;
        margin-left: -60px;
        margin-top: 15px;
    }

    .show-password {
        position: absolute;
        right: -30px;
        top: 6.5px;
        height: 22px;
        width: 22px;
        display: block;
        cursor: pointer;
    }

    .permissions-list {
        width: 300px;
    }

    .permissions-item {
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
    }

    .permissions-item.active {
        background-color: #F5FAFF;
        color: #57ADFF;
    }

    .submit-btn {
        height: 32px;
        width: 65px;
        line-height: 32px;
        text-align: center;
        border-radius: 16px;
        padding: 0;
    }
</style>

<div id="app" v-cloak>
    <el-card v-loading="cardLoading" class="box-card">
        <div slot="header" class="clearfix">
            <span v-if="isDisabled">编辑子账户信息</span>
            <span v-else>新增子账户信息</span>
            <span style="float: right;">
                当前子账户数量：{{cloudAccount.user_num}},
                最大子账户数量：{{cloudAccount.account_num == -1 ? "无限制" : cloudAccount.account_num}}
            </span>
        </div>
        <div class="form">
            <el-form ref="form" label-position="left" :model="form" :rules="rules" label-width="120px" size="small">
                <el-form-item label="用户名" prop="username">
                    <el-input :disabled="isDisabled" class="common-width" v-model="form.username"></el-input>
                </el-form-item>
                <el-form-item v-if="isShow" label="登录密码" prop="password">
                    <el-input type="password" v-if="!show_password" class="common-width"
                              v-model="form.password"></el-input>
                    <el-input type="text" v-if="show_password" class="common-width" v-model="form.password"></el-input>
                    <img class="show-password" v-if="show_password" @click="show_password = !show_password"
                         src="statics/img/admin/show.png" alt="">
                    <img class="show-password" v-if="!show_password" @click="show_password = !show_password"
                         src="statics/img/admin/hide.png" alt="">
                </el-form-item>
                <el-form-item label="手机号" prop="mobile">
                    <el-input class="common-width" v-model="form.mobile"></el-input>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input type="text" class="common-width" v-model="form.adminInfo.remark"></el-input>
                </el-form-item>
                <el-form-item label="小程序数量" prop="adminInfo.app_max_count">
                    <el-input :disabled="isAppMaxCount" type="number" class="common-width"
                              v-model="form.adminInfo.app_max_count">
                    </el-input>
                    <el-checkbox v-model="isAppMaxCount" @change="appMaxCount">无限制</el-checkbox>
                    <div class="common-width" style="color:#BBBBBB;font-size: 11px;line-height: 1;margin-top: 5px">
                        此用户可以创建的小程序的数量
                    </div>
                </el-form-item>
                <el-form-item label="账户有效期" prop="adminInfo.expired_at" ref="expired_at">
                    <el-date-picker :disabled='isExpiredDisabled' class="common-width"
                                    type="datetime"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    placeholder="选择日期"
                                    v-model="form.adminInfo.expired_at">
                    </el-date-picker>
                    <el-checkbox v-model="isCheckExpired" @change="checkExpiredAt">永久</el-checkbox>
                </el-form-item>
                <el-form-item label="权限设置">
                    <div class="permissions-item" @click="clickAll" :class="{active:allIsCheck}">全选</div>
                    <div class="permissions-list">
                        <div v-for="item in permissionList" @click="clickItem(item)" :key="item.name"
                             class="permissions-item" :class="{active:item.isCheck}">{{item.display_name}}
                        </div>
                    </div>

                </el-form-item>
                <el-form-item label="上传权限" v-if="storageShow()">
                    <div class="permissions-list">
                        <div v-for="(item, key) in storage" @click="clickStorage(key)" :key="item.name"
                             class="permissions-item" :class="storageClass(key)">{{item}}
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="模板权限" v-if="templateShow()">
                    <el-form-item label="显示权限">
                        <div flex>
                            <el-button size="mini" type="text" style="margin-right: 10px;" @click="show = true"
                                       v-if="form.adminInfo.secondary_permissions.template.is_all == 0">添加模板</el-button>
                            <el-checkbox v-model="form.adminInfo.secondary_permissions.template.is_all"
                                         true-label="1" false-label="0">全部选择</el-checkbox>
                        </div>
                        <div class="permissions-list" v-if="form.adminInfo.secondary_permissions.template.is_all == 0">
                            <el-tag v-for="(item, key) in form.adminInfo.secondary_permissions.template.list" :key="key"
                                    closable :disable-transitions="true" style="margin-right: 5px;margin-bottom: 5px"
                                    @close="templateDel(key)">{{item.name}}
                            </el-tag>
                        </div>
                    </el-form-item>
                    <el-form-item label="使用权限">
                        <div flex>
                            <el-button size="mini" type="text" style="margin-right: 10px;" @click="show_use = true"
                                       v-if="form.adminInfo.secondary_permissions.template.use_all == 0">添加模板</el-button>
                            <el-checkbox v-model="form.adminInfo.secondary_permissions.template.use_all"
                                         true-label="1" false-label="0">全部选择</el-checkbox>
                        </div>
                        <div class="permissions-list" v-if="form.adminInfo.secondary_permissions.template.use_all == 0">
                            <el-tag v-for="(item, key) in form.adminInfo.secondary_permissions.template.use_list" :key="key"
                                    closable :disable-transitions="true" style="margin-right: 5px;margin-bottom: 5px"
                                    @close="useTemplateDel(key)">{{item.name}}
                            </el-tag>
                        </div>
                    </el-form-item>
                </el-form-item>
                <el-form-item>
                    <el-button :loading="btnLoading" class="submit-btn" type="primary" @click="store('form')">保存
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
    <app-dialog-template :show="show" @selected="templateSelected" :status="1"
                         :selected="templateList"></app-dialog-template>
    <app-dialog-template :show="show_use" @selected="useTemplateSelected" :status="0"
                         :selected="useTemplateList"></app-dialog-template>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                btnLoading: false,
                cardLoading: false,
                show_password: false,
                isShow: true,//输入框是否显示
                isDisabled: false,//输入框是否禁用
                isExpiredDisabled: false,//日期选择框是否禁用
                isCheckExpired: false,//有效期永久是否勾选
                isAppMaxCount: false,//可创建小程序数量是否勾选
                checkAll: false,//权限是否全选
                isIndeterminate: false,//未全选样式
                checkedCities: [],//已勾选的权限
                permissions: [],//权限列表
                storage: null,//上传权限
                cloudAccount: {},
                form: {
                    adminInfo: {
                        expired_at: '',
                        app_max_count: '',
                        secondary_permissions: {
                            attachment: [1, 2, 3, 4],
                            template: {
                                list: [],
                                is_all: '0',
                                is_not_buy: '0',
                                use_all: '0',
                                use_list: [],
                            }
                        }
                    },
                    identity: {},
                },
                rules: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'change'},
                        {min: 4, max: 15, message: '长度在 4 到 15 个字符', trigger: 'change'}
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'change'},
                    ],
                    mobile: [
                        {required: true, message: '请输入手机号', trigger: 'change'},
                    ],
                    'adminInfo.expired_at': [
                        {required: true, message: '请选择账户有效期', trigger: 'change'},
                    ],
                    'adminInfo.app_max_count': [
                        {required: true, message: '请填写可创建小程序数量', trigger: 'change'},
                    ]
                },
                allIsCheck: false,
                show: false,
                show_use: false,
            };
        },
        methods: {
            clickItem(row) {
                let self = this;
                row.isCheck = !row.isCheck;
                let idx = self.permissions.indexOf(row);
                this.$set(self.permissions, idx, row);
                let index = self.checkedCities.indexOf(row.name);
                if (index == -1) {
                    self.checkedCities.push(row.name)
                } else {
                    self.checkedCities.splice(index, 1)
                }

                if (self.permissions.length == self.checkedCities.length) {
                    self.allIsCheck = true;
                } else  {
                    self.allIsCheck = false;
                }
            },

            clickAll() {
                let self = this;
                self.allIsCheck = self.allIsCheck ? false : true;
                self.checkedCities = [];
                if (self.allIsCheck) {
                    self.permissions.forEach(function (item, index) {
                        item.isCheck= true;
                        self.checkedCities.push(item.name)
                    })
                    this.form.adminInfo.secondary_permissions.template.is_all = '1';
                } else {
                    self.permissions.forEach(function (item, index) {
                        item.isCheck= false;
                    })
                    this.form.adminInfo.secondary_permissions.template.is_all = '0';
                }
            },

            store(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'admin/user/edit',
                            },
                            method: 'post',
                            data: {
                                form: self.form,
                                permissions: self.checkedCities,
                                isCheckExpired: self.isCheckExpired ? 1 : 0,
                                isAppMaxCount: self.isAppMaxCount ? 1 : 0,
                            },
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                self.$message.success(e.data.msg);
                                window.location.href = e.data.data.url;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getPermsssions() {
                let self = this;
                request({
                    params: {
                        r: 'admin/user/permissions',
                        id: getQuery('id'),
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.permissions = self.permissions.concat(e.data.data.permissions.mall);
                        self.permissions = self.permissions.concat(e.data.data.permissions.plugins);
                        for (let i = 0; i < self.permissions.length; i++) {
                            self.permissions[i].isCheck = false
                        }
                        self.storage = e.data.data.storage;
                    }
                }).catch(e => {

                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'admin/user/edit',
                        id: getQuery('id'),
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code === 0) {
                        self.form = e.data.data.detail;
                        if (self.form.adminInfo.expired_at == '0000-00-00 00:00:00') {
                            self.isCheckExpired = true;
                            self.isExpiredDisabled = true;
                            self.form.adminInfo.expired_at = '0000-00-00';
                        }
                        if (self.form.adminInfo.app_max_count == -1) {
                            self.isAppMaxCount = true;
                        }
                        self.checkedCities = self.form.adminInfo.permissions;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            // 账户有效期永久事件
            checkExpiredAt(value) {
                this.isCheckExpired = value;
                this.isExpiredDisabled = value;
                if (value) {
                    this.form.adminInfo.expired_at = '0000-00-00';
                } else {
                    this.form.adminInfo.expired_at = '';
                }
                this.$refs['expired_at'].clearValidate();
            },
            // 创建小程序数量无限制事件
            appMaxCount(value) {
                this.isAppMaxCount = value;
                if (value) {
                    this.form.adminInfo.app_max_count = -1;
                } else {
                    this.form.adminInfo.app_max_count = '';
                }
                this.$refs['app_max_count'].clearValidate();
            },
            // 权限全选事件
            handleCheckAllChange(val) {
                let self = this;
                self.checkedCities = [];
                if (val) {
                    self.permissions.forEach(function (item, index) {
                        self.checkedCities.push(item.name)
                    });
                }
                self.isIndeterminate = false;
            },
            getCloudAccount() {
                let self = this;
                request({
                    params: {
                        r: 'admin/user/cloud-account',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.cloudAccount = e.data.data
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            storageClass(key) {
                if (!this.form.adminInfo.secondary_permissions || !this.form.adminInfo.secondary_permissions.attachment) {
                    return ``;
                }
                let attachment = this.form.adminInfo.secondary_permissions.attachment;
                for (let i in attachment) {
                    if (key == attachment[i]) {
                        return `active`;
                    }
                }
                return ``;
            },
            clickStorage(key) {
                if (!this.form.adminInfo.secondary_permissions || !this.form.adminInfo.secondary_permissions.attachment) {
                    this.form.adminInfo.secondary_permissions.attachment = [key];
                } else {
                    let attachment = this.form.adminInfo.secondary_permissions.attachment;
                    for (let i in attachment) {
                        if (key == attachment[i]) {
                            this.form.adminInfo.secondary_permissions.attachment.splice(i, 1);
                            return true;
                        }
                    }
                    this.form.adminInfo.secondary_permissions.attachment.push(key);
                }
            },
            storageShow() {
                if (!this.storage) {
                    return false;
                }
                if (this.permissions) {
                    for (let i in this.permissions) {
                        if (this.permissions[i].name == 'attachment' && this.permissions[i].isCheck) {
                            return true;
                        }
                    }
                }
                return false;
            },
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
            templateSelected(data) {
                this.show = false;
                if (data) {
                    this.form.adminInfo.secondary_permissions.template.list = data;
                }
            },
            templateDel(key) {
                this.form.adminInfo.secondary_permissions.template.list.splice(key, 1);
            },
            useTemplateSelected(data) {
                this.show_use = false;
                if (data) {
                    this.form.adminInfo.secondary_permissions.template.use_list = data;
                }
            },
            useTemplateDel(key) {
                this.form.adminInfo.secondary_permissions.template.use_list.splice(key, 1);
            },
        },
        mounted: function () {
            this.getPermsssions();
            this.getCloudAccount();
            if (getQuery('id')) {
                this.getDetail();
                this.isShow = false;
                this.isDisabled = true;
            }
        },
        computed: {
            permissionList() {
                let self = this;
                let permissions = self.permissions;
                if (permissions.length == 0) {
                    return permissions;
                }
                if (self.checkedCities.length == 0) {
                    return permissions;
                }
                permissions.forEach((item) => {
                    for (let i in self.checkedCities) {
                        if (item.name === self.checkedCities[i]) {
                            item.isCheck = true;
                        }
                    }
                });
                if (permissions.length == self.checkedCities.length) {
                    self.allIsCheck = true;
                } else  {
                    self.allIsCheck = false;
                }
                return permissions;
            },
            templateList() {
                if (typeof this.form.adminInfo.secondary_permissions.template == 'undefined') {
                    return [];
                }
                let list = [];
                this.form.adminInfo.secondary_permissions.template.list.forEach(item => {
                    list.push(item.id);
                });
                return list;
            },
            useTemplateList() {
                if (typeof this.form.adminInfo.secondary_permissions.template == 'undefined') {
                    return [];
                }
                let list = [];
                this.form.adminInfo.secondary_permissions.template.use_list.forEach(item => {
                    list.push(item.id);
                });
                return list;
            },
        }
    });
</script>
