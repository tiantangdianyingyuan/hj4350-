<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/6
 * Time: 9:35
 */
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .table-body {
        background-color: #fff;
        margin-bottom: 20px;
    }
    .el-card {
        background-color: #f3f3f3;
    }
    .el-card .el-card__header {
        background-color: #ffffff;
    }
    .table-body .table-item-header {
        height: 60px;
        line-height: 60px;
        padding-left: 22px;
        border-bottom: 1px solid #ededef;
    }
    .table-body .table-item {
        padding: 20px;
    }
    .table-item .name>.el-form-item__content {
        width: 400px;
    }
    .table-item .name .el-date-editor {
        width: 400px;
    }
    .flight-item {
        width: 400px;
        height: 203px;
        border: 1px solid #ebeef5;
        margin-left: 20px;
        position: relative;
        border-radius: 2px;
        margin-bottom: 10px;
    }
    .flight-item .item-header {
        border-bottom: 1px solid  #ebeef5;
        height: 40px;
        padding-left: 20px;
        line-height: 40px;
    }
    .flight-item .item-body {
        padding-right: 50px;
        padding-top: 15px;
    }
    .flight-item .item-body .less .el-input{
        width: 166px;
    }
    .flight-item .item-body .el-delete {
        position: absolute;
        right: -35px;
        top: 1px;

    }
    .flight-item .item-body .el-delete .el-button {
        border-radius: 0;
        padding: 10px;
    }
    .table-item .goods .item{
        margin-bottom: 15px;
    }
    .goods-table {
        width: 830px;
        border-top: 1px solid #ebeef5;
        border-left: 1px solid #ebeef5;
        border-right: 1px solid #ebeef5;
        margin-top: 25px;
    }

    .rule-btn {
        border: 1px solid #409EFF;
        color: #409EFF
    }
    .cover_pic img {
        width: 50px;
        height: 50px;
    }
    .content {
        padding-left: 14px;
        padding-top: 5px;
    }
    .content .name {
        color: #666666;
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow:ellipsis;
        width: 425px;
    }
    .content >div {
        font-size: 13px;
    }
</style>

<div id="app" v-cloak>
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
                    :selectable='Disabled'
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
    <el-card shadow="never" class="el-card" v-loading="loading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/full-reduce/index'})">满减列表</span></el-breadcrumb-item>
                <el-breadcrumb-item>{{form.id ? '编辑满减' : '添加满减'}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form size="small"  label-width="100px" :model="form" :rules="rules" >
            <div class="table-body">
                <div class="table-item-header">
                    活动设置
                </div>
                <div class="table-item" >
                    <el-form-item prop="name" class="name">
                        <span slot="label" style="color:#606266">活动名称</span>
                        <el-input v-model="form.name" :disabled="editBool">
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="start_at" class="name">
                        <span slot="label" style="color:#606266">开始时间</span>
                        <el-date-picker
                                v-model="form.start_at"
                                type="datetime"
                                :disabled="editBool"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                placeholder="选择日期时间">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item prop="end_at" class="name">
                        <span slot="label" style="color:#606266">结束时间</span>
                        <el-date-picker
                                v-model="form.end_at"
                                type="datetime"
                                :disabled="editBool"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                placeholder="选择日期时间">
                        </el-date-picker>
                    </el-form-item>
                </div>
            </div>
            <div class="table-body">
                <div class="table-item-header">
                    商品设置
                </div>
                <div class="table-item">
                    <el-form-item prop="name" class="goods">
                        <span slot="label" style="color:#606266">满减商品</span>
                        <div class="item">
                            <el-radio :disabled="editBool" v-model="form.appoint_type" :label="1">全部商品（全部自营商品+全部多商户商品）</el-radio>
                        </div>
                        <div class="item">
                            <el-radio :disabled="editBool" v-model="form.appoint_type" :label="2">全部自营商品（不包含多商户商品）</el-radio>
                        </div>
                        <div class="item">
                            <el-radio :disabled="editBool" v-model="form.appoint_type" :label="3">指定商品参与（可选择多商户商品）
                                <el-button :disabled="form.appoint_type !== 3" @click="chooseGoods">选择商品</el-button>
                            </el-radio>
                            <div class="goods-table" v-if="form.appoint_type === 3">
                                <el-table
                                        :data="appoint_goods"
                                        @selection-change="handleSelectionChange"
                                >
                                    <el-table-column
                                            type="selection"
                                            width="55">
                                    </el-table-column>
                                    <el-table-column
                                            prop="date"
                                            label="商品"
                                            width="500"
                                    >
                                        <template slot-scope="scope">
                                            <div flex="">
                                                <div class="cover_pic">
                                                    <image :src="scope.row.cover_pic"></image>
                                                </div>
                                                <div class="content">
                                                    <div class="name">{{scope.row.name}}</div>
                                                    <div>
                                                        <span style="color: #ff4040;">￥{{scope.row.price}}</span>
                                                        <span style="margin-left: 80px;">库存：{{scope.row.goods_stock}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="shop_name" label="店铺名称" width="180"></el-table-column>
                                    <el-table-column
                                            prop="name"
                                            label="操作"
                                    >
                                        <template slot-scope="scope">
                                            <el-button class="set-el-button" size="mini" type="text" circle
                                                       @click="handleDel(scope.row)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div v-if="form.appoint_type === 3" style="width: 830px;margin-top: 20px;" flex="main:justify">
                                <el-button type="primary" size="small" @click="deleteNoappoint">批量删除</el-button>
                                <div flex="">
                                    <span style="color: #616161;margin-right: 20px;">已选{{form.appoint_goods.length}}</span>
                                    <el-pagination
                                        @current-change="appointPagination"
                                        background
                                        :current-page="appoint.current_page"
                                        layout="prev, pager, next"
                                        :page-count="appoint.page_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <el-radio :disabled="editBool" v-model="form.appoint_type" :label="4">指定商品不参与（可选择多商户商品）
                                <el-button :disabled="form.appoint_type !== 4" @click="chooseGoods">选择商品</el-button>
                            </el-radio>
                            <div class="goods-table" v-if="form.appoint_type === 4">
                                <el-table
                                    :data="noappoint_goods"
                                    @selection-change="handleSelectionChange"
                                >
                                    <el-table-column
                                            type="selection"
                                            width="55">
                                    </el-table-column>
                                    <el-table-column
                                            prop="date"
                                            label="商品"
                                            width="500"
                                            >
                                        <template slot-scope="scope">
                                            <div flex="">
                                                <div class="cover_pic">
                                                    <image :src="scope.row.cover_pic"></image>
                                                </div>
                                                <div class="content">
                                                    <div class="name">{{scope.row.name}}</div>
                                                    <div>
                                                        <span style="color: #ff4040;">￥{{scope.row.price}}</span>
                                                        <span style="margin-left: 80px;">库存：{{scope.row.goods_stock}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="shop_name" label="店铺名称" width="180"></el-table-column>
                                    <el-table-column
                                            prop="name"
                                            label="操作"
                                           >
                                        <template slot-scope="scope">
                                            <el-button class="set-el-button" size="mini" type="text" circle
                                                       @click="handleDel(scope.row)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div v-if="form.appoint_type === 4" style="width: 830px;margin-top: 20px;" flex="main:justify">
                                <el-button type="primary" size="small" @click="deleteNoappoint">批量删除</el-button>
                                <div flex="">
                                    <span style="color: #616161;margin-right: 20px;">已选{{form.noappoint_goods.length}}</span>
                                    <el-pagination
                                            @current-change="noappointPagination"
                                            background
                                            :current-page="noappoint.current_page"
                                            layout="prev, pager, next"
                                            :page-count="noappoint.page_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-form-item>
                </div>
            </div>
            <div class="table-body">
                <div class="table-item-header">
                    满减规则
                </div>
                <div class="table-item">
                    <el-form-item prop="name" class="name">
                        <span slot="label" style="color:#606266">规则设置</span>
                        <span  style="font-size: 13px;color: #999999;">提示：阶梯优惠中满足二级优惠，不再享受一级优惠</span>
                    </el-form-item>
                        <div >
                           <el-form-item>
                               <span slot="label" style="color:#606266">满减方式</span>
                               <el-radio :disabled="editBool" v-model="form.rule_type" :label="1">阶梯满减</el-radio>
                               <el-radio :disabled="editBool" v-model="form.rule_type" :label="2">循环满减</el-radio>
                               <div v-if="form.rule_type === 1">
                                   <el-radio
                                       style="margin: 20px 0;"
                                       v-model="discount_type"
                                       label="2"
                                       :disabled="editBool"
                                   >打折</el-radio>
                                   <el-radio
                                       style="margin: 20px 0 20px 20px;"
                                       v-model="discount_type"
                                       label="1"
                                       :disabled="editBool"
                                   >减钱</el-radio>
                               </div>
                               <div class="flight-item" v-if="form.rule_type === 1" v-for="(item, index) in form.discount_rule">
                                   <div class="item-header">
                                       {{index === 0 ? '一' : index === 1 ? '二' : index === 2 ? '三' : index === 3 ? '四' : '五'}}级优惠
                                   </div>
                                   <div class="item-body">
                                       <el-form-item label="优惠门槛" required>
                                           <div flex="main:left" style="width: 100%">
                                               <span style="margin-right: 10px;" :style="{color: editBool ? '#C0C4CC' : 'rgb(96, 98, 102)'}">满</span>
                                               <div style="width: 100%" >
                                                   <el-input :disabled="editBool" v-model="item.min_money" type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');">
                                                   </el-input>
                                               </div>
                                               <span style="margin-left: 10px;" :style="{color: editBool ? '#C0C4CC' : 'rgb(96, 98, 102)'}">元</span>
                                           </div>
                                       </el-form-item>
                                       <el-form-item label="优惠内容" required>
                                           <div class="less">
                                               <div style="width: 100%" >
                                                    <template v-if="discount_type == 1">
                                                        <span style="margin-right: 10px;">减</span>
                                                        <el-input  :disabled="editBool " type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" v-model="item.cut">
                                                        </el-input>
                                                        <span style="margin-left: 10px;">元</span>
                                                    </template>

                                                    <template v-if="discount_type == 2">
                                                        <span style="margin-right: 10px;">打</span>
                                                        <el-input :disabled="editBool" type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3');" v-model="item.discount">
                                                        </el-input>
                                                        <span style="margin-left: 10px;">折</span>
                                                    </template>
                                               </div>
                                           </div>
                                       </el-form-item>
                                       <div class="el-delete" @click="deleteDiscount(index)">
                                           <el-button :disabled="editBool" icon="el-icon-delete" type="primary"></el-button>
                                       </div>
                                   </div>
                               </div>
                               <div style="padding-left: 20px" v-if="form.rule_type === 1">
                                   <div style="font-size: 13px;color: #999999;">最多添加5级阶梯</div>
                                   <el-button class="rule-btn" :disabled="editBool" @click="addDiscount" v-if="form.discount_rule.length < 5">
                                       +添加{{form.discount_rule.length === 0 ? '一' : form.discount_rule.length === 1 ? '二' :
                                       form.discount_rule.length === 2 ? '三' : form.discount_rule.length === 3 ? '四':'五'
                                       }}级优惠
                                   </el-button>
                               </div>
                           </el-form-item>
                        </div>
                        <div style="width: 35%;margin-left: 70px;">
                            <div style="margin-top: 10px;" v-if="form.rule_type === 2">
                                <el-form-item label="优惠门槛" required>
                                    <div flex="main:left" style="width: 100%">
                                        <span style="margin-right: 10px;width: 40px;">每满</span>
                                        <div style="width: 100%" >
                                            <el-input
                                                    :disabled="editBool"
                                                      type="number"
                                                      oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                                                    v-model="form.loop_discount_rule.min_money">
                                            </el-input>
                                        </div>
                                        <span style="margin-left: 10px;">元</span>
                                    </div>
                                </el-form-item>
                                <el-form-item label="优惠内容" required>
                                    <div flex="main:left" style="width: 100%">
                                        <span style="margin-right: 10px;width: 40px;">减</span>
                                        <div style="width: 100%" >
                                            <el-input
                                                    :disabled="editBool"
                                                    type="number"
                                                      oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3');"  v-model="form.loop_discount_rule.cut">
                                            </el-input>
                                        </div>
                                        <span style="margin-left: 10px;">元</span>
                                    </div>
                                </el-form-item>
                            </div>
                        </div>
                    <el-form-item label="活动规则">
                        <div style="width: 458px; min-height: 458px;">
                            <app-rich-text v-model="form.content"></app-rich-text>
                        </div>
                    </el-form-item>
                </div>
            </div>
        </el-form>
    </el-card>
    <el-button  type="primary" style="margin-bottom: 65px;" @click="submit">保存</el-button>

</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                form: {
                    name: '',
                    start_at: '',
                    end_at: '',
                    appoint_type: 1,
                    rule_type: 1,
                    min_money: '',
                    discount: '',
                    discount_rule: [],
                    loop_discount_rule: {},
                    noappoint_goods: [],
                    appoint_goods: [],
                    content: '',
                    id: null
                },
                appoint_goods: [],
                deleteAppointGoods: [],
                appoint: {
                    current_page: 1,
                    page_count: 1
                },
                noappoint_goods: [],
                noappoint: {
                    current_page: 1,
                    page_count: 1
                },
                deleteNoappointGoods: [],
                list: [],
                goodsVisible: false,
                goodsSearch: {
                    keyword: '',
                    current_page: 1,
                    page_count: 1,
                    page: 1
                },
                goodsList: [],
                goodsLoading: false,
                selectionList: [],
                rules: {
                    name: [
                        { required: true, message: '请输入活动名称', trigger: 'blur' },
                        { min: 1, max: 15, message: '长度在 1 到 15 个字', trigger: 'blur' }
                    ],
                    start_at: [
                        { required: true, message: '请输入开始时间', trigger: 'change'},
                    ],
                    end_at: [
                        { required: true, message: '请输入结束时间', trigger: 'change'},
                    ]
                },
                editBool: false,
                discount_type: '1'
            };
        },
        mounted() {
            let id = getQuery('id');
            if (id) {
                this.getDetail(id);
                this.editBool = true;

            }

        },

        watch: {
            'discount_type': {
                handler(data) {
                    console.log(data);
                    this.form.discount_rule.forEach((item) => {
                        item.discount_type = data;
                        if (data === '2') {
                            item.cut = '';
                        } else {
                            item.discount = '';
                        }
                    })
                }
            }
        },
        methods: {

            getGoodsList() {
                request({
                    params: {
                        r: `mall/full-reduce/mall-goods`,
                        keyword: this.goodsSearch.keyword,
                        page: this.goodsSearch.page
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.goodsList = e.data.data.list;
                        this.goodsSearch.page_count = e.data.data.pagination.page_count;
                        this.goodsSearch.current_page = e.data.data.pagination.current_page;
                    }
                })
            },

            addDiscount() {
                this.form.discount_rule.push({
                    discount_type: this.discount_type,
                    discount: '',
                    min_money: '',
                    cut: ''
                })
            },

            deleteDiscount(index) {
                if (this.editBool) return;
                this.$delete(this.form.discount_rule, index);
            },

            submit() {
                this.loading = true;
                let form = this.form;
                let new_noappoint_goods = [];
                for (let i = 0; i < form.noappoint_goods.length; i++) {
                    new_noappoint_goods.push(form.noappoint_goods[i].goods_warehouse_id);
                }
                let new_appoint_goods = [];
                for (let i = 0; i < form.appoint_goods.length; i++) {
                    new_appoint_goods.push(form.appoint_goods[i].goods_warehouse_id);
                }
                let data = JSON.parse(JSON.stringify(this.form));
                console.log(data);
                data.noappoint_goods = new_noappoint_goods;
                data.appoint_goods = new_appoint_goods;
                request({
                    params: {
                        r: `mall/full-reduce/edit`
                    },
                    method: 'post',
                    data: data
                }).then(e => {
                    if (e.data.code === 0) {
                        this.loading = false;
                        this.$navigate({
                            r: 'mall/full-reduce/index'
                        });
                    } else {
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                        this.loading = false;
                    }
                })
            },

            getDetail(id) {
                this.loading = true;
                request({
                    params: {
                        r: `/mall/full-reduce/edit`,
                        id: id
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        Object.assign(this.form, e.data.data.detail);
                        if (this.form.time_status == 0 || this.form.time_status == 1 ) {
                            this.editBool = false;
                        }
                        this.discount_type = this.form.discount_rule[0].discount_type;
                        this.appoint_goods = this.form.appoint_goods.slice(0, 5);
                        this.noappoint_goods = this.form.noappoint_goods.slice(0, 5);
                        this.appoint.page_count = Math.ceil(this.form.appoint_goods.length / 5);
                        this.noappoint.page_count = Math.ceil(this.form.noappoint_goods.length / 5);
                    } else {
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                    }
                })
            },

            selectGood() {
                this.goodsSearch.page = 1;
                this.getGoodsList();
            },

            selectionItem(e) {
                this.selectionList = [];
                for (let i = 0; i < e.length; i++) {
                    this.selectionList.push({
                        name: e[i].goodsWarehouse.name,
                        goods_warehouse_id: e[i].goods_warehouse_id,
                        price: e[i].price,
                        shop_name: e[i].shop_name,
                        goods_stock: e[i].goods_stock,
                        cover_pic: e[i].goodsWarehouse.cover_pic
                    });
                }
            },

            addGoods() {
                if (this.form.appoint_type === 3) {
                    this.form.appoint_goods.push(...this.selectionList);
                    this.appoint_goods = this.form.appoint_goods.slice(0, 5);
                    this.appoint.page_count = Math.ceil(this.form.appoint_goods.length / 5);
                    this.appoint.current_page = 1;
                } else {
                    this.form.noappoint_goods.push(...this.selectionList);
                    this.noappoint_goods = this.form.noappoint_goods.slice(0, 5);
                    this.noappoint.page_count = Math.ceil(this.form.noappoint_goods.length / 5);
                    this.noappoint.current_page = 1;
                }
                this.goodsVisible = false;
            },

            chooseGoods() {
                this.goodsVisible = true;
                this.getGoodsList();
            },

            goodsPagination(e) {
                this.goodsSearch.page = e;
                this.getGoodsList();
            },

            appointPagination(e) {
                this.appoint.current_page = e;
                this.appoint_goods = this.form.appoint_goods.slice((e - 1) * 5, e*5 > this.form.appoint_goods.length ? this.form.appoint_goods.length : e*5);
            },
            noappointPagination(e) {
                this.noappoint.current_page = e;
                this.noappoint_goods = this.form.noappoint_goods.slice((e - 1) * 5, e*5 > this.form.noappoint_goods.length ? this.form.noappoint_goods.length : e*5);
            },
            Disabled(row) {
                let goods = '';
                if (this.form.appoint_type === 3) {
                    goods = 'appoint_goods';
                } else {
                    goods = 'noappoint_goods';
                }
                for (let i = 0; i < this.form[goods].length; i++) {
                    if (this.form[goods][i].goods_warehouse_id === row.goods_warehouse_id) {
                        return false;
                    }
                }
                return true;
            },
            deleteNoappoint() {
                let goods = '';
                let key = '';
                let del = '';
                if (this.form.appoint_type === 3) {
                    goods = 'appoint_goods';
                    key = 'appoint';
                    del = 'deleteAppointGoods';
                } else {
                    goods = 'noappoint_goods';
                    key = 'noappoint';
                    del = 'deleteNoappointGoods';
                }
                for (let i = 0; i < this[del].length; i++) {
                    for (let j = 0; j < this.form[goods].length; j++) {
                        if (this[del][i].goods_warehouse_id === this.form[goods][j].goods_warehouse_id) {
                            this.$delete(this.form[goods], j);
                        }
                    }
                }
                let page = this[key].current_page;

                if (this[goods].length === 0) {
                    this[key].current_page = page - 1 > 1 ? page - 1 : 1;
                }
                this[goods] = this.form[goods].slice((page  - 1) * 5,
                    page *5 > this.form[goods].length ? this.form[goods].length : page *5);
                this[key].page_count = Math.ceil(this.form[goods].length / 5);
                this.deleteNoappointGoods = [];
            },
            handleSelectionChange(e) {
                if (this.form.appoint_type === 4) {
                    this.deleteNoappointGoods = e;
                } else {
                    this.deleteAppointGoods = e;
                }

            },
            handleDel(row) {
                let goods = '';
                let key = '';
                if (this.form.appoint_type === 3) {
                    goods = 'appoint_goods';
                    key = 'appoint';
                } else {
                    goods = 'noappoint_goods';
                    key = 'noappoint';
                }
                let list = this.form[goods];
                for (let i = 0; i < list.length; i++) {
                    if (row.goods_warehouse_id === list[i].goods_warehouse_id) {
                        this.$delete(list, i);
                    }
                }
                let page = this[key].current_page;
                if (this[goods].length === 1) {
                    this[key].current_page = page - 1 > 1 ? page - 1 : 1;
                    page = this[key].current_page;
                }
                this[goods] = this.form[goods].slice((page  - 1) * 5,
                    page *5 > this.form[goods].length ? this.form[goods].length : page *5);
                this[key].page_count = Math.ceil(this.form[goods].length / 5);
            },

            changeDis(item) {
                if (item.discount_type == 2) {
                    item.cut = '';
                } else {
                    item.discount = '';
                }
            }
        }
    });
</script>
