<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/25
 * Time: 20:09
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('goods/app-select-coupon-two');
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
$mallUrl = Yii::$app->request->hostInfo
    . Yii::$app->request->baseUrl
    . '/statics/img/app';
?>
<style>
    .diy-component-edit .coupon-box {
        position: relative;
        border: 1px dashed #DDDDDD;
        height: 64px;
        line-height: 64px;
        margin-top: 10px;
        width: 100%;
        padding: 0 12px;
    }

    .diy-component-edit .coupon-box > div {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        color: rgba(102, 102, 102, 1);
        font-size: 14px;
    }

    .diy-component-edit .icon-delete {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        border-radius: 0;
    }


</style>

<style>
    .diy-coupon {
        width: 100%;
        padding: 20px 24px 20px 4px;
        min-height: 150px;
        overflow-x: auto;
        background: #FFFFFF;
    }
    .diy-coupon .coupon-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .diy-coupon .right {
        width: 1.6rem;
        font-size: 26px;
        line-height: 1.20;
        text-align: center;
        margin-right: 2px;
        writing-mode: vertical-rl;
    }
    .diy-coupon .receive {
        background: #b4b4b4 !important;
    }

    .diy-component-edit .add-btn.el-button--primary.is-plain {
        color: #409EFF;
        background: #FFFFFF !important;;
        border-color: #b3d8ff;
    }
</style>
<template id="diy-coupon">
    <div>
        <div class="diy-component-preview">
            <div class="diy-coupon" flex="dir:left">
                <div v-for="(coupon,index) in couponList" :style="[couponBox]" :class="{receive: index > 2}">
                    <div class="coupon-bg">
                        <img style="height: 100%;width: 100%" :src="couponBgImg" alt="">
                    </div>
                    <div v-if="couponList.length === 1" flex="dir:left" style="height: 100%">
                        <div style="width: 28%;font-size: 32px;padding-left: 35px" flex="cross:center">￥1000</div>
                        <div style="width: 71%;font-size: 20px;padding-left: 25px" flex="cross:center">满200元可用</div>
                        <div style="width: 20%" class="right" flex="main:center cross:center">立即领取</div>
                    </div>
                    <div v-if="couponList.length === 2 || couponList.length === 3" flex="dir:left" style="height: 100%">
                        <div style="text-align: center;width: 75%">
                            <div style="height: 50%;font-size: 32px;padding-top: 15px">￥1000</div>
                            <div style="height: 50%;font-size: 20px;padding-top: 15px">满200元可用</div>
                        </div>
                        <div style="flex-grow: 1" flex="main:center cross:center">
                            <div class="right" flex="main:center cross:center">{{index > 2 ? `已领取` : `立即领取`}}</div>
                        </div>
                    </div>
                    <div v-if="couponList.length > 3" flex="dir:left" style="height: 100%;width: 274px">
                        <div style="text-align: center;width: 75%">
                            <div style="height: 50%;font-size: 32px;padding-top: 15px">￥1000</div>
                            <div style="height: 50%;font-size: 20px;padding-top: 15px">满200元可用</div>
                        </div>
                        <div style="flex-grow: 1" flex="main:center cross:center">
                            <div class="right" flex="main:center cross:center">{{index > 2 ? `已领取` : `立即领取`}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="添加方式">
                    <app-radio v-model="data.addType" label="manual">手动添加</app-radio>
                    <app-radio v-model="data.addType" label="">自动添加</app-radio>
                    <div style="width: 385px" v-if="data.addType === `manual`">
                        <div v-for="(coupon,index) in data.coupons" class="coupon-box">
                            <div>
                                <span>优惠券： {{coupon.name}} (</span>
                                <span v-if="coupon.appoint_type == 1">指定商品类目,</span>
                                <span v-if="coupon.appoint_type == 2">指定商品,</span>
                                <span v-if="coupon.appoint_type == 3">全场通用,</span>
                                <span v-if="coupon.appoint_type == 4">当面付,</span>
                                <span v-if="coupon.type == 2 && coupon.min_price > 0">满{{coupon.min_price}}减{{coupon.sub_price}}）</span>
                                <span v-else-if="coupon.type == 2">减{{coupon.sub_price}}）</span>
                                <span v-else-if="coupon.type == 1 && coupon.min_price > 0">满{{coupon.min_price}}打{{coupon.discount}}折）</span>
                                <span v-else-if="coupon.type == 1">{{coupon.discount}}折）</span>
                            </div>
                            <span style="position: absolute;right: -25px;top: -22px;" @click="couponDelete(index)">
                                <el-button class="icon-delete"
                                           size="mini"
                                           type="primary"
                                           icon="el-icon-delete"
                                ></el-button>
                            </span>
                        </div>
                        <app-select-coupon-two v-model="data.coupons"
                                               @change="couponChange"
                                               :is-join="1"
                                               :max-num="10">
                            <el-button style="width: 385px" size="mini" type="primary" plain class="add-btn">+ 添加优惠券</el-button>
                        </app-select-coupon-two>
                        <span style="margin-top: 10px;color:#666666;font-size: 12px">最多添加10张优惠券</span>
                    </div>
                </el-form-item>
                <el-form-item v-if="data.addType === ``" label="优惠券数量">
                    <app-radio v-model="data.has_limit" label="">全部</app-radio>
                    <app-radio v-model="data.has_limit" label="limit">
                        <el-input placeholder="请输入优惠券数量" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                  maxlength="10"
                                  v-model.number="data.limit_num" style="width: 150px"
                                  size="small" :disabled="data.has_limit==''"></el-input>
                    </app-radio>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker @change="(row) => {row == null ? data.textColor = '#FFFFFF' : ''}" size="small"
                                     v-model="data.textColor"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;"
                              v-model="data.textColor"></el-input>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker @change="(row) => {row == null ? data.couponBg = '#D9BC8B' : ''}" size="small"
                                     v-model="data.couponBg"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;"
                              v-model="data.couponBg"></el-input>
                </el-form-item>
                <el-form-item label="背景效果">
                    <app-radio v-model="data.couponBgType" label="pure">纯色</app-radio>
                    <app-radio v-model="data.couponBgType" label="gradient">渐变</app-radio>
                </el-form-item>
                <el-form-item label="隐藏券">
                    <el-checkbox v-model="data.has_hide">隐藏已领完券</el-checkbox>
                </el-form-item>
                <el-form-item v-if="false" label="不可领取">
                    <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                    v-model="data.receiveBg">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸256*130"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="data.receiveBg" :show-delete="true"
                                 @deleted="deletePic('receiveBg')"></app-gallery>
                </el-form-item>
                <el-form-item v-if="false" label="可领取">
                    <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                    v-model="data.unclaimedBg">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸256*130"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="data.unclaimedBg" :show-delete="true"
                                 @deleted="deletePic('unclaimedBg')"></app-gallery>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-coupon', {
        template: '#diy-coupon',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    addType: '',
                    has_hide: false,
                    coupons: [],
                    couponBg: '#D9BC8B',
                    couponBgType: 'pure',

                    textColor: '#ffffff',
                    receiveBg: '<?= $mallUrl?>/coupon/icon-coupon-no.png',
                    unclaimedBg: '<?= $mallUrl?>/coupon/icon-coupon-index.png',
                    showImg: false,
                    backgroundColor: '#fff',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    has_limit: '',
                    limit_num: '',
                },
                position: 'center center',
                repeat: 'no-repeat',
                defaultData: {}
            };
        },
        created() {
            let data = JSON.parse(JSON.stringify(this.data));
            this.defaultData = data;
            if (!this.value) {
                this.$emit('input', data)
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            couponList() {
                if (this.data.addType === 'manual') {
                    if (this.data.coupons.length > 3 && this.data.has_hide) {
                        return [[], [], []];
                    }
                    if (this.data.coupons.length == 0) {
                        return [[], [], [], []]
                    }
                    return this.data.coupons;
                } else if (this.data.has_limit === 'limit' && this.data.limit_num) {
                    return new Array(parseInt(this.data.limit_num) > 20 ? 20 : parseInt(this.data.limit_num));
                } else if (this.data.has_hide) {
                    return [[], [], []];
                } else {
                    return [[], [], [], []];
                }
            },
            couponBgImg() {
                switch (this.couponList.length) {
                    case 1:
                        return 'statics/img/mall/diy/bg_coupon_index_1.png';
                    case 2:
                        return 'statics/img/mall/diy/bg_coupon_index_2.png';
                    case 3:
                        return 'statics/img/mall/diy/bg_coupon_index_3.png';
                    default:
                        return 'statics/img/mall/diy/bg_coupon_index_4.png';
                }
            },
            couponBox() {
                let width;
                switch (this.couponList.length) {
                    case 1:
                        width = '702px';
                        break;
                    case 2:
                        width = '341px';
                        break;
                    case 3:
                        width = '220px';
                        break;
                    default:
                        width = '274px';
                        break;
                };
                return {
                    background: this.data.couponBgType === 'gradient'
                        ? 'linear-gradient(to left, ' + this.data.couponBg + ',' + this.colorRgba(this.data.couponBg, 0.5) + ')'
                        : this.data.couponBg,
                    width: width,
                    color: this.data.textColor,
                    // margin: '0 auto',
                    'margin-left': '20px',
                    height: '140px',
                    position: 'relative',
                    //'margin-right': width === '274px' ? '20px' : 'auto',
                };
            },
            cListStyle() {
                if (this.data.backgroundColor) {
                    return `background-color:${this.data.backgroundColor};background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                } else {
                    return `background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }
            },
            cStyle1() {
                return `background-image: url('${this.data.unclaimedBg}');`
                    + `color: ${this.data.textColor}`;
            },
            cStyle2() {
                return `background-image: url('${this.data.receiveBg}');`
                    + `color: ${this.data.textColor}`;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            couponChange(column) {
                this.data.coupons = this.data.coupons.concat(column);
            },
            colorRgba(sHex, alpha = 1) {
                var reg = /^#([0-9a-fA-f]{3}|[0-9a-fA-f]{6})$/;
                /* 16进制颜色转为RGB格式 */
                let sColor = sHex.toLowerCase()
                if (sColor && reg.test(sColor)) {
                    if (sColor.length === 4) {
                        var sColorNew = '#'
                        for (let i = 1; i < 4; i += 1) {
                            sColorNew += sColor.slice(i, i + 1).concat(sColor.slice(i, i + 1))
                        }
                        sColor = sColorNew
                    }
                    var sColorChange = []
                    for (let i = 1; i < 7; i += 2) {
                        sColorChange.push(parseInt('0x' + sColor.slice(i, i + 2)))
                    }
                    return 'rgba(' + sColorChange.join(',') + ',' + alpha + ')'
                } else {
                    return sColor
                }
            },
            couponDelete(index) {
                this.data.coupons.splice(index, 1);
            },

            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            deletePic(param) {
                this.data[param] = this.defaultData[param]
            }
        }
    });
</script>
