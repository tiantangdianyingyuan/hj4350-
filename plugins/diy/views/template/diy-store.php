<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/28
 * Time: 16:04
 */
?>
<style>
    .diy-store .store-list {
        min-height: 100px;
        background: #fff;
    }

    .diy-store .store-item {
        border-bottom: 1px solid #e2e2e2;
        padding: 20px;
        color: #606266;
    }

    .diy-store .edit-item {
        border: 1px solid #dcdfe6;
        padding: 5px;
        margin-bottom: 5px;
        line-height: normal;
        color: #606266;
    }

    .diy-store .edit-item-options {
        position: relative;
    }

    .diy-store .edit-item-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }
</style>
<template id="diy-store">
    <div class="diy-store">
        <div class="diy-component-preview">
            <div class="store-list">
                <div class="store-item" v-for="(item,index) in data.list" flex="box:last cross:center">
                    <div flex="box:first cross:center">
                        <div>
                            <img :src="item.picUrl" style="width: 120px;height: 120px;margin-right: 20px">
                        </div>
                        <div style="line-height: 42px">
                            <div v-if="data.showName">{{item.name}}</div>
                            <div v-if="data.showScore" flex>
                                <div>评分：</div>
                                <div>
                                    <img :src="data.scorePicUrl" style="width: 20px;height: 20px;margin-right: 5px;">
                                    <img :src="data.scorePicUrl" style="width: 20px;height: 20px;margin-right: 5px;">
                                    <img :src="data.scorePicUrl" style="width: 20px;height: 20px;margin-right: 5px;">
                                    <img :src="data.scorePicUrl" style="width: 20px;height: 20px;margin-right: 5px;">
                                    <img :src="data.scorePicUrl" style="width: 20px;height: 20px;margin-right: 5px;">
                                </div>
                            </div>
                            <div v-if="data.showTel">
                                <span>电话: </span>
                                <span>{{item.mobile}}</span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: center">
                        <img :src="data.navPicUrl" style="width: 50px;height: 50px;display: block;margin: 0 auto;">
                        <div>一键导航</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="导航图标">
                    <app-image-upload v-model="data.navPicUrl" width="50" height="50"></app-image-upload>
                </el-form-item>
                <el-form-item label="评分图标">
                    <app-image-upload v-model="data.scorePicUrl" width="20" height="20"></app-image-upload>
                </el-form-item>
                <el-form-item label="显示门店名称">
                    <el-switch v-model="data.showName"></el-switch>
                </el-form-item>
                <el-form-item label="显示评分">
                    <el-switch v-model="data.showScore"></el-switch>
                </el-form-item>
                <el-form-item label="显示电话">
                    <el-switch v-model="data.showTel"></el-switch>
                </el-form-item>
                <el-form-item label="门店列表">
                    <div v-for="(item,index) in data.list" class="edit-item" style="max-width: 450px">
                        <div class="edit-item-options">
                            <el-button @click="deleteStore(index)" icon="el-icon-delete" type="primary"
                                       style="top: -6px;right: -31px;"></el-button>
                        </div>
                        <div flex>
                            <div style="width: 40px;">ID:</div>
                            <div>{{item.id}}</div>
                        </div>
                        <div flex>
                            <div style="width: 40px;">名称:</div>
                            <div>{{item.name}}</div>
                        </div>
                        <div flex>
                            <div style="width: 40px;">电话:</div>
                            <div>{{item.mobile}}</div>
                        </div>
                    </div>
                    <el-button size="small" @click="storeDialogVisible = true">添加门店</el-button>
                </el-form-item>
            </el-form>
        </div>
        <el-dialog title="选择门店" :visible.sync="storeDialogVisible" @open="storeDialogOpen">
            <el-table :data="store.list" v-loading="store.loading" style="margin-bottom: 20px">
                <el-table-column prop="id" label="ID" width="100px"></el-table-column>
                <el-table-column prop="name" label="名称"></el-table-column>
                <el-table-column prop="mobile" label="电话"></el-table-column>
                <el-table-column label="操作" width="100px">
                    <template slot-scope="scope">
                        <el-button @click="addStore(scope.row)" size="small">选择</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: center">
                <el-pagination
                        v-if="store.pagination"
                        style="display: inline-block"
                        background
                        @current-change="storePageChange"
                        layout="prev, pager, next, jumper"
                        :page-size.sync="store.pagination.pageSize"
                        :total="store.pagination.totalCount">
                </el-pagination>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-store', {
        template: '#diy-store',
        props: {
            value: Object,
        },
        data() {
            return {
                storeDialogVisible: false,
                store: {
                    page: 1,
                    list: null,
                    loading: false,
                    pagination: null,
                },
                data: {
                    navPicUrl: _currentPluginBaseUrl + '/images/nav-icon.png',
                    scorePicUrl: _currentPluginBaseUrl + '/images/score-icon.png',
                    showName: true,
                    showScore: true,
                    showTel: true,
                    list: [],
                },
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            storeDialogOpen() {
                if (this.store.list) {
                    return;
                }
                this.loadStoreData();
            },
            loadStoreData() {
                this.store.loading = true;
                this.$request({
                    params: {
                        r: 'mall/store/index',
                        page: this.store.page,
                    }
                }).then(response => {
                    this.store.loading = false;
                    if (response.data.code === 0) {
                        this.store.list = response.data.data.list;
                        this.store.pagination = response.data.data.pagination;
                    }
                }).catch(e => {
                });
            },
            storePageChange(page) {
                this.store.page = page;
                this.loadStoreData();
            },
            addStore(item) {
                this.data.list.push({
                    id: item.id,
                    name: item.name,
                    mobile: item.mobile,
                    score: parseFloat(item.score),
                    picUrl: item.cover_url,
                });
                this.storeDialogVisible = false;
            },
            deleteStore(index) {
                this.data.list.splice(index, 1);
            },
        }
    });
</script>