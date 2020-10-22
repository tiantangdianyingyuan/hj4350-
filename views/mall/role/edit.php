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
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/role/index'})">角色管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>角色编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form @submit.native.prevent :model="ruleForm" :rules="rules" ref="ruleForm" label-width="120px" size="small">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="角色名称" prop="name">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="备注/描述" prop="remark">
                            <el-input type="textarea" v-model="ruleForm.remark"></el-input>
                        </el-form-item>
                        <el-form-item label="角色权限">
                            <el-tree
                                    v-loading="treeLoading"
                                    :data="permissions"
                                    show-checkbox
                                    node-key="route"
                                    :default-checked-keys="defaultCheckedKeys"
                                    ref="tree"
                                    :props="defaultProps">
                            </el-tree>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>  
        </div>
        <el-button :loading="btnLoading" class="button-item" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    remark: ''
                },
                permissions: [],
                rules: {
                    name: [
                        {required: true, message: '请输入角色名称', trigger: 'change'},
                    ]
                },
                treeLoading: false,
                btnLoading: false,
                defaultProps: {
                    label: 'name'
                },
                defaultCheckedKeys: []
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
                                r: 'mall/role/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                                permissions: JSON.stringify(self.$refs.tree.getCheckedKeys()),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/role/index'
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
            getPermissions() {
                let self = this;
                self.treeLoading = true;
                request({
                    params: {
                        r: 'mall/role/permissions',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.treeLoading = false;
                    self.permissions = e.data.data.permissions;
                    self.defaultCheckedKeys = e.data.data.defaultCheckedKeys;
                }).catch(e => {
                    console.log(e);
                });
            },
            getDetail() {
                let self = this;
                request({
                    params: {
                        r: 'mall/role/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.ruleForm = e.data.data.detail;
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getPermissions();
        }
    });
</script>
