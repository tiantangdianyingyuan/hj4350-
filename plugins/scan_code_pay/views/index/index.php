<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('goods/app-goods-share');
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        box-shadow:0 2px 12px 0 rgba(0,0,0,.1);
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .before {
        height: 100px;
        line-height: 100px;
        width: 100px;
        background-color: #f7f7f7;
        color: #bbbbbb;
        text-align: center;
    }

    .red {
        display: inline-block;
        /*padding:0 25px;*/
        color: #ff4544;
    }

    .poster-mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }

    .poster-bg-box {
        position: relative;
        border: 1px solid #e2e3e3;
        width: 750px;
        height: 1334px;
        overflow: hidden;
        zoom: 0.5;
    }

    .poster-bg-pic {
        width: 100%;
        height: 100%;
        background-size: 100% 100%;
        background-position: center;
    }

    .title {
        padding: 15px 0;
        background-color: #f7f7f7;
        margin-bottom: 10px;
    }

    .component-item {
        width: 100px;
        height: 100px;
        cursor: pointer;
        position: relative;
        padding: 10px 0;
        border: 1px solid #e2e2e2;
        margin-right: 15px;
        margin-top: 15px;
        border-radius: 5px;
    }

    .component-item.active {
        border: 1px solid #7BBDFC;
    }

    .component-item-remove {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
        width: 28px;
        height: 28px;
    }

    .component-attributes-box {
        color: #ff4544;
    }

    .box-card {
        margin-top: 35px;
    }

    .poster-form-body {
        padding: 20px 20% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        width: 100%;
        height: 100%;
        position: relative;
        min-width: 640px;
    }

    .poster-button-item {
        padding: 9px 25px;
        position: absolute !important;
        bottom: -52px;
        left: 0;
    }

    .el-card, .el-tabs__content {
        overflow: visible;
    }

    .poster-del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="10rem" ref="form" :rules="rule">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础配置" name="first">
                    <div class="form-body">
                        <el-form-item label="是否开启当面付" prop="is_scan_code_pay">
                            <el-switch v-model="form.is_scan_code_pay" :active-value="1"
                                       :inactive-value="0"></el-switch>
                        </el-form-item>

                    </div>
                    <app-setting v-model="form" :is_territorial_limitation="false"
                                 :is_discount="false"
                                 :is_print="false" :is_send_type="false" :is_surpport_huodao="false"></app-setting>
                </el-tab-pane>
                <el-tab-pane v-if="is_show_share && form.is_share" label="分销设置" name="second">
                    <div class="form-body">
                        <el-row>
                            <el-col :span="24">
                                <template v-if="form.is_share">
                                    <el-form-item label="分销类型" prop="share_type">
                                        <el-radio v-model="goods.share_type" :label="0">固定金额</el-radio>
                                        <el-radio v-model="goods.share_type" :label="1">百分比</el-radio>
                                    </el-form-item>
                                    <app-goods-share v-model="goods"
                                                     :is_mch="1"
                                                     :attr-groups="goods.attr_groups"
                                                     :attr_setting_type="goods.attr_setting_type"
                                                     :share_type="goods.share_type"
                                                     :use_attr="goods.use_attr">
                                    </app-goods-share>
                                    <!--                                    <el-form-item label="一级佣金" prop="share_commission_first">-->
                                    <!--                                        <el-input v-model="form.share_commission_first" type="number">-->
                                    <!--                                            <template slot="append">{{form.share_type == 1 ? '%' : '元'}}</template>-->
                                    <!--                                        </el-input>-->
                                    <!--                                    </el-form-item>-->
                                    <!--                                    <el-form-item label="二级佣金" prop="share_commission_second">-->
                                    <!--                                        <el-input v-model="form.share_commission_second" type="number">-->
                                    <!--                                            <template slot="append">{{form.share_type == 1 ? '%' : '元'}}</template>-->
                                    <!--                                        </el-input>-->
                                    <!--                                    </el-form-item>-->
                                    <!--                                    <el-form-item label="三级佣金" prop="share_commission_third">-->
                                    <!--                                        <el-input v-model="form.share_commission_third" type="number">-->
                                    <!--                                            <template slot="append">{{form.share_type == 1 ? '%' : '元'}}</template>-->
                                    <!--                                        </el-input>-->
                                    <!--                                    </el-form-item>-->
                                </template>
                            </el-col>
                        </el-row>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义海报" name="three">
                    <div class="form-body" style="background:none;padding:0" flex="dir:left">
                        <div class="poster-mobile-box" flex="dir:top">
                            <div class="poster-bg-box">
                                <div class="poster-bg-pic"
                                     :style="{'background-image':'url('+form.poster.bg_pic.url+')'}">
                                </div>

                                <app-image v-if="form.poster.qr_code && form.poster.qr_code.is_show == 1"
                                           :radius="form.poster.qr_code.type == 1 ? '50%' : '0%'"
                                           :style="{
                                         position: 'absolute',
                                         width: form.poster.qr_code.size + 'px',
                                         height: form.poster.qr_code.size + 'px',
                                         top: form.poster.qr_code.top + 'px',
                                         left: form.poster.qr_code.left + 'px'}"
                                           src="statics/img/mall/poster/default_qr_code.png">
                                </app-image>
                            </div>
                        </div>
                        <div class="poster-form-body">
                            <div flex="dir:left" style="margin-bottom: 15px">
                                <app-attachment :multiple="false" :max="1"
                                                v-model="form.poster.bg_pic.url">
                                    <el-tooltip class="item"
                                                effect="dark"
                                                content="建议尺寸:750 * 1334"
                                                placement="top">
                                        <el-button size="mini">
                                            {{form.poster.bg_pic.url ? '更换背景图' : '添加背景图'}}
                                        </el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <el-button v-if="form.poster.bg_pic.url" @click="removeBgPic()"
                                           style="margin-left: 10px;"
                                           type="danger"
                                           size="mini">
                                    删除背景
                                </el-button>
                            </div>
                            <div flex="wrap:wrap">
                                <div v-for="(item,index) in components"
                                     @click="componentItemClick(index)"
                                     class="component-item"
                                     :class="components_key == item.key ? 'active' : ''"
                                     flex="dir:top cross:center main:center">
                                    <img :src="item.icon_url">
                                    <div>{{item.title}}</div>
                                    <img v-if="test2(index)"
                                         @click.stop="componentItemRemove(index)"
                                         class="component-item-remove"
                                         src="statics/img/mall/poster/icon_delete.png">
                                </div>
                            </div>
                            <el-card shadow="never" class="box-card" style="width: 100%;">
                                <div slot="header">
                                    <span>{{title_desc}}设置</span>
                                </div>
                                <div>
                                    <template if="component_key == 'qr_code'">
                                        <el-form-item label="样式">
                                            <el-radio v-model="form.poster.qr_code.type" :label="1">圆形</el-radio>
                                            <el-radio v-model="form.poster.qr_code.type" :label="2">方形</el-radio>
                                        </el-form-item>
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=35
                                                    :max=300
                                                    v-model="form.poster.qr_code.size"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.qr_code.size)
                                                    v-model="form.poster.qr_code.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.qr_code.size)
                                                    v-model="form.poster.qr_code.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                    </template>
                                </div>
                            </el-card>
                            <el-card shadow="never" class="box-card" style="width: 100%;">
                                <div slot="header">
                                    <span>选择生成海报平台</span>
                                </div>
                                <el-radio-group v-model="platform.name">
                                <el-radio style="margin-bottom: 30px;" v-for="(item, index) in platform.list" :key="index" :label="item.value">{{item.label}}</el-radio>
                            </el-radio-group>
                            </el-card>
                            <el-button :loading="downloading" style="margin-top: 20px;" size="small"
                                       @click="downloadPoster">下载海报
                            </el-button>
                            <span style='color: #ff4544;'>请先保存设置，再下载最新海报</span>
                        </div>
                    </div>
                </el-tab-pane>
                <el-button :loading="btnLoading" class="button-item" type="primary" @click="store('form')" size="small">
                    保存
                </el-button>
            </el-tabs>
        </el-form>
        <el-dialog center title="海报" :visible.sync="dialogVisible">
            <div v-loading="dialogLoading" flex="main:center">
                <app-image v-if="posterImgUrl" width="375" height="667" :src="posterImgUrl"></app-image>
            </div>
            <span slot="footer" class="dialog-footer">
    <el-button v-if="posterImgUrl" type="primary" @click="down">保存</el-button>
  </span>
        </el-dialog>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                downloading: false,
                form: {
                    poster: {
                        bg_pic: {},
                        qr_code: {},
                    },
                },
                activeName: 'first',
                rule: {},
                components: [
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                ],
                components_key: null,
                title_desc: '',
                is_show_share: 1,
                dialogVisible: false,
                posterImgUrl: "",
                dialogLoading: false,
                goods: {},
                platform: {
                    list: [],
                    name: 'wxapp',
                }
            };
        },
        computed: {
            // 控制显示的内容
            test2() {
                return function (index) {
                    var info = this.form.poster[this.components[index].key];
                    if (info && info.is_show == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.form.goods = JSON.stringify(this.goods);
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/scan_code_pay/mall/index/'
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
                        console.log('error submit!!');
                        return false;
                    }
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/index'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.form = e.data.data.setting;
                        let permissions = e.data.data.permissions;
                        let sign = false;
                        permissions.forEach(function (item, index) {
                            if (item == 'share') {
                                sign = true;
                            }
                        })
                        this.is_show_share = sign;
                        this.goods = e.data.data.goods;
                        this.platform.list = e.data.data.platform_list;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 移除背景图片
            removeBgPic() {
                this.form.poster.bg_pic.url = '';
            },
            // 添加组件
            componentItemClick(index) {
                this.components[index].is_active = true;
                this.form.poster[this.components[index].key].is_show = '1';
                this.components_key = this.components[index].key;
                this.title_desc = this.components[index].title;
            },
            // 移除组件
            componentItemRemove(index) {
                this.components[index].is_active = false;
                this.form.poster[this.components[index].key].is_show = '0';
                this.components_key = '';
            },
            downloadPoster() {
                this.dialogVisible = true;
                this.dialogLoading = true;
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/index/download-poster',
                        platform: this.platform.name,
                    },
                    method: 'get'
                }).then(e => {
                    this.dialogLoading = false;
                    if (e.data.code == 0) {
                        this.posterImgUrl = e.data.data.url
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            down() {
                var alink = document.createElement("a");
                alink.href = this.posterImgUrl;
                alink.download = '当面付海报';
                alink.click();
            }
        },
        created() {
            this.loadData();
        },
    });
</script>
