<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 9:35
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
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
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="listLoading">
        <div slot="header">
            <span>包邮规则</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/free-delivery-rules/edit'})">添加规则
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="输入规则名称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID">

                </el-table-column>
                <el-table-column prop="name" label="包邮规则名称"></el-table-column>
                <el-table-column prop="price" label="订单金额"></el-table-column>
                <el-table-column label="地区" width='500px'>
                    <template slot-scope="scope">
                        <el-tag style="margin: 4px;border:0"
                                type="info"
                                v-for="(item, index) in scope.row.detail">{{item.name}}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="280px">
                    <template slot-scope="scope">
                        <el-button size="small" type="text" @click="edit(scope.row.id)" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="small" type="text" @click="destroy(scope.row.id)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>            
        </div>
        <div flex="box:last cross:center">
            <div style="visibility: hidden">
                <el-button plain type="primary" size="small">批量操作1</el-button>
                <el-button plain type="primary" size="small">批量操作2</el-button>
            </div>
            <div>
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :page-size="pagination.pageSize"
                        :current-page.sync="pagination.page"
                        :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                listLoading: false,
                list: [],
                keyword: '',
                pagination: {},
                loading: false
            };
        },
        mounted() {
            this.loadData(1);
        },
        methods: {
            search() {
                this.loadData(1);
            },
            loadData(page) {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/free-delivery-rules/index',
                        page: page,
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
                    this.$message.error(e.data.msg);
                });
            },
            pageChange(page) {
                this.loadData(page);
            },
            edit(id) {
                navigateTo({
                    r: 'mall/free-delivery-rules/edit',
                    id: id
                })
            },
            destroy(id){
                this.$confirm('此操作将删除该运费规则, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/free-delivery-rules/destroy',
                            id: id
                        },
                        method: 'get'
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.loadData(getQuery('page'));
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            }
        }
    });
</script>