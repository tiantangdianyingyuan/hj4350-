<style>
    .nav-box {
        width: 220px;
        height: 45px;
        line-height: 45px;
        border: 1px solid #000000;
        text-align: center;
    }

    .bottom-icon {
        width: 80px;
        height: 80px;
        margin-right: 10px;
        border: 1px solid #eeeeee;
        cursor: pointer;
    }

    .nav-icon {
        width: 30px;
        height: 30px;
    }

    .nav-add {
        border: 1px dashed #eeeeee;
    }

    .nav-add-icon {
        font-size: 50px;
        color: #eeeeee;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .button-item {
        padding: 9px 25px;
    }

    .mobile {
        width: 404px;
        height: 736px;
        border-radius: 30px;
        background-color: #fff;
        padding: 33px 12px;
        margin-right: 10px;
    }

    .screen {
        border: 2px solid #F3F5F6;
        height: 670px;
        width: 379px;
        margin: 0 auto;
        position: relative;
        background-color: #F7F7F7;
    }

    .screen .head {
        position: absolute;
        top: 0;
        left: 0;
        background: #FFFFFF;
        width: 376px;
        height: 60px;
        line-height: 60px;
        font-size: 18px;
        font-weight: bolder;
        text-align: center;
    }


    .screen .content {
        position: absolute;
        top: 60px;
        bottom: 0;
        width: 100%;
    }

    .screen .foot {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 375px;
        padding: 5px 25px;
        height: 45px;
        display: flex;
        text-align: center;
        justify-content: space-between;
        font-size: 11px;
    }

    .screen .foot .nav-icon {
        height: 20px;
        width: 20px;
    }

    .screen .foot .nav-icon + div {
        margin-top: -10px;
    }


    .title {
        padding: 18px 20px;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .content-block.head-block {
        padding: 10px 12px;
        background-color: #ff4544;
    }

    .content-block.menu-block {
        background-color: #fff;
    }

    .text-center {
        text-align: center;
    }

    .share-text {
        margin: 16px 0;
    }

    .share-text .default {
        width: 10%;
        min-width: 100px;
    }

    .text-input {
        margin-left: 2%;
        width: 35%;
    }
    .top-bar {
        width: 375px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .top-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 16px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .top-bar img {
        width: 378px;
        height: 64px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .button-item {
        padding: 9px 25px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px !important;
    }

    .apply-after:before {
        content: '*';
        color: #ff4544;
    }

    .apply-form {
        background: #FFFFFF;
        height: 44px;
        padding: 0 14px
    }

    .apply-form-info {
        color: #353535;
        height: 100%;
        border-bottom: 1px solid #e2e2e2
    }
    .circle {
        height:13px;
        width: 13px;
        border-radius: 50%;
        border:1px solid #e2e2e2;
    }
    .apply-btn {
        height: 40px;
        line-height: 40px;
        margin: 12px;
        background: #ff4544;
        color: #ffffff;
        border-radius: 20px;
        text-align: center;
    }

    .el-scrollbar__wrap {
        overflow-x: hidden;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;" v-loading="listLoading">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="分销申请" name="five">
                <div style="display: flex;">
                    <div class="mobile">
                        <div class="screen">
                            <div class="top-bar" flex="main:center cross:center">
                                <div>{{apply.share_apply.name}}</div>
                            </div>
                            <div class="content">
                                <el-scrollbar style="height:100%">
                                    <div v-if="apply">
                                        <el-image style="display: block;width: 100%;height: 150px;"
                                                  :src="apply.apply_head_pic"></el-image>
                                        <div style="background:#FFFFFF;height:40px;padding:0 14px">
                                            <div flex="cross:center"
                                                 style="color:#353535;height: 100%;border-bottom:1px solid #e2e2e2">
                                                <span>欢迎加入</span>
                                                <span style="color:#ff4544">XX商城</span>
                                                <span>，请填写申请信息</span>
                                            </div>
                                        </div>
                                        <div class="apply-form">
                                            <div flex="dir:left cross:center" class="apply-form-info">
                                                <div style="width: 100px">邀请人</div>
                                                <div style="color:#ff4544">雪人</div>
                                                <div>(请核对)</div>
                                            </div>
                                        </div>
                                        <div class="apply-form">
                                            <div flex="dir:left cross:center" class="apply-form-info">
                                                <div class="apply-after" style="width: 100px">姓名</div>
                                                <div style="color:#CCCCCC">请填写真实姓名</div>
                                            </div>
                                        </div>
                                        <div class="apply-form">
                                            <div flex="dir:left cross:center" class="apply-form-info">
                                                <div class="apply-after" style="width: 100px">手机号码</div>
                                                <div style="color:#CCCCCC">请填写手机号码</div>
                                            </div>
                                        </div>
                                        <div class="apply-form">
                                            <div flex="dir:left cross:center" class="apply-form-info"
                                                 style="border-style:none">
                                                <div class="circle"></div>
                                                <div style="margin-left:10px">我已阅读并同意</div>
                                                <div style="color:#4770b1">《{{apply.share_apply_pact.name}}》</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="apply-btn"
                                         :style="{'border-radius':apply.apply_btn_round / 2 + 'px',background: apply.apply_btn_background,color:apply.apply_btn_color}">
                                        {{apply.apply_btn_title}}
                                    </div>
                                    <el-image style="display: block;width: 100%" :src="apply.apply_end_pic"></el-image>
                                </el-scrollbar>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div class="title">图片</div>
                        <el-row class='form-body'>
                            <el-form :model="form" label-width="100px">
                                <el-form-item label="头部图片" prop="apply_head_pic">
                                    <div style="margin-bottom:10px;">
                                        <app-attachment style="display:inline-block;margin-right: 10px"
                                                        :multiple="false"
                                                        :max="1" @selected="applyHeadSelect">
                                            <el-tooltip effect="dark" content="建议尺寸:750 * 300" placement="top">
                                                <el-button size="mini">选择文件</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <el-button type="primary" @click="applyHeadDefault" size="mini">恢复默认
                                        </el-button>
                                    </div>
                                    <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                        <app-attachment :multiple="false" :max="1" @selected="applyHeadSelect">
                                            <app-image mode="aspectFill" width="80px" height='80px'
                                                       :src="apply.apply_head_pic"></app-image>
                                        </app-attachment>
                                        <el-button v-if="apply.apply_head_pic" class="del-btn" size="mini"
                                                   type="danger" icon="el-icon-close" circle
                                                   @click="applyHeadClose"></el-button>
                                    </div>
                                </el-form-item>

                                <el-form-item label="底部图片" prop="apply_end_pic">
                                    <div style="margin-bottom:10px;">
                                        <app-attachment style="display:inline-block;margin-right: 10px"
                                                        :multiple="false"
                                                        :max="1" @selected="applyEndSelect">
                                            <el-tooltip effect="dark" content="建议尺寸:750 * ∞" placement="top">
                                                <el-button size="mini">选择文件</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <el-button type="primary" @click="applyEndDefault" size="mini">恢复默认
                                        </el-button>
                                    </div>
                                    <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                        <app-attachment :multiple="false" :max="1" @selected="applyEndSelect">
                                            <app-image mode="aspectFill" width="80px" height='80px'
                                                       :src="apply.apply_end_pic"></app-image>
                                        </app-attachment>
                                        <el-button v-if="apply.apply_end_pic" class="del-btn" size="mini"
                                                   type="danger" icon="el-icon-close" circle
                                                   @click="applyEndClose"></el-button>
                                    </div>
                                </el-form-item>
                            </el-form>
                        </el-row>
                        <div style="width: 100%;">
                            <div class="title">文字</div>
                            <div class='form-body'>
                                <el-form :model="form" label-width="50px">
                                    <el-form-item prop="level">
                                        <div v-if="apply.share_apply" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{apply.share_apply.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill"
                                                       :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="apply.share_apply.name"></el-input>
                                        </div>
                                        <div v-if="apply.share_apply_pact" flex="dir:left cross:center"
                                             class="share-text">
                                            <div class="default">{{apply.share_apply_pact.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill"
                                                       :src="customize_pic"></app-image>
                                            <el-input class="text-input"
                                                      v-model="apply.share_apply_pact.name"></el-input>
                                        </div>
                                    </el-form-item>
                                </el-form>
                            </div>
                        </div>
                        <div style="width: 100%;">
                            <div class="title">按钮</div>
                            <div class='form-body'>
                                <el-form :model="form" label-width="100px">
                                    <el-form-item label="按钮圆角" prop="apply_btn_round">
                                        <div flex="dir:left">
                                            <el-slider style="width: 50%;margin-right: 20px" input-size="mini"
                                                       v-model="apply.apply_btn_round"
                                                       @input="sliderInput" max="40" :min="0"
                                                       :show-tooltip="false"></el-slider>
                                            <el-input-number v-model="apply.apply_btn_round" :min="0"
                                                             :max="40" label="描述文字"></el-input-number>
                                            <div style="margin-left: 10px">px</div>
                                        </div>
                                    </el-form-item>
                                    <el-form-item label="按钮文本" prop="apply_btn_title">
                                        <el-input class="text-input" v-model="apply.apply_btn_title"></el-input>
                                    </el-form-item>
                                    <div flex="dir:left">
                                        <el-form-item label="填充颜色" prop="apply_btn_background">
                                            <div flex="dir:left cross:center">
                                                <el-color-picker v-model="apply.apply_btn_background"
                                                                 size="small"></el-color-picker>
                                                <el-input size="small" class="text-input" style="width: 50%"
                                                          v-model="apply.apply_btn_background"></el-input>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="文本颜色" prop="level">
                                            <div flex="dir:left cross:center">
                                                <el-color-picker v-model="apply.apply_btn_color"
                                                                 size="small"></el-color-picker>
                                                <el-input size="small" class="text-input" style="width: 50%"
                                                          v-model="apply.apply_btn_color"></el-input>
                                            </div>
                                        </el-form-item>
                                    </div>
                                </el-form>
                            </div>
                        </div>
                        <el-button :loading="btnLoading" type="primary" class="button-item" @click="onSubmit">保存
                        </el-button>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="分销中心" name="first">
                <div style="display: flex;">
                    <div class="mobile">
                        <div class="screen">
                            <div class="top-bar" flex="main:center cross:center">
                                <div>分销中心</div>
                            </div>
                            <div class="content">
                                <div v-if="words">
                                    <div class="content-block head-block">
                                        <div flex="dir:left cross:center" style="padding-bottom:10px;border-bottom:1px solid #FFFFFF">
                                            <div>
                                                <app-image style="display: inline-block;border: 2px solid #fff;background: #e3e3e3;width: 60px;height: 60px;border-radius: 999px" :src="avatar_url"></app-image>
                                            </div>
                                            <div style="margin-left: 20px;color: #fff;">
                                                <div>用户昵称</div>
                                                <div style="margin-top:10px">{{words.parent_name.name}}：用户昵称</div>
                                            </div>
                                        </div>
                                        <div flex="dir:left cross:center" style="margin-top:10px;color: #fff;justify-content:space-between">
                                            <div>
                                                <div>{{words.can_be_presented.name}}</div>
                                                <div>10元</div>
                                            </div>
                                            <div style="border-radius:28px;height:28px;padding: 0 10px;border: 1px solid #FFFFFF;line-height: 28px;text-align:center">
                                                {{words.cash.name}}
                                            </div>
                                        </div>
                                    </div>
                                    <div flex="dir:left box:mean cross:center" style="background-color: #fff;margin-bottom: 8px;height:80px">
                                        <div class="text-center" style="border-right:1px solid #e2e2e2">
                                            <div style="color: #22af19;">{{words.already_presented.name}}</div>
                                            <div>1000元</div>
                                        </div>
                                        <div class="text-center">
                                            <div style="color: #ff8f12;">{{words.order_money_un.name}}</div>
                                            <div>1000元</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="content-block menu-block" v-if="menus">
                                    <div flex="dir:left cross:center" style="flex-wrap:wrap;">
                                        <div class="text-center" flex="dir:top cross:center main:center" style="border-right:1px solid #e2e2e2;border-bottom:1px solid #e2e2e2;width: 125px;height:110px;" v-for="(item,index) in menus">
                                            <img :src="item.icon" style="width: 35px;height: 35px">
                                            <div style="transform: scale(0.8);font-size:16px;padding-top:8px;color:#666666">
                                                {{item.name}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div class="title">栏目</div>
                        <el-row class='form-body'>
                            <div style="border-radius:3px;width:70%;margin-left:2.5%" flex="dir-left">
                                <span v-for="(item, index) in menus" @click="menusEdit(item,index)"
                                              flex="dir:top main:center cross:center"
                                              style="cursor:pointer;height:100px;width:100px;border:1px solid rgb(227, 227, 227);margin-left: -1px;">
                                    <app-image width="50px" height="50px" mode="aspectFill"
                                               :src="item.icon"></app-image>
                                    <div style="margin-top: 8px">{{item.name}}</div>
                                </span>
                            </div>
                        </el-row>
                        <div style="width: 100%;">
                            <div class="title">文字</div>
                            <div class='form-body'>
                                <el-form :model="form" label-width="50px">
                                    <el-form-item prop="level">
                                        <div v-if="words.parent_name" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{words.parent_name.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="words.parent_name.name"></el-input>
                                        </div>
                                        <div v-if="words.can_be_presented" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{words.can_be_presented.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="words.can_be_presented.name"></el-input>
                                        </div>
                                        <div v-if="words.cash" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{words.cash.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="words.cash.name"></el-input>
                                        </div>
                                        <div v-if="words.already_presented" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{words.already_presented.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="words.already_presented.name"></el-input>
                                        </div>
                                        <div v-if="words.order_money_un" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{words.order_money_un.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="words.order_money_un.name"></el-input>
                                        </div>
                                        <div v-if="words.share_name" flex="dir:left cross:center" class="share-text">
                                            <div class="default">{{words.share_name.default}}</div>
                                            <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                            <el-input class="text-input" v-model="words.share_name.name"></el-input>
                                        </div>
                                    </el-form-item>
                                </el-form>
                            </div>
                            <el-button :loading="btnLoading" type="primary" class="button-item" @click="onSubmit">保存</el-button>
                        </div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="分销佣金" name="two">
                <div style="display: flex;">
                    <div class="mobile">
                        <div class="screen">
                            <div class="top-bar" flex="main:center cross:center">
                                <div>分销佣金</div>
                            </div>
                            <div class="content" style="font-size:13pt">
                                <div class="content-block head-block" style="padding: 0 12px;height:73px">
                                    <div flex="dir:left box:left cross:center" style="height:100%;color: #fff;justify-content:space-between">
                                        <div>
                                            <div>分销佣金</div>
                                            <div style="padding-top:10px;">3500元</div>
                                        </div>
                                        <div style="border-radius:28px;border: 1px solid #FFFFFF;height:28px;width:84px;line-height:28px;text-align:center">
                                            提现明细
                                        </div>
                                    </div>
                                </div>
                                <div flex="dir:left cross:center" style="margin:10px 0;padding:0 12px;height:48px;background:#FFFFFF">
                                    <div v-text="words.can_be_presented.name"></div>
                                    <div style="margin-left: auto;">0元</div>
                                </div>
                                <div flex="dir:left cross:center" style="margin-bottom:2px;padding:0 12px;height:48px;background:#FFFFFF">
                                    <div v-text="words.already_presented.name"></div>
                                    <div style="margin-left: auto;">0元</div>
                                </div>
                                <div flex="dir:left cross:center" style="padding:0 12px;height:48px;background:#FFFFFF">
                                    <div v-text="words.pending_money.name"></div>
                                    <div style="margin-left: auto;">0元</div>
                                </div>
                                <div flex="dir:left cross:center" style="margin:10px 0;padding:0 12px;height:48px;background:#FFFFFF">
                                    <div v-text="words.user_instructions.name"></div>
                                    <app-image style="margin-left: auto;" width="8px" height="13px" mode="aspectFill" :src="customize_pic"></app-image>
                                </div>
                                <div flex="main:center" style="margin:20px auto;color:#fff;">
                                    <div v-text="words.apply_cash.name" flex="main:center" style="background-color:#ff4544;width:351px;border-radius:40px;height:40px;line-height:40px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div class="title">文字</div>
                        <div class='form-body'>
                            <el-form :model="form" label-width="50px">
                                <el-form-item prop="level">
                                    <div v-if="words.pending_money" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.pending_money.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.pending_money.name"></el-input>
                                    </div>
                                    <div v-if="words.user_instructions" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.user_instructions.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.user_instructions.name"></el-input>
                                    </div>
                                    <div v-if="words.apply_cash" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.apply_cash.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.apply_cash.name"></el-input>
                                    </div>
                                </el-form-item>
                            </el-form>
                        </div>
                        <el-button :loading="btnLoading" type="primary" class="button-item" @click="onSubmit">保存</el-button>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="申请提现" name="three">
                <div style="display: flex;">
                    <div class="mobile">
                        <div class="screen">
                            <div class="top-bar" flex="main:center cross:center">
                                <div>申请提现</div>
                            </div>
                            <div class="content" style="font-size:13pt">
                                <div>
                                    <div flex="dir:top main:center" style="border-top:1px solid #e2e2e2;color:#353535;padding:0 8px;height:80px;background:#FFFFFF">
                                        <div style="font-size:15pt;margin-bottom:8px">账户剩余金额：￥13</div>
                                        <div flex="dir:left cross:center" style="color:#999999">
                                            <div>今日剩余提现金额: ￥1000</div>
                                            <div style="font-size:11pt;border-radius:17px;padding:1px 5px;border:1px solid #999999;margin-left:8px">
                                                规则
                                            </div>
                                        </div>
                                    </div>
                                    <div flex="dir:left cross:center" style="background:#FFFFFF;color:#666;height:80px;padding:0 12px;font-size:13pt;border-top:1px solid #e2e2e2;border-bottom:1px solid #e2e2e2">
                                        <div style="color:#ff4544;font-size:30pt">￥</div>
                                        <div style="padding-left:20px;color: #cdcdcd;font-size:19pt">
                                            请输入{{words.cash_money.name}}
                                        </div>
                                    </div>
                                    <div style="padding:10px 12px;color:#666;font-size:12pt" flex="dir:top main:center">
                                        <div>说明： {{words.cash_money.name}}必须不能大于￥80</div>
                                        <!--<div>{{words.cash_money.name}}不能大于￥80</div>-->
                                        <!--<div>{{words.cash.name}}需要加收100%的手续费</div>-->
                                    </div>
                                    <div v-text="words.cash_type.name" style="background:#FFFFFF;padding:16px 12px"></div>
                                    <div flex="dir:left cross:center" style="margin-bottom:10px;padding-left:8px;padding-bottom:10px;background:#FFFFFF">
                                        <div flex="dir:left cross:center" style="margin-left:10px;padding:0 16px;height:34px;border:1px solid #ff4544;border-radius:19px">
                                            <app-image style="height: 20px;width:20px;margin-right:8px" :src="wxapp_bg"></app-image>
                                            <div>微信</div>
                                        </div>
                                        <div flex="dir:left cross:center" style="margin-left:10px;padding:0 16px;height:34px;border:1px solid #e2e2e2;border-radius:19px">
                                            <app-image style="height:20px;width:20px;margin-right:8px" :src="alipay_bg"></app-image>
                                            <div>支付宝</div>
                                        </div>
                                    </div>
                                    <div flex="dir:left cross:center" style="background:#FFFFFF;height:44px;padding:0 12px">
                                        <div style="min-width:84px">微信号</div>
                                        <div style="color:#cdcdcd">请填写正确的微信号</div>
                                    </div>
                                    <div flex="dir:left cross:center" style="background:#FFFFFF;height:44px;padding:0 12px">
                                        <div style="min-width:84px">账号</div>
                                        <div style="color:#cdcdcd">请输入正确的账号</div>
                                    </div>
                                    <div flex="main:center" style="margin:20px auto;color:#fff;">
                                        <div flex="main:center" style="background-color:#ff4544;width:351px;border-radius:40px;height:40px;line-height:40px;">
                                            提交申请
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div class="title">文字</div>
                        <div class='form-body'>
                            <el-form :model="form" label-width="50px">
                                <el-form-item prop="level">
                                    <div v-if="words.cash_money" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.cash_money.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.cash_money.name"></el-input>
                                    </div>
                                    <div v-if="words.cash_type" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.cash_type.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.cash_type.name"></el-input>
                                    </div>
                                </el-form-item>
                            </el-form>
                        </div>
                        <el-button :loading="btnLoading" type="primary" class="button-item" @click="onSubmit">保存</el-button>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="分销商" name="four">
                <div style="display: flex;">
                    <div class="mobile">
                        <div class="screen">
                            <div class="top-bar" flex="main:center cross:center">
                                <div>我的团队</div>
                            </div>
                            <div class="content">
                                <div flex="dir:left box:mean cross:center" style="height:50px;border:1px solid #e2e2e2;background:#FFFFFF">
                                    <div flex="main:center">
                                        <div flex="cross:center" style="height:50px;border-bottom:1px solid #ff4544;color:#ff4544">
                                            {{words.one_share.name}}(0)
                                        </div>
                                    </div>
                                    <div flex="main:center">
                                        <div>{{words.second_share.name}}(10)</div>
                                    </div>
                                    <div flex="main:center">
                                        <div>{{words.three_share.name}}(0)</div>
                                    </div>
                                </div>
                                <div v-for="v in [1,2]" flex="dir:top" style="margin-bottom:10px">
                                    <div flex="dir:left cross:center" style="background:#FFFFFF;padding:10px;height:80px;">
                                        <app-image style="height:50px;width:50px;" :src="goods_pic"></app-image>
                                        <div flex="dir:top main:center" style="width:100%;margin-left:12px;">
                                            <div flex="dir:left cross:center">
                                                <div style="font-size:13pt;">张三</div>
                                                <div style="margin-left:auto">推广20人</div>
                                            </div>
                                            <div style="color:#666666;padding-top:8px;">注册时间：2012-01-12</div>
                                        </div>
                                    </div>
                                    <div flex="dir:left cross:center" style="color:#666666;background:#FFFFFF;border-top:1px solid #e2e2e2;padding:0 12px;height:40px">
                                        <div>消费200.00元</div>
                                        <div style="margin-left:auto">2个订单</div>
                                    </div>
                                </div>
                                <div flex="dir:left main:center cross:center" style="margin:16px 0;color:#999999">
                                    <div flex="cross:center">
                                        <div style="height:1px;width:84px;background:#999999"></div>
                                        <div style="margin:0 10px">没有更多了</div>
                                        <div style="height:1px;width:84px;background:#999999"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div class="title">文字</div>
                        <div class='form-body'>
                            <el-form :model="form" label-width="50px">
                                <el-form-item prop="level">
                                    <div v-if="words.one_share" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.one_share.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.one_share.name"></el-input>
                                    </div>
                                    <div v-if="words.second_share" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.second_share.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.second_share.name"></el-input>
                                    </div>
                                    <div v-if="words.three_share" flex="dir:left cross:center" class="share-text">
                                        <div class="default">{{words.three_share.default}}</div>
                                        <app-image width="12px" height="12px" mode="aspectFill" :src="customize_pic"></app-image>
                                        <el-input class="text-input" v-model="words.three_share.name"></el-input>
                                    </div>
                                </el-form-item>
                            </el-form>
                        </div>
                        <el-button :loading="btnLoading" type="primary" class="button-item" @click="onSubmit">保存</el-button>
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
        <!-- 栏目编辑 -->
        <el-dialog title="栏目编辑" :visible.sync="dialogMenus" width="30%">
            <el-form @submit.native.prevent :model="menusForm" label-width="80px" :rules="menusFormRules" ref="menusForm">
                <el-form-item label="名称" prop="name">
                    <el-input v-model="menusForm.name"></el-input>
                </el-form-item>
                <el-form-item label="图标" prop="icon">
                    <app-attachment :multiple="false" :max="1" @selected="menusPicUrl">
                        <el-tooltip class="item" effect="dark" content="建议尺寸: 60*60" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image width="80px" height="80px" mode="aspectFill" :src="menusForm.icon"></app-image>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogMenus = false">取消</el-button>
                <el-button size="small" type="primary" @click="menusSubmit">提交</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: {},
            activeName: 'five',
            menus: {},
            apply: {},
            words: {
                'parent_name': {},
                'already_presented': {},
                'apply_cash': {},
                'can_be_presented': {},
                'cash': {},
                'cash_money': {},
                'cash_type': {},
                'one_share': {},
                'order_money_un': {},
                'parent_name': {},
                'pending_money': {},
                'second_share': {},
                'share_name': {},
                'three_share': {},
                'user_instructions': {},
            },
            listLoading: false,
            btnLoading: false,
            avatar_url: 'https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=480194109,2955193021&fm=27&gp=0.jpg',
            customize_pic: _baseUrl + '/statics/img/mall/customize_jp.png',
            goods_pic: 'https://ss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=3740798461,1728086832&fm=27&gp=0.jpg',
            wxapp_bg: _baseUrl + '/statics/img/mall/icon-share-wechat.png',
            alipay_bg: _baseUrl + '/statics/img/mall/icon-share-ant.png',
            //栏目编辑
            menusForm: {},
            dialogMenus: false,
            menusFormRules: {
                name: [
                    { required: true, message: '名称不能为空', trigger: 'blur' },
                ],
                icon: [
                    { required: true, message: '图标不能为空', trigger: 'blur' },
                ],
            },
            //文字修改
            wordsForm: [],
            dialogWords: false,
            wordsFormRules: {
                name: [
                    { required: true, message: '名称不能为空', trigger: 'blur' },
                ]
            },
        };
    },
    methods: {
        //分销申请
        sliderInput(e) {
            this.apply.apply_btn_round = e;
        },
        applyHeadSelect(e) {
            if (e.length) {
                this.apply.apply_head_pic = e[0].url;
            }
        },
        applyHeadDefault() {
            this.apply.apply_head_pic = "<?php echo \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;?>/statics/img/app/share/img-share-apply.png";
        },
        applyHeadClose() {
            this.apply.apply_head_pic = '';
        },

        applyEndSelect(e) {
            if (e.length) {
                this.apply.apply_end_pic = e[0].url;
            }
        },
        applyEndDefault() {
            this.apply.apply_end_pic = "<?php echo \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl ?>/statics/img/app/share/apply-end-pic.png";
        },
        applyEndClose() {
            this.apply.apply_end_pic = '';
        },
        //栏目编辑
        menusEdit(row, index) {
            this.menusForm = Object.assign({ index: index }, row);
            this.dialogMenus = true;
        },

        menusPicUrl(e) {
            if (e.length) {
                this.menusForm.icon = e[0].url;
            }
        },

        menusSubmit() {
            let index = this.menusForm.index;
            this.menus[index] = this.menusForm;
            this.dialogMenus = false;
        },

        //文字
        wordsEdit() {
            let words = this.words;
            this.wordsForm = JSON.parse(JSON.stringify(words));;
            this.dialogWords = true;
        },
        wordsSubmit() {
            this.words = this.wordsForm;
            this.dialogWords = false;
        },
        /////
        handleClick(tab, event) {
            console.log(tab, event);
        },

        onSubmit() {
            this.btnLoading = true;
            let para = Object.assign({words: this.words}, {menus: this.menus}, {apply: this.apply});
            request({
                params: {
                    r: 'mall/share/customize',
                },
                data: {
                    data: para,
                },
                method: 'post'
            }).then(e => {
                if (e.data.code === 0) {
                    location.reload();
                } else {
                    this.$message.error(e.data.msg);
                }
                this.btnLoading = false;
            }).catch(e => {
                this.btnLoading = false;
            });
        },

        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'mall/share/customize'
                },
            }).then(e => {
                if (e.data.code == 0) {
                    if (e.data.data) {
                        this.menus = e.data.data.menus;
                        this.words = e.data.data.words;
                        this.apply = e.data.data.apply;
                    }
                }
                this.listLoading = false;
            }).catch(e => {
                this.listLoading = false;
            });
        },
    },

    mounted() {
        this.getList();
    }
})
</script>