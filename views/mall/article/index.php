<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .set-el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
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

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>文章列表</span>
            <div style="float: right;margin-top: -5px">
                <el-button type="primary" @click="$navigate({r: 'mall/article/edit', article_cat_id:2})"
                           size="small">添加文章
                </el-button>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="list" highlight-current-row v-loading="listLoading" style="width: 100%;" border>
                <el-table-column prop="id" label="ID" width="150"></el-table-column>
                <el-table-column prop="title" label="标题"></el-table-column>
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
                <el-table-column prop="status" label="状态" width="150">
                    <template slot-scope="scope">
                        <el-switch
                                active-value="1"
                                inactive-value="0"
                                @change="switchStatus(scope.row)"
                                v-model="scope.row.status">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button class="set-el-button" size="mini" type="text" circle @click="$navigate({r: 'mall/article/edit', article_cat_id:article_cat_id, id:scope.row.id})">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button class="set-el-button" size="mini" type="text" circle @click="destroy(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
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
                keyword: '',
                list: [],
                listLoading: false,
                article_cat_id: 1,
                pageCount: 0,
                page: 1,
                id: 0,
                sort: 0,
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
            quit() {
                this.id = null;
            },
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },
            change(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: 'mall/article/edit'
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
                        r: 'mall/article/switch-status',
                    },
                    method: 'post',
                    data: {
                        status: row.status,
                        id: row.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        if(row.status == 1) {
                            self.$message.success('文章已显示');
                        }else if(row.status == 0) {
                            self.$message.success('文章已隐藏');
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },

            //删除
            destroy: function (column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/article/destroy'
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        location.reload();
                        this.listLoading = false;
                    }).catch(e => {
                        this.listLoading = false;
                    });

                });
            },
            //获取列表
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/article/index',
                        article_cat_id: this.article_cat_id,
                        page: this.page,
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>
