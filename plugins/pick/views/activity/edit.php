<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */

?>

<style>

    .activity-body {
        padding: 20px;
        background-color: #fff;
    }
    .activity-body .item {
        width: 100%;
        border-radius: 5px;
        border: 1px solid #ebeef5;
        margin-bottom: 20px;
    }
    .item-title {
        font-size: 14px;
        padding: 20px;
        border-bottom: 1px solid #ebeef5;
    }
    .item-body {
        padding: 20px;
    }
    .add-goods {
        margin: 0;
        color: #3399ff;
        cursor: pointer;
    }
    .goods-visible-title {
        font-size: 17px;
        margin-right: 30px;
    }
    .set-inventory {
        height: 69px;
        border-top: 1px solid #ebeef5;
        border-left: 1px solid #ebeef5;
        border-right: 1px solid #ebeef5;
    }
    .set-inventory .input {
        text-align: center;
        height: 69px;
    }
    .set-inventory .input .el-input-group__prepend {
        width: 130px;
    }
    .save {
        margin-top: 12px;
    }
    .xin:before {
        content: '*';
        color: #ff4544;
    }
</style>

<div id="app" v-cloak>
    <el-card v-loading="listLoading"  shadow="never"  class="activity-list" style="border:0"  body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <!--商品弹框-->
        <el-dialog :visible.sync="goodsVisible" width="60%" class="activity-visible" :before-close="handleClose">
            <div slot="title">
                <span class="goods-visible-title">选择商品</span>
            </div>
            <div style="margin-bottom: 27px;">
                <el-input v-model="keyword"  @change="selectGood" @clear="selectGood" placeholder="根据名称搜索" clearable autocomplete="off">
                    <template slot="append">
                        <el-button @click="selectGood">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <el-table
                    :data="goodsList"
                    height="500"
                    border
                    @selection-change="selectionItem"
                    style="width: 100%">
                <el-table-column
                        type="selection"
                        :selectable='isDisabled'
                        width="45">
                </el-table-column>
                <el-table-column
                        prop="goods_id"
                        label="ID"
                        width="99"
                        width="180">
                </el-table-column>
                <el-table-column
                        prop="name"
                        label="名称">
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
            </el-table>
            <div slot="footer" class="dialog-footer" flex="main:justify">
                <el-pagination
                        @current-change="pagination"
                        background
                        :current-page="current_page"
                        layout="prev, pager, next, jumper"
                        :page-count="page_count">
                </el-pagination>
                <el-button type="primary" size="small" @click="addGoods()">确 定</el-button>
            </div>
        </el-dialog>
        <!--库存弹框-->
        <el-dialog title="编辑库存" :visible.sync="stockVisible" :before-close="stockClose">
            <div class="set-inventory">
                <div class="input" flex="main:center cross:center">
                    <div style="margin-right: 20px;">
                        <span>批量设置</span>
                    </div>
                    <div>
                        <el-input size="small" v-model="stock" @change="setStock">
                            <template slot="prepend">
                                库存
                            </template>
                            <template slot="append">
                                <el-button @click="setStock">
                                    确定
                                </el-button>
                            </template>
                        </el-input>
                    </div>
                </div>
            </div>
            <el-table
                :data="stockList"
                border
                max-height="288"
                @selection-change="selectionStock"
                style="width: 100%">
                <el-table-column
                        type="selection"
                        width="55">
                </el-table-column>
                <el-table-column v-for="(item, key) in attrList" :key="key" :prop="'attr_list[' + key + '].attr_name'" :label="item.attr_group_name">
                </el-table-column>
                <el-table-column
                        :render-header="renderHeader"
                        label="库存">
                    <template slot-scope="scope">
                        <el-input v-model="scope.row.stock"></el-input>
                    </template>
                </el-table-column>
            </el-table>
            <div slot="footer" class="dialog-footer">
                <el-button  @click="stockVisible = false">取消</el-button>
                <el-button type="primary" @click="sureStock">确 定</el-button>
            </div>
        </el-dialog>

        <div slot="header">
            <span style="color: #3399ff;cursor: pointer;" @click="routeGo('plugin/pick/mall/activity/index')">N元任选活动/</span>
            <span v-if="!activity_id">新建活动</span>
            <span v-else>编辑活动</span>
        </div>
        <div class="activity-body">
            <el-form label-width="116px">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="活动信息" name="second">
                        <div class="item">
                            <div class="item-title">活动设置</div>
                            <div class="item-body">
                                <el-form-item  label="活动名称" required>
                                    <div style="width: 350px">
                                        <el-input v-model="title"></el-input>
                                    </div>
                                </el-form-item>
                                <el-form-item label="活动时间" required>
                                    <el-date-picker
                                            size="small"
                                            type="datetimerange"
                                            value-format="yyyy-MM-dd HH:mm:ss"
                                            range-separator="至"
                                            v-model="picker_time"
                                            start-placeholder="开始日期"
                                            :picker-options="expireTimeOption"
                                            end-placeholder="结束日期">
                                    </el-date-picker>
                                </el-form-item>
                                <el-form-item required label="组合方案">
                                    <div flex="" class="item-input">
                                        <el-input :min="0" style="width:148px" type="number" v-model="rule_price"></el-input>
                                        <span style="margin: 0 8px;">元，任选</span>
                                        <el-input :min="0" style="width:148px" type="number" v-model="rule_num"></el-input>
                                        <span style="margin-left: 10px;">件</span>
                                    </div>
                                    <p class="add-goods">组合方案将显示在活动主页，例如：100元，任选3件，件数必须大于1</p>
                                </el-form-item>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="商品信息" name="first">
                        <div class="item">
                            <div class="item-title">商品设置</div>
                            <div class="item-body">
                                <el-form-item label="选择商品" required>
                                    <div style="margin-bottom: 15px;">
                                        <el-button @click="getGood">选择商品</el-button>
                                        <p class="add-goods" v-if="activityGoods.length === 0" @click="routeGo('mall/goods/edit')">商城还未添加商品？点击前往</p>
                                    </div>
                                    <div style="width: 600px">
                                        <el-table
                                                v-if="activityGoods.length > 0"
                                                :data="activityGoods"
                                                height="360"
                                                borde
                                                v-loading="tab_list_loading"
                                                @selection-change="selectionItem"
                                                style="width: 100%">
                                            <el-table-column
                                                    prop="name"
                                                    label="商品">
                                                <template slot-scope="scope">
                                                    <div flex="first">
                                                        <div style="padding-right: 10px;">
                                                            <app-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></app-image>
                                                        </div>
                                                        <div flex="dir:top">
                                                            <el-tooltip
                                                                effect="dark"
                                                                placement="top"
                                                                :content="scope.row.goodsWarehouse.name"
                                                            >
                                                                <app-ellipsis :line="2">{{scope.row.goodsWarehouse.name}}</app-ellipsis>
                                                            </el-tooltip>
                                                            <div style="color: #fe8181;">￥{{scope.row.price}}</div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    prop="name"
                                                    width="80"
                                                    label="活动库存">
                                                <template slot-scope="scope">
                                                    <div flex="dir:left cross:center">
                                                        <span>{{scope.row.goods_stock}}</span>
                                                        <el-button v-if="isEdit(scope.row.sign)" class="edit-sort" type="text" @click="editStock(scope.row)">
                                                            <img src="statics/img/mall/order/edit.png" alt="">
                                                        </el-button>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    width="130"
                                                    label="排序">
                                                <template slot-scope="scope">
                                                    <div flex="dir:left cross:center" v-if="sort_goods_id != scope.row.id">
                                                        <span>{{scope.row.sort}}</span>
                                                        <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                                            <img src="statics/img/mall/order/edit.png" alt="">
                                                        </el-button>
                                                    </div>
                                                    <div style="display: flex;align-items: center" v-else>
                                                        <el-input style="min-width: 70px" type="number" size="mini" class="change"
                                                                  v-model="scope.row.sort"
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
                                            <el-table-column
                                                    prop="name"
                                                    width="150"
                                                    label="操作">
                                                <template slot-scope="scope">
                                                    <div flex="dir:left cross:center">
                                                        <el-button class="edit-sort" type="text" @click="editGoods(scope.row)">
                                                            <el-tooltip  effect="dark" content="编辑商品" placement="top">
                                                                <img src="statics/img/mall/order/edit.png" alt="">
                                                            </el-tooltip>
                                                        </el-button>
                                                        <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                                                            <el-tooltip  effect="dark" content="删除" placement="top">
                                                                <img src="statics/img/mall/del.png" alt="">
                                                            </el-tooltip>
                                                        </el-button>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                        <div flex="main:right cross:center" style="margin-top: 20px;">
                                            <div v-if="goods.page_count > 0">
                                                <el-pagination
                                                        @current-change="goodsPagination"
                                                        background
                                                        :current-page="goods.current_page"
                                                        layout="prev, pager, next, jumper"
                                                        :page-count="goods.page_count">
                                                </el-pagination>
                                            </div>
                                        </div>
                                    </div>
                                </el-form-item>
                            </div>
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </div>
        <el-button :loading="saveLoading" type="primary" class="save"  @click="saveEdit">保存</el-button>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',

        data() {
            return {
                activeName: 'first',
                listLoading: false,
                goodsVisible: false,
                goodsList: [],
                form: {},
                current_page: 1,
                page_count: 1,
                keyword: '',
                page: 1,
                selectionList: [],
                activityGoods: [],
                sort_goods_id: 0,
                stockVisible: false,
                stockList: [],
                copyStockList: [],
                attrList: [],
                title: '',
                start_at: '',
                end_at: '',
                rule_price: '',
                rule_num: '',
                picker_time: null,
                stock: 0,
                setStockList: [],
                stockGoods: null,
                saveLoading: false,
                activity_id: null,
                status: 0,
                expireTimeOption: {
                    disabledDate(date) {
                        return date.getTime() < Date.now() - 8.64e7;
                    }
                },
                oldGoodsList: [],
                goods: {
                    current_page: 1,
                    page_count: 1,
                    page: 1
                },
                tab: 0,
                tab_list_loading: false
            };
        },

        methods: {

            getGood() {
                this.goodsVisible = true;
                this.selectGood();
            },
            // 选择商品
            async selectGood() {
                const e = await request({
                    params: {
                        r: '/plugin/pick/mall/activity/mall-goods',
                        page: this.page,
                        keyword: this.keyword
                    },
                    method: 'get'
                });
                if (e.data.code === 0) {
                    let { list, pagination } = e.data.data;
                    let { current_page, page_count } = pagination;
                    for (let i = 0; i < list.length; i++) {
                        list[i].goods_id = list[i].id;
                        delete list[i].id;
                    }
                    this.goodsList = list;
                    this.current_page = current_page;
                    this.page_count = page_count;
                }
            },
            pagination(e) {
                this.page = e;
                this.selectGood();
            },

            selectionItem(e) {
                let data = [];
                for (let i = 0; i < e.length; i++) {
                    e[i].sign = '';
                    data.push(e[i]);
                }
                this.selectionList = data;
            },

            async addGoods() {
                this.goodsVisible = false;
                this.listLoading = true;
                let newDate = [];
                for (let i = 0; i < this.selectionList.length; i++) {
                    newDate.push({
                        goods_id: this.selectionList[i].goods_id
                    });
                }
                const e = await this.editSaveGoods(newDate, 'add');
                const w = await this.getOldGoodsList(this.activity_id);
                this.listLoading = false;
                this.keyword = '';
                this.selectionList = [];

                // location.reload();
            },

            editSort(row) {
                this.sort_goods_id = row.id;
                this.sort = row.sort;
            },
            quit() {
                this.sort_goods_id = 0;
            },
            changeSortSubmit(row) {
                if (row.sort < 0) {
                    this.$message.warning('排序值不能小于0');
                } else {
                    this.sort_goods_id = 0;
                    let data = JSON.parse(JSON.stringify(row));
                    data.id = data.pickGoods.id;
                    this.editSaveGoods([data], 'edit');
                }
            },

            // 编辑库存
            editStock(row) {
                this.stockVisible = true;
                this.attrList = JSON.parse(row.attr_groups);
                this.stockList = JSON.parse(JSON.stringify(row.attr));
                this.copyStockList = row.attr;
                this.stockGoods = row;
            },

            async editSaveGoods(pick, type) {
                const e = await request({
                    params: {
                        r: `/plugin/pick/mall/activity/edit-goods`,
                    },
                    method: 'post',
                    data: {
                        type: type,
                        pick_activity_id: this.activity_id,
                        pick: JSON.stringify(pick)
                    }
                });
                if (e.data.code === 0) {
                    this.$message({
                        type: 'success',
                        message: e.data.msg
                    });
                } else {
                    this.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }
            },

            edit(row) {
                let data = {
                    r: 'mall/goods/edit',
                    id: row.goods_id,
                    mch_id: row.goods.mch_id,
                    page: 1
                };
                this.$navigate(data, true);
            },

            destroy(row, index) {
                this.$confirm('将删除该活动商品?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.tab_list_loading = true;
                    this.editSaveGoods([{id: row.pickGoods.id}], 'del').then(() => {
                        this.$delete(this.activityGoods, index);
                        this.getOldGoodsList(this.activity_id).then(e => {
                            this.tab_list_loading = false;
                        });
                    });
                }).catch(() => {
                    // this.$message({
                    //     type: 'info',
                    //     message: '已取消删除'
                    // });
                });
            },

            // 批量设置库存 全选
            selectionStock(e) {
                this.setStockList = e;
            },

            setStock() {
                if (this.setStockList.length === 0) {
                    this.$message({
                        type: 'warning',
                        message: '请选择规格'
                    });
                }
                for (let i = 0; i < this.setStockList.length; i++) {
                    this.setStockList[i].stock = this.stock;
                }
            },

            // 确定设置库存
            sureStock() {
                this.stockVisible = false;
                let stock = 0;
                for (let i = 0; i < this.stockList.length; i++) {
                    stock += Number(this.stockList[i].stock);
                }
                this.stockGoods.attr = this.stockList;
                this.stockGoods.goods_stock = stock;
                this.setStockList = [];
                this.stock = 0;
                let data = JSON.parse(JSON.stringify(this.stockGoods));
                data.id = data.pickGoods.id;
                this.editSaveGoods([data], 'edit');
            },

            async saveEdit() {
                this.saveLoading = true;
                if (this.picker_time) {
                    this.start_at = this.picker_time[0];
                    this.end_at = this.picker_time[1];
                } else {
                    this.start_at = '';
                    this.end_at = '';
                }
                if (!this.title) {
                    this.saveLoading = false;
                    return;
                }

                const e = await request({
                    params: {
                        r: `/plugin/pick/mall/activity/edit`
                    },
                    method: 'post',
                    data: {
                        title: this.title,
                        start_at: this.start_at,
                        end_at: this.end_at,
                        rule_price: this.rule_price,
                        rule_num: this.rule_num,
                        pick: JSON.stringify(this.activityGoods),
                        id: this.activity_id
                    }
                });
                this.saveLoading = false;
                if (e.data.code === 0) {
                    this.$navigate({
                        r: 'plugin/pick/mall/activity/index'
                    });
                } else if (e.data.code === 1) {
                    this.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }
            },

            async getDetail(id) {
                const e = await request({
                    params: {
                        r: `/plugin/pick/mall/activity/edit`,
                        id: id
                    }
                });
                if (e.data.code === 0) {
                    let {  title, rule_num, rule_price, start_at, end_at, time_status } = e.data.data.detail;
                    this.title = title;
                    this.rule_num = rule_num;
                    this.rule_price = rule_price;
                    this.picker_time = [start_at, end_at];
                    this.status = time_status;
                }
            },

            routeGo(r) {
                this.$navigate({
                    r: r
                });
            },

            renderHeader(h, {column, $index}) {
                return h("div", [
                    h("i", {
                        attrs: {
                            class: 'xin'
                        }
                    }),
                    h("span", ['库存'])
                ]);
            },

            handleClose() {
                this.keyword = '';
                this.goodsVisible = false;
            },

            isEdit(sign) {
                if (this.status !== '2'){
                    return true;
                } else {
                    if (sign === 'pick') {
                        return false;
                    } else {
                        return true;
                    }
                }
            },

            editGoods(row) {
                this.$navigate({
                    r: 'plugin/pick/mall/activity/edit-activity-goods',
                    id: row.id,
                    page: 1
                }, true);
            },

            stockClose() {
                this.stockVisible = false;
                this.stock = 0;
            },

            isDisabled(row, index) {
                for (let i = 0; i < this.oldGoodsList.length; i++) {
                    if (this.oldGoodsList[i] == row.goods_warehouse_id) {
                        return false;
                    }
                }
                return true;
            },

            async getOldGoodsList(activity_id) {
                const e = await request({
                    params: {
                        r: '/plugin/pick/mall/activity/goods',
                        id: activity_id,
                        limit: 4,
                        page: this.goods.page
                    }
                });
                let { pagination, goods, list } = e.data.data;
                this.oldGoodsList = goods;
                this.activityGoods = list;
                this.goods.page_count = pagination.page_count;
                this.goods.current_page = pagination.current_page;
            },

            goodsPagination(e) {
                this.goods.page = e;
                this.getOldGoodsList(this.activity_id);
            }
        },

        mounted() {
            let id = getQuery('id');
            let tab = getQuery('tab');
            if (tab == 1) {
                this.activeName = 'first';
            } else {
                this.activeName = 'second';
            }
            this.activity_id = id;
            if (id) {
                this.getDetail(id);
                this.getOldGoodsList(id);
            }
        },

        watch: {
            activeName(data) {
                if (data === 'first') {
                    if (!this.activity_id) {
                        this.$confirm('请先保存活动信息', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.activeName = 'second';
                        }).catch(() => {
                            this.activeName = 'second';
                        });
                    } else {
                        this.tab = 0;
                    }
                } else {

                    this.tab = 1;
                }
            }
        }
    });
</script>
