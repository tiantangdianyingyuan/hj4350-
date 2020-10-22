<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$imgUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('mch');
$indSetting = \app\forms\common\CommonOption::get(\app\models\Option::NAME_IND_SETTING);
$plugins = Yii::$app->plugin->getList();
$isWxapp = 0;
$isAlipay = 0;
foreach ($plugins as $plugin) {
    if ($plugin->name == 'wxapp') {
        $isWxapp = 1;
    }
    if ($plugin->name == 'aliapp') {
        $isAlipay = 1;
    }
}
?>
<style>
    .login-box {
        width: 350px;
        height: 360px;
        position: absolute;
        right: 170px;
        background: #ffffff;
        -webkit-border-radius: 8px;
        -moz-border-radius: 8px;
        border-radius: 8px;
        padding: 10px;
    }

    .logo {
        left: 40px;
        top: 40px;
        height: 50px;
    }

    .logo-label {
        margin-left: 15px;
        font-size: 26px;
        color: #555555;
    }

    .login-btn {
        width: 100%;
        border-radius: 20px;
        height: 38px;
        font-size: 16px;
        background: linear-gradient(to right, #2E9FFF, #3E79FF);
        box-shadow: 0 4px 10px rgba(0, 123, 255, .5)
    }

    .form-title {
        font-size: 18px;
        color: #1F4881;
        margin-bottom: 30px;
    }

    .el-input .el-input__inner {
        height: 36px;
        border-radius: 18px;
        background-color: #f7f5fb;
        border-color: #f7f5fb;
    }

    .qr_code {
        width: 60px;
        height: 60px;
        position: absolute;
        right: 10px;
        top: 10px;
        cursor: pointer;
    }

    .login-type-img {
        width: 84px;
        height: 84px;
        margin-bottom: 14px;
    }

    .login-type-label {
        color: #999999;
        font-size: 16px;
    }

    .login-type-box {
        margin: 45px 25px 0;
        cursor: pointer;
    }

    .login-type-box-mini {
        width: 120px;
        height: 44px;
        border: 1px solid rgba(238, 238, 238, 0.5);
        box-shadow: #3399FF;
        -webkit-border-radius: 50px;
        -moz-border-radius: 50px;
        border-radius: 50px;
        margin: 0 10px;
        cursor: pointer;
    }

    .type-box-active {
        border: 1px solid rgba(51, 153, 255, 0.5);
    }

    .login-type-img-mini {
        width: 24px;
        height: 24px;
        margin-right: 10px;
    }

    .qr-code-img {
        width: 140px;
        height: 140px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .pic-captcha {
        width: 100px;
        height: 36px;
        vertical-align: middle;
        cursor: pointer;
    }

</style>

<div id="app" v-cloak>
    <el-container>
        <el-header flex="cross:center" class="header-box" style="height: 90px;padding-left: 15%;">
            <div flex="cross:center">
                <image class="logo" :src="mchSettingInfo.logo"></image>
                <span class="logo-label">商家登录</span>
            </div>
        </el-header>
        <el-main flex="cross:center" style="min-height: 600px"
                 :style="{'background-image':'url('+login_bg+')'}">
            <el-card v-loading="loading" class="login-box">
                <img @click="loginTypeChange" v-if="loginType == 1" class="qr_code"
                     src="<?= $imgUrl . '/img/qr_code.png' ?>">
                <img @click="loginTypeChange" v-if="loginType == 2" class="qr_code"
                     src="<?= $imgUrl . '/img/account.png' ?>">
                <el-form :model="ruleForm" class="login-form" :rules="rules2" ref="ruleForm" label-width="0"
                         size="small">
                    <template v-if="loginType == 1">
                        <div class="form-title">账号密码登录</div>
                        <el-form-item prop="username">
                            <el-input @keyup.enter.native="login('ruleForm')" placeholder="请输入用户名"
                                      v-model="ruleForm.username"></el-input>
                        </el-form-item>
                        <el-form-item prop="password">
                            <el-input @keyup.enter.native="login('ruleForm')" type="password" placeholder="请输入密码"
                                      v-model="ruleForm.password"></el-input>
                        </el-form-item>
                        <el-form-item prop="pic_captcha">
                            <el-input @keyup.enter.native="login('ruleForm')" placeholder="验证码"
                                      style="width: 140px"
                                      v-model="ruleForm.pic_captcha"></el-input>
                            <img :src="pic_captcha_src" class="pic-captcha" @click="loadPicCaptcha">
                        </el-form-item>
                        <el-form-item>
                            <el-checkbox v-model="ruleForm.checked">记住我，以后自动登录</el-checkbox>
                        </el-form-item>
                        <el-form-item>
                            <el-button class="login-btn" :loading="btnLoading" round type="primary"
                                       @click="login('ruleForm')">登录
                            </el-button>
                        </el-form-item>
                    </template>
                    <template v-if="loginType == 2">
                        <div class="form-title">扫码授权登录</div>
                        <template v-if="!qrCodeImg">
                            <div flex="main:center">
                                <div v-if="isWxapp" @click="qrCodeLogin(1)" class="login-type-box"
                                     flex="dir:top cross:center">
                                    <img class="login-type-img" src="<?= $imgUrl . '/img/wechat.png' ?>">
                                    <span class="login-type-label">微信登录</span>
                                </div>
                                <div v-if="isAlipay" @click="qrCodeLogin(2)" class="login-type-box"
                                     flex="dir:top cross:center">
                                    <img class="login-type-img" src="<?= $imgUrl . '/img/alipay.png' ?>">
                                    <span class="login-type-label">支付宝登录</span>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div flex="dir:left main:center">
                                <div v-if="isWxapp" @click="qrCodeLogin(1)"
                                     :class="['login-type-box-mini', currentQrCodeType== 1 ? 'type-box-active' : '']"
                                     flex="dir:left main:center cross:center">
                                    <img class="login-type-img-mini" src="<?= $imgUrl . '/img/wechat.png' ?>">
                                    <span class="login-type-label">微信</span>
                                </div>
                                <div v-if="isAlipay" @click="qrCodeLogin(2)"
                                     :class="['login-type-box-mini', currentQrCodeType== 2 ? 'type-box-active' : '']"
                                     flex="dir:left main:center cross:center">
                                    <img class="login-type-img-mini" src="<?= $imgUrl . '/img/alipay.png' ?>">
                                    <span class="login-type-label">支付宝</span>
                                </div>
                            </div>
                            <div flex="main:center cross:center dir:top">
                                <img class="qr-code-img" :src="qrCodeImg">
                                <span style="color: #999999;">{{qrCodeLoginError}}</span>
                            </div>
                        </template>
                    </template>
                </el-form>
            </el-card>
        </el-main>
        <el-footer flex="main:center" style="padding-top: 40px;">
            <a style="text-decoration:none; color: #000;"
               target="_blank"
               :href="mchSettingInfo.copyright_url">
                {{mchSettingInfo.copyright}}
            </a>
        </el-footer>
    </el-container>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                login_bg: '<?= $imgUrl ?>/img/login-bg.png',
                username: '',
                password: '',
                btnLoading: false,
                dialogFormVisible: false,
                ruleForm: {
                    pic_captcha: '',
                    mall_id: getQuery('mall_id'),
                    checked: false,
                },
                rules2: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                    ],
                    pic_captcha: [
                        {required: true, message: '请输入验证码', trigger: 'blur'},
                    ],
                },
                loginType: 1,//1.账号登录 2.扫码登录
                isWxapp: <?= $isWxapp ?>,
                isAlipay: <?= $isAlipay ?>,
                loading: false,
                qrCodeImg: '',
                currentQrCodeType: 1,
                pic_captcha_src: null,
                qrCodeLoginError: '',
                mchSettingInfo: {},
            };
        },
        methods: {
            login(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'admin/passport/mch-login'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                self.$navigate({
                                    r: e.data.data.url,
                                });
                            } else {
                                this.loadPicCaptcha();
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            loginTypeChange() {
                this.loginType = this.loginType === 1 ? 2 : 1;
            },
            qrCodeLogin(type) {
                let self = this;
//                if (self.currentQrCodeType == type && self.qrCodeImg) {
//                    return false;
//                }
                self.loading = true;
                self.currentQrCodeType = type;
                request({
                    params: {
                        r: 'admin/passport/login-qr-code',
                    },
                    method: 'post',
                    data: {
                        type: type,
                        mall_id: getQuery('mall_id'),
                    },
                    headers: {'x-app-platform': type == 1 ? 'wx' : 'ali'},
                }).then(e => {
                    self.loading = false;
                    if (e.data.code === 0) {
                        self.qrCodeImg = e.data.data.data.file_path;
                        let interval = setInterval(function () {
                            self.checkQrCode(e.data.data.token)
                        }, 2000)
//                        clearInterval(interval)
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            checkQrCode(token) {
                let self = this;
                request({
                    params: {
                        r: 'admin/passport/check-mch-login',
                    },
                    method: 'post',
                    data: {
                        token: token,
                        mall_id: getQuery('mall_id'),
                    },
                }).then(e => {
                    self.loading = false;
                    if (e.data.code === 0) {
                        self.$navigate({
                            r: e.data.data.url,
                        });
                    } else {
                        self.qrCodeLoginError = e.data.msg;
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            loadPicCaptcha() {
                this.$request({
                    noHandleError: true,
                    params: {
                        r: 'site/pic-captcha',
                        refresh: true,
                    },
                }).then(response => {
                }).catch(response => {
                    if (response.data.url) {
                        this.pic_captcha_src = response.data.url;
                    }
                });
            },
            mchSetting() {
                let self = this;
                request({
                    params: {
                        r: 'admin/passport/mch-setting',
                        mall_id: getQuery('mall_id')
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        console.log(e.data.data.setting)
                        self.mchSettingInfo = e.data.data.setting;
                    } else {
                        console.log(e)
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.loadPicCaptcha();
            this.mchSetting();
        }
    });
</script>
