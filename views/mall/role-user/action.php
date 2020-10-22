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

    .input-item {
        width: 250px;
        margin-right: 40px;
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

    .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    #app .copy .el-input-group__append {
        background-color: #409EFF;
        border-color: #409EFF;
        padding: 0 10px;
    }

    #app .table-body .copybtn {
        color: #fff;
        padding: 0 30px;
    }

    .el-alert {
        padding: 0;
        padding-left: 5px;
        padding-bottom: 5px;
    }

    .el-alert--info .el-alert__description {
        color: #606266;
    }

    .el-alert .el-button {
        margin-left: 20px;
    }

    .el-alert__content {
        display: flex;
        align-items: center;
    }

    .table-body .el-alert__title {
        margin-top: 5px;
        font-weight: 400;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>操作记录</span>
            </div>
        </div>
        <div class="table-body">
            <el-form inline @submit.native.prevent="search">
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="search" size="small" placeholder="请输入用户昵称" v-model="keyword"
                                  clearable @clear="search">
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        fixed
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="user.username"
                        label="账号">
                </el-table-column>
                <el-table-column
                        prop="user.nickname"
                        label="用户名">
                </el-table-column>
                <el-table-column
                        prop="model"
                        width="220"
                        label="数据表">
                </el-table-column>
                <el-table-column
                        prop="model_id"
                        label="表记录ID">
                </el-table-column>
                <el-table-column
                        prop="remark"
                        label="备注">
                </el-table-column>
                <el-table-column
                        prop="created_at"
                        label="添加日期"
                        width="220">
                </el-table-column>
                <el-table-column
                        label="操作"
                        fixed="right"
                        width="120">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row.id)" size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="详情" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    @current-change="pagination"
                    background
                    hide-on-single-page
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
                listLoading: false,
                page: 1,
                pageCount: 0,
                keyword: '',
                btnLoading: false,
                loginRoute: '',
            };
        },
        methods: {
            search: function () {
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
                        r: 'mall/role-user/action',
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
                navigateTo({
                    r: 'mall/role-user/action-detail',
                    id: id,
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
