<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-rich-text');

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
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
    .red {
        color: #ff4544;
        padding: 0 10px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px !important;
    }

    .wechat-end-box {
        height: 32px;
        line-height: 32px;
        width: 200px;
        padding: 0 12px;
        color: #606266;
        border-left: 1px solid #e2e2e2;
        border-right: 1px solid #e2e2e2;
        border-bottom: 1px solid #e2e2e2;
    }

    .wechat-image {
        height: 232px;
        width: 200px;
        cursor: pointer;
        position: relative;
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="listLoading">
        <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础设置" name="first">
                    <app-setting v-model="form" :is_discount="false" :is_share="false" :is_territorial_limitation="false"></app-setting>
                    <div class="form-body">
                        <el-form-item label="小程序标题" prop="title">
                            <el-input size="small" style="width: 30%" v-model="form.title"
                                      autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="中奖码获取规则" prop="type">
                            <el-radio-group v-model="form.type">
                                <el-radio :label="0">分享点击即送</el-radio>
                                <el-radio :label="1">参加抽奖即送</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item label="规则说明" prop="rule">
                            <div style="width: 458px; min-height: 458px;">
                                <app-rich-text v-model="form.rule"></app-rich-text>
                            </div>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义海报" v-if="false"  class="form-body" style="padding: 0;background:none" name="second">
                    <app-poster :rule_form="form.goods_poster" :goods_component="goodsComponent"></app-poster>
                </el-tab-pane>
                <el-tab-pane label="客服设置" class="form-body" name="three">
                    <el-form-item class="switch" label="是否开启客服提示" prop="cs_status">
                        <el-switch v-model="form.cs_status" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item label="客服提示图片" prop="cs_prompt_pic">
                        <div style="margin-bottom:10px;">
                            <app-attachment style="display:inline-block;margin-right: 10px" :multiple="false" :max="1"
                                            @selected="wechatPrompt">
                                <el-tooltip effect="dark" content="建议尺寸:750 * 150" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <el-button type="primary" @click="wechatPromptDefault" size="mini">恢复默认</el-button>
                        </div>
                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                            <app-attachment :multiple="false" :max="1" @selected="wechatPrompt">
                                <app-image mode="aspectFill" width="80px" height='80px'
                                           :src="form.cs_prompt_pic"></app-image>
                            </app-attachment>
                            <el-button v-if="form.cs_prompt_pic" class="del-btn" size="mini" type="danger"
                                       icon="el-icon-close" circle @click="wechatPromptClose"></el-button>
                        </div>
                    </el-form-item>

                    <el-form-item label="客服微信" prop="cs_wechat">
                        <el-button size="mini" @click="addWechat">选择</el-button>
                        <div flex="dir:left" style="flex-wrap:wrap">
                            <div v-for="(value,index) in form.cs_wechat" style="margin-right: 24px;margin-top: 12px">
                                <div class="wechat-image" flex="dir:top"
                                     @click="editWechat(value,index)">
                                    <el-image :src="value.qrcode_url" style="height: 200px;width:100%"></el-image>
                                    <el-tooltip class="v" effect="dark" :content="'微信号'+ value.name" placement="top">
                                        <div class="wechat-end-box">微信号：{{value.name}}</div>
                                    </el-tooltip>
                                    <el-button v-if="form.cs_prompt_pic" class="del-btn" size="mini" type="danger"
                                               icon="el-icon-close" circle @click.stop="picClose(index)"></el-button>
                                </div>
                            </div>
                        </div>
                        <div style="color:#909399">注意：最多允许上传10张，前端随机展示一张</div>
                    </el-form-item>

                    <el-form-item label="微信群二维码" prop="cs_wechat_flock_qrcode_pic">
                        <app-attachment style="margin-bottom:10px" :multiple="true" :max="50" @selected="wechatFlock">
                            <el-tooltip effect="dark" content="建议尺寸:360 * 360" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery v-if="form.cs_wechat_flock_qrcode_pic && form.cs_wechat_flock_qrcode_pic.length"
                                     :show-delete="true" @deleted="wechatFlockClose"
                                     :list="form.cs_wechat_flock_qrcode_pic"></app-gallery>
                        <app-image v-else width="80px" height='80px'></app-image>
                        <div style="color:#909399">注意：最多允许上传10张，前端随机展示一张</div>
                    </el-form-item>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
        </el-form>

        <!--客服微信-->
        <el-dialog title="客服微信" :visible.sync="wechatVisible" width="30%" :close-on-click-modal="false">
            <el-form :model="wechatForm" label-width="150px" :rules="wechatRules" ref="wechatForm"
                     @submit.native.prevent>
                <el-form-item label="客服微信二维码" prop="qrcode_url">
                    <div style="margin-bottom:10px;">
                        <app-attachment style="display:inline-block;margin-right: 10px" :multiple="false" :max="1"
                                        @selected="wechatSelect">
                            <el-tooltip effect="dark" content="建议尺寸:360 * 360" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                    </div>
                    <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                        <app-attachment :multiple="false" :max="1" @selected="wechatSelect">
                            <app-image mode="aspectFill" width="80px" height='80px'
                                       :src="wechatForm.qrcode_url"></app-image>
                        </app-attachment>
                        <el-button v-if="wechatForm.qrcode_url" class="del-btn" size="mini" type="danger"
                                   icon="el-icon-close" circle @click="wechatClose"></el-button>
                    </div>
                </el-form-item>
                <el-form-item label="客服微信号" prop="name">
                    <el-input size="small" v-model="wechatForm.name" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="wechatVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click.native="wechatSubmit">提交</el-button>
            </div>
        </el-dialog>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                wechatVisible: false,
                index: -1,
                wechatForm: {
                    qrcode_url: '',
                    name: '',
                },
                wechatRules: {
                    qrcode_url: [
                        {required: true, message: '图片不能为空', trigger: 'blur'},
                    ]
                },

                form: {
                    payment_type: ['online_pay'],
                    send_type: ['express', 'offline']
                },
                listLoading: false,
                btnLoading: false,
                FormRules: {
                    type: [
                        {required: true, message: '规则不能为空', trigger: 'blur'},
                    ]
                },
                cs_default: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/prompt.png",
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
                        key: 'poster_bg_two',
                        icon_url: 'statics/img/mall/poster/icon-free.png',
                        title: '免费标识',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品原价',
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
                ],
                goodsComponentKey: 'head',
            };
        },
        methods: {
            wechatSelect(e) {
                if (e.length) {
                    this.wechatForm.qrcode_url = e[0].url;
                }
            },

            wechatClose() {
                this.wechatForm.qrcode_url = '';
            },
            wechatSubmit() {
                this.$refs.wechatForm.validate((valid) => {
                    if (valid) {
                        if (this.index === -1) {
                            this.form.cs_wechat.push(Object.assign({}, this.wechatForm));
                        } else {
                            this.form.cs_wechat.splice(this.index, 1, this.wechatForm);
                        }
                        this.wechatVisible = false;
                    }
                });
            },

            picClose(index) {
                this.form.cs_wechat.splice(index, 1);
            },

            addWechat() {
                this.index = -1;
                this.wechatForm = {
                    qrcode_url: '',
                    name: '',
                };
                this.wechatVisible = true
            },

            editWechat(item, index) {
                this.index = index;
                this.wechatForm = Object.assign({}, item);
                this.wechatVisible = true;
            },

            wechatPrompt(e) {
                if (e.length) {
                    this.form.cs_prompt_pic = e[0].url;
                }
            },
            wechatPromptDefault() {
                this.form.cs_prompt_pic = this.cs_default;
            },
            wechatPromptClose() {
                this.form.cs_prompt_pic = '';
            },

            wechatFlock(e) {
                if (e.length) {
                    for (let i = 0; i < e.length; i++) {
                        this.form.cs_wechat_flock_qrcode_pic.push(e[i]);
                    }
                }
            },

            wechatFlockClose(e) {
                let pic = this.form.cs_wechat_flock_qrcode_pic;
                let index = pic.indexOf(e);
                this.form.cs_wechat_flock_qrcode_pic.splice(index, 1)
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'plugin/lottery/mall/setting',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                this.$message.success(e.data.msg)
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.$message.error(e.data.msg);
                            this.btnLoading = false;
                        });
                    }
                });
            },

            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/lottery/mall/setting',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.form = e.data.data;
                            this.form.cs_wechat_qrcode_pic = this.form.cs_wechat_qrcode_pic ? this.form.cs_wechat_qrcode_pic : [];
                            this.form.cs_wechat_flock_qrcode_pic = this.form.cs_wechat_flock_qrcode_pic ? this.form.cs_wechat_flock_qrcode_pic : [];
                        }
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.$message.error(e.data.msg);
                    this.listLoading = false;
                });
            },
        },

        mounted: function () {
            this.getList();
        }
    })
</script>