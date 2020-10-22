<?php
defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 60%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/recharge/index'})">充值管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>设置</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="form" v-loading="loading" label-width="10rem" ref="form">
                <el-form-item label="开启余额功能" prop="status">
                    <el-switch v-model="form.status" active-value="1" inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="是否开放自定义金额" prop="type">
                    <el-switch v-model="form.type" active-value="1" inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="背景图片" prop="bj_pic_url" size="small">
                    <app-attachment @selected="bjSelected">
                        <el-tooltip class="item" effect="dark" content="建议尺寸750*324" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image mode="aspectFill" :src="form.bj_pic_url.url" width="80" height="80"></app-image>
                </el-form-item>
                </el-form-item>
                <el-form-item label="广告图片" prop="ad_pic_url" size="small">
                    <app-attachment @selected="adSelected">
                        <el-tooltip class="item" effect="dark" content="建议尺寸750*180" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image mode="aspectFill" :src="form.ad_pic_url.url" width="80" height="80"></app-image>
                </el-form-item>
                </el-form-item>
                <el-form-item label="广告图片跳转链接" prop="page_url">
                    <el-input :disabled="true" size="small" v-model="form.page_url" autocomplete="off">
                        <app-pick-link slot="append" @selected="selectAdvertUrl">
                            <el-button size="mini">选择链接</el-button>
                        </app-pick-link>
                    </el-input>
                </el-form-item>
                <el-form-item label="充值按钮文字" prop="re_name">
                    <el-input type="input" size="small" v-model="form.re_name"></el-input>
                </el-form-item>
                <el-form-item label="充值说明图标" prop="re_pic_url" size="small">
                    <app-attachment @selected="iconSelected">
                        <el-tooltip class="item" effect="dark" content="建议尺寸36*36" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image mode="aspectFill" :src="form.re_pic_url.url" width="80" height="80"></app-image>
                </el-form-item>
                </el-form-item>
                <el-form-item label="充值说明" prop="explain">
                    <div style="width: 458px; min-height: 458px;">
                        <app-rich-text v-model="form.explain"></app-rich-text>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" @click="onSubmit" :loading=btnLoading size="mini">提交</el-button>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    bj_pic_url: {},
                    re_pic_url: {},
                    ad_pic_url: {},
                },
                btnLoading:false,
                loading:false
            };
        },
        methods: {
            bjSelected(list) {
                this.form.bj_pic_url = list.length ? list[0] : null;
            },

            adSelected(list) {
                this.form.ad_pic_url = list.length ? list[0] : null;
            },

            iconSelected(list) {
                this.form.re_pic_url = list.length ? list[0] : null;
            },

            selectAdvertUrl(e){
                let self = this;
                e.forEach(function (item, index) {
                    self.form.page_url = item.new_link_url;
                    self.form.open_type = item.open_type;
                    self.form.params = item.params;
                })
            },
            // 提交数据
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    this.btnLoading = true;
                    if (valid) {
                        let para = Object.assign(this.form);
                        request({
                            params: {
                                r: 'mall/recharge/setting',
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
                                setTimeout(function(){
                                    navigateTo({ r: 'mall/recharge/index' });
                                },300);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },

            //获取列表
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/recharge/setting',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {     
                        this.form = e.data.data;
                    }
                }).catch(e => {

                });
            },
        },

        created() {
            this.getList();
        }
    })
</script>
