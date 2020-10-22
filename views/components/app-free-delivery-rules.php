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
<div id="app-free-delivery-rules" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="listLoading">
        <div class="table-body">
            <div style="justify-content:space-between;display: flex">
                <div class="input-item">
                    <el-input @keyup.enter.native="search" size="small" placeholder="输入规则名称搜索" v-model="keyword" clearable @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
                <el-button style="float: right; margin: -5px 0; width: 80px; height: 32px;" type="primary" size="small"
                           @click="$navigate({r:'mall/free-delivery-rules/edit'})">添加规则
                </el-button>
            </div>

            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID" width='170px'></el-table-column>

                <el-table-column prop="name" label="规则名称" width='250px'></el-table-column>

                <el-table-column label="包邮类型" prop="type_text" width='470px'></el-table-column>

                <el-table-column
                    prop="status"
                    label="是否默认"
                    width="380">
                    <template slot-scope="scope">
                        <el-radio v-model="scope.row.status" :label="1" @change="setStatus(scope.row)">默认</el-radio>
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
        <div class="fixed-pagination">
            <el-pagination
                background
                hide-on-single-page
                @current-change="pageChange"
                layout="prev, pager, next, jumper"
                :page-size="pagination.pageSize"
                :current-page.sync="pagination.current_page"
                :total="pagination.totalCount">
            </el-pagination>
        </div>
    </el-card>
</div>
<script>
    Vue.component('app-free-delivery-rules', {
        template: '#app-free-delivery-rules',
        data() {
            return {
                listLoading: false,
                list: [],
                keyword: '',
                pagination: {
                    pageSize: 1,
                    current_page: 1,
                    totalCount: 1
                },
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
                        keyword: this.keyword,
                        limit: 10
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    let { code, data, msg} = e.data;
                    if (code === 0) {
                        this.list = data.list;
                        this.pagination.pageSize = Number(data.pagination.pageSize);
                        this.pagination.current_page = data.pagination.current_page;
                        this.pagination.totalCount = data.pagination.totalCount;
                    } else {
                        this.$message.error(msg);
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
                this.$confirm('此操作将删除该包邮规则, 是否继续?', '提示', {
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
                        if (e.data.code === 0) {
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
            },

            setStatus(param){
                for (let i in this.list) {
                    this.list[i].status = 0;
                }
                param.status = 1;
                request({
                    params: {
                        r: '/mall/free-delivery-rules/status',
                        id: param.id
                    },
                    method: 'get'
                }).then(e => {
                    let { code, msg } = e.data;
                    code === 0 ? this.$message.success(msg) : this.$message.error(msg);
                });
            }
        }
    });
</script>