<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-rich-text');

?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }
    .red {
        padding: 0 25px;
        color: #ff4544;
    }
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
        min-width: 900px;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }
    .poster-mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }
    .zidingyi .poster-bg-box {
        position: relative;
        border: 2px solid #e2e3e3;
        width: 100%;
        height: 1334px;
        zoom: 0.5;
        overflow: hidden;
    }

    .poster-bg-pic {
        width: 100%;
        height: 100%;
        background-size: 100% 100%;
        background-position: center;
    }

    .zidingyi .poster-form {
        margin-bottom: 10px;
        width: 100%;
        position: relative;
        background-color: #ffffff;
        min-width: 640px;
    }
    .poster-form-title {
        padding: 24px 25% 24px 32px;
        border-bottom: 1px solid #ebeef5;
    }
    .poster-form-baby {
        padding: 26px 25% 26px 24px;
    }
    .del-btn.el-button--mini.is-circle {
        position: absolute;
        top: -8px;
        right: -8px;
        padding: 4px;
    }
    .zidingyi .bg_url {
        width: 100%;
        position: absolute;
        top: 0;
        height: 250px;
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }
    .zidingyi .poster-bg-title {
        width:100%;
        height: 40px;
        background-color: #fff0f0;
        line-height: 40px;
        text-align: center;
        font-size: 13px;
        top: 250px;
        position: absolute;
    }
    .el-date-editor .el-range-separator {
        padding: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" :rules="rules" label-width="150px" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本设置" name="first">
                        <app-setting
                            v-model="form"
                            :is_mail="false"
                            :is_print="false"
                            :is_sms="false"
                            :is_regional="true"
                            :is_discount="false"
                        ></app-setting>
                    <div class="form-body " style="padding-right: 0">
                        <div class="poster-form-title" style="margin-bottom: 24px;">活动规则设置</div>
                        <div style="width: 50%">
                            <el-form-item label="规则标题">
                                <el-input v-model="form.title"></el-input>
                            </el-form-item>
                            <el-form-item label="活动规则">
                                <div style="width: 458px; min-height: 458px;">
                                    <app-rich-text v-model="form.rule"></app-rich-text>
                                </div>
                            </el-form-item>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义海报" name="second" v-if="false">
                    <app-poster :rule_form="form.goods_poster"
                                :goods_component="goodsComponent"
                    ></app-poster>
                </el-tab-pane>
                <el-tab-pane label="自定义背景图" name="three">
                    <div style="display: flex;" class="zidingyi">
                        <div class="poster-mobile-box" flex="dir:top">
                            <div class="poster-bg-box">
                                <div style="" class="bg_url" :style="{backgroundImage: `url(${form.bg_url})`}"></div>
                                <div class="poster-bg-title" :style="{backgroundColor: form.form.bg.color, color: form.form.text.color}">100元，任选3件</div>
                            </div>
                        </div>
                        <div flex="dir:top" style="width: calc(100% - 420px);">
                            <div class="poster-form">
                                <div class="poster-form-title" style="border-bottom: 1px solid #ebeef5;">
                                    活动背景图设置
                                </div>
                                <div class="poster-form-baby"  style="margin-bottom: 15px;position: relative;">
                                    <el-form-item label="头部banner图片" required>
                                       <div flex="">
                                           <app-attachment
                                                   :multiple="false"
                                                   :max="1"
                                                   v-model="form.bg_url">
                                               <el-tooltip
                                                       class="item"
                                                       effect="dark"
                                                       content="建议尺寸:750 * 1334"
                                                       placement="top">
                                                   <el-button size="mini">
                                                       选择文件
                                                   </el-button>
                                               </el-tooltip>
                                           </app-attachment>
                                          <div style="margin-left: 15px;">
                                              <el-button size="mini" @click="form.bg_url = form.default_bg_url" type="primary">
                                                  恢复默认
                                              </el-button>
                                          </div>
                                       </div>
                                    </el-form-item>
                                    <el-form-item >
                                        <div style="position: relative;width: 80px;">
                                            <app-image width="80px"
                                                       height="80px"
                                                       mode="aspectFill"
                                                       :src="form.bg_url">
                                            </app-image>
                                            <el-button v-if="form.bg_url != ''" class="del-btn" @click="form.bg_url = ''" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                        </div>
                                    </el-form-item>
                                </div>
                            </div>
                            <div style="background-color: #f3f3f3;margin-top: 10px;"></div>
                            <div  class="poster-form">
                                <div class="poster-form-title" style="margin-bottom: 30px;">
                                    组合方案标识设置
                                </div>
                                <el-form-item label="文字颜色" >
                                    <div flex="cross:center">
                                        <el-color-picker v-model="form.form.text.color"></el-color-picker>
                                        <el-button size="mini" @click="form.form.text.color = form.default_form.text.color"  style="height: 30px;margin-left: 15px;" type="primary">
                                            恢复默认
                                        </el-button>
                                    </div>
                                </el-form-item>
                                <el-form-item label="背景颜色" >
                                   <div flex="cross:center">
                                       <el-color-picker v-model="form.form.bg.color" ></el-color-picker>
                                       <el-button size="mini" @click="form.form.bg.color = form.default_form.bg.color" style="height: 30px;margin-left: 15px;" type="primary">
                                           恢复默认
                                       </el-button>
                                   </div>
                                </el-form-item>
                            </div>
                        </div>
                    </div>
                </el-tab-pane>
            <el-button class="button-item" type="primary" size="small" :loading="submitLoading" @click="submit('form')">保存</el-button>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
                loading: false,
                form: {
                    area_limit: [
                        {
                            list: []
                        }
                    ],
                    form: {
                        text: {
                            color: ''
                        },
                        bg: {
                            color: ''
                        }
                    }
                },
                submitLoading: false,
                rules: {},
                goodsComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_nickname.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '商品名称',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品价格',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'poster_bg',
                        icon_url: 'statics/img/mall/poster/icon-mark.png',
                        title: '标识',
                        is_active: true
                    },
                ]
            };
        },
        methods: {
            async getSet() {
                this.loading = true;
                const e = await request({
                    params: {
                        r: '/plugin/pick/mall/setting'
                    },
                    method: 'get'
                });
                if (e.data.code === 0) {
                    this.form =e.data.data;
                    if (!this.form.area_limit) {
                        this.form.area_limit = [
                            {
                                list: []
                            }
                        ]
                    }
                }
                this.loading = false;
            },

            async submit() {
                this.loading = true;
                const e = await request({
                    params: {
                        r: `/plugin/pick/mall/setting`
                    },
                    method: 'post',
                    data: this.form
                });
                this.loading = false;
                if (e.data.code === 0) {
                    this.$message({
                        message: '保存成功',
                        type: 'success'
                    });
                }
            }
        },
        mounted() {
            this.getSet();
        }
    });
</script>
