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
        margin-bottom: 10px;
    }

    .form-body {
        padding: 20px 35% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        width: 100%;
        height: 100%;
        position: relative;
        min-width: 640px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .poster-button-item {
        padding: 9px 25px;
        position: absolute !important;
        bottom: -52px;
        left: 0;
    }

    .mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }

    .bg-box {
        position: relative;
        border: 1px solid #e2e3e3;
        width: 750px;
        height: 1334px;
        zoom: 0.5;
    }

    .bg-pic {
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

    .red {
        color: #ff4544;
        margin-left: 10px;
    }
    .doit {
        position: absolute;
        right: 10px;
        top: 10px;
    }

    .el-dialog {
        min-width: 600px;
    }

    .title {
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .right-button {
        position: absolute;
        top: 100%;
        left: 0;
    }
    .required-icon .el-form-item__label:before {
        content: '*';
        color: #F56C6C;
        margin-right: 4px;
    }

    .exchange-setting .el-input {
        height: 34px;
        margin: 0 10px;
        width: 200px; 
    }

    .exchange-setting .el-input input {
        height: 34px;
    }
    .reset {
        position: absolute;
        top: 1px;
        left: 90px;
    }
</style>

<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="100px" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本设置" name="first">
                    <app-setting v-model="form" :is_full_reduce="true" :is_surpport_huodao="false" :is_vip_show="true" :is_send_type="false" :is_territorial_limitation="false">
                        <el-card class="exchange-setting" style="margin-bottom: 10px">
                            <div slot="header">兑换设置</div>
                            <el-form-item label="使用说明">
                                <div flex="dir:left cross:center">
                                    <el-switch v-model="form.is_rules" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                    <el-button @click="dialogVisible = true;" type="text" style="margin-left: 20px">查看图例</el-button>
                                </div>
                                <div v-if="form.is_rules == 1" style="padding-top: 10px;">
                                    <app-rich-text  style="width: 490px;" v-model="form.rules"></app-rich-text>
                                </div>
                            </el-form-item>
                            <el-form-item>
                                <template slot='label'>
                                    <span>兑换码防刷</span>
                                    <el-tooltip effect="dark" content="在兑换中心设置某个用户在N分钟内输入N次错误冻结N小时"
                                            placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-switch v-model="form.is_anti_brush" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                <div flex="dir:left cross:center" v-if="form.is_anti_brush == 1" style="padding-top: 10px;">
                                    <div>
                                        <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="0" style="margin-left: 0;" v-model="form.anti_brush_minute">
                                            <template slot="append">分钟</template>
                                        </el-input>
                                    </div>
                                    <div>内连续输错</div>
                                    <div>
                                        <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="0" v-model="form.exchange_error">
                                            <template slot="append">次</template>
                                        </el-input>
                                    </div>
                                    <div>后，冻结该用户兑换资格</div>
                                    <div>
                                        <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="0" v-model="form.freeze_hour">
                                            <template slot="append">小时</template>
                                        </el-input>
                                    </div>
                                </div>
                            </el-form-item>
                            <el-form-item>
                                <template slot='label'>
                                    <span>限制兑换次数</span>
                                    <el-tooltip effect="dark" content="每个用户每天能成功兑换的次数"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-switch v-model="form.is_limit" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                <div flex="dir:left cross:center" v-if="form.is_limit == 1" style="padding-top: 10px;">
                                    <el-radio v-model="form.limit_user_type" label="day">每位用户每天限时兑换成功</el-radio>
                                    <div>
                                        <el-input type="number"
                                                  oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="0"
                                                  v-model="form.limit_user_num">
                                            <template slot="append">次</template>
                                        </el-input>
                                    </div>
                                </div>

                                <div flex="dir:left cross:center" v-if="form.is_limit == 1" style="padding-top: 10px;">
                                    <el-radio v-model="form.limit_user_type" label="all">每位用户永久兑换成功</el-radio>
                                    <div>
                                        <el-input type="number"
                                                  oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="0"
                                                  v-model="form.limit_user_success_num">
                                            <template slot="append">次</template>
                                        </el-input>
                                    </div>
                                </div>
                            </el-form-item>
                        </el-card>
                        <el-card class="exchange-setting" style="margin-bottom: 10px">
                            <div slot="header">跳转设置</div>
                            <el-form-item label="兑换中心跳转礼品卡开关">
                                <el-switch v-model="form.is_to_gift" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                <div style="position: relative;margin-top: 10px" v-if="form.is_to_gift == 1">
                                    <app-attachment :multiple="false" :max="1" @selected="toGiftPic">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:54*54" placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </app-attachment>
                                    <div style="margin-top: 10px;position: relative">
                                        <app-image width="100px"
                                                   height="100px"
                                                   mode="aspectFill"
                                                   :src="form.to_gift_pic">
                                            </app-image>
                                        </div>
                                        <el-button size="mini" @click="resetImg(2)" class="reset" type="primary">恢复默认</el-button>
                                    </div>
                            </el-form-item>
                            <el-form-item label="礼品卡跳转兑换中心开关">
                                    <el-switch v-model="form.is_to_exchange" :active-value="1"
                                           :inactive-value="0"></el-switch>
                                    <div style="position: relative;margin-top: 10px" v-if="form.is_to_exchange == 1">
                                        <app-attachment :multiple="false" :max="1" @selected="toExchangePic">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸:54*54" placement="top">
                                                <el-button size="mini">选择图标</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <div style="margin-top: 10px;position: relative">
                                            <app-image width="100px"
                                                       height="100px"
                                                       mode="aspectFill"
                                                       :src="form.to_exchange_pic">
                                            </app-image>
                                        </div>
                                        <el-button size="mini" @click="resetImg(1)" class="reset" type="primary">恢复默认</el-button>
                                    </div>
                            </el-form-item>
                        </el-card>
                    </app-setting>
                </el-tab-pane>
                <el-tab-pane label="自定义海报" name="second">
                    <div style="display: flex;" v-if="form.poster.bg_pic">
                        <div class="mobile-box">
                            <div class="bg-box">
                                <div class="bg-pic"
                                     :style="{'background-image':'url('+form.poster.bg_pic.url+')'}"></div>
                                <app-image v-if="form.poster.head.is_show == 1"
                                           mode="aspectFill"
                                           radius="50%"
                                           :style="{
                                                    position: 'absolute',
                                                    top: form.poster.head.top + 'px',
                                                    left: form.poster.head.left + 'px'}"
                                           :width='form.poster.head.size + ""'
                                           :height='form.poster.head.size + ""'
                                           src="statics/img/mall/poster/default_head.png">
                                </app-image>
                                <span v-if="form.poster.nickname.is_show == 1"
                                      :style="{
                                                    position: 'absolute',
                                                    top: form.poster.nickname.top + 'px',
                                                    left: form.poster.nickname.left + 'px',
                                                    fontSize: form.poster.nickname.font * 2 + 'px',
                                                    color: form.poster.nickname.color}">
                                          用户昵称
                                </span>
                                <span v-if="form.poster.exchange_prompt.is_show == 1"
                                      :style="{
                                                width: form.poster.exchange_prompt.width + 'px',
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.exchange_prompt.top + 'px',
                                                left: form.poster.exchange_prompt.left + 'px',
                                                fontSize: form.poster.exchange_prompt.font * 2 + 'px',
                                                color: form.poster.exchange_prompt.color}">
                                        {{form.poster.exchange_prompt.text}}
                                </span>
                                <span v-if="form.poster.big_title.is_show == 1"
                                      :style="{
                                                width: form.poster.big_title.width + 'px',
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.big_title.top + 'px',
                                                left: form.poster.big_title.left + 'px',
                                                fontSize: form.poster.big_title.font * 2 + 'px',
                                                color: form.poster.big_title.color}">
                                        {{form.poster.big_title.text}}
                                </span>
                                <span v-if="form.poster.big_title.is_show == 1"
                                      :style="{
                                                width: form.poster.small_title.width + 'px',
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.small_title.top + 'px',
                                                left: form.poster.small_title.left + 'px',
                                                fontSize: form.poster.small_title.font * 2 + 'px',
                                                color: form.poster.small_title.color}">
                                        {{form.poster.small_title.text}}
                                </span>
                                <span v-if="form.poster.message.is_show == 1"
                                      :style="{
                                                width: form.poster.message.width + 'px',
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.message.top + 'px',
                                                left: form.poster.message.left + 'px',
                                                fontSize: form.poster.message.font * 2 + 'px',
                                                color: form.poster.message.color}">
                                        {{form.poster.message.text}}
                                </span>
                                <app-image v-if="form.poster.qr_code.is_show == 1"
                                           mode="aspectFill"
                                           :radius="form.poster.qr_code.type == 1 ? '50%' : '0%'"
                                           :style="{
                                                    position: 'absolute',
                                                    top: form.poster.qr_code.top + 'px',
                                                    left: form.poster.qr_code.left + 'px'}"
                                           :width='form.poster.qr_code.size + ""'
                                           :height='form.poster.qr_code.size + ""'
                                           src="statics/img/mall/poster/default_qr_code.png">
                                </app-image>
                                <span v-if="form.poster.desc.is_show == 1"
                                      :style="{
                                                width: form.poster.desc.width + 'px',
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.desc.top + 'px',
                                                left: form.poster.desc.left + 'px',
                                                fontSize: form.poster.desc.font * 2 + 'px',
                                                color: form.poster.desc.color}">
                                        {{form.poster.desc.text}}
                                </span>
                                <span v-if="form.poster.code.is_show == 1"
                                      :style="{
                                                width: form.poster.code.width + 'px',
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.code.top + 'px',
                                                left: form.poster.code.left + 'px',
                                                fontSize: form.poster.code.font * 2 + 'px',
                                                color: form.poster.code.color}">
                                        {{form.poster.code.text}}
                                </span>
                                <span v-if="form.poster.valid_time.is_show == 1"
                                      :style="{
                                                wordWrap: 'break-word',
                                                wordBreak: 'normal',
                                                position: 'absolute',
                                                top: form.poster.valid_time.top + 'px',
                                                left: form.poster.valid_time.left + 'px',
                                                fontSize: form.poster.valid_time.font * 2 + 'px',
                                                color: form.poster.valid_time.color}">
                                        {{form.poster.valid_time.text}}
                                </span>
                            </div>
                        </div>
                        <div class="form-body" flex="dir:top">
                            <div flex="dir:left" style="margin-bottom: 15px">
                                <app-attachment :multiple="false" :max="1" @selected="picUrl"
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
                                <el-button style="margin-left: 10px;" @click="reset" size="mini">恢复默认</el-button>
                                <el-button v-if="form.poster.bg_pic.url" @click="removeBgPic()"
                                           style="margin-left: 10px;"
                                           type="danger"
                                           size="mini">
                                    删除背景
                                </el-button>
                            </div>
                            <div flex="wrap:wrap" style="width: 100%;">
                                <div v-for="(item,index) in component"
                                     @click="componentItemClick(index)"
                                     class="component-item"
                                     :class="componentKey == item.key ? 'active' : ''"
                                     flex="dir:top cross:center main:center">
                                    <img :src="item.icon_url">
                                    <div>{{item.title}}</div>
                                    <img v-if="test(index)"
                                         @click.stop="componentItemRemove(index)"
                                         class="component-item-remove"
                                         src="statics/img/mall/poster/icon_delete.png">
                                </div>
                            </div>
                            <el-card shadow="never" class="box-card" style="width: 100%;">
                                <div slot="header">
                                    <span v-for="(item,index) in component " v-if="componentKey == item.key">{{item.title}}设置</span>
                                </div>
                                <div>
                                    <template v-if="componentKey == 'head'">
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=40
                                                    :max=300
                                                    v-model="form.poster.head.size"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.head.size)
                                                    v-model="form.poster.head.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.head.size)
                                                    v-model="form.poster.head.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                    </template>

                                    <template v-else-if="componentKey == 'nickname'">
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=12
                                                    :max=40
                                                    v-model="form.poster.nickname.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.nickname.font)
                                                    v-model="form.poster.nickname.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.nickname.font)
                                                    v-model="form.poster.nickname.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.nickname.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>

                                    <template v-else-if="componentKey == 'qr_code'">
                                        <el-form-item label="样式">
                                            <el-radio v-model="form.poster.qr_code.type" :label="1">圆形</el-radio>
                                            <el-radio v-model="form.poster.qr_code.type" :label="2">方形</el-radio>
                                        </el-form-item>
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=30
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

                                    <template v-else-if="componentKey == 'desc'">
                                        <el-form-item label="文本内容">
                                            <el-input v-model="form.poster.desc.text"></el-input>
                                        </el-form-item>
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.desc.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.desc.font)
                                                    v-model="form.poster.desc.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.desc.font)
                                                    v-model="form.poster.desc.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="文本宽度">
                                            <el-slider
                                                    :min=1
                                                    :max=750
                                                    v-model="form.poster.desc.width"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.desc.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>

                                    <template v-else-if="componentKey == 'code'">
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.code.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.code.font)
                                                    v-model="form.poster.code.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.code.font)
                                                    v-model="form.poster.code.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="文本宽度">
                                            <el-slider
                                                    :min=1
                                                    :max=750
                                                    v-model="form.poster.code.width"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.code.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>

                                    <template v-else-if="componentKey == 'exchange_prompt'">
                                        <el-form-item label="文本内容">
                                            <el-input v-model="form.poster.exchange_prompt.text"></el-input>
                                        </el-form-item>
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.exchange_prompt.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.exchange_prompt.font)
                                                    v-model="form.poster.exchange_prompt.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.exchange_prompt.font)
                                                    v-model="form.poster.exchange_prompt.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="文本宽度">
                                            <el-slider
                                                    :min=1
                                                    :max=750
                                                    v-model="form.poster.exchange_prompt.width"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.exchange_prompt.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>

                                    <template v-else-if="componentKey == 'valid_time'">
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.valid_time.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.valid_time.font)
                                                    v-model="form.poster.valid_time.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.valid_time.font)
                                                    v-model="form.poster.valid_time.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.valid_time.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>
                                    <template v-else-if="componentKey == 'message'">
                                        <el-form-item label="文本内容">
                                            <el-input v-model="form.poster.message.text"></el-input>
                                        </el-form-item>
                                        <el-form-item label="大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.message.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.message.font)
                                                    v-model="form.poster.message.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.message.font)
                                                    v-model="form.poster.message.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="文本宽度">
                                            <el-slider
                                                    :min=1
                                                    :max=750
                                                    v-model="form.poster.message.width"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.message.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>
                                    <template v-else-if="componentKey == 'big_title'">
                                        <el-form-item label="大标题内容">
                                            <el-input v-model="form.poster.big_title.text"></el-input>
                                        </el-form-item>
                                        <el-form-item label="大标题大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.big_title.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.big_title.font)
                                                    v-model="form.poster.big_title.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.big_title.font)
                                                    v-model="form.poster.big_title.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="文本宽度">
                                            <el-slider
                                                    :min=1
                                                    :max=750
                                                    v-model="form.poster.big_title.width"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.big_title.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                        <el-form-item label="副标题内容">
                                            <el-input v-model="form.poster.small_title.text"></el-input>
                                        </el-form-item>
                                        <el-form-item label="副标题大小">
                                            <el-slider
                                                    :min=12
                                                    :max=30
                                                    v-model="form.poster.small_title.font"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                    :min=0
                                                    :max=1334-(form.poster.small_title.font)
                                                    v-model="form.poster.small_title.top"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                    :min=0
                                                    :max=750-(form.poster.small_title.font)
                                                    v-model="form.poster.small_title.left"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="文本宽度">
                                            <el-slider
                                                    :min=1
                                                    :max=750
                                                    v-model="form.poster.small_title.width"
                                                    show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                    style="margin-left: 20px;"
                                                    color-format="rgb"
                                                    v-model="form.poster.small_title.color"
                                                    :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>
                                </div>
                            </el-card>
                            <el-button class="poster-button-item" :loading="btnLoading" type="primary"
                                       @click="submit('form')" size="small">保存
                            </el-button>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button v-if="activeName == 'first'" style="margin-bottom: 150px;" :loading="btnLoading" class="button-item" type="primary" @click="submit('form')" size="small">保存</el-button>
            </el-tabs>
        </el-form>
    </el-card>
    <el-dialog title="查看使用说明图例" :visible.sync="dialogVisible" width="600px">
        <div style="border-top: 1px solid #e2e2e2;text-align: center;padding-top: 40px">
            <img src="./../plugins/exchange/assets/img/rule.png" width="240" alt="">
        </div>
        <span slot="footer" class="dialog-footer">
        <el-button type="primary" size="small" @click="dialogVisible = false">我知道了</el-button>
        </span>
    </el-dialog>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                dialogVisible: false,
                componentKey: 'head',
                default_setting: {},
                form: {
                    is_coupon: 1,
                    is_integral: 1,
                    is_member_price: 1,
                    is_share: 0,
                    is_territorial_limitation: 0,
                    svip_status: 1,
                    banner: '',
                    poster: {},
                },
                component: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'exchange_prompt',
                        icon_url: 'statics/img/mall/poster/icon_tip.png',
                        title: '送礼提示',
                        is_active: true
                    },
                    {
                        key: 'big_title',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '标题',
                        is_active: true
                    },
                    {
                        key: 'message',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '寄语',
                        is_active: true
                    },
                    {
                        key: 'code',
                        icon_url: 'statics/img/mall/poster/icon_code.png',
                        title: '兑换码',
                        is_active: true
                    },
                    {
                        key: 'valid_time',
                        icon_url: 'statics/img/mall/poster/icon_expire_time.png',
                        title: '有效时间',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                ],
                predefineColors: [
                    '#000',
                    '#fff',
                    '#888',
                    '#ff4544'
                ],
                activeName: 'first',
                default_poster: {}
            };
        },
        computed: {
            // 控制显示的内容
            test() {
                return function (index) {
                    var isShow = this.form.poster[this.component[index].key].is_show;
                    return isShow == 1 ? true : false;
                }
            }
        },
        created() {
            this.loadSetting();
        },
        methods: {
            resetImg(index) {
                if(index == 1) {
                    this.form.to_exchange_pic = this.default_setting.to_exchange_pic
                }else {
                    this.form.to_gift_pic = this.default_setting.to_gift_pic
                }
            },
            toGiftPic(e) {
                this.form.to_gift_pic = e[0].url;
            },
            toExchangePic(e) {
                this.form.to_exchange_pic = e[0].url;
            },
            // 更换背景图
            picUrl(e) {
                if (e.length) {
                    this.form.poster.bg_pic.url = e[0].url;
                }
            },
            // 移除背景图片
            removeBgPic() {
                this.form.poster.bg_pic.url = '';
            },
            // 恢复默认
            reset() {
                this.form.poster = JSON.parse(JSON.stringify(this.default_poster));
            },
            // 添加组件
            componentItemClick(index) {
                this.component[index].is_active = true;
                this.form.poster[this.component[index].key].is_show = '1';
                this.componentKey = this.component[index].key;
            },
            // 移除组件
            componentItemRemove(index) {
                this.component[index].is_active = false;
                this.form.poster[this.component[index].key].is_show = '0';
                this.componentKey = '';
            },
            async submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = JSON.parse(JSON.stringify(this.form));
                        request({
                            params: {
                                r: 'plugin/exchange/mall/setting'
                            },
                            method: 'post',
                            data: para
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success('保存成功');
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
            async loadSetting() {
               try {
                   this.loading = true;
                   const e = await request({
                       params: {
                           r: 'plugin/exchange/mall/setting'
                       },
                       method: 'get'
                   });
                   this.loading = false;
                   if (e.data.code === 0) {
                       this.form = e.data.data.setting;
                       this.default_setting = e.data.data.default_setting;
                       this.default_poster = e.data.data.default_poster;
                   }
               } catch (e) {
                   this.loading = false;
                   throw new Error(e);
               }
            },
        },
    });
</script>
