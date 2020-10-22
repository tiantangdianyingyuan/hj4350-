<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/18
 * Time: 16:09
 */

/* @var $this \yii\web\View */
?>
<style>
    .key-textarea textarea{
        font-family: SFMono-Regular, Consolas !important;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 30% 20px 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">头条小程序配置</div>
        <div class="form-body">
            <el-form :model="form" :rules="rules" ref="form">
                <el-form-item label="AppID(小程序Key)" prop="app_key">
                    <el-input v-model="form.app_key"></el-input>
                </el-form-item>
                <el-form-item label="AppSecret(小程序Key)" prop="app_secret">
                    <el-input @focus="hidden.app_secret = false"
                              v-if="hidden.app_secret"
                              readonly
                              placeholder="已隐藏内容，点击查看或编辑">
                    </el-input>
                    <el-input v-else v-model="form.app_secret"></el-input>
                </el-form-item>
                <el-form-item label="商户号(支付设置)" prop="mch_id">
                    <el-input v-model="form.mch_id"></el-input>
                </el-form-item>
                <el-form-item label="App ID(支付设置)" prop="pay_app_id">
                    <el-input v-model="form.pay_app_id"></el-input>
                </el-form-item>
                <el-form-item label="支付secret(支付设置)" prop="pay_app_secret">
                    <el-input @focus="hidden.pay_app_secret = false"
                              v-if="hidden.pay_app_secret"
                              readonly
                              placeholder="已隐藏内容，点击查看或编辑">
                    </el-input>
                    <el-input v-else v-model="form.pay_app_secret"></el-input>
                </el-form-item>
                <el-form-item label="支付宝应用app_id" prop="alipay_app_id">
                    <el-input v-model="form.alipay_app_id"></el-input>
                </el-form-item>
                <el-form-item label="支付宝公钥alipay_public_key" prop="alipay_public_key">
                    <el-input v-model="form.alipay_public_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
                <el-form-item label="支付宝应用私钥alipay_private_key" prop="alipay_private_key">
                    <el-input @focus="hidden.alipay_private_key = false"
                              v-if="hidden.alipay_private_key"
                              readonly
                              placeholder="已隐藏内容，点击查看或编辑">
                    </el-input>
                    <el-input v-else v-model="form.alipay_private_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="submitLoading" @click="submit('form')" type="primary">保存</el-button>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                form: {
                    app_key: '',
                    app_secret: '',
                    mch_id: '',
                    pay_app_id: '',
                    pay_app_secret: '',
                    alipay_app_id: '',
                    alipay_public_key: '',
                    alipay_private_key: '',
                },
                hidden: {
                    app_secret: true,
                    pay_app_secret: true,
                    alipay_private_key: true,
                },
                rules: {
                    app_key: [{required: true, message: '请填写小程序AppKey'}],
                    app_secret: [{required: true, message: '请填写小程序AppSecret'}],
                    mch_id: [{required: true, message: '请填写小程序商户号'}],
                    pay_app_id: [{required: true, message: '请填写头条支付secret'}],
                    pay_app_secret: [{required: true, message: '请填写支付key'}],
                    alipay_app_id: [{required: true, message: '请填写支付宝app_id'}],
                    alipay_public_key: [{required: true, message: '请填写支付宝支付公钥'}],
                    alipay_private_key: [{required: true, message: '请填写支付宝支付私钥'}],
                },
                submitLoading: false,
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/ttapp/index/setting',
                    },
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        if (response.data.data) {
                            this.form = response.data.data;
                        }
                    } else {
                    }
                }).catch(e => {
                });
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        this.$request({
                            params: {
                                r: 'plugin/ttapp/index/setting',
                            },
                            method: 'post',
                            data: this.form,
                        }).then(response => {
                            this.submitLoading = false;
                            if (response.data.code === 0) {
                                this.$message.success(response.data.msg);
                            } else {
                                this.$alert(response.data.msg);
                            }
                        }).catch(e => {
                        });
                    }
                });
            },
        },
    });
</script>
