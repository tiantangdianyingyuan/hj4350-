<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>

<style>
    .form-body {
        padding: 20px;
        padding-right: 30%;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
    }

    .goods-list {
        flex-wrap: wrap;
        margin-top: 10px;
        cursor: move;
    }

    .goods-item,
    .goods-add {
        width: 50px;
        height: 50px;
        position: relative;
        border: 1px solid #e2e2e2;
        margin-right: 15px;
        margin-bottom: 15px;
    }

    .goods-add .el-button {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 0;
        padding: 0;
    }

    .goods-delete {
        position: absolute;
        left: calc(100% - 13px);
        top: -13px;
        width: 25px;
        height: 25px;
        line-height: 25px;
        padding: 0 0;
        visibility: hidden;
        z-index: 1;
    }

    .goods-delete .el-icon-close {
        position: absolute;
        top: 6px;
        left: 6px;
    }

    .goods-item:hover .goods-delete {
        visibility: visible;
    }

    .goods-pic {
        background-size: cover;
        background-position: center;
        width: 100%;
        height: 100%;
        background-color: #f6f6f6;
        background-repeat: no-repeat;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

</style>

<div id="app" v-cloak>
    <el-card v-loading="loading" v-if="is_show" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;" shadow="never">
        <div slot="header">
            推荐设置
        </div>
        <el-form @submit.native.prevent ref="form" :model="form" label-width="150px" size="small">
            <el-card>
                <div slot="header">
                    商品详情页推荐设置
                </div>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="推荐商品状态">
                            <el-switch :active-value="1" :inactive-value="0"
                                       v-model="form.goods.is_recommend_status"></el-switch>
                        </el-form-item>
                        <el-form-item label="推荐商品显示数量">
                            <el-input v-model="form.goods.goods_num">
                                <template slot="append">个</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>
            <el-card style="margin-top: 10px;" shadow="never">
                <div slot="header">
                    订单完成后推荐设置
                </div>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="推荐商品状态">
                            <el-switch :active-value="1" :inactive-value="0"
                                       v-model="form.order_pay.is_recommend_status"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="form.order_pay.is_recommend_status" label="自定义推荐商品">
                            <el-switch :active-value="1" :inactive-value="0"
                                       v-model="form.order_pay.is_custom"></el-switch>
                            <span>{{form.order_pay.is_custom ? "最多添加20件商品" : "按商品列表排序显示前10件商品"}}</span>
                            <div v-if="form.order_pay.is_custom">
                                <draggable v-model="form.order_pay.goods_list" flex class="goods-list">
                                    <div class="goods-item" v-for="(goods,goodsIndex) in form.order_pay.goods_list">
                                        <el-tooltip effect="dark" content="移除商品" placement="top">
                                            <el-button @click="deleteGoods(goodsIndex, 'order_pay')" circle
                                                       class="goods-delete" type="danger"
                                                       icon="el-icon-close"></el-button>
                                        </el-tooltip>
                                        <div class="goods-pic"
                                             :style="'background-image:url('+goods.cover_pic+')'"></div>
                                    </div>
                                </draggable>
                                <div v-if="form.order_pay.goods_list.length < goodsDialog.max" class="goods-add">
                                    <el-tooltip effect="dark" content="添加商品" placement="top">
                                        <el-button @click="showGoodsDialog('order_pay')"
                                                   icon="el-icon-plus"></el-button>
                                    </el-tooltip>
                                </div>
                            </div>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>
            <el-card style="margin-top: 10px;" shadow="never">
                <div slot="header">
                    评论后推荐设置
                </div>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="推荐商品状态">
                            <el-switch :active-value="1" :inactive-value="0"
                                       v-model="form.order_comment.is_recommend_status"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="form.order_comment.is_recommend_status" label="自定义推荐商品">
                            <el-switch :active-value="1" :inactive-value="0"
                                       v-model="form.order_comment.is_custom"></el-switch>
                            <span>{{form.order_comment.is_custom ? "最多添加20件商品" : "按商品列表排序显示前10件商品"}}</span>
                            <div v-if="form.order_comment.is_custom">
                                <draggable v-model="form.order_comment.goods_list" flex class="goods-list">
                                    <div class="goods-item"
                                         v-for="(goods,goodsIndex) in form.order_comment.goods_list">
                                        <el-tooltip effect="dark" content="移除商品" placement="top">
                                            <el-button @click="deleteGoods(goodsIndex, 'order_comment')" circle
                                                       class="goods-delete" type="danger"
                                                       icon="el-icon-close"></el-button>
                                        </el-tooltip>
                                        <div class="goods-pic"
                                             :style="'background-image:url('+goods.cover_pic+')'"></div>
                                    </div>
                                </draggable>
                                <div v-if="form.order_comment.goods_list.length < goodsDialog.max" class="goods-add">
                                    <el-tooltip effect="dark" content="添加商品" placement="top">
                                        <el-button @click="showGoodsDialog('order_comment')"
                                                   icon="el-icon-plus"></el-button>
                                    </el-tooltip>
                                </div>
                            </div>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>
            <el-card style="margin-top: 10px;" shadow="never">
                <el-button :loading="btnLoading" type="primary" size="small" @click="store">保存</el-button>
            </el-card>
        </el-form>
    </el-card>

    <el-dialog @open="getGoods" title="选择商品" :visible.sync="goodsDialog.visible" :close-on-click-modal="false">
        <el-form size="small" :inline="true" :model="search" @submit.native.prevent>
            <el-form-item>
                <div class="input-item">
                    <el-input @clear="toSearch" clearable @keyup.enter.native="toSearch" size="small"
                              placeholder="请输入商品ID/名称搜索"
                              v-model="search.keyword">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </el-form-item>
        </el-form>
        <el-table :data="goodsDialog.list" v-loading="goodsDialog.loading" @selection-change="goodsSelectionChange">
            <el-table-column label="选择" type="selection"></el-table-column>
            <el-table-column label="ID" prop="id" width="100px"></el-table-column>
            <el-table-column label="名称" prop="name"></el-table-column>
        </el-table>
        <div style="text-align: center; margin-top: 15px;">
            <el-pagination
                    v-if="goodsDialog.pagination"
                    style="display: inline-block"
                    background
                    @current-change="goodsDialogPageChange"
                    layout="prev, pager, next, jumper"
                    :page-size.sync="goodsDialog.pagination.pageSize"
                    :total="goodsDialog.pagination.totalCount">
            </el-pagination>
        </div>
        <div slot="footer">
            <el-button @click="goodsDialog.visible = false">取 消</el-button>
            <el-button type="primary" @click="addGoods">确 定</el-button>
        </div>

    </el-dialog>
</div>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    goods: {},
                    order_pay: {
                        goods_list: []
                    },
                    order_comment: {
                        goods_list: []
                    },
                    fxhb: {
                        goods_list: []
                    },
                },
                goodsDialog: {
                    visible: false,
                    page: 1,
                    loading: null,
                    pagination: null,
                    list: null,
                    index: null,
                    selectedList: null,
                    max: 20,//添加商品最大数量
                },
                loading: false,
                is_show: false,
                btnLoading: false,
                search: {
                    keyword: '',
                }
            }
        },
        created() {
            this.getSetting();
        },
        methods: {
            showGoodsDialog(index) {
                this.goodsDialog.visible = true;
                this.goodsDialog.index = index;
            },
            goodsSelectionChange(e) {
                this.goodsDialog.selectedList = e;
            },
            addGoods() {
                this.goodsDialog.visible = false;
                for (let i in this.goodsDialog.selectedList) {
                    const item = {
                        id: this.goodsDialog.selectedList[i].id,
                        name: this.goodsDialog.selectedList[i].name,
                        cover_pic: this.goodsDialog.selectedList[i].cover_pic,
                        price: this.goodsDialog.selectedList[i].price,
                    };
                    if (this.form[this.goodsDialog.index].goods_list.length < this.goodsDialog.max) {
                        this.form[this.goodsDialog.index].goods_list.push(item);
                    }
                }
            },
            deleteGoods(goodsIndex, index) {
                this.form[index].goods_list.splice(goodsIndex, 1);
            },
            getGoods() {
                let self = this;
                self.goodsDialog.loading = true;
                request({
                    params: {
                        r: 'mall/goods/recommend-goods',
                        page: self.goodsDialog.page,
                        search: JSON.stringify(self.search),
                    },
                    method: 'get',
                }).then(e => {
                    self.goodsDialog.loading = false;
                    self.goodsDialog.list = e.data.data.list;
                    self.goodsDialog.pagination = e.data.data.pagination;
                }).catch(e => {
                    console.log(e);
                });
            },
            getSetting() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'mall/goods/recommend-setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    self.is_show = true;
                    self.form = e.data.data.setting;
                }).catch(e => {
                    console.log(e);
                });
            },
            store() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/goods/recommend-setting',
                    },
                    method: 'post',
                    data: {
                        form: JSON.stringify(self.form)
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg)
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            goodsDialogPageChange(page) {
                this.goodsDialog.page = page;
                this.getGoods();
            },
            toSearch() {
                this.getGoods();
            }
        }

    });
</script>
