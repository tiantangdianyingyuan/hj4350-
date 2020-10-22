<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/10
 * Time: 16:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
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
</style>

<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>卡密列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">新增卡密</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入卡密名称搜索" v-model="form.keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="listLoading" :data="list" border style="width: 100%">
                <el-table-column
                        prop="name"
                        label="卡密名称">
                </el-table-column>
                <el-table-column
                        prop="sales"
                        label="已售">
                </el-table-column>
                <el-table-column
                        prop="stock"
                        sortable
                        label="库存">
                    <template slot-scope="scope">
                        <span :style="{color: scope.row.stock<50 ? '#ff6363' : '#606266'}">{{scope.row.stock}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="created_at"
                        label="创建时间">
                </el-table-column>
                <el-table-column
                        prop="address"
                        fixed="right"
                        label="操作">
                    <template slot-scope="scope">
                        <el-button   type="text" circle size="mini" @click="editItem(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img style="border: none;" src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button  type="text" circle size="mini" @click="management(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="卡密管理" placement="top">
                                <img style="border: none;" src="statics/img/plugins/setting.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button  type="text" circle size="mini" @click="deleteItem(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img style="border: none;" src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="main:right cross:center" style="margin-top: 20px;">
                <div v-if="page_count > 0">
                    <el-pagination
                            @current-change="pagination"
                            background
                            :current-page="current_page"
                            layout="prev, pager, next, jumper"
                            :page-count="page_count">
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
                page_count: 1,
                current_page: 1,
                form: {
                    keyword: '',
                    page: 1
                }
            }
        },

        methods: {
            async getList() {
                this.listLoading = true;
                const e = await this.$request({
                    params: {
                        r: 'plugin/ecard/mall/index/index',
                        keyword: this.form.keyword,
                        page: this.form.page
                    }
                });
                if (e.data.code === 0) {
                    let {list, pagination} = e.data.data;
                    this.listLoading = false;
                    this.list = list;
                    this.page_count = pagination.page_count;
                    this.current_page = pagination.current_page;
                }
            },

            pagination(e) {
                this.form.page = e;
                this.getList();
            },

            search() {
                this.form.page = 1;
                this.getList();
            },

            edit() {
                this.$navigate({
                    r: 'plugin/ecard/mall/index/edit'
                })
            },

            async deleteItem(row) {
                console.log(row);
                if (!row.can_delete) {
                    this.$confirm('卡密数据正在售卖中，不允许删除此卡密', '提示', {
                        confirmButtonText: '我知道了',
                        showCancelButton: false,
                        type: 'warning'
                    }).then(() => {
                    }).catch(() => {
                    });
                    return;
                }
                let that = this;
                that.$confirm('删除该条卡密, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: `plugin/ecard/mall/index/ecard-destroy`,
                            id: row.id
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.getList();
                            that.$message({
                                type: 'success',
                                message: e.data.msg
                            });
                        } else if (e.data.code === 1) {
                            this.$confirm(e.data.msg, '提示', {
                                confirmButtonText: '我知道了',
                                showCancelButton: false,
                                type: 'warning'
                            }).then(() => {
                            }).catch(() => {
                            });
                        }
                    })
                }).catch(() => {
                    that.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },

            editItem(id) {
                let params = {
                    r: `plugin/ecard/mall/index/edit`,
                    id: id,
                };
                this.$navigate(params);
            },

            management(id) {
                let params = {
                    r: `plugin/ecard/mall/index/list`,
                    id: id,
                };
                this.$navigate(params);
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
