<?php
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-rich-text');
?>

<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }
    .poster-form-title {
        padding: 24px 25% 24px 18px;
        border-bottom: 1px solid #ebeef5;
    }
</style>

<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="loading">
        <el-form :model="form" label-width="150px" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本设置" class="form-body" name="first">
                    <app-setting
                            v-model="form"
                            :is_coupon="true"
                            :is_payment="false"
                            :is_send_type="false"
                            :is_full_reduce="true"
                            :is_territorial_limitation="true"
                            :is_offer_price="true"
                    ></app-setting>
                    <div class="el-card" style="padding-right: 0;margin-bottom: 20px">
                        <div class="poster-form-title" style="margin-bottom: 24px;">活动规则设置</div>
                        <div style="width: 50%">
                            <el-form-item label="活动规则">
                                <div style="width: 458px; min-height: 458px;">
                                    <app-rich-text v-model="form.content"></app-rich-text>
                                </div>
                            </el-form-item>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>

            <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small">
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
                loading: false,
                btnLoading: false,
                form: {
                    is_coupon: 0,
                    is_integral: 0,
                    is_member_price: 0,
                    is_share: 0,
                    content: "",
                    is_offer_price: 1
                },
                rule: {},
                activeName: 'first',
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/flash_sale/mall/setting/index'
                            },
                            method: 'post',
                            data: this.form,
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    } else {
                        this.btnLoading = false;
                        return false;
                    }
                })
            },
            async loadData() {
                try {
                    this.loading = true;
                    const e = await request({
                        params: {
                            r: 'plugin/flash_sale/mall/setting/index'
                        },
                        method: 'get'
                    });
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.form = e.data.data;
                        this.is_show = true;
                    }
                } catch (e) {
                    throw new Error(e);
                }
            }
        },

        created() {
            this.loadData();
        },
    })
</script>
