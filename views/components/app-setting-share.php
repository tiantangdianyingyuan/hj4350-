<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .app-setting-share .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
    }

    .app-setting-share .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .app-setting-share .customize-share-title .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .app-setting-share .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .app-setting-share .app-share > div {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }
</style>
<template id="app-setting-share">
    <div class="app-setting-share">
        <div class="form-body">
            <el-form-item prop="share_title">
                <label slot="label">
                    <span>自定义分享标题</span>
                    <el-tooltip class="item" effect="dark" content="分享给好友时，作为微信分享卡片标题" placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                </label>
                <el-input size="small" placeholder="请输入分享标题" v-model="value.share_title"></el-input>
                <el-button @click="showModel('title')" type="text">查看图例</el-button>
            </el-form-item>
            <el-form-item prop="share_pic">
                <label slot="label">
                    <span>自定义分享图片</span>
                    <el-tooltip class="item" effect="dark" content="分享给好友时，作为微信分享卡片主图" placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                </label>

                <app-attachment v-model="value.share_pic" :multiple="false" :max="1">
                    <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336" placement="top">
                        <el-button size="mini">选择图片</el-button>
                    </el-tooltip>
                </app-attachment>

                <div class="customize-share-title">
                    <app-image mode="aspectFill"
                               width='80px'
                               height='80px'
                               :src="value.share_pic"></app-image>
                    <el-button v-if="value.share_pic"
                               class="del-btn"
                               size="mini"
                               type="danger"
                               icon="el-icon-close"
                               @click="value.share_pic = ``"
                               circle></el-button>
                </div>
                <el-button @click="showModel('bg')" type="text">查看图例</el-button>
            </el-form-item>
        </div>

        <el-dialog :title="shareDialogTitle" :visible.sync="shareDialog" width="30%">
            <div flex="dir:left main:center" class="app-share">
                <div v-if="shareType == 'title'" :style="{backgroundImage: 'url('+titlePic+')'}"></div>
                <div v-if="shareType == 'bg'" :style="{backgroundImage: 'url('+bgPic+')'}"></div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" type="primary" @click="shareDialog = false">我知道了</el-button>
            </span>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-setting-share', {
        template: '#app-setting-share',
        props: {
            value: {
                type: Object,
                default() {
                    return {
                        share_title: '',
                        share_pic: '',
                    }
                },
            },

            titlePic: {
                type: String,
                default: "",
            },
            bgPic: {
                type: String,
                default: "",
            },
        },


        data() {
            return {
                customizeTitle: '自定义分享标题',
                customizePic: '自定义分享图片',
                shareType: '',
                shareDialog: false,
            };
        },
        computed: {
            shareDialogTitle() {
                if (this.shareType === `title`) {
                    return `查看` + this.customizeTitle + `图例`;
                }
                if (this.shareType === `bg`) {
                    return `查看` + this.customizePic + `图例`;
                }
                return '';
            }
        },
        mounted() {
            this.list = this.value;
        },
        methods: {
            showModel(type) {
                this.shareDialog = true;
                this.shareType = type;
            },
            formDestroy(index) {
                this.value.splice(index, 1);
                this.$emit('update:value', this.list)
            }
        }
    });
</script>