
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

    .table-info .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin-left: 20px;
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

    .item-box .label {
        margin-right: 10px;
    }

    .item-box .el-select {
        width: 160px;
    }

    .time-box {
        margin: 0 15px;
    }
    .el-message-box__status+.el-message-box__message::after {
        content: '删除后，会禁用当前可用的兑换码，请慎重操作!';
        color: #ff4544;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>兑换码管理</span>
                <el-button @click="toCreated" style="float: right; margin: -5px 0" type="primary" size="small">
                    新增兑换码库
                </el-button>
            </div>
        </div>
        <div class="table-body">
            <div flex="wrap:wrap cross:center" style="margin-bottom: 15px;">
                <div>创建时间</div>
                <el-date-picker
                        class="time-box"
                        size="small"
                        @change="search"
                        v-model="created_at"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>
                <div class="item-box" flex="dir:left cross:center">
                    <div class="label">奖励品类型</div>
                    <el-select size="small" v-model="rewards_s" @change='search' class="select">
                        <el-option key="0" label="全部" value="0"></el-option>
                        <el-option :key="item.type" :label="item.name" :value="item.type" v-for="item in type" :key="item.type"></el-option>
                    </el-select>
                </div>
                <div class="input-item">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入兑换码库名称搜索" v-model="keyword" clearable @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
            </div>
            <el-tabs v-model="activeName" @tab-click="search" >
                <el-tab-pane label="全部" name="0"></el-tab-pane>
                <el-tab-pane label="回收站" name="1"></el-tab-pane>
            </el-tabs>
            <el-table class="table-info" :header-cell-style="{'height':'80px'}"  height="580" :data="list" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="name" label="兑换码库名称" width="320"></el-table-column>
                <el-table-column prop="rewards_text" label="奖励品类型" width="180">
                </el-table-column>
                <el-table-column prop="can_use_num" label="可用数量" width="180">
                </el-table-column>
                <el-table-column prop="not_can_use_num" label="不可用数量" width="180">
                    <template slot-scope="scope">
                        <span style="color: #ff4544">{{scope.row.not_can_use_num}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="record_num" label="已兑换数量" width="180">
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" wdith="220"></el-table-column>
                <el-table-column label="操作" fixed="right" >
                    <template slot-scope="scope">
                        <el-button v-if="activeName == '0'" circle size="mini" type="text" @click="edit(scope.row)">
                            <el-tooltip effect="dark" style="margin-right: 20px" content="兑换码管理" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-else circle size="mini" type="text" @click="toRecycle(scope.row)">
                            <el-tooltip effect="dark" style="margin-right: 20px" content="恢复兑换码库" placement="top">
                                <img src="statics/img/mall/order/renew.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" style="margin-right: 20px" type="text" @click="look(scope.row)">
                            <el-tooltip effect="dark" content="查看兑换记录" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="activeName == '0'" circle size="mini" type="text" @click="toRecycle(scope.row)">
                            <el-tooltip effect="dark" content="放入回收站" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-else circle size="mini" type="text" @click="destroy(scope.row,scope.$index)">
                            <el-tooltip effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="main:right cross:center" style="margin-top: 20px;">
                <div v-if="pagination.page_count > 0">
                    <el-pagination
                            @current-change="changePage"
                            background
                            :current-page="pagination.current_page"
                            layout="prev, pager, next, jumper"
                            :page-count="pagination.page_count">
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
                rewards_s: '0',
                list: [],
                type: [],
                created_at: [],
                pagination: {
                    page_count: 0
                },
                activeName: '0',
                keyword: '',
                listLoading: false,
            };
        },
        methods: {
            destroy(row) {
                this.$confirm('删除该条兑换码库, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/exchange/mall/library/destory'
                        },
                        data: {
                            id: row.id
                        },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.listLoading = true;
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    })
                })
            },
            toCreated() {
                navigateTo({
                    r: 'plugin/exchange/mall/library/edit'
                })
            },
            edit(row) {
                navigateTo({
                    r: 'plugin/exchange/mall/library/edit',
                    id: row.id
                })
            },
            look(row) {
                navigateTo({
                    r: 'plugin/exchange/mall/library/edit',
                    id: row.id,
                    log: 1
                })
            },
            // 回收站
            toRecycle(e) {
                let that = this;
                let text = "是否放入回收站(可在回收站中恢复)?";
                let para = {
                    id: e.id,
                    is_recycle: 1
                };
                if (e.is_recycle == 1) {
                    para.is_recycle = 0;
                    text = "是否移出回收站?"
                }
                this.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/exchange/mall/library/recycle',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            search() {
                this.list = [];
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/exchange/mall/library/list',
                        page: 1,
                        is_recycle: this.activeName,
                        name: this.keyword,
                        created_at: this.created_at,
                        rewards_s: this.rewards_s == 0? [] :[this.rewards_s]
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.type = e.data.data.type;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },

            changePage(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;

                request({
                    params: {
                        r: 'plugin/exchange/mall/library/list',
                        page: this.page,
                        is_recycle: this.activeName,
                        created_at: this.created_at,
                        rewards_s: this.rewards_s == 0? [] :[this.rewards_s]
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.type = e.data.data.type;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>