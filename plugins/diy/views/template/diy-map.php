<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
?>
<style>
    .diy-map .map-container {
        background-size: cover;
        background-position: center;
    }
</style>
<template id="diy-map">
    <div class="diy-map">
        <div class="diy-component-preview">
            <div :style="'padding-top: '+data.marginTop+'px; background-color: '+data.marginTopColor">
                <div :style="cContainerStyle" class="map-container">
                    <div :style="cMapStyle" style="background-size: cover;background-position: center;"></div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="地图">
                    <app-map @map-submit="mapEvent">
                        <el-button type="primary" size="small">地图选点</el-button>
                    </app-map>
                </el-form-item>
                <el-form-item label="经纬度">
                    <el-input size="small" v-model="data.location" placeholder="请使用地图选点选择经纬度"></el-input>
                </el-form-item>
                <el-form-item label="地图高度">
                    <el-slider v-model="data.height" :max="1500" show-input></el-slider>
                </el-form-item>
                <el-form-item label="上下边距">
                    <el-slider v-model="data.paddingY" :max="1500" show-input></el-slider>
                </el-form-item>
                <el-form-item label="左右边距">
                    <el-slider v-model="data.paddingX" :max="375" show-input></el-slider>
                </el-form-item>
                <el-form-item label="顶部外边距">
                    <el-slider v-model="data.marginTop" :max="1500" show-input></el-slider>
                </el-form-item>
                <el-form-item label="外边距颜色">
                    <el-color-picker v-model="data.marginTopColor"></el-color-picker>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker v-model="data.backgroundColor"></el-color-picker>
                </el-form-item>
                <el-form-item label="背景图片">
                    <app-image-upload v-model="data.backgroundPicUrl"></app-image-upload>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-map', {
        template: '#diy-map',
        props: {
            value: Object,
        },
        data() {
            return {
                mapDemoPic: _currentPluginBaseUrl + '/images/map-demo.png',
                data: {
                    location: '',
                    height: 400,
                    paddingY: 40,
                    paddingX: 40,
                    marginTop: 0,
                    marginTopColor: '#ffffff',
                    backgroundColor: '#ffffff',
                    backgroundPicUrl: '',
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
            cContainerStyle() {
                let style = `padding: ${this.data.paddingY}px ${this.data.paddingX}px;`
                    + `background-color: ${this.data.backgroundColor};`
                    + `background-image: url(${this.data.backgroundPicUrl});`;
                return style;
            },
            cMapStyle() {
                let style = `height: ${this.data.height}px;`
                    + `background-image: url(${this.mapDemoPic});`;
                return style;
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
            mapEvent(e) {
                this.data.location = e.lat + ',' + e.long;
            },
        }
    });
</script>