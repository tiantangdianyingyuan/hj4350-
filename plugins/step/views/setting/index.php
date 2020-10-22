<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
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
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
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

    .before {
        height: 100px;
        line-height: 100px;
        width: 100px;
        background-color: #f7f7f7;
        color: #bbbbbb;
        text-align: center;
    }
    .red {
        color: #ff4544;
    }

    .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="10rem" ref="form" :rules="rules">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础配置" name="first">
                        <app-setting v-model="form" :is_discount="false"></app-setting>
                </el-tab-pane>
                <el-tab-pane label="步数宝设置" name="three">
                    <div class="form-body">
                        <el-form-item v-if="false" label="小程序标题" prop="title">
                            <el-input size="small" v-model="form.title" autocomplete="off"></el-input>
                        </el-form-item>

                        <el-form-item prop="convert_max">
                            <template slot='label'>
                                <span>每日最高兑换数</span>
                                <el-tooltip effect="dark" content="请输入最高兑换数" placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <div flex="dir:left main:center cross:center">
                                <div style="width:100%">
                                    <el-input size="small"
                                              type="number"
                                              :disabled="form.convert_max === 0"
                                              placeholder="请输入最高兑换数"
                                              v-model.number="form.convert_max"
                                              autocomplete="off">
                                        <template slot="append">步</template>
                                    </el-input>
                                </div>
                                <el-checkbox style="margin-left: 5px;" @change="itemChecked"
                                             v-model="form.convert_max === 0"
                                >无限制
                                </el-checkbox>
                            </div>
                        </el-form-item>
                        <el-form-item prop="invite_ratio">
                            <template slot='label'>
                                <span>邀请用户加成比率</span>
                                <el-tooltip effect="dark" content="邀请概率为无限制永久增加" placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input size="small" type="number" v-model="form.invite_ratio" autocomplete="off">
                                <template slot="prepend">千分之</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="步数兑换比率" prop="convert_ratio">
                            <el-input size="small" type="number" v-model="form.convert_ratio" autocomplete="off">
                                <template slot="append">兑换1活力币</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item prop="remind_at">
                            <template slot='label'>
                                <span>兑换提醒时间</span>
                                <el-tooltip effect="dark" content="消息一天只发送一次" placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-time-select size="small" v-model="form.remind_at" placeholder="选择时间"
                                            :picker-options="{
                              start: '00:00',
                              step: '00:15',
                              end: '23:45'
                            }"></el-time-select>
                        </el-form-item>
                        <el-form-item prop="qrcode_title">
                            <template slot='label'>
                                <span>海报文字</span>
                                <el-tooltip effect="dark" content="最多显示十二个字" placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input size="small" v-model="form.qrcode_title" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="步数兑换规则说明" prop="rule">
                            <div style="width: 458px; min-height: 458px;">
                                <app-rich-text v-model="form.rule"></app-rich-text>
                            </div>
                        </el-form-item>
                        <el-form-item label="步数挑战规则说明" prop="activity_rule">
                            <div style="width: 458px; min-height: 458px;">
                                <app-rich-text v-model="form.activity_rule"></app-rich-text>
                            </div>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义设置" name="second">
                    <div class="form-body">
<!--                         <el-form-item prop="share_title">
                            <template slot='label'>
                                <span>转发标题</span>
                                <el-tooltip effect="dark" content="多个标题请换行，多个标题随机选一个标题显示" placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input type="textarea" v-model="form.share_title"></el-input>
                        </el-form-item> -->
                        <el-form-item label="活力币别名" prop="currency_name">
                            <el-input size="small" v-model="form.currency_name" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="全国纪录最多显示" prop="ranking_num">
                            <el-input size="small" v-model="form.ranking_num" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="排行榜背景" prop="ranking_pic">
                            <el-input size="small" v-model="form.ranking_pic" autocomplete="off">
                                <template slot="append">
                                    <app-attachment :multiple="true" :max="100" @selected="chooseTop">选择文件</app-attachment>
                                </template>
                            </el-input>
                            <div class="before" v-if="!form.ranking_pic">750*400</div>
                            <app-image mode="" width="100px" height="100px" v-if="form.ranking_pic" :src="form.ranking_pic"></app-image>
                        </el-form-item>
                        <el-form-item label="步数挑战背景" prop="activity_pic">
                            <el-input size="small" v-model="form.activity_pic" autocomplete="off">
                                <template slot="append">
                                    <app-attachment :multiple="true" :max="100" @selected="chooseDare">选择文件</app-attachment>
                                </template>
                            </el-input>
                            <div class="before" v-if="!form.activity_pic">750*400</div>
                            <app-image mode="" width="100px" height="100px" v-if="form.activity_pic" :src="form.activity_pic"></app-image>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="商品海报自定义" name="four" v-if="false" >
                    <app-poster
                            :rule_form="form.goods_poster"
                            :goods_component="goodsComponentGoods">
                    </app-poster>
                </el-tab-pane>
                <el-tab-pane label="分享海报自定义" name="fives">
                    <app-poster
                            :rule_form="form.step_poster"
                            :goods_component="goodsComponentStep"
                    >
                        <template v-slot:step_poster>
                            <el-form-item label="海报模板" prop="qrcode_pic">
                                <app-attachment :multiple="true" :max="100" @selected="choosePic" style="padding-bottom:10px">
                                    <el-button size="small">选择文件</el-button>
                                </app-attachment>
                                <div class="before" v-if="!qrcode_pic[0]">750*900</div>
                                <app-gallery :show-delete="true" @deleted="picDeleted" :list="qrcode_pic"></app-gallery>
                            </el-form-item>
                        </template>
                    </app-poster>
                </el-tab-pane>
                <el-tab-pane label="分享自定义" name="six">
                    <div class="form-body">
                        <el-form-item prop="share_title">
                            <label slot="label">
                                <span>自定义分享标题</span>
                                <el-tooltip class="item" effect="dark" content="分享给好友时，作为微信分享卡片标题"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input size="small" placeholder="请输入分享标题"
                                      v-model="form.share_title"></el-input>
                            <el-button @click="app_share.dialog = true;app_share.type = 'name_bg'"
                                       type="text">查看图例
                            </el-button>
                        </el-form-item>
                        <el-form-item prop="share_pic">
                            <label slot="label">
                                <span>自定义分享图片</span>
                                <el-tooltip class="item" effect="dark" content="分享给好友时，作为微信分享卡片主图"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <app-attachment v-model="form.share_pic" :multiple="false" :max="1">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <div class="customize-share-title">
                                <app-image mode="aspectFill" width='80px' height='80px'
                                           :src="form.share_pic ? form.share_pic : ''"></app-image>
                                <el-button v-if="form.share_pic" class="del-btn" size="mini"
                                           type="danger" icon="el-icon-close" circle
                                           @click="form.share_pic = ''"></el-button>
                            </div>
                            <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                                       type="text">查看图例
                            </el-button>
                        </el-form-item>
                    </div>
                </el-tab-pane>
            <el-button class="button-item" type="primary" size="small" :loading=submitLoading @click="submit">保存</el-button>
        </el-form>
    </el-card>
    <el-dialog :title="app_share.type == 'name_bg' ? '查看自定义分享标题图例':'查看自定义分享图片图例'" :visible.sync="app_share.dialog" width="30%">
        <div style="border-top: 1px solid #e2e2e2;padding-top: 30px;" flex="main:center cross:center">
            <img v-if="app_share.type == 'name_bg'" src="statics/img/plugins/name_bg.png" alt="">
            <img v-if="app_share.type == 'pic_bg'" src="statics/img/plugins/pic_bg.png" alt="">
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" type="primary" @click="app_share.dialog = false">我知道了</el-button>
        </span>
    </el-dialog>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    qrcode_pic: [],
                    payment_type: ['online_pay'],
                    send_type: ['express', 'offline']
                },
                app_share: {
                    dialog: false,
                    type: ''
                },
                loading: false,
                activeName: 'first',
                pagination: null,
                submitLoading: false,
                qrcode_pic: [],
                rules: {
                    title: [
                        {min: 0, max: 30, message: "标题长度在0-30个字符内"},
                    ],
                    convert_max: [
                        {required: true, pattern: /^[0-9]\d{0,8}$/, message: '每日最高兑换数不能为空'},
                    ],
                    convert_ratio: [
                        {required: true, pattern: /^[0-9]\d{0,8}$/, message: '步数兑换比率不能为空'},
                    ],
                    remind_at: [
                        {required: true, message: '提醒时间不能为空'},
                    ],
                    rule: [
                        {required: true, message: '步数兑换规则不能为空'},
                    ],
                    activity_rule: [
                        {required: true, message: '步数挑战规则不能为空'},
                    ]
                },
                goodsComponentGoods: [
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
                ],
                goodsComponentStep: [
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
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_step.png',
                        title: '步数',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '模板图片',
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
            };
        },
        methods: {
            itemChecked() {
                this.form.convert_max = this.form.convert_max > 0 ? 0 : 1
            },
            choosePic(e) {
                if (e.length) {
                    for(let i = 0;i < e.length;i++) {
                        this.qrcode_pic.push(e[i]);
                    }
                }
            },

            chooseTop(e) {
                if (e.length) {
                    this.form.ranking_pic = e[0].url;
                }
            },

            chooseDare(e) {
                if (e.length) {
                    this.form.activity_pic = e[0].url;
                }
            },

            picDeleted(e) {
                let pic = this.qrcode_pic;
                let index = pic.indexOf(e);
                this.qrcode_pic.splice(index,1)
            },

            getList() {
                this.loading = true;
                let para = {
                    r: 'plugin/step/mall/setting',
                }
                request({
                    params: para,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if(e.data.data) {
                            this.form = e.data.data;
                            this.qrcode_pic = this.form.qrcode_pic;
                            for(let i = 0;i < qrcode_pic.length;i++) {
                                qrcode_pic[i].url = qrcode_pic[i].pic_url;
                            }                            
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },

            submit() {
                let that = this;
                that.submitLoading = true;
                for(let i = 0;i < that.qrcode_pic.length;i++) {
                    that.qrcode_pic[i].pic_url = that.qrcode_pic[i].url;
                }
                that.form.qrcode_pic = JSON.stringify(that.qrcode_pic)
                let para = that.form;
                request({
                    params: {
                        r: 'plugin/step/mall/setting'
                    },
                    data: that.form,
                    method: 'post'
                }).then(e => {
                    that.submitLoading = false;
                    if (e.data.code === 0) {
                        that.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        setTimeout(function(){
                            that.getList();
                            that.activeName = 'first';
                        },500); 
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
        },
        created() {
            this.getList();
        }
    });
</script>
