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
        margin: 12px 0;
    }
    .xin:before {
        content: '*';
        color: #ff4544;
    }

    .rlue-list {
        border: 1px solid #e2e2e2;
        padding: 20px 10px;
        margin-bottom: 20px;
        display: inline-block;
    }
    
    .item-input {
        height: 50px;
    }

    .item-input .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .place-button {
        height: 50px;
        width: 50px;
        margin: 0 40px;
    }

    .dialog-choose .el-radio__label {
        display: none;
    }
</style>

<div id="app" v-cloak>
    <el-card v-loading="listLoading"  shadow="never"  class="activity-list" style="border:0"  body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-dialog title="选择地区" :visible.sync="dialogVisible" width="50%">
            <div style="margin-bottom: 1rem;">
                <app-district :edit="area_limit" @selected="selectDistrict" :level="3"></app-district>
                <div style="text-align: right;margin-top: 1rem;">
                    <el-button type="primary" @click="districtConfirm">
                        确定选择
                    </el-button>
                </div>
            </div>
        </el-dialog>
        <!--商品弹框-->
        <el-dialog :visible.sync="goodsVisible" width="60%" class="activity-visible" :before-close="handleClose">
            <div slot="title">
                <span class="goods-visible-title">选择商品</span>
            </div>
            <div style="margin-bottom: 27px;">
                <el-input v-model="keyword"  @change="searchGood" @clear="searchGood" placeholder="根据名称搜索" clearable autocomplete="off">
                    <template slot="append">
                        <el-button @click="searchGood">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <el-table
                    v-loading="goodsLoading"
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
                        @current-change="changePagination"
                        background
                        :current-page="current_page"
                        layout="prev, pager, next"
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
            <span style="color: #3399ff;cursor: pointer;" @click="routeGo('plugin/community/mall/activity/index')">社区团购</span>
            <span style="margin: 0 5px;">/</span>
            <span v-if="!activity_id">新建活动</span>
            <span v-else>编辑活动</span>
        </div>
        <div class="activity-body">
            <el-form :model="form" size="small" label-width="116px">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="活动信息" name="second">
                        <div class="item">
                            <div class="item-title">活动设置</div>
                            <div class="item-body">
                                <el-form-item  label="团购名称" required>
                                    <div style="width: 350px">
                                        <el-input v-model="form.title" show-word-limit maxlength="10"></el-input>
                                    </div>
                                </el-form-item>
                                <el-form-item label="活动时间" required>
                                    <el-date-picker
                                            :disabled="activity_id > 0"
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
                                <el-form-item label="活动地区" required>
                                    <el-radio :disabled="form.activity_status == 1" v-model="form.is_area_limit" :label="0">全部地区</el-radio>
                                    <el-radio :disabled="form.activity_status == 1" v-model="form.is_area_limit" :label="1">指定地区</el-radio>
                                    <div v-if="form.is_area_limit == 1">
                                        <el-button type="text" size="mini" :disabled="form.activity_status == 1" circle @click.native="openDistrict">选择地区</el-button>
                                    </div>
                                    <div flex="dir:left" v-if="form.is_area_limit == 1" style="flex-wrap: wrap;width: 70%;max-width: 600px;">
                                        <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in form.area_limit" :key="key.id">
                                            {{value.name}}
                                        </el-tag>
                                    </div>
                                </el-form-item>
                                <el-form-item label="最低成团" required>
                                    <el-radio v-model="form.condition" v-if="form.condition == 1 || form.condition == 0" :label="1">开启</el-radio>
                                    <el-radio v-model="form.condition" v-if="form.condition == 2" :label="2">开启</el-radio>
                                    <el-radio v-model="form.condition" :label="0">关闭</el-radio>
                                    <div style="color: #999;font-size: 12px;margin-top: -5px;">开启后，未达到成团条件的活动订单，将自动退款</div>
                                    <div v-if="form.condition > 0" flex="dir:left cross:center" style="margin-bottom: 15px">
                                        <el-radio v-model="form.condition" :label="1">参团人数，至少满</el-radio>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="number" min="1" style="width: 200px;" v-model.number="people_num">
                                                <template slot="append">人</template>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-if="form.condition > 0" flex="dir:left cross:center">
                                        <el-radio v-model="form.condition" :label="2">商品件数，至少满</el-radio>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="number" min="1" style="width: 200px;" v-model.number="goods_num">
                                                <template slot="append">件</template>
                                            </el-input>
                                        </div>
                                    </div>
                                </el-form-item>
                                <el-form-item v-if="form.activity_status != 1 || (form.activity_status == 1 && form.full_price.length > 0)" label="优惠方式">
                                    <div class="rlue-list" v-if="form.full_price.length > 0">
                                        <div v-for="(item,index) in form.full_price" flex="dir:left cross:center">
                                            <div class="item-input" flex="dir:left cross:center">
                                                <span style="margin: 0 8px;">满</span>
                                                <el-input :disabled="form.activity_status == 1" :min="0" style="width:148px" type="number" v-model="item.full_price"></el-input>
                                                <span style="margin: 0 8px;">元，减</span>
                                                <el-input :disabled="form.activity_status == 1" :max="item.full_price" :min="0" style="width:148px" type="number" v-model="item.reduce_price"></el-input>
                                                <span style="margin: 0 8px;">元</span>
                                            </div>
                                            <div>
                                                <el-button v-if="form.activity_status != 1" type="text" size="mini" circle style="margin: 0 40px;" @click.native="deleteRlue(index)">
                                                    <el-tooltip effect="dark" content="删除" placement="top">
                                                        <img src="statics/img/mall/del.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                                <div v-else class="place-button"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.full_price.length < 3">
                                        <el-button v-if="form.activity_status != 1" type="primary" class="save" style="margin-top: -10px" @click="addRlue">新增一条优惠</el-button>
                                    </div>
                                </el-form-item>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="商品信息" name="first">
                        <div class="item">
                            <div class="item-title">商品信息</div>
                            <div class="item-body">
                                <el-form-item label="选择商品" required>
                                    <div style="margin-bottom: 15px;">
                                        <el-button @click="getGood">选择商品</el-button>
                                        <el-button v-if="activityGoods.length == 0" @click="findOther">导入其他活动商品</el-button>
                                        <p class="add-goods" v-if="activityGoods.length === 0" @click="routeGo('mall/goods/edit')">商城还未添加商品？点击前往</p>
                                    </div>
                                    <div style="width: 960px">
                                        <el-table
                                                v-if="activityGoods.length > 0"
                                                :data="activityGoods"
                                                border
                                                :header-cell-style="{'background':'#F6F7FA'}"
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
                                                                <app-ellipsis :line="1">{{scope.row.goodsWarehouse.name}}</app-ellipsis>
                                                            </el-tooltip>
                                                            <div v-if="scope.row.use_attr == 1" style="color: #999;font-size: 12px">已选{{scope.row.attr.length}}个规格</div>
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
                                                        <el-input style="min-width: 70px" type="number" min="0" size="mini" class="change"
                                                                  v-model="sort"
                                                                  autocomplete="off"></el-input>
                                                        <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                                                   icon="el-icon-error"
                                                                   circle @click="sort_goods_id = 0"></el-button>
                                                        <el-button class="change-success" type="text"
                                                                   style="margin-left: 0;color: #67C23A;padding: 0"
                                                                   icon="el-icon-success" circle @click="changeSortSubmit(scope.row)">
                                                        </el-button>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    prop="name"
                                                    width="150"
                                                    label="买家购买价格">
                                                <template slot-scope="scope">
                                                    <div flex="dir:left cross:center">
                                                        <div flex="dir:left cross:center" v-if="scope.row.use_attr == 1">
                                                            <span>{{scope.row.price_section.min_price}}~{{scope.row.price_section.max_price}}</span>
                                                            <el-button v-if="scope.row.status == 0 || form.activity_status != 1" class="edit-sort" type="text" @click="editGoods(scope.row,'price')">
                                                                <img src="statics/img/mall/order/edit.png" alt="">
                                                            </el-button>
                                                        </div>
                                                        <div v-else>
                                                            <div flex="dir:left cross:center" v-if="price_goods_id != scope.row.id">
                                                                <span>{{scope.row.price}}</span>
                                                                <el-button v-if="scope.row.status == 0 || form.activity_status != 1" class="edit-sort" type="text" @click="editPrice(scope.row)">
                                                                    <img src="statics/img/mall/order/edit.png" alt="">
                                                                </el-button>
                                                            </div>
                                                            <div style="display: flex;align-items: center" v-else>
                                                                <el-input style="min-width: 70px" type="number" min="0" size="mini" class="change" v-model="price" autocomplete="off"></el-input>
                                                                <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                                                           icon="el-icon-error"
                                                                           circle @click="price_goods_id = 0"></el-button>
                                                                <el-button class="change-success" type="text"
                                                                           style="margin-left: 0;color: #67C23A;padding: 0"
                                                                           icon="el-icon-success" circle @click="changePriceSubmit(scope.row)">
                                                                </el-button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    prop="name"
                                                    width="150"
                                                    label="团长供货价格">
                                                <template slot-scope="scope">
                                                    <div flex="dir:left cross:center">
                                                        <div flex="dir:left cross:center" v-if="scope.row.use_attr == 1">
                                                            <span>{{scope.row.supply_price_section.min_price}}~{{scope.row.supply_price_section.max_price}}</span>
                                                            <el-button v-if="scope.row.status == 0 || form.activity_status != 1" class="edit-sort" type="text" @click="editGoods(scope.row,'price')">
                                                                <img src="statics/img/mall/order/edit.png" alt="">
                                                            </el-button>
                                                        </div>
                                                        <div v-else>
                                                            <div flex="dir:left cross:center" v-if="supply_goods_id != scope.row.id">
                                                                <span>{{scope.row.supply_price}}</span>
                                                                <el-button v-if="scope.row.status == 0 || form.activity_status != 1" class="edit-sort" type="text" @click="editSupply(scope.row)">
                                                                    <img src="statics/img/mall/order/edit.png" alt="">
                                                                </el-button>
                                                            </div>
                                                            <div style="display: flex;align-items: center" v-else>
                                                                <el-input style="min-width: 70px" type="number" min="0" size="mini" class="change" v-model="supply_price" autocomplete="off"></el-input>
                                                                <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                                                           icon="el-icon-error"
                                                                           circle @click="supply_goods_id = 0"></el-button>
                                                                <el-button class="change-success" type="text"
                                                                           style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                                                           icon="el-icon-success" circle @click="changeSupplySubmit(scope.row)">
                                                                </el-button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    prop="name"
                                                    width="80"
                                                    label="是否上架">
                                                <template slot-scope="scope">
                                                    <el-switch @change="switchStatus(scope.$index)" v-model="scope.row.status" :active-value="1"
                                                 :inactive-value="0"></el-switch>
                                                </template>
                                            </el-table-column>
                                            <el-table-column
                                                    prop="name"
                                                    width="120"
                                                    label="操作">
                                                <template slot-scope="scope">
                                                    <div flex="dir:left cross:center">
                                                        <el-button v-if="scope.row.status == 0 || form.activity_status != 1" class="edit-sort" type="text" @click="editGoods(scope.row)">
                                                            <el-tooltip  effect="dark" content="编辑商品" placement="top">
                                                                <img src="statics/img/mall/edit.png" alt="">
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
                                                        layout="prev, pager, next"
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
        <el-button :loading="saveLoading" type="primary" size="small" class="save"  @click="saveEdit">保存</el-button>
    </el-card>
    <el-dialog class="dialog-choose" title="选择其他团购活动" :visible.sync="listDialog" width="30%">
        <div class="dialog-goods-list">
            <div style="margin-bottom: 27px;">
                <el-input size="small" v-model="activityKeyword"  @change="searchActivity" @clear="searchActivity" placeholder="根据名称搜索" clearable autocomplete="off">
                    <template slot="append">
                        <el-button @click="searchActivity">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <el-table v-loading="listLoading" :data="list" style="width: 100%">
                <el-table-column align="center" width="60px" props="id">
                    <template slot-scope="props">
                        <el-radio-group v-model="radioSelection" @change="handleSelectionChange(props.row)">
                            <el-radio :label="props.row.id"></el-radio>
                        </el-radio-group>
                    </template>
                </el-table-column>
                <el-table-column label="名称">
                    <template slot-scope="props">
                        <div flex="dir:left cross:center">
                            <app-ellipsis :line="2">{{props.row.title}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    v-if="pagination.page_count > 0"
                    style="display: flex;justify-content: center;"
                    @current-change="changePage"
                    background
                    :current-page="pagination.current_page"
                    layout="prev, pager, next"
                    :page-count="pagination.page_count">
            </el-pagination>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="listDialog = false">取 消</el-button>
            <el-button size="small" type="primary" :loading="saveLoading" @click="submitActivity">确 定</el-button>
        </span>
    </el-dialog>
</div>

<script>
    const app = new Vue({
        el: '#app',

        data() {
            return {
                activeName: 'first',
                listLoading: false,
                goodsLoading: false,
                goodsVisible: false,
                dialogVisible: false,
                listDialog: false,
                multiple: false,
                goodsList: [],
                area_limit: [],
                people_num: '',
                goods_num: '',
                form: {
                    title: '',
                    is_area_limit: 0,
                    area_limit: [],
                    condition: 0,
                    full_price: []
                },
                list: [],
                chooseActivity: [],
                radioSelection: 0,
                pagination: {
                    page_count: 0
                },
                current_page: 1,
                page_count: 1,
                keyword: '',
                activityKeyword: '',
                page: 1,
                selectionList: [],
                activityGoods: [],
                sort_goods_id: 0,
                price_goods_id: 0,
                supply_goods_id: 0,
                stockVisible: false,
                stockList: [],
                copyStockList: [],
                attrList: [],
                start_at: '',
                end_at: '',
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
                    page_count: 0,
                    page: 1
                },
                tab: 0,
                sort: 0,
                price: 0,
                supply_price: 0,
                tab_list_loading: false
            };
        },

        methods: {
            submitActivity() {
                this.saveLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/import-goods',
                        activity_id: this.activity_id,
                        selected_activity_id: this.chooseActivity[0].id,
                    },
                    method: 'get',
                }).then(e => {
                    this.saveLoading = false;
                    this.listDialog = false;
                    if (e.data.code == 0) {
                        this.$message({
                          message: e.data.msg,
                          type: 'success'
                        });
                        this.getOldGoodsList(this.activity_id);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            handleSelectionChange(val) {
                this.chooseActivity = [val];
            },
            findOther() {
                this.listDialog = true;
                this.searchActivity();
            },
            changePage(page) {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/index',
                        keyword: this.activityKeyword,
                        keyword_label: 'title',
                        page: page,
                    },
                    method: 'get',
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            searchActivity() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/index',
                        keyword: this.activityKeyword,
                        keyword_label: 'title',
                        page: 1,
                        scene: 'import',
                    },
                    method: 'get',
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            deleteRlue(index) {
                this.form.full_price.splice(index,1)
            },
            openDistrict(index) {
                this.area_limit = JSON.parse(JSON.stringify(this.form.area_limit));
                this.dialogVisible = true;
            },
            districtConfirm(e) {
                if(this.area_limit.length == 0) {
                    this.$message({
                        type: 'warning',
                        message: '请选择地区'
                    });
                    return false;
                }
                this.form.area_limit = JSON.parse(JSON.stringify(this.area_limit));
                this.area_limit = [];
                this.dialogVisible = false;
            },
            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.area_limit = list;
            },
            addRlue() {
                let para = {full_price:'',reduce_price:''}
                this.form.full_price.push(para)
            },
            getGood() {
                this.goodsVisible = true;
                this.selectGood();
            },
            searchGood() {
                this.page = 1;
                this.selectGood();
            },
            // 选择商品
            async selectGood() {
                this.goodsLoading = true;
                const e = await request({
                    params: {
                        r: '/plugin/community/mall/activity/mall-goods',
                        page: this.page,
                        keyword: this.keyword
                    },
                    method: 'get'
                });
                if (e.data.code === 0) {
                    this.goodsLoading = false;
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
            changePagination(e) {
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
            editPrice(row) {
                this.price_goods_id = row.id;
                this.price = row.price;
            },
            editSupply(row) {
                this.supply_goods_id = row.id;
                this.supply_price = row.supply_price;
            },
            changeSortSubmit(row) {
                if (this.sort < 0) {
                    this.$message.warning('排序值不能小于0');
                } else {
                    row.sort = this.sort;
                    this.sort_goods_id = 0;
                    let data = JSON.parse(JSON.stringify(row));
                    data.id = data.communityGoods.id;
                    this.editSaveGoods([data], 'edit');
                }
            },
            changePriceSubmit(row) {
                if (this.price < 0) {
                    this.$message.warning('买家购买价格不能小于0');
                } else if(!this.price) {
                    this.$message.warning('买家购买价需要是一个数字');
                } else if(this.price < +row.supply_price) {
                    this.$message.warning('买家购买价不能小于团长供货价');
                }  else {
                    row.price = (+this.price).toFixed(2);
                    this.price_goods_id = 0;
                    let data = JSON.parse(JSON.stringify(row));
                    data.id = data.communityGoods.id;
                    this.editSaveGoods([data], 'edit');
                }
            },
            changeSupplySubmit(row) {
                if (this.supply_price < 0) {
                    this.$message.warning('团长供货价不能小于0');
                } else if(!this.supply_price) {
                    this.$message.warning('团长供货价需要是一个数字');
                } else if(this.supply_price > +row.price) {
                    this.$message.warning('团长供货价不能大于买家购买价');
                } else {
                    row.supply_price = (+this.supply_price).toFixed(2);
                    this.supply_goods_id = 0;
                    let data = JSON.parse(JSON.stringify(row));
                    data.id = data.communityGoods.id;
                    this.editSaveGoods([data], 'edit');
                }
            },
            switchStatus(index) {
                let data = this.activityGoods[index];
                data.id = data.communityGoods.id;
                this.editSaveGoods([data], 'edit');
            },

            async editSaveGoods(community, type) {
                const e = await request({
                    params: {
                        r: `/plugin/community/mall/activity/edit-goods`,
                    },
                    method: 'post',
                    data: {
                        type: type,
                        activity_id: this.activity_id,
                        community: JSON.stringify(community)
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
                let that = this;
                that.$confirm('此操作将删除该活动商品，是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    that.tab_list_loading = true;
                    that.editSaveGoods([{id: row.communityGoods.id}], 'del').then(() => {
                        console.log(1)
                        that.$delete(that.activityGoods, index);
                        that.getOldGoodsList(that.activity_id).then(e => {
                            that.tab_list_loading = false;
                        });
                    });
                }).catch(() => {
                        console.log(2)
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
                data.id = data.communityGoods.id;
                this.editSaveGoods([data], 'edit');
            },

            async saveEdit() {
                if(this.activeName == "first" && this.activityGoods.length == 0) {
                    this.$message({
                        type: 'warning',
                        message: '请添加商品'
                    });
                    return false
                }
                for(let item of this.form.full_price) {
                    if(item.full_price  && item.reduce_price && +item.full_price > -0.1 && +item.reduce_price > -0.1) {
                        if(+item.full_price < +item.reduce_price) {
                            this.$message({
                                type: 'warning',
                                message: '优惠金额大于门槛金额'
                            });
                            return false;
                        }
                    }else {
                        this.$message({
                            type: 'warning',
                            message: '优惠方式设置有误'
                        });
                        return false
                    }
                }
                this.saveLoading = true;
                if (this.picker_time) {
                    this.start_at = this.picker_time[0];
                    this.end_at = this.picker_time[1];
                } else {
                    this.start_at = '';
                    this.end_at = '';
                }
                if(this.form.condition == 1) {
                    this.form.num = this.people_num;
                }else if(this.form.condition == 2) {
                    this.form.num = this.goods_num;
                }
                const e = await request({
                    params: {
                        r: `/plugin/community/mall/activity/edit`
                    },
                    method: 'post',
                    data: {
                        title: this.form.title,
                        start_at: this.start_at,
                        end_at: this.end_at,
                        is_area_limit: this.form.is_area_limit,
                        area_limit: JSON.stringify(this.form.area_limit),
                        condition: this.form.condition,
                        num: this.form.num,
                        full_price: JSON.stringify(this.form.full_price),
                        id: this.activity_id
                    }
                });
                this.saveLoading = false;
                localStorage.removeItem("community");
                if (e.data.code === 0) {
                    if(this.activity_id > 0) {
                        this.$message({
                            type: 'success',
                            message: e.data.msg
                        });
                        this.$navigate({
                            r: 'plugin/community/mall/activity/index'
                        });
                    }else {
                        this.activity_id = e.data.activity_id;
                        this.activeName = 'first';
                    }
                } else if (e.data.code === 1) {
                    this.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }
            },

            async getDetail(id) {
                this.listLoading = true;
                const e = await request({
                    params: {
                        r: `/plugin/community/mall/activity/edit`,
                        id: id
                    }
                });
                if (e.data.code === 0) {
                    this.listLoading = false;
                    this.form = e.data.data.detail;
                    this.start_at = e.data.data.detail.start_at;
                    this.end_at = e.data.data.detail.end_at;
                    if(this.form.condition == 1) {
                        this.people_num = this.form.num;
                    }else if(this.form.condition == 2) {
                        this.goods_num = this.form.num;
                    }
                    this.area_limit = JSON.parse(JSON.stringify(this.form.area_limit));
                    this.picker_time = [this.start_at, this.end_at];
                }else {
                    this.listLoading = false;
                }
            },

            routeGo(r) {
                if(r == 'mall/goods/edit') {
                    localStorage.setItem("community",this.activity_id);
                }
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
                    if (sign === 'community') {
                        return false;
                    } else {
                        return true;
                    }
                }
            },

            editGoods(row,other) {
                localStorage.setItem("community",this.activity_id);
                this.$navigate({
                    r: 'plugin/community/mall/activity/edit-activity-goods',
                    id: row.communityGoods.goods_id,
                    activity: this.activity_id,
                    other: other,
                    page: 1
                });
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
                        r: '/plugin/community/mall/activity/goods',
                        id: activity_id,
                        limit: 4,
                        sort_type: 3,
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
            }else {
                this.activity_id = localStorage.getItem("community");
                if(this.activity_id > 0) {
                    this.getDetail(this.activity_id);
                    this.getOldGoodsList(this.activity_id);
                }
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
