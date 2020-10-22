<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/25
 * Time: 16:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
?>
<style>
    .diy-link {
        width: 100%;
        padding: 30px 0;
        position: relative;
    }

    .diy-link > div {
        width: 100%;
    }

    .diy-link .title {
        flex-grow: 1;
        padding: 0 24px;
    }

    .diy-link .title>div {
        display: inline-block;
        vertical-align: middle;
    }

    .diy-link .arrow {
        width: 12px;
        height: 22px;
        position: absolute;
        right: 24px;
        top: 50%;
        margin-top: -11px;
    }

    .diy-component-edit .link-page .el-input-group__append {
        background-color: #fff
    }

    .diy-link .big-style {
        height: 50px;
        line-height: 50px;
        font-size: 36px;
    }

    .diy-link .big-style .title-icon {
        width: 50px;
        height: 50px;
        margin-right: 16px;
        vertical-align: middle;
    }

    .diy-link .medium-style {
        height: 36px;
        line-height: 36px;
        font-size: 28px;
    }

    .diy-link .medium-style .title-icon {
        width: 36px;
        height: 36px;
        margin-right: 16px;
        vertical-align: middle;
    }

    .diy-link .small-style {
        height: 28px;
        line-height: 28px;
        font-size: 24px;
    }

    .diy-link .small-style .title-icon {
        width: 28px;
        height: 28px;
        margin-right: 16px;
        vertical-align: middle;
    }

    .title-line {
        width: 82px;
        height: 5px;
        vertical-align: middle;
        margin: 0 20px;
    }

    .title-line.star {
        height: 20px;
        width: 28px;
    }

    .title-line.star .top-icon {
        width: 28px;
        height: 4px;
        margin-bottom: 12px;
    }

    .title-line.star .bottom-icon {
        width: 28px;
        height: 4px;
    }

    .title-line.div {
        width: 8px;
        height: 28px;
        margin: 0 10px 0 0;
    }

    .title-line.radius {
        width: 28px;
        height: 28px;
        margin: 0 10px 0 0;
        border-radius: 14px;
        border: 2px solid #353535;
    }
</style>
<template id="diy-link">
    <div>
        <div class="diy-component-preview">
            <div class="diy-link" :style="cStyle" flex="cross:center">
                <div :class="style" flex="dir:left cross:center">
                    <div v-if="data.style == 1" class="title" :style="{textAlign: data.position}">
                        <img v-if="data.picSwitch" class="title-icon" :src="data.picUrl" alt="">
                        <div style="font-weight: 600">{{data.title?data.title:'这里是标题示例'}}</div>
                    </div>
                    <div v-else-if="data.style == 2" class="title" style="text-align: center">
                        <img :style="{backgroundColor: data.styleColor}" class="title-line" src="<?= $pluginUrl ?>/images/line-style.png">
                        <div style="font-weight: 600">{{data.title?data.title:'这里是标题示例'}}</div>
                        <img :style="{backgroundColor: data.styleColor}" class="title-line" src="<?= $pluginUrl ?>/images/line-style.png">
                    </div>
                    <div v-else-if="data.style == 3" class="title" style="text-align: center">
                        <div class="title-line star">
                            <div :style="{backgroundColor: data.styleColor}" class="top-icon"></div>
                            <div :style="{backgroundColor: data.styleColor}" class="bottom-icon"></div>
                        </div>
                        <div style="font-weight: 600">{{data.title?data.title:'这里是标题示例'}}</div>
                        <div class="title-line star">
                            <div :style="{backgroundColor: data.styleColor}" class="top-icon"></div>
                            <div :style="{backgroundColor: data.styleColor}" class="bottom-icon"></div>
                        </div>
                    </div>
                    <div v-else-if="data.style == 4" class="title" style="text-align: left">
                        <img :style="{backgroundColor: data.styleColor}" class="title-line div" src="<?= $pluginUrl ?>/images/div-style.png">
                        <div style="font-weight: 600">{{data.title?data.title:'这里是标题示例'}}</div>
                    </div>
                    <div v-else-if="data.style == 5" class="title" style="text-align: left">
                        <div :style="[{'borderColor':data.styleColor,'backgroundColor':data.background}]" class="title-line radius"></div>
                        <div style="font-weight: 600">{{data.title?data.title:'这里是标题示例'}}</div>
                    </div>
                    <div v-if="data.arrowsSwitch" style="font-size: 16px;color: #999;padding-right: 45px;">更多</div>
                    <img class="arrow" src="<?= $pluginUrl ?>/images/icon-jiantou-r.png" v-if="data.arrowsSwitch">
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="标题">
                    <label slot="label">标题
                        <el-tooltip class="item" effect="dark"
                                    content="标题长度不超过10个字"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-input size="small" v-model="data.title" maxlength="10"></el-input>
                </el-form-item>
                <el-form-item label="字体大小">
                    <app-radio v-model="data.fontSize" label="36">大</app-radio>
                    <app-radio v-model="data.fontSize" label="28">中</app-radio>
                    <app-radio v-model="data.fontSize" label="24">小</app-radio>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker size="small" v-model="data.color"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.color"></el-input>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker size="small" v-model="data.background"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.background"></el-input>
                </el-form-item>
                <el-form-item label="标题样式">
                    <app-radio v-model="data.style" label="1">样式1</app-radio>
                    <app-radio v-model="data.style" label="2">样式2</app-radio>
                    <app-radio v-model="data.style" label="3">样式3</app-radio>
                    <app-radio v-model="data.style" label="4">样式4</app-radio>
                    <app-radio v-model="data.style" label="5">样式5</app-radio>
                </el-form-item>
                <el-form-item v-if="data.style == '1'" label="标题位置">
                    <app-radio v-model="data.position" label="left">居左</app-radio>
                    <app-radio v-model="data.position" label="center">居中</app-radio>
                    <app-radio v-model="data.position" label="right">居右</app-radio>
                </el-form-item>
                <el-form-item v-if="data.style != '1'" label="样式颜色">
                    <el-color-picker v-model="data.styleColor"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.styleColor"></el-input>
                </el-form-item>
                <el-form-item label="链接页面">
                    <el-input size="small" class="link-page" v-model="data.link.name" :disabled="true">
                        <template slot="append">
                            <app-pick-link @selected="selectLink">
                                <el-button>选择链接</el-button>
                            </app-pick-link>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item v-if="data.style == '1'" label="图标开关">
                    <el-switch v-model="data.picSwitch"></el-switch>
                </el-form-item>
                <template v-if="data.picSwitch">
                    <el-form-item label="图标">
                        <label slot="label">图标
                            <el-tooltip class="item" effect="dark"
                                        content="最大宽度750px，最大高度36px，图标等比例缩放"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <app-attachment title="选择图标" :multiple="false" :max="1" type="image"
                                        @selected="pickPicUrl">
                            <el-button size="mini">选择图标</el-button>
                        </app-attachment>
                        <app-gallery :list="[{url:data.picUrl}]" :show-delete="false"
                                     @deleted="deletePic('picUrl')"></app-gallery>
                    </el-form-item>
                </template>
                <el-form-item label="箭头开关">
                    <el-switch v-model="data.arrowsSwitch"></el-switch>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-link', {
        template: '#diy-link',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    title: '',
                    style: '1',
                    fontSize: '36',
                    position: 'left',
                    styleColor: '#353535',
                    link: {},
                    picSwitch: false,
                    arrowsSwitch: false,
                    picUrl: '',
                    color: '#353535',
                    background: '#ffffff'
                }
            }
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
                if (!this.data.link) {
                    this.data.link = {};
                }
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
        },
        computed: {
            cStyle() {
                if(this.data.background) {
                    return `color: ${this.data.color};`
                        + `background: ${this.data.background};`
                }else {
                    return `color: ${this.data.color};`
                }
            },
            style() {
                if (this.data.fontSize == '36') {
                    return `big-style`
                } else if (this.data.fontSize == '28') {
                    return `medium-style`
                } else {
                    return `small-style`;
                }
            }
        },
        methods: {
            selectLink(e) {
                this.data.link = e[0];
            },
            pickPicUrl(e) {
                if (e) {
                    this.data.picUrl = e[0].url;
                }
            }
        }
    });
</script>
