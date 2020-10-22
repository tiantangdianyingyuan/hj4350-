<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" class="box-card" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>想买好物圈</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="edit()">添加商品</el-button>
        </div>
        <div class="table-body">
            <el-form @submit.native.prevent="commonSearch" size="small" :inline="true" :model="search">
                <el-form-item style="margin-bottom: 0">
                    <div class="input-item">
                        <el-input size="small" placeholder="请输入商品名称" v-model="search.keyword" clearable @clear='commonSearch'>
                            <el-button slot="append" icon="el-icon-search" @click="commonSearch"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table :data="list" border style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="goods.id" label="商品ID" width="80"></el-table-column>
                <el-table-column label="商品">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px">
                                <app-image mode="aspectFill"
                                           :src="scope.row.goods.goodsWarehouse.cover_pic"></app-image>
                            </div>
                            <div>
                                <!-- <el-tag size="mini" type="success">{{scope.row.plugin_name}}</el-tag> -->
                                <app-ellipsis :line="1">{{scope.row.goods.goodsWarehouse.name}}</app-ellipsis>
                                <el-tag size="mini" v-for="item in scope.row.goods.goodsWarehouse.cats" :key="item.id">
                                    {{item.name}}
                                </el-tag>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="goods.price" label="售价"></el-table-column>
                <el-table-column prop="like_user_count" label="想买人数"></el-table-column>
                <el-table-column
                        label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="scope.row.like_user_count == 0" @click="destroy(scope.row, scope.$index)"
                                   type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div></div>
                <div>
                    <el-pagination
                            v-if="pageCount > 0"
                            @current-change="pagination"
                            background
                            layout="prev, pager, next, jumper"
                            :page-count="pageCount">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: '',
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                id: null,
                sort: 0
            };
        },
        created() {
            this.getList();
        },
        methods: {
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },

            editSort(row) {
                this.id = row.id;
                this.sort = row.goods.sort;
            },

            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/shopping/mall/like-goods/index',
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/shopping/mall/like-goods/users',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/shopping/mall/like-goods/edit',
                    });
                }
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/shopping/mall/like-goods/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
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
            // 搜索
            commonSearch() {
                this.page = 1;
                this.getList();
            }
        }
    });
</script>
