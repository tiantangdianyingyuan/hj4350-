<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-transfer .transfer {
        width: 280px;
        height: 500px;
    }

    .app-transfer .transfer .cat-header {
        padding: 0 16px;
        height: 40px;
        line-height: 40px;
        border-bottom: 1px solid #ebeef5;
        background-color: #f5f7fa;
    }

    .app-transfer .transfer .cat-body {
        overflow-y: auto;
        height: 460px;
        padding: 16px;
        width: 100%;
    }

    .app-transfer .transfer .cat-next {
        color: #353535;
        text-align: right;
        font-size: 10px;
        padding: 0;
    }

    .app-transfer .middle {
        border-radius: 36px;
        width: 36px;
        height: 36px;
        margin: 0 30px;
        font-size: 10px;
    }
</style>

<template id="app-transfer">
    <div class="app-transfer">
        <el-form ref="form" :data="form">
            <el-input size="small" style="display: none"></el-input>
            <div flex="dir:left">
                <template v-for="(item, index) in list">
                    <div v-if="index == 'middle'" flex="dir:left cross:center">
                        <el-button class="middle" type="text" icon="el-icon-arrow-right" :disabled="true"
                                   :style="middleStyle()"></el-button>
                    </div>
                    <el-card class="transfer" shadow="never" v-loading="item.loading" v-else
                             body-style="height: 100%;padding: 0">
                        <div class="cat-header" v-if="item.grandParent">
                            <el-button type="text" @click="next(0, index)" style="margin: 0;">
                                分类/
                            </el-button>
                            <el-button type="text" @click="next(item.grandParent.id, index)" style="margin: 0;">
                                {{item.grandParent.name}}/
                            </el-button>
                            {{item.parent.name}}
                        </div>
                        <div class="cat-header" v-else-if="item.parent">
                            <el-button type="text" @click="next(0, index)" style="margin: 0;">
                                分类/
                            </el-button>
                            {{item.parent.name}}
                        </div>
                        <div class="cat-header" v-else>分类</div>
                        <div class="cat-body">
                            <el-input size="small" suffix-icon="el-icon-search" v-model="item.keyword"
                                      @keyup.enter.native="search(index)"></el-input>
                            <div style="width: 100%;margin-top: 16px;" v-for="cat in item.list"
                                 flex="dir:left box:first">
                                <app-ellipsis :line="1">
                                    <el-radio :label="cat.id" v-model="item.selectCat"
                                              style="max-width: 140px;font-size: 14px;color: #303133">{{cat.name}}
                                    </el-radio>
                                </app-ellipsis>
                                <el-button class="cat-next" type="text" icon="el-icon-arrow-right"
                                           v-if="cat.child.length > 0" @click="next(cat.id, index)"></el-button>
                            </div>
                        </div>
                    </el-card>
                </template>
            </div>
            <el-form-item>
                <el-button type="primary" size="mini" @click="submit" :loading="btnLoading">开始转移</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    Vue.component('app-transfer', {
        template: '#app-transfer',
        props: {

        },
        data() {
            return {
                form: {},
                btnLoading: false,
                selectCat: '',
                list: {
                    before: {
                        keyword: '',
                        selectCat: 0,
                        list: [],
                        pagination: null,
                        loading: false,
                        parent: null,
                        grandParent: null,
                    },
                    middle: {

                    },
                    after: {
                        keyword: '',
                        selectCat: 0,
                        list: [],
                        pagination: null,
                        loading: false,
                        parent: null,
                        grandParent: null,
                    },
                },
                url: 'mall/cat/transfer-cat',
            }
        },
        created() {
            this.getCatList({
                r: this.url,
                id: 0
            });
        },
        methods: {
            getCatList(params) {
                if (this.selectCat == '') {
                    for (let i in this.list) {
                        this.list[i].loading = true;
                        this.list[i].list = [];
                    }
                } else {
                    this.list[this.selectCat].loading = true;
                    this.list[this.selectCat].list = [];
                }
                request({
                    params: params,
                    method: 'get',
                }).then(response => {
                    if (this.selectCat == '') {
                        for (let i in this.list) {
                            this.list[i].loading = false;
                        }
                    } else {
                        this.list[this.selectCat].loading = false;
                    }
                    if (response.data.code == 0) {
                        if (this.selectCat == '') {
                            for (let i in this.list) {
                                this.list[i].list = response.data.data.list;
                                this.list[i].pagination = response.data.data.pagination;
                                if (typeof params.keyword === 'undefined') {
                                    this.list[i].parent = response.data.data.parent;
                                    this.list[i].grandParent = response.data.data.grandParent;
                                }
                            }
                        } else {
                            this.list[this.selectCat].list = response.data.data.list;
                            this.list[this.selectCat].pagination = response.data.data.pagination;
                            if (typeof params.keyword === 'undefined') {
                                this.list[this.selectCat].parent = response.data.data.parent;
                                this.list[this.selectCat].grandParent = response.data.data.grandParent;
                            }
                        }
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    console.log(response);
                });
            },
            submit() {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'mall/goods/transfer',
                    },
                    method: 'post',
                    data: {
                        before: this.list.before.selectCat,
                        after: this.list.after.selectCat,
                    }
                }).then(response => {
                    this.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success(response.data.msg);
                    } else {
                        this.$message.error(response.data.msg);
                    }
                });
            },
            search(param) {
                let id = this.list[param].grandParent ?
                    this.list[param].grandParent.id :
                    (this.list[param].parent ? this.list[param].parent.id : 0);
                this.list[param].selectCat = 0;
                this.selectCat = param;
                this.getCatList({
                    r: this.url,
                    id: id,
                    keyword: this.list[param].keyword
                });
            },
            next(id, param) {
                this.selectCat = param;
                this.list[param].keyword = '';
                this.list[param].selectCat = 0;
                this.getCatList({
                    r: this.url,
                    id: id
                });
            },
            middleStyle() {
                if (this.list.before.selectCat > 0 && this.list.after.selectCat > 0) {
                    return `background-color: #409EFF;color: #fff`;
                } else {
                    return `background-color: #f7f5fa;color: rgba(0, 0, 0, 0.5);`;
                }
            }
        }
    })
</script>