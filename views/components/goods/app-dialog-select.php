<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/22
 * Time: 11:13
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-dialog-dialog .cover-pic {
        height: 50px;
        width: 50px;
        margin-right: 10px
    }

    .app-dialog-dialog {
        min-width: 700px;
    }

    .app-dialog-dialog .el-table {
        position: static;
    }
</style>
<template id="app-dialog-select">
    <div class="app-dialog-select">
        <el-dialog append-to-body :title="title" :visible.sync="visible" :close-on-click-modal="false"
                   custom-class="app-dialog-dialog" :before-close="close">
            <div>
                <el-input v-model="search.keyword" placeholder="根据名称搜索" @keyup.enter.native="getDetail(1)">
                    <el-button slot="append" @click="getDetail(1)">搜索</el-button>
                </el-input>
                <el-table border v-loading="listLoading" :data="list" style="margin-top: 24px;" height="500"
                          @select-all="handleSelectionChange" @select="select">
                    <el-table-column :selectable="status" type="selection" width="60px" label="ID" props="id" v-if="multiple">
                    </el-table-column>
                    <el-table-column width="100px" label="ID" props="id" v-else>
                        <template slot-scope="props">
                            <el-radio-group v-model="radioSelection" @change="handleSelectionChange(props.row)">
                                <el-radio :disabled="props.row.select" :label="props.row.id"></el-radio>
                            </el-radio-group>
                        </template>
                    </el-table-column>
                    <el-table-column width="100px" label="ID" props="id" v-if="multiple">
                        <template slot-scope="props">
                            {{props.row.id}}
                        </template>
                    </el-table-column>
                    <el-table-column label="名称">
                        <template slot-scope="props">
                            <div flex="dir:left cross:center">
                                <img style="height: 50px;width: 50px;margin-right: 10px;" :src="props.row.goodsWarehouse.cover_pic" alt="">
                                <app-ellipsis :line="2">{{props.row[listKey]}}</app-ellipsis>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div style="margin-top: 24px;">
                <el-row>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="getDetail"
                            layout="prev, pager, next"
                            :total="pagination.total_count">
                    </el-pagination>
                    <el-button type="primary" size="small" style="float: right" @click="confirm">选择</el-button>
                </el-row>
            </div>
        </el-dialog>
        <div @click="click" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('app-dialog-select', {
        template: '#app-dialog-select',
        props: {
            url: {
                type: String,
                default: 'mall/goods/index'
            },
            default: Array,
            multiple: Boolean,
            title: {
                type: String,
                default: '商品选择'
            },
            listKey: {
                type: String,
                default: 'name'
            },
            params: Object,
            max: {
                type: Number,
                default: -1
            }
        },
        data() {
            return {
                visible: false,
                listLoading: false,
                list: [],
                pagination: null,
                radioSelection: 0,
                search: {
                    keyword: ''
                },
                multipleSelection: []
            }
        },
        methods: {
            click() {
                this.getDetail(1);
                this.visible = !this.visible;
            },
            getDetail(page) {
                this.list = [];
                this.listLoading = true;
                let params = Object.assign({
                    r: this.url,
                    search: this.search,
                    page: page
                }, this.params);
                console.log(params);
                request({
                    params: params
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.multipleSelection = [];
                        if(!this.multiple) {
                            for(let j in this.list) {
                                this.list[j].select = false;
                                for(let i in this.default) {
                                    if(this.default[i].id == this.list[j].id) {
                                        this.list[j].select = true;
                                    }
                                }
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            handleSelectionChange(val) {
                let list = val;
                if (this.max >= 0) {
                    if (list.length > this.max) {
                        list.splice(this.max, list.length - this.max);
                    }
                }
                this.multipleSelection = list;
            },
            confirm() {
                this.$emit('selected', this.multipleSelection);
                this.visible = false;
                this.radioSelection = 0;
                this.search.keyword = '';
                this.$emit('input', this.multipleSelection);
            },
            status(row, index) {
                let that = this;
                let list = that.multipleSelection;
                if(that.default.length > 0) {
                    for(let i in that.default) {
                        if (that.default[i].id == row.id) {
                            return false
                        }
                    }
                }
                if (that.max >= 0) {
                    if (list.length >= that.max) {
                        let flag = false;
                        list.forEach(item => {
                            if (item == row) {
                                flag = true;
                            }
                        });
                        return flag
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            },
            close() {
                this.visible = false;
                this.search.keyword = '';
            },
            select(val, row) {
                this.multipleSelection = val;
            }
        }
    });
</script>
