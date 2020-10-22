<?php
/**
 * Created by : PhpStorm
 * File name: edit.php
 * User: fujuntao
 * Date: 2020/9/18
 * Time: 17:17
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>

<style>
    .activity-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 100px;
    }
    .activity-body .item-cont {
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

    .selectionTab {
        width: 328px;
        position: absolute;
        bottom: 13px;

        z-index: 1000;
    }
    .selectionTab .tab {
        width: 100%;
        height: 50px;
        border: 1px solid #e4e7ed;
        background: #ffffff;
        padding: 0 10px;
    }
    .selectionTab .tab>div {
        line-height: 50px;
    }
    .batch_n_0 {
        left: 160px;
    }
    .batch_n_1 {
        left: 255px;
    }
    .batch_n_2 {
        left: 340px;
    }
    .triangle {
        width: 8px;
        height: 8px;
        border-top: 1px solid #e4e7ed;
        border-left: 1px solid #e4e7ed;
        position: absolute;
        top: -3px;
        left: 25px;
        transform: rotate(45deg);
        background: white;
    }
    .el-tabs__content {
        padding-bottom: 30px;
    }
    .el-table /deep/.DisabledSelection .cell .el-checkbox__inner {
        display: none;
        position: relative;
    }
    .el-table /deep/.DisabledSelection .cell:before {
        content: '选择';
        position: absolute;
        right: 11px;
    }
    .attr-name {
        padding: 17px 10px 17px 10px;
        border-top:1px solid #ebeef5;
        border-left:1px solid #ebeef5;
        border-right:1px solid #ebeef5;
    }
    .goods_page {
        border-bottom: 1px solid #ebeef5;
        border-left: 1px solid #ebeef5;
        border-right: 1px solid #ebeef5;
        padding: 10px;
    }
    .add-goods {
        color: #409eff;
        cursor: pointer;
        margin: 0;
    }
    .el-form-item__content .el-radio {
        width: 300px;
    }
    .goods-visible-title {
        font-size: 21px;
    }
    .example-visible .el-dialog {
        min-width: 500px;
    }
    .example-visible .el-dialog__body {
        padding: 10px 20px 30px 20px;
    }
    .input-red input {
        border-color: #ff7b7b;
    }
</style>

<div id="app" v-cloak>
    <el-card style="border:0"  shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="loading">
        <div slot="header">
            <span style="color: #3399ff;cursor: pointer;" @click="routeGo('plugin/flash_sale/mall/activity/index')">限时抢购/</span>
            <span>{{header_name}}</span>
        </div>
        <!--商品弹框-->
        <el-dialog :visible.sync="goodsVisible" width="60%" class="activity-visible">
            <div slot="title">
                <span class="goods-visible-title" style="font-size: 23px;">选择商品</span>
            </div>
            <div style="margin-bottom: 27px;">
                <el-input v-model="goodsSearch.keyword"  @change="selectGood" @clear="selectGood" placeholder="根据名称搜索" clearable autocomplete="off">
                    <template slot="append">
                        <el-button @click="selectGood">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <el-table
                    :data="goodsList"
                    height="500"
                    border
                    v-loading="goodsLoading"
                    @selection-change="selectionItem"
                    style="width: 100%">
                <el-table-column
                        type="selection"
                        :selectable='isDisabled'
                        width="45">
                </el-table-column>
                <el-table-column
                        prop="goods_warehouse_id"
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
                        @current-change="goodsPagination"
                        background
                        :current-page="goodsSearch.current_page"
                        layout="prev, pager, next"
                        :page-count="goodsSearch.page_count">
                </el-pagination>
                <el-button type="primary" size="small" @click="addGoods()">确 定</el-button>
            </div>
        </el-dialog>

        <el-dialog :visible.sync="exampleVisible" width="30%" class="example-visible">
            <div slot="title">
                <span class="goods-visible-title">查看活动预告图例</span>
            </div>
           <div flex="main:center" style="padding-top: 36px;border-top: 1px solid #e2e2e2">
               <image style="width: 308px; height: 353px;" :src="example_url"></image>
           </div>
            <div slot="footer" class="dialog-footer" flex="main:right">
                <el-button type="primary" size="small" @click="exampleVisible = false">我知道了</el-button>
            </div>
        </el-dialog>

        <el-form :model="form" label-width="100px" class="activity-body" :rules="rule" ref="form" style="background-color: #ffffff;">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="活动信息" name="first">
                    <div class="item-cont">
                        <div class="item-title">活动设置</div>
                        <div class="item-body">
                            <el-form-item  label="活动名称" prop="title">
                                <div style="width: 350px">
                                    <el-input type="text" maxlength="10"
                                              :disabled="title_disabled"
                                              show-word-limit v-model="form.title"></el-input>
                                </div>
                            </el-form-item>
                            <el-form-item label="开始时间" prop="start_at">
                                <el-date-picker
                                    type="datetime"
                                    v-model="form.start_at"
                                    :disabled="start_at_disabled"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    :picker-options="expireTimeOption"
                                    placeholder="选择日期">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item label="结束时间" prop="end_at">
                                <el-date-picker
                                    type="datetime"
                                    v-model="form.end_at"
                                    :disabled="end_at_disabled"
                                    :picker-options="expireTimeOption"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    placeholder="选择日期">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item label="活动预告" style="margin-bottom: 0">
                                <div flex="dir:top" style="padding-top: 15px;">
                                    <el-radio
                                        :label="0"
                                        :disabled="notice_disabled"
                                        v-model="form.notice"
                                    >不进行预告
                                    </el-radio>
                                    <el-radio
                                        style="margin-top: 15px;"
                                        :label="1"
                                        :disabled="notice_disabled"
                                        v-model="form.notice"
                                    >创建后就进行活动预告
                                    </el-radio>
                                    <el-radio
                                        style="margin-top: 10px;"
                                        :label="2"
                                        :disabled="notice_disabled"
                                        v-model="form.notice"
                                    >
                                        <span>活动开始前</span>
                                        <el-input
                                            v-model="form.notice_hours"
                                            :disabled="notice_disabled"
                                            placeholder="请输入大于0的整数"
                                            @input.native="inputhandle"
                                            style="width: 180px"
                                        ></el-input>
                                        <span>小时进行预告</span>
                                    </el-radio>
                                    <div v-if="notice_hours" style="font-size: 11px;color: #ff7b7b;line-height: 1.5;padding-left: 100px;">请输入大于0的整数</div>
                                </div>
                                <div style="color: #999999;font-size: 14px;">
                                    活动预告期间，商品详情页将会展示开始时间和折扣力度
                                    <span style="color: #409eff;cursor: pointer;"  @click="exampleVisible = true">查看图例</span>
                                </div>
                            </el-form-item>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="商品信息" name="second">
                    <div class="item-cont">
                        <div class="item-title">商品设置</div>
                        <div class="item-body">
                            <el-form-item label="选择商品" required>
                                <div style="margin-bottom: 15px;">
                                    <el-button  @click="getGood">选择商品</el-button>
                                    <p class="add-goods" v-if="list.length === 0" @click="routeGo('mall/goods/edit')">商城还未添加商品？点击前往</p>
                                </div>
                            </el-form-item>
                            <el-table
                                v-if="list.length > 0"
                                ref="multipleTable"
                                border :data="list"
                                :header-cell-class-name="cellCalss"
                                v-loading="tabLoading"
                                @selection-change="handleSelectionChange"
                            >
                                <el-table-column
                                        type="selection"
                                        width="45">
                                </el-table-column>
                                <el-table-column
                                        prop="id"
                                        label="ID"
                                        width="99">
                                </el-table-column>
                                <el-table-column
                                    label="商品">
                                    <template slot-scope="scope">
                                        <div flex="first" >
                                            <div style="margin-right: 10px;">
                                                <app-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></app-image>
                                            </div>
                                            <div style="position: relative;width:200px;">
                                                <div style="position:absolute;">
                                                    <el-tooltip class="item"
                                                                effect="dark"
                                                                placement="top">
                                                        <template slot="content">
                                                            <div style="width: 320px;">{{scope.row.goodsWarehouse.name}}</div>
                                                        </template>
                                                        <div style="overflow: hidden;display: -webkit-box;height:25px;line-height: 25px;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">{{scope.row.goodsWarehouse.name}}</div>
                                                    </el-tooltip>
                                                    <div>库存：{{scope.row.goods_stock}}</div>
                                                    <div v-if="scope.row.use_attr == 1">已选{{scope.row.attr.length}}个规格</div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="售价">
                                    <template slot-scope="scope">
                                        {{scope.row | getPrice}}
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        width="185"
                                        label="折扣">
                                    <template slot-scope="scope">
                                        <div style="height: 60px;">
                                            <div flex="main:center cross: center" style="margin-top: 8px;">
                                                <el-input

                                                    @change="check_dis(scope.row)"
                                                    style="width: 120px;margin-right: 11px;"
                                                    @input="cut_dis_in(scope.row)"
                                                    :class="scope.row.is_discount ? 'input-red' : ''"
                                                    v-if="scope.row.flashSaleGoods.type == 1"
                                                    v-model="scope.row.discount"
                                                ></el-input>
                                                <el-input

                                                    style="width: 120px;margin-right: 11px;"
                                                    :class="scope.row.is_discount ? 'input-red' : ''"
                                                    v-if="scope.row.flashSaleGoods.type == 2"
                                                    :placeholder="scope.row.discount"
                                                    @focus="focus_change(scope.row, 1)"
                                                ></el-input>
                                                <div flex="cross:center">折</div>
                                            </div>
                                            <div v-if="scope.row.is_discount" style="font-size: 11px;color: #ff7b7b;">请输入0.1-10之间的折扣</div>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        width="185"
                                        label="减钱（元）">
                                    <template slot-scope="scope">
                                        <div style="height: 60px;">
                                            <div flex="main:center cross: center" style="margin-top: 8px;">
                                                <div flex="cross:center">减</div>
                                                <el-input v-if="scope.row.flashSaleGoods.type == 2"
                                                          :class="scope.row.is_cut ? 'input-red' : ''"
                                                          @change="cut_money(scope.row)"

                                                          @input="cut_money_in(scope.row)"
                                                          style="width: 150px;margin-left: 12px;"
                                                          v-model="scope.row.cut"
                                                          :placeholder="scope.row.cut"></el-input>
                                                <el-input v-if="scope.row.flashSaleGoods.type == 1"
                                                          @focus="focus_change(scope.row, 2)"

                                                          style="width: 150px;margin-left: 12px;"
                                                          :placeholder="scope.row.cut"></el-input>
                                            </div>
                                            <div v-if="scope.row.is_cut"
                                                 style="font-size: 11px;color: #ff7b7b;"
                                            >减钱金额须小于售价</div>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="flashSaleGoods.type"
                                    width="125"
                                    label="优惠方式">
                                    <template slot-scope="scope">
                                        {{scope.row.flashSaleGoods.type == 1 ? '折扣' : scope.row.flashSaleGoods.type == 2 ? '减钱' : scope.row.flashSaleGoods.type == 3 ? '促销价' : ''}}
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
                                        v-if="!goods_disabled_edit"
                                        fixed="right"
                                    label="操作">
                                    <template slot-scope="scope">
                                        <el-button type="text" circle size="mini" @click="edit(scope.row)" >
                                            <el-tooltip class="" effect="dark" content="编辑" placement="top">
                                                <img src="statics/img/mall/edit.png">
                                            </el-tooltip>
                                        </el-button>
                                        <el-button type="text" circle size="mini" @click="deleteGoods(scope.row)" >
                                            <el-tooltip class="" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div flex="main:justify cross:center"  class="goods_page" v-if="list.length > 0">
                                <div style="position: relative;">
                                    <el-checkbox v-model="all" @change="toggleSelection" :disabled="goods_disabled" style="margin-right: 32px;">
                                        全选当前页
                                    </el-checkbox>
                                    <el-button plain size="small" :disabled="goods_disabled" @click="batchSetting(0)">设置折扣</el-button>
                                    <el-button plain size="small" :disabled="goods_disabled" @click="batchSetting(1)">设置减钱</el-button>
                                </div>
                                <div>
                                    <el-pagination
                                            v-if="listGoods.page_count > 0"
                                            @current-change="pagination"
                                            background
                                            layout="prev, pager, next"
                                            :page-count="listGoods.page_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="selectionTab" v-if="is_batch" :class="batchN === 0 ? 'batch_n_0' : batchN === 1 ? 'batch_n_1 ' : batchN === 2 ? 'batch_n_2' : ''">
                        <div class="triangle"></div>
                        <div class="tab" flex="cross: center">
                            <div flex="cross: center" v-if="batchN === 1">减</div>
                            <div style="margin-left: 10px;">
                               <div>
                                   <el-input style="width: 132px" size="small" @change="batchInput" v-model="batch">
                                       <span slot="append">{{batchN == 0 ? '折' : '元'}}</span>
                                   </el-input>
                               </div>
                            </div>
                            <div style="margin-left: 21px;">
                                <el-button size="small" @click="is_batch = false">取消</el-button>
                            </div>
                            <div style="margin-left: 7px;">
                                <el-button size="small" @click="sureBatch"  type="primary">确定</el-button>
                            </div>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" v-if="!save_disabled" :loading="btnLoading" type="primary" @click="store('form')" size="small">
                保存
            </el-button>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                tabLoading: false,
                form: {
                    title: '',
                    start_at: '',
                    end_at: '',
                    notice: 0,
                    notice_hours: null,
                    id: null
                },
                rule: {
                    title: [
                        {required: true, message: '请输入活动名称', trigger: 'change'}
                    ],
                    start_at: [
                        {required: true, message: '请输入开始时间', trigger: 'change'}
                    ],
                    end_at: [
                        {required: true, message: '请输入结束时间', trigger: 'change'}
                    ]
                },
                activeName: 'first',
                list: [],
                goodsSearch: {
                    page: 1,
                    keyword: '',
                    page_count: 1
                },
                goodsList: [],
                goodsVisible: false,
                goodsLoading: false,
                selectionList: [],
                listGoods: {
                    page_count: 1,
                    page: 1
                },
                oldGoods: [],
                batch: 0,
                is_batch: false,
                batchN: 0,
                allSelect: false,
                all: false,


                selectList: [],

                goods_disabled: false,
                title_disabled: false,
                start_at_disabled: false,
                end_at_disabled: false,
                notice_disabled: false,
                goods_disabled_edit: false,
                exampleVisible: false,
                save_disabled: false,
                example_url: '',
                editList: [],
                expireTimeOption: {
                    disabledDate(date) {
                        return date.getTime() < Date.now() - 8.64e7;
                    }
                },
                header_name: '新建活动',
                notice_hours: false,
                sort_goods_id: null
            };
        },
        methods: {
            store(formName) {
                if (this.activeName === 'first') {
                    this.$refs[formName].validate(valid => {
                        if (valid) {
                            this.btnLoading = true;
                            if (this.notice_hours) {
                                this.btnLoading = false;
                                return;
                            }
                            request({
                                params: {
                                    r: 'plugin/flash_sale/mall/activity/edit'
                                },
                                method: 'post',
                                data: this.form
                            }).then(e => {
                                this.btnLoading = false;
                                if (e.data.code === 0) {
                                    this.form.id = e.data.id;
                                    this.$message.success(e.data.msg);
                                    this.$nextTick(() => {
                                        this.activeName = 'second';
                                    });
                                } else if (e.data.code === 1) {
                                    this.$message.warning(e.data.msg);
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            });
                        } else {
                            this.btnLoading = false;
                            return false;
                        }
                    })
                } else {
                    this.$refs[formName].validate(valid => {
                        if (valid) {
                            this.btnLoading = true;
                            let is_ok = false;
                            for (let i = 0; i < this.list.length; i++) {
                                if (this.list[i].is_cut || this.list[i].is_discount) {
                                    is_ok = true;
                                }
                            }
                            if (is_ok) {
                                this.btnLoading = false;
                                return;
                            }
                            request({
                                params: {
                                    r: 'plugin/flash_sale/mall/activity/edit-goods'
                                },
                                method: 'post',
                                data: {
                                    activity_id: this.form.id,
                                    add: JSON.stringify([]),
                                    del: JSON.stringify([]),
                                    edit: JSON.stringify(this.editList)
                                },
                            }).then(e => {
                                this.btnLoading = false;
                                if (e.data.code === 0) {
                                    this.$message.success('保存成功');
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            });
                        } else {
                            this.btnLoading = false;
                            return false;
                        }
                    })
                }
            },

            async getList() {
                const g = await request({
                    params: {
                        r: 'plugin/flash_sale/mall/activity/goods',
                        id: this.form.id,
                        limit: 10,
                        page: this.listGoods.page
                    },
                    method: 'get'
                });
                let { code , data } = g.data;
                if (code === 0) {
                    let {goods, list, pagination } = data;
                    this.listGoods.page_count = pagination.page_count;
                    this.oldGoods = goods;
                    for (let i = 0; i < list.length; i++) {
                        list[i].attr.sort((a, b) => {
                            return Number(a.price) - Number(b.price);
                        });
                        list[i].is_discount = false;
                        list[i].is_cut = false;
                        if (list[i].use_attr === 0) {
                            let {cut, discount, type} = list[i].attr[0].extra;
                            list[i].cut = cut;
                            list[i].discount = discount;
                            list[i].flashSaleGoods.type = type;
                        } else if (list[i].use_attr === 1) {
                            list[i].flashSaleGoods.type = list[i].attr[0].extra.type;
                            if (list[i].attr.length !== 1) {
                                // cut
                                let minCut = Number(list[i].attr[0].extra.cut);
                                let maxCut = Number(list[i].attr[list[i].attr.length - 1].extra.cut);
                                if (minCut === maxCut) {
                                    list[i].cut = minCut;
                                } else {
                                    list[i].cut = `${minCut}~${maxCut}`;
                                }
                                // discount
                                let minDiscount = Number(list[i].attr[0].extra.discount);
                                let maxDiscount = Number(list[i].attr[list[i].attr.length - 1].extra.discount);
                                if (minDiscount === maxDiscount) {
                                    list[i].discount = minDiscount.toFixed(1);
                                } else {
                                    list[i].discount = `${minDiscount.toFixed(1)}~${maxDiscount.toFixed(1)}`;
                                }
                            } else {
                                let { cut, discount } = list[i].attr[0].extra;
                                list[i].discount = discount;
                                list[i].cut = cut;
                            }
                        }
                    }
                    for (let i = 0; i < list.length; i++) {
                        if (Number(list[i].attr[0].extra.cut) > Number(list[i].attr[0].price) ) {
                            list[i].is_cut = true;
                            list[i].is_discount = true;
                        }
                    }
                    this.list = list;
                }
            },

            async loadData() {
                try {
                    this.loading = true;
                    const e = await request({
                        params: {
                            r: 'plugin/flash_sale/mall/activity/edit',
                            id: this.form.id
                        },
                        method: 'get'
                    });
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.form = e.data.data.detail;
                        this.example_url =e.data.example_url;
                    } else {
                        this.example_url =e.data.example_url;
                    }
                    await this.getList();
                } catch (e) {
                    throw new Error(e);
                }
            },

            getGood() {
                this.goodsVisible = true;
                this.getGoods();
            },

            getGoods() {
                this.goodsLoading = true;
                request({
                    params: {
                        r: '/plugin/flash_sale/mall/activity/mall-goods',
                        page: this.goodsSearch.page,
                        keyword: this.goodsSearch.keyword
                    }
                }).then(e => {
                    this.goodsLoading = false;
                    if (e.data.code === 0) {
                        this.goodsList = e.data.data.list;
                        this.goodsSearch.page_count = e.data.data.pagination.page_count;
                        this.goodsSearch.current_page = e.data.data.pagination.current_page;
                    }
                })
            },

            goodsPagination(e) {
                this.goodsSearch.page = e;
                this.getGoods();
            },

            selectGood() {
                this.goodsSearch.page = 1;
                this.getGoods();
            },
            selectionItem(e) {
                this.selectionList = e;
            },

            isDisabled(row) {
                for (let i = 0; i < this.oldGoods.length; i++) {
                    if (this.oldGoods[i] == row.goods_warehouse_id) {
                        return false;
                    }
                }
                return true;
            },

            addGoods() {
                this.goodsVisible = false;
                this.tabLoading = true;
                let add = [];
                for (let i = 0; i < this.selectionList.length; i++) {
                    add.push({
                        id: this.selectionList[i].id,
                        attr: this.selectionList[i].attr,
                        attr_groups: this.selectionList[i].attr_groups,
                        type: 1
                    })
                }
                let del = [];
                let edit = []
                request({
                    params: {
                        r: 'plugin/flash_sale/mall/activity/edit-goods'
                    },
                    method: 'post',
                    data: {
                        activity_id: this.form.id,
                        add: JSON.stringify(add),
                        del: JSON.stringify(del),
                        edit: JSON.stringify(edit)
                    },
                }).then(e => {
                    this.tabLoading = false;
                    if (e.data.code === 0) {
                        this.listGoods.page = 1;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },

            handleClick(e) {
            },

            pagination(e) {
                this.listGoods.page = e;
                this.tabLoading = true;
                this.editList = [];
                this.getList().then(() => {
                    this.tabLoading = false;
                });
            },

            deleteGoods(row) {
                this.$confirm('删除商品', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.tabLoading = true;
                    let del = [{
                        id: row.flashSaleGoods.id,
                        attr: row.attr,
                        attr_groups: row.attr_groups,
                    }];
                    request({
                        params: {
                            r: 'plugin/flash_sale/mall/activity/edit-goods'
                        },
                        method: 'post',
                        data: {
                            activity_id: this.form.id,
                            add: JSON.stringify([]),
                            del: JSON.stringify(del),
                            edit: JSON.stringify([])
                        },
                    }).then(e => {
                        if (e.data.code === 0) {
                            if (this.list.length === 1) {
                                let page = this.listGoods.page;
                                if (page - 1 > 0) {
                                    this.listGoods.page = page - 1;
                                } else {
                                    this.listGoods.page = 1;
                                }
                            }
                            this.getList().then(() => {
                                this.tabLoading = false;
                            });

                        } else {
                            this.$message.error(e.data.msg);
                        }
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消'
                    });
                });
            },

            toggleSelection() {
                this.$refs.multipleTable.toggleAllSelection();
            },

            batchSetting(data) {
                if (this.selectList.length === 0) return;
                this.is_batch = true;
                this.batchN = data;
                this.batch = 0;
            },

            batchInput() {
                if (this.batchN === 0) {
                    let batch = '' + this.batch;
                    batch = batch.replace(/[^\d.]/g, '')
                        .replace(/\.{2,}/g, '.')
                        .replace('.', '$#$')
                        .replace(/\./g, '')
                        .replace('$#$', '.')
                        .replace(/^(\-)*(\d+)\.(\d).*$/, '$1$2.$3');
                    if (batch.indexOf('.') < 0 && batch != '') {
                        batch = parseFloat(batch);
                    }

                    this.batch = batch;
                }
            },

            sureBatch() {
                this.is_batch = true;
                let type = 1;
                if (this.batchN === 0) {
                    type = 1;
                    for (let i = 0; i < this.selectList.length; i++) {
                        this.selectList[i].discount = this.batch;
                        if (this.batch > 10 || this.batch < 0.1) {
                            this.selectList[i].is_cut = true;
                            this.selectList[i].is_discount = true;
                        } else {
                            this.selectList[i].is_cut = false;
                            this.selectList[i].is_discount = false;
                        }
                        for (let j = 0; j < this.selectList[i].attr.length; j++) {
                            this.selectList[i].attr[j].extra.discount = this.batch;
                            this.selectList[i].attr[j].extra.type = type;
                            this.selectList[i].attr[j].extra.cut = (this.selectList[i].attr[j].price - this.selectList[i].attr[j].price * (this.batch/10)).toFixed(2);
                        }
                        if (this.selectList[i].use_attr === 1) {
                            if (this.selectList[i].attr.length === 1) {
                                this.selectList[i].cut = `${this.selectList[i].attr[0].extra.cut}`;
                            } else {
                                if (Number(this.selectList[i].attr[0].extra.cut) === Number(this.selectList[i].attr[this.selectList[i].attr.length - 1].extra.cut)) {
                                    this.selectList[i].cut = this.selectList[i].attr[0].extra.cut;
                                } else {
                                    this.selectList[i].cut = `${this.selectList[i].attr[0].extra.cut}~${this.selectList[i].attr[this.selectList[i].attr.length - 1].extra.cut}`;
                                }

                            }
                        } else {
                            this.selectList[i].cut = this.selectList[i].attr[0].extra.cut;
                        }
                    }
                } else if (this.batchN === 1) {
                    // 减钱
                    type = 2;
                    for (let i = 0; i < this.selectList.length; i++) {
                        for (let j = 0; j < this.selectList[i].attr.length; j++) {
                            let attr = this.selectList[i].attr[j];
                            let price = Number(attr.price);
                            attr.extra.cut = this.batch;
                            if (price > this.batch) {
                                this.selectList[i].is_cut = false;
                                this.selectList[i].is_discount = false;
                            } else {
                                this.selectList[i].is_discount = true;
                                this.selectList[i].is_cut = true;
                            }
                            attr.extra.type = type;
                        }
                        this.selectList[i].cut = this.selectList[i].attr[0].extra.cut;
                        for (let j = 0; j < this.selectList[i].attr.length; j++) {
                            let attr = this.selectList[i].attr[j];
                            let price = Number(attr.price);
                            this.selectList[i].attr[j].extra.cut = this.selectList[i].cut;
                            if (price - attr.extra.cut === 0) {
                                attr.extra.discount = '0.0';
                            } else {
                                attr.extra.discount = (((price - attr.extra.cut) / price) * 10).toFixed(1);
                            }
                        }
                        if (this.selectList[i].use_attr === 1) {
                            let maxDiscount = this.selectList[i].attr[0].extra.discount;
                            let minDiscount = this.selectList[i].attr[this.selectList[i].attr.length - 1].extra.discount;
                            if (this.selectList[i].attr.length === 1) {
                                this.selectList[i].discount = `${maxDiscount}`;
                            } else {
                                if (Number(maxDiscount) === Number(minDiscount)) {
                                    this.selectList[i].discount = `${minDiscount}`;
                                } else {
                                    this.selectList[i].discount = `${maxDiscount}~${minDiscount}`;
                                }
                            }
                        } else {
                            this.selectList[i].discount = this.selectList[i].attr[0].extra.discount;
                        }
                    }
                }
                let edit = [];
                for (let i = 0; i < this.selectList.length; i++) {
                    this.selectList[i].flashSaleGoods.type = type;
                    edit.push({
                        id: this.selectList[i].id,
                        attr: this.selectList[i].attr,
                        attr_groups: this.selectList[i].attr_groups,
                        type: type,
                        sort: this.selectList[i].sort,

                    })
                }
                this.editList = edit;
                this.is_batch = false;
                this.batch = 0;
            },
            cellCalss(row) {
                if (row.columnIndex === 0) {
                    return 'DisabledSelection'
                }
            },

            check_dis(row) {

                let discount = '' + row.discount;

                if (discount.indexOf('.') < 0 && discount != '') {
                    discount = parseFloat(discount);
                }

                row.discount = discount;
                for (let i = 0; i < row.attr.length; i++) {
                    row.attr[i].extra.discount = discount;
                    row.attr[i].extra.cut = (row.attr[i].price - row.attr[i].price * (discount/10)).toFixed(2);
                    row.attr[i].extra.type = 1;
                }

                if (row.use_attr === 1) {
                    if (row.attr.length === 1) {
                        row.cut = `${row.attr[0].extra.cut}`;
                    } else {
                        if (Number(row.attr[0].extra.cut) === Number(row.attr[row.attr.length - 1].extra.cut)) {
                            row.cut = row.attr[0].extra.cut;
                        } else {
                            row.cut = `${row.attr[0].extra.cut}~${row.attr[row.attr.length - 1].extra.cut}`;
                        }

                    }
                } else {
                    row.cut = row.attr[0].extra.cut;
                }
                row.flashSaleGoods.type = 1;
                for (let i = 0; i < this.editList.length; i++) {
                    if (this.editList[i].id == row.id) {
                        this.editList.splice(i, 1);
                    }
                }
                this.editList.push({
                    id: row.id,
                    attr: row.attr,
                    attr_groups: row.attr_groups,
                    type: row.flashSaleGoods.type,
                    sort: row.sort,

                })
            },

            cut_dis_in(row) {
                row.discount = row.discount.replace(/[^\d.]/g, '')
                    .replace(/\.{2,}/g, '.')
                    .replace('.', '$#$')
                    .replace(/\./g, '')
                    .replace('$#$', '.')
                    .replace(/^(\-)*(\d+)\.(\d).*$/, '$1$2.$3');
                if (row.discount > 10 || row.discount < 0.1) {
                    row.is_cut = true;
                    row.is_discount = true;
                } else {
                    row.is_cut = false;
                    row.is_discount = false;
                }
            },
            cut_money(row) {
                let cut = '' + row.cut;
                let price = Number(row.attr[0].price);

                for (let i = 0; i < row.attr.length; i++) {
                    let price = Number(row.attr[i].price);
                    row.attr[i].extra.cut = cut;
                    if (price - cut === 0) {
                        row.attr[i].extra.discount = '0.0';
                    } else if (price - cut < 0) {
                        row.attr[i].extra.discount = -1;
                    } else {
                        row.attr[i].extra.discount = (((price - cut) / price) * 10).toFixed(1);
                    }
                    row.attr[i].extra.type = 2;
                }
                if (row.use_attr === 1) {
                    let maxDiscount = row.attr[0].extra.discount;
                    let minDiscount = row.attr[row.attr.length - 1].extra.discount;

                    if (row.attr.length === 1) {
                        row.discount = `${maxDiscount}`;
                    } else {
                        if (Number(maxDiscount) === Number(minDiscount)) {
                            row.discount = `${minDiscount}`;
                        } else {
                            row.discount = `${maxDiscount}~${minDiscount}`;
                        }
                    }
                } else {
                    row.discount = (((price - cut) / price) * 10).toFixed(1);
                }
                row.cut = cut;

                row.flashSaleGoods.type = 2;
                for (let i = 0; i < this.editList.length; i++) {
                    if (this.editList[i].id == row.id) {
                        this.editList.splice(i, 1);
                    }
                }
                this.editList.push({
                    id: row.id,
                    attr: row.attr,
                    attr_groups: row.attr_groups,
                    type: row.flashSaleGoods.type,
                    sort: row.sort,
                })
            },

            cut_money_in(row) {
                row.cut = row.cut.replace(/[^\d.]/g, '').replace(/\.{2,}/g, '.')
                    .replace('.', '$#$')
                    .replace(/\./g, '')
                    .replace('$#$', '.')
                    .replace(/^(\-)*(\d+)\.(\d\d).*$/, '$1$2.$3');
                let price = Number(row.attr[0].price);
                if (row.cut > price) {
                    row.is_cut = true;
                    row.is_discount = true;
                } else {
                    row.is_cut = false;
                    row.is_discount = false;
                }
            },

            edit(row) {
                navigateTo({
                    r: 'plugin/flash_sale/mall/activity/edit-activity-goods',
                    id: row.id,
                    activity_id: this.form.id,
                    edit: 2
                });
            },

            routeGo(r) {
                navigateTo({
                    r
                });
            },


            handleSelectionChange(data) {
                this.selectList = data;

            },

            focus_change(row, i) {
                if (i === 2) {
                    row.cut = '';
                } else {
                    row.discount = '';
                }
                row.flashSaleGoods.type = i;
            },

            inputhandle({ target }) {
                target.value = target.value.replace(/[^0-9]/g, "");
                target.value = Number(target.value);
                if (target.value <= 0 && this.form.notice == 2) {
                    this.notice_hours = true;
                } else {
                    this.notice_hours = false;
                }
                this.form.notice_hours = target.value;
            },

            editSort(row) {
                this.sort_goods_id = row.id;
            },

            quit() {
                this.sort_goods_id = 0;
            },

            changeSortSubmit(row) {
                if (row.sort < 0) {
                    this.$message.warning('排序值不能小于0');
                } else {
                    this.sort_goods_id = 0;
                    for (let i = 0; i < this.editList.length; i++) {
                        if (this.editList[i].id == row.id) {
                            this.editList.splice(i, 1);
                        }
                    }
                    this.editList.push({
                        id: row.id,
                        attr: row.attr,
                        attr_groups: row.attr_groups,
                        type: row.flashSaleGoods.type,
                        sort: row.sort,
                    });
                }
            },
        },
        filters: {
            getPrice(row) {
                let data = JSON.parse(JSON.stringify(row));
                if (data.use_attr === 1) {
                    let str = ``;
                    if (data.attr.length === 1) {
                        str = `￥${data.attr[0].price}`;
                    } else {
                        str = `￥${data.attr[0].price}~￥${data.attr[data.attr.length - 1].price}`;
                    }
                    return str;
                } else {
                    return `￥${data.price}`;
                }
            }
        },
        watch: {
            activeName: {
                handler(data) {
                    if (data === 'second' && !this.form.id) {
                        this.$confirm('请先保存活动信息', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.$nextTick(() => {
                                this.activeName = 'first';
                            });
                        }).catch(() => {
                            this.$nextTick(() => {
                                this.activeName = 'first';
                            });
                        });
                    }
                }
            },
            'form.notice': {
                handler(data) {
                    console.log(data);
                    if (!this.form.id) {
                        this.form.notice_hours = null;
                    }
                    this.notice_hours = false;
                }
            },
            'selectList': function() {
                const self = this;
                let sign = 0;
                this.list.forEach(function (item, index) {
                    self.selectList.map((item1) => {
                        if (JSON.stringify(item1.id) === JSON.stringify(item.id)) {
                            sign++;
                        }
                    });
                });
                self.all = self.list.length === sign;
            }
        },
        created() {
            let id = getQuery('id');
            this.form.id = id;
            if (id) {
                this.header_name = '编辑活动';
            }
            let status = getQuery('status');
            if (status === '3') {
                this.goods_disabled = true;
                this.title_disabled = true;
                this.start_at_disabled = true;
                this.end_at_disabled = true;
                this.notice_disabled = true;
                this.goods_disabled_edit = true;
                this.save_disabled = true;
                this.header_name = '查看活动';
            } else if (status === '2') {
                this.goods_disabled = true;
                this.start_at_disabled = true;
            }
            let edit = getQuery('edit');
            if (edit === '2') {
                setTimeout(() => {
                    this.activeName = 'second';
                }, 1000);
            }
            this.loadData(id);
        }
    })
</script>
