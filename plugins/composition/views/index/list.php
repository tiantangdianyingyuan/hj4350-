<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-12
 * Time: 14:29
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('goods/app-batch');
?>
<style>
    .input-item {
        width: 250px;
        margin: 0;
        margin-left: 15px;
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

    .edit-content {
        width: 100%;
        background: #ffffff;
        padding: 10px;
        display: block;
    }

    .edit-content .item {
        text-align: center;
        background: #ECF5FE;
        width: 400px;
        padding: 20px 40px;
        display: inline-block;
    }


    .item:first-child {
        margin-right: 10px;
    }

    .item div:nth-child(2) {
        color: #888888;
        margin: 10px 6px 20px;
    }

    .edit-content .table-body .el-table .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .zero-stock {
        color: #ff4544;
    }
    .goods-dialog .el-dialog__body {
        padding-top: 0;
    }
    .list-title {
        color: #3399ff;
        background-color: #ECF5FE;
        height: 32px;
        line-height: 32px;
        padding: 0 10px;
        display: inline-block;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        margin-top: 8px;
        font-size: 14px;
    }
    .off-price {
        color: #FFA525;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>套餐组合</span>
            </div>
        </div>
        <div class="edit-content">
            <div style="display: block;margin-bottom: 20px">
                <div class="item">
                    <div>固定套餐</div>
                    <div>套餐内商品打包销售，消费者需购买整个套餐</div>
                    <div>
                        <el-button type="primary"
                                   @click="$navigate({r: 'plugin/composition/mall/index/fixed'})"
                                   size="mini">新建套餐
                        </el-button>
                    </div>
                </div>
                <div class="item">
                    <div>搭配套餐</div>
                    <div>套餐内主商品必选，搭配商品任意选</div>
                    <div>
                        <el-button type="primary"
                                   @click="$navigate({r: 'plugin/composition/mall/index/goods'})"
                                   size="mini">新建套餐
                        </el-button>
                    </div>
                </div>
            </div>

            <el-tabs v-model="activeName" @tab-click="toSearch">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="销售中" name="1"></el-tab-pane>
                <el-tab-pane label="下架中" name="0"></el-tab-pane>
            </el-tabs>
            <div class="table-body">
                <div flex="dir:left cross:center" style='margin: 5px 0 20px'>
                    <div class="item-box" flex="dir:left cross:center">
                        <div class="label">套餐类型：</div>
                        <el-select style="width: 120px;margin-left: 10px" size="small" v-model="search.type" @change='toSearch'>
                            <el-option key="all" label="全部套餐" value=""></el-option>
                            <el-option key="1" label="固定套餐" value="1"></el-option>
                            <el-option key="2" label="搭配套餐" value="2"></el-option>
                        </el-select>
                    </div>
                    <div style='margin-left: 30px'>添加时间：</div>
                    <el-date-picker
                            class="item-box"
                            size="small"
                            @change="changeTime"
                            v-model="search.time"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                    <div class="input-item">
                        <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入套餐名称搜索" v-model="search.keyword" clearable @clear='toSearch'>
                            <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                        </el-input>
                    </div>
                </div>
                <app-batch :choose-list="choose_list"
                           @to-search="loadData"
                           :is-show-batch-button="isShowBatchButton"
                           @get-all-checked="getAllChecked"
                           :batch-update-status-url="batch_update_status_url">
                </app-batch>
                <el-table @sort-change="changeSort" :data="list" border v-loading="loading" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" align="center" width="60"></el-table-column>
                    <el-table-column align="center" width="60" prop="id" label="ID"></el-table-column>
                    <el-table-column prop="sort" :width="sort_goods_id != id ? 150 : 100" label="排序"
                                     sortable="custom">
                        <template slot-scope="scope">
                            <div v-if="sort_goods_id != scope.row.id" flex="dir:left cross:center">
                                <span>{{scope.row.sort}}</span>
                                <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                    <img src="statics/img/mall/order/edit.png" alt="">
                                </el-button>
                            </div>
                            <div style="display: flex;align-items: center" v-else>
                                <el-input style="min-width: 70px" type="number" size="mini" class="change"
                                          v-model="sort"
                                          autocomplete="off"></el-input>
                                <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                           icon="el-icon-error"
                                           circle @click="quit()"></el-button>
                                <el-button class="change-success" type="text"
                                           style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                           icon="el-icon-success" circle @click="changeSortSubmit(scope.row)">
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="套餐名称" width="300" prop="name"></el-table-column>
                    <el-table-column label="套餐类型" prop="type">
                        <template slot-scope="scope">
                            <div>{{scope.row.type == 1 ? '固定套餐' : '搭配套餐'}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="商品数量" prop="goods_count"></el-table-column>
                    <el-table-column label="优惠金额" prop="price">
                        <template slot-scope="scope">
                            <div v-if="scope.row.type == 1">￥{{scope.row.price}}</div>
                            <div v-else>￥{{scope.row.min_price}}<span v-if="scope.row.min_price != scope.row.max_price">~￥{{scope.row.max_price}}</span></div>
                        </template>
                    </el-table-column>
                    <el-table-column label="库存" prop="stock" sortable="custom">
                        <template slot-scope="scope">
                            <div>
                                <span :class="scope.row.stock < 50 ? 'zero-stock':''">{{scope.row.stock}}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status == 1">
                                <el-tag type="success">销售中</el-tag>
                            </div>
                            <div v-else-if="scope.row.status == 0">
                                <el-tag type="warning">下架中</el-tag>
                            </div>
                            <div v-else-if="scope.row.status == 2">
                                <el-tag type="danger">售罄</el-tag>
                            </div>
                            <div v-else-if="scope.row.status == 3">
                                <el-tooltip effect="dark" content="套餐异常，请重新编辑" placement="top">
                                    <el-tag type="warning">异常</el-tag>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="添加时间" prop="created_at"></el-table-column>
                    <el-table-column fixed="right" width='200' label="操作">
                        <template slot-scope="scope">
                            <el-button circle size="mini" type="text" @click="look(scope.row)">
                                <el-tooltip effect="dark" content="查看" placement="top">
                                    <img src="statics/img/mall/look.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button circle size="mini" type="text" @click="edit(scope.row)">
                                <el-tooltip effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button circle size="mini" type="text" @click="destroy(scope.row.id,scope.$index)">
                                <el-tooltip effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div flex="dir:right" style="margin-top: 20px;">
                    <el-pagination
                        hide-on-single-page
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
    <el-dialog title="查看商品" class="goods-dialog" :visible.sync="dialogVisible" width="30%">
        <div>
            <span v-if="detail.type == 1">固定套餐</span>
            <span v-else>搭配套餐</span>
            <span>共计{{detail.goods_count}}款商品</span>
            <span v-if="detail.type == 1">合计金额
                <span style="color: #ff4544;">¥{{min_price}}{{max_price != min_price &&  max_price != 0? ' ~ '+max_price : ''}} </span>
            </span>
            <span v-if="detail.type == 1" class="off-price" style="margin-left: 5px;">优惠金额：￥{{detail.price}}</span>
        </div>
        <el-table v-if="detail.type == 1" :show-header="!dialogVisible" stripe :data="detail.goods_list" border style="width: 100%;margin-top: 10px">
            <el-table-column>
                <template slot-scope="scope">
                    <div flex="dir:left">
                        <img width="50" height="50" style="margin-right: 10px;flex-shrink: 0" :src="scope.row.cover_pic" alt="">
                        <div>
                            <div>{{scope.row.name}}</div>
                            <div style="color: #ff4544;">¥{{scope.row.min_price}}{{scope.row.max_price != scope.row.min_price ? '~'+scope.row.max_price : ''}}</div>
                        </div>
                    </div>
                </template>
            </el-table-column>
        </el-table>
        <div v-else>
            <div class="list-title">主商品</div>
            <el-table :show-header="!dialogVisible" stripe :data="detail.host_list" border style="width: 100%;">
                <el-table-column>
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <img width="50" height="50" style="margin-right: 10px;flex-shrink: 0" :src="scope.row.cover_pic" alt="">
                            <div>
                                <div>{{scope.row.name}}</div>
                                <div style="color: #ff4544;">
                                    ¥{{scope.row.min_price}}{{scope.row.max_price != scope.row.min_price ? '~'+scope.row.max_price : ''}}
                                    <span v-if="detail.type == 2" class="off-price">（优惠金额：￥{{scope.row.price}}）</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div class="list-title">搭配商品</div>
            <el-table :show-header="!dialogVisible" stripe :data="detail.goods_list" border style="width: 100%;">
                <el-table-column>
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <img width="50" height="50" style="margin-right: 10px;flex-shrink: 0" :src="scope.row.cover_pic" alt="">
                            <div>
                                <div>{{scope.row.name}}</div>
                                <div style="color: #ff4544;">
                                    ¥{{scope.row.min_price}}{{scope.row.max_price != scope.row.min_price ? '~'+scope.row.max_price : ''}}
                                    <span v-if="detail.type == 2" class="off-price">（优惠金额：￥{{scope.row.price}}）</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                dialogVisible: false,
                list: [],
                url: 'plugin/composition/mall',
                batch_update_status_url: '/plugin/composition/mall/index/batch-update',
                search: {
                    keyword: '',
                    date_start: '',
                    date_end: '',
                    type: '',
                    time: [],
                },
                detail: {},
                isShowBatchButton: false,
                choose_list: [],
                id: null,
                activeName: '-1',
                pagination: {
                    page_count: 0
                },
                page: 1,
                sort_goods_id: 0,
                sort: 0,
                min_price: 0,
                max_price: 0,
                prop: null,
                order: null
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            changeSort(e) {
                this.page = 1;
                this.sort_prop = e.prop;
                if(e.order == "descending") {
                    this.sort_type = 0
                }else if (e.order == "ascending") {
                    this.sort_type = 1
                }else {
                    this.order = null
                }
                this.loadData();
            },
            changeSortSubmit(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: self.url + '/index/update',
                    },
                    data:{
                        id: row.id,
                        prop: 'sort',
                        prop_value: self.sort
                    },
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                        self.sort_goods_id = 0;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                });
            },
            editSort(row) {
                this.sort_goods_id = row.id;
                this.sort = row.sort;
            },
            handleSelectionChange(val) {
                this.choose_list = val;
            },
            edit(row) {
                if(row.type == 1) {
                    navigateTo({
                        r: 'plugin/composition/mall/index/fixed',
                        id: row.id
                    })
                }else {
                    navigateTo({
                        r: 'plugin/composition/mall/index/goods',
                        id: row.id
                    })
                }
            },
            look(row) {
                this.detail = row;
                this.min_price = 0;
                this.max_price = 0;
                for(let i in row.goods_list) {
                    this.min_price += +row.goods_list[i].min_price
                    if(row.goods_list[i].max_price > 0) {
                        this.max_price += +row.goods_list[i].max_price
                    }
                }
                this.min_price = this.min_price.toFixed(2);
                this.max_price = this.max_price.toFixed(2);
                this.dialogVisible = true;
            },
            destroy(id, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: self.url + '/index/update',
                        },
                        data:{
                            id: id,
                            prop: 'is_delete',
                            prop_value: '1'
                        },
                        method: 'post'
                    }).then(e => {
                        self.loading = false;
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
            getAllChecked(e) {
                this.$emit('get-all-checked', e)
            },
            changePage(currentPage) {
                let self = this;
                self.page = currentPage;
                self.loadData();
            },
            changeTime() {
                if(this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                }else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.toSearch();
            },
            toSearch() {
                let that = this;
                that.loading = true;
                that.$request({
                    params: {
                        r: that.url + '/index/list',
                        status: that.activeName,
                        type: that.search.type,
                        date_start: that.search.date_start,
                        date_end: that.search.date_end,
                        keyword: that.search.keyword
                    }
                }).then(response => {
                    that.loading = false;
                    that.list = response.data.data.list;
                    that.pagination = response.data.data.pagination;
                });

            },
            loadData() {
                let that = this;
                that.loading = true;
                that.$request({
                    params: {
                        r: that.url + '/index/list',
                        page: that.page,
                        status: that.activeName,
                        type: that.search.type,
                        date_start: that.search.date_start,
                        date_end: that.search.date_end,
                        keyword: that.search.keyword,
                        sort_type: that.sort_type,
                        sort_prop: that.sort_prop,
                    }
                }).then(response => {
                    that.loading = false;
                    that.list = response.data.data.list;
                    that.pagination = response.data.data.pagination;
                });
            },
        }
    });
</script>
