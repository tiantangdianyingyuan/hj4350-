<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('app-plugins-banner');
?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }

    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .red {
        padding: 0 25px;
        color: #ff4544;
    }

    .block-box {
        color: #ffffff;
        cursor: pointer;
        display: inline-block;
        margin-right: 20px;
        border: 1px solid #D6D6D6;
        padding: 30px 10px 0;
        border-top-left-radius: 30px;
        border-top-right-radius: 30px;
        width: 300px;
        height: 280px;
        position: relative;
        margin-bottom: 10px;
    }

    .active {
        border: 1px solid #3399ff;
    }

    .open-css {
        width: 280px;
        height: 250px;
        position: absolute;
        bottom: 0;
        left: 10px;
        z-index: 5;
        background-color: rgba(0, 0, 0, .3);
        display: none;
    }

    .block-box:hover .open-css {
        display: block;
    }

    .block-box.active:hover .open-css {
        display: none;
    }

    .open-css .el-button {
        position: absolute;
        bottom: 125px;
        left: 0;
        right: 0;
        width: 80px;
        height: 30px;
        line-height: 30px;
        margin: 0 auto;
        padding: 0;
        text-align: center;
    }

    .block-box img {
        width: 280px;
        height: 250px;
        position: absolute;
        bottom: 0;
        left: 10px;
        z-index: 2;
    }

    .form-body {
        padding: 20px 0 10px 0;
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

    .block-box .inuse {
        position: absolute;
        left: 0;
        top: 0;
        height: 80px;
        width: 80px;
        z-index: 11;
    }
</style>

<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="loading">
        <div class="text item" style="width:100%">
            <el-form :model="form" label-width="150px" :rules="rule" ref="form">
                <el-tabs v-model="activeName">

                    <el-tab-pane label="基本设置" class="form-body" name="first">
                        <app-setting :is_full_reduce="true" v-model="form.detail" :is_coupon="true" ></app-setting>

                        <el-card>
                            <div slot="header">拼团规则设置</div>
                            <el-form-item label="活动规则">
                                <div style="width: 458px; min-height: 458px;">
                                    <app-rich-text v-model="form.detail.new_rules"></app-rich-text>
                                </div>
                            </el-form-item>
                        </el-card>
                    </el-tab-pane>

                    <el-tab-pane v-if="false" label="自定义海报" class="form-body" style="background:none;padding:0" name="second">
                        <app-poster :rule_form="form.goods_poster"
                                    :goods_component="goodsComponent"
                        ></app-poster>
                    </el-tab-pane>

                    <el-tab-pane label="轮播图" class="form-body" name="fitrst">
                        <app-plugins-banner
                            @concat_list="concat_banner_list"
                            @delete_list="delete_banner_list"
                            pic_size="建议尺寸:750 * 230"
                            :list="form.banner_list"></app-plugins-banner>
                    </el-tab-pane>

                    <el-tab-pane label="拼团广告" class="form-body" name="f">
                        <el-card style="border:0;background-color: #ffffff" shadow="never">
                            <el-form-item label="拼团广告状态" prop="is_advertisement">
                                <el-switch v-model="form.detail.is_advertisement" :active-value="1"
                                           :inactive-value="0"></el-switch>
                            </el-form-item>
                            <el-form-item label="样式选择">
                                <div @click="selectStyle(item)" v-for="item in style_1" class="block-box"
                                     :class="item.id == currentStyleId ? 'active' : ''">
                                    <img src="statics/img/mall/cat/select.png" class="inuse"
                                         v-if="item.id == currentStyleId"
                                         alt="">
                                    <img :src="item.bg_url">
                                    <div class="open-css">
                                        <el-button type="primary">启用该样式</el-button>
                                    </div>
                                </div>
                                <div @click="selectStyle(item)" v-for="item in style_2" class="block-box"
                                     :class="item.id == currentStyleId ? 'active' : ''">
                                    <img src="statics/img/mall/cat/select.png" class="inuse"
                                         v-if="item.id == currentStyleId"
                                         alt="">
                                    <img :src="item.bg_url">
                                    <div class="open-css">
                                        <el-button type="primary">启用该样式</el-button>
                                    </div>
                                </div>
                                <div @click="selectStyle(item)" v-for="item in style_3" class="block-box"
                                     :class="item.id == currentStyleId ? 'active' : ''">
                                    <img src="statics/img/mall/cat/select.png" class="inuse"
                                         v-if="item.id == currentStyleId"
                                         alt="">
                                    <img :src="item.bg_url">
                                    <div class="open-css">
                                        <el-button type="primary">启用该样式</el-button>
                                    </div>
                                </div>
                            </el-form-item>
                            <el-form-item label="板块设置">
                                <template v-if="form.detail.advertisement.list.length > 0">
                                    <el-row v-for="(item,index) in form.detail.advertisement.list"
                                            :key="index"
                                            style="margin-bottom: 15px;">
                                        <el-col :span="2" style="min-width: 100px">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸:300*600"
                                                        placement="top">
                                                <app-attachment :multiple="false" :max="1" :params="{'index':index}"
                                                                @selected="picUrl">
                                                    <el-button size="mini">选择文件</el-button>
                                                </app-attachment>
                                            </el-tooltip>
                                            <app-image width="80px" height="80px" mode="aspectFill"
                                                       style="margin-top: 10px"
                                                       :src="item.pic_url"></app-image>
                                        </el-col>
                                        <el-col :span="10">
                                            <div flex="box:last">
                                                <el-input disabled v-model="item.link_url"></el-input>
                                                <app-pick-link :params="{'index': index}" @selected="selectLinkUrl">
                                                    <el-button size="mini">选择链接</el-button>
                                                </app-pick-link>
                                            </div>
                                        </el-col>
                                    </el-row>
                                </template>
                                <template v-else>
                                    <el-tag type="danger">请先选择样式</el-tag>
                                </template>
                            </el-form-item>
                        </el-card>
                    </el-tab-pane>

                </el-tabs>
                <el-button style="margin-bottom: 150px;" class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small">
                    保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {
                    detail: {
                        id: 0,
                        mall_id: 0,
                        is_share: 0,
                        is_sms: 0,
                        is_mail: 0,
                        is_print: 0,
                        rules: [],
                        send_type: [],
                        payment_type: [],
                        advertisement: {
                            list: []
                        },
                        is_advertisement: 0
                    },
                    poster: {
                        bg_pic: {
                            url: ''
                        }
                    },
                    banner_list: [],
                },
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
                    }
                ],
                style_1: [
                    {
                        id: 1,
                        bg_url: 'statics/img/mall/home_block/1-1.png',
                        type: 1,
                        num: 1,
                    },
                    {
                        id: 2,
                        bg_url: 'statics/img/mall/home_block/1-2.png',
                        type: 1,
                        num: 2
                    },
                    {
                        id: 3,
                        bg_url: 'statics/img/mall/home_block/1-3.png',
                        type: 1,
                        num: 3
                    },
                    {
                        id: 4,
                        bg_url: 'statics/img/mall/home_block/1-4.png',
                        type: 1,
                        num: 4
                    },
                ],
                style_2: [
                    {
                        id: 5,
                        bg_url: 'statics/img/mall/home_block/2-1.png',
                        type: 2,
                        num: 2
                    },
                    {
                        id: 6,
                        bg_url: 'statics/img/mall/home_block/2-2.png',
                        type: 2,
                        num: 3
                    },
                    {
                        id: 7,
                        bg_url: 'statics/img/mall/home_block/2-3.png',
                        type: 2,
                        num: 4
                    },
                ],
                style_3: [
                    {
                        id: 8,
                        bg_url: 'statics/img/mall/home_block/3-1.png',
                        type: 3,
                        num: 4
                    }
                ],
                currentStyleId: 0,
                type: 0,
            };
        },
        methods: {
            // 合并轮播图
            concat_banner_list(data) {
                this.form.banner_list = this.form.banner_list.concat(data);
            },
            // 删除轮播图
            delete_banner_list(index) {
                this.form.banner_list.splice(index, 1);
            },
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        let bannerIds = [];
                        for (let i = 0; i < this.form.banner_list.length; i++) {
                            bannerIds.push(this.form.banner_list[i].id);
                        }
                        let {is_share, is_sms, is_mail, is_print,new_rules, is_territorial_limitation, rules, advertisement, payment_type, send_type, svip_status,
                            is_integral,
                            is_member_price,
                            is_full_reduce,
                            is_coupon} = this.form.detail;
                        advertisement.current_style_id = this.currentStyleId;
                        advertisement.type = this.type;
                        let data = {
                            // 是否开启分销0.否|1.是
                            is_share,
                            // 是否开启短信0.否|1.是
                            is_sms,
                            // 是否开启邮件0.否|1.是
                            is_mail,
                            // 是否开启打印0.否|1.是
                            is_print,
                            // 是否开启区域购买限制0.否|1.是
                            is_territorial_limitation,
                            // 拼团规则
                            rules,
                            payment_type,
                            send_type,
                            svip_status,
                            is_integral,
                            is_member_price,
                            is_coupon,
                            new_rules,
                            is_full_reduce,
                            // 海报数据 JSON格式
                            goods_poster: JSON.stringify(this.form.poster),
                            // 广告数据 JSON格式
                            advertisement: JSON.stringify(advertisement),
                            // 是否开启拼团广告
                            is_advertisement: this.form.detail.is_advertisement,
                            // 轮播图ID Array格式
                            bannerIds
                        };
                        request({
                            params: {
                                r: 'plugin/pintuan/mall/index/'
                            },
                            method: 'post',
                            data: data,
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
            async loadData() {
                try {
                    this.loading = true;
                    const e = await request({
                        params: {
                            r: 'plugin/pintuan/mall/index'
                        },
                        method: 'get'
                    });
                    this.loading = false;
                    if (e.data.code === 0) {
                        console.log(e.data.data);
                        this.currentStyleId = e.data.data.detail.advertisement.current_style_id;
                        this.form = e.data.data;
                        if (!(this.form.detail.advertisement  instanceof Object)) {
                            this.form.detail.advertisement.list = [];
                        }
                        this.is_show = true;
                    }
                } catch (e) {
                    throw new Error(e);
                }
            },
            // 添加权益
            addRules() {
                this.form.detail.rules = this.form.detail.rules ? this.form.detail.rules : [];
                this.form.detail.rules.push({
                    title: '',
                    content: '',
                })
            },
            // 删除权益
            destroyRules(index) {
                this.form.detail.rules.splice(index, 1);
            },
            // 选择样式
            selectStyle(e) {
                if (this.currentStyleId == e.id) {
                    return;
                }
                this.type = e.type;
                this.currentStyleId = e.id;
                let oldLength = this.form.detail.advertisement.list.length;
                if (oldLength > e.num) {
                    let newLength = oldLength - e.num;
                    for (let i = 0; i < newLength; i++) {
                        let len = this.form.detail.advertisement.list.length;
                        this.form.detail.advertisement.list.splice(len - 1);
                    }
                } else {
                    let newLength = e.num - oldLength;
                    for (let i = 0; i < newLength; i++) {
                        this.form.detail.advertisement.list.push({
                            pic_url: '',
                            link_url: '',
                        })
                    }
                }
            },
            // 选择链接
            selectLinkUrl(e, params) {
                let self = this;
                e.forEach(function (item, index) {
                    self.form.detail.advertisement.list[params.index].link_url = item.new_link_url;
                    self.form.detail.advertisement.list[params.index].open_type = item.open_type;
                });
            },
            // 选择图片
            picUrl(e, params) {
                if (e.length) {
                    this.form.detail.advertisement.list[params.index].pic_url = e[0].url;
                }
            },
        },

        created() {
            this.loadData();
        },
    })
</script>