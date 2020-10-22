<?php defined('YII_ENV') or exit('Access Denied'); ?>
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

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>打印机管理</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/printer/setting'})">打印设置
            </el-button>
            <el-button style="float: right; margin: -5px 20px" type="primary" size="small"
                       @click="$navigate({r:'mall/printer/edit'})">添加打印机
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width='300' prop="name" label="打印机名称"></el-table-column>
                <el-table-column prop="type" label="打印机品牌">
                    <template slot-scope="scope">
                        <div v-for="res in select" v-if="scope.row.type == res.value">{{res.label}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button type="text" @click="handleEdit(scope.$index, scope.row,list.id)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="handleDel(scope.$index, scope.row,list.id)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
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
                            :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper"
                            :total="pagination.total_count">
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
                loading: false,
                list: [],
                keyword: null,
                select:[],
                pagination: null,
            };
        },

        methods: {
            search: function() {
                this.loading = true;
                let keyword = this.keyword;
                request({
                    params: {
                        r: 'mall/printer/index',
                        keyword: keyword
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.select = e.data.data.select;
                        this.pagination = e.data.data.pagination;
                    }else{
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                }
                }).catch(e => {
                    this.loading = false;
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });        
            },

            //带着ID前往编辑页面
            handleEdit: function(row, column)
            {
                navigateTo({r: 'mall/printer/edit',id:column.id});
            },
            //分页
            pageChange(page) {
                this.loading = true;
                loadList('mall/printer',page).then(e => {
                    this.loading = false;
                    this.list = e.list;
                    this.pagination = e.pagination;
                });
            },

            //删除
            handleDel: function(row, column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = { id: column.id};
                    request({
                        params: {
                            r: 'mall/printer/destroy'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            const h = this.$createElement;
                            this.$message({
                                message: '删除成功',
                                type: 'success'
                            });
                            setTimeout(function(){
                                location.reload();
                            },300);
                        }else{
                            this.$alert(e.data.msg, '提示', {
                              confirmButtonText: '确定'
                            })
                    }
                    }).catch(e => {
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                    });
                }).catch(() => {
                    this.$message.info('已取消删除')
                });
            }
        },
        created() {
            this.loading = true;
            // 获取列表
            loadList('mall/printer/index').then(e => {
                this.loading = false;
                this.list = e.list;
                this.select = e.select;
                this.pagination = e.pagination;
            });
        }
    })
</script>
