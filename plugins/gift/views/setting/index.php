<?php defined('YII_ENV') or exit('Access Denied');
    Yii::$app->loadViewComponent('app-poster');
    Yii::$app->loadViewComponent('app-rich-text');
    Yii::$app->loadViewComponent('app-setting');
$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
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
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .red {
        color: #ff4544;
        margin-left: 10px;
    }

    .input-len input {
           width: 400px;
           height: 33px;
    }

    .input-len-i {
        width: 300px;
        height: 33px;
    }
    .input-len {
        width: 400px;
        height: 33px;
    }
    .input-len-i input {
        width: 255px;
        height: 33px;
    }

    .el-input__count-inner {
        background-color: transparent !important;
    }
    .iphone {
        min-width: 400px;
        height: 740px;
        background-color: white;
        border-radius: 23px;
        background-repeat: no-repeat;
        background-position: 50% 50%;
        position: relative;
    }
    .background-iphone {
        background-repeat: no-repeat;
        width: 300px;
        height: 400px;
        position: absolute;
        top: 178px;
        left: 50%;
        transform: translate(-50%, 0);
        background-size: 100% 100%;
    }
    .content-phone {
        width: 1250px;
        background-color: white;
        border-radius: 5px;
        margin-left: 20px;
        padding: 20px 35% 20px 20px;
    }
    .color-item {
        width: 126px;
        height: 60px;
        border: 1px solid #f0f0f0;
        margin-right: 23px;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
    }
    .color-item-active {
        border-color: #3399ff;
    }
    .color-item .img {
        width: 46px;
        height: 32px;
        margin-right: 10px;
    }
    .theme-color-template {
        width: 850px;
        height: 480px;
        margin-top: 40px;
    }
    .template-image {
        width: 252px;
        height: 474px;
        background-color: pink;
        box-shadow: 2px 2px 5px #f4f4f4;
        margin-right: 40px;
        border-radius: 5px;
    }
    .background-text {
        position: absolute;
        text-align: center;
    }
    .background-text  p {
        margin: 0;
    }
    .poster_pic {
        width: 80px;
        height: 80px;
        background-repeat: no-repeat;
        background-size:  cover;
    }
    #app {
        min-width: 1000px;
    }

    .avatar {
        width: 62px;
        height: 62px;
        position: absolute;
        top: 62px;
        left: 50%;
        transform: translateX(-50%);
    }
</style>

<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;" v-loading="loading">
        <div class="text item" style="width:100%">
            <el-form :model="form" :rules="rules" label-width="150px"  ref="form">
                <el-tabs v-model="activeName">

                    <el-tab-pane label="基本设置"  name="first">
                        <el-card style="margin-bottom: 10px">
                            <div slot="header">基础设置</div>

                            <el-form-item label="页面标题" prop="title">
                                <el-input
                                    v-model="form.title"
                                    class="input-len"
                                    maxlength="12"
                                    show-word-limit
                                >
                                </el-input>
                            </el-form-item>
                            <el-form-item label="自动退款" prop="auto_refund">
                                <label slot="label">自动退款
                                    <el-tooltip class="item" effect="dark"
                                                content="超出设置时间，礼物未领取，自动退款给买家"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <div>
                                    <el-input type="number" min="1" max="30" class="input-len-i" v-model.number="form.auto_refund">
                                        <template slot="append">天</template>
                                    </el-input>
                                    <span class="ml-24" style="margin-left: 15px;">
                                    未领取自动退款
                                    </span>
                                </div>
                            </el-form-item>
                        </el-card>
                        <el-card style="margin-bottom: 10px">
                            <div slot="header">玩法设置</div>

                            <el-form-item label="选择玩法" prop="type" >
                                <label slot="label">选择玩法
                                </label>
                                <el-checkbox-group v-model="form.type" size="mini">
                                    <el-checkbox label="direct_open" size="mini">直接送礼</el-checkbox>
                                    <el-checkbox label="time_open" size="mini">定时开奖</el-checkbox>
                                    <el-checkbox label="num_open" size="mini">满人开奖</el-checkbox>
                                </el-checkbox-group>
                            </el-form-item>

                            <el-form-item label="送礼未成功提醒" prop="auto_remind">
                                <label slot="label">送礼未成功提醒
                                    <el-tooltip class="item" effect="dark"
                                                content="超出设置时间，领取的礼物未填写地址，提醒买家礼物未成功送达"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <div>
                                    <el-input type="number"  min="1" max="30" class="input-len-i" v-model.number="form.auto_remind" @input.native="InputNumber">
                                        <template slot="append">天</template>
                                    </el-input>
                                    <span class="ml-24" style="margin-left: 15px;">
                                    未填写收货地址
                                    </span>
                                </div>
                            </el-form-item>

                            <el-form-item label="送礼祝福语" prop="bless_word">
                                <el-input
                                    type="text"
                                    placeholder="请输入内容"
                                    v-model="form.bless_word"
                                    class="input-len"
                                    maxlength="12"
                                    show-word-limit
                                >
                                </el-input>
                            </el-form-item>

                            <el-form-item label="求礼话术语" prop="ask_gift">
                                <el-input
                                    type="text"
                                    placeholder="请输入内容"
                                    v-model="form.ask_gift"
                                    class="input-len"
                                    maxlength="18"
                                    show-word-limit
                                >
                                </el-input>
                            </el-form-item>
                        </el-card>
                        <app-setting v-model="form" :is_coupon="true" :is_full_reduce="true"></app-setting>
                        <el-card style="margin-bottom: 10px">
                            <div slot="header">社交送礼规则设置</div>
                            <el-form-item label="规则说明">
                                <div style="width: 458px; min-height: 458px;">
                                    <app-rich-text v-model="form.explain"></app-rich-text>
                                </div>
                            </el-form-item>
                        </el-card>
                    </el-tab-pane>

                    <el-tab-pane label="自定义分享海报" class="form-body" name="second" style="background:none;padding:0">
                        <app-poster :rule_form="form.poster"
                                    :goods_component="goodsComponent"
                        >
                            <el-button slot="more-button" size="mini" @click="reset('poster')" style="margin-left: 10px">
                                恢复默认
                            </el-button>
                            <div slot="step_poster">
                                <el-form-item label="多个商品礼包图">
                                    <app-attachment :multiple="false" :max="1"
                                                    v-model="form.poster.pic.pic_url">
                                        <el-button>选择图片</el-button>
                                        <div class="poster_pic" :style="{backgroundImage: `url(${form.poster.pic.pic_url})`, marginTop: '10px'}"></div>
                                    </app-attachment>

                                </el-form-item>
                            </div>
                            <span slot="desc-text-slot"
                                  :style="{
                                                    wordWrap: 'break-word',
                                                    wordBreak: 'normal',
                                                    position: 'absolute',
                                                    top: '974px',
                                                    left: '50%',
                                                    transform: 'translateX(-50%)',
                                                    fontSize: '28px',
                                                    color: '#ffffff'
                                                    }">
                                7天内无人领取将自动退款
                            </span>
                        </app-poster>
                    </el-tab-pane>

                    <el-tab-pane label="自定义背景" name="third">
                        <div flex="">
                            <div class="iphone" :style="{backgroundImage: `url(${pic_url})`}">
                                <div class="background-iphone" :style="{backgroundImage: `url(${form.background.bg_pic})`}">
                                    <image class="avatar" :src="avatar_url"></image>
                                    <div class="background-text" :style="{color: form.background.color, top: `${form.background.top}px`, left: `${form.background.left}px`}">
                                        <p style="font-size: 14px;margin-bottom: 6px;line-height: 1;">打篮球的小明</p>
                                        <p style="font-size: 14px;margin-bottom: 26px;line-height: 1;">送你一份礼物，快来领取吧</p>
                                        <p style="line-height: 1;">大吉大利，送你好礼！</p>
                                    </div>
                                </div>
                            </div>
                            <div class="content-phone" flex="dir:top">
                                <div flex="dir:left" style="margin-bottom: 15px">
                                    <app-attachment :multiple="false" :max="1"
                                                    v-model="form.background.bg_pic">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    content="建议尺寸:600 * 800"
                                                    placement="top">
                                            <el-button size="mini">
                                                {{form.background.bg_pic ? '更换背景图' : '添加背景图'}}
                                            </el-button>
                                        </el-tooltip>
                                    </app-attachment>
                                    <el-button  @click="reset('background')"
                                               style="margin-left: 10px;"
                                               size="mini">
                                        恢复默认
                                    </el-button>
                                    <el-button v-if="form.background.bg_pic" @click="removeBgPic()"
                                               style="margin-left: 10px;"
                                               type="danger"
                                               size="mini">
                                        删除背景
                                    </el-button>
                                </div>
                                <el-card shadow="never" class="box-card" style="width: 100%;">
                                    <div slot="header">
                                        <span>文本设置</span>
                                    </div>
                                    <div>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=310
                                                    v-model="form.background.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=132
                                                    v-model="form.background.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    v-model="form.background.color"
                                                    color-format="rgb"
                                                    >
                                            </el-color-picker>
                                        </el-form-item>
                                    </div>
                                </el-card>
                            </div>
                        </div>
                    </el-tab-pane>

                    <el-tab-pane label="主题色自定义" name="fourth">
                        <el-card shadow="never"  style="width: 100%;padding: 39px;">
                            <div flex="dir:top" style="width: 100%;height: 100%;">
                                <div flex="dir:left" style="height: 67px; width: 100%;">
                                    <div flex="main:center cross:center" @click="activeThemeColor(index)"
                                         class="color-item"
                                         :class="{'color-item-active': item.active}"
                                         v-for="(item, index) in theme"
                                         :key="item.id"
                                    >
                                       <image class="img" :src="_currentPluginBaseUrl+'/img/'+item.pic_url+'.png'"/>
                                        <span> {{item.text}}</span>
                                    </div>
                                </div>
                                <div class="theme-color-template" flex="dir:left">
                                    <image class="template-image" :src="_currentPluginBaseUrl+'/img/'+theme[activeTemplateImage].pic_url+'-template-1'+'.png'"></image>
                                    <image class="template-image" :src="_currentPluginBaseUrl+'/img/'+theme[activeTemplateImage].pic_url+'-template-2'+'.png'"></image>
                                    <image class="template-image" :src="_currentPluginBaseUrl+'/img/'+theme[activeTemplateImage].pic_url+'-template-3'+'.png'"></image>
                                </div>
                            </div>
                        </el-card>
                    </el-tab-pane>

                </el-tabs>

                <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>

    const _currentPluginBaseUrl =  `<?=$_currentPluginBaseUrl?>`;

    const app = new Vue({
        el: '#app',

        data() {
            return {
                // tab点击
                activeName: 'first',
                //列表刷新loading
                loading: false,
                // 按钮刷新loading
                btnLoading: false,
                pic_url: _currentPluginBaseUrl + `/img/pop-ups-bg.png`,
                avatar_url: _currentPluginBaseUrl + '/img/avatar.png',
                form: {
                    // 求礼物话术语
                    ask_gift: '',
                    // 页面标题
                    title: '',
                    // 选择玩法
                    type: [
                        'direct_open',
                    ],
                    // 自动退款
                    auto_refund: 0,
                    // 送礼未成功自动提醒
                    auto_remind: 0,
                    // 打印
                    is_print: 0,
                    // 邮件
                    is_mail: 0,
                    // 短信
                    is_sms: 0,
                    // 支付方式
                    payment_type: ['online_pay'],
                    // 发货方式
                    send_type: [
                        'express'
                    ],
                    is_share: 0,
                    //送礼祝福语
                    bless_word: '',
                    // 分享海报
                    poster: {
                        // 背景图片
                        bg_pic: {
                            url: '',
                        },
                        pic: {
                            pic_url: ''
                        },
                        desc: {
                            width: 0,
                        },
                        hide_desc: {
                            is_show: 1,
                            font: 14,
                            top: 487,
                            left: 113,
                            text: '7天内无人领取将自动退款',
                            color: 'rgb(255, 255, 255)',
                            file_type: 'text',
                        }
                    },
                    // 规则
                    explain: '',
                    // 自定义背景
                    background: {
                        bg_pic: '',
                        top: 0,
                        left: 0,
                        color: `rgb(255, 255, 255)`
                    },
                    theme: {},
                    default: {
                        background: {}
                    }
                },
                // 自定义海报分类
                activeTemplateImage: 0,
                // 分享海报
                goodsComponent: [
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_nickname.png',
                        title: '送礼提示',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                ],
                // 主题色
                theme: [
                    {
                        id: 1,
                        active: true,
                        text: '流金色',
                        pic_url: 'streamer-gold'
                    },
                    {
                        id: 2,
                        active: false,
                        text: '浪漫粉',
                        pic_url: 'romantic-powder'
                    },
                    {
                        id: 3,
                        active: false,
                        text: '热情红',
                        pic_url: 'taste-red'
                    },
                    {
                        id: 4,
                        active: false,
                        text: '优雅紫',
                        pic_url: 'elegant-purple'
                    },
                    {
                        id: 5,
                        active: false,
                        text: '清新绿',
                        pic_url: 'fresh-green'
                    },
                    {
                        id: 6,
                        active: false,
                        text: '商务蓝',
                        pic_url: 'business-blue'
                    }
                ],
                rules: {
                    type: [
                        { required: true, message: '请选择玩法', trigger: 'change' },
                    ],
                    auto_remind: [
                        {
                            required: true,
                            type: 'number',
                            message: '请输入送礼未成功提醒时间',
                            trigger: 'blur' },
                        {
                            pattern: /^30$|^([1-2]\d)$|^\d?$/,
                            message: '最小1天，最大30天'
                        }

                    ],
                    auto_refund: [
                        { required: true, message: '请输入自动退款时间', trigger: 'blur',  type: 'number',},
                        {
                            pattern: /^30$|^([1-2]\d)$|^\d?$/,
                            message: '最小1天，最大30天'
                        },
                    ],
                    payment_type: [
                        { required: true, message: '请选择支付方式', trigger: 'change' },
                    ],
                    send_type: [
                        { required: true, message: '请选择发货方式', trigger: 'change' },
                    ],
                    bless_word: [
                        { required: true, message: '请输入自动送礼祝福语', trigger: 'blur' },
                    ],
                    ask_gift: [
                        { required: true, message: '请输入求礼话术语', trigger: 'blur' },
                    ],
                    title: [
                        {required: true, message: '请输入标题', trigger: 'change' }
                    ]
                },
                defaultObj: {},
            };
        },

        methods: {
            onSubmit() {
                let self = this;
                self.$refs.form.validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        for (let i = 0; i < self.theme.length; i++) {
                            if (self.theme[i].active) {
                                self.form.theme = self.theme[i];
                            }
                        }
                        let para = Object.assign({}, self.form);
                        request({
                            params: {
                                r: `plugin/gift/mall/setting/index`
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.btnLoading = false;
                        }).catch(e => {
                            self.btnLoading = false;
                        })
                    }
                });
            },
            InputNumber(v) {
            },
            removeBgPic() {
                this.form.background.bg_pic = '';
            },

            // 获取配置
            async getRequest() {
                this.loading = true;
                try {
                    const res = await request({
                        params: {
                            r: `plugin/gift/mall/setting/index`
                        },
                        method: 'get',
                    });
                    this.loading = false;
                    if (res.data.code === 0) {
                        this.form = res.data.data;
                        this.defaultObj = res.data.data.default;
                        for (let i = 0; i < this.theme.length; i++) {
                            if (this.theme[i].id == this.form.theme.id) {
                                this.activeTemplateImage = i;
                                this.theme[i].active = true;
                            } else {
                                this.theme[i].active = false;
                            }
                        }
                    } else {
                        this.$message.error(res.data.msg);
                    }
                } catch (e) {
                    this.$message.error(e);
                }
            },

            // 选取主题色
            activeThemeColor(data) {
                this.theme.map((item, index) => {
                    if (item.active) {
                        item.active = false;
                    }
                    if (data === index) {
                        item.active = true;
                        this.activeTemplateImage = index;
                    }
                });
            },

            reset(data) {
                if (data === 'background') {
                    this.form.background.bg_pic = this.defaultObj.background.bg_pic;
                    this.form.background.color = this.defaultObj.background.color;
                    this.form.background.left = this.defaultObj.background.left;
                    this.form.background.top = this.defaultObj.background.top;
                } else if (data === 'poster') {
                    let { bg_pic, desc, nickname, pic, qr_code} = this.defaultObj.poster;
                    this.form.poster.bg_pic.url = bg_pic.url;
                    let {color, file_type, font, is_show, left, text, top, width} = desc;
                    this.form.poster.desc.color = color;
                    this.form.poster.desc.file_type = file_type;
                    this.form.poster.desc.font = font;
                    this.form.poster.desc.is_show = is_show;
                    this.form.poster.desc.left = left;
                    this.form.poster.desc.text = text;
                    this.form.poster.desc.top = top;
                    this.form.poster.desc.width = width;

                    this.form.poster.nickname.color = nickname.color;
                    this.form.poster.nickname.file_type = nickname.file_type;
                    this.form.poster.nickname.font = nickname.font;
                    this.form.poster.nickname.is_show = nickname.is_show;
                    this.form.poster.nickname.left = nickname.left;
                    this.form.poster.nickname.text = nickname.text;
                    this.form.poster.nickname.top = nickname.top;

                    this.form.poster.pic.file_type = pic.file_type;
                    this.form.poster.pic.height = pic.height;
                    this.form.poster.pic.is_show = pic.is_show;
                    this.form.poster.pic.left = pic.left;
                    this.form.poster.pic.pic_url = pic.pic_url;
                    this.form.poster.pic.top = pic.top;
                    this.form.poster.pic.width = pic.width;

                    this.form.poster.qr_code.file_path = qr_code.file_path;
                    this.form.poster.qr_code.file_type = qr_code.file_type;
                    this.form.poster.qr_code.is_show = qr_code.is_show;
                    this.form.poster.qr_code.left = qr_code.left;
                    this.form.poster.qr_code.size = qr_code.size;
                    this.form.poster.qr_code.top = qr_code.top;
                    this.form.poster.qr_code.type = qr_code.type;
                }
            },

        },

        mounted() {
            this.getRequest();
        },
    })
</script>