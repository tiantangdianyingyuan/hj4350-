<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/role-user/index'})">用户管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>用户编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-row class="form-body">
            <el-col :span="12">
                <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="120px" size="small">
                    <el-form-item label="用户名" prop="username">
                        <el-input :disabled="isDisabled" v-model="ruleForm.username"></el-input>
                    </el-form-item>
                    <el-form-item v-if="isShow" label="密码" prop="password">
                        <el-input type="password" v-model="ruleForm.password"></el-input>
                    </el-form-item>
                    <el-form-item label="昵称" prop="nickname">
                        <el-input v-model="ruleForm.nickname"></el-input>
                    </el-form-item>
                    <el-form-item label="角色">
                        <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll"
                                     @change="handleCheckAllChange">
                            全选
                        </el-checkbox>
                        <div style="margin: 15px 0;"></div>
                        <el-checkbox-group v-model="checkedCities" @change="handleCheckedCitiesChange">
                            <el-checkbox v-for="item in checkList" :label="item.id" :key="item.id">
                                {{item.name}}
                            </el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {},
                rules: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'change'},
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'change'},
                    ],
                    nickname: [
                        {required: true, message: '请输入昵称', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                checkAll: false,//是否全选
                isIndeterminate: false,//未全选样式
                checkedCities: [],//已勾选的
                checkList: [],//列表
                isShow: true,//输入框是否显示
                isDisabled: false,//输入框是否禁用
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/role-user/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                                roles: self.checkedCities,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/role-user/index'
                                })
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
            getRoleList() {
                let self = this;
                request({
                    params: {
                        r: 'mall/role-user/role-list',
                    },
                    method: 'get',
                }).then(e => {
                    self.checkList = e.data.data.list;
                }).catch(e => {
                    console.log(e);
                });
            },
            getDetail() {
                let self = this;
                request({
                    params: {
                        r: 'mall/role-user/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.ruleForm = e.data.data.detail;
                        self.checkedCities = e.data.data.checkedKeys;
                        self.handleCheckedCitiesChange(self.checkedCities);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 全选事件
            handleCheckAllChange(val) {
                let self = this;
                self.checkedCities = [];
                if (val) {
                    self.checkList.forEach(function (item, index) {
                        self.checkedCities.push(item.id)
                    });
                }
                self.isIndeterminate = false;
            },
            // 单选事件
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.checkList.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.checkList.length;
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
                this.isShow = false;
                this.isDisabled = true;
            }

            this.getRoleList();
        }
    });
</script>
