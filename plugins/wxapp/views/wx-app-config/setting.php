<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form_box {
        background-color: #fff;
        padding: 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">微信小程序配置</div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="支付类型选择" prop="is_choise">
                            <el-radio-group v-model="ruleForm.is_choise">
                                <el-radio :label="0">普通商户</el-radio>
                                <el-radio :label="1">服务商</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="小程序AppId" prop="appid">
                            <el-input v-model.trim="ruleForm.appid"></el-input>
                        </el-form-item>
                        <el-form-item label="小程序appSecret" prop="appsecret">
                            <el-input @focus="hidden.appsecret = false"
                                      v-if="hidden.appsecret"
                                      readonly
                                      placeholder="已隐藏内容，点击查看或编辑">
                            </el-input>
                            <el-input v-else v-model.trim="ruleForm.appsecret"></el-input>
                        </el-form-item>

                        <el-form-item label="微信支付商户号" prop="mchid" v-if="ruleForm.is_choise == 0">
                            <el-input v-model.trim="ruleForm.mchid"></el-input>
                        </el-form-item>

                        <el-form-item label="特约商户商户号" prop="mchid" v-if="ruleForm.is_choise == 1">
                            <el-input v-model.trim="ruleForm.mchid"></el-input>
                        </el-form-item>

                        <template v-if="ruleForm.is_choise != 1">
                            <el-form-item label="微信支付Api密钥" prop="key">
                                <el-input @focus="hidden.key = false"
                                          v-if="hidden.key"
                                          readonly
                                          placeholder="已隐藏内容，点击查看或编辑">
                                </el-input>
                                <el-input v-else v-model.trim="ruleForm.key"></el-input>
                            </el-form-item>
                            <el-form-item label="微信支付apiclient_cert.pem" prop="cert_pem">
                                <el-input @focus="hidden.cert_pem = false"
                                          v-if="hidden.cert_pem"
                                          readonly
                                          type="textarea"
                                          :rows="5"
                                          placeholder="已隐藏内容，点击查看或编辑">
                                </el-input>
                                <el-input v-else type="textarea" :rows="5" v-model="ruleForm.cert_pem"></el-input>
                            </el-form-item>
                            <el-form-item label="微信支付apiclient_key.pem" prop="key_pem">
                                <el-input @focus="hidden.key_pem = false"
                                          v-if="hidden.key_pem"
                                          readonly
                                          type="textarea"
                                          :rows="5"
                                          placeholder="已隐藏内容，点击查看或编辑">
                                </el-input>
                                <el-input v-else type="textarea" :rows="5" v-model="ruleForm.key_pem"></el-input>
                            </el-form-item>
                        </template>
                        <template v-else>
                            <el-form-item label="服务商AppId" prop="service_appid">
                                <el-input v-model.trim="ruleForm.service_appid"></el-input>
                            </el-form-item>
                            <el-form-item label="服务商商户号" prop="service_mchid">
                                <el-input v-model.trim="ruleForm.service_mchid"></el-input>
                            </el-form-item>
                            <el-form-item label="微信支付服务商Api密钥" prop="service_key">
                                <el-input @focus="hidden.key = false"
                                          v-if="hidden.key"
                                          readonly
                                          placeholder="已隐藏内容，点击编辑">
                                </el-input>
                                <el-input v-else v-model.trim="ruleForm.service_key"></el-input>
                            </el-form-item>
                            <el-form-item label="微信支付服务商apiclient_cert.pem">
                                <app-upload @complete="updateSuccess" accept="" :params="params_cert"
                                            :simple="true" style="display: inline-block">
                                    <el-button size="small">上传文件</el-button>
                                </app-upload>
                            </el-form-item>
                            <el-form-item label="微信支付服务商apiclient_key.pem">
                                <app-upload @complete="updateSuccess" accept="" :params="params_key"
                                            :simple="true" style="display: inline-block">
                                    <el-button size="small">上传文件</el-button>
                                </app-upload>
                            </el-form-item>
                        </template>
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class='button-item' :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                params_key: {
                    r: 'plugin/wxapp/wx-app-config/upload-pem',
                    type: 'key',
                },
                params_cert: {
                    r: 'plugin/wxapp/wx-app-config/upload-pem',
                    type: 'cert',
                },
                hidden: {
                    appid: true,
                    appsecret: true,
                    mchid: false,
                    key: true,
                    cert_pem: true,
                    key_pem: true,
                    service_key: true
                },
                ruleForm: {
                    appid: '',
                    appsecret: '',
                    cert_pem: '',
                    key: '',
                    key_pem: '',
                    mchid: '',
                    is_choise: '',
                    service_appid: '',
                    service_mchid: '',
                    service_key: ''
                },
                rules: {
                    appid: [
                        {required: true, message: '请输入appid', trigger: 'change'},
                    ],
                    appsecret: [
                        {required: true, message: '请输入appsecret', trigger: 'change'},
                    ],
                    key: [
                        {required: true, message: '请输入key', trigger: 'change'},
                        {max: 32, message: '微信支付Api密钥最多为32个字符', trigger: 'change'},
                    ],
                    is_choise: [
                        {required: true, message: '请选择类型', trigger: 'change'},
                    ],
                    mchid: [
                        {required: true, message: '请输入mchid', trigger: 'change'},
                    ],
                    service_appid: [
                        {required: true, message: '请输入服务商appid', trigger: 'change'},
                    ],
                    service_mchid: [
                        {required: true, message: '请输入服务商mchid', trigger: 'change'},
                    ],
                    service_key: [
                        {required: false, message: '请输入key', trigger: 'change'},
                        {max: 32, message: '微信支付服务商Api密钥最多为32个字符', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
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
                                r: 'plugin/wxapp/wx-app-config/setting'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/wxapp/wx-app-config/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                        self.params_key.id = e.data.data.detail.id;
                        self.params_cert.id = e.data.data.detail.id;
                    } else {
                        self.$message.error(e.data.msg);
                        self.rules.service_key[0].required = true;
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            updateSuccess(e) {
                if (e[0].response.data.code == 0) {
                    this.$message.success('上传成功')
                }
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
