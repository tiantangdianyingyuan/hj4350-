<?php defined('YII_ENV') or exit('Access Denied');
    Yii::$app->loadViewComponent('app-poster');
    Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }

    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>

<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;" v-loading="listLoading">
        <div class="text item" style="width:100%">
            <el-form :model="setting" label-width="150px" :rules="FormRules" ref="form">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础设置" class="form-body" name="first">
                       <el-card style="margin-bottom: 10px">
                           <div slot="header">显示设置</div>
                           <el-form-item label="是否显示分类">
                               <el-switch
                                       v-if="loading"
                                       v-model="setting.is_cat"
                                       :active-value="1"
                                       :inactive-value="0">
                               </el-switch>
                           </el-form-item>
                       </el-card>
                        <app-setting v-model="setting" :is_territorial_limitation="false" :is_coupon="true" :is_send_type="false"  :is_full_reduce="true"></app-setting>
                    </el-tab-pane>

                    <el-tab-pane label="表单设置" class="form-body" name="second">
                        <el-card>
                            <el-form-item label="表单状态" prop="is_form">
                                <el-switch
                                    v-if="loading"
                                    v-model="setting.is_form"
                                    :active-value="1"
                                    :inactive-value="0">
                                </el-switch>
                            </el-form-item>
                            <el-form-item v-if="setting.is_form == 1" label="表单设置" prop="selectedOptions">
                                <app-form :is_date_range="true" :is_time_range="true" :value.sync="form_data"></app-form>
                            </el-form-item>
                        </el-card>
                    </el-tab-pane>
                    <el-tab-pane v-if="false"  label="自定义海报" class="form-body" name="three" style="background:none;padding:0">
                        <app-poster :rule_form="form.goods_poster"
                                    :goods_component="goodsComponent"
                        ></app-poster>
                    </el-tab-pane>
                </el-tabs>
                <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">保存</el-button>
            </el-form>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',

        data() {
            return {
                loading: false,
                setting: {
                    is_cat: 0,
                    is_share: 0,
                    is_sms: 0,
                    is_mall:0,
                    is_order: 0,
                    is_form: 0,
                    payment_type: ['online_pay'],
                    is_member_price: 0,
                },
                poster: {
                    bg_pic: {},
                    pic: {},
                    head: {},
                    poster_bg: {}
                },
                form_data: {},
                listLoading: false,
                activeName: 'first',
                btnLoading: false,
                FormRules: {
                    is_share: [
                        {required: true, message: '分销不能为空', trigger: 'blur'},
                    ],
                    is_sms: [
                        {required: true, message: '短信提醒不能为空', trigger: 'blur'},
                    ],
                    is_mail: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_print: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_form: [
                        {required: true, message: '显示表单不能为空', trigger: 'blur'},
                    ]
                },
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
                    }
                ],
            };
        },

        methods: {
            onSubmit() {
                let self = this;
                self.$refs.form.validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        let { is_mail, is_sms, is_share,is_full_reduce, is_print, is_cat, is_form, payment_type, is_coupon, is_member_price, is_integral,svip_status, is_territorial_limitation } = this.setting;
                        let para = {
                            goods_poster: this.poster,
                            form_data: this.form_data,
                            is_share,
                            is_sms,
                            is_mail,
                            is_print,
                            is_form,
                            is_cat,
                            payment_type,
                            is_coupon,
                            is_member_price,
                            is_integral,
                            svip_status,
                            is_full_reduce
                        };
                        request({
                            params: {
                                r: 'plugin/booking/mall/setting',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.btnLoading = false;
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    }
                });
            },

            async getList() {
                this.listLoading = true;
                const e = await request({
                    params: {
                        r: 'plugin/booking/mall/setting',
                    },
                });
                if (e.data.code === 0) {
                    this.loading = true;
                    if (e.data.data) {
                        let { form_data, setting, poster } = e.data.data;
                        this.setting = setting;
                        this.poster = poster;
                        this.form_data = form_data;
                    }
                }
                this.listLoading = false;
            },
        },

        mounted() {
            this.getList();
        }
    })
</script>