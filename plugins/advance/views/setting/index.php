<?php defined('YII_ENV') or exit('Access Denied');
    Yii::$app->loadViewComponent('app-poster');
    Yii::$app->loadViewComponent('app-plugins-banner');
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
    .red {
        padding: 0px 25px;
        color: #ff4544;
    }
    .comment {
        margin: 0;
        height: 15px;
        line-height: 15px;
        color: #909399;
        font-size: 13px;
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="cardLoading">
        <div class="text item" style="width:100%">
            <el-form :model="ruleForm" label-width="190px" :rules="rules" ref="ruleForm">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基本设置" name="first">


                        <el-card style="margin-bottom: 10px">
                            <div slot="header">购买设置</div>
                            <el-form-item prop="over_time">
                                <template slot='label'>
                                    <span>预售订单未付定金取消时间</span>
                                </template>
                                <el-input v-model="ruleForm.over_time" style="width: 30%;" type="number">
                                    <template slot="append">分</template>
                                </el-input>
                            </el-form-item>

                            <el-form-item class="switch" label="是否开启分销" prop="is_share">
                                <el-switch v-model="ruleForm.is_share" :active-value="1" :inactive-value="0"></el-switch>
                                <span class="red">注：必须在“
                                    <el-button type="text" @click="$navigate({r:'mall/share/basic'}, true)">分销中心=>基础设置</el-button>
                                    ”中开启，才能使用
                                </span>
                                <p class="comment">注:  针对尾款订单</p>
                            </el-form-item>

                            <el-form-item class="switch" label="是否开启区域允许购买" prop="is_territorial_limitation">
                                <el-switch v-model="ruleForm.is_territorial_limitation" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                <span class="ml-24 red">注：必须在“
                                    <el-button type="text" @click="$navigate({r:'mall/territorial-limitation/index'}, true)">
                                        系统管理=>区域允许购买
                                    </el-button>
                                    ”中开启，才能使用
                                </span>
                                <p class="comment">注:  针对尾款订单</p>
                            </el-form-item>

                            <el-form-item>
                                <label slot="label">定金支付方式
                                    <el-tooltip class="item" effect="dark"
                                                content="默认支持线上支付；若二个都不勾选，则视为勾选线上支付"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-checkbox-group v-model="ruleForm.deposit_payment_type" size="mini" :min="1">
                                    <el-checkbox label="online_pay" size="mini">线上支付</el-checkbox>
                                    <el-checkbox label="balance" size="mini">余额支付</el-checkbox>
                                </el-checkbox-group>
                            </el-form-item>

                            <el-form-item>
                                <label slot="label">尾款支付方式
                                    <el-tooltip class="item" effect="dark"
                                                content="默认支持线上支付；若三个都不勾选，则视为勾选线上支付"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-checkbox-group v-model="ruleForm.payment_type" size="mini" :min="1">
                                    <el-checkbox label="online_pay" size="mini">线上支付</el-checkbox>
                                    <el-checkbox label="balance" size="mini">余额支付</el-checkbox>
                                    <el-checkbox label="huodao" size="mini">货到付款</el-checkbox>
                                </el-checkbox-group>
                            </el-form-item>

                            <el-form-item label="发货方式" >
                                <label slot="label">发货方式
                                    <el-tooltip class="item" effect="dark"
                                                content="自提需要设置门店，如果您还未设置门店请保存本页后设置门店"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <div>
                                    <el-checkbox-group v-model="ruleForm.send_type" size="mini" :min="1">
                                        <el-checkbox v-for="item in send_type_desc"   :label="item.key">
                                            {{item.modify ? item.modify : item.origin}}{{item.origin !== item.modify && item.modify ? `(${item.origin})` : ''}}
                                        </el-checkbox>
                                    </el-checkbox-group>
                                </div>
                                <p class="comment">注:  针对尾款订单</p>
                                <div class="comment" >注：手机端显示排序（<span v-for="(item, index) in send_type_list" :key="index">{{index + 1}}.{{item}} </span>）</div>
                            </el-form-item>

                        </el-card>

                        <el-card style="margin-bottom: 10px">
                            <div slot="header">优惠叠加设置</div>
                            <el-form-item label="优惠券" >
                                <el-switch v-model="ruleForm.is_coupon" :active-value="1"
                                               :inactive-value="0"></el-switch>
                            </el-form-item>
                            <el-form-item label="超级会员卡" v-if="ruleForm.svip_status != -1" >
                                <el-switch v-model="ruleForm.svip_status" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                <span class="ml-24 red">注：必须在“
                                <el-button type="text" @click="$navigate({r:'mall/territorial-limitation/index'}, true)">
                                    插件中心=>超级会员卡
                                </el-button>
                                ”中开启，才能使用
                            </el-form-item>
                            <el-form-item label="会员价" >
                                <el-switch v-model="ruleForm.is_member_price" :active-value="1"
                                           :inactive-value="0"></el-switch>
                            </el-form-item>
                            <el-form-item label="积分抵扣" >
                                <el-switch v-model="ruleForm.is_integral" :active-value="1"
                                           :inactive-value="0"></el-switch>
                            </el-form-item>
                            <el-form-item label="满减优惠" >
                                <el-switch v-model="ruleForm.is_full_reduce" :active-value="1"
                                           :inactive-value="0"></el-switch>
                            </el-form-item>
                        </el-card>
                    </el-tab-pane>

                    <el-tab-pane v-if="false" label="自定义海报" class="form-body" name="second">
                        <app-poster :rule_form="ruleForm.goods_poster"
                                    :goods_component="goodsComponent"
                        ></app-poster>
                    </el-tab-pane>

                    <el-tab-pane label="轮播图" class="form-body" name="third">
                        <app-plugins-banner
                                @concat_list="concat_banner_list"
                                @delete_list="delete_banner_list"
                                :list="ruleForm.banner_list"></app-plugins-banner>
                    </el-tab-pane>
                </el-tabs>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')"
                           size="small">保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    desc: [],
                    deposit_payment_type: 'online_pay',
                    send_type: [
                        'express'
                    ],
                    payment_type: [],
                    is_member_price: 0,
                    svip_status: -1,
                    is_coupon: 0,
                    is_integral: 0,
                    is_full_reduce: 0
                },
                rules: {},
                btnLoading: false,
                cardLoading: false,
                is_show: false,
                activeName: 'first',
                send_type_desc: [],
                FormRules: {
                    is_cat: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
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
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/advance/mall/setting/index',
                    },
                }).then(e => {
                    this.cardLoading = false;
                    this.is_show = true;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.setting;
                    }
                }).catch(e => {
                });
            },
            // 合并轮播图
            concat_banner_list(data) {
                this.ruleForm.banner_list = this.ruleForm.banner_list.concat(data);
            },
            // 删除轮播图
            delete_banner_list(index) {
                this.ruleForm.banner_list.splice(index, 1);
            },
            store(formName) {
                let banner_ids = [];
                for (let i = 0; i < this.ruleForm.banner_list.length; i++) {
                    banner_ids.push(this.ruleForm.banner_list[i].id);
                }
                this.ruleForm.banner_ids = JSON.stringify(banner_ids);
                this.$refs[formName].validate((valid) => {
                    console.log(this.ruleForm);
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/advance/mall/setting/index'
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
            // 添加权益
            addIntegralDesc() {
                this.ruleForm.desc.push({
                    title: '',
                    content: '',
                })
            },
            // 删除权益
            destroyIntegralDesc(index) {
                this.ruleForm.desc.splice(index, 1);
            }
        },
        mounted: function () {
            this.getDetail();
            request({
                params: {
                    r: 'mall/index/setting-one',
                    column: 'send_type_desc'
                },
                method: 'get'
            }).then(e => {
                this.send_type_desc = e.data.data;
            })
        },
        computed: {
            send_type_list() {
                let list = [];
                for (let i in this.ruleForm.send_type) {
                    if (this.ruleForm.send_type[i] == 'express') {
                        list.push('快递配送');
                    }
                    if (this.ruleForm.send_type[i] == 'offline') {
                        list.push('到店自提');
                    }
                    if (this.ruleForm.send_type[i] == 'city') {
                        list.push('同城配送');
                    }
                }
                return list;
            }
        }
    })
</script>