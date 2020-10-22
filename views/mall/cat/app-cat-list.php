<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-cat-list .input-item {
        display: inline-block;
        width: 250px;
    }

    .app-cat-list .input-item .el-input__inner {
        border-right: 0;
    }

    .app-cat-list .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-cat-list .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-cat-list .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-cat-list .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .app-cat-list .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-cat-list .table-body .cat-item {
        display: flex;
        justify-content: space-between;
        height: 65px;
        align-items: center;
        padding-left: 5px;
        border-top: 1px solid #F5F5F5;
        color: #000000;
        cursor: pointer;
        width: 100%;
    }

    .app-cat-list .active {
        background-color: #F5F5F5;
    }

    .app-cat-list .table-body .cat-item:first-of-type {
        border-top: 0;
    }

    .app-cat-list .table-body .cat-item .cat-name {
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .app-cat-list .table-body .cat-item .el-form-item {
        margin-bottom: 0;
    }

    .app-cat-list .table-body .cat-item .el-form-item .el-button {
        padding: 0;
        margin: 0 5px;
    }

    .app-cat-list .table-body .cat-item .el-input {
        width: 100px;
    }

    .app-cat-list .change {
        width: 80px;
    }

    .app-cat-list .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
        padding: 0;
    }

    .app-cat-list .cat-name-info {
        width: 100px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .app-cat-list .cat-list {
        white-space: nowrap;
    }

    .app-cat-list .cat-list .el-card {
        display: inline-block;
    }

    .app-cat-list .cat-list .el-card:first-of-type {
        margin-left: 0
    }

    .app-cat-list .cat-list .card-item-box {
        margin-right: 5px;
        height: 552px;
    }

    .app-cat-list .cat-id {
        width: 55px;
        color: #999;
        font-size: 14px;
        margin-left: 5px;
    }

    .app-cat-list .el-form--inline .el-form-item {
        margin-right: 0px;
    }

    .app-cat-list .cat-icon {
        margin-right: 10px;
    }

    .app-cat-list .cat-item .el-form-item {
        margin-bottom: 0;
    }

    .app-cat-list .edit-sort-box .el-button.is-circle {
        padding: 3px;
    }
    .app-cat-list .card-item-box .el-card__header {
        padding: 18px 0;
    }
</style>

<template id="app-cat-list">
    <div class="app-cat-list">
        <el-form size="small" :inline="true" :model="search" @submit.native.prevent>
            <template v-if="!isEditSort">
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="searchCat" clearable @clear="searchCat" size="small"
                                  placeholder="请输入搜索内容"
                                  v-model="search.keyword">
                            <el-button slot="append" icon="el-icon-search" @click="searchCat"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </template>
            <el-form-item v-if="!isEditSort">
                <el-button @click="isEditSort=true" style="margin-left: 10px" type="primary">编辑排序</el-button>
            </el-form-item>
            <el-form-item v-if="isEditSort">
                <el-button :loading="submitLoading" @click="storeSort" style="margin-left: 10px" type="primary">保存排序
                </el-button>
                <el-button @click="isEditSort=false" style="margin-left: 10px">取消编辑
                </el-button>
                <span style="margin-left: 10px;">拖动分类名称排序</span>
            </el-form-item>
        </el-form>
        <div class="cat-list" flex="dir:left box:mean">
            <el-card v-loading="listLoading"
                     v-for="(cItem, cIndex) in catList"
                     :key="cIndex"
                     shadow="never"
                     class="card-item-box"
                     body-style="padding:0;height: 500px;overflow:auto">
                <div slot="header" v-if="cItem.list.length > 0">
                    <el-row style="cursor: pointer;">
                        <el-col :span="2" flex="main:center">
                            <el-checkbox @change="allClick(cItem)" :disabled="cItem.id !== 1 ? true : false" :indeterminate="cItem.indeterminate" v-model="cItem.allChecked"></el-checkbox>
                        </el-col>
                        <el-col :span="22">
                            <span>{{cItem.title}}</span>
                        </el-col>
                    </el-row>
                </div>
                <div v-if="cItem.list.length > 0">
                    <draggable v-model="cItem.list" :options="{disabled:!isEditSort}">
                        <div :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                             v-for="(item,index) in cItem.list"
                             :key="index"
                             class="cat-item"
                             @click="select(item, cItem)"
                             :class="cItem.currentId == item.id ? 'active':''">
                            <el-row flex="cross:center" style="height: 50px">
                                <el-col :span="2" flex="main:center">
                                    <el-checkbox @change="itemChange(cItem)" :disabled="cItem.id !== 1 ? true : false"
                                                 v-model="item.checked"></el-checkbox>
                                </el-col>
                                <el-col :span="4">
                                    <el-tooltip class="item" effect="dark" :content="item.id" placement="top">
                                        <div class="cat-id">{{item.id}}</div>
                                    </el-tooltip>
                                </el-col>
                                <el-col :span="11" flex="cross:center">
                                    <app-image class="cat-icon" :src="item.pic_url" width="30px"
                                               height="30px"></app-image>
                                    <div class="cat-name-info">
                                        <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                            <span>{{item.name}}</span>
                                        </el-tooltip>
                                    </div>
                                </el-col>
                                <el-col :span="7">
                                    <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                             @submit.native.prevent>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="edit(item.id)">
                                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                    <img src="statics/img/mall/edit.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="destroy(item)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </div>
                    </draggable>
                </div>
            </el-card>
        </div>
        <!-- 分类搜索弹框-->
        <el-dialog :visible.sync="searchFinish">
            <el-table border :data="searchList" @row-click="rowClick">
                <el-table-column align="center" property="status_text" label="分类等级" width="200">
                    <template slot-scope="scope">
                        <span v-if="!scope.row.status_text">一级分类</span>
                        <span v-else>{{scope.row.status_text}}</span>
                    </template>
                </el-table-column>
                <el-table-column align="center" property="name" label="分类名称" width="300">
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center;justify-content: center">
                            <app-image style="margin-right: 10px" :src="scope.row.pic_url" width="30px"
                                       height="30px"></app-image>
                            <span>{{scope.row.name}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" class="set-el-button" size="mini" circle @click="edit(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" class="set-el-button" size="mini" circle @click="destroy(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-cat-list', {
        template: '#app-cat-list',
        data() {
            return {
                listLoading: false,
                editSortVisible: false,
                editSortForm: {
                    sort: 100,
                },

                submitLoading: false,
                isEditSort: false,
                catList: [
                    {
                        id: 1,
                        title: '一级分类',
                        list: [],
                        currentId: 0,
                        allChecked: false,
                        indeterminate: false,
                        select: [],
                    },
                    {
                        id: 2,
                        title: '二级分类',
                        list: [],
                        currentId: 0,
                        allChecked: false,
                        indeterminate: false,
                        select: [],
                    },
                    {
                        id: 3,
                        title: '三级分类',
                        list: [],
                        currentId: 0,
                        allChecked: false,
                        indeterminate: false,
                        select: [],
                    },
                ],
                searchFinish: false,
                searchList: [],
                search: {
                    keyword: ''
                },
            }
        },
        methods: {
            changeSort(e) {
                this.editSortVisible = true;
                this.editSortForm = e;
            },
            // 修改排序
            changeSortSubmit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/cat/sort',
                                id: this.editSortForm.id,
                                sort: this.editSortForm.sort
                            },
                            method: 'get',
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.editSortVisible = false
                                this.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                            } else {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'warning'
                                });
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 选中一级分类
            select(lItem, cItem) {
                if (cItem.id === 1) {
                    this.catList[1].list = lItem.child;
                    this.catList[2].list = [];
                }

                if (cItem.id === 2) {
                    this.catList[2].list = lItem.child;
                }

                // 选中效果
                cItem.currentId = lItem.id;

                if (this.isEditSort) {
                    return;
                }
            },
            // 获取数据
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/cat/index',
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    // 处理列表数据
                    let list = e.data.data.list;
                    self.catList[0].list = [];
                    self.catList[1].list = [];
                    self.catList[2].list = [];
                    list.forEach((lItem, lIndex) => {
                        lItem.checked = false;
                        if (lIndex === 0) {
                            self.catList[0].currentId = lItem.id;
                            self.catList[1].list = lItem.child
                        }

                    });
                    self.catList[0].list = list;
                    self.itemChange(self.catList[0])

                }).catch(e => {
                    self.listLoading = false;
                });
            },
            // 搜索editSortVisible
            searchCat() {
                let self = this;
                self.searchList = [];
                if (self.search.keyword == '') {
                    this.getList();
                    return false
                }
                request({
                    params: {
                        r: 'mall/cat/index',
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.searchFinish = true;
                    self.searchList = e.data.data.list;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 编辑
            edit(id) {
                navigateTo({
                    r: 'mall/cat/edit',
                    id: id,
                });
            },
            // 删除
            destroy(row) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/cat/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            switchStatus(id) {
                let self = this;
                request({
                    params: {
                        r: 'mall/cat/switch-status',
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            rowClick(row) {
                let self = this;
                self.catList[0].currentId = 0;
                self.catList[1].currentId = 0;
                self.catList[2].currentId = 0;

                self.catList[1].list = [];
                self.catList[2].list = [];

                self.catList[0].list.forEach(function (lItem) {
                    if (lItem.id == row.id) {
                        self.catList[0].currentId = lItem.id;

                        self.catList[1].list = lItem.child;
                    }
                    lItem.child.forEach((cItem1, cIndex1) => {
                        if (cItem1.id == row.id) {
                            self.catList[0].currentId = lItem.id;
                            self.catList[1].currentId = cItem1.id;

                            self.catList[1].list = lItem.child;
                            self.catList[2].list = cItem1.child;
                        }

                        cItem1.child.forEach((cItem2, cIndex2) => {
                            if (cItem2.id == row.id) {
                                self.catList[0].currentId = lItem.id;
                                self.catList[1].currentId = cItem1.id;
                                self.catList[2].currentId = cItem2.id;

                                self.catList[1].list = lItem.child;
                                self.catList[2].list = cItem1.child;
                            }
                        })


                    })
                })
                self.searchFinish = false;
            },
            storeSort() {
                let self = this;
                self.submitLoading = true;

                let firstList = [];
                let secondList = [];
                let thirdList = [];

                self.catList.forEach((cItem, cIndex) => {
                    cItem.list.forEach((lItem) => {
                        if (cIndex === 0) {
                            firstList.push({
                                id: lItem.id,
                                name: lItem.name
                            })
                        }
                        if (cIndex === 1) {
                            secondList.push({
                                id: lItem.id,
                                name: lItem.name
                            })
                        }
                        if (cIndex === 2) {
                            thirdList.push({
                                id: lItem.id,
                                name: lItem.name
                            })
                        }
                    });
                });

                request({
                    params: {
                        r: 'mall/cat/store-sort'
                    },
                    method: 'post',
                    data: {
                        first_list: JSON.stringify(firstList),
                        second_list: JSON.stringify(secondList),
                        third_list: JSON.stringify(thirdList),
                    }
                }).then(e => {
                    self.submitLoading = false;
                    if (e.data.code === 0) {
                        self.isEditSort = false;
                        self.$message.success(e.data.msg);
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.submitLoading = false;
                });
            },
            itemChange(cItem) {
                let self = this;
                let newSelect = [];
                cItem.list.forEach((lItem) => {
                    if (lItem.checked) {
                        newSelect.push(lItem.id)
                    }
                });
                cItem.select = newSelect;
                self.$emit('select', newSelect);
                cItem.indeterminate = !!(newSelect.length && newSelect.length !== cItem.list.length);
                cItem.allChecked = cItem.list.length === cItem.select.length
            },
            allClick(cItem) {
                let self = this;
                cItem.list.forEach((lItem) => {
                    lItem.checked = cItem.allChecked;
                });
                self.itemChange(cItem);
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>