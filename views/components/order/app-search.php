<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-search .tabs {
        margin-top: 20px;
    }

    .app-search .label {
        margin-right: 10px;
    }

    .app-search .item-box {
        margin-bottom: 10px;
        margin-right: 15px;
    }

    .app-search .clear-where {
        color: #419EFB;
        cursor: pointer;
    }

    .app-search .show-search-icon .el-input__inner {
        border-right: 0;
    }

    .app-search .show-search-icon .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-search .show-search-icon .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-search .show-search-icon .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-search .show-search-icon .el-input-group__append .el-button {
        padding: 0;
    }

    .app-search .show-search-icon .el-input-group__append .el-button {
        margin: 0;
    }

    .date-select {
        margin-right: 0!important;
        color: #909399;
    }

    .date-select .el-input__inner {
        background-color: #F5F7FA;
        width: 120px;
        border-right: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        color: #909399;
    }
    .date-picker{
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>

<template id="app-search">
    <div class="app-search">
        <div flex="wrap:wrap cross:center">

            <div class="item-box" flex="dir:left cross:center" v-if="isShowPlatform">
                <div class="label">所属平台</div>
                <el-select style="width: 120px;" size="small" v-model="search.platform" @change='toSearch'>
                    <el-option key="all" label="全部平台" value=""></el-option>
                    <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                    <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                    <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                    <el-option key="bdapp" label="百度" value="bdapp"></el-option>
                </el-select>
            </div>

            <div class="item-box" flex="dir:left" v-if="isGoodsType && is_show_ecard">
                <div flex="cross:center" style="height: 32px;">商品类型：</div>
                <el-select @change="toSearch" v-model="search.type" style="width: 150px;" placeholder="请选择" size="small">
                    <el-option
                            label="全部"
                            value="">
                    </el-option>
                    <el-option
                            label="实体商品"
                            value="goods">
                    </el-option>
                    <el-option
                            label="虚拟商品"
                            value="ecard">
                    </el-option>
                </el-select>
            </div>

            <div v-if="isShowOrderPlugin" class="item-box" flex="dir:left cross:center">
                <div class="label">订单类型</div>
                <el-select size="small" style="width: 120px" v-model="search.plugin" @change="toSearch"
                           placeholder="订单类型">
                    <el-option v-for="item in plugins" :key="item.sign" :label="item.name"
                               :value="item.sign">
                    </el-option>
                </el-select>
            </div>
            <div class="item-box" v-if="isShowOrderType" flex="dir:left cross:center">
                <div class="label">配送方式</div>
                <el-select size="small" style="width: 120px" v-model="search.send_type" @change="toSearch"
                           placeholder="配送方式">
                    <el-option label="全部订单" :value="-1"></el-option>
                    <el-option label="快递配送" :value="0"></el-option>
                    <el-option label="到店核销" :value="1"></el-option>
                    <el-option label="同城配送" :value="2"></el-option>
                </el-select>
            </div>
            <slot name="extra"></slot>
            <div style="display: inherit;">
                <el-select class="item-box date-select" size="small" v-model="search.date_type" placeholder="请选择">
                <el-option :label="dateLabel" value="created_time"></el-option>
                <el-option v-for="item in dateTypeList" :key="item.value" :label="item.label" :value="item.value"></el-option>
                </el-select>
                <el-date-picker
                        class="item-box date-picker"
                        size="small"
                        @change="changeTime"
                        v-model="search.time"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>
            </div>
            <div class="item-box label" :class="{'show-search-icon':!isSearchMenu}">
                <el-input :style="{ width: isSearchMenu ? '350px' : '250px' }" size="small" v-model="search.keyword" :placeholder="placeholder"  clearable
                          @clear="toSearch"
                          @keyup.enter.native="toSearch">
                    <el-select v-if="isSearchMenu" style="width: 120px" slot="prepend" v-model="search.keyword_1">
                        <el-option v-for="item in selectList" :key="item.value"
                                   :label="item.name"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                    <el-button v-if="!isSearchMenu" slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>
            <div class="item-box" flex="cross:center">
                <div v-if="isShowClear" @click="clearWhere" class="div-box clear-where">清空筛选条件</div>
            </div>
            <div v-if="isShowPrintInvoice && isSendTemplate" class="item-box" flex="dir:left cross:center">
                <el-button type="primary" size="small" @click="printInvoice">打印发货单</el-button>
            </div>
        </div>
        <slot></slot>
        <div class="tabs">
            <el-tabs v-model="newActiveName" @tab-click="handleClick">
                <el-tab-pane v-for="(item, index) in tabs" :key="index" :label="item.name"
                             :name="item.value"></el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>

<script>
    Vue.component('app-search', {
        template: '#app-search',
        props: {
            selectList: {
                type: Array,
                default: function () {
                    return [
                        {value: '1', name: '订单号'},
                        {value: '9', name: '商户单号'},
                        {value: '2', name: '用户名'},
                        {value: '4', name: '用户ID'},
                        {value: '5', name: '商品名称'},
                        {value: '3', name: '收件人'},
                        {value: '6', name: '收件人电话'},
                        {value: '7', name: '门店名称'}
                    ]
                }
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {value: '-1', name: '全部'},
                        {value: '0', name: '未付款'},
                        {value: '1', name: '待发货'},
                        {value: '2', name: '待收货'},
                        {value: '3', name: '已完成'},
                        {value: '4', name: '待处理'},
                        {value: '5', name: '已取消'},
                        {value: '7', name: '回收站'},
                    ]
                }
            },
            activeName: {
                type: String,
                default: '-1',
            },
            plugins: {
                type: Array,
                default: function () {
                    return [
                        {
                            name: '全部订单',
                            sign: 'all',
                        }
                    ];
                }
            },
            dateTypeList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            isShowOrderType: {
                type: Boolean,
                default: true
            },
            isShowOrderPlugin: {
                type: Boolean,
                default: false
            },
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        time: null,
                        keyword: '',
                        keyword_1: '1',
                        date_start: '',
                        date_end: '',
                        platform: '',
                        status: '',
                        plugin: 'all',
                        send_type: -1,
                        type: '',
                        date_type: 'created_time',
                    }
                }
            },
            dateLabel: {
                type: String,
                default: '下单时间'
            },
            placeholder: {
                type: String,
                default: '请输入搜索内容'
            },
            isShowPlatform: {
                type: Boolean,
                default: true
            },
            isShowPrintInvoice: {
                type: Boolean,
                default: false
            },
            isSendTemplate: {
                type: Boolean,
                default: false
            },
            isGoodsType: {
                type: Boolean,
                default: false
            },
            is_show_ecard: {
                type: Boolean,
                default: false
            },
            isSearchMenu: {
                type: Boolean,
                default: true
            },
        },
        data() {
            return {
                search: {},
                newActiveName: null,
                isShowClear: false,
            }
        },
        methods: {
            printInvoice() {
                this.$emit('print');
            },
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.toSearch();
            },
            toSearch() {
                this.search.page = 1;
                console.log(this.search)
                this.$emit('search', this.search);
                this.checkSearch();
            },
            handleClick(res) {
                this.search.status = this.newActiveName;
                this.toSearch();
            },
            clearWhere() {
                this.search.keyword = '';
                this.search.date_start = null;
                this.search.date_end = null;
                this.search.time = null;
                this.search.platform = '';
                this.search.send_type = -1;
                this.search.plugin = 'all';
                this.toSearch();
            },
            checkSearch() {
                if (this.search.keyword || (this.search.date_start && this.search.date_end)
                    || this.search.plugin != 'all' || this.search.send_type != -1
                    || this.search.platform) {
                    this.isShowClear = true;
                } else {
                    this.isShowClear = false;
                }
            }
        },
        created() {
            this.search = this.newSearch;
            if(this.selectList[0].value != this.newSearch.keyword_1) {
                this.newSearch.keyword_1 = this.selectList[0].value
            }
            this.newActiveName = this.activeName;
            this.search.status = this.newActiveName;
            this.checkSearch();
        }
    })
</script>