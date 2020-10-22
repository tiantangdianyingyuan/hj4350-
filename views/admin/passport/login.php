<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$indSetting = \app\forms\common\CommonOption::get(\app\models\Option::NAME_IND_SETTING);
?>
<script>const passportBg = '<?=($indSetting && !empty($indSetting['passport_bg'])) ? $indSetting['passport_bg'] : ''?>';</script>
<style>
    .login {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        position: relative;
        background-size: 100% 100%;
    }

    .login .box-card {
        position: relative;
        border-radius: 15px;
        z-index: 99;
        border: 0;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .5);
        width: 825px;
        height: 480px;
        margin: 0 auto;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center;
        background-size: 100% 100%;
    }

    .logo {
        position: absolute;
        left: 40px;
        top: 40px;
        height: 50px;
    }

    .username, password {
        margin-bottom: 20px;
    }

    .login-btn {
        width: 100%;
        border-radius: 20px;
        height: 38px;
        font-size: 16px;
        background: linear-gradient(to right, #2E9FFF, #3E79FF);
        box-shadow: 0 4px 10px rgba(0, 123, 255, .5)
    }

    .radio-box {
        height: 35px;
        line-height: 35px;
    }

    .register_box {
        position: absolute;
        right: 15%;
        bottom: 35px;
        width: 150px;
    }

    .register {
        display: inline-block;
        width: 48%;
        height: 15px;
        line-height: 15px;
        text-align: center;
        cursor: pointer;
        color: #4291ff;
    }

    .el-dialog {
        width: 35%;
    }

    .el-card__body {
        padding: 0;
    }

    .login-form {
        padding: 50px 45px 30px;
        height: 480px;
        width: 335px;
        float: right;
        background-color: #fff;
    }

    .form-title {
        font-size: 26px;
        color: #1F4881;
        margin-bottom: 40px;
    }

    .opacity {
        background-color: rgba(0, 0, 0, 0.15);
        height: 100%;
        width: 100%;
        position: absolute;
        left: 0;
        top: 0;
        z-index: 1;
    }

    .el-input .el-input__inner {
        height: 36px;
        border-radius: 18px;
        background-color: #f7f5fb;
        border-color: #f7f5fb;
    }

    .foot {
        position: absolute;
        left: 0;
        right: 0;
        width: auto;
        color: #fff;
        text-align: center;
        font-size: 16px;
    }

    .foot a,
    .foot a:visited {
        color: #f3f3f3;
    }

    .footer-text {
        margin-bottom: 10px;
    }

    .pic-captcha {
        width: 100px;
        height: 36px;
        vertical-align: middle;
        cursor: pointer;
    }
</style>

<div id="app" v-cloak>
    <div class="login" :style="{'background-image':'url('+login_bg+')'}">
        <div class="opacity" flex="cross:center main:center">
            <el-card class="box-card" shadow="always" :style="{'background-image':'url('+login_bg+')'}">
                <img v-if="user_type == 2" class="logo" :src="roleSetting.logo" alt="">
                <img v-else class="logo" :src="login_logo" alt="">
                <el-form :model="ruleForm" class="login-form" :rules="rules2" ref="ruleForm" label-width="0"
                         size="small">
                    <div class="form-title">{{user_type == 1 ? '管理员' : '员工'}}登录</div>
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
                </el-form>
                <img class="logo" :src="roleSetting.logo" alt="">
                <div v-if="user_type == 1" class="register_box">
                    <span class="register" @click="forget">忘记密码?</span>
                    <?php if ($indSetting
                        && isset($indSetting['open_register'])
                        && $indSetting['open_register'] == 1) : ?>
                        <span class="register" style="border-left: 1px solid #a9a9a9;" @click="register">注册账号</span>
                    <?php endif; ?>
                </div>
            </el-card>

            <!--忘记密码-->
            <div class="foot" :style="{'bottom': footHeight}">
                <!--员工-->
                <template v-if="user_type == 2">
                    <a :href="roleSetting.copyright_url" target="_blank">{{roleSetting.copyright}}</a>
                </template>
                <!--管理员-->
                <template v-else>
                    <?php if ($indSetting && !empty($indSetting['copyright'])) : ?>
                        <a style="text-decoration: none" href="<?= $indSetting['copyright_url'] ?>"
                           target="_blank"><?= $indSetting['copyright'] ?></a>
                    <?php else : ?>
                        <a href="#" target="_blank">底部版权</a>
                    <?php endif; ?>
                </template>
            </div>
        </div>
    </div>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                login_bg: passportBg ? passportBg : (_baseUrl + '/statics/img/admin/BG.png'),
                login_logo: _siteLogo,
                username: '',
                password: '',
                footHeight: '5%',
                btnLoading: false,
                user_type: '2',
                dialogFormVisible: false,
                ruleForm: {
                    pic_captcha: '',
                    checked: false
                },
                roleSetting: {},
                rules2: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                    ],
                    pic_captcha: [
                        {required: true, message: '请输入右侧图片上的文字', trigger: 'blur'},
                    ],
                },
                pic_captcha_src: null,
            };
        },
        created() {
            this.loadPicCaptcha();
        },
        methods: {
            login(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'admin/passport/login'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                                user_type: self.user_type,
                                mall_id: getQuery('mall_id'),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                self.$navigate({
                                    r: e.data.data.url,
                                });
                            } else {
                                if (e.data.data && e.data.data.register) {
                                    this.$navigate({r: 'admin/passport/register', active: 3});
                                }
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
            register() {
                navigateTo({
                    r: 'admin/passport/register',
                });
            },
            forget() {
                navigateTo({
                    r: 'admin/passport/register',
                    status: 'forget'
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
            getRoleSetting() {
                let self = this;
                request({
                    params: {
                        r: 'admin/passport/role-setting',
                        mall_id: this.mall_id,
                    },
                    method: 'get',
                }).then(e => {
                    self.roleSetting = e.data.data.setting;
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.mall_id = getQuery('mall_id');
            this.user_type = this.mall_id ? '2' : '1';
            let height = document.body.clientHeight;
            this.footHeight = height < 600 ? '1%' : '5%'
            if (this.user_type == 2) {
                this.getRoleSetting();
            }
        }
    });
</script>
