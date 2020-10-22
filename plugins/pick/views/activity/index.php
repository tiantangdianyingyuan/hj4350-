<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
Yii::$app->loadViewComponent('goods/app-search');

?>

<style>
    .activity-body {
        padding: 20px;
        background-color: #fff;
    }
    .search .search-time {
        margin-bottom: 10px;
    }
    .search .search-box {
        margin-right: 10px;
    }
    .batch {
        height: 50px;
        background-color: #f9f9f9;
        padding: 0 22px;
    }
    .kind-color {
        color: #409eff;
        cursor: pointer;
    }
    .activity-visible .el-dialog__body {
        padding: 10px 20px;
    }
    .activity-visible-title {
        font-size: 17px;
        margin-right: 30px;
    }
    .activity-visible .button {
        padding: 12px 0 12px 17px;
    }
    .el-date-editor .el-range-separator {
        padding: 0;
    }
</style>

<div id="app" v-cloak>
   <el-card  shadow="never"  class="activity-list" style="border:0"  body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <!-- 活动商品表 -->
       <el-dialog :visible.sync="activityVisible" width="60%" class="activity-visible">
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
                           width="180">
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
                           label="售价">
                       <template slot-scope="scope">
                           ￥{{scope.row.price}}
                       </template>
                   </el-table-column>
                   <el-table-column
                           sortable="custom"
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
                   layout="prev, pager, next, jumper"
                   :page-count="activityGoods.page_count">
               </el-pagination>
               <el-button type="primary" size="small" @click="saveItem()">确 定</el-button>
           </div>
       </el-dialog>
       <div slot="header">
           <span>N元任选活动</span>
           <div style="float: right; margin: -5px 0">
               <el-button type="primary" size="small" @click="edit">新建活动</el-button>
           </div>
       </div>
       <div class="activity-body">
           <div class="search">
               <el-tabs v-model="activeName" @tab-click="tabClick">
                   <el-tab-pane v-for="(item, index) in tabs" :key="index" :label="item.name" :name="item.value"></el-tab-pane>
               </el-tabs>
               <div class="search-time" flex="dir:left cross-center">
                   <div class="search-box" flex="dir:left">
                       <div flex="cross:center" style="height: 32px;">活动时间：</div>
                       <el-date-picker
                               size="small"
                               @change="toSearch"
                               v-model="search.date"
                               type="datetimerange"
                               value-format="yyyy-MM-dd"
                               range-separator="至"
                               start-placeholder="开始日期"
                               end-placeholder="结束日期">
                       </el-date-picker>
                   </div>
                   <div class="search-input search-box" flex="cross-center">
                       <div>
                           <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入活动名称搜索"
                                     v-model="search.keyword" clearable
                                     @clear="toSearch">
                               <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                           </el-input>
                       </div>
                   </div>
               </div>
           </div>
           <div class="batch" flex="dir:left cross:center">
               <el-button @click="batchDeletion" size="mini">批量删除</el-button>
           </div>
           <el-table
               border
               :data="data_list"
               tooltip-effect="dark"
               v-loading="listLoading"
               @selection-change="selectionItem"
               style="width: 100%;margin-bottom: 15px"
           >
               <el-table-column
                   type="selection"
                   width="55">
               </el-table-column>
               <el-table-column
                   label="活动名称"
                   prop="title">
               </el-table-column>
               <el-table-column
                   label="组合方案（XX元/件）"
                   width="300">
                   <template slot-scope="scope">
                       {{scope.row.rule_price}}元/{{scope.row.rule_num}}件
                   </template>
               </el-table-column>
               <el-table-column
                   label="商品件数"
                   prop="kind"
                   width="220">
                   <template slot-scope="scope">
                       <span class="kind-color" @click="viewGoods(scope.row, scope.$index)">{{scope.row.kind}}</span>
                   </template>
               </el-table-column>
               <el-table-column
                   label="活动时间"
                   width="240">
                   <template slot-scope="scope">
                       <p>{{scope.row.start_at}}至</p>
                       <p>{{scope.row.end_at}}</p>
                   </template>
               </el-table-column>
               <el-table-column
                       label="活动状态"
                       width="150">
                   <template slot-scope="scope">
                       <el-tag :type="getStatus(scope.row.time_status)">{{getStatusText(scope.row.time_status)}}</el-tag>
                   </template>
               </el-table-column>
               <el-table-column
                       label="操作"
                       fixed="right"
                       width="250">
                   <template slot-scope="scope">
                       <el-button v-if="scope.row.time_status != 3" @click="edit(scope.row, '0')" type="text" circle size="mini">
                           <el-tooltip class="item" effect="dark" content="编辑活动" placement="top">
                               <img src="statics/img/mall/edit.png" alt="">
                           </el-tooltip>
                       </el-button>
                       <el-button v-if="scope.row.time_status != 3" @click="edit(scope.row, '1')" type="text" circle size="mini">
                           <el-tooltip class="item" effect="dark" content="编辑商品" placement="top">
                               <img src="statics/img/plugins/edit-goods.png" alt="">
                           </el-tooltip>
                       </el-button>
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
           <div flex="dir:right" style="margin-top: 20px;">
               <el-pagination
                   hide-on-single-page
                   @current-change="pagination"
                   background
                   :current-page="current_page"
                   layout="prev, pager, next, jumper"
                   :page-count="page_count">
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
                listLoading: false,
                search: {
                    keyword: '',
                    status: '-1',
                    date_start: null,
                    date_end: null,
                    date: [],
                    page: 1
                },
                datetime: [],
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
                    keyword: ''
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
                        r: '/plugin/pick/mall/activity',
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

            // 新建活动
            edit(row, tab) {
                let r = 'plugin/pick/mall/activity/edit';
                if (row) {
                    this.$navigate({
                        r: r,
                        id: row.id,
                        tab: tab
                    });
                } else {
                    this.$navigate({
                        r: r
                    });
                }
            },

            // 条件搜索
            async toSearch() {
                if (this.search.date !== null) {
                    this.search.date_start = this.search.date[0];
                    this.search.date_end = this.search.date[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.search.page = 1;
                this.getList();
            },

            // 切换状态
            async tabClick(e) {
                this.search.page = 1;
                this.search.status = e.name;
                this.getList();
            },

            changeTime() {},

            // 批量删除
            batchDeletion() {
                if (this.selectionList.length === 0) {
                    this.$message({
                        type: 'warning',
                        message: '请选中'
                    });
                    return;
                }
                this.$confirm('选中' + this.selectionList.length +'个活动，是否批量删除?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.setStatus(this.selectionList, 'del').then(() => {
                        this.getList();
                        this.selectionList = [];
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消批量删除'
                    });
                });
            },

            // 选择数据
            selectionItem(data) {
                let id = [];
                for (let i = 0; i < data.length; i++) {
                    id.push(data[i].id);
                }
                this.selectionList = id;
            },

            pagination(currentPage) {
                this.search.page = currentPage;
                this.getList();
            },

            getStatus: function(status) {
                let data = '';
                switch (status) {
                    case '1':
                        data = 'info';
                        break;
                    case '2':
                        data = 'success';
                        break;
                    case '3':
                        data = 'info';
                        break;
                    case '0':
                        data = 'warning';
                        break;
                }
                return data;
            },

            getStatusText(status) {
                let data = '';
                switch (status) {
                    case '1':
                        data = '未开始';
                        break;
                    case '2':
                        data = '进行中';
                        break;
                    case '3':
                        data = '已结束';
                        break;
                    case '0':
                        data = '下架中';
                        break;
                }
                return data;
            },

            // 删除
            destroy(e, index) {
                this.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.setStatus([e.id], 'del').then(() => {
                        this.$delete(this.data_list, index);
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },

            // 上下架
            takeOff(e) {
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
                    this.setStatus([e.id], data).then(() => {
                        this.getList();
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消操作'
                    });
                });
            },

            async setStatus(ids, type) {
                const e = await request({
                    params: {
                        r: '/plugin/pick/mall/activity/edit-status',
                    },
                    data: {
                        type: type,
                        ids: ids
                    },
                    method: 'post',
                });
                if (e.data.code === 0) {
                    this.$message({
                        type: 'success',
                        message: type === 'up' ? '上架成功！' : type === 'down' ? '下架成功！' : '删除成功!'
                    });
                } else {
                    this.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }
            },

            // 查看商品
            async viewGoods(item, index) {
                this.activityVisible = true;
                this.viewGoodsIndex = index;
                this.viewActivityItem = item;
                this.activityGoods.id = item.id;
                this.activityGoods.page = 1;
                this.activityGoods.keyword = '';
                this.viewGetGoods();
            },

            async viewGetGoods() {
                this.activityNumber = 0;
                this.viewGoodsLoading = true;
                const e = await request({
                    params: {
                        r: '/plugin/pick/mall/activity/goods',
                        id: this.activityGoods.id,
                        page: this.activityGoods.page,
                        keyword: this.activityGoods.keyword,
                        sort_type: 1
                    }
                });
                this.viewGoodsLoading = false;
                let { list, pagination } = e.data.data;
                let { current_page, page_count, total_count } = pagination;
                this.activityGoodsList = list;
                this.activityNumber = total_count;
                this.activityGoods.current_page = current_page;
                this.activityGoods.page_count = page_count;
            },

            searchGetGoods() {
                this.activityGoods.page = 1;
                this.viewGetGoods();
            },

            activityGoodsPagination(e) {
                this.activityGoods.page = e;
                this.viewGetGoods();
            },

            async saveItem() {
                let { title, start_at, end_at, pickGoods, rule_price, rule_num,id } = this.viewActivityItem;
                this.activityVisible = false;
                const e = await request({
                    params: {
                        r: `/plugin/pick/mall/activity/edit`
                    },
                    method: 'post',
                    data: {
                        title,
                        start_at,
                        end_at,
                        pick: JSON.stringify(pickGoods),
                        rule_price,
                        rule_num,
                        id
                    }
                });
                if (e.data.code === 0) {

                }
            },

            async sortChange(d) {
                let sort_type = 1;
                if (d.order === 'ascending') {
                    sort_type = 1;
                } else {
                    sort_type = 2;
                }
                this.activityNumber = 0;
                this.viewGoodsLoading = true;
                const e = await request({
                    params: {
                        r: '/plugin/pick/mall/activity/goods',
                        id: this.activityGoods.id,
                        page: this.activityGoods.page,
                        keyword: this.activityGoods.keyword,
                        sort_type: sort_type
                    }
                });
                this.viewGoodsLoading = false;
                let { list, pagination } = e.data.data;
                let { current_page, page_count, total_count } = pagination;
                this.activityGoodsList = list;
                this.activityNumber = total_count;
                this.activityGoods.current_page = current_page;
                this.activityGoods.page_count = page_count;
            }
        },

        mounted() {
            this.getList();
        }
    });
</script>
