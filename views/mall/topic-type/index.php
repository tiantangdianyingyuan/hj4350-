<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .set-el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
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

    .input-item {
        display: inline-block;
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

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>专题分类</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/topic-type/edit'})">新增
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="searchList.keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="name" label="名称"></el-table-column>
                <el-table-column prop="sort" label="排序" width="250">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.sort}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="sort"
                                          autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px" icon="el-icon-error"
                                           circle @click="quit()"></el-button>
                            <el-button class="change-success" type="text" style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="change(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="120">
                    <template slot-scope="scope">
                        <el-switch active-value="1" inactive-value="0" @change="switchStatus(scope.row)"
                                   v-model="scope.row.status">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" class="set-el-button" size="mini" circle
                                   @click="handleEdit(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                        </el-tooltip>
                        </el-button>
                        <el-button type="text" class="set-el-button" size="mini" circle
                                   @click="handleDel(scope.$index, scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                        </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    background
                    hide-on-single-page
                   :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper"
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
                list: [],
                loading: false,
                pagination: {},
                page: 1,
                id: 0,
                sort: 0,
                searchList: {
                    keyword: '',
                }
            };
        },
        directives: {
            // 注册一个局部的自定义指令 v-focus
            focus: {
                // 指令的定义
                inserted: function (el) {
                    // 聚焦元素
                    el.querySelector('input').focus()
                }
            }
        },
        methods: {
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },

            quit() {
                this.id = null
            },

            change(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: 'mall/topic-type/edit'
                    },
                    method: 'post',
                    data: row,
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        this.id = null;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },
            search() {
                this.page = 1;
                this.getList();
            },
            switchStatus(row) {
                let self = this;
                request({
                    params: {
                        r: 'mall/topic-type/switch-status',
                    },
                    method: 'post',
                    data: {
                        status: row.status,
                        id: row.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        if(row.status == 1) {
                            self.$message.success('专题分类已显示');
                        }else if(row.status == 0) {
                            self.$message.success('专题分类已隐藏');
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            //带着ID前往编辑页面
            handleEdit: function (row, column) {
                navigateTo({r: 'mall/topic-type/edit', id: column.id});
            },
            // 选择页数
            pageChange: function (page) {
                this.loading = true;
                this.page = page;
                this.getList();
            },
            //删除
            handleDel: function (index, row) {
                let _this = this;
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = {id: row.id};
                    request({
                        params: {
                            r: 'mall/topic-type/destroy'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        const h = this.$createElement;
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        setTimeout(function () {
                            location.reload();
                        }, 300);
                    }).catch(e => {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    });
                })
            },

            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/topic-type/index',
                        page: this.page,
                        search: this.searchList,
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.loading = false;
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

        },
        mounted() {
            this.getList();
        }
    })
</script>