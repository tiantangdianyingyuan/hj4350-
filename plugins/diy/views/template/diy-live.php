<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/6
 * Time: 16:03
 */
?>
<style>
    .diy-live .room-box {
        display: flex;
        flex-direction: column;
    }

    .diy-live .room-box .label {
        color: #999999;
    }

    /*样式一*/
    .diy-live .style-box-1 {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 0 10px 5px rgba(0, 0, 0, 0.1);
    }

    .diy-live .style-box-1 .item {
        height: 360px;
        background: #999999;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }

    .diy-live .style-box-1 .label-box {
        border-top-left-radius: 16px;
        border-top-right-radius: 30px;
        border-bottom-right-radius: 30px;
        position: absolute;
        top: 0;
        left: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
    }

    .diy-live .style-box-1 .text-time {
        color: #ffffff;
        font-size: 24px;
        margin: 0 20px;
    }

    .diy-live .style-box-1 .label-box-item {
        background: #22ac38;
        border-top-right-radius: 30px;
        border-bottom-right-radius: 30px;
        display: flex;
        align-items: center;
        padding: 12px 20px;
    }

    .diy-live .style-box-1 .icon {
        width: 10px;
        height: 10px;
        background: #ffffff;
        border-radius: 50%;
    }

    .diy-live .style-box-1 .text {
        color: #ffffff;
        font-size: 24px;
        margin-left: 12px;
    }

    .diy-live .style-box-1 .user-info-box {
        position: absolute;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        margin: 20px;
    }

    .diy-live .style-box-1 .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
    }

    .diy-live .style-box-1 .nickname {
        width: 578px;
        margin-left: 12px;
        font-size: 24px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #ffffff;
    }

    .diy-live .style-box-1 .title {
        width: 645px;
        font-size: 32px;
        color: #353535;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin: 28px 0 20px;
    }

    .diy-live .style-box-1 .goods-box {
        display: flex;
        align-items: center;
        padding: 10px;
        background: #f7f7f7;
        border-radius: 16px;
    }

    .diy-live .style-box-1 .goods-cover {
        border-radius: 8px;
        width: 80px;
        height: 80px;
        background: #999999;
    }

    .diy-live .style-box-1 .goods-item {
        display: flex;
        flex-direction: column;
        margin-left: 16px;
    }

    .diy-live .style-box-1 .goods-name {
        width: 540px;
        font-size: 26px;
        color: #666666;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .diy-live .style-box-1 .goods-price {
        margin-top: 16px;
    }

    /*样式二*/

    .diy-live .style-box-2 {
        width: 100%;
        display: flex;
        justify-content: space-between;
    }

    .diy-live .style-box-2 .item {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 0 10px 5px rgba(0, 0, 0, 0.1);
    }

    .diy-live .style-box-2 .live-img-box {
        border-radius: 16px;
        overflow: hidden;
        position: relative;
    }

    .diy-live .style-box-2 .live-img {
        width: 346px;
        height: 346px;
        background: #999999;
    }

    .diy-live .style-box-2 .label-box-item {
        width: 100%;
        border-top-right-radius: 24px;
        border-bottom-right-radius: 24px;
        position: absolute;
        top: 0;
        left: 0;
        background: rgba(0, 0, 0, 0.5);
    }

    .diy-live .style-box-2 .label-box {
        padding: 12px 20px;
        border-top-right-radius: 30px;
        border-bottom-right-radius: 30px;
        background: #777777;
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
    }

    .diy-live .style-box-2 .icon {
        width: 10px;
        height: 10px;
        background: #ffffff;
        border-radius: 50%;
    }

    .diy-live .style-box-2 .text {
        color: #ffffff;
        font-size: 24px;
        margin-left: 12px;
    }

    .diy-live .style-box-2 .user-info {
        position: absolute;
        bottom: 0;
        left: 0;
        margin: 20px;
        display: flex;
        justify-content: center;
    }

    .diy-live .style-box-2 .avatar {
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 50%;
    }

    .diy-live .style-box-2 .nickname {
        margin-left: 12px;
        font-size: 24px;
        width: 194px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #ffffff;
    }

    .diy-live .style-box-2 .title {
        font-size: 28px;
        color: #353535;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin: 28px;
        width: 290px;
    }

    .diy-live .style-box-2 .goods-box {
        display: flex;
        margin: 0 28px 28px;
        align-items: center;
    }

    .diy-live .style-box-2 .goods-cover {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        background: #999999;
    }

    .diy-live .style-box-2 .goods-item {
        display: flex;
        flex-direction: column;
        margin-left: 16px;
    }

    .diy-live .style-box-2 .goods-name {
        font-size: 26px;
        color: #666666;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 194px;
    }

    .diy-live .style-box-2 .goods-price {
        font-size: 26px;
        color: #353535;
        margin-top: 16px;
    }

    /*样式三*/
    .diy-live .style-box-3 {
        display: flex;
        flex-direction: row;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
    }

    .diy-live .style-box-3 .live-item {
        position: relative;
    }

    .diy-live .style-box-3 .live-img {
        width: 360px;
        height: 360px;
        background: #999999;
        border-radius: 16px;
    }

    .diy-live .style-box-3 .label-box {
        position: absolute;
        top: 0;
        left: 0;
        border-top-right-radius: 24px;
        border-bottom-right-radius: 24px;
        display: flex;
        align-items: center;
    }

    .diy-live .style-box-3 .label-box-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        border-top-right-radius: 24px;
        border-bottom-right-radius: 24px;
        background: #22ac38;
    }

    .diy-live .style-box-3 .icon {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #ffffff;
    }

    .diy-live .style-box-3 .text {
        color: #ffffff;
        margin-left: 12px;
    }

    .diy-live .style-box-3 .text-time {
        color: #ffffff;
        margin-left: 12px;
    }

    .diy-live .style-box-3 .item {
        display: flex;
        flex-direction: column;
        padding: 0 20px;
    }

    .diy-live .style-box-3 .title {
        width: 310px;
        height: 100px;
        font-size: 36px;
        color: #353535;
        overflow: hidden;
        text-overflow: ellipsis;
        -webkit-line-clamp: 2;
        line-clamp: 2;
    }

    .diy-live .style-box-3 .user-info-box {
        display: flex;
        margin: 20px 0;
        align-items: center;
    }

    .diy-live .style-box-3 .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f7f7f7;
    }

    .diy-live .style-box-3 .nickname {
        color: #000;: #f7f7f7;
        margin-left: 12px;
        width: 258px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .diy-live .style-box-3 .goods-box {
        display: flex;
        justify-content: space-between;
    }

    .diy-live .style-box-3 .goods-box {
        display: flex;
        justify-content: space-between;
    }

    .diy-live .style-box-3 .goods-cover {
        width: 148px;
        height: 148px;
        border-radius: 16px;
        background: #999999;
        padding: 10px;
        display: flex;
        align-items: flex-end;
    }

    .diy-live .style-box-3 .goods-price {
        font-size: 24px;
        color: #ffffff;
    }

    .diy-live .style-box-3 .goods-cover-2 {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffffff;
    }
</style>
<template id="diy-live">
    <div class="diy-live">
        <div v-loading="!data" class="diy-component-preview">
            <div :style="{padding: '20px', background: data.background}">
                <div class="style-box-1" v-if="data.style_type == 1 && index == 0" v-for="(item, index) in liveList"
                     :key="index">
                    <div class="item">
                        <div class="label-box">
                            <div class="label-box-item">
                                <div class="icon"></div>
                                <div class="text">{{item.text}}</div>
                            </div>
                            <span class="text-time">{{item.text_time}}</span>
                        </div>
                        <div class="user-info-box">
                            <div class="avatar"></div>
                            <span class="nickname">{{item.nickname}}</span>
                        </div>
                    </div>
                    <div class="title">{{item.title}}</div>
                    <div v-if="data.is_show_goods" class="goods-box">
                        <div class="goods-cover"></div>
                        <div class="goods-item">
                            <span class="goods-name">{{item.goods_list[0].goods_name}}</span>
                            <span class="goods-price">{{item.goods_list[0].goods_price}}</span>
                        </div>
                    </div>
                </div>
                <div class="style-box-2" v-if="data.style_type == 2">
                    <div class="item" v-for="(item, index) in liveList">
                        <div class="live-img-box">
                            <div class="live-img"></div>
                            <div class="label-box-item">
                                <div class="label-box">
                                    <div class="icon"></div>
                                    <div class="text">已结束</div>
                                </div>
                            </div>
                            <div class="user-info">
                                <div class="avatar"></div>
                                <span class="nickname">{{item.nickname}}</span>
                            </div>
                        </div>
                        <div class="title">{{item.title}}</div>
                        <div v-if="data.is_show_goods" class="goods-box">
                            <div class="goods-cover"></div>
                            <div class="goods-item">
                                <div class="goods-name">{{item.goods_list[0].goods_name}}</div>
                                <div class="goods-price">{{item.goods_list[0].goods_price}}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-box-3" v-if="data.style_type == 3 && index == 0" v-for="(item, index) in liveList"
                     :key="index">
                    <div class="live-item">
                        <div class="live-img"></div>
                        <div class="label-box">
                            <div class="label-box-item">
                                <div class="icon"></div>
                                <div class="text">{{item.text}}</div>
                            </div>
                            <span class="text-time">{{item.text_time}}</span>
                        </div>

                    </div>
                    <div class="item">
                        <div class="title">{{item.title}}</div>
                        <div class="user-info-box">
                            <div class="avatar"></div>
                            <span class="nickname">{{item.nickname}}</span>
                        </div>
                        <div v-if="data.is_show_goods" class="goods-box">
                            <div class="goods-cover">
                                <span class="goods-price">{{item.goods_list[0].goods_price}}</span>
                            </div>
                            <div class="goods-cover goods-cover-2">
                                <div>{{item.goods_list.length}}</div>
                                <div>宝贝</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form v-loading="loading" @submit.native.prevent label-width="100px">
                <el-form-item label="显示商品">
                    <el-switch v-model="data.is_show_goods" @change="showGoodsChange"></el-switch>
                </el-form-item>
                <el-form-item label="卡片样式">
                    <el-radio v-model="data.style_type" :label="1">样式1</el-radio>
                    <el-radio v-model="data.style_type" :label="2">样式2</el-radio>
                    <el-radio v-model="data.style_type" :label="3">样式3</el-radio>
                </el-form-item>
                <el-form-item label="直播间数量">
                    <div class="room-box">
                        <el-input size="small" type="number" v-model.number="data.number" placeholder="请输入直播间数量"></el-input>
                        <span class="label">此组件最多支持20个直播间</span>
                    </div>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker size="small" v-model="data.background"></el-color-picker>
                    <el-input size="small" style="width: 100px;" v-model="data.background"></el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-live', {
        template: '#diy-live',
        props: {
            value: Object,
        },
        data() {
            return {
                loading: false,
                default: {
                    background: '#f7f7f7',
                    is_show_goods: true,
                    style_type: 1,
                    number: 5,
                },
                form: {},
                background: '#FFFFFF',
                top_bottom_padding: 0,
                data: {
                    background: '#f7f7f7',
                    is_show_goods: true,
                    style_type: 1,
                    number: 5,
                },
                liveList: [
                    {
                        title: '直播间名称',
                        nickname: '主播昵称',
                        text: '预告',
                        text_time: '今晚19:00开播',
                        goods_list: [
                            {
                                goods_name: '商品名称',
                                goods_price: '￥469.00'
                            }, {
                                goods_name: '商品名称',
                                goods_price: '￥469.00'
                            },
                        ]
                    }, {
                        title: '直播间名称',
                        nickname: '主播昵称',
                        text: '预告',
                        text_time: '今晚19:00开播',
                        goods_list: [
                            {
                                goods_name: '商品名称',
                                goods_price: '￥469.00'
                            }, {
                                goods_name: '商品名称',
                                goods_price: '￥469.00'
                            },
                        ]
                    }
                ]
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    if (newVal.number > 20) {
                        newVal.number = 20;
                    }
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            showGoodsChange(e) {
                console.log(e)
            },
            loadData() {
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/setting/index'
                    },
                    method: 'get'
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.setting != "") {
                            that.plugin = e.data.data.setting.form
                        }
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
        }
    });
</script>
