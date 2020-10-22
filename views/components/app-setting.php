<?php
/**
 * Created by PhpStorm.
 * User: fjt
 * Date: 2019/12/6
 * Time: 16:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

?>
<style>
    .red {
        display:inline-block;
        padding:0 25px;
        color: #ff4544;
    }
</style>
<template id="app-setting">
    <div class="app-setting">
        <el-form size="mini" :data="form" :label-width="label_width + 'px'">
            <el-card style="margin-bottom: 10px">
                <div slot="header">购买设置</div>

                <!--是否开启分销-->
                <el-form-item label="是否开启分销" prop="is_share" v-if="is_share && form.is_share != -1">
                    <el-switch
                        v-model="form.is_share"
                        :active-value="1"
                        :inactive-value="0">
                    </el-switch>
                    <span class="red">注：必须在“
                        <el-button type="text" @click="$navigate({r:'mall/share/basic'}, true)">分销中心=>基础设置</el-button>
                        ”中开启，才能使用
                    </span>
                </el-form-item>

                <!--是否开启区域允许购买-->
                <el-form-item class="switch" label="是否开启区域允许购买" v-if="is_territorial_limitation">
                    <el-switch v-model="form.is_territorial_limitation" :active-value="1"
                               :inactive-value="0"></el-switch>
                    <span class="ml-24 red">注：必须在“
                        <el-button type="text" @click="$navigate({r:'mall/territorial-limitation/index'}, true)">
                            系统管理=>区域允许购买
                        </el-button>
                        ”中开启，才能使用
                    </span>
                </el-form-item>

                <!--是否开启起送规则-->
                <el-form-item class="switch" label="是否开启起送规则" v-if="is_offer_price">
                    <el-switch v-model="form.is_offer_price" :active-value="1"
                               :inactive-value="0"></el-switch>
                    <span class="ml-24 red">注：必须在“
                        <el-button type="text" @click="$navigate({r:'mall/index/rule', tab: 'fourth'}, true)">
                            系统管理=>规则设置=>起送规则
                        </el-button>
                        ”中开启，才能使用
                    </span>
                </el-form-item>

                <!--支付方式-->
                <el-form-item label="支付方式" prop="payment_type" v-if="is_payment">
                    <label slot="label">支付方式
                        <el-tooltip class="item" effect="dark"
                                    :content="is_surpport_huodao?'默认支持线上支付；若三个都不勾选，则视为勾选线上支付':'默认支持线上支付；若两个都不勾选，则视为勾选线上支付'"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-checkbox-group v-model="form.payment_type" size="mini" :min="1" :max="3">
                        <el-checkbox label="online_pay" size="mini">线上支付</el-checkbox>
                        <el-checkbox label="huodao" size="mini" v-if="is_surpport_huodao">货到付款</el-checkbox>
                        <el-checkbox label="balance" size="mini">余额支付</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>

                <!--发货方式-->
                <el-form-item label="发货方式" prop="send_type" v-if="is_send_type">
                    <label slot="label">发货方式
                        <el-tooltip
                            class="item"
                            effect="dark"
                            content="自提需要设置门店，如果您还未设置门店请保存本页后设置门店"
                            placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <div>
                        <el-checkbox-group v-model="form.send_type" :min="1" :max="send_type_desc.length">
                            <el-checkbox v-for="item in send_type_desc"   :label="item.key">
                                {{item.modify ? item.modify : item.origin}}{{item.origin !== item.modify && item.modify ? `(${item.origin})` : ''}}
                            </el-checkbox>
                        </el-checkbox-group>
                        <div style="color: #CCCCCC;">注：手机端显示排序（<span v-for="(item, index) in send_type_list" :key="index">{{index + 1}}.{{item}} </span>）</div>
                    </div>
                </el-form-item>
            </el-card>

            <el-card style="margin-bottom: 10px" v-if="is_discount">
                <div slot="header">优惠叠加设置</div>
                <el-form-item label="优惠券" v-if="is_coupon">
                    <el-switch v-model="form.is_coupon" :active-value="1"
                               :inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="超级会员卡" v-if="form.svip_status != -1 && form.svip_status != null && form.svip_status != undefined">
                    <el-switch v-model="form.svip_status" :active-value="1"
                               :inactive-value="0"></el-switch>
                    <span class="ml-24 red">注：必须在“
                                <el-button type="text" @click="$navigate({r:'plugin/vip_card/mall/setting/index'}, true)">
                                    插件中心=>超级会员卡
                                </el-button>
                                ”中开启，才能使用
                </el-form-item>
                <el-form-item label="会员价" v-if="is_member_price">
                    <el-switch v-model="form.is_member_price" :active-value="1"
                               :inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="积分抵扣" v-if="is_integral">
                    <el-switch v-model="form.is_integral" :active-value="1"
                               :inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="满减优惠" v-if="is_full_reduce">
                    <el-switch v-model="form.is_full_reduce" :active-value="1"
                               :inactive-value="0"></el-switch>
                </el-form-item>
            </el-card>
            <slot></slot>
        </el-form>
    </div>
</template>

<script>
    Vue.component('app-setting', {
        template: '#app-setting',
        props: {
            value: Object,
            is_share: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_sms: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_mail: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_print: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_territorial_limitation: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_regional: {
                type: Boolean,
                default() {
                    return false;
                }
            },
            is_payment: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_send_type: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_surpport_huodao: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_surpport_city: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_coupon: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            label_width: {
                type: Number,
                default() {
                    return 180;
                }
            },
            label_show: {
                type: Boolean,
                default() {
                    return true
                }
            },
            is_member_price: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_integral: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_discount: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_full_reduce: {
                type: Boolean,
                default() {
                    return false;
                }
            },
            is_offer_price: {
                type: Boolean,
                default() {
                    return false;
                }
            },
            sign: {
                type: String
            }

        },
        data() {
            return {
                setting: {
                    is_share: 0,
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    is_territorial_limitation: 0,
                    send_type: ['express', 'offline'],
                    payment_type: ['online_pay'],
                    is_coupon: 0,
                    svip_status: -1,
                    is_member_price: 0,
                    is_integral: 0,
                    is_full_reduce: 0,
                    is_offer_price: 1
                },
                send_type_desc: []
            }
        },
        computed: {
            form() {
                for (let key in this.setting) {
                    if (typeof this.value[key] === 'undefined') {
                        this.value[key] = this.setting[key];
                    }
                }
                return this.value;
            },
            send_type_list() {
                let list = [];
                for (let i in this.form.send_type) {
                    if (this.form.send_type[i] == 'express') {
                        list.push('快递配送');
                    }
                    if (this.form.send_type[i] == 'offline') {
                        list.push('到店自提');
                    }
                    if (this.sign !== 'mch' && this.form.send_type[i] == 'city') {
                        list.push('同城配送');
                    }
                }
                return list;
            }
        },
        created() {
          if (this.is_send_type) {
            request({
                  params: {
                       r: 'mall/index/setting-one',
                       column: 'send_type_desc'
                   },
                  method: 'get'
                }).then(e => {
                    this.send_type_desc = e.data.data;
                    if (this.sign === 'mch') {
                        for (let i = 0; i < this.send_type_desc.length; i++) {
                            if (this.send_type_desc[i].key === 'city') {
                                this.$delete(this.send_type_desc, i);
                            }
                        }
                    }

              })
         }
        }
    });
</script>
