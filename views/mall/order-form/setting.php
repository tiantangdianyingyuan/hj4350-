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
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="cardLoading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/order-form/list'})">
                        表单列表
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>表单设置</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form :model="ruleForm" @submit.native.prevent :rules="rules" size="small" ref="ruleForm" label-width="120px">
            <div class="form-body">
                <el-row>
                    <el-col :span="24">
                        <el-form-item prop="status" style="width: 180px">
                            <template slot='label'>
                                <span>表单状态</span>
                                <el-tooltip effect="dark"
                                        placement="top">
                                    <div slot="content">此表单只适用于结算页面<br/>开启自定义表单后，结算页面的买家留言将取消</div>
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <div>
                                <el-switch
                                        v-model="ruleForm.status"
                                        :active-value="1"
                                        :inactive-value="0">
                                </el-switch>
                            </div>
                        </el-form-item>
                        <template v-if="ruleForm.status == 1">
                            <el-form-item label="表单名称" prop="name" style="width: 500px">
                                <el-input v-model="ruleForm.name" placeholder="请输入表单名称"></el-input>
                            </el-form-item>
                            <el-form-item label="表单设置" prop="selectedOptions">
                                <app-form :value.sync="ruleForm.value"></app-form>
                            </el-form-item>
                        </template>
                    </el-col>
                </el-row>
            </div>
            <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">
                保存
            </el-button>
        </el-form>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                rules: {
                    name: [
                        {required: true, message: '请输入表单名称', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '请选择表单状态', trigger: 'change'},
                    ],
                },
                ruleForm: {
                    status: 0,
                    name: '',
                    value: [],
                    id: 0,
                },
                btnLoading: false,
                cardLoading: false,
                selectedOptions: [],
            };
        },
        methods: {
            store(formName) {
                let self = this;
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        if (self.ruleForm.status == 1 && !this.ruleForm.value.length) {
                            this.$message.error('至少添加一项表单设置');
                            return;
                        }
                        self.ruleForm.value.forEach(function (item, index) {
//                            if (item.key === 'date') {
//                                item.default = dayjs(item.default).isValid()
//                                    ? dayjs(item.default).format('YYYY-MM-DD') : '';
//                            }
                        });

                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/order-form/setting'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/order-form/list'
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
            getDetail(id) {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/order-form/setting',
                        id: id
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
            } else {
                this.ruleForm.status = 1;
            }
        }
    });
</script>
