<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
?>
<style>
    .diy-user-info .avatar-icon {
        background: #E3E3E3;
        border: 2px solid #fff;
        width: 80px;
        height: 80px;
        border-radius: 9999px;
        display: inline-block;
    }
</style>
<template id="diy-user-info">
    <div class="diy-user-info">
        <div class="diy-component-preview">
            <div class="user-info-container" flex="cross:center"
                 style="height: 300px;background-color: #f5f7f9;background-size: cover;background-position: center;padding: 50px;"
                 :style="'background-image: url('+data.backgroundPicUrl+');'">
                <div style="width: 100%;" :style="cOutBoxStyle">
                    <div style="width: 100%;" :flex="cFlexDir">
                        <div :style="cAvatarBoxStyle">
                            <div class="avatar-icon"></div>
                        </div>
                        <div style="color: #fff;" :style="cNameBoxStyle">用户昵称</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="样式">
                    <app-radio v-model="data.style" :label="1">样式1</app-radio>
                    <app-radio v-model="data.style" :label="2">样式2</app-radio>
                    <app-radio v-model="data.style" :label="3">样式3</app-radio>
                </el-form-item>
                <el-form-item label="背景图片">
                    <app-image-upload v-model="data.backgroundPicUrl" width="750" height="300"></app-image-upload>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker v-model="data.backgroundColor"></el-color-picker>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-user-info', {
        template: '#diy-user-info',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    style: 1,
                    backgroundPicUrl: '',
                    backgroundColor: '#ffffff',
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
            cFlexDir() {
                let style = '';
                if (this.data.style === 1) {
                    style += 'dir:left box:first cross:center';
                }
                if (this.data.style === 2) {
                    style += 'dir:top main:center';
                }
                if (this.data.style === 3) {
                    style += 'dir:left box:first cross:center';
                }
                return style;
            },
            cOutBoxStyle() {
                let style = '';
                if (this.data.style === 3) {
                    style += `padding: 30px 20px;border-radius: 10px; background-color: ${this.data.backgroundColor};`;
                }
                return style;
            },
            cAvatarBoxStyle() {
                let style = '';
                if (this.data.style === 1 || this.data.style === 3) {
                    style += 'padding: 0 20px;';
                }
                if (this.data.style === 2) {
                    style += 'text-align: center;';
                }
                return style;
            },
            cNameBoxStyle() {
                let style = '';
                if (this.data.style === 2) {
                    style += 'text-align: center;';
                }
                if (this.data.style === 3) {
                    style += 'color: #333333;';
                }
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
        methods: {}
    });
</script>