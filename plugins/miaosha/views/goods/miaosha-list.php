<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('goods/app-search');
Yii::$app->loadViewComponent('goods/app-batch');
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>

<div id="app" v-cloak>
    <el-card v-loading="listLoading" class="box-card" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/miaosha/mall/goods'})">秒杀商品</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>秒杀场次</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <app-search :is-show-cat="false"
                        :tabs="[]"
                        :is-show-search="false"
                        :new-search="search"
                        @to-search="toSearch">
            </app-search>
            <app-batch :choose-list="choose_list"
                       @to-search="getList"
                       batch-update-status-url="plugin/miaosha/mall/goods/batch-update-status"
                       batch-destroy-url="plugin/miaosha/mall/goods/batch-miaosha-destroy"
                       batch-confine-url="plugin/miaosha/mall/goods/batch-update-confine-count"
                       batch-freight-url="plugin/miaosha/mall/goods/batch-update-freight"
                       :is-show-svip="false"
                       :batch-extra-params="batchExtraParams"
                       :is-show-integral="false">
            </app-batch>
            <el-table @selection-change="handleSelectionChange" :data="list" border
                      style="width: 100%;margin-bottom: 15px">
                <el-table-column align="center" type="selection" width="60"></el-table-column>
                <el-table-column prop="goods_id" width="80" label="商品ID"></el-table-column>
                <el-table-column prop="open_date" width="120" label="秒杀日期"></el-table-column>
                <el-table-column prop="open_time" label="秒杀时间"></el-table-column>
                <el-table-column label="限购数量">
                    <template slot-scope="scope">
                        <span>{{scope.row.goods.confine_count == '-1' ? '无限制' : scope.row.goods.confine_count}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="限单">
                    <template slot-scope="scope">
                        <span>{{scope.row.goods.confine_order_count == '-1' ? '无限制' : scope.row.goods.confine_order_count}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="status" width="100" label="上架状态">
                    <template slot-scope="scope">
                        <el-switch
                                active-value="1"
                                inactive-value="0"
                                @change="switchStatus(scope.row)"
                                v-model="scope.row.goods.status">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作"
                        width="140">
                    <template slot-scope="scope">
                        <el-button v-if="scope.row.is_show_status" @click="edit(scope.row.goods_id)" type="text" circle
                                   size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
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
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="pageCount > 0"
                            @current-change="pagination"
                            background
                            layout="prev, pager, next, jumper"
                            :page-count="pageCount">
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
                search: {
                    keyword: '',
                    date_start: null,
                    date_end: null,
                    status: '',
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                choose_list: [],

                freight: {
                    dialog: false,
                    list: [],
                    checked: {},
                    loading: false,
                    btnLoading: false,
                },
                confineCount: {
                    dialog: false,
                    value: -1,
                    loading: false,
                    btnLoading: false,
                },
                // new
                tabs: [],
                batchExtraParams: [],
            };
        },
        created() {
            this.getList();
            this.batchExtraParams.push({
                key: 'goods_warehouse_id',
                value: getQuery('id')
            })
        },
        methods: {
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.goods_id);
                })
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
                        r: 'plugin/miaosha/mall/goods/miaosha-list',
                        page: self.page,
                        id: getQuery('id'),
                        search: self.search,
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
                        r: 'plugin/miaosha/mall/goods/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/miaosha/mall/goods/edit',
                    });
                }
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/miaosha/mall/goods/miaosha-destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
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
            // 商品上下架
            switchStatus(row) {
                let self = this;
                request({
                    params: {
                        r: 'plugin/miaosha/mall/goods/switch-status',
                    },
                    method: 'post',
                    data: {
                        status: row.status,
                        id: row.goods_id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            toSearch(searchData) {
                this.page = 1;
                this.search = searchData;
                this.getList();
            },
        }
    });
</script>
