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

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 15px 0 0;">
        <div slot="header">
            <div>
                <span>红包活动列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加活动</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear='search'>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="pic_url"
                        width="120"
                        label="活动封面图">
                    <template slot-scope="scope">
                        <app-image :src="scope.row.pic_url"></app-image>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="name"
                        label="活动名称">
                    <template slot-scope="scope">
                        <span>{{scope.row.name}}</span></span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="count_price"
                        label="红包总额度"
                        width="180">
                </el-table-column>
                <el-table-column
                        prop="sponsor_count"
                        label="活动总次数"
                        width="150">
                    <template slot-scope="scope">
                        <span v-if="scope.row.sponsor_count == -1">无限制</span>
                        <span v-else>{{scope.row.sponsor_count}}</span>
                    </template>    
                </el-table-column>
                <el-table-column
                        label="活动状态"
                        width="120">
                    <template slot-scope="scope">
                        <el-switch
                                @change="switchStatus(scope.row.id, scope.$index)"
                                v-model="scope.row.status"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                        label="活动时间"
                        width="330">
                    <template slot-scope="scope">
                        <span>{{scope.row.start_time}}</span>至<span>{{scope.row.end_time}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作"
                        fixed="right"
                        width="220">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row.id, scope.$index)" type="text" circle size="mini">
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
                list: [],
                keyword: null,
                listLoading: false,
                page: 1,
                pageCount: 0,
                keyword: '',
            };
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },
            
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/fxhb/mall/activity/index',
                        page: self.page,
                        keyword: self.keyword
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
                        r: 'plugin/fxhb/mall/activity/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/fxhb/mall/activity/edit',
                    });
                }
            },
            destroy(id, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/fxhb/mall/activity/destroy',
                        },
                        method: 'post',
                        data: {
                            id: id,
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
            switchStatus(id, index) {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/fxhb/mall/activity/status',
                    },
                    method: 'post',
                    data: {
                        id: id,
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.list[index].status = self.list[index].status ? 1: 0;
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
