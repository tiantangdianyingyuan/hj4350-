<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
?>

<style>
    .el-tabs .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="150px" ref="form" size="small" v-loading="loading" :rules="FormRules">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本设置" class="form-body" name="first">
                    <el-form-item label="可发圈对象" prop="type">
                        <template slot='label'>
                            <span>可发圈商品对象</span>
                            <el-tooltip effect="dark" content="选择全部自营商品时，所有商品均可在商品详情页一键发圈"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-radio-group v-model="form.type">
                            <el-radio :label="0">仅素材商品</el-radio>
                            <el-radio :label="1">全部自营商品</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-tab-pane>

                <el-tab-pane label="自定义海报" class="form-body" name="second" style="background:none;padding:0">
                    <app-poster :rule_form="form.goods_poster"
                                :goods_component="goodsComponent"
                    ></app-poster>
                </el-tab-pane>
            </el-tabs>
            <el-button type="primary" class="button-item" size="small" :loading="btnLoading" @click="submit">提交
            </el-button>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {},
                activeName: 'first',
                FormRules: {
                    type: [
                        {required: true, message: '可发圈对象不能为空', trigger: 'change'},
                    ]
                },
                goodsComponent: [
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '素材图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_content.png',
                        title: '文案内容',
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
                    }
                ],
            };
        },

        methods: {
            submit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'plugin/quick_share/mall/setting',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                            this.$message.error(e.data.msg);
                        });
                    }
                });
            },
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/quick_share/mall/setting',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.form = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    })
</script>