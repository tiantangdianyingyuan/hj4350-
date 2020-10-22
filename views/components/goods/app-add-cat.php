<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .app-add-cat .app-goods-cat-list {
        border: 1px solid #E8EAEE;
        border-radius: 5px;
        margin-top: -5px;
        padding: 10px 0;
        overflow: scroll;
        height: 400px;
    }

    .app-add-cat .app-goods-cat-list .cat-item {
        cursor: pointer;
        padding: 5px 10px;
    }

    .app-add-cat .app-goods-cat-list .active {
        background: #FAFAFA;
    }

    .app-add-cat .el-checkbox {
        margin-right: 0;
    }

    .app-add-cat .tag-box {
        margin: 20px 0;
    }

    .app-add-cat .tag-box .tag-item {
        margin-right: 5px;
    }
</style>

<template id="app-add-cat">
    <div class="app-add-cat">
        <el-dialog :title="title" width="1100px" :visible.sync="dialogVisible">
            <el-row v-loading="dialogLoading" :gutter="20" style="margin-top: -30px;">
                <template v-if="options.length > 0">
                    <el-col :span="8">
                        <h3>一级分类</h3>
                        <div class="app-goods-cat-list active">
                            <div :class="{'active': current1 == option.value ? true : false}" class="cat-item"
                                 flex="dir:left box:first"
                                 v-for="(option,index) in options"
                                 :key="option.value">
                                <el-checkbox @change="optionClick(option)" v-model="option.checked"
                                             :label="option.value" size="mini">
                                    <span style="display: none;">{{option.label}}</span>
                                </el-checkbox>
                                <div @click="selectCats(option,1)" flex="box:last cross:center">
                                    <span>{{option.label}}</span>
                                    <i v-if="option.children" class="el-icon-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="8" v-if="children.length > 0">
                        <h3>二级分类</h3>
                        <div class="app-goods-cat-list">
                            <div :class="{'active': current2 == option.value ? true : false}" class="cat-item"
                                 flex="dir:left box:first"
                                 v-for="(option,index) in children"
                                 :key="option.value">
                                <el-checkbox @change="optionClick(option)" v-model="option.checked"
                                             :label="option.value" size="mini">
                                    <span style="display: none;">{{option.label}}</span>
                                </el-checkbox>
                                <div @click="selectCats(option,2)" flex="box:last cross:center">
                                    <span>{{option.label}}</span>
                                    <i v-if="option.children" class="el-icon-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="8" v-if="third.length > 0">
                        <h3>三级分类</h3>
                        <div class="app-goods-cat-list">
                            <div :class="{'active': current3 == option.value ? true : false}" class="cat-item"
                                 flex="dir:left box:first"
                                 v-for="(option,index) in third"
                                 :key="option.value">
                                <el-checkbox @change="optionClick(option)" v-model="option.checked"
                                             :label="option.value" size="mini">
                                    <span style="display: none;">{{option.label}}</span>
                                </el-checkbox>
                                <div @click="selectCats(option,3)" flex="box:last cross:center">
                                    <span>{{option.label}}</span>
                                    <i v-if="option.children" class="el-icon-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </el-col>
                </template>
                <template v-else>
                    <div flex="main:center" style="align-items: center;margin-top: 20px;">
                        <span>无系统分类</span>
                        <el-button style="display: inline-block;margin-left: 10px" flex="main:center" type="primary"
                                   size="small"
                                   @click="$navigate({r: 'mall/cat/edit'})">
                            请先添加商品分类
                        </el-button>
                    </div>
                </template>
            </el-row>
            <div class="tag-box" v-if="cats.length > 0">
                <el-tag type="warning" class="tag-item" @close="deleteCat(item, index)" closable v-for="(item, index) in cats"
                        :key="item.value">
                    {{item.label}}
                </el-tag>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button size='small' @click="dialogVisible = false">取 消</el-button>
                <el-button size='small' type="primary" @click="dialogSubmit">确 定</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-add-cat', {
        template: '#app-add-cat',
        props: {
            mch_id: {
                type: Number,
                default: 0
            },
            newCats: {
                type: Array,
                default: function () {
                    return []
                }
            }
        },
        data() {
            return {
                dialogVisible: false,
                options: [],// 商品分类列表
                children: [],
                third: [],
                cats: [], //用于前端已选的分类展示
                dialogLoading: false,
                title: '选择分类',
                current1: '',
                current2: '',
                current3: '',
            }
        },
        methods: {
            openDialog() {
                let self = this;
                this.getCats();
                self.title = self.mch_id > 0 ? '选择多商户分类' : '选择分类';
                this.cats = [];
                this.children = [];
                this.third = [];
                this.dialogVisible = true;
            },
            // 获取商品分类
            getCats() {
                let self = this;
                self.dialogLoading = true;
                request({
                    params: {
                        r: 'mall/cat/options',
                        mch_id: self.mch_id
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.dialogLoading = false;
                    if (e.data.code === 0) {
                        self.options = e.data.data.list;
                        self.setCats();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.dialogLoading = false;
                });
            },
            setCats() {
                let self = this;
                self.options.forEach(function (item) {
                    item.checked = false;
                    self.newCats.forEach(function (cItem) {
                        if (item.value === cItem) {
                            item.checked = true;
                            self.cats.push({
                                label: item.label,
                                value: item.value,
                            })
                        }
                    });
                    if (item.children) {
                        item.children.forEach(function (item2) {
                            item2.checked = false;
                            self.newCats.forEach(function (cItem) {
                                if (item2.value === cItem) {
                                    item2.checked = true;
                                    self.cats.push({
                                        label: item2.label,
                                        value: item2.value,
                                    })
                                }
                            });
                            if (item2.children) {
                                item2.children.forEach(function (item3) {
                                    item3.checked = false;
                                    self.newCats.forEach(function (cItem) {
                                        if (item3.value === cItem) {
                                            item3.checked = true;
                                            self.cats.push({
                                                label: item3.label,
                                                value: item3.value,
                                            })
                                        }
                                    });
                                })
                            }
                        })
                    }
                })
            },
            deleteCat(option, index) {
                let self = this;
                self.cats.splice(index, 1);
                self.options.forEach(function (item) {
                    if (item.value === option.value) {
                        item.checked = false;
                    }
                    if (item.children) {
                        item.children.forEach(function (item2) {
                            if (item2.value === option.value) {
                                item2.checked = false
                            }
                            if (item2.children) {
                                item2.children.forEach(function (item3) {
                                    if (item3.value === option.value) {
                                        item3.checked = false
                                    }
                                })
                            }
                        })
                    }
                })
            },
            optionClick(option) {
                let self = this;
                let sign = true;
                self.cats.forEach(function (item, index) {
                    // 移除分类
                    if (option.value === item.value) {
                        sign = false;
                        self.cats.splice(index, 1);
                    }
                });
                // 新增分类
                if (sign && option.checked) {
                    self.cats.push({
                        label: option.label,
                        value: option.value,
                    })
                }
            },
            // 选择分类
            selectCats(option, type) {
                let self = this;
                if (type === 1) {
                    self.current1 = option.value;
                    self.children = [];
                    if (option.children) {
                        option.children.forEach(function (item) {
                            self.children.push(item);
                        })
                    }
                }

                if (type === 2) {
                    self.current2 = option.value;
                    self.third = [];
                    if (option.children) {
                        option.children.forEach(function (item) {
                            self.third.push(item);
                        })
                    }
                }

                if (type === 3) {
                    self.current3 = option.value;
                }
            },
            dialogSubmit() {
                this.dialogVisible = false;
                this.$emit('select', this.cats)
            }
        }
    })
</script>
