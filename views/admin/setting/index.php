<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/22
 * Time: 16:23
 */
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .my-img {
        height: 50px;
        border: 1px solid #d7dae2;
        border-radius: 2px;
        margin-top: 10px;
        background-color: #e2e2e2;
        overflow: hidden;
    }

    .form-body {
        display: flex;
        justify-content: center;
    }

    .form-body .el-form {
        width: 450px;
        margin-top: 10px;
    }

    .currency-width {
        width: 300px;
    }

    .currency-width .el-input__inner {
        height: 35px;
        line-height: 35px;
        border-radius: 8px;
    }

    .isAppend .el-input__inner {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .form-body .currency-width .el-input-group__append {
        width: 80px;
        background-color: #2E9FFF;
        color: #fff;
        padding: 0;
        line-height: 35px;
        height: 35px;
        text-align: center;
        border-radius: 8px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border: 0;
    }

    .preview {
        height: 75px;
        line-height: 75px;
        text-align: center;
        width: 200px;
        background-color: #F7F7F7;
        color: #BBBBBB;
        margin-top: 10px;
        font-size: 12px;
    }

    .qr-title:first-of-type {
        margin-top: 0;
    }

    .qr-title {
        color: #BBBBBB;
        font-size: 13px;
        margin-top: 10px;
    }

    .line {
        border: none;
        border-bottom: 1px solid #e2e2e2;
        margin: 40px 0;
    }

    .title {
        margin-bottom: 20px;
    }

    .submit-btn {
        height: 32px;
        width: 65px;
        line-height: 32px;
        text-align: center;
        border-radius: 16px;
        padding: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading">
        <div style="margin-bottom: 20px">站点配置</div>
        <div class='form-body' ref="body">
            <el-form label-position="left" label-width="150px">
                <el-form-item label="站点logo">
                    <el-input disabled class="currency-width isAppend">
                        <template slot="append">
                            <app-upload @complete="updateSuccess" accept="image/vnd.microsoft.icon" :params="params" :simple="true">
                                <el-button size="small">上传logo</el-button>
                            </app-upload>
                        </template>
                    </el-input>
                    <div style="height: 40px;line-height: 40px" class="preview">仅支持上传 .ico 格式文件</div>
                </el-form-item>
            </el-form>
        </div>
        <div style="margin-bottom: 20px">基础配置</div>
        <div class='form-body' ref="body">
            <el-form label-position="left" label-width="150px" :model="form" ref="form">
                <!-- 商城设置 -->
                <el-form-item label="网站名称">
                    <el-input class="currency-width" v-model="form.name"></el-input>
                </el-form-item>
                <el-form-item label="网站简称">
                    <el-input class="currency-width" v-model="form.description"></el-input>
                </el-form-item>
                <el-form-item label="网站关键字">
                    <el-input type="textarea" class="currency-width" v-model="form.keywords"></el-input>
                    <div style="color: #909399;font-size: 12px;line-height: 1;margin-top: 10px">
                        多个关键字用英文,号隔开 例如: 衣服,包包,鞋子
                    </div>
                </el-form-item>
                <el-form-item label="LOGO图片URL">
                    <el-input class="currency-width isAppend" v-model="form.logo">
                        <template slot="append">
                            <app-attachment v-model="form.logo" :simple="true">
                                <el-button>上传图片</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                    <img class="my-img" style="background-color: #100a46;border-color: #100a46; height: 36px;"
                         v-if="form.logo" :src="form.logo">
                    <div v-else class="preview">建议尺寸98*50</div>
                </el-form-item>
                <el-form-item label="底部版权信息">
                    <el-input class="currency-width" v-model="form.copyright"></el-input>
                </el-form-item>
                <el-form-item label="底部版权url">
                    <el-input class="currency-width" v-model="form.copyright_url"
                              placeholder="例如:https://www.baidu.com">
                    </el-input>
                </el-form-item>
                <el-form-item label="登录页背景图">
                    <el-input class="currency-width isAppend" v-model="form.passport_bg">
                        <template slot="append">
                            <app-attachment v-model="form.passport_bg" :simple="true">
                                <el-button>上传图片</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                    <img class="my-img" style="background-color: #100a46;border-color: #100a46; height: 108px;"
                         v-if="form.passport_bg" :src="form.passport_bg">
                    <div v-else class="preview">建议尺寸1920*1080</div>
                </el-form-item>
                <el-form-item label="管理页背景图">
                    <el-input class="currency-width isAppend" v-model="form.manage_bg">
                        <template slot="append">
                            <app-attachment v-model="form.manage_bg" :simple="true">
                                <el-button>上传图片</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                    <img class="my-img" style="background-color: #100a46;border-color: #100a46; height: 100px;"
                         v-if="form.manage_bg" :src="form.manage_bg">
                    <div v-else style="height: 40px;line-height: 40px" class="preview">建议尺寸1920*200</div>
                </el-form-item>
                <el-form-item label="开启注册功能">
                    <el-radio v-model="form.open_register" label="1">是</el-radio>
                    <el-radio v-model="form.open_register" label="0">否</el-radio>
                </el-form-item>
                <el-form-item label="证件信息是否必填">
                    <el-radio v-model="form.is_required" label="1">是</el-radio>
                    <el-radio v-model="form.is_required" label="0">否</el-radio>
                </el-form-item>
                <el-form-item label="注册页背景图">
                    <el-input class="currency-width isAppend" v-model="form.registered_bg">
                        <template slot="append">
                            <app-attachment v-model="form.registered_bg" :simple="true">
                                <el-button>上传图片</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                    <img class="my-img" style="background-color: #100a46;border-color: #100a46; height: 100px;"
                         v-if="form.registered_bg" :src="form.registered_bg">
                    <div v-else style="height: 40px;line-height: 40px" class="preview">建议尺寸1920*200</div>
                </el-form-item>
                <el-form-item label="注册页二维码">
                    <div class="qr-title">图片1</div>
                    <el-input style="margin-bottom: 10px;" class="currency-width" placeholder="添加图片描述"
                              v-model="form.qr1_about"></el-input>
                    <el-input class="currency-width isAppend" v-model="form.qr1">
                        <template slot="append">
                            <app-attachment v-model="form.qr1" :simple="true">
                                <el-button>上传图片</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                    <img class="my-img" style="background-color: #100a46;border-color: #100a46; height: 70px;"
                         v-if="form.qr1" :src="form.qr1">
                    <div v-else style="height: 140px;line-height: 140px" class="preview">建议尺寸140*140</div>
                    <div class="qr-title">图片2</div>
                    <el-input style="margin-bottom: 10px;" class="currency-width" placeholder="添加图片描述"
                              v-model="form.qr2_about"></el-input>
                    <el-input class="currency-width isAppend" v-model="form.qr2">
                        <template slot="append">
                            <app-attachment v-model="form.qr2" :simple="true">
                                <el-button>上传图片</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                    <img class="my-img" style="background-color: #100a46;border-color: #100a46; height: 70px;"
                         v-if="form.qr2" :src="form.qr2">
                    <div v-else style="height: 140px;line-height: 140px" class="preview">建议尺寸140*140</div>
                </el-form-item>
                <el-form-item label="注册协议" style="width: 600px;">
                    <app-rich-text :simple-attachment="true" v-model="form.register_protocol"></app-rich-text>
                </el-form-item>
                <!-- 分割线 -->
                <hr :style="line" class="line">
                <!-- 短信设置 -->
                <!-- <el-form-item> -->
                <div :style="line" class="title">
                    <span style="font-size: 15px;">短信配置（阿里云）</span>
                    <span style="color: #909399;font-size: 12px;">用于发送（注册、重置密码）短信验证码、注册结果短信通知。</span>
                </div>
                <!-- </el-form-item> -->

                <el-form-item label="AccessKeyId">
                    <el-input class="currency-width" v-model="form.ind_sms.aliyun.access_key_id"></el-input>
                </el-form-item>
                <el-form-item label="AccessKeySecret">
                    <el-input class="currency-width" v-model="form.ind_sms.aliyun.access_key_secret"></el-input>
                </el-form-item>
                <el-form-item label="短信签名">
                    <el-input class="currency-width" v-model="form.ind_sms.aliyun.sign"></el-input>
                </el-form-item>
                <el-form-item label="验证码模板ID">
                    <el-input class="currency-width" v-model="form.ind_sms.aliyun.tpl_id"></el-input>
                    <div style="color: #909399;font-size: 12px;line-height: 1;margin-top: 10px">模板示例: 您的验证码是${code}
                    </div>
                </el-form-item>
                <el-form-item label="注册审核成功模板ID">
                    <el-input class="currency-width" v-model="form.ind_sms.aliyun.register_success_tpl_id"></el-input>
                    <div style="color: #909399;font-size: 12px;line-height: 1;margin-top: 10px">
                        用于用户注册审核成功的通知，模板示例：您注册的账户${name}审核已通过。
                    </div>
                </el-form-item>
                <el-form-item label="注册审核失败模板ID">
                    <el-input class="currency-width" v-model="form.ind_sms.aliyun.register_fail_tpl_id"></el-input>
                    <div style="color: #909399;font-size: 12px;line-height: 1;margin-top: 10px">
                        用于用户注册审核失败的通知，模板示例：您注册的账户${name}审核未通过。
                    </div>
                </el-form-item>

                <el-form-item>
                    <el-button class="submit-btn" type="primary" @click="submit" :loading="submitLoading">保存</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                submitLoading: false,
                line: {
                    width: '450px',
                    marginLeft: '-150px'
                },
                form: {
                    name: '',
                    logo: '',
                    copyright: '',
                    passport_bg: '',
                    open_register: '0',
                    is_required: '1',
                    register_protocol: '',
                    ind_sms: {
                        aliyun: {
                            access_key_id: '',
                            access_key_secret: '',
                            sign: '',
                            tpl_id: '',
                            register_success_tpl_id: '',
                            register_fail_tpl_id: '',
                        }
                    },
                },
                params: {
                    r: 'admin/setting/upload-logo'
                },
            };
        },
        created() {
            this.loadData();
            this.$nextTick(function () {
                this.line.width = this.$refs.body.clientWidth + 'px';
                this.line.marginLeft = -(this.$refs.body.clientWidth - 450) / 2 + 'px';
            })
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'admin/setting/index',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        if (e.data.data.setting) {
                            this.form = e.data.data.setting;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            submit() {
                this.submitLoading = true;
                this.$request({
                    params: {
                        r: 'admin/setting/index',
                    },
                    method: 'post',
                    data: {
                        setting: this.form,
                    },
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            updateSuccess(e) {
                this.$message.success('上传成功')
            }
        }
    });
</script>