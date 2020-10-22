<?php
Yii::$app->loadViewComponent('goods/app-search');
Yii::$app->loadViewComponent('goods/app-batch');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
    .el-form-item--small.el-form-item {
        margin-bottom: 0;
    }
    .shelves {
        background-color: #f5f7fa;
        height: 50px;
        line-height: 50px;
        padding-left: 20px;
        margin-top: 20px;
    }
    .box-card {
        border: none;
    }
    .activity-visible-title {
        font-size: 18px;
        margin-right: 10px;
    }
</style>

<div id="app" v-cloak>
    <el-card  class="box-card" shadow="never"
              body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <!-- 活动商品表 -->
        <el-dialog :visible.sync="activityVisible" width="50%" class="activity-visible">
            <div slot="title">
                <span class="activity-visible-title">活动商品</span>
                <span>共计{{activityNumber}}款商品</span>
            </div>
            <div style="margin-bottom: 25px;">
                <el-input @change="searchGetGoods" @clear="searchGetGoods" v-model="activityGoods.keyword" placeholder="根据名称搜索" autocomplete="off" clearable>
                    <template slot="append">
                        <el-button @click="searchGetGoods">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <template>
                <el-table
                        :data="activityGoodsList"
                        height="500"
                        border
                        :default-sort="{prop: 'goods_stock', order: 'ascending'}"
                        @sort-change="sortChange"
                        v-loading="viewGoodsLoading"
                        style="width: 100%">
                    <el-table-column
                            prop="id"
                            label="ID"
                            width="99"
                            width="180">
                    </el-table-column>
                    <el-table-column
                            prop="goodsWarehouse.name"
                            label="名称"
                            >
                        <template slot-scope="scope">
                            <div flex="first">
                                <div style="padding-right: 10px;">
                                    <app-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></app-image>
                                </div>
                                <div flex="cross:center">
                                    <el-tooltip
                                            effect="dark"
                                            placement="top"
                                            :content="scope.row.goodsWarehouse.name"
                                    >
                                        <app-ellipsis :line="2">{{scope.row.goodsWarehouse.name}}</app-ellipsis>
                                    </el-tooltip>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            width="180"
                            label="售价">
                        <template slot-scope="scope">
                            ￥{{scope.row.price}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            sortable="custom"
                            width="180"
                            props="goods_stock"
                            label="剩余库存">
                        <template slot-scope="scope">
                            <span :style="{color: scope.row.goods_stock<50 ? '#ff6363' : '#606266'}">{{scope.row.goods_stock}}</span>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
            <div slot="footer" class="dialog-footer" flex="main:justify">
                <el-pagination
                        @current-change="activityGoodsPagination"
                        background
                        :current-page="activityGoods.current_page"
                        layout="prev, pager, next"
                        :page-count="activityGoods.page_count">
                </el-pagination>
                <el-button type="primary" size="small" @click="saveItem()">确 定</el-button>
            </div>
        </el-dialog>
        <div slot="header">
            <span>限时抢购</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="edit">新建活动</el-button>
        </div>
        <div class="table-body">
            <app-search :is-show-cat="false" :tabs="tabs" date-label="活动时间" :new-search="search" place-holder="请输入活动名称搜索" @to-search="toSearch" date_label="活动时间" :new-active-name="activeName"></app-search>
            <div class="shelves">
<!--                <el-button size="mini" @click="operatingActivity(0)">上架</el-button>-->
<!--                <el-button size="mini" @click="operatingActivity(1)">下架</el-button>-->
                <el-button size="mini" @click="operatingActivity(2)">批量删除</el-button>
            </div>
            <el-table
                    ref="multipleTable"
                    :data="data_list"
                    v-loading="listLoading"
                    tooltip-effect="dark"
                    style="width: 100%"
                    border
                    @selection-change="selectionChange">
                <el-table-column
                        type="selection"
                        width="55">
                </el-table-column>
                <el-table-column
                        prop="title"
                        label="活动名称"
                        width="250">
                </el-table-column>
                <el-table-column
                        prop="flashSaleGoods"
                        width="180"
                        label="商品款数">
                    <template slot-scope="scope">
                        <div @click="openGoods(scope.row.id)" style="cursor: pointer;color: #409eff;">
                            {{scope.row.flashSaleGoods.length}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="statics.total_pay_price"
                        width="180"
                        label="订单实付金额（元）">
                </el-table-column>
                <el-table-column
                        prop="statics.order_num"
                        width="180"
                        label="支付订单数">
                </el-table-column>
                <el-table-column
                        prop="statics.user_num"
                        width="180"
                        label="参与人数">
                </el-table-column>
                <el-table-column
                        label="活动时间"
                        width="180"
                        show-overflow-tooltip>
                    <template slot-scope="scope">
                        <template>
                            <p>{{scope.row.start_at}} 至</p>
                            <p>{{scope.row.end_at}}</p>
                        </template>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="time_status"
                        label="活动状态"
                        width="120">
                    <template slot-scope="scope">
                        <el-tag  :type="scope.row.time_status == 1 ? 'info' : scope.row.time_status == 2 ? 'success' : scope.row.time_status == 3 ? 'info' : scope.row.time_status == 0 ? 'warning' : ''">
                            {{scope.row.time_status == 1 ? '未开始' : scope.row.time_status == 2 ? '进行中' : scope.row.time_status == 3 ? '已结束' : scope.row.time_status == 0 ? '下架中' : ''}}
                        </el-tag>
                    </template>
                </el-table-column>

                <el-table-column
                        fixed="right"
                        label="操作">
                    <template slot-scope="scope">
                        <template v-if="scope.row.time_status != 3">
                            <el-button type="text" circle size="mini" @click="edit(scope.row, 1)">
                                <el-tooltip class="item" effect="dark" content="编辑活动" placement="top">
                                    <img src="statics/img/plugins/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" circle size="mini" @click="edit(scope.row, 2)" >
                                <el-tooltip class="item" effect="dark" content="编辑商品" placement="top">
                                    <img src="statics/img/plugins/edit-goods.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                       <template v-else>
                           <el-button type="text" circle size="mini" @click="lookOver(scope.row)">
                               <el-tooltip class="item" effect="dark" content="查看活动" placement="top">
                                   <img src="statics/img/plugins/look.png" alt="">
                               </el-tooltip>
                           </el-button>
                       </template>
                        <el-button v-if="scope.row.time_status != 3" @click="takeOff(scope.row)" type="text" circle size="mini">
                            <el-tooltip  class="item" effect="dark" :content="scope.row.time_status == 0 ? '上架' : '下架'" placement="top">
                                <img :src="scope.row.time_status == 0 ? 'statics/img/plugins/shelves.png' : 'statics/img/plugins/take-off.png'" alt="">
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
                <div style="visibility: hidden;">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="page_count > 0"
                            @current-change="pagination"
                            background
                            layout="prev, pager, next"
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
                listLoading: false,
                search: {
                    keyword: '',
                    status: '-1',
                    date_start: null,
                    date_end: null,
                    date: [],
                    page: 1
                },
                tabs: [
                    {
                        name: '全部',
                        value: '-1'
                    },
                    {
                        name: '未开始',
                        value: '0'
                    },
                    {
                        name: '进行中',
                        value: '1'
                    },
                    {
                        name: '已结束',
                        value: '2'
                    },
                    {
                        name: '下架中',
                        value: '3'
                    },
                ],
                activeName: '-1',
                data_list: [],
                current_page: 1,
                page_count: 1,
                selectionList: [],

                activityVisible: false,
                form: {},
                activityGoodsList: [],
                activityNumber: 0,
                viewGoodsIndex: -1,
                viewGoodsLoading: false,
                deleteGoodsList: [],
                viewActivityItem: {},
                goodsVisible: false,
                goodsList: [],
                goodsKeyword: '',
                goods: {
                    current_page: 1,
                    page_count: 1,
                    page: 1
                },
                activityGoods: {
                    current_page: 1,
                    page_count: 1,
                    page: 1,
                    id: 0,
                    keyword: '',
                    sort_type: 1
                }
            };
        },
        methods: {
            // 获取活动
            async getList() {
                this.listLoading = true;
                let { keyword, date_start, date_end, status, page} = this.search;
                const e = await request({
                    params: {
                        r: '/plugin/flash_sale/mall/activity',
                        keyword,
                        start_at: date_start,
                        end_at: date_end,
                        status,
                        keyword_label: 'title',
                        page
                    },
                    method: 'get'
                });
                this.listLoading = false;
                if (e.data.code === 0) {
                    let { list, pagination } = e.data.data;
                    let { current_page, page_count } = pagination;
                    this.data_list = list;
                    this.current_page = current_page;
                    this.page_count = page_count;
                }
            },

            edit(data, s) {
                if (data) {
                    navigateTo({
                        r: 'plugin/flash_sale/mall/activity/edit',
                        id: data.id,
                        edit: s,
                        status: data.time_status
                    });
                } else {
                    navigateTo({
                        r: 'plugin/flash_sale/mall/activity/edit'
                    });
                }
            },

            toSearch(data) {
                this.search = data;
                this.search.page = 1;
                this.getList();
            },

            pagination(e) {
                this.search.page = e;
                this.getList();
            },
            destroy(data) {
                this.$confirm('删除该条数据，是否继续？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    let ids = [data.id];
                    request({
                        params: {
                            r: '/plugin/flash_sale/mall/activity/edit-status',
                        },
                        data: {
                            ids: ids,
                            type: 'del'
                        },
                        method: 'post'
                    }).then(e => {
                        this.listLoading =  false;
                        if (e.data.code === 0) {
                            this.getList();
                        }
                    })
                });
            },
            takeOff(e) {
                console.log(e);
                let data = '';
                if (e.time_status === '0') {
                    data = 'up';
                } else {
                    data = 'down';
                }
                let str = `确认${data === 'up' ? '上架' : '下架'}此活动?`;
                this.$confirm(str, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: '/plugin/flash_sale/mall/activity/edit-status',
                        },
                        data: {
                            ids: [e.id],
                            type: data
                        },
                        method: 'post'
                    }).then(e => {
                        this.listLoading =  false;
                        if (e.data.code === 0) {
                            this.getList();
                        } else {
                            this.$message({
                                type: 'warning',
                                message: e.data.msg
                            });
                        }
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消操作'
                    });
                });
            },
            operatingActivity() {
                if (this.selectionList.length !== 0) {
                    let type = 'del';
                    let str = `选中${this.selectionList.length}个活动，是否批量删除？`;
                    this.$confirm(str, '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        this.listLoading = true;
                        let ids = [];
                        for (let i = 0; i < this.selectionList.length; i++) {
                            ids.push(this.selectionList[i].id);
                        }
                        request({
                            params: {
                                r: '/plugin/flash_sale/mall/activity/edit-status',
                            },
                            data: {
                                ids: ids,
                                type: type
                            },
                            method: 'post'
                        }).then(e => {
                            this.listLoading =  false;
                            if (e.data.code === 0) {
                               this.getList();
                            } else {

                            }
                        })
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消'
                        });
                    });
                } else {
                    this.$message.warning('请先选择活动');
                }
            },

            selectionChange(e) {
                this.selectionList = e;
            },

            openGoods(id) {
                this.activityVisible = true;
                this.viewGoodsLoading = true;
                this.activityGoods.id = id;
                this.activityGoodsList = [];
                request({
                    params: {
                        r: '/plugin/flash_sale/mall/activity/goods',
                        id: id,
                        page: this.activityGoods.page,
                        limit: 10,
                        keyword: this.activityGoods.keyword,
                        sort_type: this.activityGoods.sort_type
                    },
                    method: 'get'
                }).then(e => {
                    if (e.data.code === 0) {
                        this.activityGoodsList = e.data.data.list;
                        this.activityGoods.current_page = e.data.data.pagination.current_page;
                        this.activityGoods.page_count = e.data.data.pagination.page_count;
                        this.viewGoodsLoading = false;
                        this.activityNumber = e.data.data.goods.length;
                    }
                })
            },

            searchGetGoods() {
                this.openGoods(this.activityGoods.id);
            },

            sortChange(value) {
                console.log(value);
                if (value.order === 'ascending') {
                    this.activityGoods.sort_type = 1;
                } else {
                    this.activityGoods.sort_type = 2;
                }
                // if (this.activityGoods.sort_type == 1) {
                //     this.activityGoods.sort_type = 2;
                // } else {
                //     this.activityGoods.sort_type = 1;
                // }
                this.openGoods(this.activityGoods.id);
            },

            sortMethod(e) {
                console.log(e);
            },

            activityGoodsPagination(e) {
                this.activityGoods.page = e;
                this.openGoods(this.activityGoods.id);
            },

            saveItem() {
                this.activityVisible = false;
            },

            lookOver(data) {
                navigateTo({
                    r: 'plugin/flash_sale/mall/activity/edit',
                    id: data.id,
                    status: data.time_status
                });
            }
        },
        mounted() {
            this.getList();
        }
    });
</script>
