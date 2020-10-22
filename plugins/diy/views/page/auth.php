<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/16
 * Time: 9:28
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-hotspot');
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
$picUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/mall';
?>
<link rel="stylesheet" href="<?= $pluginUrl ?>/css/style.css">
<style>
    .outside {
        width: 401px;
        height: 741px;
        background-color: #fff;
        border-radius: 30px;
        padding: 37px 13px;
        margin-right: 24px;
    }

    .inside {
        width: 375px;
        height: 667px;
        background-color: #eee;
    }

    .inside .pic {
        width: auto;
        height: auto;
        max-width: 750px;
        max-height: 1334px;
        transform: scale(0.5);
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="margin-bottom: 10px;">授权自定义</el-card>
    <el-row type="flex" flex="box:first">
        <div class="outside">
            <div class="inside" flex="dir:left main:center cross:center">
                <img class="pic"
                     :src="ruleForm.pic_url ? ruleForm.pic_url : default_pic_url">
            </div>
        </div>
        <div class="right">
            <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="100px" size="small">
                <el-card shadow="never" v-loading="loading">
                    <div slot="header">设置</div>
                    <el-form-item prop="is_open" label="是否开启">
                        <el-switch
                                v-model="ruleForm.is_open"
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#dcdfe6">
                        </el-switch>
                    </el-form-item>
                    <el-form-item prop="pic_url" label="上传图片">
                        <app-attachment :multiple="false" :max="1" @selected="selectPic">
                            <el-tooltip effect="dark" content="推荐尺寸650*700" placement="bottom">
                                <el-button>选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery style="margin-top: 10px" :show-delete="true" width="125px" height="222px" v-if="ruleForm.pic_url"
                                     @deleted="deletePic"
                                     :list="[{url:ruleForm.pic_url}]">
                        </app-gallery>
                    </el-form-item>
                    <el-form-item prop="pic_url" label="热区">
                        <div flex="dir:left">
                            <app-hotspot :multiple="true" width="650px" height="700px" @confirm="confirm"
                                         :max="2" mode="auth"
                                         :hotspot-array="hotspot()"
                                         :pic-url="ruleForm.pic_url ? ruleForm.pic_url : default_pic_url">
                                <el-button>划分热区</el-button>
                            </app-hotspot>
                        </div>
                        <div>注：需要划分两个热区：一个是登录按钮热区，一个是暂不登录按钮热区</div>
                    </el-form-item>
                </el-card>
                <el-button class="button-item" type="primary" size="small" :loading="submitLoading"
                           @click="submit('ruleForm')">
                    保存
                </el-button>
            </el-form>
        </div>
    </el-row>
</div>
<script>
    // 尺寸转化比例尺1:2，即PC端375px相当于小程序端750rpx
    const app = new Vue({
        el: '#app',
        data() {
            return {
                default_pic_url: '<?= $picUrl ?>/auth-default.png',
                submitLoading: false,
                loading: false,
                ruleForm: {
                    pic_url: '',
                    is_open: 0,
                    hotspot: '',
                    hotspot_cancel: '',
                },
                rules: {},
            }
        },
        created() {
            this.getDetail();
        },
        methods: {
            // 选择图片
            selectPic(e) {
                this.ruleForm.pic_url = e[0].url;
            },
            // 划分热区
            confirm(e) {
                for (let i in e) {
                    if (e[i].open_type == 'login') {
                        this.ruleForm.hotspot = e[i];
                    }
                    if (e[i].open_type == 'cancel') {
                        this.ruleForm.hotspot_cancel = e[i];
                    }
                }
            },
            // 删除图片
            deletePic(item, index) {
                this.ruleForm.pic_url = '';
            },
            // 保存
            submit(formName) {
                if (!this.ruleForm.hotspot) {
                    this.$message.error('请先选择登录按钮热区');
                    return ;
                }
                if (!this.ruleForm.hotspot_cancel) {
                    this.$message.error('请先选择不登录按钮热区');
                    return ;
                }
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'plugin/diy/mall/page/auth'
                            },
                            method: 'post',
                            data: this.ruleForm
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {

                        });
                    }
                })
            },
            getDetail() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/page/auth'
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        if (e.data.data) {
                            this.ruleForm = e.data.data;
                        }
                    }
                }).catch(e => {

                });
            },
            hotspot() {
                let res = [];
                if (this.ruleForm.hotspot) {
                    res.push(this.ruleForm.hotspot);
                }
                if (this.ruleForm.hotspot_cancel) {
                    res.push(this.ruleForm.hotspot_cancel);
                }
                return res;
            }
        }
    });
</script>
