<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
?>
<style>
    .diy-user-order .diy-component-edit .nav-list {
        flex-wrap: wrap;
        width: 500px;
        line-height: normal;
    }

    .diy-user-order .diy-component-edit .nav-item {
        width: 20%;
        border: 1px solid #e2e2e2;
        margin-left: -1px;
        text-align: center;
        font-size: 12px;
        color: #333;
        cursor: pointer;
    }
</style>
<template id="diy-user-order">
    <div class="diy-user-order">
        <div class="diy-component-preview">
            <div style="border: 1px solid #eee;border-width: 1px 0;" :style="cStyle">
                <div style="padding: 20px 30px;border-bottom: 1px solid #e2e2e2;">我的订单</div>
                <div flex="">
                    <div v-for="item in data.navs" flex="cross:center" style="width: 20%;">
                        <div style="text-align: center;width: 100%;padding: 20px 0;">
                            <div>
                                <img :src="item.picUrl" style="width: 50px;height: 50px;">
                            </div>
                            <div style="color: #333; font-size: 24px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                {{item.text}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="100px">
                <el-form-item label="导航菜单">
                    <div class="nav-list" flex>
                        <div class="nav-item" v-for="nav in data.navs">
                            <app-attachment v-model="nav.picUrl">
                                <div style="width: 100px;padding: 10px 0;">
                                    <div><img :src="nav.picUrl" style="width: 25px;height: 25px;"></div>
                                    <div>{{nav.text}}</div>
                                </div>
                            </app-attachment>
                        </div>
                    </div>
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
    Vue.component('diy-user-order', {
        template: '#diy-user-order',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    navs: [
                        {
                            url: '',
                            openType: 'navigate',
                            picUrl: _currentPluginBaseUrl + '/images/user-order-dfk.png',
                            text: '待付款',
                        },
                        {
                            url: '',
                            openType: 'navigate',
                            picUrl: _currentPluginBaseUrl + '/images/user-order-dfh.png',
                            text: '待发货',
                        },
                        {
                            url: '',
                            openType: 'navigate',
                            picUrl: _currentPluginBaseUrl + '/images/user-order-dsh.png',
                            text: '待收货',
                        },
                        {
                            url: '',
                            openType: 'navigate',
                            picUrl: _currentPluginBaseUrl + '/images/user-order-ywc.png',
                            text: '待评价',
                        },
                        {
                            url: '',
                            openType: 'navigate',
                            picUrl: _currentPluginBaseUrl + '/images/user-order-sh.png',
                            text: '售后',
                        },
                    ],
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
            cStyle() {
                if(this.data.backgroundColor) {
                    return `background: ${this.data.backgroundColor};`
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
        methods: {}
    });
</script>