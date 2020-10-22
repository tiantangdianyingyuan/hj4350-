<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
?>

<style>
    .theme-list {
        width: 60%;
    }
    .item {
        width: 127px;
        height: 61px;
        position: relative;
        border: 1px solid #e2e2e2;
        border-radius: 5px;
        margin-left: 20px;
        margin-bottom: 20px;
        overflow: hidden;
        cursor:pointer;
    }

    .item-active {
        border: 2px solid #3399ff;
    }
    .item:hover {
        margin-top: -3px;
        box-shadow: 0 4px 4px 4px #ECECEC;
    }
    .item .color {
        width: 46px;
        height: 33px;
        margin-right: 5px;
        transform-origin: 50% 50%;

        position: relative;
    }

    .color div {
        width: 25px;
        height: 25px;
        border-radius: 5px;
        position: absolute;
        top: 3.5px;
        transform: rotate(45deg);
    }
    .item .text {
        margin-left: 5px;
        font-size: 12px;
        color: #666666;
    }
    .deep {
        left: 3.5px;
    }
    .shallow {
        left: 18.5px;

    }
    .theme-show {
        height: 460px;
        width: 60%;
        margin-left: 20px;
        padding: 5px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .theme-item {
        width: 250px;
        height: 100%;
        box-shadow: 0 10px 30px 3px #dddddd;
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">
            <div>
                <span>商城风格</span>
            </div>
        </div>
        <div style="background-color: #fff;padding: 20px 0;">
            <div class="theme-list" flex="dir:left" style="flex-wrap: wrap">
                <div class="item" flex="dir:left main:center cross:center" :class="{'item-active': item.is_select}"  v-for="(item, index) in list" :key="index" @click="select(index)">
                    <div class="color">
                        <div class="deep" :style="{backgroundColor: item.color.secondary}"></div>
                        <div class="shallow"  :style="{backgroundColor: item.color.main}"></div>
                    </div>
                    <div class="text">{{item.name}}</div>
                </div>
            </div>
            <div class="theme-show" flex="dir:left main:justify">
                <div class="theme-item" v-for="item in pic_list" :style="{backgroundImage: `url(${item})`}"></div>
            </div>
        </div>
        <el-button class="button-item" type="primary"  @click="onSubmit">保存</el-button>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: true,
                list: [],
                timeout: -1,
                index: 0,
                pic_list: [],
            };
        },
        methods: {
            select(index) {
                this.list.map(item => {
                    item.is_select = false;
                });
                this.index = index;
                this.list[index].is_select = true;
                this.pic_list = this.list[index].pic_list;

            },

            onSubmit() {
                this.save();
            },

            async save() {
                this.loading = true;
                try {
                    const e = await request({
                        params: {
                            r: '/mall/theme-color/index',
                        },
                        data: {
                            theme_color: this.list[this.index].key,
                        },
                        method: 'post',
                    });
                    this.loading = false;
                    if (e.data.code === 0) {
                    } else {
                        this.$message.error(e.data.msg);
                    }
                } catch(e) {
                    this.$message.error(e);
                }
            }
        },
        mounted: function () {
            request({
                params: {
                    r: '/mall/theme-color/index'
                },
                method: 'get',
            }).then(e => {
                this.loading = false;
                if (e.data.code === 0) {
                    this.list = e.data.data.list;
                    this.list.map(item => {
                        if (item.is_select) {
                            this.pic_list = item.pic_list;
                        }
                    })
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                this.$message.error(e.data.msg);
                this.loading = false;
            });
        },
    });
</script>
