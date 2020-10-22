<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
?>
<style>
    .diy-check-in .diy-component-preview .hotspot {
        border: 1px dashed #5CB3FD;
        background-color: rgba(92, 179, 253, 0.2);
        z-index: 1;
        position: absolute;
    }
</style>
<template id="diy-check-in">
    <div class="diy-check-in">
        <div class="diy-component-preview">
            <div style="height: 200px;padding: 0 50px;background-size: 100% 100%;position: relative;"
                 :style="'background-image: url('+data.backgroundPicUrl+');'"
                 flex="cross:center">
                <div v-if="data.showText" :style="cTextStyle" style="width: 100%;">
                    <div style="margin-bottom: 10px;">今日签到可获得5积分</div>
                    <div>已连续签到10天</div>
                </div>
                <div v-if="data.hotspot" class="hotspot" :style="cHotspotStyle"></div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="背景图">
                    <app-image-upload v-model="data.backgroundPicUrl" width="750" height="200"></app-image-upload>
                </el-form-item>
                <el-form-item label="点击热区">
                    <app-hotspot :pic-url="data.backgroundPicUrl"
                                 :hotspot-array="data.hotspot?[data.hotspot]:[]"
                                 :multiple="false"
                                 :is-link="false"
                                 @confirm="selectHotspot"
                                 width="750px"
                                 height="200px">
                        <el-button size="small">设置热区</el-button>
                    </app-hotspot>
                </el-form-item>
                <el-form-item label="显示文字">
                    <el-switch v-model="data.showText"></el-switch>
                </el-form-item>
                <el-form-item label="文字位置">
                    <app-radio v-model="data.textPosition" label="left">靠左</app-radio>
                    <app-radio v-model="data.textPosition" label="center">居中</app-radio>
                    <app-radio v-model="data.textPosition" label="right">靠右</app-radio>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker @change="(row) => {row == null ? data.textColor = '#FFFFFF' : ''}"
                                     v-model="data.textColor"></el-color-picker>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-check-in', {
        template: '#diy-check-in',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    backgroundPicUrl: _currentPluginBaseUrl + '/images/check-in-default-bg.png',
                    showText: true,
                    textPosition: 'left',
                    textColor: '#ffffff',
                    hotspot: null,
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cTextStyle() {
                return `color: ${this.data.textColor};text-align: ${this.data.textPosition};`;
            },
            cHotspotStyle() {
                if (!this.data.hotspot)
                    return '';
                return `width: ${this.data.hotspot.width}px;`
                    + `height: ${this.data.hotspot.height}px;`
                    + `left: ${this.data.hotspot.left}px;`
                    + `top: ${this.data.hotspot.top}px;`;
            },
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
            selectHotspot(e) {
                this.hotspotList = e;
                if (e && e.length) {
                    this.data.hotspot = e[0];
                }
            },
        }
    });
</script>