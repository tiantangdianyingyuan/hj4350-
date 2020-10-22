<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
        min-width: 1280px;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
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
        color: #ff4544;
    }

    .title {
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .mobile {
        width: 400px;
        height: 740px;
        border: 1px solid #cccccc;
        padding: 25px 10px;
        border-radius: 30px;
        margin: 0 20px;
        position: relative;
    }

    .mobile img {
        width: 375px;
        height: 667px;
    }

    .mobile .bg-img {
        height: 180px;
        width: 375px;
        position: absolute;
        left: 0;
        top: 65px;
    }

    .mobile .bottom-bg-img {
        width: 375px;
        height: auto;
        position: absolute;
        left: 10px;
        top: 486px;
    }

    .left-setting-menu {
        width: 140px;
    }

    .left-setting-menu .el-form-item {
        height: 60px;
        display: flex;
        align-items: center;
        margin-bottom: 0;
        cursor: pointer;
    }

    .left-setting-menu .el-form-item .el-form-item__label {
        cursor: pointer;
    }

    .left-setting-menu .el-form-item.active {
        background-color: #F3F5F6;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .no-radius {
        border-top-left-radius: 0!important;
    }

    .left-setting-menu .el-form-item .el-form-item__content {
        margin-left: 0!important
    }

    .item-img {
        height: 700px;
        padding: 25px 10px;
        border-radius: 30px;
        border: 1px solid #CCCCCC;
        background-color: #fff;
        position: relative;
    }

    .item-img .title-txt {
        position: absolute;
        top: 55px;
        left: 10px;
        text-align: center;
        width: 375px;
        font-size: 16px;
        font-weight: 600;
        color: #303133;
    }

    .item-img .status-txt {
        position: absolute;
        top: 130px;
        left: 10px;
        text-align: center;
        width: 375px;
        font-size: 14px;
        color: #999;
    }

    .item-img .status-txt div {
        width: 33.3%;
    }

    .item-img .tab-txt {
        position: absolute;
        top: 195px;
        height: 28px;
        width: 100%;
        line-height: 26px;
        font-size: 12px;
        color: #666;
    }

    .item-img .tab-txt .tab-list {
        border: 1px solid #ff4544;
        border-radius: 14px;
    }

    .item-img .tab-txt .tab-list div {
        padding: 0 12px;
    }

    .item-img .price-text {
        position: absolute;
        right: 32px;
        top: 250px;
        text-align: right;
        width: 200px;
        font-size: 11px;
        color: #999;
    }

    .item-img .price-text div {
        height: 101px;
    }

    .item-img .offer-text {
        position: absolute;
        left: 85px;
        top: 215px;
        width: 200px;
        font-size: 11px;
        color: #999;
    }

    .item-img .offer-text div {
        height: 72px;
    }

    .item-img .become-txt {
        position: absolute;
        top: 440px;
        left: 10px;
        text-align: center;
        width: 375px;
        font-size: 14px;
        color: #353535;
    }

    .item-img .become-about {
        position: absolute;
        top: 510px;
        left: 46px;
        text-align: left;
        width: 305px;
        font-size: 12px;
        color: #999999;
    }

    .item-img .enter {
        position: absolute;
        top: 340px;
        left: 20px;
        width: 351px;
        flex-wrap: wrap;
    }

    .item-img .enter .enter-item {
        width: 117px;
        text-align: center;
        padding-top: 23.5px;
        color: #666;
    }

    .item-img .enter .enter-item img {
        width: 30.5px;
        width: 30.5px;
        margin-bottom: 5px;
    }

    .item-img .enter .enter-item span {
        color: #ff4544;
    }

    .item-img .index {
        width: 375px;
        height: 667px;
        background-color: #F7F7F7;
        padding-top: 76px;
    }

    .item-img .index .header {
        position: absolute;
        top: 25px;
        left: 10px;
        width: 375px;
        height: 64px;
    }

    .item-img .index .header-item {
        margin: 0 auto;
        width: 351px;
        padding: 20px;
        color: #fff;
        font-size: 16px;
        border-radius: 8px;
        background: linear-gradient(to bottom, #ff7757, #ff6362);
        font-size: 14px;
    }

    .item-img .index .price-item {
        width: 160px;
    }

    .item-img .index .bonus-item {
        height: 50px;
        border-radius: 8px;
        width: 351px;
        margin: 8px auto 0;
        background-color: #fff;
        font-size: 14px;
        color: #353535;
        padding: 0 20px;
    }

    .item-img .index .cash-btn {
        height: 24px;
        width: 48px;
        border-radius: 12px;
        line-height: 22px;
        border: 1px solid #ff4544;
        color: #ff4544;
        text-align: center;
    }

    .default {
        width: 10%;
        min-width: 100px;
    }

    .left-setting-menu .el-form-item__label {
        text-align: left;
        padding: 0 0 0 30px;
    }

    .reset {
        position: absolute;
        top: 7px;
        left: 90px;
    }

    .del-btn.el-button--mini.is-circle {
        position: absolute;
        top: -11px;
        right: -11px;
        padding: 4px;
    }

    .rate {
        font-size: 12px;
        height: 40px;
    }

    .rate img {
        height: 14px;
        width: 14px;
        margin-left: 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form v-model="loading" :model="form" label-width="150px" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本" name="first">
                    <div class="form-body">
                        <el-form-item class="switch" label="团队分红" prop="bonusSwitch">
                            <el-switch v-model="bonusSwitch" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="bonusSwitch == 1" prop="bonus_rate">
                            <template slot='label'>
                                <span>分红比例</span>
                                <el-tooltip effect="dark" content="分红=商品实付金额*分红比例"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.bonus_rate" autocomplete="off">
                                <template slot="append">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item v-if="bonusSwitch == 1" class="switch" label="成为队长条件" prop="become_type">
                            <el-radio-group @change="tabType" v-model="form.become_type">
                                <el-radio :label="0">申请</el-radio>
                                <el-radio :label="3">下线人数</el-radio>
                                <el-radio :label="4">下线分销商数</el-radio>
                                <el-radio :label="1">累计佣金总额
                                    <el-tooltip effect="dark" content="累计佣金总额=可提现佣金金额+已提现佣金金额"
                                            placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </el-radio>
                                <el-radio :label="2">已提现佣金总额</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item class="switch" v-if="bonusSwitch == 1 && form.become_type == 3" label="下线人数" prop="title">
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.condition" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="bonusSwitch == 1 && form.become_type == 4" label="下线分销商数" prop="title">
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.condition" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="bonusSwitch == 1 && form.become_type == 1" label="累计佣金总额" prop="title">
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.condition" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="bonusSwitch == 1 && form.become_type == 2" label="已提现佣金总额" prop="title">
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.condition" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item v-if="bonusSwitch == 1" class="switch" label="显示申请协议" prop="showTreaty">
                            <el-switch v-model="showTreaty" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="bonusSwitch == 1 && showTreaty == 1" class="switch" label="协议名称" prop="agreement_title">
                            <el-input size="small" style="width: 590px;" v-model="form.agreement_title" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item v-show="bonusSwitch == 1 && showTreaty == 1" class="switch" label="协议内容" prop="agreement_content">
                            <app-rich-text style="width: 590px;" v-model="form.agreement_content"></app-rich-text>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane v-if="bonusSwitch == 1" label="结算" name="second">
                    <div class="form-body">
                        <el-form-item label-width="220px" class="switch" label="提现方式" prop="is_share">
                            <el-checkbox-group v-model="pay_type">
                                <el-checkbox label="auto">自动打款</el-checkbox>
                                <el-checkbox label="wechat">微信线下转账</el-checkbox>
                                <el-checkbox label="alipay">支付宝线下转账</el-checkbox>
                                <el-checkbox label="bank">银行卡线下转账</el-checkbox>
                                <el-checkbox label="balance">余额提现</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label-width="220px" prop="min_money">
                            <template slot='label'>
                                <span>提现门槛金额</span>
                                <el-tooltip effect="dark" content="可提现金额到达此金额时，方可申请提现"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.min_money" autocomplete="off">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label-width="220px" prop="cash_service_charge">
                            <template slot='label'>
                                <span>分红提现手续费</span>
                                <el-tooltip effect="dark" content="申请提现金额-申请提现金额x手续费=实际到账金额；设置0，则不扣除手续费"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input type="number" size="small" style="width: 590px;" v-model="form.cash_service_charge" autocomplete="off">
                                <template slot="append">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label-width="220px">
                            <template slot='label'>
                                <span>免手续费提现金额区间</span>
                                <el-tooltip effect="dark" placement="top">
                                    <div slot="content" style="text-align: center">单笔申请提现金额在此区间时，不扣除手续费；<br/>结束金额必须大于起始金额；<br/>均不填，则不设置免手续提现金额</div>
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <div flex="dir:left">
                                <div style="margin-right: 10px">起始金额</div>
                                <div>
                                    <el-input type="number" size="small" :disabled="form.cash_service_charge == 0" style="width: 200px;" v-model="form.free_cash_min" autocomplete="off">
                                        <template slot="append">元</template>
                                    </el-input>
                                </div>
                                <div style="margin: 0 25px">~</div>
                                <div style="margin-right: 10px">结束金额</div>
                                <div>
                                    <el-input type="number" size="small" :disabled="form.cash_service_charge == 0"  style="width: 200px;" v-model="form.free_cash_max" autocomplete="off">
                                        <template slot="append">元</template>
                                    </el-input>
                                </div>
                            </div>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane v-if="bonusSwitch == 1" label="页面" name="three">
                    <div class="title">
                        <span>头部banner图片</span>
                    </div>
                    <div class="form-body">
                        <div flex="dir:left">
                            <div class="mobile">
                                <div style="height: 690px;position: absolute;overflow-x: hidden;overflow-y: auto;">
                                    <img src="statics/img/plugins/web.png" alt="">
                                    <img class="bg-img" :src="bg_url" alt="">
                                    <img class="bottom-bg-img" :src="bottom_bg_url" alt="">
                                </div>
                            </div>
                            <div>
                                <el-form-item label="申请页面背景图片" prop="top_pic_url">
                                    <div style="position: relative">
                                        <app-attachment :multiple="false" :max="1" @selected="topPicUrl">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸:750*360" placement="top">
                                                <el-button size="mini">选择文件</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <div style="margin-top: 10px;position: relative">
                                            <app-image width="100px"
                                                       height="100px"
                                                       mode="aspectFill"
                                                       :src="bg_url">
                                            </app-image>
                                            <el-button v-if="bg_url != ''" class="del-btn" @click="resetImg(1,'top')" size="mini" type="danger" icon="el-icon-close" circle @click="delPic(index)"></el-button>
                                        </div>
                                        <el-button size="mini" @click="resetImg(2,'top')" class="reset" type="primary">恢复默认</el-button>
                                    </div>
                                </el-form-item>
                                <el-form-item label="申请页面底部图片" prop="bottom_pic_url">
                                    <div style="position: relative">
                                        <app-attachment :multiple="false" :max="1" @selected="bottomPicUrl">
                                            <el-button size="mini">选择文件</el-button>
                                        </app-attachment>
                                        <div style="margin-top: 10px;position: relative">
                                            <app-image width="100px"
                                                       height="100px"
                                                       mode="aspectFill"
                                                       :src="bottom_bg_url">
                                            </app-image>
                                            <el-button v-if="bottom_bg_url != ''" class="del-btn" @click="resetImg(1,'bottom')" size="mini" type="danger" icon="el-icon-close" circle @click="delPic(index)"></el-button>
                                        </div>
                                        <el-button size="mini" @click="resetImg(2,'bottom')" class="reset" type="primary">恢复默认</el-button>
                                    </div>
                                </el-form-item>
                            </div>
                        </div>
                    </div>
                    <div class="title" style="margin-top: 10px">
                        <span>自定义文字</span>
                    </div>
                    <div class="form-body" style="padding: 40px;display: flex;">
                        <div class='left-setting-menu'>
                            <el-form-item label-width="140px" :class='active_setting == "1" ? "active":""' @click.native='chooseSetting("1")' label="入口位置" prop="is_purchase_frame">
                            </el-form-item>
                            <el-form-item v-if="form.become_type != 0" label-width="140px" v-if="" :class='active_setting == "7" ? "active":""' @click.native='chooseSetting("7")' label="未达条件页" prop="become_name">
                            </el-form-item>
                            <el-form-item label-width="140px" :class='active_setting == "2" ? "active":""' @click.native='chooseSetting("2")' label="页面标题" prop="is_comment">
                            </el-form-item>
                            <el-form-item label-width="140px" :class='active_setting == "3" ? "active":""' @click.native='chooseSetting("3")' label="主页" prop="is_sales">
                            </el-form-item>
                            <el-form-item label-width="140px" :class='active_setting == "4" ? "active":""' @click.native='chooseSetting("4")' label="数据统计页" prop="is_member_price">
                            </el-form-item>
                            <el-form-item label-width="140px" :class='active_setting == "5" ? "active":""' @click.native='chooseSetting("5")' label="主页-订单页" prop="is_share_price">
                            </el-form-item>
                            <el-form-item label-width="140px" :class='active_setting == "6" ? "active":""' @click.native='chooseSetting("6")' label="主页-队员页" prop="is_mobile_auth">
                            </el-form-item>
                        </div>
                        <div style='background-color: #F3F5F6;padding: 30px;border-radius: 10px;' :class='active_setting == "1" ? "no-radius":""' flex="dir:left">
                            <div v-if='active_setting == "1"' class="item-img">
                                <app-image mode="aspectFill" src="statics/img/plugins/index.png" style="margin-bottom: 20px" height="667" width="375"></app-image>
                                <div class="enter" flex="dir:left">
                                    <div class="enter-item">
                                        <img src="statics/img/mall/share-custom/img-share-price.png" alt="">
                                        <div>分销佣金</div>
                                        <div><span>3500</span>元</div>
                                    </div>
                                    <div class="enter-item">
                                        <img src="statics/img/mall/share-custom/img-share-order.png" alt="">
                                        <div>分销订单</div>
                                        <div><span>26</span>元</div>
                                    </div>
                                    <div class="enter-item">
                                        <img src="statics/img/mall/share-custom/img-share-cash.png" alt="">
                                        <div>提现明细</div>
                                        <div><span>10</span>元</div>
                                    </div>
                                    <div class="enter-item">
                                        <img src="statics/img/mall/share-custom/img-share-team.png" alt="">
                                        <div>我的团队</div>
                                        <div><span>4</span>人</div>
                                    </div>
                                    <div class="enter-item">
                                        <img src="statics/img/mall/share-custom/img-share-qrcode.png" alt="">
                                        <div>分销佣金</div>
                                    </div>
                                    <div class="enter-item">
                                        <img src="statics/img/mall/share-custom/img-share-bonus.png" alt="">
                                        <div>{{form.form.entry_bonus?form.form.entry_bonus:'团队分红'}}</div>
                                    </div>
                                </div>
                            </div>
                            <div v-if='active_setting == "2" || active_setting == "3"' class="item-img">
                                <div class="index">
                                    <img class="header" src="statics/img/mall/home_block/head.png" alt="">
                                    <div class="title-txt">{{form.form.title?form.form.title:'团队分红'}}</div>
                                    <div class="header-item">
                                        <div flex="main:justify">
                                            <div flex="cross:center">
                                                <app-image style="border: 1px solid #fff;border-radius: 50%;margin-right: 14px;" src="https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=480194109,2955193021&fm=27&gp=0.jpg" width='40' height="40"></app-image>
                                                <div>潘先生</div>
                                            </div>
                                            <div flex="cross:center" class="rate">
                                                <span>{{form.form.rate?form.form.rate:'提成比例'}}5%</span>
                                                <img src="statics/img/mall/question.png" alt="">
                                            </div>
                                        </div>
                                        <div flex="dir:left" style="margin-top: 20px;">
                                            <div class="price-item">
                                                <div>
                                                    <span style="font-size: 20px">2500.20</span>元
                                                </div>
                                                <div>
                                                    <span>{{form.form.total_bonus?form.form.total_bonus:'累计分红金额'}}</span>
                                                    <span>></span>
                                                </div>
                                            </div>
                                            <div>
                                                <div>
                                                    <span style="font-size: 20px">5136.20</span>元
                                                </div>
                                                <div>
                                                    <span>{{form.form.expect_bonus?form.form.expect_bonus:'预计分红金额'}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div flex="main:justify cross:center" class="bonus-item">
                                        <div style="color: #999999">{{form.form.cashd_bonus?form.form.cashd_bonus:'已提现分红'}}</div>
                                        <div flex="cross:center">
                                            <div>
                                                <span style="font-size: 16px">8652.36</span>元
                                                <span style="color: #999999;margin-left: 4px;">></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div flex="main:justify cross:center" class="bonus-item" style="height: 70px;">
                                        <div>
                                            <div>
                                                <span style="font-size: 16px">8652.36</span>元
                                            </div>
                                            <div style="color: #999999">{{form.form.can_cash_bonus?form.form.can_cash_bonus:'可提现分红'}}</div>
                                        </div>
                                        <div class="cash-btn">提现</div>
                                    </div>
                                    <div flex="main:justify cross:center" class="bonus-item" style="height: 100px;">
                                        <div>
                                            <img src="statics/img/app/bonus/order.png" style="height: 20px;width: 20px;display: block;margin-bottom:30px" alt="">
                                            <img src="statics/img/app/bonus/member.png" style="height: 20px;width: 20px;display: block;" alt="">
                                        </div>
                                        <div>
                                            <div style="height: 50px;width: 275px" flex="main:justify cross:center">
                                                <div>{{form.form.orders?form.form.orders:'订单'}}</div>
                                                <div style="color: #9999999;">></div>
                                            </div>
                                            <div style="height: 50px;border-top: 1px solid #e2e2e2;width: 275px" flex="main:justify cross:center">
                                                <div>{{form.form.members?form.form.members:'队员'}}</div>
                                                <div style="color: #9999999">></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if='active_setting == "4"' class="item-img">
                                <app-image mode="aspectFill" src="statics/img/plugins/statics.png" style="margin-bottom: 20px" height="667" width="375"></app-image>
                                <div class="title-txt">{{form.form.statistic_bonus?form.form.statistic_bonus:'分红统计'}}</div>
                                <div class="status-txt" flex="main:justify">
                                    <div>昨日{{form.form.statics_text ? form.form.statics_text : '分红'}}</div>
                                    <div>7日{{form.form.statics_text ? form.form.statics_text : '分红'}}总计</div>
                                    <div>月{{form.form.statics_text ? form.form.statics_text : '分红'}}总计</div>
                                </div>
                                <div class="tab-txt" flex="main:center">
                                    <div class="tab-list" flex="main:center">
                                        <div>昨日{{form.form.statics_text ? form.form.statics_text : '分红'}}</div>
                                        <div style="background-color: #ff4544;color:#fff">7日{{form.form.statics_text ? form.form.statics_text : '分红'}}</div>
                                        <div>月{{form.form.statics_text ? form.form.statics_text : '分红'}}</div>
                                    </div>
                                </div>
                            </div>
                            <div v-if='active_setting == "5"' class="item-img">
                                <app-image mode="aspectFill" src="statics/img/plugins/order.png" style="margin-bottom: 20px" height="667" width="375"></app-image>
                                <div class="title-txt">{{form.form.orders?form.form.orders:'分红订单'}}</div>
                                <div class="price-text">
                                    <div v-for="item in [1,2,3,4,5]">{{form.form.price_text ? form.form.price_text : '分红金额'}}
                                        <span style="color: #ff4544;font-size: 14px;">￥0.48</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if='active_setting == "6"' class="item-img">
                                <app-image mode="aspectFill" src="statics/img/plugins/member.png" style="margin-bottom: 20px" height="667" width="375"></app-image>
                                <div class="title-txt">{{form.form.members?form.form.members:'队员'}}</div>
                                <div class="title-txt" style="top: 145px;font-weight: 400;font-size: 14px;color: #666;align-items: center" flex="main:center">
                                    <span>{{form.form.offer_text?form.form.offer_text:'贡献分红金额'}}</span>
                                    <img style="height: 13px;width: 8px;margin-left: 8px;" src="statics/img/plugins/shop-price-less.png" alt="">
                                </div>
                                <div class="offer-text">
                                    <div v-for="item in [1,2,3,4,5,6,7]">
                                        <span style="display: inline-block;height: 18px;padding: 0 5px;text-align: center;line-height: 18px;background-color: #efeff4">{{form.form.offer_text ? form.form.offer_text : '贡献分红金额'}}</span>
                                        <span style="color: #666;font-size: 12px;">￥0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if='active_setting == "7"' class="item-img">
                                <app-image mode="aspectFill" src="statics/img/plugins/become.png" style="margin-bottom: 20px" height="667" width="375"></app-image>
                                <div v-if="form.become_type == 3" class="become-txt">还差<span style="font-size: 19px;color: #ff8f17">XXX</span>个{{form.form.become_name?form.form.become_name:'下线'}}成为队长</div>
                                <div v-if="form.become_type == 4" class="become-txt">还差<span style="font-size: 19px;color: #ff8f17">XXX</span>个{{form.form.become_name?form.form.become_name:'下线分销商'}}成为队长</div>
                                <div v-if="form.become_type == 1" class="become-txt">还差<span style="font-size: 19px;color: #ff8f17">XXX</span>{{form.form.become_name?form.form.become_name:'累计佣金'}}成为队长</div>
                                <div v-if="form.become_type == 2" class="become-txt">还差<span style="font-size: 19px;color: #ff8f17">XXX</span>{{form.form.become_name?form.form.become_name:'已提现佣金'}}成为队长</div>
                                <div v-if="form.become_type == 3" class="become-about">已有{{form.form.become_name?form.form.become_name:'下线'}}XXX人</div>
                                <div v-if="form.become_type == 4" class="become-about">已有{{form.form.become_name?form.form.become_name:'下线分销商'}}XXX人</div>
                                <div v-if="form.become_type == 1" class="become-about">已有{{form.form.become_name?form.form.become_name:'累计佣金'}}￥XXX</div>
                                <div v-if="form.become_type == 2" class="become-about">已有{{form.form.become_name?form.form.become_name:'已提现佣金'}}￥XXX</div>
                            </div>
                            <div class="item-img" style="border: 0;width: 570px;margin-left: 20px;">
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 1">
                                    <div class="default">团队分红</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.entry_bonus" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 2">
                                    <div class="default">团队分红</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.title" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 3">
                                    <div class="default">提成比例</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.rate" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 3">
                                    <div class="default">累计分红金额</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.total_bonus" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 3">
                                    <div class="default">预计分红金额</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.expect_bonus" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 3">
                                    <div class="default">已提现分红</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.cashd_bonus" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 3">
                                    <div class="default">可提现分红</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.can_cash_bonus" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 4">
                                    <div class="default">分红统计</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.statistic_bonus" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 4">
                                    <div class="default">分红</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.statics_text" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 5">
                                    <div class="default">分红订单</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.orders" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 5">
                                    <div class="default">分红金额</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.price_text" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 6">
                                    <div class="default">队员</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.members" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 6">
                                    <div class="default">贡献分红金额</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.offer_text" class="text-input"></el-input>
                                </div>
                                <div style="height: 32px;margin-left: 40px;margin-bottom: 20px" flex="dir:left cross:center" v-if="active_setting == 7">
                                    <div v-if="form.become_type == 3" class="default">下线</div>
                                    <div v-if="form.become_type == 4" class="default">下线分销商</div>
                                    <div v-if="form.become_type == 1" class="default">累计佣金</div>
                                    <div v-if="form.become_type == 2" class="default">已提现佣金</div>
                                    <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                    <el-input style="width: 310px;margin-left: 25px;" size="small" v-model="form.form.become_name" class="text-input"></el-input>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-tab-pane>
            <el-button class="button-item" type="primary" size="small" :loading=submitLoading @click="submit">保存</el-button>
        </el-form>
    </el-card>
</div>  

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                bonusSwitch: 0,
                showTreaty: 0,
                pay_type: ['auto'],
                form: {
                    is_bonus: 0,
                    is_agreement: 0,
                    agreement_content: '',
                    bonus_rate: 0,
                    bg_url: 'statics/img/app/bonus/banner.png',
                    become_type: 0,
                    condition: 0,
                    min_money: 0,
                    free_cash_min: '',
                    free_cash_max: '',
                    cash_service_charge: 0,
                    pay_type: [],
                    form: {
                        entry_bonus: '',
                        title: '',
                        total_bonus: '',
                        expect_bonus: '',
                        cashd_bonus: '',
                        become_name: '',
                        statics_text: '',
                        price_text: '',
                        can_cash_bonus: '',
                        orders: '',
                        statistic_bonus: '',
                        bottom_bg_url: '',
                        members: '',
                        offer_text: ''
                    }
                },
                active_setting: '1',
                customize_pic: _baseUrl + '/statics/img/mall/customize_jp.png',
                bg_url: 'statics/img/app/bonus/banner.png',
                bottom_bg_url: 'statics/img/app/bonus/right.png',
                loading: false,
                activeName: 'first',
                submitLoading: false,
            };
        },
        methods: {
            topPicUrl(e) {
                this.form.bg_url = e[0].url;
                this.bg_url = e[0].url;
            },

            tabType() {
                this.form.form.become_name = '';
                if(this.form.become_type == 0) {
                    this.active_setting = 1;
                }
            },

            bottomPicUrl(e) {
                this.form.form.bottom_bg_url = e[0].url;
                this.bottom_bg_url = e[0].url;
            },

            chooseSetting(e) {
                this.active_setting = e;
            },

            getList() {
                let para = {
                    r: 'plugin/bonus/mall/setting',
                }
                request({
                    params: para,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if(e.data.data) {
                            if(e.data.data.list.is_bonus == 1) {
                                this.form = e.data.data.list;
                                this.bonusSwitch = this.form.is_bonus;    
                                this.showTreaty = this.form.is_agreement;
                                this.pay_type = this.form.pay_type;
                                this.bg_url = this.form.bg_url; 
                                this.bottom_bg_url = this.form.form.bottom_bg_url;
                                if(this.form.pay_type.length == 0) {
                                    this.pay_type = ['auto']
                                }
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },

            resetImg(res,position) {
                if(position == 'top') {
                    if(res == 2) {
                        this.bg_url = 'statics/img/app/bonus/banner.png';
                        this.form.bg_url = 'statics/img/app/bonus/banner.png';
                    }else {
                        this.bg_url = '';
                        this.form.bg_url = '';
                    }
                }else if(position == 'bottom') {
                    if(res == 2) {
                        this.bottom_bg_url = 'statics/img/app/bonus/right.png';
                        this.form.form.bottom_bg_url = 'statics/img/app/bonus/right.png';
                    }else {
                        this.bottom_bg_url = '';
                        this.form.form.bottom_bg_url = '';
                    }
                }
            },

            submit() {
                let that = this;
                that.submitLoading = true;
                let para = that.form;
                para.is_bonus = that.bonusSwitch;
                para.is_agreement = that.showTreaty;
                para.pay_type = [];
                if(that.pay_type.indexOf('balance') > -1) {
                    para.pay_type.push('balance')
                }
                if(that.pay_type.indexOf('bank') > -1) {
                    para.pay_type.push('bank')
                }
                if(that.pay_type.indexOf('alipay') > -1) {
                    para.pay_type.push('alipay')
                }
                if(that.pay_type.indexOf('wechat') > -1) {
                    para.pay_type.push('wechat')
                }
                if(that.pay_type.indexOf('auto') > -1 || that.pay_type.length == 0) {
                    para.pay_type.push('auto')
                }
                if(para.cash_service_charge == 0) {
                    para.free_cash_min = '';
                    para.free_cash_max = '';
                }
                request({
                    params: {
                        r: 'plugin/bonus/mall/setting'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    that.submitLoading = false;
                    if (e.data.code === 0) {
                        that.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        setTimeout(function(){
                            that.getList();
                        },500);
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
        },
        created() {
            this.loading = true;
            this.getList();
        }
    });
</script>
