<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/13
 * Time: 14:08
 */
$baseAssetsUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('diy');
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<style>
    .diy-integral-mall .diy-component-edit .goods-list {
        line-height: normal;
        flex-wrap: wrap;
    }

    .diy-integral-mall .diy-component-edit .goods-item,
    .diy-integral-mall .diy-component-edit .goods-add {
        width: 50px;
        height: 50px;
        border: 1px solid #e2e2e2;
        background-position: center;
        background-size: cover;
        margin-right: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .diy-integral-mall .diy-component-edit .goods-add {
        cursor: pointer;
    }

    .diy-integral-mall .diy-component-edit .goods-delete {
        position: absolute;
        top: -11px;
        right: -11px;
        width: 25px;
        height: 25px;
        line-height: 25px;
        padding: 0 0;
        visibility: hidden;
    }

    .diy-integral-mall .diy-component-edit .goods-item:hover .goods-delete {
        visibility: visible;
    }

    .diy-integral-mall .diy-component-edit hr {
        height: 0;
        border-width: 0 0 1px 0;
        border-bottom: 1px solid #e2e2e2;
        /*position: absolute;*/
        /*top: 250px;*/
        /*left: 25px;*/
        width: 95%;
    }

    /*-------------------- 预览部分 --------------------*/
    .diy-integral-mall .diy-component-preview .goods-list {
        flex-wrap: wrap;
        padding: 10px;
    }

    .diy-integral-mall .diy-component-preview .goods-item {
        position: relative;
    }

    .diy-integral-mall .diy-component-preview .goods-tag {
        position: absolute;
        top: 0;
        left: 0;
        width: 64px;
        height: 64px;
        background-position: center;
        background-size: cover;
    }

    .diy-integral-mall .diy-component-preview .goods-pic {
        width: 100%;
        height: 706px;
        background-color: #e2e2e2;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
    }

    .diy-integral-mall .diy-component-preview .goods-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .diy-integral-mall .diy-component-preview .goods-cover-3-2 .goods-pic {
        height: 470px;
    }

    .diy-integral-mall .diy-component-preview .goods-list-2 .goods-pic {
        height: 343px;
    }

    .diy-integral-mall .diy-component-preview .goods-list--1 .goods-pic {
        width: 200px;
        height: 200px;
        margin-right: 20px;
    }

    .diy-integral-mall .diy-component-preview .goods-list--1 .goods-item {
        margin-bottom: 20px;
    }

    .diy-integral-mall .diy-component-preview .goods-list--1 .goods-item:last-child {
        margin-bottom: 0;
    }

    .diy-integral-mall .diy-component-preview .goods-name-static {
        height: 80px;
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        word-break: break-all;
        margin-bottom: 12px;
    }

    .diy-integral-mall .diy-component-preview .goods-price {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #ff4544;
        line-height: 48px;
    }

    .diy-integral-mall .diy-component-preview .coupon-list {
        padding: 10px;
    }

    .diy-integral-mall .diy-component-preview .coupon-item {
        width: 256px;
        height: 130px;
        background-size: cover;
        background-position: center;
        margin: 10px;
    }

    .diy-integral-mall .diy-component-preview .coupon-condition {
        height: 50px;
        line-height: 50px;
        text-align: center;
        font-size: 24px;
    }
</style>
<template id="diy-integral-mall">
    <div class="diy-integral-mall">
        <div class="diy-component-preview" :style="cListStyle">
            <div v-if="data.showCoupon" style="overflow-x: auto;" class="coupon-list" flex="dir:left">
                <div style="display: inline-block" v-for="item in couponList">
                    <div class="coupon-item"
                         :style="'background-image: url('+data.couponPicUrl+'); color: '+data.couponColor+';'"
                         flex="box:last">
                        <div flex="dir:top box:last">
                            <div flex="main:center cross:center" style="margin-top: 15px">
                                <div>
                                    <span style="font-size: 20px;">￥</span><span
                                            style="font-size: 40px;">50.00</span>
                                </div>
                            </div>
                            <div class="coupon-condition">满100可用</div>
                        </div>
                        <div flex="cross:center"
                             style="width: 56px; padding: 0 15px;line-height: 1.15;font-size: 24px;">
                            立即兑换
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="data.showGoods" class="goods-list" :class="'goods-list-'+data.listStyle" :flex="cListFlex">
                <div v-for="item in cList" style="padding: 10px;" :style="cItemStyle">
                    <div class="goods-item"
                         :flex="cGoodsFlex"
                         :class="'goods-cover-'+data.goodsCoverProportion"
                         :style="cGoodsStyle">
                        <div class="goods-pic" :style="cGoodsPicStyle(item.picUrl)"></div>
                        <div v-if="data.showGoodsTag" class="goods-tag"
                             :style="'background-image: url('+data.goodsTagPicUrl+');'"></div>
                        <div :style="cGoodsInfoStyle">
                            <div class="goods-name" :class="data.listStyle===-1?'goods-name-static':''">
                                <template v-if="data.showGoodsName">{{item.name}}</template>
                            </div>
                            <div flex="box:last cross:bottom">
                                <div class="goods-price" :style="cPriceStyle">
                                    <div style="text-overflow: ellipsis;overflow: hidden;">
                                        <template v-if="item.integral > 0">{{item.integral}}积分+￥{{item.price}}</template>
                                    </div>
                                    <div v-if="data.listStyle == -1 && data.isUnderLinePrice" style="color: #909399;text-decoration: line-through;position: absolute;left: 0;bottom: 0;font-size: 24px;">
                                        <span>￥{{item.originalPrice}}</span>
                                    </div>
                                    <div v-else-if="data.isUnderLinePrice" style="color: #909399;text-decoration: line-through;font-size: 24px;line-height: 1">
                                        <span>￥{{item.originalPrice}}</span>
                                    </div>
                                </div>
                                <div v-if="cShowBuyBtn" style="padding: 0 10px;">
                                    <el-button :style="cButtonStyle" type="primary" style="font-size: 24px;">{{data.buyBtnText}}</el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="150px">
                <template>
                    <el-form-item label="显示优惠券">
                        <el-switch v-model="data.showCoupon"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="data.showCoupon" label="优惠券文字颜色">
                        <el-color-picker v-model="data.couponColor"></el-color-picker>
                    </el-form-item>
                    <el-form-item v-if="data.showCoupon" label="优惠券背景图片">
                        <app-image-upload width="320" height="164" v-model="data.couponPicUrl"></app-image-upload>
                    </el-form-item>
                </template>
                <template>
                    <hr>
                    <el-form-item style="margin-top: 12px" label="显示商品">
                        <el-switch v-model="data.showGoods"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="data.showGoods" label="商品列表">
                        <div class="goods-list" flex>
                            <div class="goods-item" v-for="(goods,goodsIndex) in data.list"
                                 :style="'background-image: url('+goods.picUrl+');'">
                                <el-button @click="deleteGoods(goodsIndex)" class="goods-delete"
                                           size="small" circle type="danger"
                                           icon="el-icon-close"></el-button>
                            </div>
                            <div class="goods-add" flex="main:center cross:center"
                                 @click="goodsDialog.visible=true">
                                <i class="el-icon-plus"></i>
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item v-if="data.showGoods" label="购买按钮颜色">
                        <el-color-picker v-model="data.buttonColor"></el-color-picker>
                    </el-form-item>
                    <el-form-item v-if="data.showGoods" label="列表样式">
                        <app-radio v-model="data.listStyle" :label="-1" @change="listStyleChange">列表模式</app-radio>
                        <app-radio v-model="data.listStyle" :label="1" @change="listStyleChange">一行一个</app-radio>
                        <app-radio v-model="data.listStyle" :label="2" @change="listStyleChange">一行两个</app-radio>
                    </el-form-item>
                    <el-form-item v-if="data.showGoods" label="商品封面图宽高比例" v-if="data.listStyle==1">
                        <app-radio v-model="data.goodsCoverProportion" label="1-1">1:1</app-radio>
                        <app-radio v-model="data.goodsCoverProportion" label="3-2">3:2</app-radio>
                    </el-form-item>
                    <el-form-item v-if="data.showGoods" label="商品封面图填充">
                        <app-radio v-model="data.fill" :label="1">填充</app-radio>
                        <app-radio v-model="data.fill" :label="0">留白</app-radio>
                    </el-form-item>
                    <el-form-item label="商品样式">
                        <app-radio v-model="data.goodsStyle" :label="1">白底无边框</app-radio>
                        <app-radio v-model="data.goodsStyle" :label="2">白底有边框</app-radio>
                        <app-radio v-model="data.goodsStyle" :label="3">无底无边框</app-radio>
                    </el-form-item>
                    <el-form-item label="显示商品名称">
                        <el-switch v-model="data.showGoodsName"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="data.listStyle!==-1" label="文本样式">
                        <app-radio v-model="data.textStyle" :label="1">左对齐</app-radio>
                        <app-radio v-model="data.textStyle" :label="2">居中</app-radio>
                    </el-form-item>
                    <el-form-item v-if="data.showGoods" label="显示商品名称">
                        <el-switch v-model="data.showGoodsName"></el-switch>
                    </el-form-item>
                    <template v-if="cShowEditBuyBtn && data.showGoods">
                        <el-form-item label="显示购买按钮">
                            <el-switch v-model="data.showBuyBtn"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.showBuyBtn && data.showGoods" label="购买按钮样式">
                            <app-radio v-model="data.buyBtnStyle" :label="1">填充</app-radio>
                            <app-radio v-model="data.buyBtnStyle" :label="2">线条</app-radio>
                            <app-radio v-model="data.buyBtnStyle" :label="3">圆角填充</app-radio>
                            <app-radio v-model="data.buyBtnStyle" :label="4">圆角线条</app-radio>
                        </el-form-item>
                        <el-form-item v-if="data.showBuyBtn && data.showGoods" label="购买按钮文字">
                            <el-input maxlength='4' size="small" v-model="data.buyBtnText"></el-input>
                        </el-form-item>
                    </template>
                    <el-form-item v-if="data.showGoods" label="显示商品角标">
                        <el-switch v-model="data.showGoodsTag"></el-switch>
                    </el-form-item>
                    <el-form-item label="商品角标样式" v-if="data.showGoodsTag">
                        <app-radio v-model="data.goodsTagPicUrl" v-for="tag in goodsTags" :label="tag.picUrl"
                                  :key="tag.name"
                                  @change="goodsTagChange">
                            {{tag.name}}
                        </app-radio>
                        <app-radio v-model="data.customizeGoodsTag" :label="true" @change="customizeGoodsTagChange">自定义
                        </app-radio>
                    </el-form-item>
                    <el-form-item label="自定义商品角标" v-if="data.showGoodsTag&&data.customizeGoodsTag && data.showGoods">
                        <app-image-upload width="64" height="64" v-model="data.goodsTagPicUrl"></app-image-upload>
                    </el-form-item>
                    <el-form-item label="显示划线价" v-if="[-1,1,2].indexOf(data.listStyle) != -1">
                        <el-switch v-model="data.isUnderLinePrice"></el-switch>
                    </el-form-item>
                    <diy-bg :data="data" @update="updateData" @toggle="toggleData" @change="changeData"></diy-bg>
                </template>
            </el-form>
        </div>
        <el-dialog title="选择商品" :visible.sync="goodsDialog.visible" @open="goodsDialogOpened">
            <el-input size="mini" v-model="goodsDialog.keyword" placeholder="根据名称搜索" :clearable="true"
                      @clear="goodsDialogPageChange(1)" @keyup.enter.native="goodsDialogPageChange(1)">
                <el-button slot="append" @click="goodsDialogPageChange(1)">搜索</el-button>
            </el-input>
            <el-table :data="goodsDialog.list" v-loading="goodsDialog.loading" @selection-change="goodsSelectionChange">
                <el-table-column type="selection" width="50px"></el-table-column>
                <el-table-column label="ID" prop="id" width="100px"></el-table-column>
                <el-table-column label="名称" prop="name"></el-table-column>
            </el-table>
            <div style="text-align: center">
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
                <el-button type="primary" @click="addGoods">确定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-integral-mall', {
        template: '#diy-integral-mall',
        props: {
            value: Object,
        },
        data() {
            return {
                goodsDialog: {
                    visible: false,
                    page: 1,
                    keyword: '',
                    loading: false,
                    list: [],
                    pagination: null,
                    selected: null,
                },
                goodsTags: [
                    {
                        name: '热销',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-rx.png',
                    },
                    {
                        name: '新品',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-xp.png',
                    },
                    {
                        name: '折扣',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-zk.png',
                    },
                    {
                        name: '推荐',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-tj.png',
                    },
                ],
                couponList: [1, 2, 3, 4],
                data: {
                    showCoupon: true,
                    showGoods: true,
                    couponColor: '#ffffff',
                    couponPicUrl: '<?=$baseAssetsUrl?>/images/coupon-background.png',
                    buttonColor: '#ff4544',
                    list: [],
                    listStyle: 1,
                    fill: 1,
                    goodsCoverProportion: '1-1',
                    goodsStyle: 1,
                    textStyle: 1,
                    showGoodsName: true,
                    showBuyBtn: true,
                    buyBtnStyle: 1,
                    buyBtnText: '立即兑换',
                    showGoodsTag: false,
                    customizeGoodsTag: false,
                    goodsTagPicUrl: '',
                    showImg: false,
                    backgroundColor: '#fff',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    isUnderLinePrice: true,
                },
                position: 'center center',
                repeat: 'no-repeat',
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cListStyle() {
                if(this.data.backgroundColor) {
                    return `background-color:${this.data.backgroundColor};background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }else {
                    return `background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }
            },
            cList() {
                if (!this.data.list || !this.data.list.length) {
                    const item = {
                        id: 0,
                        name: '演示商品名称',
                        picUrl: '',
                        price: '0.00',
                        integral: 50,
                        originalPrice: '50.00',
                    };
                    return [item, item,];
                } else {
                    return this.data.list;
                }
            },
            cListFlex() {
                if (this.data.listStyle === -1) {
                    return 'dir:top';
                } else {
                    return 'dir:left';
                }
            },
            cItemStyle() {
                if (this.data.listStyle === 2) {
                    return 'width: 50%;';
                } else {
                    return 'width: 100%;';
                }
            },
            cGoodsStyle() {
                let style = 'border-radius:5px;';
                if (this.data.goodsStyle === 2) {
                    style += 'border: 1px solid #e2e2e2;';
                }
                if (this.data.goodsStyle != 3) {
                    style += 'background-color:#ffffff';
                }
                return style;
            },
            cGoodsInfoStyle() {
                let style = 'position:relative;';
                if (this.data.listStyle !== -1) {
                    style += 'padding:20px;';
                }else {
                    style += 'padding: 15px 20px 0 0;'
                }
                if (this.data.textStyle === 2) {
                    style += 'text-align: center;';
                }
                return style;
            },
            cPriceStyle() {
                let style = 'margin-top: 10px;';
                if (this.data.textStyle === 2) {
                    style += 'text-align: center;width: 100%;';
                }
                return style;
            },
            cGoodsFlex() {
                if (this.data.listStyle === -1) {
                    return 'dir:left box:first';
                } else {
                    return 'dir:top';
                }
            },
            cButtonStyle() {
                console.log(this.data.buyBtnStyle);
                let style = `background: ${this.data.buttonColor};border-color: ${this.data.buttonColor};height:48px;line-height50px;padding:0 20px;`;
                if (this.data.buyBtnStyle === 3 || this.data.buyBtnStyle === 4) {
                    style += `border-radius:999px;`;
                }
                if (this.data.buyBtnStyle === 2 || this.data.buyBtnStyle === 4) {
                    style += `background:#fff;color:${this.data.buttonColor}`;
                }
                return style;
            },
            cShowBuyBtn() {
                return this.data.textStyle !== 2
                    && this.data.showBuyBtn
            },
            cShowEditBuyBtn() {
                return this.data.textStyle !== 2
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            cGoodsPicStyle(picUrl) {
                let style = `background-image: url(${picUrl});`
                    + `background-size: ${(this.data.fill === 1 ? 'cover' : 'contain')};`;
                return style;
            },
            listStyleChange(listStyle) {
                if (listStyle === -1 && this.data.textStyle === 2) {
                    this.data.textStyle = 1;
                }
            },
            goodsDialogOpened() {
                this.loadGoodsList();
            },
            loadGoodsList() {
                this.goodsDialog.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/get-goods',
                        page: this.goodsDialog.page,
                        keyword: this.goodsDialog.keyword,
                        sign: 'integral_mall',
                    }
                }).then(response => {
                    this.goodsDialog.loading = false;
                    if (response.data.code === 0) {
                        this.goodsDialog.list = response.data.data.list;
                        this.goodsDialog.pagination = response.data.data.pagination;
                    }
                }).catch(e => {
                });
            },
            goodsDialogPageChange(page) {
                this.goodsDialog.page = page;
                this.loadGoodsList();
            },
            goodsSelectionChange(e) {
                if (e && e.length) {
                    this.goodsDialog.selected = e;
                } else {
                    this.goodsDialog.selected = null;
                }
            },
            addGoods() {
                if (!this.goodsDialog.selected || !this.goodsDialog.selected.length) {
                    this.goodsDialog.visible = false;
                    return;
                }
                // const total = this.data.list.length + this.goodsDialog.selected.length;
                // if (total > 10) {
                //     this.goodsDialog.visible = false;
                //     this.$message.error('商品数量不能大于10个。');
                //     return;
                // }
                for (let i in this.goodsDialog.selected) {
                    const item = {
                        id: this.goodsDialog.selected[i].id,
                        name: this.goodsDialog.selected[i].name,
                        picUrl: this.goodsDialog.selected[i].cover_pic,
                        price: this.goodsDialog.selected[i].price,
                        integral: this.goodsDialog.selected[i].integral,
                        originalPrice: this.goodsDialog.selected[i].original_price,
                    };
                    this.data.list.push(item);
                }
                this.goodsDialog.selected = null;
                this.goodsDialog.visible = false;
            },
            deleteGoods(index) {
                this.data.list.splice(index, 1);
            },
            goodsTagChange(e) {
                this.data.goodsTagPicUrl = e;
                this.data.customizeGoodsTag = false;
            },
            customizeGoodsTagChange(e) {
                this.data.goodsTagPicUrl = '';
                this.data.customizeGoodsTag = true;
            },
        }
    });
</script>
