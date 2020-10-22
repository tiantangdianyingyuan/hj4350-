<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-banner');
Yii::$app->loadViewComponent('app-rich-text');

?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }
    .red {
        display:inline-block;
        padding: 0 25px;
        color: #ff4544;
    }
    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }
    .button-item {
        margin-top: 20px;
        padding: 9px 25px;
    }
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
    }
    .red {
        color: #ff4544;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="loading">
        <div class="text item" style="width:100%">
            <el-form :model="form" label-width="150px" :rules="rule" ref="form">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础设置" class="form-body" name="first">
                        <app-setting v-model="form" :is_member_price="false" :is_territorial_limitation="true" :is_coupon="true"></app-setting>

                        <el-card style="margin-bottom: 10px">
                            <div slot="header">砍价规则设置</div>
                            <el-form-item label="活动规则" prop="rule">
                                <div style="width: 458px; min-height: 458px;">
                                    <app-rich-text v-model="form.rule"></app-rich-text>
                                </div>
                            </el-form-item>
                            <el-form-item label="活动标题" prop="title">
                                <label slot="label">活动标题
                                    <el-tooltip class="item" effect="dark"
                                                content="多个标题请换行，多个标题随机选一个标题显示"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input class="ml-24" style="width: 600px" type="textarea" :rows="3" placeholder="请输入活动标题"
                                          v-model="form.title"></el-input>
                            </el-form-item>
                        </el-card>

                    </el-tab-pane>
                    <el-tab-pane v-if="false"  label="自定义海报" class="form-body" name="second">
                        <app-poster :rule_form="form.goods_poster"
                                      :goods_component="goodsComponent"
                                      goods_component_key_tmp="head"
                        ></app-poster>
                    </el-tab-pane>
                    <el-tab-pane label="轮播图" class="form-body" name="third">
                        <app-banner url="plugin/bargain/mall/index/banner-store" submit_url="plugin/bargain/mall/index/banner-store" :title="false"></app-banner>
                    </el-tab-pane>
                </el-tabs>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small" v-if="activeName != 'third'">保存</el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {},
                rule: {},
                is_show: false,
                activeName: 'first',
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
                    {
                        key: 'time_str',
                        icon_url: 'statics/img/mall/poster/icon_time.png',
                        title: '时间',
                        is_active: true
                    },
                ],
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/bargain/mall/index/index-data'
                            },
                            method: 'post',
                            data: this.form
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    } else {
                        this.btnLoading = false;
                        return false;
                    }
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/bargain/mall/index/index-data'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    this.is_show = true;
                    if (e.data.code == 0) {
                        this.form = e.data.data.list;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            }
        },

        created() {
            this.loadData();
        },
    })
</script>