<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/6
 * Time: 9:35
 */
?>
<style>
    .header>span {
        padding: 9px 15px;
    }
    .header button {
        float: right;
         margin: -5px 0;
    }
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
    .el-form-item.el-form-right {
        margin-left: 30px;
    }
    .el-form-item.el-form-right .el-form-item__content .el-input {
        width: 50%;
    }
    .el-select-dropdown {
        min-width: 105px !important;
    }
    .delete {
        background-color: #f5f7fa;
        height: 45px;
        line-height: 45px;
        padding-left: 20px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="header">
            <span>满减列表</span>
            <el-button
                type="primary"
                size="small"
                @click="$navigate({r:'mall/full-reduce/edit'})"
            >新增满减
            </el-button>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" >
                <el-form size="small" :inline="true" :model="search">
                    <!-- 搜索框 -->
                    <el-form-item prop="time">
                        <span slot="label" style="color:#606266">活动名称</span>
                        <div class="input-item">
                            <el-input @keyup.enter.native="searchList" size="small" placeholder="请输入活动名称搜索"
                                      v-model="search.keyword" clearable @clear='searchList'>
                                <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                    <el-form-item class="el-form-right">
                        <span slot="label" style="color:#606266;">活动状态</span>
                        <el-select v-model="search.status" placeholder="请选择" @change="searchList">
                            <el-option
                                label="全部"
                                :value="-1">
                            </el-option>
                            <el-option
                                label="未开始"
                                :value="0">
                            </el-option>
                            <el-option
                                label="进行中"
                                :value="1">
                            </el-option>
                            <el-option
                                label="已结束"
                                :value="2">
                            </el-option>
                            <el-option
                                    label="下架中"
                                    :value="3">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col class="delete">
                <el-button size="small" @click="delList">删除</el-button>
            </el-col>
            <!--列表-->
            <el-table v-loading="loading" border @selection-change="handleSelectionChange" :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column
                        type="selection"
                        width="55">
                </el-table-column>
                <el-table-column label="活动名称" prop="name">
                </el-table-column>
                <el-table-column label="状态" >
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.time_status == 0" type="warning">下架中</el-tag>
                        <el-tag v-if="scope.row.time_status == 1" type="info">未开始</el-tag>
                        <el-tag v-if="scope.row.time_status == 2">进行中</el-tag>
                        <el-tag v-if="scope.row.time_status == 3" type="danger">已结束</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="优惠时间">
                    <template slot-scope="scope">
                        {{scope.row.start_at}} 至 {{scope.row.end_at}}
                    </template>
                </el-table-column>
                <el-table-column  label="操作" fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="scope.row.time_status != 3" @click="edit(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑活动" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="scope.row.time_status != 3" @click="takeOff(scope.row)" type="text" circle size="mini">
                            <el-tooltip  class="item" effect="dark" :content="scope.row.time_status == 0 ? '上架' : '下架'" placement="top">
                                <img :src="scope.row.time_status == 0 ? 'statics/img/plugins/shelves.png' : 'statics/img/plugins/take-off.png'" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <div  class="fixed-pagination">
                <el-pagination
                    background
                    hide-on-single-page
                    layout="prev, pager, next, jumper"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count">
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
                search: {
                    keyword: '',
                    status: -1,
                    page: 1
                },
                loading: false,
                list: [],
                pagination: {
                    pageSize: 1,
                    total_count: 1
                },
                deleteList: []
            };
        },
        mounted() {
            this.getList();
        },
        methods: {
            pageChange(e) {
                this.search.page = e;
                this.getList();
            },

            searchList() {
                this.getList();
            },

            getList() {
                this.loading = true;
                request({
                    params: {
                        r: `/mall/full-reduce/index`,
                        keyword: this.search.keyword,
                        status: this.search.status,
                        keyword_label: 'name',
                        page: this.search.page
                    },
                    method:'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }
                })
            },

            destroy(e, index) {
                this.$confirm('删除该条优惠, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: `/mall/full-reduce/edit-status`
                        },
                        method:'post',
                        data: {
                            ids: [e.id],
                            type: 'del'
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$delete(this.list, index);
                            this.getList();
                        }
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },

            takeOff(row) {
                let str = '';
                row.status === '1' ? str = 'down' : str = 'up';
                this.$confirm( row.status === '1'
                    ? '此操作将下架该条优惠, 是否继续?' : '此操作将上架该条优惠, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: `/mall/full-reduce/edit-status`
                        },
                        method:'post',
                        data: {
                            ids: [row.id],
                            type: str
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.getList()
                        } else {
                            this.$message({
                                type: 'warning',
                                message: e.data.msg
                            });
                        }
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },

            edit(row) {
                console.log(row.id)
                this.$navigate({
                    r: 'mall/full-reduce/edit',
                    id: row.id
                });
            },

            handleSelectionChange(e) {
                this.deleteList = [];
                e.forEach((item) => {
                    this.deleteList.push(item.id)
                })
            },

            delList() {
                if (this.deleteList.length === 0) {
                    this.$message({
                        type: 'warning',
                        message: '请先选择活动'
                    });
                    return;
                }
                this.$confirm('批量删除优惠, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: `/mall/full-reduce/edit-status`
                        },
                        method:'post',
                        data: {
                            ids: this.deleteList,
                            type: 'del'
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            let page = this.search.page;
                            if (this.deleteList.length === this.list.length) {
                                page >= 2 ? this.search.page = page -1 : 1;
                            }
                            this.getList();
                        } else {
                            this.$message({
                                type: 'warning',
                                message: e.data.msg
                            });
                        }
                    })
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