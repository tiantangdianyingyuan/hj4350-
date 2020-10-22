<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/15
 * Time: 17:10
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
    }

    .tip {
        margin-left: 78px;
        margin-bottom: 20px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <span>基本配置</span>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="150px" :rules="rule" ref="form" size="mini">
                <div class="tip">请先购买接口，购买地址：<a href="https://www.99api.com/Login?log=5&referee=2262" target="_blank">https://www.99api.com/Login?log=5&referee=2262</a></div>
                <el-form-item label="Api Key" prop="api_key">
                    <el-input class="ml-24" style="width: 600px" placeholder="请输入api key"
                              maxlength="32"
                              show-word-limit
                              v-model="form.api_key"></el-input>
                </el-form-item>
                <el-form-item label="Api Key" prop="api_key" hidden>
                    <el-input class="ml-24" style="width: 600px" placeholder="请输入api key"
                              v-model="form.api_key"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" size="small" :loading="btnLoading" @click="onSubmit">提交</el-button>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {
                    api_key: ''
                },
                rule: {
                    api_key: [
                        {required: true, message: '请输入api key', trigger: 'blur'},
                    ]
                }
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/assistant/mall/index/index',
                    },
                    method: 'get'
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data;
                    } else {
                        this.$message.fail(e.data.data.msg)
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/assistant/mall/index/index',
                            },
                            data: this.form,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg)
                            } else {
                                this.$message.error(e.data.msg)
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            }
        }
    });
</script>
