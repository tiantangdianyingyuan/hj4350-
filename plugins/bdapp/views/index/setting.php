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
        <div slot="header">百度小程序配置</div>
        <div class="form-body">
            <el-form :model="form" :rules="rules" ref="form">
                <el-form-item label="小程序AppID" prop="app_id">
                    <el-input v-model="form.app_id"></el-input>
                </el-form-item>
                <el-form-item label="小程序AppKey" prop="app_key">
                    <el-input v-model="form.app_key"></el-input>
                </el-form-item>
                <el-form-item label="小程序AppSecret" prop="app_secret">
                    <el-input @focus="hidden.app_secret = false"
                              v-if="hidden.app_secret"
                              readonly
                              placeholder="已隐藏内容，点击查看或编辑">
                    </el-input>
                    <el-input v-else v-model="form.app_secret"></el-input>
                </el-form-item>

                <el-form-item label="pay_dealid" prop="pay_dealid">
                    <el-input v-model="form.pay_dealid"></el-input>
                </el-form-item>
                <el-form-item label="支付key" prop="pay_app_key">
                    <el-input v-model="form.pay_app_key"></el-input>
                </el-form-item>

                <el-form-item label="支付应用私钥" prop="pay_private_key">
                    <el-input @focus="hidden.pay_private_key = false"
                              v-if="hidden.pay_private_key"
                              readonly
                              placeholder="已隐藏内容，点击查看或编辑">
                    </el-input>
                    <el-input v-else v-model="form.pay_private_key" type="textarea" rows="5" class="key-textarea"></el-input>
                </el-form-item>

                <el-form-item label="平台公钥" prop="pay_public_key">
                    <el-input v-model="form.pay_public_key" type="textarea" rows="5" class="key-textarea"></el-input>
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
                hidden: {
                    app_secret: true,
                    pay_dealid: '',
                    pay_app_key: '',
                    pay_private_key: true,
                    pay_public_key: '',
                },
                form: {
                    app_id: '',
                    app_key: '',
                    app_secret: '',
                    pay_dealid: '',
                    pay_app_key: '',
                    pay_private_key: '',
                    pay_public_key: '',
                },
                rules: {
                    app_id: [{required: true, message: '请填写小程序AppId'}],
                    app_key: [{required: true, message: '请填写小程序AppKey'}],
                    app_secret: [{required: true, message: '请填写小程序AppSecret'}],
                    pay_dealid: [{required: true, message: '请填写支付dealId'}],
                    pay_app_key: [{required: true, message: '请填写支付key'}],
                    pay_private_key: [{required: true, message: '请填写支付应用私钥'}],
                    pay_public_key: [{required: true, message: '请填写平台公钥'}],
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
                        r: 'plugin/bdapp/index/setting',
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
                                r: 'plugin/bdapp/index/setting',
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