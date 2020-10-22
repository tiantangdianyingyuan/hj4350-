<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('goods/app-goods-share');
?>
<style>
    .tabs>.el-tabs__header {
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .el-tabs__nav.is-top {
        transform: translateX(25px)!important;
    }

    .middle-card .el-tabs__nav.is-top {
        transform: translateX(0)!important;
    }

    .form-body {
        padding: 40px 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
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

    .button-item.third-button-item {
        position: absolute;
        bottom: -60px;
        left: 0;
    }

    .button-item.third-button-item.reset-btn {
        left: 90px;
    }

    .before {
        height: 100px;
        line-height: 100px;
        width: 100px;
        background-color: #f7f7f7;
        color: #bbbbbb;
        text-align: center;
    }

    .red {
        display: inline-block;
        padding:0 25px;
        color: #ff4544;
    }

    .poster-mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }

    .poster-bg-box {
        position: relative;
        border: 1px solid #e2e3e3;
        width: 750px;
        height: 1334px;
        overflow: hidden;
        zoom: 0.5;
    }

    .poster-bg-pic {
        width: 100%;
        height: 100%;
        background-size: 100% 100%;
        background-position: center;
    }

    .title {
        padding: 15px 0;
        background-color: #f7f7f7;
        margin-bottom: 10px;
    }

    .component-item {
        width: 100px;
        height: 100px;
        cursor: pointer;
        position: relative;
        padding: 10px 0;
        border: 1px solid #e2e2e2;
        margin-right: 15px;
        margin-top: 15px;
        border-radius: 5px;
    }

    .component-item.active {
        border: 1px solid #7BBDFC;
    }

    .component-item-remove {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
        width: 28px;
        height: 28px;
    }

    .component-attributes-box {
        color: #ff4544;
    }

    .poster-form-body {
        padding: 20px 20% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        width: 100%;
        height: 100%;
        position: relative;
        min-width: 640px;
    }

    .poster-button-item {
        padding: 9px 25px;
        position: absolute !important;
        bottom: -52px;
        left: 0;
    }

    .el-card, .el-tabs__content {
        overflow: visible;
    }

    .poster-del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .tip {
        color: #909399;
        font-size: 13px;
        height: 25px;
        margin-top: -10px;
    }

    .mobile {
        width: 425px;
        height: 740px;
        padding: 36px 0;
        position: absolute;
    }

    .mobile .mobile-web {
        width: 375px;
        height: 664px;
    }

    .form-body .box-card {
        margin: 0 30px 10px;
        width: 480px;
    }

    .reset {
        position: absolute;
        top: 7px;
        left: 90px;
    }

    .del-btn.el-button--mini.is-circle {
        position: absolute;
        top: -8px;
        right: -8px;
        padding: 4px;
    }

    .user-label .el-form-item__label {
        width: 4.5rem!important;
    }

    .user-label>.el-form-item__label {
        width: 6rem!important;
    }

    .user-label .el-form-item__content {
        margin-left: 4.5rem!important;
    }

    .user-label>.el-form-item__content {
        margin-left: 7rem!important;
    }

    .buy-user-info {
        position: absolute;
        height: 60px;
        width: 351px;
        left: 12px;
        top: 225px;
    }

    .buy-user-info .buy-bg {
        height: 60px;
        width: 351px;
        border-radius: 8px;
    }

    .buy-user-info .buy-logo {
        width: 30px;
        height: 30px;
        position: absolute;
        z-index: 5;
        left: 15px;
        top: 15px;
    }

    .buy-user-info .buy-big {
        position: absolute;
        z-index: 5;
        left: 60px;
        top: 13px;
        font-size: 13px;
        color: #D0B8A5;
    }

    .buy-user-info .buy-small {
        position: absolute;
        z-index: 5;
        left: 60px;
        font-size: 8px;
        bottom: 12px;
        color: #C09878;
    }

    .buy-user-info .buy-btn {
        position: absolute;
        right: 15px;
        top: 17px;
        width: 70px;
        height: 26px;
        line-height: 26px;
        border-radius: 13px;
        text-align: center;
        z-index: 5;
        font-size: 12px;
        color: #5A4D40;
    }

    .buy-user-info .buy-btn.default {
        background: linear-gradient(to right,#fbdec7,#f3bf95);
    }

    .color-picker {
        margin-left: 10px;
    }

    .middle-item {
        height: 96px;
        width: 96px;
        border: 1px solid #e2e2e2;
        margin-left: -1px;
        cursor: pointer;
    }

    .active-middle {
        background-color: #EAEAEA;
        height: 96px;
        width: 96px;
        border: 1px solid #e2e2e2;
        margin-left: -1px;
    }

    .active-middle img,.middle-item img {
        margin-bottom: 10px
    }

    .vip-icon-list {
        position: relative;
        height: 90px;
        width: 310px;
        padding: 10px;
        border: 1px solid #e2e2e2;
        margin-bottom: 10px;
    }

    .vip-icon-list .edit-options {
        position: absolute;
        top: 4px;
        right: -24px;
        height: 24px;
        width: 24px;
    }

    .vip-icon-list .edit-options .el-button {
        padding: 0;
        height: 24px;
        width: 24px;
        border-radius: 0;
    }

    .middle-card .block {
        border: 1px solid #e0e0e0;
        padding: 5px;
        margin: 0 10px 10px 0;
        text-align: center;
        cursor: pointer;
    }

    .middle-card .block.active {
        border: 1px #5CB3FD solid;
    }

    .middle-card .el-card__body {
        padding-left: 0;
    }

    .mobile .mobile-head {
        position: absolute;
        top: 36px;
        left: 25px;
        z-index: 101;
        height: 64px;
        width: 375px;
    }

    .mobile-head-card {
        height: 135px;
        width: 375px;
        position: relative;
    }

    .mobile-head-card .card-bg {
        position: absolute;
        z-index: 11;
        top: 30px;
        left: 10px;
        height: 180px;
        width: 351px;
        border-radius: 15px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, .3)
    }

    .mobile-head-card .card-name {
        position: absolute;
        top: 50px;
        left: 40px;
        font-size: 18px;
        z-index: 12;
    }

    .mobile-head-card .card-bottom {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 40px;
        width: 375px;
        z-index: 5;
    }

    .mobile-title div{
        margin: 0 10px;
        font-size: 16px;
    }

    .mobile-title img {
        width: 30px;
        height: 10px;
    }

    .mobile-item {
        background-color: #fff;
        width: 351px;
        padding: 20px 24px;
        border-radius: 15px;
        margin: 6px 12px 6px;
        box-shadow: 0 0 1px rgba(0, 0, 0, .3)
    }

    .mobile-right {
        margin-top: 10px;
        position: relative;
        height: 80px;
    }

    .mobile-right .mobile-right-text {
        position: absolute;
        left: 25px;
        top: 12px;
        font-size: 16px;
    }

    .mobile-right .mobile-right-text div:first-of-type {
        font-size: 22px;
        margin-bottom: 3px;
    }

    .mobile-right img{
        width: 315px;
        height: 80px;
        border-radius: 7.5px;
    }

    .mobile-vip-list {
        overflow-y: hidden;
        flex-wrap:  wrap;
    }

    .mobile-vip-list .mobile-vip-item {
        margin-top: 10px;
        text-align: center;
        font-size: 12px;
        height: 70px;
    }

    .mobile-vip-list .mobile-vip-item img {
        width: 40px;
        height: 40px;
        margin: 0 auto 10px;
    }

    .vip-card-gift {
        position: relative;
    }

    .vip-card-gift img {
        position: absolute;
        left: 0;
        height: 26px;
        width: 28px;
        z-index: 2;
    }

    .vip-card-gift .vip-card-gift-bg {
        width: 327px;
        height: 430px;
        position: relative;
    }

    .vip-card-gift .vip-card-gift-integral {
        top: 58px;
    }

    .vip-card-gift .vip-card-gift-balance {
        top: 118px;
    }

    .vip-card-gift .vip-card-gift-coupon {
        top: 180px;
    }

    .vip-card-gift .vip-card-gift-card {
        top: 316px;
    }

    .mobile-foot {
        position: absolute;
        z-index: 101;
        bottom: 36px;
        left: 25px;
        width: 375px;
        padding-top: 5px;
        height: 80px;
        background-color: #fff;
    }

    .mobile-foot .mobile-foot-button {
        width: 351px;
        margin: 5px auto;
        height: 40px;
        line-height: 40px;
        font-size: 12px;
    }

    .mobile-foot .mobile-foot-button span {
        font-size: 18px;
    }

    .mobile-foot .mobile-foot-button div:first-of-type {
        width: 251px;
        padding-left: 20px;
        border-top-left-radius: 20px;
        border-bottom-left-radius: 20px;
    }

    .mobile-foot .mobile-foot-button div:last-of-type {
        width: 100px;
        text-align: center;
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    .mobile-foot .bottom-border {
        border: 1px dashed #409EFF;
        height: 80px;
    }

    .mobile-foot .bottom-no-border {
        border: 1px dashed #fff;
        height: 80px;
    }

    .mobile-foot .bottom-read {
        font-size: 9px;
    }

    .mobile-foot .bottom-read img {
        width: 12px;
        height: 12px;
        margin-right: 5px;
        border-radius: 50%;
    }

    .active-border {
        border: 1px dashed #409EFF;
        overflow-x: visible;
        width: 375px;
        background-color: #f7f7f7;
    }

    .active-no-border {
        border: 1px dashed #F7F7F7;
        width: 375px;
        background-color: #f7f7f7;
    }

    .edit-btn {
        position: absolute;
        width: 375px;
    }

    .edit-btn .el-button {
        padding: 0;
        height: 24px;
        width: 24px;
        border-radius: 0;
        margin: 0;
    }

    .edit-btn .delete-btn {
        position: absolute;
        left: -25px;
        top: 0;
    }

    .edit-btn .up-btn {
        position: absolute;
        right: -24px;
        top: 0;
    }

    .edit-btn .down-btn {
        position: absolute;
        right: -24px;
        top: 30px;
    }

    .show-rubik {
        width: 300px;
        margin: 0 auto;
    }

    .mobile-body {
        max-height: 668px;
        overflow-y: auto;
        background-color: #F3F3F3;
        padding: 0 25px;
        width: 435px;
    }

    .mobile-top {
        position: absolute;
        top: 0;
        left: 15px;
        height: 740px;
        width: 395px;
        background-color: #fff;
        border-radius: 25px;
    }

    .rubik {
        position: absolute;
        border: 1px dashed #409EFF;
        text-align: center;
        cursor: pointer;
    }

    .rubik.active {
        border: 1px solid #409EFF;
    }

    .form-icon-list {
        flex-wrap: wrap;
        width: 351px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form size="small" :model="detail" label-width="10rem" ref="detail" :rules="rule">
            <el-tabs class="tabs" v-model="activeName">
                <el-tab-pane label="基础配置" name="first">
                    <div class="form-body">
                        <el-form-item label="SVIP会员卡开关" prop="is_scan_code_pay">
                            <el-switch v-model="detail.is_vip_card" :active-value="1"
                                       :inactive-value="0"></el-switch>
                            <div class="tip">注意：关闭后不影响已购买的用户</div>
                        </el-form-item>
                        <el-form-item label="是否开启短信提醒" prop="is_sms">
                            <el-switch v-model="form.is_sms" :active-value="1"
                                       :inactive-value="0"></el-switch>
                            <div class="red">注：必须在“
                                <el-button type="text" @click="$navigate({r:'mall/sms/setting'}, true)">
                                    系统管理=>短信通知
                                </el-button>
                                ”中开启，才能使用
                            </div>
                        </el-form-item>
                        <el-form-item label="是否开启邮件提醒" prop="is_mail">
                            <el-switch v-model="detail.is_mail" :active-value="1"
                                       :inactive-value="0"></el-switch>
                            <div class="red">注：必须在“
                                <el-button type="text" @click="$navigate({r:'mall/index/mail'}, true)">
                                    系统管理=>邮件通知
                                </el-button>
                                ”中开启，才能使用
                            </div>
                        </el-form-item>
                        <el-form-item label="支付方式" prop="payment_type">
                            <label slot="label">支付方式
                                <el-tooltip class="item" effect="dark"
                                            content="默认支持线上支付；若两个都不勾选，则视为勾选线上支付"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-checkbox-group v-model="detail.payment_type" size="mini">
                                <el-checkbox label="online_pay" size="mini">线上支付</el-checkbox>
                                <el-checkbox label="balance" size="mini">余额支付</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item class="switch" label="显示协议" prop="showTreaty">
                            <el-switch v-model="showTreaty" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item class="switch" v-if="showTreaty" label="开通会员卡协议名称" prop="agreement_title">
                            <el-input size="small" style="width: 590px;" v-model="detail.agreement_title" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="showTreaty" label="协议内容" prop="agreement_content">
                            <app-rich-text style="width: 590px;" v-model="detail.agreement_content"></app-rich-text>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane v-if="is_show_share" label="分销设置" name="second">
                    <div class="form-body">
                        <el-row>
                            <el-form-item label="是否开启分销" prop="is_share">
                                <el-switch v-model="detail.is_share" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                <div class="red">注：必须在“
                                    <el-button type="text" @click="$navigate({r:'mall/share/basic'}, true)">
                                        分销中心=>基础设置
                                    </el-button>
                                    ”中开启，才能使用
                                </div>
                            </el-form-item>
                            <template v-if="detail.is_share">
                                <el-form-item label="购买成为分销商" prop="is_buy_become_share">
                                    <el-switch v-model="detail.is_buy_become_share" :active-value="1"
                                               :inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="分销佣金类型" prop="share_type">
                                    <el-radio v-model="detail.share_type" :label="2">固定金额</el-radio>
                                    <el-radio v-model="detail.share_type" :label="1">百分比</el-radio>
                                </el-form-item>
                                <app-goods-share v-model="detail" :share_type="detail.share_type" :attr_setting_type="0"></app-goods-share>
<!--                                 <el-col :xl="20" :lg="16">
                                    <el-form-item label="分销佣金">
                                    </el-form-item>
                                </el-col> -->
                            </template>
                        </el-row>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="页面设置" name="third">
                    <div flex="dir:left">
                        <div class="mobile">
                            <div class="mobile-top"></div>
                            <!-- 入口页样式自定义 -->
                            <div v-if="activePage == 'enter'" style="height: 668px;position: absolute;overflow-x: hidden;overflow-y: auto;left: 25px">
                                <img class="mobile-web" src="statics/img/plugins/user-center.png" alt="">
                                <div class="buy-user-info">
                                    <img class="buy-bg" :src="buy_bg" alt="">
                                    <img class="buy-logo" src="statics/img/app/vip_card/logo.png" alt="">
                                    <div class="buy-big" :style="{'color':form.buy_big_color}">{{form.buy_big}}</div>
                                    <div class="buy-small" :style="{'color':form.buy_small_color}">{{form.buy_small}}</div>
                                    <div :class="form.buy_btn_bg_color ? 'buy-btn' : 'buy-btn default'" :style="{'background-color': form.buy_btn_bg_color ? form.buy_btn_bg_color : '','color':form.buy_btn_color}">{{form.buy_btn_text}}</div>
                                </div>
                            </div>
                            <!-- 其他样式自定义 -->
                            <img v-if="activePage != 'enter'" class="mobile-head" src="statics/img/plugins/head.png" alt="">
                            <!-- 底部样式 -->
                            <div v-if="activePage != 'enter'" class="mobile-foot">
                                <div :class="activePage == 'bottom'? 'bottom-border': 'bottom-no-border'">
                                    <div class="mobile-foot-button" flex="dir:left">
                                        <div :style="{'background-color': form.bottom_style_1, 'color': form.bottom_style_2}"><span>￥99</span>/有效期365天</div>
                                        <div :style="{'background-color': form.bottom_btn_style_1,'color': form.bottom_btn_style_2}">立即开通</div>
                                    </div>
                                    <div class="bottom-read" flex="main:center cross:center">
                                        <img :style="{'background-color': form.bottom_btn_style_1}" src="statics/img/plugins/vip-check.png" alt="">
                                        <div :style="{'color': form.bottom_btn_style_2}">我已仔细阅读并同意<span :style="{'color': form.bottom_btn_style_1}">《开通协议》</span></div>
                                    </div>
                                </div>
                            </div>
                            <!-- 中间排版 -->
                            <div v-if="activePage != 'enter'" id="body" class="mobile-body">
                                <!-- 卡片 -->
                                <div style="width: 375px;height: 64px;"></div>
                                <div class="mobile-head-card" :style="{'background-color': form.card_bg}">
                                    <img :src="head_card" class="card-bg" alt="">
                                    <img class="card-bottom" src="statics/img/app/vip_card/card-bottom.png" alt="">
                                    <div class="card-name"  :style="{'color': form.card_color}">{{detail.name}}</div>
                                </div>
                                <div style="height: 85px;position: relative;background-color: #f7f7f7;"></div>
                                <div v-for="(item,index) in form.sort">
                                    <!-- 会员专享 -->
                                    <div v-if="item == 'member'" id="member" :class="activeMiddle == 1? 'active-border': 'active-no-border'" style="position: relative">
                                        <div v-if="activeMiddle == 1" class="edit-btn" style="position: relative">
                                            <el-button @click="toRemove(index)" class="delete-btn" icon="el-icon-delete" type="primary"></el-button>
                                            <el-button @click="toUp(index,item)" v-if="index != 0" class="up-btn" icon="el-icon-arrow-up" type="primary"></el-button>
                                            <el-button @click="toDown(index,item)" v-if="index != form.sort.length - 1" class="down-btn" icon="el-icon-arrow-down" type="primary"></el-button>
                                        </div>
                                        <div style="position: absolute;top: 24px;left: 0;width: 375px" class="mobile-title" flex="main:center cross:center">
                                            <img src="statics/img/app/vip_card/left.png" alt="">
                                            <div :style="{'color': form.vip_color}">会员专享</div>
                                            <img src="statics/img/app/vip_card/right.png" alt="">
                                        </div>
                                        <div class="mobile-item" :style="{'background-color': form.vip_bg,'padding': '20px 0'}">
                                            <div style="height: 20px;"></div>
                                            <div style="width: 100%;overflow-x: auto">
                                                <div class="mobile-vip-list" flex="dir:left" :style="{'color': form.vip_color, 'max-height': vipHeight,'width': icon_width + 'px'}">
                                                    <div v-for="res in form.vip_icon_list" flex="dir:left cross:top" class="form-icon-list" :style="{'height': Math.ceil(res.length / form.vip_number) * 80 + 'px'}">
                                                        <div v-for="item in res" flex="dir:top" class="mobile-vip-item" :style="{'width': vip_icon_width + 'px'}">
                                                            <img :src="item.img" alt="">
                                                            <div>{{item.name}}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 会员特权 -->
                                    <div v-if="item == 'right'" id="right" :class="activeMiddle == 2? 'active-border': 'active-no-border'" style="position: relative">
                                        <div v-if="activeMiddle == 2" class="edit-btn" style="position: relative">
                                            <el-button @click="toRemove(index)" class="delete-btn" icon="el-icon-delete" type="primary"></el-button>
                                            <el-button @click="toUp(index,item)" v-if="index != 0" class="up-btn" icon="el-icon-arrow-up" type="primary"></el-button>
                                            <el-button @click="toDown(index,item)" v-if="index != form.sort.length - 1" class="down-btn" icon="el-icon-arrow-down" type="primary"></el-button>
                                        </div>
                                        <div class="mobile-item" style="padding: 24px 16px 10px">
                                            <div class="mobile-title" flex="main:center cross:center">
                                                <img src="statics/img/app/vip_card/left.png" alt="">
                                                <div>会员特权</div>
                                                <img src="statics/img/app/vip_card/right.png" alt="">
                                            </div>
                                            <div v-for="item in form.right_list" class="mobile-right">
                                                <img :src="item.img" alt="">
                                                <div class="mobile-right-text">
                                                    <div :style="{'color': item.big_color}">{{item.big}}</div>
                                                    <div :style="{'color': item.small_color}">{{item.small}}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 开卡即赠 -->
                                    <div v-if="item == 'gift'" id="gift" :class="activeMiddle == 3? 'active-border': 'active-no-border'" style="position: relative">
                                        <div v-if="activeMiddle == 3" class="edit-btn" style="position: relative">
                                            <el-button @click="toRemove(index)" class="delete-btn" icon="el-icon-delete" type="primary"></el-button>
                                            <el-button @click="toUp(index,item)" v-if="index != 0" class="up-btn" icon="el-icon-arrow-up" type="primary"></el-button>
                                            <el-button @click="toDown(index,item)" v-if="index != form.sort.length - 1" class="down-btn" icon="el-icon-arrow-down" type="primary"></el-button>
                                        </div>
                                        <div class="mobile-item" style="padding: 0 12px;">
                                            <div class="vip-card-gift">
                                                <img class="vip-card-gift-bg" src="statics/img/plugins/vip-card-gift.png" alt="">
                                                <img class="vip-card-gift-integral" :src="integral_icon" alt="">
                                                <img class="vip-card-gift-balance" :src="balance_icon" alt="">
                                                <img class="vip-card-gift-coupon" :src="coupon_icon" alt="">
                                                <img class="vip-card-gift-card" :src="card_icon" alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 图片广告 -->
                                    <div v-if="item == 'rubik'" id="rubik" :class="activeMiddle == 4? 'active-border': 'active-no-border'" style="position: relative">
                                        <div v-if="activeMiddle == 4" class="edit-btn" style="position: relative">
                                            <el-button @click="toRemove(index)" class="delete-btn" icon="el-icon-delete" type="primary"></el-button>
                                            <el-button @click="toUp(index,item)" v-if="index != 0" class="up-btn" icon="el-icon-arrow-up" type="primary"></el-button>
                                            <el-button @click="toDown(index,item)" v-if="index != form.sort.length - 1" class="down-btn" icon="el-icon-arrow-down" type="primary"></el-button>
                                        </div>
                                        <div :style="{'height': form.rubik.height + 'px','position': 'relative'}">
                                            <div @click="toImg(index)" :class="chooseIndex == index ? 'rubik active': 'rubik' " v-for="(item,index) in form.rubik.list" :style="{'top': item.y + 'px','left': item.x + 'px','width': item.w + 'px','height': item.h + 'px','line-height': item.h+ 'px'}">
                                                <img :style="{'height': item.h + 'px','width': item.w + 'px'}" v-if="item.pic" :src="item.pic" alt="">
                                                <span v-else>{{item.w * 2}} * {{item.h * 2}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="height: 90px;width: 375px;"></div>
                            </div>
                        </div>
                        <div class="form-body" style="padding: 10px 0;margin-left: 450px;width: 100%">
                            <el-tabs v-model="activePage" @tab-click="handleClick">
                                <el-tab-pane label="入口页" name="enter">
                                    <template>
                                        <el-card shadow="never" class="box-card">
                                            <div slot="header" class="clearfix">
                                                <span>入口样式设置</span>
                                            </div>
                                            <el-form label-position="left">
                                                <el-form-item class="user-label" label="未购买用户" prop="buy_user">
                                                    <el-form-item label="背景图" prop="buy_bg">
                                                        <div style="position: relative">
                                                            <app-attachment :multiple="false" :max="1" @selected="buyPicUrl">
                                                                <el-tooltip class="item" effect="dark" content="建议尺寸:702*120" placement="top">
                                                                    <el-button size="mini">选择文件</el-button>
                                                                </el-tooltip>
                                                            </app-attachment>
                                                            <div style="margin: 10px 0;position: relative;width: 80px;">
                                                                <app-image width="80px"
                                                                           height="80px"
                                                                           mode="aspectFill"
                                                                           :src="buy_bg">
                                                                </app-image>
                                                                <el-button v-if="buy_bg != ''" class="del-btn" @click="resetImg(1,'buy')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                            </div>
                                                            <el-button size="mini" @click="resetImg(2,'buy')" class="reset" type="primary">恢复默认</el-button>
                                                        </div>
                                                    </el-form-item>
                                                    <el-form-item label="文字" prop="buy_text">
                                                        <div flex="dir:left cross:center">
                                                            <el-input maxlength="17" style="margin-left: 10px;width: 240px" size="small" v-model="form.buy_big" placeholder="大标题（字符限制17）"></el-input>
                                                            <el-color-picker class="color-picker" size="small" v-model="form.buy_big_color"></el-color-picker>
                                                        </div>
                                                        <div flex="dir:left cross:center">
                                                            <el-input maxlength="10" style="margin-left: 10px;width: 240px" size="small" v-model="form.buy_small" placeholder="小标题（字符限制10）"></el-input>
                                                            <el-color-picker class="color-picker" size="small" v-model="form.buy_small_color"></el-color-picker>
                                                        </div>
                                                    </el-form-item>
                                                    <el-form-item label="按钮颜色" prop="buy_btn_bg_color">
                                                        <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.buy_btn_bg_color"></el-color-picker>
                                                    </el-form-item>
                                                    <el-form-item label="按钮文字" prop="buy_btn_text">
                                                        <div flex="dir:left cross:center">
                                                            <el-input maxlength="4" style="margin-left: 10px;width: 240px" size="small" v-model="form.buy_btn_text" placeholder="大标题（字符限制4）"></el-input>
                                                            <el-color-picker class="color-picker" size="small" v-model="form.buy_btn_color"></el-color-picker>
                                                        </div>
                                                    </el-form-item>
                                                </el-form-item>
                                            </el-form>
                                            <el-form label-position="left">
                                                <el-form-item class="user-label" label="已购买用户" prop="renew_user">
                                                    <el-form-item label="背景图" prop="renew_bg">
                                                        <div style="position: relative">
                                                            <app-attachment :multiple="false" :max="1" @selected="renewPicUrl">
                                                                <el-tooltip class="item" effect="dark" content="建议尺寸:702*120" placement="top">
                                                                    <el-button size="mini">选择文件</el-button>
                                                                </el-tooltip>
                                                            </app-attachment>
                                                            <div style="margin: 10px 0;position: relative;width: 80px;">
                                                                <app-image width="80px"
                                                                           height="80px"
                                                                           mode="aspectFill"
                                                                           :src="renew_bg">
                                                                </app-image>
                                                                <el-button v-if="renew_bg != ''" class="del-btn" @click="resetImg(1,'renew')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                            </div>
                                                            <el-button size="mini" @click="resetImg(2,'renew')" class="reset" type="primary">恢复默认</el-button>
                                                        </div>
                                                    </el-form-item>
                                                    <el-form-item label="文字" prop="renew_text">
                                                        <div flex="dir:left cross:center">
                                                            <el-input maxlength="17" style="margin-left: 10px;width: 240px" size="small" v-model="form.renew_text" placeholder="标题（字符限制17）"></el-input>
                                                            <el-color-picker class="color-picker" size="small" v-model="form.renew_text_color"></el-color-picker>
                                                        </div>
                                                    </el-form-item>
                                                    <el-form-item label="按钮颜色" prop="renew_btn_bg_color">
                                                        <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.renew_btn_bg_color"></el-color-picker>
                                                    </el-form-item>
                                                </el-form-item>
                                            </el-form>
                                        </el-card>
                                    </template>
                                </el-tab-pane>
                                <el-tab-pane label="头部样式" name="head">
                                    <el-card shadow="never" class="box-card">
                                        <div slot="header" class="clearfix">
                                            <span>头部样式设置</span>
                                        </div>
                                        <el-form label-width="120px">
                                            <el-form-item label="卡片样式" prop="head_card">
                                                <div style="position: relative">
                                                    <app-attachment :multiple="false" :max="1" @selected="headPicUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*360" placement="top">
                                                            <el-button size="mini">选择文件</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                                        <app-image width="80px"
                                                                   height="80px"
                                                                   mode="aspectFill"
                                                                   :src="head_card">
                                                        </app-image>
                                                        <el-button v-if="head_card != ''" class="del-btn" @click="resetImg(1,'head')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                    </div>
                                                    <el-button size="mini" @click="resetImg(2,'head')" class="reset" type="primary">恢复默认</el-button>
                                                </div>
                                            </el-form-item>
                                            <el-form-item label="卡片背景颜色" prop="card_bg">
                                                <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.card_bg"></el-color-picker>
                                            </el-form-item>
                                            <el-form-item label="卡片文字颜色" prop="card_color">
                                                <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.card_color"></el-color-picker>
                                            </el-form-item>
                                        </el-form>
                                    </el-card>
                                </el-tab-pane>
                                <el-tab-pane label="中间排版" name="middle">
                                    <div flex="dir:left" style="margin: 30px 30px 10px;">
                                        <div :class="activeMiddle == 1 ? 'active-middle':'middle-item'" @click="chooseMiddle(1)" flex="dir:top main:center cross:center">
                                            <img src="statics/img/plugins/vip.png" alt="">
                                            <div>会员专享</div>
                                        </div>
                                        <div :class="activeMiddle == 2 ? 'active-middle':'middle-item'" @click="chooseMiddle(2)" flex="dir:top main:center cross:center">
                                            <img src="statics/img/plugins/rights.png" alt="">
                                            <div>会员特权</div>
                                        </div>
                                        <div :class="activeMiddle == 3 ? 'active-middle':'middle-item'" @click="chooseMiddle(3)" flex="dir:top main:center cross:center">
                                            <img src="statics/img/plugins/open-gift.png" alt="">
                                            <div>开卡即赠</div>
                                        </div>
                                        <div :class="activeMiddle == 4 ? 'active-middle':'middle-item'" @click="chooseMiddle(4)" flex="dir:top main:center cross:center">
                                            <img src="statics/img/plugins/rubik.png" alt="">
                                            <div>图片广告</div>
                                        </div>
                                    </div>
                                    <el-card v-if="activeMiddle > 0" shadow="never" class="box-card middle-card" :style="{'width': activeMiddle == 4 ? '625px': '480px'}">
                                        <div slot="header" class="clearfix">
                                            <span v-if="activeMiddle == 1">会员专享设置</span>
                                            <span v-else-if="activeMiddle == 2">会员特权设置</span>
                                            <span v-else-if="activeMiddle == 3">开卡即赠设置</span>
                                            <span v-else-if="activeMiddle == 4">图片广告设置</span>
                                        </div>
                                        <el-form v-if="activeMiddle == 1" label-width="120px" @submit.native.prevent>
                                            <el-form-item label="背景颜色" prop="vip_bg">
                                                <el-color-picker style="margin-top: 10px;" size="small" v-model="form.vip_bg"></el-color-picker>
                                            </el-form-item>
                                            <el-form-item label="文字颜色" prop="vip_color">
                                                <el-color-picker style="margin-top: 10px;" size="small" v-model="form.vip_color"></el-color-picker>
                                            </el-form-item>
                                            <el-form-item label="每页行数" prop="vip_line">
                                                <el-input @change="changeLine" style="width: 240px" size="small" min="1" max="100" v-model="form.vip_line" type="number"></el-input>
                                            </el-form-item>
                                            <el-form-item label="每行个数" prop="vip_number">
                                                <el-radio-group v-model="form.vip_number" @change="changeVipNumber">
                                                    <el-radio :label="3">3</el-radio>
                                                    <el-radio :label="4">4</el-radio>
                                                    <el-radio :label="5">5</el-radio>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item label="左右滑动" prop="is_vip_scroll">
                                                <el-switch @change="openScroll" v-model="is_vip_scroll"></el-switch>
                                            </el-form-item>
                                            <el-form-item label="会员专享图标" prop="vip_icon">
                                                <div v-for="(list,idx) in form.vip_icon_list">
                                                    <div class="vip-icon-list" v-for="(item,index) in list">
                                                        <app-image-upload v-model="item.img" width="88" height="88"></app-image-upload>
                                                        <el-input style="width: 200px;vertical-align: top;" size="small" v-model="item.name"></el-input>
                                                        <div class="edit-options">
                                                            <el-button @click="deleteAd(index,idx)" icon="el-icon-delete" type="primary"></el-button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <el-button size="small" @click="addIcon">添加图标</el-button>
                                            </el-form-item>
                                        </el-form>
                                        <el-form v-if="activeMiddle == 2" label-width="120px" @submit.native.prevent>
                                            <div class="vip-icon-list" flex="dir:left" style="width: 410px;margin-left: 30px" v-for="(item,index) in form.right_list">
                                                <app-image-upload v-model="item.img" width="630" height="160"></app-image-upload>
                                                <div>
                                                    <div style="margin-bottom: 5px;" flex="dir:left cross:center">
                                                        <el-input maxlength="4" style="margin-left: 10px;width: 240px" size="small" v-model="item.big" placeholder="大标题（字符限制4）"></el-input>
                                                        <el-color-picker class="color-picker" size="small" v-model="item.big_color"></el-color-picker>
                                                    </div>
                                                    <div flex="dir:left cross:center">
                                                        <el-input maxlength="6" style="margin-left: 10px;width: 240px" size="small" v-model="item.small" placeholder="小标题（字符限制6）"></el-input>
                                                        <el-color-picker class="color-picker" size="small" v-model="item.small_color"></el-color-picker>
                                                    </div>
                                                </div>
                                                <div class="edit-options">
                                                    <el-button @click="deleteRight(index)" icon="el-icon-delete" type="primary"></el-button>
                                                </div>
                                            </div>
                                            <el-button v-if="form.right_list.length < 20" style="margin-left: 30px" size="small" @click="addRight">添加图标</el-button>
                                        </el-form>
                                        <el-form v-if="activeMiddle == 3" label-width="60px" @submit.native.prevent>
                                            <el-form-item label="积分" prop="integral_icon">
                                                <div style="position: relative">
                                                    <app-attachment :multiple="false" :max="1" @selected="integralPicUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*360" placement="top">
                                                            <el-button size="mini">选择文件</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                                        <app-image width="80px"
                                                                   height="80px"
                                                                   mode="aspectFill"
                                                                   :src="integral_icon">
                                                        </app-image>
                                                        <el-button v-if="integral_icon != ''" class="del-btn" @click="resetImg(1,'integral')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                    </div>
                                                    <el-button size="mini" @click="resetImg(2,'integral')" class="reset" type="primary">恢复默认</el-button>
                                                </div>
                                            </el-form-item>
                                            <el-form-item label="余额" prop="balance_icon">
                                                <div style="position: relative">
                                                    <app-attachment :multiple="false" :max="1" @selected="balancePicUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*360" placement="top">
                                                            <el-button size="mini">选择文件</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                                        <app-image width="80px"
                                                                   height="80px"
                                                                   mode="aspectFill"
                                                                   :src="balance_icon">
                                                        </app-image>
                                                        <el-button v-if="balance_icon != ''" class="del-btn" @click="resetImg(1,'balance')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                    </div>
                                                    <el-button size="mini" @click="resetImg(2,'balance')" class="reset" type="primary">恢复默认</el-button>
                                                </div>
                                            </el-form-item>
                                            <el-form-item label="优惠券" prop="coupon_icon">
                                                <div style="position: relative">
                                                    <app-attachment :multiple="false" :max="1" @selected="couponPicUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*360" placement="top">
                                                            <el-button size="mini">选择文件</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                                        <app-image width="80px"
                                                                   height="80px"
                                                                   mode="aspectFill"
                                                                   :src="coupon_icon">
                                                        </app-image>
                                                        <el-button v-if="coupon_icon != ''" class="del-btn" @click="resetImg(1,'coupon')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                    </div>
                                                    <el-button size="mini" @click="resetImg(2,'coupon')" class="reset" type="primary">恢复默认</el-button>
                                                </div>
                                            </el-form-item>
                                            <el-form-item label="卡券" prop="card_icon">
                                                <div style="position: relative">
                                                    <app-attachment :multiple="false" :max="1" @selected="cardPicUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*360" placement="top">
                                                            <el-button size="mini">选择文件</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                                        <app-image width="80px"
                                                                   height="80px"
                                                                   mode="aspectFill"
                                                                   :src="card_icon">
                                                        </app-image>
                                                        <el-button v-if="card_icon != ''" class="del-btn" @click="resetImg(1,'card')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                                    </div>
                                                    <el-button size="mini" @click="resetImg(2,'card')" class="reset" type="primary">恢复默认</el-button>
                                                </div>
                                            </el-form-item>
                                        </el-form>
                                        <el-form v-if="activeMiddle == 4" label-width="80px" @submit.native.prevent>
                                            <el-tabs v-model="middleName" type="card">
                                                <el-tab-pane label="图片样式" name="css">
                                                    <el-form-item label="样式">
                                                        <div flex="dir:left" style="flex-wrap: wrap;">
                                                            <div :class="activeStyle == index ? 'block active':'block'" v-for="(item, index) in style_list"
                                                                 @click="selectStyle(index)">
                                                                <div class="rubik-list">
                                                                    <img :src="item.icon" style="width: 150px;height: 72px;">
                                                                </div>
                                                                <div style="font-size: 12px;margin: 6px">{{item.name}}</div>
                                                            </div>
                                                        </div>
                                                    </el-form-item>
                                                </el-tab-pane>
                                                <el-tab-pane label="图片上传" name="upload">
                                                    <template v-if="chooseIndex != null">
                                                        <el-card shadow="never">
                                                            <el-form-item label="图片上传">
                                                                <app-attachment :multiple="false" :max="1" v-model="form.rubik.list[chooseIndex].pic">
                                                                    <el-button size="mini">选择图片</el-button>
                                                                </app-attachment>
                                                                <app-gallery v-if="form.rubik.list[chooseIndex].pic" :multiple="false" width="100px" height="100px"
                                                                             :url="form.rubik.list[chooseIndex].pic"></app-gallery>
                                                            </el-form-item>
                                                            <el-form-item label="选择链接">
                                                                <app-pick-link title="选择链接" @selected="selectLink">
                                                                    <el-input size="small" v-model="form.rubik.list[chooseIndex].url" :disabled="true">
                                                                        <template slot="append">
                                                                            <el-button>选择链接</el-button>
                                                                        </template>
                                                                    </el-input>
                                                                </app-pick-link>
                                                            </el-form-item>
                                                        </el-card>
                                                    </template>
                                                    <template v-else>
                                                        <el-form-item label="图片上传">
                                                            <span>请先在左边选择图片位置</span>
                                                        </el-form-item>
                                                    </template>
                                                </el-tab-pane>
                                            </el-tabs>
                                        </el-form>
                                    </el-card>
                                </el-tab-pane>
                                <el-tab-pane label="底部样式" name="bottom">
                                    <el-card shadow="never" class="box-card">
                                        <div slot="header" class="clearfix">
                                            <span>底部样式设置</span>
                                        </div>
                                        <el-form label-width="120px">
                                            <el-form-item label="文字颜色" prop="bottom_style_2">
                                                <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.bottom_style_2"></el-color-picker>
                                            </el-form-item>
                                            <el-form-item label="背景颜色" prop="bottom_style_1">
                                                <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.bottom_style_1"></el-color-picker>
                                            </el-form-item>
                                            <el-form-item label="按钮文字颜色" prop="bottom_btn_style_2">
                                                <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.bottom_btn_style_2"></el-color-picker>
                                            </el-form-item>
                                            <el-form-item label="按钮背景颜色" prop="bottom_btn_style_1">
                                                <el-color-picker class="color-picker" style="margin-top: 10px;" size="small" v-model="form.bottom_btn_style_1"></el-color-picker>
                                            </el-form-item>
                                        </el-form>
                                    </el-card>
                                </el-tab-pane>
                                <el-button :loading="btnLoading" class="third-button-item button-item" type="primary" @click="store('form')" size="small">保存</el-button>
                                <el-button v-if="hideBtn" :loading="btnLoading" class="third-button-item reset-btn button-item" @click="resetSetting" size="small">恢复默认</el-button>
                            </el-tabs>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="下单表单" name="four">
                    <div class="form-body">
                        <el-row>
                            <el-form-item label="表单状态">
                                <el-switch
                                        v-model="detail.is_order_form"
                                        :active-value="1"
                                        :inactive-value="0">
                                </el-switch>
                            </el-form-item>
                            <el-form-item v-if="detail.is_order_form"  label="表单设置" prop="selectedOptions">
                                <app-form :value.sync="detail.order_form"></app-form>
                            </el-form-item>
                        </el-row>
                    </div>
                </el-tab-pane>
                <el-button v-if="activeName != 'third'" :loading="btnLoading" class="button-item" type="primary" @click="store('form')" size="small">
                    保存
                </el-button>
            </el-tabs>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                showTreaty: false,
                loading: false,
                hideBtn: true,
                btnLoading: false,
                vipHeight: '160px',
                icon_list: [],
                downloading: false,
                is_vip_scroll: 0,
                form: {
                    buy_big_color: '#D0B8A5',
                    buy_small_color: '#C09878',
                    buy_btn_color: '#5A4D40',
                    buy_btn_bg_color: '',
                    buy_big: '开通超级会员，立省更多',
                    buy_small: '超值全场8.8折！',
                    buy_btn_text: '立即开通',
                    renew_text_color: '#D0B8A5',
                    renew_btn_bg_color: '',
                    renew_text: 'SVIP会员优享9.5折，全场包邮',
                    card_bg: '#000',
                    card_color: '#000',
                    vip_bg: '#fff',
                    vip_color: '#000',
                    vip_number: 4,
                    vip_line: 2,
                    is_vip_scroll: 0,
                    vip_icon_list: [[
                        {img: 'statics/img/app/vip_card/icon1.png', name: '全场包邮'},
                        {img: 'statics/img/app/vip_card/icon2.png', name: '专属折扣'},
                        {img: 'statics/img/app/vip_card/icon3.png', name: '大额积分'},
                        {img: 'statics/img/app/vip_card/icon4.png', name: '余额红包'},
                        {img: 'statics/img/app/vip_card/icon5.png', name: '海量优惠券'},
                        {img: 'statics/img/app/vip_card/icon6.png', name: '获赠卡券'},
                        {img: 'statics/img/app/vip_card/icon7.png', name: '敬请期待'}
                    ]],
                    right_list: [
                        {img: 'statics/img/app/vip_card/off.png', big: '专属折扣',big_color: '#fff',small:'省上加省',small_color: '#fff'},
                        {img: 'statics/img/app/vip_card/free-shipping.png', big: '全场包邮',big_color: '#fff',small:'畅享购物',small_color: '#fff'},
                    ],
                    rubik: {
                        height: 180,
                        list: []
                    },
                    bottom_style_1: '#342E25',
                    bottom_style_2: '#F3BE94',
                    bottom_btn_style_1: '#F3BE94',
                    bottom_btn_style_2: '#342E25',
                    sort: ['member','right','gift'],
                    head_card: 'statics/img/app/vip_card/default-card.png',
                    buy_bg: 'statics/img/app/vip_card/buy_bg.png',
                    renew_bg: 'statics/img/app/vip_card/buy_bg.png',
                    integral_icon: 'statics/img/app/vip_card/integral.png',
                    card_icon: 'statics/img/app/vip_card/card.png',
                    coupon_icon: 'statics/img/app/vip_card/coupon.png',
                    balance_icon: 'statics/img/app/vip_card/balance.png',
                },
                chooseIndex: null,
                style_list: [
                    {
                        name: '1张图',
                        height: 180,
                        list: [
                            {
                                w: 375,
                                h: 180,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-0.png',
                    },
                    {
                        name: '2张图',
                        height: 180,
                        list: [
                            {
                                w: 150,
                                h: 180,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 225,
                                h: 180,
                                x: 150,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-1.png',
                    },
                    {
                        name: '3张图',
                        height: 180,
                        list: [
                            {
                                w: 150,
                                h: 180,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 225,
                                h: 90,
                                x: 150,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 225,
                                h: 90,
                                x: 150,
                                y: 90,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-2.png',
                    },
                    {
                        name: '4张图',
                        height: 180,
                        list: [
                            {
                                w: 150,
                                h: 180,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 225,
                                h: 90,
                                x: 150,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 112.5,
                                h: 90,
                                x: 150,
                                y: 90,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 112.5,
                                h: 90,
                                x: 262.5,
                                y: 90,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-3.png',
                    },
                    {
                        name: '2张图平分',
                        height: 120,
                        list: [
                            {
                                w: 187.5,
                                h: 120,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 187.5,
                                h: 120,
                                x: 187.5,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-4.png',
                    },
                    {
                        name: '3张图平分',
                        height: 120,
                        list: [
                            {
                                w: 125,
                                h: 120,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 125,
                                h: 120,
                                x: 125,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 125,
                                h: 120,
                                x: 250,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-5.png',
                    },
                    {
                        name: '4张图左右平分',
                        height: 188,
                        list: [
                            {
                                w: 96,
                                h: 188,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 96,
                                h: 188,
                                x: 96,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 96,
                                h: 188,
                                x: 192,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 96,
                                h: 188,
                                x: 288,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-6.png',
                    },
                    {
                        name: '4张图上下平分',
                        height: 200,
                        list: [
                            {
                                w: 187.5,
                                h: 93,
                                x: 0,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 187.5,
                                h: 93,
                                x: 187.5,
                                y: 0,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 187.5,
                                h: 93,
                                x: 0,
                                y: 93,
                                pic: '',
                                url: ''
                            },
                            {
                                w: 187.5,
                                h: 93,
                                x: 187.5,
                                y: 93,
                                pic: '',
                                url: ''
                            },
                        ],
                        icon: 'statics/img/plugins/rubik-7.png',
                    },
                ],
                activeStyle: null,
                detail: {
                    rules: [],
                    share_level: [],
                    payment_type: []
                },
                share: [],
                head_card: 'statics/img/app/vip_card/default-card.png',
                buy_bg: 'statics/img/app/vip_card/buy_bg.png',
                renew_bg: 'statics/img/app/vip_card/buy_bg.png',
                integral_icon: 'statics/img/app/vip_card/integral.png',
                card_icon: 'statics/img/app/vip_card/card.png',
                coupon_icon: 'statics/img/app/vip_card/coupon.png',
                balance_icon: 'statics/img/app/vip_card/balance.png',
                activeName: 'first',
                activePage: 'enter',
                middleName: 'css',
                rule: {},
                title_desc: '',
                shareLevel: [],
                is_show_share: 1,
                dialogVisible: false,
                dialogLoading: false,
                activeMiddle: null,
                vip_icon_width: 87.5,
                icon_width: 351,
                activeRubik: null,
                formStatus: 1,
                ruleForm:{
                    value: {}
                }
            };
        },
        methods: {
            resetSetting() {
                if(this.activeName == 'third') {
                    if(this.activePage == 'enter') {
                        this.form.buy_big_color = '#D0B8A5';
                        this.form.buy_small_color = '#C09878';
                        this.form.buy_btn_color = '#5A4D40';
                        this.form.buy_btn_bg_color = '';
                        this.form.buy_big = '开通超级会员，立省更多';
                        this.form.buy_small = '超值全场8.8折！';
                        this.form.buy_btn_text = '立即开通';
                        this.form.renew_text_color = '#D0B8A5';
                        this.form.renew_btn_bg_color = '';
                        this.form.renew_text = 'SVIP会员优享9.5折，全场包邮';
                        this.resetImg(2,'buy');
                        this.resetImg(2,'renew');
                    }else if(this.activePage == 'head') {
                        this.form.card_bg = '#000';
                        this.form.card_color = '#000';
                        this.resetImg(2,'head');
                    }else if(this.activePage == 'middle') {
                        if(this.activeMiddle == 1) {
                            this.form.vip_bg = '#fff';
                            this.form.vip_color = '#000';
                            this.form.vip_number = 4;
                            this.form.vip_line = 2;
                            this.form.is_vip_scroll = 0;
                            this.form.vip_icon_list = [[
                                {img: 'statics/img/app/vip_card/icon1.png', name: '全场包邮'},
                                {img: 'statics/img/app/vip_card/icon2.png', name: '专属折扣'},
                                {img: 'statics/img/app/vip_card/icon3.png', name: '大额积分'},
                                {img: 'statics/img/app/vip_card/icon4.png', name: '余额红包'},
                                {img: 'statics/img/app/vip_card/icon5.png', name: '海量优惠券'},
                                {img: 'statics/img/app/vip_card/icon6.png', name: '获赠卡券'},
                                {img: 'statics/img/app/vip_card/icon7.png', name: '敬请期待'}
                            ]];
                        }else if (this.activeMiddle == 2) {
                            this.form.right_list = [
                                {img: 'statics/img/app/vip_card/off.png', big: '专属折扣',big_color: '#fff',small:'省上加省',small_color: '#fff'},
                                {img: 'statics/img/app/vip_card/free-shipping.png', big: '全场包邮',big_color: '#fff',small:'畅享购物',small_color: '#fff'},
                            ]
                        }else if (this.activeMiddle == 3) {
                            this.resetImg(2,'integral');
                            this.resetImg(2,'balance');
                            this.resetImg(2,'coupon');
                            this.resetImg(2,'card');
                        }else if (this.activeMiddle == 4) {
                            this.form.rubik = {
                                height: 180,
                                list: []
                            }
                        }
                    }else if(this.activePage == 'bottom') {
                        this.form.bottom_style_1 = '#342E25';
                        this.form.bottom_style_2 = '#F3BE94';
                        this.form.bottom_btn_style_1 = '#F3BE94';
                        this.form.bottom_btn_style_2 = '#342E25';
                    }
                }
            },

            toImg(index) {
                this.chooseIndex = index;
                this.activePage = 'middle'
                this.activeMiddle = 4;
                this.middleName = 'upload'
            },
            toRemove(index) {
                this.form.sort.splice(index,1);
                this.activeMiddle = null;
            },

            toUp(index,item) {
                let sort = this.form.sort;
                let inactive = this.form.sort[index - 1];
                this.form.sort[index - 1] = item;
                this.form.sort[index] = inactive;
                Vue.set(this.form.sort,[index - 1],item)
                Vue.set(this.form.sort,[index],inactive)
            },

            toDown(index,item) {
                let sort = this.form.sort;
                let inactive = this.form.sort[index + 1];
                this.form.sort[index + 1] = item;
                this.form.sort[index] = inactive;
                Vue.set(this.form.sort,[index + 1],item)
                Vue.set(this.form.sort,[index],inactive)
            },

            selectLink(e) {
                console.log(e)
                this.form.rubik.list[this.chooseIndex].url = e[0].new_link_url;
                this.form.rubik.list[this.chooseIndex].open_type = e[0].open_type;
            },

            changeLine() {
                this.openScroll(this.is_vip_scroll);
            },

            changeVipNumber(e) {
                if(e == 3) {
                    this.vip_icon_width = 117
                }else if(e == 4) {
                    this.vip_icon_width = 87.5
                }else if(e == 5) {
                    this.vip_icon_width = 70
                }
                this.openScroll(this.is_vip_scroll);
            },

            openScroll(e) {
                let that = this;
                that.is_vip_scroll = e;
                that.form.is_vip_scroll = e;
                let vip_icon_list = [[]];
                that.vipHeight = +that.form.vip_line * 80 + 'px';
                let num = 1;
                let number = 0;
                for(let i in that.form.vip_icon_list) {
                    for(let index in that.form.vip_icon_list[i]) {
                        vip_icon_list[0].push(that.form.vip_icon_list[i][index])
                    }
                }
                that.form.vip_icon_list = vip_icon_list;
                if(that.is_vip_scroll) {
                    vip_icon_list = [];
                    num = Math.ceil(that.form.vip_icon_list[0].length / (that.form.vip_number * that.form.vip_line));
                    let proportion = +that.form.vip_number * +that.form.vip_line;
                    for(let i=0;i<that.form.vip_icon_list[0].length;i++){
                        if(i % proportion == 0 && i != 0){
                            vip_icon_list.push(that.form.vip_icon_list[0].slice(number,i));
                            number = i;
                        }
                        if((i+1)==that.form.vip_icon_list[0].length){
                            vip_icon_list.push(that.form.vip_icon_list[0].slice(number,(i+1)));
                        }
                    }
                }
                that.form.vip_icon_list = vip_icon_list;
                that.icon_width = 351 * num
            },

            selectStyle(index) {
                this.activeStyle = index;
                this.form.rubik = this.style_list[index];
            },

            handleClick() {
                this.activeMiddle = null;
                this.activePage === 'middle' ? this.hideBtn = false : this.hideBtn = true;
            },

            addIcon() {
                this.form.vip_icon_list[this.form.vip_icon_list.length - 1].push({img: '', name: ''})
            },

            addRight() {
                this.form.right_list.push({img: '', name: ''})
            },
            deleteAd(index,idx) {
                this.form.vip_icon_list[idx].splice(index,1)
            },
            deleteRight(index) {
                this.form.right_list.splice(index,1)
            },
            // 选择中间样式
            chooseMiddle(res) {
                this.hideBtn = true;
                this.activeMiddle = res;
                let partotop = document.getElementById('body').offsetTop;
                let distance;
                if(res == 1) {
                    if(this.form.sort.indexOf('member') == -1) {
                        this.form.sort.push('member');
                        distance = 2000;
                    }else {
                        distance = document.getElementById('member').offsetTop;
                    }
                } else if (res == 2){
                    if(this.form.sort.indexOf('right') == -1) {
                        this.form.sort.push('right')
                        distance = 2000;
                    }else {
                        distance = document.getElementById('right').offsetTop;
                    }
                } else if (res == 3){
                    if(this.form.sort.indexOf('gift') == -1) {
                        this.form.sort.push('gift')
                        distance = 2000;
                    }else {
                        distance = document.getElementById('gift').offsetTop;
                    }
                } else if (res == 4){
                    if(this.form.sort.indexOf('rubik') == -1) {
                        this.form.sort.push('rubik')
                        distance = 2000;
                    }else {
                        distance = document.getElementById('rubik').offsetTop;
                    }
                }
                setTimeout(v => {
                    document.getElementById('body').scrollTop=distance - partotop - 70;
                },300)
            },

            headPicUrl(e) {
                this.form.head_card = e[0].url;
                this.head_card = e[0].url;
            },

            buyPicUrl(e) {
                this.form.buy_bg = e[0].url;
                this.buy_bg = e[0].url;
            },
            renewPicUrl(e) {
                this.form.renew_bg = e[0].url;
                this.renew_bg = e[0].url;
            },
            // offPicUrl(e) {
            //     this.form.off_bg = e[0].url;
            //     this.off_bg = e[0].url;
            // },
            // freeShippingPicUrl(e) {
            //     this.form.freeShipping_bg = e[0].url;
            //     this.freeShipping_bg = e[0].url;
            // },
            integralPicUrl(e) {
                this.form.integral_icon = e[0].url;
                this.integral_icon = e[0].url;
            },
            cardPicUrl(e) {
                this.form.card_icon = e[0].url;
                this.card_icon = e[0].url;
            },
            couponPicUrl(e) {
                this.form.coupon_icon = e[0].url;
                this.coupon_icon = e[0].url;
            },
            balancePicUrl(e) {
                this.form.balance_icon = e[0].url;
                this.balance_icon = e[0].url;
            },
            // 获取分销设置
            getShareSetting() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/share/basic'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        let shareArr = [
                            {
                                label: '一级分销',
                                value: 'share_commission_first'
                            },
                            {
                                label: '二级分销',
                                value: 'share_commission_second'
                            },
                            {
                                label: '三级分销',
                                value: 'share_commission_third'
                            },
                        ];
                        let level = e.data.data.list.level;
                        shareArr.splice((level));
                        self.shareLevel = shareArr;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            resetImg(res,position) {
                switch(position) {
                     case 'buy':
                        if(res == 2) {
                            this.buy_bg = 'statics/img/app/vip_card/buy_bg.png';
                            this.form.buy_bg = 'statics/img/app/vip_card/buy_bg.png';
                        }else {
                            this.buy_bg = '';
                            this.form.buy_bg = '';
                        }
                        break;
                     case 'renew':
                        if(res == 2) {
                            this.renew_bg = 'statics/img/app/vip_card/buy_bg.png';
                            this.form.renew_bg = 'statics/img/app/vip_card/buy_bg.png';
                        }else {
                            this.renew_bg = '';
                            this.form.renew_bg = '';
                        }
                        break;
                     case 'head':
                        if(res == 2) {
                            this.head_card = 'statics/img/app/vip_card/default-card.png';
                            this.form.head_card = 'statics/img/app/vip_card/default-card.png';
                        }else {
                            this.head_card = '';
                            this.form.head_card = '';
                        }
                        break;
                     case 'card':
                        if(res == 2) {
                            this.card_icon = 'statics/img/app/vip_card/card.png';
                            this.form.card_icon = 'statics/img/app/vip_card/card.png';
                        }else {
                            this.card_icon = '';
                            this.form.card_icon = '';
                        }
                        break;
                     case 'coupon':
                        if(res == 2) {
                            this.coupon_icon = 'statics/img/app/vip_card/coupon.png';
                            this.form.coupon_icon = 'statics/img/app/vip_card/coupon.png';
                        }else {
                            this.coupon_icon = '';
                            this.form.coupon_icon = '';
                        }
                        break;
                     case 'balance':
                        if(res == 2) {
                            this.balance_icon = 'statics/img/app/vip_card/balance.png';
                            this.form.balance_icon = 'statics/img/app/vip_card/balance.png';
                        }else {
                            this.balance_icon = '';
                            this.form.balance_icon = '';
                        }
                        break;
                     case 'integral':
                        if(res == 2) {
                            this.integral_icon = 'statics/img/app/vip_card/integral.png';
                            this.form.integral_icon = 'statics/img/app/vip_card/integral.png';
                        }else {
                            this.integral_icon = '';
                            this.form.integral_icon = '';
                        }
                        break;
                     case 'freeShipping':
                        if(res == 2) {
                            this.freeShipping_bg = 'statics/img/app/vip_card/free-shipping.png';
                            this.form.freeShipping_bg = 'statics/img/app/vip_card/free-shipping.png';
                        }else {
                            this.freeShipping_bg = '';
                            this.form.freeShipping_bg = '';
                        }
                        break;
                     case 'off':
                        if(res == 2) {
                            this.off_bg = 'statics/img/app/vip_card/off.png';
                            this.form.off_bg = 'statics/img/app/vip_card/off.png';
                        }else {
                            this.off_bg = '';
                            this.form.off_bg = '';
                        }
                        break;
                }
            },
            // 提交
            store(formName) {
                this.detail.is_agreement = this.showTreaty;
                this.detail.form = JSON.stringify(this.form);
                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/setting/index'
                    },
                    method: 'post',
                    data: this.detail
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            // 获取数据
            loadData() {
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/setting/index'
                    },
                    method: 'get'
                }).then(e => {
                    that.loading = false;
                    if (e.data.code === 0) {
                        that.detail = e.data.data.setting;
                        if(that.detail.form != "") {
                            that.form = e.data.data.setting.form;
                            that.head_card = e.data.data.setting.form.head_card
                            that.buy_bg = e.data.data.setting.form.buy_bg
                            that.renew_bg = e.data.data.setting.form.renew_bg
                            that.off_bg = e.data.data.setting.form.off_bg
                            that.freeShipping_bg = e.data.data.setting.form.freeShipping_bg
                            that.integral_icon = e.data.data.setting.form.integral_icon
                            that.card_icon = e.data.data.setting.form.card_icon
                            that.coupon_icon = e.data.data.setting.form.coupon_icon
                            that.balance_icon = e.data.data.setting.form.balance_icon
                        }
                        that.showTreaty = that.detail.is_agreement;
                        that.vipHeight = +that.form.vip_line * 80 + 'px';
                        that.is_vip_scroll = that.form.is_vip_scroll;
                        that.icon_width = 351 * that.form.vip_icon_list.length;
                        if(that.form.vip_number == 3) {
                            this.vip_icon_width = 117
                        }else if(that.form.vip_number == 4) {
                            this.vip_icon_width = 87.5
                        }else if(that.form.vip_number == 5) {
                            this.vip_icon_width = 70
                        }
                        let permissions = e.data.data.permissions;
                        let sign = false;
                        permissions.forEach(function (item, index) {
                            if (item == 'share') {
                                sign = true;
                            }
                        })
                        that.is_show_share = sign;
                    }
                }).catch(e => {
                    that.loading = false;
                });
            }
        },
        created() {
            this.loadData();
            this.getShareSetting();
        },
    });
</script>
