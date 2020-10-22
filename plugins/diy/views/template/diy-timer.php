<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/5
 * Time: 17:48
 */
?>
<style>
    .chooseLink .el-input-group__append {
        background-color: #fff;
    }

    .diy-component-preview .bg {
        height: 140px;
        padding: 0 50px;
        color: #fff;
        background-size: 100% 100%;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
<template id="diy-timer">
    <div>
        <div class="diy-component-preview">
            <div style="font-size: 0;">
                <img v-if="data.picUrl" :src="data.picUrl" style="width: 100%;height: auto;">
            </div>
            <div class="bg" :style="'background-image: url('+data.bgPicUrl+');'" flex="cross:center">
                <div>
                    <div>距离活动开始还有</div>
                    <div>xx天xx小时xx分xx秒</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="图片">
                    <app-image-upload v-model="data.picUrl"></app-image-upload>
                </el-form-item>
                <el-form-item label="背景图片">
                    <app-image-upload v-model="data.bgPicUrl"></app-image-upload>
                </el-form-item>
                <el-form-item class="chooseLink" label="链接">
                    <el-input style="width: 300px" v-model="data.link.url" placeholder="点击选择链接" :disabled="true"
                              size="small">
                        <app-pick-link slot="append" @selected="linkSelected">
                            <el-button size="small">选择链接</el-button>
                        </app-pick-link>
                    </el-input>
                </el-form-item>
                <el-form-item label="开始时间">
                    <el-date-picker v-model="data.startDateTime"
                                    size="small"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    type="datetime"
                                    placeholder="选择日期时间">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="结束时间">
                    <el-date-picker v-model="data.endDateTime"
                                    size="small"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    type="datetime"
                                    placeholder="选择日期时间">
                    </el-date-picker>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-timer', {
        template: '#diy-timer',
        props: {
            value: Object,
        },
        data() {
            return {
                bottomBg: _currentPluginBaseUrl + '/images/timer-bottom-bg.png',
                data: {
                    picUrl: '',
                    link: {
                        url: '',
                        openType: '',
                        bgPicUrl: _currentPluginBaseUrl + '/images/timer-bottom-bg.png',
                    },
                    startDateTime: '',
                    endDateTime: '',
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
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            linkSelected(list) {
                if (!list || !list.length) {
                    return;
                }
                this.data.link.url = list[0].new_link_url;
                this.data.link.openType = list[0].open_type;
            },
        }
    });
</script>
