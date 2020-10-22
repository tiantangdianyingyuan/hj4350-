<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
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

<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="180px" ref="form" :rules="rules">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本设置" name="first">
                    <app-setting v-model="form" :is_payment="false" :is_full_reduce="true" :is_send_type="false" :is_offer_price="true"></app-setting>
                    <el-card style="margin-bottom: 10px">
                        <div slot="header">订单取消时间设置</div>
                        <el-form-item prop="over_time">
                            <template slot='label'>
                                <span>未支付订单取消时间</span>
                                <el-tooltip effect="dark" content="注意：时间设置为0则表示不开启自动删除未支付订单功能，不能大于100"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input style="width: 420px;" type="text"
                                      maxlength="8"
                                      oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                      v-model.number="form.over_time" autocomplete="off">
                                <template slot="append">分</template>
                            </el-input>
                        </el-form-item>
                    </el-card>
                </el-tab-pane>
                <el-button
                        style="margin-bottom: 150px;"
                        :loading="btnLoading" class="button-item" type="primary" @click="submit('form')" size="small"
                >保存
                </el-button>
            </el-tabs>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            let noPay = (rule, value, callback) => {
                let reg = /^[1-9]\d*$/;
                if (!reg.test(this.form.over_time) && this.form.over_time != 0) {
                    callback(new Error('未支付订单超时时间必须为整数'))
                } else if (this.form.over_time > 100) {
                    callback(new Error('未支付订单超时时间不能大于100'))
                } else {
                    callback()
                }
            };
            return {
                loading: false,
                btnLoading: false,
                form: {
                    open_time: [],
                    is_share: 0,
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    over_time: '',
                    is_offer_price: 1,
                },
                activeName: 'first',
                goodsComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_nickname.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '商品名称',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品价格',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'poster_bg',
                        icon_url: 'statics/img/mall/poster/icon-mark.png',
                        title: '标识',
                        is_active: true
                    },
                    {
                        key: 'time_str',
                        icon_url: 'statics/img/mall/poster/icon_time.png',
                        title: '时间',
                        is_active: true
                    },
                ],
                rules: {
                    over_time: [
                        {validator: noPay, trigger: 'blur'}
                    ],
                }
            };
        },
        created() {
            this.loadSetting();
        },
        methods: {
            async submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/miaosha/mall/index/'
                            },
                            method: 'post',
                            data: this.form
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success('保存成功');
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                })
            },
            async loadSetting() {
               try {
                   this.loading = true;
                   const e = await request({
                       params: {
                           r: 'plugin/miaosha/mall/index'
                       },
                       method: 'get'
                   });
                   this.loading = false;
                   if (e.data.code === 0) {
                       this.form = e.data.data.detail;
                   }
               } catch (e) {
                   this.loading = false;
                   throw new Error(e);
               }
            },
        },
    });
</script>
