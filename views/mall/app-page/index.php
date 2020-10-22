<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .item {
        background-color: #fff;
        width: 33%;
        min-height: 185px;
        margin-bottom: 10px;
        position: relative;
        padding: 20px;
        margin-right: 0.33%
    }

    .item .app-icon {
        display: flex;
        width: 85px;
        justify-content: space-between;
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .item .app-icon img {
        cursor: pointer;
    }

    .item .name {
        background-color: #F4F4F5;
        color: #909399;
        width: auto;
        display: inline-block;
        padding: 0 10px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        font-size: 12px;
        border-radius: 3px;
        border: 1px solid #E0E0E3;
        margin-bottom: 5px;
    }

    .el-form-item {
        margin-bottom: 0px;
    }

    .showqr .el-dialog__body {
        text-align: center;
        padding-bottom: 10px;
    }

    .el-dialog {
        min-width: 400px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="cardLoading">
        <el-tabs v-model="activeName" @tab-click="handleClick(activeName)">

            <el-tab-pane v-for="(tab, tabIndex) in tabList" :key="tabIndex" :label="tab.name" :name="tab.value">
                <div style="display: flex;flex-wrap: wrap">
                    <div v-for="item in list" class="item">
                        <div class="name">{{item.name}}</div>
                        <div class="app-icon">
                            <el-tooltip effect="dark" content="复制链接" placement="top">
                                <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                     data-clipboard-action="copy" :data-clipboard-target="'#' + tab.value + item.id">
                            </el-tooltip>
                            <el-tooltip effect="dark" content="二维码" placement="top">
                                <img src="statics/img/mall/qr.png" @click="qrcode(item)" alt="">
                            </el-tooltip>
                        </div>
                        <el-form @submit.native.prevent label-position="left" label-width="50px">
                            <el-form-item label="路径">
                                <span :id="tab.value + item.id">
                                    <span>
                                        {{item.value}}<template v-if="item.params"
                                                                v-for="(param, index) in item.params">{{index === 0 ? `?` + param.key + `=` + param.value : `&` + param.key + `=` + param.value}}</template>
                                    </span>
                                </span>
                            </el-form-item>
                            <el-form-item v-if="item.params" v-for="(param, index) in item.params" :key="index" :label="`参数` + (index + 1)">
                                <el-input size="small" v-model="param.value" :placeholder="param.desc"></el-input>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
    </el-card>
    <el-dialog class="showqr" :visible.sync="showqr" width="20%" center>
        <div class="name" style="text-align: center">{{title}}</div>
        <app-image :src="qrimg" style="margin: 20px auto 10px" height='200' width='200'></app-image>
        <span slot="footer" class="dialog-footer">
            <el-button type="primary" style="margin-bottom: 10px;" size="small" @click="down">保存二维码图片</el-button>
        </span>
    </el-dialog>
</div>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('.copy-btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });

    const app = new Vue({
        el: '#app',
        data() {
            return {
                qrimg: '',
                showqr: false,
                list: [],
                cardLoading: false,
                activeName: 'base',
                detail: [],
                tabList: [],
                title: '',
            };
        },
        methods: {
            down() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.title;
                alink.click();
            },

            change(e, row) {
                row.goods_id = e;
            },

            handleClick(e) {
                this.list = this.detail[e]
            },

            qrcode(row) {
                this.cardLoading = true;
                let value = row.value.replace('/', '')
                let para = {
                    r: 'mall/app-page/qrcode',
                    path: value,
                };
                if (row.params) {
                    para.params = {};
                    row.params.forEach((item) => {
                        para.params[item.key] = item.value;
                    })
                }

                this.title = row.name;
                request({
                    params: para,
                    method: 'get',
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.wechat) {
                            this.qrimg = e.data.data.wechat.file_path
                        } else if (e.data.data.alipay) {
                            this.qrimg = e.data.data.alipay.file_path
                        }
                        this.showqr = true;
                    } else {
                        this.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    this.cardLoading = false;
                    console.log(e);
                });
            },
            getList() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/app-page/index',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.detail = e.data.data.list;
                        if (typeof self.detail.plugin != 'undefined') {
                            self.detail.plugin.forEach(function (row) {
                                row.goods_id = ''
                            })
                        }
                        self.list = e.data.data.list.base;
                        self.getTabList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getTabList() {
                let self = this;
                let tabList = Object.keys(self.detail);
                let newTabList = [];
                // TODO 已下代码应该写在后端
                tabList.forEach((item) => {
                    if (item === 'base') {
                        newTabList.push({
                            name: '基础页面',
                            value: item
                        })
                    }
                    if (item === 'marketing') {
                        newTabList.push({
                            name: '营销页面',
                            value: item
                        })
                    }
                    if (item === 'order') {
                        newTabList.push({
                            name: '订单页面',
                            value: item
                        })
                    }
                    if (item === 'diy') {
                        newTabList.push({
                            name: 'diy页面',
                            value: item
                        })
                    }
                    if (item === 'plugin') {
                        newTabList.push({
                            name: '插件页面',
                            value: item
                        })
                    }
                })
                this.tabList = newTabList;
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
