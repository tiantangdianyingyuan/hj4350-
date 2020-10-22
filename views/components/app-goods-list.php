<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

$mchId = Yii::$app->user->identity->mch_id;
Yii::$app->loadViewComponent('goods/app-search');
Yii::$app->loadViewComponent('goods/app-batch');
Yii::$app->loadViewComponent('app-new-export-dialog');
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .app-goods-list .table-body .edit-sort-img {
        width: 14px;
        height: 14px;
        margin-left: 5px;
        cursor: pointer;
    }

    .app-goods-list .goods-cat .el-tag--mini {
        max-width: 60px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .app-goods-list .export-dialog .el-dialog {
        min-width: 350px;
    }

    .app-goods-list .export-dialog .el-dialog__body {
        padding: 20px 20px;
    }

    .app-goods-list .export-dialog .el-button--submit {
        color: #FFF;
        background-color: #409EFF;
        border-color: #409EFF;
    }
    .el-table th {
        display: table-cell !important;
        width: 15px !important;
    }
</style>
<template id="app-goods-list">
    <div class="app-goods-list">
        <el-card v-loading="listLoading" class="box-card" shadow="never" style="border:0"
                 body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <div slot="header">
                <span>{{header_title}}</span>
                <div style="float: right; margin: -5px 0">
                    <el-button v-if="is_add_goods" type="primary" size="small" @click="edit">{{add_goods_title}}</el-button>
                    <app-new-export-dialog
                            v-if="sign === 'exchange'"
                            text="礼品卡导出"
                            style="margin-left: 15px"
                            :field_list='exchangeFields'
                            :action_url="goods_url"
                            :directly="true"
                            :params="search">
                    </app-new-export-dialog>
                    <el-button v-if="isShowExportGoods" type="primary" size="small" @click="exportGoods">{{text}}导出
                    </el-button>
                    <el-dialog
                            flex="cross:center"
                            class="export-dialog"
                            :title="exportParams.is_show_download ? '下载' : '提示'"
                            :visible.sync="exportDialogVisible"
                            width="20%">
                        <template v-if="!exportParams.is_show_download">
                            <div flex="cross:center">
                                <i style="color: #E6A23C;font-size: 20px;margin-right: 5px" class="el-icon-warning"></i>
                                <span>选中{{choose_list.length == 0 ? '全部,共计'+ exportParams.goods_count : choose_list.length}}件{{text}}，是否确认导出</span>
                            </div>
                            <span slot="footer" class="dialog-footer">
                                    <el-button @click="exportDialogVisible = false" size="small">取 消</el-button>
                                    <el-button @click="exportGoodsData" size="small" type="primary">确定</el-button>
                                </span>
                        </template>
                        <template v-else>
                            <el-progress :text-inside="true" :stroke-width="18"
                                         :percentage="exportParams.percentage"></el-progress>
                            <span slot="footer" class="dialog-footer">
                                <form target="_blank" :action="exportParams.action_url" method="post">
                                    <div class="modal-body">
                                        <input name="_csrf" type="hidden" id="_csrf"
                                               value="<?= Yii::$app->request->csrfToken ?>">
                                        <input name="flag" value="EXPORT" type="hidden">
                                        <input name="is_download" :value="exportParams.is_download" type="hidden">
                                    </div>
                                    <div flex="dir:right" style="margin-top: 20px;">
                                        <button v-if="exportParams.percentage == 100" type="submit"
                                                class="el-button el-button--primary el-button--small">点击下载</button>
                                    </div>
                                </form>
                            </span>
                        </template>
                    </el-dialog>
                </div>
            </div>
            <div class="table-body">
                <app-search :tabs="tabs" :place-holder="placeholder" :is-show-cat="isShowCat" :new-search="search" :is-goods-type="isGoodsType" :is_mch="is_mch" :is_show_ecard="hide_function.is_show_ecard" @to-search="toSearch"
                            :new-active-name="newActiveName"></app-search>
                <app-batch :choose-list="choose_list"
                           @to-search="getList"
                           @get-all-checked="getAllChecked"
                           :batch-update-status-url="batch_update_status_url"
                           :status-change-text="status_change_text"
                           :is-show-svip="isShowSvip"
                           :is-show-express="isShowExpress"
                           :is-show-shipping="false"
                           :is-show-integral="isShowIntegral"
                           :is-show-batch-button="isShowBatchButton"
                           :batch-list="batchList">
                    <template slot="batch" slot-scope="item">
                        <slot name="batch" :item="item.item"></slot>
                    </template>
                </app-batch>
                <el-table
                        ref="multipleTable"
                        :data="list"
                        border
                        height="600"
                        style="width: 100%;margin-bottom: 15px"
                        @selection-change="handleSelectionChange"
                        @sort-change="sortChange">
                    <el-table-column type="selection" align="center" width="40"></el-table-column>
                    <el-table-column prop="id" label="ID" width="120" :sortable="sign !== 'exchange' ? true:false"></el-table-column>
                    <el-table-column v-if="!is_mch && sign !== 'exchange'" prop="sort" :width="sort_goods_id != id ? 150 : 100" label="排序"
                                     sortable="false">
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
                    <el-table-column v-else-if="is_mch && sign !== 'exchange'" prop="mchGoods.sort" :width="sort_goods_id != id ? 150 : 100" label="排序"
                                     sortable="false">
                        <template slot-scope="scope">
                            <div v-if="sort_goods_id != scope.row.id" flex="dir:left cross:center">
                                <span>{{scope.row.mchGoods.sort}}</span>
                                <img class="edit-sort-img" @click="editSort(scope.row)"
                                     src="statics/img/mall/order/edit.png" alt="">
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
                    <slot name="column-col-first"></slot>
                    <el-table-column v-if="sign !== 'exchange'" width="90" label="分类">
                        <template slot-scope="scope">
                            <div class="goods-cat"  v-if="!is_mch">
                                <el-tag v-if="scope.row.cats && scope.row.cats.length > 0"
                                        size="mini">
                                    {{scope.row.cats[0].name}}
                                </el-tag>
                                <el-tooltip v-if="scope.row.cats && scope.row.cats.length > 1" placement="top">
                                    <div slot="content">
                                        <span v-for="item in scope.row.cats" :key="item.id">{{item.name}}&nbsp;</span>
                                    </div>
                                    <span>...</span>
                                </el-tooltip>
                            </div>
                            <div class="goods-cat" v-if="is_mch" >
                                <el-tag v-if="scope.row.mchCats.length > 0" size="mini">
                                    {{scope.row.mchCats[0].name}}
                                </el-tag>
                                <el-tooltip v-if="scope.row.mchCats.length > 1" placement="top" effect="light">
                                    <div slot="content">
                                        <el-tag style="margin-right: 5px" size="mini"
                                                v-for="item in scope.row.mchCats" :key="item.id">
                                            {{item.name}}
                                        </el-tag>
                                    </div>
                                    <span>...</span>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="sign !== 'exchange' ? '商品名称':'礼品卡名称'" width="350" :resizable="false">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <image style="width: 24px;height: 24px;position: absolute;" v-if="scope.row.goodsWarehouse.type === 'goods' && isGoodsType && hide_function.is_show_ecard && !is_mch" src="statics/img/mall/goods.png"></image>
                                    <image style="width: 24px;height: 24px;position: absolute;" v-if="scope.row.goodsWarehouse.type === 'ecard' && isGoodsType && hide_function.is_show_ecard && !is_mch" src="statics/img/mall/ecard-goods.png"></image>
                                    <app-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></app-image>
                                </div>
                                <div flex="cross:top">
                                    <div v-if="goodsId != scope.row.id" flex="dir:left" style="overflow:hidden;">
                                        <div style="position: absolute;width: 250px;height: 50px;overflow: hidden" flex="dir:left">
                                            <el-tooltip class="item"
                                                        effect="dark"
                                                        placement="top">
                                                <template slot="content">
                                                    <div style="width: 320px;">{{scope.row.goodsWarehouse.name}}</div>
                                                </template>
                                                <div v-line-clamp="2" >{{scope.row.goodsWarehouse.name}}</div>
                                            </el-tooltip>
                                            <el-button v-if="is_edit_goods_name" style="padding: 0;margin-left: 5px;" type="text"
                                                       @click="editGoodsName(scope.row)">
                                                <img src="statics/img/mall/order/edit.png" alt="">
                                            </el-button>
                                        </div>
                                    </div>
                                    <div style="display: flex;align-items: center" v-else>
                                        <el-input style="min-width: 70px"
                                                  type="text"
                                                  size="mini"
                                                  class="change"
                                                  v-model="goodsName"
                                                  maxlength="100"
                                                  show-word-limit
                                                  autocomplete="off"
                                        ></el-input>
                                        <el-button class="change-quit" type="text"
                                                   style="color: #F56C6C;padding: 0 5px"
                                                   icon="el-icon-error"
                                                   circle @click="quit()"></el-button>
                                        <el-button class="change-success" type="text"
                                                   style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                                   icon="el-icon-success" circle
                                                   @click="changeGoodsName(scope.row)">
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" :label="sign !== 'exchange'?'售价':'价格'" :sortable="sign !== 'exchange' ? true:false">
                        <template slot-scope="scope">
                            <div v-if="goods_url == 'plugin/integral_mall/mall/goods/index'">{{scope.row.goodsWarehouse.original_price}}</div>
                            <div v-else>{{sign !== 'exchange' ? '':'￥'}}{{scope.row.price}}</div>
                        </template>
                    </el-table-column>
                    <slot name="column-col-sec"></slot>
                    <el-table-column prop="goods_stock" label="库存" :sortable="sign !== 'exchange' ? true:false">
                        <template slot-scope="scope">
                            <div v-if="scope.row.goods_stock > 0">{{scope.row.goods_stock}}</div>
                            <div v-else style="color: red;">售罄</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="sales" width="150" label="已出售量" sortable="false">
                        <template slot="header">
                            <div style="vertical-align: middle;">已出售量
                                <el-tooltip class="item" effect="dark"
                                            content="已出售量=实际销量+虚拟销量，按实际销量排序"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip></div>
                        </template>
                        <template slot-scope="scope">
                            <div>{{scope.row.sales}}+<span style="color: #999999;font-size: 12px">{{scope.row.virtual_sales}}</span></div>
                        </template>
                    </el-table-column>
                    <slot name="column-col"></slot>
                    <el-table-column prop="created_at" width="160" label="添加时间"></el-table-column>
                    <el-table-column width="90" v-if="is_mch && mchMallSetting.is_goods_audit == 1" label="申请状态">
                        <template slot-scope="scope">
                            <template>
                                <el-button v-if="scope.row.mchGoods.status == 0 || scope.row.mchGoods.status == 3"
                                           @click="applyStatus(scope.row.id)"
                                           type="primary" size="mini">申请上架
                                </el-button>
                                <div v-if="scope.row.mchGoods.status == 1">
                                    申请中
                                </div>
                                <div v-if="scope.row.mchGoods.status == 2">
                                    已通过
                                </div>
                                <el-tooltip v-if="scope.row.mchGoods.status == 3 && scope.row.mchGoods.remark"
                                            effect="dark"
                                            :content="scope.row.mchGoods.remark"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" width="100">
                        <template slot-scope="scope">
                            <template v-if="!is_mch || mchMallSetting.is_goods_audit == 0">
                                <el-tag size="small" type="success" v-if="scope.row.status">销售中</el-tag>
                                <el-tag size="small" type="warning" v-else>下架中</el-tag>
                            </template>
                            <template v-else>
                                <template v-if="scope.row.status == 1">
                                    <div flex="dir:top">
                                        <div flex="main:center">已上架</div>
                                        <el-button :loading="btnLoading" size="mini" @click="switchStatus(scope.row)">
                                            下架
                                        </el-button>
                                    </div>
                                </template>
                                <el-tag size="small" type="danger" v-else>下架中</el-tag>
                            </template>
                        </template>
                    </el-table-column>
                    <slot name="switch-col"></slot>
                    <el-table-column
                        label="操作"
                        fixed="right"
                        :width="actionWitch">
                        <template slot-scope="scope">
                            <slot name="action" :item="scope.row"></slot>
                            <template v-if="is_action">
                                <el-button @click="edit(scope.row)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <slot name="action-sec" :item="scope.row"></slot>
                                <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </template>
                    </el-table-column>
                </el-table>
                <div flex="dir:right" style="margin-top: 20px;">
                    <el-pagination
                        @current-change="pagination"
                        background
                        hide-on-single-page
                        :current-page="current_page"
                        layout="prev, pager, next, jumper"
                        :page-count="pageCount">
                    </el-pagination>
                </div>
            </div>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-goods-list', {
        template: '#app-goods-list',
        props: {
            goods_url: {
                type: String,
                default: 'mall/goods/index'
            },
            edit_goods_url: {
                type: String,
                default: 'mall/goods/edit'
            },
            destroy_goods_url: {
                type: String,
                default: 'mall/goods/destroy'
            },
            edit_goods_sort_url: {
                type: String,
                default: 'mall/goods/edit-sort'
            },
            edit_goods_status_url: {
                type: String,
                default: 'mall/goods/switch-status'
            },
            batch_update_status_url: {
                type: String,
                default: 'mall/goods/batch-update-status'
            },
            is_edit_goods_name: {
                type: Boolean,
                default: false
            },
            is_action: {
                type: Boolean,
                default: true
            },
            actionWitch: {
                type: Number,
                default: 150
            },
            is_add_goods: {
                type: Boolean,
                default: true
            },
            status_change_text: {
                type: String,
                default: '',
            },
            add_goods_title: {
                type: String,
                default: '添加商品',
            },
            header_title: {
                type: String,
                default: '商品列表',
            },
            text: {
                type: String,
                default: '商品',
            },
            placeHolder: {
                type: String
            },
            sign: {
                type: String
            },
            isShowCat: {
                type: Boolean,
                default: true
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {
                            name: '全部',
                            value: '-1'
                        },
                        {
                            name: '销售中',
                            value: '1'
                        },
                        {
                            name: '下架中',
                            value: '0'
                        },
                        {
                            name: '售罄',
                            value: '2'
                        },
                    ];
                }
            },
            /**
             * 批量设置参数
             * 具体参数看 app-batch 组件
             */
            batchList: Array,
            isShowSvip: {// 批量设置超级会员卡是否显示
                type: Boolean,
                default: true
            },
            isShowExpress: { // 批量设置运费是否显示
                type: Boolean,
                default: true
            },
            isShowIntegral: {// 批量设置积分是否显示
                type: Boolean,
                default: true
            },
            isShowBatchButton: {//批量设置按钮是否显示
                type: Boolean,
                default: true
            },
            isShowExportGoods: {//商品导出按钮
                type: Boolean,
                default: false
            },
            isShowUpdate: {//商品销量更新按钮,
                type: Boolean,
                default: false
            },
            isGoodsType: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                exchangeFields: ['id','name','price','cardGoods','goods_stock','sales','created_at','status'],
                search: {
                    keyword: '',
                    status: '-1',
                    sort_prop: '',
                    sort_type: '',
                    cats: [],
                    date_start: null,
                    date_end: null,
                    type: ''
                },
                placeholder: '',
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                current_page: 1,
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
                mchMallSetting: {},
                choose_list: [],
                btnLoading: false,
                sort: 0,
                id: 0,// 应该无用了
                sort_goods_id: 0,

                goodsId: 0,
                goodsName: '',

                // 分类筛选
                dialogVisible: false,
                dialogLoading: false,
                options: [],
                cats: [],
                children: [],
                third: [],
                mch_id: '<?= $mchId ?>',
                newActiveName: '-1',
                exportDialogVisible: false,
                // 商品导出参数
                exportParams: {
                    page: 1,
                    is_show_download: false,
                    is_download: 0,
                    percentage: 0,
                    action_url: '<?= Yii::$app->urlManager->createUrl('mall/goods/export-goods-list') ?>',
                    goods_count: 0,//商品总数
                },
                hide_function: {
                    is_show_ecard: false
                }
            };
        },
        created() {
            let self = this;
            if (this.is_mch) {
                this.getMchMallSetting();
            }
            if (getQuery('page') > 1) {
                this.page = getQuery('page');
            }
            this.placeholder = this.placeHolder ? this.placeHolder :'请输入'+this.text+'ID或名称搜索'
            // 搜索条件从缓存中获取
            let search = this.getCookie('search');
            if (search) {
                let newSearch = JSON.parse(search);
                this.search.keyword = newSearch.keyword;
                this.search.cats = newSearch.cats;
                this.search.date_start = newSearch.date_start;
                this.search.date_end = newSearch.date_end;
                self.tabs.forEach(function (item) {
                    if (item.value == newSearch.status) {
                        self.search.status = newSearch.status;
                        self.newActiveName = newSearch.status;
                    }
                })
            }
            this.getList();
        },
        methods: {
            editGoodsName(row) {
                this.goodsId = row.id;
                this.goodsName = row.goodsWarehouse.name;
            },
            changeGoodsName(row) {
                let self = this;
                request({
                    params: {
                        r: 'mall/goods/update-goods-name'
                    },
                    data: {
                        goods_id: self.goodsId,
                        goods_name: self.goodsName
                    },
                    method: 'post'
                }).then(e => {
                    if (e.data.code == 0) {
                        self.goodsId = null;
                        self.$message.success(e.data.msg);
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                });
            },
            // 全选单前页
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                self.saveSearch();
                request({
                    params: {
                        r: self.goods_url,
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    self.current_page = e.data.data.pagination.current_page;
                    self.exportParams.goods_count = e.data.data.goods_count;
                    self.hide_function = e.data.data.hide_function;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(row) {
                if (row.id) {
                    navigateTo({
                        r: this.edit_goods_url,
                        id: row.id,
                        mch_id: row.mch_id,
                        page: this.page,
                    });
                    this.saveSearch();
                } else {
                    navigateTo({
                        r: this.edit_goods_url,
                        page: this.page
                    });
                }
            },
            getCookie(cname) {
                let name = cname + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            },
            saveSearch() {
                document.cookie = "search=" + JSON.stringify(this.search);
            },
            destroy(row, index) {
                let self = this;
                let text = '删除该条数据, 是否继续?';
                if(this.sign === 'exchange') {
                    text = '确认删除此礼品卡？'
                }
                self.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: this.destroy_goods_url,
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            applyStatus(id) {
                let self = this;
                self.$confirm('申请上架, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/goods/apply-status',
                        },
                        method: 'post',
                        data: {
                            id: id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.getList();
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消申请')
                });
            },
            // 商品上下架
            switchStatus(row) {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: this.edit_goods_status_url,
                    },
                    method: 'post',
                    data: {
                        status: row.status,
                        id: row.id,
                        mch_id: row.mch_id
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.getList();
                }).catch(e => {
                    console.log(e);
                });
            },
            getMchMallSetting() {
                let self = this;
                request({
                    params: {
                        r: 'mall/mch/mch-setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.mchMallSetting = e.data.data.setting;
                }).catch(e => {
                    console.log(e);
                });
            },
            toSearch(searchData) {
                this.page = 1;
                this.search = searchData;
                this.getList();
            },
            // 排序排列
            sortChange(row) {
                if (row.prop && row.order) {
                    this.search.sort_prop = row.prop;
                    this.search.sort_type = row.order == "descending" ? 0 : 1;
                } else {
                    this.search.sort_prop = '';
                    this.search.sort_type = '';
                }
                this.getList();
            },
            quit() {
                this.sort_goods_id = null;
                this.goodsId = null;
            },
            editSort(row) {
                this.sort_goods_id = row.id;
                this.sort = row.sort;
                if (this.is_mch) {
                    this.sort = row.mchGoods.sort;
                }
            },
            changeSortSubmit(row) {
                let self = this;
                row.sort = self.sort;
                let route = self.edit_goods_sort_url;
                if (!row.sort || row.sort < 0) {
                    self.$message.warning('排序值不能小于0')
                    return;
                }
                if (this.is_mch) {
                    route = 'plugin/mch/mall/goods/edit-sort';
                    row.mchGoods.sort = self.sort;
                }
                request({
                    params: {
                        r: route
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                        sort: row.sort,
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                        this.sort_goods_id = null;
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },
            getAllChecked(e) {
                this.$emit('get-all-checked', e)
            },
            // 商品导出
            exportGoods() {
                this.exportDialogVisible = true;
                this.exportParams = {
                    page: 1,
                    is_show_download: false,
                    is_download: 0,
                    percentage: 0,
                    action_url: '<?= Yii::$app->urlManager->createUrl('mall/goods/export-goods-list') ?>',
                    goods_count: this.exportParams.goods_count,//商品总数
                }
            },
            exportGoodsData() {
                let self = this;
                self.exportParams.is_show_download = true;
                request({
                    params: {
                        r: 'mall/goods/export-goods-list',
                    },
                    data: {
                        page: self.exportParams.page,
                        search: JSON.stringify(self.search),
                        flag: 'EXPORT',
                        choose_list: self.choose_list,
                        _csrf: '<?= Yii::$app->request->csrfToken ?>',
                        is_download: self.exportParams.is_download,
                    },
                    method: 'post',
                }).then(e => {
                    let data = e.data.data;
                    if (e.data.code === 0) {
                        self.exportParams.increase = 100 / data.pagination.page_count;
                        self.exportParams.percentage += self.exportParams.increase;
                        self.exportParams.percentage = parseFloat(self.exportParams.percentage.toFixed(2));
                        if (data.pagination.current_page == data.pagination.page_count || data.pagination.page_count == 0) {
                            self.exportParams.percentage = 100;
                            self.exportParams.is_download += data.export_goods.is_download;
                        }
                        if (data.pagination.current_page < data.pagination.page_count) {
                            self.exportParams.page += 1;
                            self.exportGoodsData();
                        }
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            updateSales() {
                this.$confirm('更新商品销量会消耗大量的时间，请谨慎点击', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$request({
                        params: {
                            r: 'mall/goods/update-sales'
                        }
                    }).then(response => {
                        if (response.data.code === 0) {
                            this.$message.success(response.ata.msg);
                        } else {
                            this.$message.error(response.ata.msg);
                        }
                    });
                }).catch(() => {
                    console.log('您点击了取消')
                })
            }
        },
    });
</script>

