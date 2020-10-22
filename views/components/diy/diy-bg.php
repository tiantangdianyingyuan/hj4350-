<?php
?>
<style>
    .diy-bg hr {
        border: none;
        height: 1px;
        background-color: #e2e2e2;
        margin-bottom: 30px;
    }

    .diy-bg .background-position {
        width: 170px;
        height: 180px;
    }

    .diy-bg .background-position > div {
        height: 50px;
        width: 50px;
        margin-bottom: 10px;
        cursor: pointer;
        background-color: #F5F7F9;
    }

    .diy-bg .background-position > .active {
        background-color: #E6F4FF;
        border: 2px dashed #5CB3FD;
    }

    .diy-bg .input-color .el-input__inner {
        padding: 0 10px;
    }
</style>
<template id="diy-bg">
    <div class="diy-bg">
        <hr v-if="hr">
        <el-form-item label="背景颜色">
            <div class="input-color" flex="dir:left cross:center">
                <el-color-picker size="small" :change="changeColor" v-model="data.backgroundColor"></el-color-picker>
                <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.backgroundColor"></el-input>
            </div>
        </el-form-item>
        <el-form-item v-if="background" label="背景图片">
            <el-switch v-model="data.showImg" @change="showBackground"></el-switch>
            <slot name="about"></slot>
        </el-form-item>
        <el-form-item v-if="data.showImg" label="上传背景">
            <app-image-upload v-model="backgroundPicUrl"></app-image-upload>
        </el-form-item>
        <el-form-item v-if="data.showImg" label="图片位置">
            <div class="background-position" flex="main:justify wrap:wrap">
                <div @click="choose(1,'top left')" :class="data.position==1?'active':''"></div>
                <div @click="choose(2,'top center')" :class="data.position==2?'active':''"></div>
                <div @click="choose(3,'top right')" :class="data.position==3?'active':''"></div>
                <div @click="choose(4,'center left')" :class="data.position==4?'active':''"></div>
                <div @click="choose(5,'center center')" :class="data.position==5?'active':''"></div>
                <div @click="choose(6,'center right')" :class="data.position==6?'active':''"></div>
                <div @click="choose(7,'bottom left')" :class="data.position==7?'active':''"></div>
                <div @click="choose(8,'bottom center')" :class="data.position==8?'active':''"></div>
                <div @click="choose(9,'bottom right')" :class="data.position==9?'active':''"></div>
            </div>
        </el-form-item>
        <el-form-item v-if="data.showImg" label="填充方式">
            <el-radio-group v-model="data.mode" @change="changeMode">
                <el-radio :label="1">充满</el-radio>
                <el-radio :label="2">左右平铺</el-radio>
                <el-radio :label="3">上下平铺</el-radio>
                <el-radio :label="4">平铺满</el-radio>
            </el-radio-group>
        </el-form-item>
        <el-form-item v-if="data.showImg && data.mode != 1" label="背景图宽">
            <el-slider v-model="data.backgroundWidth" :show-input-controls="false" style="float: left;width: 95%" :max="100" show-input></el-slider>
            <div style="float: right">%</div>
        </el-form-item>
        <el-form-item v-if="data.showImg && data.mode != 1" label="背景图高">
            <el-slider v-model="data.backgroundHeight" :show-input-controls="false" style="float: left;width: 95%" :max="100" show-input></el-slider>
            <div style="float: right">%</div>
        </el-form-item>
    </div>
</template>
<script>
    Vue.component('diy-bg', {
        template: '#diy-bg',
        props: {
            data: Object,
            hr: {
                type: Boolean,
                default: true
            },
            background: {
                type: Boolean,
                default: false
            },
        },
        data() {
            return {
                backgroundPicUrl: '',
                position: 'center center',
                repeat: 'no-repeat'
            }
        },
        computed: {
            changeColor() {
                if(!this.data.borderBackground) {
                    this.data.borderBackground = '#ffffff'
                }
            },
        },
        watch: {
            backgroundPicUrl: {
                handler(data) {
                    if(this.data.showImg) {
                        this.data.backgroundPicUrl = this.backgroundPicUrl
                    }else {
                        this.data.backgroundPicUrl = ''
                    }
                },
            }
        },
        created() {
            if(this.data.backgroundPicUrl) {
                this.backgroundPicUrl = this.data.backgroundPicUrl
            }
        },
        methods: {
            choose(num,position) {
                this.data.position = num;
                this.position = position;
                this.$emit('toggle', this.position);
                this.$emit('update', this.data);
            },

            showBackground(e) {
                this.data.showImg = e;
                if(!e) {
                    this.data.backgroundPicUrl = ''
                }else {
                    this.data.backgroundPicUrl = this.backgroundPicUrl
                }
                if(this.data.mode == 2) {
                    this.repeat = 'repeat-x'
                }else if(this.data.mode == 3) {
                    this.repeat = 'repeat-y'
                }else if(this.data.mode == 4) {
                    this.repeat = 'repeat'
                }else if(this.data.mode == 1) {
                    this.repeat = 'no-repeat';
                    this.data.backgroundHeight = 100;
                    this.data.backgroundWidth = 100;
                }
                this.$emit('change', this.repeat);
                this.$emit('update', this.data);
            },
            changeMode(e) {
                if(e == 2) {
                    this.repeat = 'repeat-x'
                }else if(e == 3) {
                    this.repeat = 'repeat-y'
                }else if(e == 4) {
                    this.repeat = 'repeat'
                }else if(e == 1) {
                    this.repeat = 'no-repeat';
                    this.data.backgroundHeight = 100;
                    this.data.backgroundWidth = 100;
                }
                this.$emit('change', this.repeat);
            }
        }
    });
</script>
