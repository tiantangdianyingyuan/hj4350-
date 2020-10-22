<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .mobile-box {
        min-width: 400px;
        height: 740px;
        border-radius: 25px;
        border: 1px solid #F2F4F5;
        background-size: 375px 667px;
        background-repeat: no-repeat;
        position: relative;
        background: #FFFFFF;
        font-size: .85rem;
        margin-right: 1rem;
        margin-left: 1px;
        z-index: 1;
    }

    .mobile-box .mobile-screen {
        position: absolute;
        top: 36.5px;
        left: 12.5px;
        right: 12.5px;
        bottom: 36.5px;
        border: 1px solid #F4F5F6;
        background: #f5f7f9;
        overflow-y: hidden;
    }

    .mobile-box .mobile-navbar {
        position: absolute;
        top: 0px;
        left: 0px;
        right: 0px;
        height: 65px;
        line-height: 65px;
        text-align: center;
        background: #fff;
    }

    .mobile-box .mobile-content {
        cursor: pointer;
        position: absolute;
        top: 65px;
        left: 0;
        right: 0;
        bottom: 0;
        overflow-y: auto;
    }

    .mobile-box .mobile-content::-webkit-scrollbar {
        width: 2px;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }

    .clearfix:after {
        clear: both
    }

    .btn-right {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 24px 0 0 24px;
        padding: 3px 10px;
        margin-bottom: 3px;
        color: #FFFFFF;
    }

    .check-head {
        color: #FFFFFF;
        background-size: 100% 200px;
        height: 200px;
        width: 100%;
    }

    .btn-lq {
        padding: 5px 15px;
        border-radius: 26px;
    }

    .line {
        height: 60px;
        margin: 0 10px;
    }

    .start {
        height: 100%;
        width: 100%;
    }

    .form-body {
        padding: 3%;
        background-color: #fff;
        margin-bottom: 80px;
        width: 100%;
        position: relative;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
        position: absolute !important;
        bottom: -50px;
        left: 0;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading" style="border:0" shadow="never"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <span>自定义配置</span>
        </div>
        <div style="display: flex;justify-content: space-between">
            <div class="mobile-box">
                <div class="mobile-screen">
                    <div class="mobile-navbar">签到</div>
                    <div class="mobile-content" class="start">
                        <div class="check-head"
                             :style="{'background-image': form.head_bg ? 'url('+form.head_bg+')' : 'url('+defaultTopBg+')'}">
                            <div flex="dir:left box:last" style="padding-top: 30px;padding-left:10px">
                                <div flex="dir-left">
                                    <div :style="{'color': form.remind_font ? form.remind_font : '#FFFFFF'}"
                                         style="padding-right:10px">签到提醒
                                    </div>
                                    <app-image width="30px" height="15px" :src="defaultRemind"></app-image>
                                </div>
                                <div flex="dir:top">
                                    <div class="btn-right">签到规则</div>
                                    <div class="btn-right">回到首页</div>
                                </div>
                            </div>
                            <div :style="{'color': form.daily_font ? form.daily_font : '#FFFFFF'}"
                                 v-if="checkInStatus == 2" @click="enter(1)" flex="dir:top cross:center"
                                 style="margin-top:-60px">
                                <app-image height="100px" width="100px" style="border-radius:50%"
                                           :src="form.not_signed_icon ? form.not_signed_icon : defaultCheckInOff"></app-image>
                                <div flex="dir:left main:center">
                                    <div style="margin-right:100px" flex="dir:top cross:center">
                                        <div>11</div>
                                        <div>连续签到天数</div>
                                    </div>
                                    <div flex="dir:top cross:center">
                                        <div>12</div>
                                        <div>累计签到天数</div>
                                    </div>
                                </div>
                            </div>
                            <div :style="{'color': form.daily_font ? form.daily_font : '#FFFFFF'}"
                                 v-if="checkInStatus == 1" @click="enter(2)" flex="dir:top cross:center"
                                 style="margin-top:-60px">
                                <app-image height="100px" width="100px" style="border-radius:50%"
                                           :src="form.signed_icon ? form.signed_icon : defaultCheckInOn"></app-image>
                                <div>今日还未签到</div>
                                <div>已连续签到11天</div>
                            </div>
                        </div>
                        <div flex="dir:top" :style="{
                                background: form.end_style == 1 ?
                                (form.end_bg ? 'linear-gradient(' + form.end_bg +', '+ (form.end_gradient_bg ? form.end_gradient_bg : '#FFFFFF') + ')'
                                : 'linear-gradient(#283777, '+ (form.end_gradient_bg ? form.end_gradient_bg : '#FFFFFF') + ')')
                                : (form.end_bg ? form.end_bg : '#283777')
                            }">
                            <div flex="dir:top"
                                 style="border-radius:10px;background:#FFFFFF;width:90%;margin:0 auto">
                                <div class="line"
                                     :style="{'border-bottom': form.line_font ? '1px dashed '+form.line_font : '1px dashed #5997fc'}"
                                     flex="dir:left cross:center box:justify">
                                    <app-image height="30px" width="30px"
                                               :src="form.integral_icon ? form.integral_icon : defaultRed"></app-image>
                                    <div style="margin-left: 15px" flex="dir:top">
                                        <div>连续签到5天</div>
                                        <div>赠送20积分</div>
                                    </div>
                                    <div class="btn-lq" :style="{
                                            background: form.btn_bg ? form.btn_bg : '#cdcdcd',
                                            color: form.prompt_font ? form.prompt_font : '#ffffff'
                                        }">已领取
                                    </div>
                                </div>
                                <div style="height:58px;margin:0 10px" flex="dir:left cross:center box:justify">
                                    <app-image height="30px" width="30px"
                                               :src="form.balance_icon ? form.balance_icon : defaultIntegral"></app-image>
                                    <div style="margin-left: 15px" flex="dir:top">
                                        <div>连续签到6天</div>
                                        <div>赠送2.0余额红包</div>
                                    </div>
                                    <div class="btn-lq" :style="{
                                            background: form.not_btn_bg ? form.not_btn_bg : '#5997fc',
                                            color: form.not_prompt_font ? form.not_prompt_font : '#ffffff'
                                        }">未领取
                                    </div>
                                </div>
                            </div>
                            <div style="height:10px;"></div>
                            <div flex="dir:top"
                                 style="border-radius:10px;background:#FFFFFF;width:90%;margin:0 auto">
                                <div style="height:60px;margin:0 10px;"
                                     flex="dir:left cross:center box:justify">
                                    <app-image height="30px" width="30px"
                                               :src="form.integral_icon ? form.integral_icon : defaultRed"></app-image>
                                    <div style="margin-left: 15px" flex="dir:top">
                                        <div>累计签到5天</div>
                                        <div>赠送20积分</div>
                                    </div>
                                </div>
                            </div>
                            <div style="height:10px;"></div>
                            <div flex="dir:top cross:center"
                                 style="border-radius:10px;position:relative;background:#FFFFFF;width:90%;margin:0 auto">
                                <app-image height="305px" width="351px" :src="defaultCalendar" style="z-index: 99"></app-image>
                                <app-image style="position:absolute;top:147px;left:246px;height:30px;width:30px"
                                           :src="form.calendar_icon ? form.calendar_icon : defaultChooser"></app-image>
                                <app-image style="position:absolute;top:147px;left:292px;height:30px;width:30px"
                                           :src="form.calendar_icon ? form.calendar_icon : defaultChooser"></app-image>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-body">
                <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                    <el-row>
                        <el-col :span="10">
                            <el-form-item label="签到提醒字体颜色" prop="remind_font">
                                <el-color-picker color-format="rgb" v-model="form.remind_font"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="今日签到字体颜色" prop="daily_font">
                                <el-color-picker color-format="rgb" v-model="form.daily_font"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="已领取字体颜色" prop="prompt_font">
                                <el-color-picker color-format="rgb" v-model="form.prompt_font"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="已领取按钮颜色" prop="btn_bg">
                                <el-color-picker color-format="rgb" v-model="form.btn_bg"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="未领取字体颜色" prop="not_prompt_font">
                                <el-color-picker color-format="rgb" v-model="form.not_prompt_font"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="未领取按钮颜色" prop="not_btn_bg">
                                <el-color-picker color-format="rgb" v-model="form.not_btn_bg"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="分割线颜色" prop="line_font">
                                <el-color-picker color-format="rgb" v-model="form.line_font"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item label="下半部颜色配置" prop="end_style">
                                <el-select size="small" v-model="form.end_style" placeholder="请选择">
                                    <el-option label="純色" value="0"></el-option>
                                    <el-option label="渐变" value="1"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="下半部背景颜色" prop="end_bg">
                                <el-color-picker color-format="rgb" v-model="form.end_bg"
                                                 show-alpha></el-color-picker>
                            </el-form-item>
                            <el-form-item v-if="form.end_style == 1" label="下半部渐变颜色配置" prop="end_gradient_bg">
                                <el-color-picker color-format="rgb" v-model="form.end_gradient_bg"
                                                 show-alpha></el-color-picker>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="14">
                            <el-form-item label="未签到图标" prop="not_signed_icon">
                                <app-attachment :multiple="false" :max="1" @selected="notSignedPhoto">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:260*260" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image v-if="form.not_signed_icon" mode="aspectFill" width='80px' height='80px'
                                           :src="form.not_signed_icon"></app-image>
                            </el-form-item>
                            <el-form-item label="已签到图标" prop="signed_icon">
                                <app-attachment :multiple="false" :max="1" @selected="signedPhoto">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:260*260" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image v-if="form.signed_icon" mode="aspectFill" width='80px' height='80px'
                                           :src="form.signed_icon"></app-image>
                            </el-form-item>
                            <el-form-item label="头部背景图" prop="head_bg">
                                <app-attachment :multiple="false" :max="1" @selected="headBgPhoto">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:750*500" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image v-if="form.head_bg" mode="aspectFill" width='80px' height='80px'
                                           :src="form.head_bg"></app-image>
                            </el-form-item>
                            <el-form-item label="红包图标" prop="balance_icon">
                                <app-attachment :multiple="false" :max="1" @selected="balancePhoto">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:72*72" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image v-if="form.balance_icon" mode="aspectFill" width='80px' height='80px'
                                           :src="form.balance_icon"></app-image>
                            </el-form-item>
                            <el-form-item label="积分图标" prop="integral_icon">
                                <app-attachment :multiple="false" :max="1" @selected="integralPhoto">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:72*72" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image v-if="form.integral_icon" mode="aspectFill" width='80px' height='80px'
                                           :src="form.integral_icon"></app-image>
                            </el-form-item>
                            <el-form-item label="日历签到图标" prop="calendar_icon">
                                <app-attachment :multiple="false" :max="1" @selected="calendarPhoto">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:64*64" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image v-if="form.calendar_icon" mode="aspectFill" width='80px' height='80px'
                                           :src="form.calendar_icon"></app-image>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
                <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
            </div>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                listLoading: false,
                btnLoading: false,
                checkInStatus: 1,
                defaultRemind: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/remind.png',
                defaultCheckInOn: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/check-in.png',
                defaultCheckInOff: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/over.png',
                defaultTopBg: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/top-bg.png',
                defaultRed: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/red.png',
                defaultIntegral: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/integral.png',
                defaultCalendar: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/calendar.png',
                defaultChooser: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>" + '/img/choose.png',

                FormRules: {},
            };
        },
        methods: {
            enter(e) {
                console.log(e, 'enter');
                this.checkInStatus = e;
            },
            leave(e) {
                console.log(e, 'leave');
                this.checkInStatus = e;
            },
            notSignedPhoto(e) {
                if (e.length) {
                    Vue.set(this.form, 'not_signed_icon', e[0].url);
                }
            },
            signedPhoto(e) {
                if (e.length) {
                    Vue.set(this.form, 'signed_icon', e[0].url);
                }
            },
            headBgPhoto(e) {
                if (e.length) {
                    Vue.set(this.form, 'head_bg', e[0].url);
                }
            },
            integralPhoto(e) {
                if (e.length) {
                    Vue.set(this.form, 'integral_icon', e[0].url);
                }
            },
            balancePhoto(e) {
                if (e.length) {
                    Vue.set(this.form, 'balance_icon', e[0].url);
                }
            },
            calendarPhoto(e) {
                if (e.length) {
                    Vue.set(this.form, 'calendar_icon', e[0].url);
                }
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'plugin/check_in/mall/index/customize',
                            },
                            data: para,
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
                    }
                });
            },

            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/check_in/mall/index/customize',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.form = e.data.data;
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