<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$mchId = Yii::$app->user->identity->mch_id;
Yii::$app->loadViewComponent('goods/app-add-cat');
?>

<style>
    .app-search .search-box {
        margin-bottom: 10px;
    }

    .app-search .div-box {
        margin-right: 10px;
    }

    .app-search .input-item {
        display: inline-block;
        width: 250px;
    }

    .app-search .input-item .el-input__inner {
        border-right: 0;
    }

    .app-search .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-search .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-search .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-search .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .app-search .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-search .clear-where {
        color: #419EFB;
        cursor: pointer;
    }
</style>

<template id="app-search">
    <div class="app-search">
        <el-tabs v-if="tabs.length > 0" v-model="activeName" @tab-click="handleClick">
            <el-tab-pane v-for="(item, index) in tabs" :key="index" :label="item.name" :name="item.value"></el-tab-pane>
        </el-tabs>
        <div class="search-box" flex="dir:left cross-center">
            <div class="div-box" flex="dir:left" v-if="isGoodsType && is_show_ecard && !is_mch">
                <div flex="cross:center" style="height: 32px;">商品类型：</div>
                <el-select @change="getList" v-model="search.type" style="width: 150px;" placeholder="请选择" size="small">
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
            <div v-if="isShowCat" class="div-box">
                <el-button size="small" @click="$refs.cats.openDialog()">分类筛选</el-button>
                <el-button size="small" v-if="search.cats && search.cats.length > 0" type="danger" @click="clearCat">清除分类</el-button>
            </div>
            <div class="div-box" flex="dir:left">
                <div flex="cross:center" style="height: 32px;">{{dateLabel}}：</div>
                <el-date-picker
                        size="small"
                        @change="changeTime"
                        v-model="datetime"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>
            </div>
            <div v-if="isShowSearch" class="input-item div-box" flex="cross-center">
                <div>
                    <el-input @keyup.enter.native="toSearch" size="small" :placeholder="placeHolder"
                              v-model="search.keyword" clearable
                              @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <div v-if="isShowClear" @click="clearWhere" class="div-box clear-where" flex="cross:center">清空筛选条件</div>
        </div>
        <app-add-cat ref="cats" :new-cats="newSearch.cats" @select="selectCat" :mch_id="mch_id"></app-add-cat>
    </div>
</template>

<script>
    Vue.component('app-search', {
        template: '#app-search',
        props: {
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        keyword: '',
                        status: '-1',
                        sort_prop: '',
                        sort_type: '',
                        cats: [],
                        date_start: null,
                        date_end: null,
                        type: ''
                    }
                }
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {
                            name: '全部',
                            value: '-1'
                        },
                        {
                            name: '销售中',
                            value: '1'
                        },
                        {
                            name: '下架中',
                            value: '0'
                        },
                        {
                            name: '售罄',
                            value: '2'
                        },
                    ];
                }
            },
            isShowCat: {
                type: Boolean,
                default: true
            },
            isShowSearch: {
                type: Boolean,
                default: true
            },
            newActiveName: {
                type: String,
                default: '-1'
            },
            dateLabel: {
                type: String,
                default: '添加时间'
            },
            placeHolder: {
                type: String,
                default: '请输入商品ID或名称搜索'
            },
            isGoodsType: {
                type: Boolean,
                default: false
            },
            is_show_ecard: {
                type: Boolean,
                default: false
            },
            is_mch: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                activeName: '-1',
                dialogVisible: false,
                dialogLoading: false,
                options: [],
                cats: [],
                children: [],
                third: [],
                datetime: [],
                mch_id: <?= $mchId ?>,
                isShowClear: false,
            }
        },
        methods: {
            handleClick(res) {
                this.search.status = this.activeName;
                this.getList();
            },
            toSearch() {
                this.dialogVisible = false;
                this.getList();
            },
            clearCat() {
                this.search.cats = [];
                this.getList();
            },
            changeTime() {
                if (this.datetime) {
                    this.search.date_start = this.datetime[0];
                    this.search.date_end = this.datetime[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.getList();
            },
            getList() {
                this.$emit('to-search', this.search);
                this.checkClear();
            },
            clearWhere() {
                this.search.cats = [];
                this.search.keyword = '';
                this.search.date_start = null;
                this.search.date_end = null;
                this.datetime = [];
                this.getList();
            },
            checkClear() {
                if (this.search.keyword || (this.search.cats && this.search.cats.length > 0)
                    || (this.search.date_start && this.search.date_end)) {
                    this.isShowClear = true;
                } else {
                    this.isShowClear = false;
                }
            },
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.search.cats = arr;
                this.getList();
            },
        },
        created() {
            this.search = this.newSearch;
            if (this.search.date_start && this.search.date_end) {
                this.datetime = [this.search.date_start, this.search.date_end];
            }
            this.activeName = this.newActiveName;
            this.checkClear();
        }
    })
</script>