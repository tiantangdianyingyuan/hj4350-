<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/31
 * Time: 15:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .set-el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0 !important;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>表单列表</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/order-form/setting'})">添加表单
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入表单名称搜索" v-model="keyword"
                          clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="name" label="表单名称"></el-table-column>
                <el-table-column width='200' prop="sub_price" label="是否默认">
                    <template slot-scope="scope">
                        <el-radio v-model="scope.row.is_default"
                                  :label="1" @change="changeDefault(scope.row,scope.$index)">默认</el-radio>
                    </template>
                </el-table-column>
                <el-table-column width='200' prop="sub_price" label="状态">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.status" :inactive-value="0" :active-value="1"
                                   @change="changeStatus(scope.row,scope.$index)"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text"
                                   @click="$navigate({r:'mall/order-form/setting',id:scope.row.id})">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button :loading="scope.row.loading" circle size="mini" type="text"
                                   @click="deleteItem(scope.row,scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination v-if="pagination" :page-size="pagination.pageSize"
                           style="display: inline-block;float: right;" background @current-change="pageChange"
                           layout="prev, pager, next, jumper" :total="pagination.total_count">
            </el-pagination>
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
                keyword: '',
                pagination: null,
                page: 1,
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/order-form/list',
                        keyword: this.keyword,
                        page: this.page,
                    },
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.list = response.data.data.list;
                        this.pagination = response.data.data.pagination;
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            search() {
                this.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.page = page;
                this.loadData();
            },
            changeDefault(item, index) {
                this.update(item).then(() => {
                    location.reload();
                });
            },
            changeStatus(item, index) {
                this.update(item);
            },
            deleteItem(item, index) {
                this.$confirm('确认删除？', '提示', {
                    type: 'warning',
                    center: true
                }).then(() => {
                    item.is_delete = 1;
                    this.update(item).then(() => {
                        this.list.splice(index, 1);
                    }).catch(() => {
                    });
                }).catch(() => {
                });
            },
            update(item) {
                return new Promise(((resolve, reject) => {
                    this.$request({
                        params: {
                            r: 'mall/order-form/update',
                        },
                        method: 'post',
                        data: item,
                    }).then(response => {
                        if (response.data.code === 0) {
                            this.$message.success(response.data.msg);
                            resolve();
                        } else {
                            this.$message.error(response.data.msg);
                            reject();
                        }
                    }).catch(e => {
                    });
                }));
            }
        }
    });
</script>
