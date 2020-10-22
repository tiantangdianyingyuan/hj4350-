<?php
/**
 * @link:http://www.zjhejiang.com/
 * @copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/11/29
 * Time: 11:02
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
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

    .el-input-group__append .el-button {
        margin: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" style="justify-content:space-between;display: flex">
            <span>运费规则</span>
            <el-button style="margin: -5px 0" type="primary" size="small"
                @click="$navigate({r:'mall/postage-rule/edit'})">添加规则
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="输入规则名称搜索" v-model="keyword" clearable @clear="load">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="list"
                      border
                      style="width: 100%;margin-bottom: 15px">
                <el-table-column
                    prop="name"
                    label="规则名称">
                </el-table-column>
                <el-table-column
                    prop="status"
                    label="是否默认"
                    width="180">
                    <template slot-scope="scope">
                        <el-radio v-model="scope.row.status" :label="1" @change="setStatus(scope.row)">默认</el-radio>
                    </template>
                </el-table-column>
                <el-table-column
                    width="180"
                    label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row.id)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="destroy(scope.row)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :page-size="pagination.pageSize"
                        :current-page="pagination.current_page"
                        :total="pagination.totalCount">
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
                list: [],
                listLoading: false,
                radio: 1,
                items: ['Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item 5', 'Item 6', 'Item 7', 'Item 8'],
                test: 1,
                keyword: '',
                pagination: null
            }
        },
        mounted: function () {
            this.load();
        },
        methods: {
            search() {
                this.load();
            },
            load(page){
                let self = this;
                this.listLoading = true;
                this.list = [];
                request({
                    params: {
                        r: 'mall/postage-rule/index',
                        page: page || 1,
                        keyword: this.keyword
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            edit(id) {
                navigateTo({
                    r: 'mall/postage-rule/edit',
                    id: id
                })
            },
            destroy(param) {
                this.$confirm('此操作将删除该运费规则, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/postage-rule/destroy',
                            id: param.id
                        },
                        method: 'get'
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.load();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            pageChange(page){
                this.load(page);
            },
            setStatus(param){
                for (let i in this.list) {
                    this.list[i].status = 0;
                }
                param.status = 1;
                request({
                    params: {
                        r: '/mall/postage-rule/status',
                        id: param.id
                    },
                    method: 'get'
                }).then(e => {
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            }
        }
    });
</script>

