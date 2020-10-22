<?php
defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .mobile-box {
        pointer-events: none !important;
        width: 400px;
        height: calc(800px - 55px);
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        background-size: cover;
        position: relative;
        font-size: .85rem;
        float: left;
        margin-right: 1rem;
    }

    .mobile-box .show-box {
        height: calc(667px - 55px);
        width: 375px;
        overflow: auto;
        font-size: 12px;
    }

    .menus-box .menu-item {
        cursor: move;
        background-color: #fff;
        margin: 5px 0;
    }

    .head-bar {
        width: 378px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .head-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 16px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .head-bar img {
        width: 378px;
        height: 64px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .bg-rule {
        color: #FFFFFF;
        font-size: 12px;
        text-align: center;
        background: rgba(0, 0, 0, 0.4);
        line-height: 24px;
        width: 46px;
        border-radius: 12px 0 0 12px;
        position: absolute;
        right: 0;
        top: 20px;
    }

    .bg-rule:nth-child(2) {
        top: 56px;
    }

    .preview-icon {
        position: absolute;
        height: 90%;
        top: 10%;
        left: 9%;
        right: 9%;
    }

    .pond-left {
        height: 26.5%;
        padding: 4% 4% 7%;
        width: 33%;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-tabs v-model="activeName">
            <el-tab-pane label="九宫格抽奖设置" name="setting"></el-tab-pane>
            <el-tab-pane label="背景图设置" name="bg"></el-tab-pane>
        </el-tabs>
        <el-form :model="form" label-width="150px" ref="form" size="small" v-loading="loading"
                 :rules="FormRules">
            <template v-if="activeName === 'setting'">
                <app-setting v-model="form" :is_discount="false" :is_share="false"
                             :is_territorial_limitation="false"
                ></app-setting>
                <div class="form-body">
                    <el-form-item label="中奖概率" prop="probability">
                        <el-input size="small" type="number" v-model="form.probability" autocomplete="off">
                            <template slot="prepend">万分之</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="抽奖时间" prop="time">
                        <el-date-picker v-model="form.time" unlink-panels type="datetimerange" size="small"
                                        value-format="yyyy-MM-dd HH:mm:ss" range-separator="至" start-placeholder="开始日期"
                                        end-placeholder="结束日期"
                        ></el-date-picker>
                    </el-form-item>
                    <el-form-item label="抽奖规则" prop="type">
                        <el-radio v-model="form.type" :label="1">一天{{ form.oppty }}次</el-radio>
                        <el-radio v-model="form.type" :label="2">一人{{ form.oppty }}次</el-radio>
                    </el-form-item>
                    <el-form-item label="抽奖次数" prop="oppty">
                        <el-input size="small" type="number" v-model="form.oppty" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="小程序标题" v-if="false" prop="title">
                        <el-input size="small" v-model="form.title" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="消耗积分" prop="deplete_integral_num">
                        <el-input size="small" v-model="form.deplete_integral_num" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="规则说明" prop="rule">
                        <el-input rows="15" type="textarea" v-model="form.rule" autocomplete="off"></el-input>
                    </el-form-item>
                </div>
                <el-button type="primary" class="button-item" size="small" :loading=btnLoading @click="onSubmit">保存
                </el-button>
            </template>
            <template v-if="activeName=== 'bg'">
                <div style="display: flex;">
                    <div class="mobile-box">
                        <div class="head-bar" flex="main:center cross:center">
                            <div>抽奖</div>
                        </div>
                        <div class="show-box">
                            <div style="position: relative">
                                <app-image :src="form.bg_pic" width='100%' height='240'></app-image>
                                <div class="bg-rule">分享</div>
                                <div class="bg-rule">规则</div>
                            </div>
                            <div style="line-height: 28px;font-size: 14px;margin-top: -20px;position: relative;text-align: center;z-index: 1">
                                <div style="color: #FFFFFF;padding: 0 15px;display: inline-block;background: rgba(0,0,0,0.3);border-radius: 14px">
                                    您还有<span style="color: #ffb92a">3</span>次机会
                                </div>
                            </div>
                            <div style="height: 370px;margin-top: -8px;padding-top: 20px; position: relative;"
                                 :style="{background: 'linear-gradient(' + form.bg_color +', '+ (form.bg_color_type === 'gradient' ? form.bg_gradient_color: form.bg_color) + ')'}">
                                <app-image
                                        style="margin: 0 auto"
                                        src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/pond-box.png"
                                        width='340' height='298'
                                ></app-image>
                                <div class="preview-icon">
                                    <el-col :span=8 v-for="(item,index) in prizeImg" class="pond-left">
                                        <el-image style="width:100%;height:100%;"
                                                  :src="item"
                                                  v-if="index != 4"
                                                  fit="contain"
                                        ></el-image>
                                    </el-col>
                                </div>
                                <div flex="dir:left cross:center main:center"
                                     style="font-size: 14px;color: #FFFFFF;padding: 15px 0">
                                    <div flex="dir:left cross:center">
                                        <i class="el-icon-house"></i>
                                        <span style="margin-left: 10px">回到首页</span>
                                    </div>
                                    <div flex="dir:left cross:center" style="margin-left: 90px">
                                        <span style="margin-right: 10px">我的中奖记录</span>
                                        <i class="el-icon-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div style="background: #FFFFFF;padding: 24px">
                            <el-form-item label="背景图" prop="bg_pic">
                                <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                                @selected="selectBgPic">
                                    <el-tooltip effect="dark"
                                                content="建议尺寸:750 * 480"
                                                placement="top">
                                        <el-button size="mini">选择图标</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                    <app-attachment :multiple="false" :max="1"
                                                    @selected="selectBgPic">
                                        <app-image mode="aspectFill"
                                                   width="80px"
                                                   height='80px'
                                                   :src="form.bg_pic">
                                        </app-image>
                                    </app-attachment>
                                </div>
                                <el-button size="mini" @click="resetImg('bg_pic')"
                                           style="position: absolute;top: 3px;left: 90px" type="primary">恢复默认
                                </el-button>
                            </el-form-item>
                            <el-form-item label="下半部颜色配置" prop="bg_color_type">
                                <el-radio v-model="form.bg_color_type" label="pure">纯色</el-radio>
                                <el-radio v-model="form.bg_color_type" label="gradient">渐变</el-radio>
                            </el-form-item>
                            <el-form-item label="下半部背景颜色" prop="bg_color">
                                <div flex="dir:left cross:center">
                                    <el-color-picker @change="(row) => {row == null ? form.bg_color = '#f12416' : ''}"
                                                     size="small"
                                                     v-model="form.bg_color"></el-color-picker>
                                    <el-input size="small" style="width: 80px;margin-left: 5px;"
                                              v-model="form.bg_color"></el-input>
                                </div>
                            </el-form-item>
                            <el-form-item v-if="form.bg_color_type === 'gradient'" label="下半部渐变颜色配置" prop="bg_gradient_color">
                                <div flex="dir:left cross:center">
                                    <el-color-picker
                                            @change="(row) => {row == null ? form.bg_gradient_color = '#f12416' : ''}"
                                            size="small"
                                            v-model="form.bg_gradient_color"></el-color-picker>
                                    <el-input size="small" style="width: 80px;margin-left: 5px;"
                                              v-model="form.bg_gradient_color"></el-input>
                                </div>
                            </el-form-item>
                        </div>
                        <el-button type="primary" style="margin-top: 20px" class="button-item" size="small"
                                   :loading=btnLoading @click="onSubmit">保存
                        </el-button>
                        <el-button size="small" @click="setDefault">
                            恢复默认
                        </el-button>
                    </div>
                </div>
        </el-form>
        </template>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                prizeImg: [],
                activeName: 'setting',
                loading: false,
                time: null,
                btnLoading: false,
                form: {
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    payment_type: ['online_pay'],
                    send_type: ['express', 'offline'],
                    bg_pic:  "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('pond') . '/img/pond-head.png' ?>",
                    bg_color: '#f12416',
                    bg_color_type: 'pure',
                    bg_gradient_color: '#f12416',
                },
                FormRules: {
                    probability: [
                        {required: true, message: '概率不能为空', trigger: 'blur'},
                    ],
                    oppty: [
                        {required: true, message: '抽奖次数不能为空', trigger: 'blur'},
                    ],
                    type: [
                        {required: true, message: '抽奖规则不能为空', trigger: 'blur'},
                    ],
                    time: [
                        {required: true, message: '抽奖时间不能为空', trigger: 'blur'},
                        {
                            validator(rule, value, callback, source, options) {
                                let status = value.some(item => {
                                    let date = new Date(item).getTime();
                                    if (date / 1000 > 2145974400) {
                                        return true;
                                    }
                                    return false;
                                });
                                status ? callback("时间溢出") : callback();
                            }
                        }
                    ],
                },
            };
        },

        methods: {
            selectBgPic(e) {
                if(e.length){
                    this.form.bg_pic = e.shift()['url'];
                }
            },
            resetImg(type) {
                if (type === 'bg_pic') {
                    this.form.bg_pic = "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('pond') . '/img/pond-head.png' ?>";
                }
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.form.start_at = this.form.time[0];
                        this.form.end_at = this.form.time[1];
                        this.btnLoading = true;

                        let para = Object.assign(this.form);
                        request({
                            params: {
                                r: 'plugin/pond/mall/setting/',
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

            setDefault() {
                this.resetImg('bg_pic');
                this.form.bg_color = '#f12416';
                this.form.bg_color_type = 'pure';
                this.form.bg_gradient_color = '#f12416';
            },
            getPondList() {
                request({
                    params: {
                        r: 'plugin/pond/mall/pond',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let picList = e.data.data.list;

                        this.prizeImg = [
                            picList[0].image_url,
                            picList[1].image_url,
                            picList[2].image_url,
                            picList[7].image_url,
                            '',
                            picList[3].image_url,
                            picList[6].image_url,
                            picList[5].image_url,
                            picList[4].image_url,
                        ];
                    }
                });
            },
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/pond/mall/setting',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        if (!e.data.data) {
                            return ;
                        }
                        let time = [];
                        time.unshift(e.data.data.start_at);
                        time.push(e.data.data.end_at);
                        e.data.data.time = time;
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
            this.getPondList();
        }
    })
</script>