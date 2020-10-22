<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/6
 * Time: 16:03
 */
?>
<style>
    .diy-user-label .el-form-item__label {
        width: 4.5rem!important;
    }

    .diy-user-label>.el-form-item__label {
        width: 6rem!important;
    }

    .diy-user-label .el-form-item__content {
        margin-left: 4.5rem!important;
    }

    .diy-user-label>.el-form-item__content {
        margin-left: 7rem!important;
    }

    .diy-del-btn.el-button--mini.is-circle {
        position: absolute;
        top: -16px;
        right: -16px;
        padding: 8px;
    }

    .diy-reset {
        position: absolute;
        top: 7px;
        left: 90px;
    }

    .diy-buy-user-info>div {
        position: relative;
        width: 750px;
        height: 120px;
        margin: 0 auto;
    }

    .diy-buy-user-info .buy-bg {
        height: 120px;
        width: 702px;
        margin: 0 24px;
        border-radius: 16px;
    }

    .diy-buy-user-info .buy-logo {
        width: 60px;
        height: 60px;
        position: absolute;
        z-index: 5;
        left: 42px;
        top: 30px;
    }

    .diy-buy-user-info .buy-big {
        position: absolute;
        z-index: 5;
        left: 120px;
        top: 26px;
        font-size: 26px;
        color: #D0B8A5;
    }

    .diy-buy-user-info .buy-small {
        position: absolute;
        z-index: 5;
        left: 120px;
        font-size: 16px;
        bottom: 24px;
        color: #C09878;
    }

    .diy-buy-user-info .buy-btn {
        position: absolute;
        right: 42px;
        top: 34px;
        width: 140px;
        height: 52px;
        line-height: 52px;
        border-radius: 26px;
        text-align: center;
        z-index: 5;
        font-size: 24px;
        color: #5A4D40;
    }

    .diy-buy-user-info .buy-btn.default {
        background: linear-gradient(to right,#fbdec7,#f3bf95);
    }

    .diy-color-picker {
        margin-left: 10px;
    }

</style>
<template id="diy-vip-card">
    <div class="diy-vip-card">
        <div v-loading="!data" class="diy-component-preview">
            <div class="diy-buy-user-info" :style="{'background-color':background,'padding': top_bottom_padding + `px 0`,'height': top_bottom_padding*2 + 120 +'px'}">
                <div>
                    <img class="buy-bg" :src="buy_bg" alt="">
                    <img class="buy-logo" src="statics/img/app/vip_card/logo.png" alt="">
                    <div class="buy-big" :style="{'color':data.buy_big_color}">{{data.buy_big}}</div>
                    <div class="buy-small" :style="{'color':data.buy_small_color}">{{data.buy_small}}</div>
                    <div :class="data.buy_btn_bg_color ? 'buy-btn' : 'buy-btn default'" :style="{'background-color': data.buy_btn_bg_color ? data.buy_btn_bg_color : '','color':data.buy_btn_color}">{{data.buy_btn_text}}</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form v-loading="loading" @submit.native.prevent label-width="100px">
                <el-form-item label="背景色">
                    <el-color-picker @change="chooseColor" class="diy-color-picker" size="small" v-model="background"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="background"></el-input>
                </el-form-item>
                <el-form-item label="上下间距">
                    <el-input @change="chooseHeight" size="small" v-model="top_bottom_padding">
                        <template slot="append">px</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="使用插件配置">
                    <el-switch v-model="usePluginConfig" @change="reset"></el-switch>
                </el-form-item>
                <template v-if="!usePluginConfig">
                    <el-form label-position="left">
                        <el-form-item class="diy-user-label" label="未购买用户" prop="buy_user">
                            <el-form-item label="背景图" prop="buy_bg">
                                <div style="position: relative">
                                    <app-attachment :multiple="false" :max="1" @selected="buyPicUrl">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*120" placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </app-attachment>
                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                        <app-image width="80px"
                                                   height="80px"
                                                   mode="aspectFill"
                                                   :src="buy_bg">
                                        </app-image>
                                        <el-button v-if="buy_bg != ''" class="diy-del-btn" @click="resetImg(1,'buy')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                    </div>
                                    <el-button size="mini" @click="resetImg(2,'buy')" class="diy-reset" type="primary">恢复默认</el-button>
                                </div>
                            </el-form-item>
                            <el-form-item label="文字" prop="buy_text">
                                <div flex="dir:left cross:center">
                                    <el-input maxlength="15" style="margin-left: 10px;width: 240px" size="small" v-model="data.buy_big" placeholder="大标题（字符限制15）"></el-input>
                                    <el-color-picker class="diy-color-picker" size="small" v-model="data.buy_big_color"></el-color-picker>
                                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.buy_big_color"></el-input>
                                </div>
                                <div flex="dir:left cross:center">
                                    <el-input maxlength="10" style="margin-left: 10px;width: 240px" size="small" v-model="data.buy_small" placeholder="小标题（字符限制10）"></el-input>
                                    <el-color-picker class="diy-color-picker" size="small" v-model="data.buy_small_color"></el-color-picker>
                                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.buy_small_color"></el-input>
                                </div>
                            </el-form-item>
                            <el-form-item label="按钮颜色" prop="buy_btn_bg_color">
                                <el-color-picker class="diy-color-picker" size="small" v-model="data.buy_btn_bg_color"></el-color-picker>
                                <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.buy_btn_bg_color"></el-input>
                            </el-form-item>
                            <el-form-item label="按钮文字" prop="buy_btn_text">
                                <div flex="dir:left cross:center">
                                    <el-input maxlength="4" style="margin-left: 10px;width: 240px" size="small" v-model="data.buy_btn_text" placeholder="大标题（字符限制4）"></el-input>
                                    <el-color-picker class="diy-color-picker" size="small" v-model="data.buy_btn_color"></el-color-picker>
                                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.buy_btn_color"></el-input>
                                </div>
                            </el-form-item>
                        </el-form-item>
                    </el-form>
                    <el-form label-position="left">
                        <el-form-item class="diy-user-label" label="已购买用户" prop="renew_user">
                            <el-form-item label="背景图" prop="renew_bg">
                                <div style="position: relative">
                                    <app-attachment :multiple="false" :max="1" @selected="renewPicUrl">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:702*120" placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </app-attachment>
                                    <div style="margin: 10px 0;position: relative;width: 80px;">
                                        <app-image width="80px"
                                                   height="80px"
                                                   mode="aspectFill"
                                                   :src="renew_bg">
                                        </app-image>
                                        <el-button v-if="renew_bg != ''" class="diy-del-btn" @click="resetImg(1,'renew')" size="mini" type="danger" icon="el-icon-close" circle></el-button>
                                    </div>
                                    <el-button size="mini" @click="resetImg(2,'renew')" class="diy-reset" type="primary">恢复默认</el-button>
                                </div>
                            </el-form-item>
                            <el-form-item label="文字" prop="renew_text">
                                <div flex="dir:left cross:center">
                                    <el-input maxlength="15" style="margin-left: 10px;width: 240px" size="small" v-model="data.renew_text" placeholder="标题（字符限制15）"></el-input>
                                    <el-color-picker class="diy-color-picker" size="small" v-model="data.renew_text_color"></el-color-picker>
                                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.renew_text_color"></el-input>
                                </div>
                            </el-form-item>
                            <el-form-item label="按钮颜色" prop="renew_btn_bg_color">
                                <el-color-picker class="diy-color-picker" size="small" v-model="data.renew_btn_bg_color"></el-color-picker>
                                <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.renew_btn_bg_color"></el-input>
                            </el-form-item>
                        </el-form-item>
                    </el-form>
                </template>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-vip-card', {
        template: '#diy-vip-card',
        props: {
            value: Object,
        },
        data() {
            return {
                loading: false,
                usePluginConfig: false,
                default: {
                    buy_bg: 'statics/img/app/vip_card/buy_bg.png',
                    renew_bg: 'statics/img/app/vip_card/buy_bg.png',
                    buy_big_color: '#D0B8A5',
                    buy_small_color: '#C09878',
                    buy_btn_color: '#5A4D40',
                    buy_btn_bg_color: '',
                    buy_big: '开通超级会员，立省更多',
                    buy_small: '超值全场8.8折！',
                    buy_btn_text: '立即开通',
                    renew_text_color: '#D0B8A5',
                    renew_btn_bg_color: '',
                    renew_text: 'SVIP会员优享9.5折，全场包邮',
                },
                form: {},
                background: '#FFFFFF',
                top_bottom_padding: 0,
                data: {
                    background: '#FFFFFF',
                    usePluginConfig: true,
                    top_bottom_padding: 0,
                    buy_bg: '',
                    renew_bg: '',
                    buy_big_color: '',
                    buy_small_color: '',
                    buy_btn_color: '',
                    buy_btn_bg_color: '',
                    buy_big: '',
                    buy_small: '',
                    buy_btn_text: '',
                    renew_text_color: '',
                    renew_btn_bg_color: '',
                    renew_text: '',
                },
                buy_bg: '',
                renew_bg: '',
                plugin: {
                    buy_bg: 'statics/img/app/vip_card/buy_bg.png',
                    renew_bg: 'statics/img/app/vip_card/buy_bg.png',
                    buy_big_color: '#D0B8A5',
                    buy_small_color: '#C09878',
                    buy_btn_color: '#5A4D40',
                    buy_btn_bg_color: '',
                    buy_big: '开通超级会员，立省更多',
                    buy_small: '超值全场8.8折！',
                    buy_btn_text: '立即开通',
                    renew_text_color: '#D0B8A5',
                    renew_btn_bg_color: '',
                    renew_text: 'SVIP会员优享9.5折，全场包邮',
                },
            };
        },
        created() {
            this.loadData();
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
                this.reset();
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
                this.buy_bg = this.data.buy_bg;
                this.usePluginConfig = this.data.usePluginConfig;
                this.renew_bg = this.data.renew_bg;
                this.background = this.data.background;
                this.top_bottom_padding = this.data.top_bottom_padding;
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            chooseColor() {
                this.data.background = this.background;
            },
            chooseHeight() {
                this.data.top_bottom_padding = this.top_bottom_padding;
            },
            reset() {
                let that = this;
                if(that.usePluginConfig) {
                    that.data = that.plugin;
                    that.data.usePluginConfig = that.usePluginConfig;
                    that.buy_bg = that.plugin.buy_bg;
                    that.renew_bg = that.plugin.renew_bg;
                    that.data.background = that.background;
                    that.data.top_bottom_padding = that.top_bottom_padding;
                }else {
                    that.data = that.default;
                    that.data.usePluginConfig = that.usePluginConfig;
                    that.buy_bg = 'statics/img/app/vip_card/buy_bg.png';
                    that.renew_bg = 'statics/img/app/vip_card/buy_bg.png';
                    that.data.background = that.background;
                    that.data.top_bottom_padding = that.top_bottom_padding;
                    that.data.background = that.background;
                    that.data.top_bottom_padding = that.top_bottom_padding;
                }
            },

            loadData() {
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/setting/index'
                    },
                    method: 'get'
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        if(e.data.data.setting != "") {
                            that.plugin = e.data.data.setting.form
                        }
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
            resetImg(res,position) {
                switch(position) {
                     case 'buy':
                        if(res == 2) {
                            this.buy_bg = 'statics/img/app/vip_card/buy_bg.png';
                            this.data.buy_bg = 'statics/img/app/vip_card/buy_bg.png';
                        }else {
                            this.buy_bg = '';
                            this.data.buy_bg = '';
                        }
                        break;
                     case 'renew':
                        if(res == 2) {
                            this.renew_bg = 'statics/img/app/vip_card/buy_bg.png';
                            this.data.renew_bg = 'statics/img/app/vip_card/buy_bg.png';
                        }else {
                            this.renew_bg = '';
                            this.data.renew_bg = '';
                        }
                        break;
                }
            },
            buyPicUrl(e) {
                this.data.buy_bg = e[0].url;
                this.buy_bg = e[0].url;
            },
            renewPicUrl(e) {
                this.data.renew_bg = e[0].url;
                this.renew_bg = e[0].url;
            },
        }
    });
</script>
