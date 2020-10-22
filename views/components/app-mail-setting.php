<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/16
 * Time: 15:25
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .form-body {
        padding: 20px;
        padding-right: 50%;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<template id="app-mail-setting">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-cloak
             v-loading="listLoading">
        <el-form size="small" :model="ruleForm" ref="ruleForm" :rules="rules" label-width="150px">
            <div class="form-body">
                <el-form-item label="邮件提醒" prop="status">
                    <el-switch
                            v-model="ruleForm.status"
                            :active-value="1"
                            :inactive-value="0"
                            active-color="#409EFF">
                    </el-switch>
                </el-form-item>
                <el-form-item v-if="ruleForm.status == 1" label="邮箱参数" prop="show_type">
                    <el-checkbox-group v-model="show_type">
                        <el-checkbox label="attr">规格显示</el-checkbox>
                        <el-checkbox label="goods_no">货号显示</el-checkbox>
                        <el-checkbox label="form_data">下单表单显示</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <template v-if="ruleForm.status == 1">
                    <el-form-item label="发送平台" prop="status">
                        <span>QQ邮箱</span>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.status == 1" label="发件人邮箱" prop="send_mail">
                        <el-input v-model="ruleForm.send_mail"></el-input>
                    </el-form-item>
                    <el-form-item label="授权码" prop="send_pwd">
                        <el-input @focus="updateHideStatus"
                                  v-if="hide"
                                  readonly
                                  placeholder="授权码 被隐藏,点击查看">
                        </el-input>
                        <el-input v-else v-model="ruleForm.send_pwd"></el-input>
                        <div class="fs-sm">
                            <el-button @click="goto" type="text" style="color: #92959B">什么是授权码<i
                                    class="el-icon-question"></i></el-button>
                        </div>
                    </el-form-item>
                    <el-form-item label="发件平台名称" prop="send_name">
                        <el-input v-model="ruleForm.send_name"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <template slot="label">
                            <span>收件人邮箱</span>
                            <el-tooltip effect="dark" content="请输入邮箱后,按回车键"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-tag v-if="ruleForm.receive_mail.length"
                                style="margin-right: 5px;"
                                v-for="(item, index) in ruleForm.receive_mail"
                                @close="deleteMail(index)"
                                :key="item.id"
                                closable>
                            {{item}}
                        </el-tag>
                        <el-input style="width: 200px"
                                  @keyup.enter.native="addMail"
                                  v-model="mail"
                                  placeholder="请输入邮箱后,按回车键">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="" prop="receive_mail">
                        <el-button size="small" @click="testSubmit('ruleForm')"
                                   :loading="testLoading">测试发送
                        </el-button>
                    </el-form-item>
                </template>
            </div>
            <el-form-item class="form-button">
                <el-button sizi="mini" class="button-item" :loading="submitLoading" type="primary"
                           @click="onSubmit('ruleForm')">
                    保存
                </el-button>
            </el-form-item>
        </el-form>
    </el-card>
</template>
<script>
    Vue.component('app-mail-setting', {
        template: '#app-mail-setting',
        data() {
            let validator = (rule, value, callback) => {
                if (this.ruleForm.status == 1) {
                    if (value == '') {
                        callback(new Error('请输入发件人邮箱'));
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            };
            return {
                show_type: [],
                ruleForm: {
                    status: 0,
                    send_mail: '',
                    send_pwd: '',
                    send_name: '',
                    receive_mail: [],
                    test: 0,
                    show_type: [],
                },
                mail: '',
                testLoading: false,
                submitLoading: false,
                listLoading: false,
                rules: {
                    send_mail: [
                        {
                            validator: validator, trigger: 'blur', message: '请输入发件人邮箱'
                        }
                    ],
                    send_pwd: [
                        {
                            validator: validator, trigger: 'blur', message: '请输入授权码'
                        }
                    ],
                    send_name: [
                        {
                            validator: validator, trigger: 'blur', message: '请输入发件平台名称'
                        }
                    ]
                },
                hide: true
            }
        },
        watch: {
            show_type: {
                handler(v) {
                    this.ruleForm.show_type = {
                        'attr': v.indexOf('attr') === -1 ? 0 : 1,
                        'goods_no': v.indexOf('goods_no') === -1 ? 0 : 1,
                        'form_data': v.indexOf('form_data') === -1 ? 0 : 1,
                    };
                },
            }
        },
        mounted: function () {
            this.load();
        },
        methods: {
            load() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/index/mail'
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    this.ruleForm = e.data.data.model;

                    let show_type = [];
                    for (let key in this.ruleForm.show_type) {
                        if (this.ruleForm.show_type[key] == 1) show_type.push(key)
                    }
                    this.show_type = show_type;
                }).catch(e => {
                    this.listLoading = false;
                    this.$message.error(e.data.msg);
                });
            },
            onSubmit(formName) {
                this.ruleForm.test = 0;
                this.submitLoading = true;
                this.submit(formName);
            },
            updateHideStatus() {
                this.hide = false;
            },
            goto() {
                navigateTo('https://service.mail.qq.com/cgi-bin/help?subtype=1&&no=1001256&&id=28', true);
            },
            testSubmit(formName) {
                this.ruleForm.test = 1;
                this.testLoading = true;
                this.submit(formName)
            },
            submit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        request({
                            params: {
                                r: 'mall/index/mail'
                            },
                            method: 'post',
                            data: {
                                form: this.ruleForm
                            }
                        }).then(e => {
                            this.submitLoading = false;
                            this.testLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.submitLoading = false;
                            this.testLoading = false;
                            this.$message.error(e.data.msg);
                        });
                    } else {
                        console.log('error submit!!');
                        this.submitLoading = false;
                        return false;
                    }
                })
            },
            addMail() {
                if (this.mail) {
                    let flag = true;
                    if (this.ruleForm.receive_mail) {
                        this.ruleForm.receive_mail.forEach((item, index) => {
                            if (this.mail === item) {
                                flag = false;
                                return;
                            }
                        })
                    }
                    if (flag) {
                        this.ruleForm.receive_mail.push(this.mail);
                        this.mail = '';
                    }
                }
            },
            deleteMail(index) {
                this.ruleForm.receive_mail.splice(index, 1);
            }
        }
    });
</script>

