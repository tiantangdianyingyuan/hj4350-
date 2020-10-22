<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
        min-width: 900px;
    }

    .mobile {
        width: 400px;
        height: 740px;
        border: 1px solid #cccccc;
        padding: 25px 10px;
        border-radius: 30px;
        margin: 0 20px;
        position: relative;
        flex-shrink: 0;
    }

    .mobile .bg {
        width: 375px;
        height: 667px;
    }
    .mobile .title {
        position: absolute;
        top: 30px;
        left: 0;
        text-align: center;
        width: 375px;
        font-size: 16px;
        font-weight: 600;
        color: #303133;
    }
    .mobile .content {
        position: absolute;
        top: 65px;
        left: 0;
        width: 375px;
        height: 625px;
        overflow-y: auto
    }

    .mobile .content p {
        margin: 0;
    }

    .mobile .content img {
        max-width: 100%;
    }
    .url .el-input .el-input__inner {
        background-color: #F5F7FA;
        border-color: #E4E7ED;
        color: #C0C4CC;
    }

    .button-item {
        padding: 9px 25px;
    }

    .bottom-div {
        border-top: 1px solid #E3E3E3;
        position: fixed;
        bottom: 0;
        background-color: #ffffff;
        z-index: 999;
        padding: 10px;
        width: 80%;
    }
</style>

<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;" v-loading="loading">
        <div class="text item" style="width:100%">
            <el-form :model="form" label-width="150px" size="small" ref="form">
                <div class="form-body">
                    <div flex="dir:left">
                        <div class="mobile">
                            <div style="height: 690px;position: absolute;overflow-x: hidden;overflow-y: auto;">
                                <img class="bg" src="statics/img/plugins/template.png" alt="">
                                <div class="title">{{form.recruit_title}}</div>
                                <div class="content">
                                    <div v-html="form.recruit_content"></div>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 30px;">
                            <el-form-item class="url" label="小程序路径">
                                <el-input v-model="url" id="url" :readonly="true">
                                    <template slot="append">
                                        <el-button size="mini" class="copy-btn" data-clipboard-action="copy" type="primary"
                                                   data-clipboard-target="#url">复制
                                        </el-button>
                                    </template>
                                </el-input>
                                <el-popover placement="bottom" width="210" trigger="click">
                                    <div flex="dir:top cross:center main:center">
                                        <img :src="qrimg" width="145" height="145" style="display: block;margin-bottom: 10px" alt="">
                                        <el-button type="text" @click="downloadImg">下载小程序码</el-button>
                                    </div>
                                    <el-button slot="reference" type="text">在小程序上推广</el-button>
                                </el-popover>
                            </el-form-item>
                            <el-form-item label="页面标题">
                                <el-input v-model="form.recruit_title" maxlength="13"></el-input>
                            </el-form-item>
                            <div style="padding-top: 30px;">
                                <app-rich-text style="width: 590px;padding-left: 80px;" v-model="form.recruit_content"></app-rich-text>
                            </div>
                        </div>
                    </div>
                </div>
            </el-form>
            <div class="bottom-div" flex="cross:center">
                <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
                </el-button>
            </div>
        </div>
    </el-card>
</section>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('.copy-btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });
    const app = new Vue({
        el: '#app',

        data() {
            return {
                //列表刷新loading
                loading: false,
                // 按钮刷新loading
                btnLoading: false,
                bg_url: '',
                qrimg: '',
                url: 'plugins/community/recruit/recruit',
                form: {
                    recruit_title: '招募令',
                    recruit_content:''
                },
            };
        },

        methods: {
            downloadImg() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.form.recruit_title ? this.form.recruit_title : '招募令';
                alink.click();
            },
            qrcode() {
                let para = {
                    r: 'mall/app-page/qrcode',
                    path: 'plugins/community/recruit/recruit',
                };
                request({
                    params: para,
                    method: 'get',
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.wechat) {
                            this.qrimg = e.data.data.wechat.file_path
                        } else if (e.data.data.alipay) {
                            this.qrimg = e.data.data.alipay.file_path
                        }
                    } else {
                        this.$message.error(e.data.msg)
                    }
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/setting/setting-data',
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.form = e.data.data;
                        this.bg_url = this.form.banner
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        	onSubmit() {
                if(!this.form.recruit_title) {
                    this.$message.error('页面标题不能为空');
                    return false
                }
                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/setting/recruit',
                    },
                    data: this.form,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 0) {
                        this.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
        	}
        },

        created() {
            this.loadData();
            this.qrcode();
        },
    })
</script>