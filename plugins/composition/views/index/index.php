<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 13:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .bg .left {
        zoom: 0.5;
        width: 790px;
        height: 1456px;
        padding: 60px 20px;
        background: #ffffff;
        -webkit-border-radius: 60px;
        -moz-border-radius: 60px;
        border-radius: 60px;
        margin-right: 20px;
    }

    .bg .left .content {
        width: 751px;
        height: 1331px;
        background: #f7f7f7;
        border: 1px solid #eeeeee;
        position: relative;
    }

    .bg .left .content .mobile-bg {
        width: 750px;
        height: 1334px;
    }

    .bg .left .content .activity-bg {
        position: absolute;
        top: 125px;
        left: 0;
        height: 280px;
        width: 750px;
    }

    .bg .right {
        height: 100%;
        position: relative;
    }

    .red {
        color: #ff4544;
        margin-left: 20px;
    }
    .doit {
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .el-dialog {
        min-width: 800px;
    }

    .title {
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .right-button {
        position: absolute;
        top: 100%;
        left: 0;
    }

    .mobile-rule {
        position: absolute;
        right: 24px;
        top: 146px;
        z-index: 100;
        height: 48px;
        line-height: 48px;
        font-size: 22px;
        color: #fff;
        background-color: rgba(0, 0, 0, .4);
        padding: 0 16px;
        border-radius: 24px;
    }
</style>
<div id='app' v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="loading">
        <div class="text item" style="width:100%">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础设置" name="first">

                        <app-setting v-model="form" :label_width="200" :is_full_reduce="true" :is_integral="false" :is_member_price="false"
                                     :is_sms="hiddenSetting" :is_mail="hiddenSetting" :is_print="hiddenSetting">
                            <template slot="more" slot-scope="scope">
                                <el-form-item label="是否允许使用优惠券" prop="is_coupon">
                                    <el-switch v-model="form.is_coupon"
                                               :active-value="1"
                                               :inactive-value="0">
                                    </el-switch>
                                </el-form-item>
                            </template>
                        </app-setting>
                    <div class="title">
                        <span>活动规则设置</span>
                    </div>
                    <div class="form-body" v-if="!loading">
                        <el-form :model="form" label-width="200px" :rules="rules" ref="form">
                            <el-form-item label="规则标题" prop="title">
                                <el-input v-model="form.title"></el-input>
                            </el-form-item>
                            <el-form-item label="活动规则" prop="rule">
                                <el-input class="ml-24" style="max-width: 700px" type="textarea" :rows="5"
                                          v-model="form.rule"></el-input>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义广告图" name="second">
                    <div class="bg" flex="dir:left box:first">
                        <div class="left">
                            <div class="content">
                                <img class="mobile-bg" src="statics/img/plugins/composition.png" alt="">
                                <div class="mobile-rule">活动规则</div>
                                <img class="activity-bg" :src="form.activityBg" alt="">
                            </div>
                        </div>
                        <div class="right form-body">
                            <el-form :model="form" label-width="120px" :rules="rules" ref="form">
                                <el-form-item label="广告图" prop="activityBg">
                                    <div flex style="margin-bottom: 8px;">
                                        <app-attachment :multiple="false" :max="1" @selected="sellOutOtherPic">
                                            <el-tooltip effect="dark"
                                                        content="建议尺寸:750 * 280"
                                                        placement="top">
                                                <el-button size="mini">选择图标</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <div style="margin-left: 10px;">
                                            <el-button type="primary" size="mini" @click="resetBg">恢复默认</el-button>
                                        </div>
                                    </div>
                                    <app-gallery :url="form.activityBg" :show-delete="true" @deleted="deleteBg"></app-gallery>
                                </el-form-item>
                            </el-form>
                            <div class="right-button" v-if="activeName == 'second'">
                                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small">保存</el-button>
                            </div>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" v-if="activeName == 'first'" :loading="btnLoading" type="primary" @click="store('form')" size="small">
                保存
            </el-button>
        </div>
    </el-card>
    <el-dialog title="选择地区" :visible.sync="dialogVisible" width="50%">
        <div style="margin-bottom: 1rem;">
            <app-district :edit="detail" @selected="selectDistrict" :level="3"></app-district>
            <div style="text-align: right;margin-top: 1rem;">
                <el-button type="primary" @click="districtConfirm">
                    确定选择
                </el-button>
            </div>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                if (this.form.activityBg === '' || this.form.activityBg === undefined) {
                    callback(new Error('请选择广告图'));
                } else {
                    callback();
                }
            };
            return {
                loading: false,
                btnLoading: false,
                activeName: 'first',
                defaultImg: 'statics/img/app/composition/banner.png',
                dialogVisible: false,
                detail: {
                    list: []
                },
                hiddenSetting: false,
                form: {
                    payment_type: ['online_pay','balance'],
                    send_type: ['express','offline','city'],
                    is_share: 0,
                    is_coupon: 0,
                    title: '',
                    rule: '',
                    is_territorial_limitation: 0,
                    detail: [
                        {list: []}
                    ],
                    activityBg: this.defaultImg
                },
                rules: {
                    activityBg: [
                        { required: true, validator: validateRate, trigger: 'blur' }
                    ],
                },
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/composition/mall/index/index'
                    }
                }).then(e => {
                    this.loading = false;
                    this.form = e.data.data;
                    this.defaultImg = e.data.data.defaultImg;
                });
            },
            openDistrict(index) {
                this.detail = JSON.parse(JSON.stringify(this.form.detail));
                this.dialogVisible = true;
            },
            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.detail[0].list = list;
            },
            districtConfirm() {
                this.form.detail = JSON.parse(JSON.stringify(this.detail));
                this.detail = [];
                this.dialogVisible = false;
            },
            deleteDistrict(index) {
                this.form.detail[0].list = [];
            },
            sellOutOtherPic(e) {
                if (e.length) {
                    this.form.activityBg = e[0].url;
                }
            },
            deleteBg() {
                this.form.activityBg = '';
            },
            resetBg() {
                this.form.activityBg = this.defaultImg;
            },
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        if (this.form.activityBg == '' || this.form.activityBg == undefined) {
                            this.$message.error('请选择广告图');
                            return false;
                        }
                        this.btnLoading = true;
                        this.$request({
                            params: {
                                r: 'plugin/composition/mall/index/index'
                            },
                            method: 'post',
                            data: {
                                ruleForm: JSON.stringify(this.form)
                            }
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    }
                })
            }
        }
    });
</script>
