<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/7
 * Time: 12:02
 */
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<style>
    .diy-mch .diy-component-preview .mch-list {
        padding: 9px;
    }
    
    .diy-mch .diy-component-preview .mch-item {
        padding-bottom: 16px;
        margin-bottom: 12px;
        border-radius: 8px;
    }

    .diy-mch .diy-component-preview .mch-pic,
    .diy-mch .diy-component-preview .goods-pic {
        display: inline-block;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 210px 210px;
        background-color: #d6d6d6;
    }

    .diy-mch .diy-component-preview .mch-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .diy-mch .diy-component-preview .mch-info {
        color: #B8B8B8;
        font-size: 24px;
    }

    .diy-mch .diy-component-preview .goods-list {
        padding-top: 16px;
        margin: 0 -10px;
        overflow-x: auto;
    }

    .diy-mch .diy-component-preview .goods-item {
        background: #fff;
        margin: 0 10px;
        position: relative;
        height: 210px;
    }

    .diy-mch .diy-component-preview .goods-price {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #ff4544;
        text-align: center;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50px;
        line-height: 50px;
        background-color: rgba(255, 255, 255, .8)
    }

    .diy-mch .diy-component-preview .mch-item .to-look {
        height: 60px;
        width: 150px;
        border: 1px solid #E7E7E7;
        border-radius: 30px;
        text-align: center;
        line-height: 58px;
        color: #353535;
        font-size: 24px;
    }

    /*-------------- 编辑部份 --------------*/
    .diy-mch .diy-component-edit .edit-item {
        border: 1px solid #e2e2e2;
        padding: 15px;
        margin-bottom: 5px;
    }

    .diy-mch .diy-component-edit .edit-options {
        position: relative;
    }

    .diy-mch .diy-component-edit .edit-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .diy-mch .diy-component-edit .goods-list {
        line-height: normal;
        flex-wrap: wrap;
    }

    .diy-mch .diy-component-edit .goods-item,
    .diy-mch .diy-component-edit .goods-add {
        width: 50px;
        height: 50px;
        border: 1px solid #e2e2e2;
        background-position: center;
        background-size: cover;
        margin-right: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .diy-mch .diy-component-edit .goods-add {
        cursor: pointer;
    }

    .diy-mch .diy-component-edit .goods-delete {
        position: absolute;
        top: -11px;
        right: -11px;
        width: 25px;
        height: 25px;
        line-height: 25px;
        padding: 0 0;
        visibility: hidden;
    }

    .diy-mch .diy-component-edit .goods-item:hover .goods-delete {
        visibility: visible;
    }
</style>
<template id="diy-mch">
    <div class="diy-mch">
        <div class="diy-component-preview" :style="cListStyle">
            <div class="mch-list" v-if="!data.showGoods" flex="dir:left" style="overflow-x: auto;">
                <div class="mch-item" :style="cItemStyle" style="margin-right: 8px" v-for="(mch,mchIndex) in cMchList">
                    <div class="mch-pic" :style="mch.picUrl?('background-image:url('+mch.picUrl+');'):''"
                         style="width: 224px;height: 224px;"></div>
                    <div class="mch-name" style="width: 224px;text-align: center;">{{mch.name}}</div>
                </div>
            </div>
            <div class="mch-list" v-else>
                <div class="mch-item" :style="cItemStyle" v-for="(mch,mchIndex) in cMchList">
                    <div flex="box:justify cross:center">
                        <div>
                            <div class="mch-pic"
                                 :style="mch.picUrl?('background-image:url('+mch.picUrl+');'):''"
                                 style="width: 100px;height: 100px;margin-right: 20px;"></div>
                        </div>
                        <div>
                            <div class="mch-name" style="font-size: 16px">{{mch.name}}</div>
                            <div class='mch-info'>
                                <span>商品数: {{mch.goodsNum}}</span>
                                <span style="margin-left: 10px">已售: {{mch.orderNum}}</span>
                            </div>
                        </div>
                        <div>
                            <div class="to-look">进店逛逛</div>
                        </div>
                    </div>

                    <div v-if="fGoodsList(mch) && fGoodsList(mch).length" class="goods-list" flex>
                        <div v-for="(goods,goodsIndex) in fGoodsList(mch)" class="goods-item">
                            <div class="goods-pic"
                                 style="width: 210px;height: 210px;"
                                 :style="goods.picUrl?('background-image:url('+goods .picUrl+');'):''"></div>
                            <div class="goods-price">￥{{goods.price}}</div>
                        </div>
                    </div>
                    <div v-else class="goods-list" flex="main:center cross:center"
                         style="height: 100px;color: #909399;">暂无商品
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="100px">
                <el-form-item label="显示商品">
                    <el-switch v-model="data.showGoods"></el-switch>
                </el-form-item>
                <el-form-item label="商户列表">
                    <div class="edit-item" v-for="(mch,mchIndex) in data.list">
                        <div class="edit-options">
                            <el-button @click="deleteMch(mchIndex)"
                                       type="primary"
                                       icon="el-icon-delete"
                                       style="top: -16px;right: -41px;"></el-button>
                        </div>
                        <div flex="box:first">
                            <div style="width: 95px;">商户名称</div>
                            <div>{{mch.name}}</div>
                        </div>
                        <template v-if="data.showGoods">
                            <div flex="box:first" v-if="!mch.staticGoods">
                                <div style="width: 95px;">商品显示数量</div>
                                <div>
                                    <el-input size="small" v-model.number="mch.showGoodsNum" type="number"
                                              min="0" max="10"
                                              placeholder="最多10个" @change="goodsNumChange(mchIndex)"></el-input>
                                </div>
                            </div>
                            <div flex="box:first">
                                <div style="width: 95px;">自定义商品</div>
                                <div>
                                    <el-switch v-model="mch.staticGoods"></el-switch>
                                </div>
                            </div>
                            <div v-if="mch.staticGoods">
                                <div>商品列表</div>
                                <div class="goods-list" flex>
                                    <div class="goods-item" v-for="(goods,goodsIndex) in fGoodsList(mch)"
                                         :style="'background-image: url('+goods.picUrl+');'">
                                        <el-button @click="deleteGoods(mchIndex,goodsIndex)" class="goods-delete"
                                                   size="small" circle type="danger"
                                                   icon="el-icon-close"></el-button>
                                    </div>
                                    <div class="goods-add" flex="main:center cross:center"
                                         @click="showGoodsDialog(mchIndex)">
                                        <i class="el-icon-plus"></i>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <el-button size="small" @click="showMchDialog">添加商户</el-button>
                </el-form-item>
                <el-form-item label="卡片样式">
                    <el-radio v-model="data.cardStyle" :label="1">白底无边框</el-radio>
                    <el-radio v-model="data.cardStyle" :label="2">白底有边框</el-radio>
                    <el-radio v-model="data.cardStyle" :label="3">无底无边框</el-radio>
                </el-form-item>
                <diy-bg :hr="hr" :data="data" @update="updateData" @toggle="toggleData" @change="changeData"></diy-bg>
            </el-form>
        </div>
        <el-dialog title="选择商户" :visible.sync="mchDialog.visible" @open="mchDialogOpened">
            <el-input size="mini" v-model="mchDialog.keyword" placeholder="根据名称搜索" :clearable="true"
                      @clear="loadMchList" @keyup.enter.native="loadMchList">
                <el-button slot="append" @click="loadMchList">搜索</el-button>
            </el-input>
            <el-table :data="mchDialog.list" v-loading="mchDialog.loading" @selection-change="mchSelectionChange">
                <el-table-column type="selection" width="50px"></el-table-column>
                <el-table-column label="ID" prop="id" width="100px"></el-table-column>
                <el-table-column label="名称" prop="store.name"></el-table-column>
            </el-table>
            <div style="text-align: center">
                <el-pagination
                        v-if="mchDialog.pagination"
                        style="display: inline-block"
                        background
                        @current-change="mchDialogPageChange"
                        layout="prev, pager, next, jumper"
                        :page-size.sync="mchDialog.pagination.pageSize"
                        :total="mchDialog.pagination.totalCount">
                </el-pagination>
            </div>
            <div slot="footer">
                <el-button type="primary" @click="addMch">确定</el-button>
            </div>
        </el-dialog>
        <el-dialog title="选择商品" :visible.sync="goodsDialog.visible" @open="goodsDialogOpened">
            <el-input size="mini" v-model="goodsDialog.keyword" placeholder="根据名称搜索" :clearable="true"
                      @clear="loadGoodsList" @keyup.enter.native="loadGoodsList">
                <el-button slot="append" @click="loadGoodsList">搜索</el-button>
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
    Vue.component('diy-mch', {
        template: '#diy-mch',
        props: {
            value: Object,
        },
        data() {
            return {
                mchDialog: {
                    visible: false,
                    page: 1,
                    keyword: '',
                    loading: false,
                    list: [],
                    pagination: null,
                    selected: null,
                },
                goodsDialog: {
                    visible: false,
                    mchIndex: null,
                    page: 1,
                    keyword: '',
                    loading: false,
                    list: [],
                    pagination: null,
                    selected: null,
                },
                data: {
                    showGoods: false,
                    list: [],
                    cardStyle: 1,
                    showImg: false,
                    backgroundColor: '#fff',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                },
                hr: false,
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
            fGoodsList() {
                return (mch) => {
                    if (!mch.staticGoods) {
                        const goods = {
                            id: 0,
                            picUrl: '',
                            price: '999.00',
                        }
                        return [goods, goods, goods];
                    }
                    return mch.goodsList;
                }
            },
            cListStyle() {
                if(this.data.backgroundColor) {
                    return `background-color:${this.data.backgroundColor};background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }else {
                    return `background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }
            },
            cItemStyle() {
                let style;
                if(this.data.cardStyle == 1) {
                    style = `background-color:#ffffff;`
                }else if(this.data.cardStyle == 2) {
                    style = `background-color:#ffffff;border: 1px solid #e2e2e2;`
                }else {
                    style = ''
                }
                if(this.data.showGoods) {
                    style += `padding: 20px;`
                }
                return style
            },
            cMchList() {
                if (this.data.list && this.data.list.length) {
                    return this.data.list;
                }
                const goods = {
                    id: 0,
                    picUrl: '',
                    price: '999.00',
                };
                const item = {
                    id: 0,
                    name: '商户名称',
                    picUrl: '',
                    goodsList: [goods, goods, goods,],
                    goodsNum: 'xxx',
                    orderNum: 'xxx',
                    showGoodsNum: 10,
                    staticGoods: false,
                };
                return [item, item, item,];
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
            mchDialogOpened() {
                if (!this.mchDialog.list || !this.mchDialog.list.length) {
                    this.loadMchList();
                }
            },
            loadMchList() {
                this.mchDialog.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/mch/mall/mch/index',
                        page: this.mchDialog.page,
                        keyword: this.mchDialog.keyword,
                    }
                }).then(response => {
                    this.mchDialog.loading = false;
                    if (response.data.code === 0) {
                        this.mchDialog.list = response.data.data.list;
                        this.mchDialog.pagination = response.data.data.pagination;
                    }
                }).catch(e => {
                });
            },
            mchDialogPageChange(page) {
                this.mchDialog.page = page;
                this.loadMchList();
            },
            mchSelectionChange(e) {
                if (e && e.length) {
                    this.mchDialog.selected = e;
                } else {
                    this.mchDialog.selected = null;
                }
            },
            addMch() {
                if (!this.mchDialog.selected || !this.mchDialog.selected.length) {
                    this.mchDialog.visible = false;
                    return;
                }
                for (let i in this.mchDialog.selected) {
                    const item = {
                        id: this.mchDialog.selected[i].id,
                        name: this.mchDialog.selected[i].store.name,
                        picUrl: this.mchDialog.selected[i].store.cover_url,
                        goodsNum: 'xxx',
                        orderNum: 'xxx',
                        goodsList: [],
                        showGoodsNum: 10,
                        staticGoods: false,
                    };
                    this.data.list.push(item);
                }
                this.mchDialog.selected = null;
                this.mchDialog.visible = false;
            },
            deleteMch(mchIndex) {
                this.data.list.splice(mchIndex, 1);
            },
            goodsNumChange(mchIndex) {
                console.log(mchIndex);
                console.log(this.data.list[mchIndex].showGoodsNum);
                if (this.data.list[mchIndex].showGoodsNum > 10) {
                    this.data.list[mchIndex].showGoodsNum = 10;
                }
                if (this.data.list[mchIndex].showGoodsNum < 0) {
                    this.data.list[mchIndex].showGoodsNum = 0;
                }
            },
            showGoodsDialog(mchIndex) {
                this.goodsDialog.mchIndex = mchIndex;
                this.goodsDialog.visible = true;
                this.goodsDialog.list = null;
            },
            goodsDialogOpened() {
                if (!this.goodsDialog.list || !this.goodsDialog.list.length) {
                    this.loadGoodsList();
                }
            },
            loadGoodsList() {
                this.goodsDialog.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/get-goods',
                        page: this.goodsDialog.page,
                        mch_id: this.data.list[this.goodsDialog.mchIndex].id,
                        cat_id: this.goodsDialog.catId,
                        sign: 'mch',
                        keyword: this.goodsDialog.keyword
                    },
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
                if (!this.goodsDialog.selected
                    || !this.goodsDialog.selected.length
                    || this.goodsDialog.mchIndex === null) {
                    this.goodsDialog.visible = false;
                    return;
                }
                // const total = this.data.list[this.goodsDialog.mchIndex].goodsList.length
                //     + this.goodsDialog.selected.length;
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
                    };
                    this.data.list[this.goodsDialog.mchIndex].goodsList.push(item);
                }
                this.goodsDialog.mchIndex = null;
                this.goodsDialog.selected = null;
                this.goodsDialog.visible = false;
            },
            deleteGoods(mchIndex, goodsIndex) {
                this.data.list[mchIndex].goodsList.splice(goodsIndex, 1);
            },
            showMchDialog() {
                this.mchDialog.list = null;
                this.mchDialog.selected = null;
                this.mchDialog.visible = true;
            }
        }
    });
</script>