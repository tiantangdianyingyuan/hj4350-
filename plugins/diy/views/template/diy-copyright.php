<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
?>
<style>
    .chooseLink .el-input-group__append {
        background-color: #fff;
    }
</style>
<template id="diy-copyright">
    <div>
        <div class="diy-component-preview">
            <div style="padding: 28px 28px;"
                 :style="cStyle"
                 flex="main:center cross:center">
                <div>
                    <div v-if="data.picUrl" style="text-align: center;">
                        <img :src="data.picUrl" style="width: 160px;height: 50px;">
                    </div>
                    <div style="text-align: center;color: #333;">{{data.text}}</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="版权文字">
                    <el-input size="small" v-model="data.text"></el-input>
                </el-form-item>
                <el-form-item label="版权图标">
                    <app-image-upload width="160" height="50" v-model="data.picUrl"></app-image-upload>
                </el-form-item>
                <el-form-item class="chooseLink" label="版权链接">
                    <el-input v-model="data.link.url" placeholder="点击选择链接" :disabled="true" size="small">
                        <app-pick-link slot="append" @selected="linkSelected">
                            <el-button size="small">选择链接</el-button>
                        </app-pick-link>
                    </el-input>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker size="small" v-model="data.backgroundColor"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.backgroundColor"></el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-copyright', {
        template: '#diy-copyright',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    picUrl: '',
                    text: '',
                    link: {
                        url: '',
                        openType: '',
                        data: {},
                    },
                    backgroundColor: '#fff',
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
            cStyle() {
                if(this.data.backgroundColor) {
                    return `background-color: ${this.data.backgroundColor};`
                }else {
                    return ``
                }
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
            linkSelected(e) {
                if (!e.length) {
                    return;
                }
                this.data.link = {
                    url: e[0].new_link_url,
                    openType: e[0].open_type,
                    data: e[0],
                };

            },
        }
    });
</script>