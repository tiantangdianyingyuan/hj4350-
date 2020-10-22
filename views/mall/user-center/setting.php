<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>

<style>
    .mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        background-size: cover;
        position: relative;
        font-size: .85rem;
        float: left;
        margin-right: 1rem;
    }

    .mobile-box .show-box {
        height: 606px;
        width: 375px;
        overflow: auto;
        font-size: 12px;
    }

    .show-box::-webkit-scrollbar { /*滚动条整体样式*/
        width: 1px; /*高宽分别对应横竖滚动条的尺寸*/
    }

    .order-box {
        height: 80px;
        padding-top: 10px;
        border: 1px solid #eeeeee;
        margin-left: -1px;
        cursor: pointer;
        min-width: 60px;
    }

    .menus-box {
        border: 1px solid #eeeeee;
        background: #F6F8F9;
    }

    .menu-add {
        text-align: right;
        background: #ffffff;
        height: 40px;
        line-height: 40px;
        padding-right: 10px;
    }

    .top-box {
        width: 100%;
        height: 150px;
        background: #F5F7F9;
    }

    .top-box .top-style-1 {
        width: 100%;
        height: 100%;
    }

    .top-box .top-style-1 .head {
        width: 40px;
        height: 40px;
        -webkit-border-radius: 50%;
        -moz-border-radius: 50%;
        border-radius: 50%;
        border: 2px solid #ffffff;
        background: #E3E3E3;
        margin-left: 20px;
    }

    .top-box .top-style-2 {
        width: 100%;
        height: 100%;
    }

    .top-box .top-style-2 .head {
        width: 40px;
        height: 40px;
        -webkit-border-radius: 50%;
        -moz-border-radius: 50%;
        border-radius: 50%;
        border: 2px solid #ffffff;
        background: #E3E3E3;
    }

    .top-box .top-style-3 {
        width: 100%;
        height: 100%;
    }

    .top-box .top-style-3 .head {
        width: 40px;
        height: 40px;
        -webkit-border-radius: 50%;
        -moz-border-radius: 50%;
        border-radius: 50%;
        border: 2px solid #ffffff;
        background: #E3E3E3;
    }

    .top-box .top-style-3 .center-box {
        width: 81%;
        height: 120px;
        background: #ffffff;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        padding: 0 20px;
    }

    .account-box {
        width: 100%;
        height: 60px;
        background-color: #f7f7f7;
        padding: 0 8px 8px;
    }

    .account-box>div {
        background-color: #fff;
        border-radius: 4px;
        padding: 8px 0;
        height: 100%;
    }

    .order-bar-box {
        width: 100%;
        background-color: #f7f7f7;
        padding: 0 8px 1px;
        margin-bottom: 8px;
    }

    .order-bar-box>div {
        background-color: #fff;
        border-radius: 8px;
        height: 100%;
    }

    .mobile-menus-box {
        width: 100%;
        background-color: #f7f7f7;
        padding: 0 8px;
    }

    .mobile-menus-box>div {
        background-color: #fff;
        border-radius: 8px;
        height: 100%;
    }

    .mobile-menus-box .mobile-menu-title {
        padding: 10px 16px;
        font-size: 14px;
    }

    .menus-box .menu-item {
        cursor: move;
        background-color: #fff;
        margin: 5px 0;
    }

    .button-item {
        padding: 9px 25px;
        margin-left: 420px;
        margin-top: 10px;
    }

    .head-bar {
        width: 378px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .head-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 16px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .head-bar img {
        width: 378px;
        height: 64px;
    }

    .form-body {
        width: 100%;
        height: 740px;
        overflow-y: scroll;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .topic-style {
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .account-item {
        width: 25%;
        border: 1px solid #eeeeee;
    }

    .foot-box {
        position: relative;
        background-color: #f7f7f7;
    }

    .foot-box-line {
        position: absolute;
        height: 20px;
        width: 1px;
        background-color: #666666;
        top: 22px;
        left: 50%;
        margin-left: -1px;
    }

    .foot-box-item {
        height: 64px;
        color: #666666;
        font-size: 13px;
        width: 50%;
    }

    .foot-box-num {
        font-size: 16px;
        margin-bottom: 6px;
    }

    .foot-box-info {
        padding-top: 8px;
        margin-left: 8.5px;
        text-align: center;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>用户中心设置</span>
            </div>
        </div>
        <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
            <div style="display: flex;">
                <div class="mobile-box">
                    <div class="head-bar" flex="main:center cross:center">
                        <div>用户中心</div>
                    </div>
                    <div class="show-box">
                        <div class="top-box">
                            <div :style="{'background-image':'url('+ruleForm.top_pic_url+')'}"
                                 class="topic-style top-style-1"
                                 flex="dir:top main:center" v-if="ruleForm.top_style == 1">
                                <div flex="cross:center">
                                    <div class="head"></div>
                                    <span style="margin-left: 10px;color: #ffffff;">用户昵称</span>
                                </div>
                            </div>

                            <div :style="{'background-image':'url('+ruleForm.top_pic_url+')'}"
                                 class="topic-style top-style-2"
                                 flex="main:center cross:center dir:top"
                                 v-if="ruleForm.top_style == 2">
                                <div class="head"></div>
                                <span style="color: #ffffff">用户昵称</span>
                            </div>

                            <div :style="{'background-image':'url('+ruleForm.top_pic_url+')'}"
                                 class="topic-style top-style-3"
                                 flex="main:center cross:center"
                                 v-if="ruleForm.top_style == 3">
                                <div class="center-box"
                                     :style="{'background-image': 'url('+ruleForm.style_bg_pic_url+')'}"
                                     style="background-size: 100%;background-repeat:no-repeat;background-position: center"
                                     flex="dir:left cross:center">
                                    <div class="head"></div>
                                    <span style="color: #000000;margin-left: 10px;">用户昵称</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="ruleForm.is_foot_bar_status == 1" class="foot-box" flex="main:center cross:center">
                            <div flex="main:center" class="foot-box-item" v-for="item in ruleForm.foot_bar">
                                <app-image style="margin-top: 33px;" width="20px" height="20px" mode="aspectFill"
                                           :src="item.icon_url"></app-image>
                                <div class="foot-box-info">
                                    <div class="foot-box-num">0</div>
                                    <div>{{item.name}}</div>
                                </div>
                            </div>
                            <div class="foot-box-line"></div>
                        </div>
                        <div v-if="ruleForm.account_bar.status == 1" class="account-box">
                            <div flex="dir:left box:mean">
                                <!-- 积分 -->
                                <div style="padding: 5px 0;border-right: 1px solid #e2e2e2;"
                                     flex="main:center cross:center dir:top">
                                     <div style="color: #ffbb43;">0</div>
                                    <app-ellipsis style="margin-top: 5px;" :line="1">
                                        <app-image style="display: inline-block"
                                                   width="10px"
                                                   height="10px"
                                                   mode="aspectFill"
                                                   :src="ruleForm.account_bar && ruleForm.account_bar.integral ? ruleForm.account_bar.integral.icon : ''">
                                        </app-image>
                                        {{ruleForm.account_bar && ruleForm.account_bar.integral ?
                                        ruleForm.account_bar.integral.text : '积分'}}
                                    </app-ellipsis>
                                </div>
                                <!-- 余额 -->
                                <div style="padding: 5px 0;border-right: 1px solid #e2e2e2;"
                                     flex="main:center cross:center dir:top">
                                     <div style="color: #ffbb43;">0</div>
                                    <app-ellipsis style="margin-top: 5px;" :line="1">
                                        <app-image style="display: inline-block"
                                                   width="10px"
                                                   height="10px"
                                                   mode="aspectFill"
                                                   :src="ruleForm.account_bar && ruleForm.account_bar.balance ? ruleForm.account_bar.balance.icon : ''">
                                        </app-image>
                                        {{ruleForm.account_bar && ruleForm.account_bar.balance ?
                                        ruleForm.account_bar.balance.text : '余额'}}
                                    </app-ellipsis>
                                </div>
                                <!-- 优惠券 -->
                                <div style="padding: 5px 0;border-right: 1px solid #e2e2e2;"
                                     flex="main:center cross:center dir:top">
                                     <div style="color: #ffbb43;">0</div>
                                    <app-ellipsis style="margin-top: 5px;" :line="1">
                                        <app-image style="display: inline-block"
                                                   width="10px"
                                                   height="10px"
                                                   mode="aspectFill"
                                                   :src="ruleForm.account_bar && ruleForm.account_bar.coupon ? ruleForm.account_bar.coupon.icon : ''">
                                        </app-image>
                                        {{ruleForm.account_bar && ruleForm.account_bar.coupon ?
                                        ruleForm.account_bar.coupon.text : '优惠券'}}
                                    </app-ellipsis>
                                </div>
                                <!-- 卡券 -->
                                <div style="padding: 5px 0;" flex="main:center cross:center dir:top">
                                    <div style="color: #ffbb43;">0</div>
                                    <app-ellipsis style="margin-top: 5px;" :line="1">
                                        <app-image style="display: inline-block"
                                                   width="10px"
                                                   height="10px"
                                                   mode="aspectFill"
                                                   :src="ruleForm.account_bar && ruleForm.account_bar.card ? ruleForm.account_bar.card.icon : ''">
                                        </app-image>
                                        {{ruleForm.account_bar && ruleForm.account_bar.card ?
                                        ruleForm.account_bar.card.text : '卡券'}}
                                    </app-ellipsis>
                                </div>
                            </div>
                        </div>

                        <div v-if="ruleForm.is_order_bar_status == 1" class="order-bar-box">
                            <div>
                                <div style="padding: 10px;" flex="main:justify cross:center">
                                    <div>我的订单</div>
                                    <div style="color: #999999">查看更多></div>
                                </div>
                                <div flex="dir:left box:mean"
                                     style="margin: 10px 0;padding-bottom: 10px">
                                    <div v-for="item in ruleForm.order_bar" flex="main:center cross:center dir:top">
                                        <app-image width="30px"
                                                   height="30px"
                                                   mode="aspectFill"
                                                   :src="item.icon_url">
                                        </app-image>
                                        <app-ellipsis style="margin-top: 5px;" :line="1">{{item.name}}</app-ellipsis>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="ruleForm.is_menu_status == 1 && ruleForm.menu_style == 1"
                             class="mobile-menus-box">
                             <div flex="dir:top">
                                <div class="mobile-menu-title">{{ruleForm.menu_title}}</div>
                                <div style="padding: 8px 16px" v-for="item in ruleForm.menus"
                                     flex="dir:left cross:center">
                                    <app-image width="25px"
                                               height="25px"
                                               mode="aspectFill"
                                               :src="item.icon_url">
                                    </app-image>
                                    <app-ellipsis style="margin-left: 10px;" :line="1">{{item.name}}</app-ellipsis>
                                </div>
                             </div>
                        </div>

                        <div v-if="ruleForm.is_menu_status == 1 && ruleForm.menu_style == 2"
                             class="mobile-menus-box">
                            <div class="mobile-menu-title">{{ruleForm.menu_title}}</div>
                            <div flex="wrap:wrap">
                                <div v-for="item in ruleForm.menus"
                                     style="width: 25%;margin-bottom: 18px"
                                     flex="cross:center main:center dir:top">
                                    <app-image width="25px"
                                               height="25px"
                                               style="margin-bottom: 8px"
                                               mode="aspectFill"
                                               :src="item.icon_url">
                                    </app-image>
                                    <app-ellipsis :line="1">{{item.name}}</app-ellipsis>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-body">
                    <el-card shadow="never" style="margin-bottom: 20px;min-width:500px" body-style="padding-right:30%">
                        <div slot="header">
                            <span>头像栏设置</span>
                        </div>
                        <el-form-item label="背景图片" prop="top_pic_url">
                            <app-attachment :multiple="false" :max="1" @selected="topPicUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:750*300" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px"
                                       height="80px"
                                       mode="aspectFill"
                                       :src="ruleForm.top_pic_url">
                            </app-image>
                        </el-form-item>
                        <el-form-item label="普通用户图标" prop="member_pic_url">
                            <app-attachment :multiple="false" :max="1" @selected="memberPicUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:44*44" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px"
                                       height="80px"
                                       mode="aspectFill"
                                       :src="ruleForm.member_pic_url">
                            </app-image>
                        </el-form-item>
                        <el-form-item label="普通用户文字">
                            <el-input v-model="ruleForm.general_user_text"></el-input>
                        </el-form-item>
                        <el-form-item label="会员中心背景图" prop="member_bg_pic_url">
                            <app-attachment :multiple="false" :max="1" @selected="memberBgPicUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:660*320" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px"
                                       height="80px"
                                       mode="aspectFill"
                                       :src="ruleForm.member_bg_pic_url">
                            </app-image>
                        </el-form-item>
                        <el-form-item label="头像样式">
                            <el-radio v-model="ruleForm.top_style" label="1">头像靠左</el-radio>
                            <el-radio v-model="ruleForm.top_style" label="2">头像居中</el-radio>
                            <el-radio v-model="ruleForm.top_style" label="3">头像内嵌</el-radio>
                        </el-form-item>
                        <el-form-item label="头像内嵌背景图" v-if="ruleForm.top_style == 3">
                            <app-attachment :multiple="false" :max="1" v-model="ruleForm.style_bg_pic_url">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:656x220" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px"
                                       height="80px"
                                       mode="aspectFill"
                                       :src="ruleForm.style_bg_pic_url">
                            </app-image>
                        </el-form-item>
                    </el-card>
                    <el-card shadow="never" style="margin-bottom: 20px;min-width:500px" body-style="padding-right:30%">
                        <div slot="header">
                            <span>收藏足迹栏</span>
                        </div>
                        <el-form-item label="收藏足迹栏显示状态">
                            <el-switch
                                    v-model="ruleForm.is_foot_bar_status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.is_foot_bar_status == 1" label="收藏足迹栏">
                            <div flex="box:mean" style="flex-wrap: wrap">
                                <div style="max-width: 134.16px" v-for="(item, index) in ruleForm.foot_bar"
                                     @click="openDialogForm(item,index, 4)"
                                     class="order-box"
                                     flex="dir:top box:mean main:center cross:center">
                                    <div flex="cross:center">
                                        <app-image width="30px" height="30px" mode="aspectFill" :src="item.icon_url">
                                        </app-image>
                                    </div>
                                    <div>{{item.name}}</div>
                                </div>
                            </div>
                        </el-form-item>
                    </el-card>
                    <el-card shadow="never" style="margin-bottom: 20px;min-width:500px" body-style="padding-right:30%">
                        <div slot="header">
                            <span>订单栏设置</span>
                        </div>
                        <el-form-item label="订单栏显示状态">
                            <el-switch
                                    v-model="ruleForm.is_order_bar_status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.is_order_bar_status == 1" label="订单栏">
                            <div flex="box:mean" style="flex-wrap: wrap">
                                <div v-for="(item, index) in ruleForm.order_bar"
                                     @click="openDialogForm(item,index, 1)"
                                     class="order-box"
                                     flex="dir:top box:mean main:center cross:center">
                                    <div flex="cross:center">
                                        <app-image width="30px" height="30px" mode="aspectFill" :src="item.icon_url">
                                        </app-image>
                                    </div>
                                    <div>{{item.name}}</div>
                                </div>
                            </div>
                        </el-form-item>
                    </el-card>

                    <el-card shadow="never" style="margin-bottom: 20px;min-width:500px" body-style="padding-right:30%">
                        <div slot="header">
                            <span>账户栏设置</span>
                        </div>
                        <el-form-item label="显示状态">
                            <el-switch
                                    v-model="ruleForm.account_bar.status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.account_bar.status == 1" label="我的账户">
                            <div flex="box:mean">
                                <div v-for="(item, index) in ruleForm.account_bar"
                                     v-if="index != 'status'"
                                     @click="openDialogForm(item, index, 2)"
                                     class="order-box"
                                     flex="dir:top box:mean main:center cross:center">
                                    <div flex="cross:center">
                                        <app-image width="21px" height="21px" mode="aspectFill" :src="item.icon">
                                        </app-image>
                                    </div>
                                    <div>{{item.text}}</div>
                                </div>
                            </div>
                        </el-form-item>
                    </el-card>

                    <el-card shadow="never" style="margin-bottom: 20px;min-width:500px" body-style="padding-right:30%">
                        <div slot="header">
                            <span>菜单栏设置</span>
                        </div>
                        <el-form-item label="菜单栏显示状态">
                            <el-switch
                                    v-model="ruleForm.is_menu_status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <template v-if="ruleForm.is_menu_status == 1">
                            <el-form-item  label="菜单栏标题">
                                <el-input v-model="ruleForm.menu_title"></el-input>
                            </el-form-item>
                            <el-form-item label="菜单栏样式">
                                <el-radio v-model="ruleForm.menu_style" label="1">列表形式</el-radio>
                                <el-radio v-model="ruleForm.menu_style" label="2">九宫格形式</el-radio>
                            </el-form-item>
                            <el-form-item  label="菜单栏">
                                <div class="menus-box">
                                    <div class="menu-add">
                                        <app-pick-link type="multiple" @selected="selectLinkUrl">
                                            <el-button plain size="mini">添加</el-button>
                                        </app-pick-link>
                                    </div>
                                    <draggable v-model="ruleForm.menus">
                                        <div v-for="(item, index) in ruleForm.menus"
                                             flex="main:center cross:center box:justify"
                                             class="menu-item">
                                            <div style="margin: 0 10px;">
                                                <app-image width="25px" height="25px" mode="aspectFill"
                                                           :src="item.icon_url">
                                                </app-image>
                                            </div>
                                            <div>{{item.name}}</div>
                                            <div flex="dir-left" style="width: 94px;margin: 5px 0;">
                                                <el-button style="padding: 0" @click="openDialogForm(item,index,3)"
                                                           type="text" circle size="mini">
                                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                        <img src="statics/img/mall/edit.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                                <el-button style="padding: 0" @click="menuDestroy(index)" type="text" circle
                                                           size="mini">
                                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                        <img src="statics/img/mall/del.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                            </div>
                                        </div>
                                    </draggable>
                                </div>
                            </el-form-item>
                        </template>
                    </el-card>
                </div>
            </div>
            <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">
                保存
            </el-button>
            <el-button class="button-item" :loading="btnLoading" type="default" @click="reset" size="small">
                恢复默认
            </el-button>
        </el-form>


        <el-dialog :title="dialogFormType == 4 ? '收藏栏编辑': dialogFormType == 2 ? '我的账户编辑' : dialogFormType == 3 ? '菜单栏编辑' : '订单栏编辑'"
                   :visible.sync="dialogFormVisible">
            <el-form @submit.native.prevent :model="dialogForm" label-width="120px" size="small">
                <template v-if="dialogFormType ===  1 || dialogFormType === 4">
                    <el-form-item label="名称">
                        <el-tag type="info">{{dialogForm.name}}</el-tag>
                    </el-form-item>
                    <el-form-item label="图标" prop="icon_url">
                        <app-attachment :multiple="false" :max="1" @selected="iconUrl">
                            <el-tooltip v-if="dialogFormType === 1" class="item" effect="dark" content="建议尺寸:60*60"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                            <el-tooltip v-if="dialogFormType === 4" class="item" effect="dark" content="建议尺寸:40*40"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.icon_url">
                        </app-image>
                    </el-form-item>
                </template>

                <template v-if="dialogFormType === 2 || dialogFormType === 3">
                    <el-form-item label="名称">
                        <el-input v-model="dialogForm.name" placeholder="输入自定义名称" maxlength="4"
                                  v-if="dialogFormType === 2"></el-input>
                        <el-input v-model="dialogForm.name" placeholder="输入自定义名称"
                                  v-if="dialogFormType === 3"></el-input>
                    </el-form-item>
                    <el-form-item label="图标" prop="icon_url">
                        <app-attachment :multiple="false" :max="1" @selected="iconUrl">
                            <el-tooltip v-if="dialogFormType === 2 && dialogFormIndex == 0" class="item" effect="dark"
                                        content="建议尺寸:48*48" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                            <el-tooltip v-if="dialogFormType === 2 && dialogFormIndex != 0" class="item" effect="dark"
                                        content="建议尺寸:26*26" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                            <el-tooltip v-if="dialogFormType === 3" class="item" effect="dark" content="建议尺寸:50*50"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image width="80px"
                                   height="80px"
                                   mode="aspectFill"
                                   :src="dialogForm.icon_url">
                        </app-image>
                    </el-form-item>
                    <el-form-item style="margin-bottom: 0" v-for="item in dialogForm.params"
                                  :key="item.key" :prop="item.is_required ? 'key_name' : ''">
                        <template slot='label'>
                            <span>{{item.key}}</span>
                            <el-tooltip v-if="item.desc" effect="dark" :content="item.desc"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-input size="small" :type="item.data_type ? item.data_type : ''"
                                  v-model="item.value"
                                  @input="formatLinkUrl(dialogForm)"
                                  :placeholder="item.desc">
                        </el-input>
                        <span v-if="item.page_url">
                            所需数据请到“<el-button type="text" @click="$navigate({r:item.page_url}, true)">{{item.page_url_text}}</el-button>”查看
                        </span>
                    </el-form-item>
                </template>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogFormConfirm">确 定</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script src="//cdn.jsdelivr.net/npm/sortablejs@1.8.3/Sortable.min.js"></script>
<!-- CDNJS :: Vue.Draggable (https://cdnjs.com/) -->
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                mobile_bg: _baseUrl + '/statics/img/mall/mobile-background.png',
                ruleForm: {
                    top_pic_url: '',
                    top_style: '1',
                    is_order_bar_status: '1', // 订单栏显示
                    is_foot_bar_status: '1', // 订单栏显示
                    is_menu_status: '1',
                    menu_title: '我的服务',
                    menu_style: '1',
                    menus: [],
                    order_bar: [
                        {
                            id: 1,
                            name: '待付款',
                            icon_url: '',
                        },
                        {
                            id: 2,
                            name: '待发货',
                            icon_url: '',
                        },
                        {
                            id: 3,
                            name: '待收货',
                            icon_url: '',
                        },
                        {
                            id: 4,
                            name: '已完成',
                            icon_url: '',
                        },
                        {
                            id: 5,
                            name: '售后',
                            icon_url: '',
                        },
                    ],
                    foot_bar: [
                        {
                            id: 1,
                            name: '我的收藏',
                            icon_url: '',
                        },
                        {
                            id: 2,
                            name: '我的足迹',
                            icon_url: '',
                        }
                    ],
                    account: [
                        {
                            id: 2,
                            name: '积分',
                            icon_url: '',
                        },
                        {
                            id: 3,
                            name: '余额',
                            icon_url: '',
                        },
                    ],
                    account_bar: {
                        status: '1',
                        integral: {
                            status: '1',
                            text: '积分',
                            icon: '',
                        },
                        balance: {
                            status: '1',
                            text: '余额',
                            icon: '',
                        },
                        coupon: {
                            status: '1',
                            text: '优惠券',
                            icon: '',
                        },
                        card: {
                            status: '1',
                            text: '卡券',
                            icon: '',
                        },
                    },
                },
                rules: {
                    top_pic_url: [
                        {required: true, message: '请选择顶部背景图片', trigger: 'change'},
                    ],
                    member_pic_url: [
                        {required: true, message: '请选择会员图标', trigger: 'change'},
                    ],
                    member_bg_pic_url: [
                        {required: true, message: '请选择普通会员背景图', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                dialogForm: {},
                dialogFormVisible: false,
                dialogFormType: '',
                dialogFormIndex: '',
            };
        },
        methods: {
            formatLinkUrl(cItem) {
                let params = '';
                cItem.params.forEach(function (pItem, pIndex) {
                    if (!pItem.value && pItem.is_required === true) {
                        sign = false;
                        self.$message.error(cItem.name + '->' + pItem.desc)
                    }

                    let value = pItem['value'];
                    if (pItem['key'] === 'url') {
                        value = encodeURIComponent(value);
                    }
                    params += pItem['key'] + '=' + value + '&';
                });
                params = params.substr(0, params.length - 1);

                let matches = cItem.link_url.match(/([\s|\S])*\?/);
                if (matches) {
                    cItem.link_url = matches[0] + params;
                } else {
                    cItem.link_url = params;
                }
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/user-center/setting'
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/user-center/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.detail) {
                            self.ruleForm = e.data.data.detail;
                            // if(!self.ruleForm.foot_bar) {
                            //     self.ruleForm.foot_bar = [
                            //         {
                            //             id: 1,
                            //             name: '我的收藏',
                            //             icon_url: '',
                            //         },
                            //         {
                            //             id: 2,
                            //             name: '我的足迹',
                            //             icon_url: '',
                            //         }
                            //     ]
                            // }
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            topPicUrl(e) {
                if (e.length) {
                    this.ruleForm.top_pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('top_pic_url');
                }
            },
            memberPicUrl(e) {
                if (e.length) {
                    this.ruleForm.member_pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('member_pic_url');
                }
            },
            memberBgPicUrl(e) {
                if (e.length) {
                    this.ruleForm.member_bg_pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('member_bg_pic_url');
                }
            },
            // 订单图标
            iconUrl(e, params) {
                if (e.length) {
                    this.dialogForm.icon_url = e[0].url;
                }
            },
            // 添加链接
            selectLinkUrl(e) {
                let self = this;
                e.forEach(function (item, index) {
                    let obj = {
                        icon_url: item.icon,
                        name: item.name,
                        link_url: item.new_link_url,
                        open_type: item.open_type,
                        params: item.params ? item.params : []
                    }
                    if (item.key) {
                        obj.key = item.key
                    }
                    self.ruleForm.menus.push(obj);
                })
            },
            // 删除链接
            menuDestroy(index) {
                this.ruleForm.menus.splice(index, 1);
            },
            // 编辑
            openDialogForm(item, index, type) {
                let self = this;
                self.dialogFormVisible = true;
                self.dialogFormType = type;
                self.dialogFormIndex = index;
                let dialogForm = JSON.parse(JSON.stringify(item));
                if (type == 2) {
                    dialogForm.name = item.text;
                    dialogForm.icon_url = item.icon
                }
                this.dialogForm = dialogForm;
            },
            dialogFormConfirm() {
                if (this.dialogFormType == 3 && this.dialogForm && this.dialogForm.open_type === 'tel' && this.dialogForm.params.length) {
                    let value = this.dialogForm.params[0].value;
                    let sentinel = /(^1\d{10}$)|(^([0-9]{3,4}-)?\d{7,8}$)|(^400[0-9]{7}$)|(^800[0-9]{7}$)|(^(400)-(\d{3})-(\d{4})(.)(\d{1,4})$)|(^(400)-(\d{3})-(\d{4}$))/.test(value);
                    if (!sentinel) {
                        this.$message({
                            message: '请填写有效的联系电话或手机',
                            type: 'error'
                        });
                        return;
                    }
                }
                this.dialogFormVisible = false;
                if (this.dialogFormType == 1) {
                    this.ruleForm.order_bar[this.dialogFormIndex] = this.dialogForm;
                }
                if (this.dialogFormType == 2) {
                    this.ruleForm.account_bar[this.dialogFormIndex].text = this.dialogForm.name;
                    this.ruleForm.account_bar[this.dialogFormIndex].icon = this.dialogForm.icon_url;
                }
                if (this.dialogFormType == 3) {
                    this.ruleForm.menus[this.dialogFormIndex] = this.dialogForm;
                }
                if (this.dialogFormType == 4) {
                    this.ruleForm.foot_bar[this.dialogFormIndex] = this.dialogForm;
                }
            },
            reset() {
                this.btnLoading = true;
                this.$confirm('确认恢复默认设置？').then(() => {
                    this.$request({
                        params: {
                            r: 'mall/user-center/reset-default'
                        }
                    }).then(response => {
                        this.btnLoading = false;
                        if (response.data.code == 0) {
                            this.$message.success('恢复成功');
                            this.getDetail();
                        }
                    });
                }).catch(() => {
                    this.btnLoading = false;
                })
            },
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
