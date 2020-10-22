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

    .goods-image {
        width: 50px;
        height: 50px;
        margin-right: 10px;
    }

    .el-button--mini.is-circle {
        padding: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div flex="dir:left box:last">
                <div>直播商品</div>
                <div>
                    <el-button size="small" @click="edit" type="primary">添加直播商品</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane v-for="(tab, index) in tabs" :key="index" :label="tab.label" :name="tab.value"></el-tab-pane>
            </el-tabs>
            <el-table
                v-loading="listLoading"
                :data="list"
                border
                style="width: 100%">
                <el-table-column
                        width="80"
                        prop="goodsId"
                        label="商品ID"
                        width="120">
                </el-table-column>
                <el-table-column
                    label="商品名称"
                    width="220">
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <img :src="scope.row.coverImgUrl" class="goods-image">
                            <div>
                                <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    width="180"
                    label="价格形式/价格">
                    <template slot-scope="scope">
                        <el-tag type="primary"><span v-html="scope.row.price_text"></span></el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="url"
                        label="小程序路径">
                </el-table-column>
                <el-table-column
                        width="100"
                        label="添加方式">
                        <template slot-scope="scope">
                            <el-tag v-if="scope.row.thirdPartyTag == 2">后台添加</el-tag>
                            <el-tag v-else type="success">微信添加</el-tag>
                        </template>
                </el-table-column>
                <el-table-column
                        width="100"
                    label="审核状态">
                    <template slot-scope="scope">
                        <el-tag v-if="status == 0" type="info">未审核</el-tag>
                        <el-tag v-if="status == 1" type="primary">审核中</el-tag>
                        <el-tag v-if="status == 2" type="success">审核通过</el-tag>
                        <el-tag v-if="status == 3" type="danger">审核驳回</el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                    label="操作">
                    <template slot-scope="scope">
                        <template>
                            <el-button v-if="status != 1" @click="edit(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                            </el-button>
                            <el-button v-if="status == 0 || status == 3" @click="submitAudit(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="审核" placement="top">
                                    <img src="statics/img/mall/live/btn_examine_n.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="status == 1 && scope.row.audit_id" @click="cancelAudit(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="撤回" placement="top">
                                    <img src="statics/img/mall/live/btn_withdraw_n.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="status != 1" @click="deleteGoods(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
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
                listLoading: false,
                page: 1,
                pageCount: 0,
                status: 0,

                tabs: [
                    {
                        label: '未审核',
                        value: '0'
                    },
                    {
                        label: '审核中',
                        value: '1'
                    },
                    {
                        label: '审核通过',
                        value: '2'
                    },
                    {
                        label: '审核驳回',
                        value: '3'
                    },
                ],
                activeName: '0',
            };
        },
        methods: {
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
                        r: 'mall/live/goods',
                        page: self.page,
                        status: self.status,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pageCount;
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            deleteGoods(row) {
                let self = this;
                this.$confirm('此操作将删除该商品,直播间上架的该商品也将被同步删除不可恢复, 是否继续?', '提示', {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                      request({
                        params: {
                            r: 'mall/live/delete-goods',
                        },
                        data: {
                            goods_id: row.goodsId,
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg)
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            cancelAudit(row) {
                let self = this;
                this.$confirm('撤销审核, 是否继续?', '提示', {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                      request({
                        params: {
                            r: 'mall/live/cancel-audit',
                        },
                        data: {
                            goods_id: row.goodsId,
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg)
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            submitAudit(row) {
                let self = this;
                this.$confirm('提交审核, 是否继续?', '提示', {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                      request({
                        params: {
                            r: 'mall/live/submit-audit',
                        },
                        data: {
                            goods_id: row.goodsId,
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg)
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            handleClick(tab, event) {
                this.status = tab.index;
                this.getList();
            },
            edit(row) {
                if (row) {
                    this.$navigate({
                        r: 'mall/live/goods-edit',
                        goods_id: row.goodsId
                    });
                } else {
                    this.$navigate({
                        r: 'mall/live/goods-edit',
                    });
                }
            }
        },
        mounted: function () {
            if (getQuery('status')) {
                this.activeName = getQuery('status');
                this.status = getQuery('status');
            }
            this.getList();
        }
    });
</script>
