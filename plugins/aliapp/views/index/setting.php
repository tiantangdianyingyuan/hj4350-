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
        <div slot="header">支付宝小程序配置</div>
        <div class="form-body">
            <el-form :model="form" :rules="rules" ref="form">
                <el-form-item label="小程序AppID" prop="appid">
                    <el-input v-model="form.appid"></el-input>
                </el-form-item>
                <el-form-item label="支付宝公钥" prop="alipay_public_key">
                    <el-input v-model="form.alipay_public_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
                <el-form-item label="应用私钥" prop="app_private_key">
                    <el-input v-model="form.app_private_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
                <el-form-item label="AES密钥(授权手机号)" prop="app_aes_secret">
                    <el-input v-model="form.app_aes_secret"></el-input>
                </el-form-item>
                <el-form-item label="云客服TntInstId" prop="cs_tnt_inst_id">
                    <el-input v-model="form.cs_tnt_inst_id"></el-input>
                </el-form-item>
                <el-form-item label="云客服Scene" prop="cs_scene">
                    <el-input v-model="form.cs_scene"></el-input>
                </el-form-item>

                <el-form-item label="app_id（转账）" prop="transfer_app_id">
                    <el-input v-model="form.transfer_app_id"></el-input>
                </el-form-item>
                <el-form-item label="应用私钥（转账）" prop="transfer_app_private_key">
                    <el-input v-model="form.transfer_app_private_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
                <el-form-item label="支付宝公钥（转账）" prop="transfer_alipay_public_key">
                    <el-input v-model="form.transfer_alipay_public_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
                <el-form-item label="应用公钥证书（转账）" prop="transfer_appcert">
                    <el-input v-model="form.transfer_appcert" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>
                <el-form-item label="支付宝根证书（转账）" prop="transfer_alipay_rootcert">
                    <el-input v-model="form.transfer_alipay_rootcert" type="textarea" rows="5" class="key-textarea"></el-input>
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
                    appid: '',
                    alipay_public_key: '',
                    app_private_key: '',
                    cs_tnt_inst_id: '',
                    cs_scene: '',
                    app_aes_secret: '',
                    transfer_app_id: '',
                    transfer_app_private_key: '',
                    transfer_alipay_public_key: '',
                    transfer_appcert:'',
                    transfer_alipay_rootcert:''
                },
                rules: {
                    appid: [{required: true, message: '请填写小程序AppID'}],
                    alipay_public_key: [{required: true, message: '请填写支付宝公钥'}],
                    app_private_key: [{required: true, message: '请填写应用私钥'}],
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
                        r: 'plugin/aliapp/index/setting',
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
                                r: 'plugin/aliapp/index/setting',
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