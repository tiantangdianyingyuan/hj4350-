<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/26
 * Time: 15:35
 */
defined('YII_ENV') or exit('Access Denied');
$iconBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('vip_card') . '/img/';
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .card-name {
        height: 58px;
        line-height: 58px;
        background-color: #fff;
        padding: 0 40px;
        display: inline-block;
    }

    .card {
        height: 158px;
        border: 1px solid #EBEEF5;
        width: 100%;
        margin-bottom: 20px;
        overflow-x: auto;
        position: relative;
    }

    .card-info {
        text-align: center;
        width: 190px;
        border-right: 1px solid #EBEEF5;
    }

    .card-info .card-cover {
        height: 85px;
        width: 131px;
        border-radius: 4px;
        margin-bottom: 6px;
    }

    .card-right {
        flex-grow: 1;
        overflow-x: auto;
    }

    .right-label {
        border-bottom: 1px solid #EBEEF5;
        height: 62px;
        line-height: 62px;
        padding: 0 25px;
        width: 100%;
    }

    .right-info {
        height: 60px;
        padding-left: 20px;
        font-size: 14px;
        color: #000000;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .right-info:nth-child(2) {
        border-left: 1px solid #e2e2e2;
    }

    .right-img {
        height: 39px;
        width: 40px;
        margin-right: 18px;
    }

    .about-item>div {
        height: 26px;
        line-height: 26px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
    }

    .about-item>div span {
        font-size: 10px;
        height: 26px;
        line-height: 26px;
        padding: 0 10px;
        background-color: #FCF6EB;
        color: #EAB25D;
        margin-left: 10px;
        border-radius: 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>会员卡列表</span>
            </div>
        </div>
        <div style="margin-bottom: 10px;" flex="dir:left cross:center">
            <div v-if="list" class="card-name">{{list.name}}</div>
            <div v-if="list" flex="cross:center">
                <el-button style="padding: 9px 24px;margin-left: 25px" type="primary" size="small" @click="toAdd(0)">+新增子卡</el-button>
            </div>
        </div>
        <div v-if="list" class="table-body">
            <div class="card" flex="dir:left">
                <div class="card-info" flex="main:center cross:center">
                    <div>
                        <img class="card-cover" :src="list.cover ? list.cover : 'statics/img/plugins/svip.png'" alt="">
                        <div style="width: 100%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap">{{list.name}}</div>
                    </div>
                </div>
                <div class="card-right">
                    <div class="right-label" flex="main:justify">
                        <div>会员卡权益</div>
                        <div>
                            <el-button style="padding: 9px 24px;margin-left: 25px" type="primary" size="small" @click="edit">编辑</el-button>
                        </div>
                    </div>
                    <div style="width:auto;overflow-x: auto;height: 94px" flex="dir:left cross:center">
                        <div v-if="list.is_free_delivery == '1'" class="right-info" flex="dir:left cross:center">
                            <img class="right-img" src="<?= $iconBaseUrl ?>/delivery.png" alt="">
                            <div>
                                <div>自营商品包邮</div>
                            </div>
                        </div>
                        <div v-if="list.type_info_detail.all" class="right-info" flex="dir:left cross:center">
                            <img class="right-img" src="<?= $iconBaseUrl ?>/all.png" alt="">
                            <div>
                                <div>全场自营商品{{list.discount == 0 ? '免费': list.discount + '折'}}</div>
                            </div>
                        </div>
                        <div v-if="(list.type_info_detail.cats.length > 0 || list.type_info_detail.goods.length > 0)" style="width: auto;" class="right-info right-about" flex="dir:left cross:center">
                            <img class="right-img" src="<?= $iconBaseUrl ?>/all.png" alt="">
                            <div>
                                <div>指定分类/商品{{list.discount == 0 ? '免费': list.discount + '折'}}</div>
                            </div>
                            <div style="margin-left: 13px;">
                                <div style="margin-bottom: 10px;" flex="dir:left cross:center">
                                    <div>指定分类：</div>
                                    <el-button @click="toLook(2)" v-if="list.type_info_detail.cats.length > 0" size="small">查看</el-button>
                                    <el-button v-else size="small">无</el-button>
                                </div>
                                <div flex="dir:left cross:center">
                                    <div>指定商品：</div>
                                    <el-button @click="toLook(1)" v-if="list.type_info_detail.goods.length > 0" size="small">查看</el-button>
                                    <el-button v-else size="small">无</el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <el-table
                v-loading="listLoading"
                :data="list.detail"
                border
                style="width: 100%">
                <el-table-column
                        label="子卡标题"
                        width="200">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column
                        label="价格"
                        width="150">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.price}}</div>
                    </template>
                </el-table-column>

                <el-table-column
                        label="有效期"
                        width="120">
                    <template slot-scope="scope">
                        <div>{{scope.row.expire_day}}天</div>
                    </template>
                </el-table-column>

                <el-table-column
                        label="折扣"
                        width="120">
                    <template slot-scope="scope">
                        <div>{{list.discount == 0 ? '免费': list.discount + '折'}}</div>
                    </template>
                </el-table-column>

                <el-table-column
                        label="赠送">
                    <template slot-scope="scope">
                        <app-ellipsis v-if="scope.row.send_integral_num > 0" :line="1">赠送积分{{scope.row.send_integral_num}}</app-ellipsis>
                        <app-ellipsis v-if="scope.row.send_balance > 0" :line="1">赠送余额{{scope.row.send_balance}}</app-ellipsis>
                        <app-ellipsis v-for="item in scope.row.coupons" :key="item.id" :line="1">赠送优惠券{{item.send_num}}张 {{item.name}}</app-ellipsis>
                        <app-ellipsis v-for="item in scope.row.cards" :key="item.id" :line="1">赠送卡券{{item.send_num}}张 {{item.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" width="150" label="排序">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.sort}}</span>
                            </el-tooltip>
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
                                       icon="el-icon-success" circle @click="change(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="库存">
                    <template slot-scope="scope">
                        <div>{{scope.row.num != 0 ? scope.row.num : '告罄'}}</div>
                    </template>
                </el-table-column>

                <el-table-column
                        label="显示开关"
                        width="150">
                    <template slot-scope="scope">
                        <el-switch @change="changeStatus(scope.row)" v-model="scope.row.status" active-value="0" inactive-value="1">
                        </el-switch>
                    </template>
                </el-table-column>

                <el-table-column
                        label="已出售"
                        width="120">
                    <template slot-scope="scope">
                        <div>{{scope.row.sales}}</div>
                    </template>
                </el-table-column>

                <el-table-column
                    label="操作"
                    fixed="right"
                    width="150">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="toAdd(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="destroy(scope.row, scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                   :page-size="table_pagination.pageSize"
                   background
                   @current-change="tablePageChange"
                   layout="prev, pager, next, jumper"
                   hide-on-single-page
                   :total="table_pagination.total_count"
                >
                </el-pagination>
            </div>
        </div>
        <div style="width: 100%;" v-else flex="dir:right">
            <el-button style="padding: 9px 24px" @click="edit" type="primary" size="small">新建超级会员卡</el-button>
        </div>
        <el-dialog :title="lookGoods ? '查看商品':'查看分类'" :visible.sync="showDialog">
            <el-input size="small" v-model="keyword" placeholder="根据名称搜索">
                <template slot="append">
                    <el-button slot="append" @click="search(1)">搜索</el-button>
                </template>
            </el-input>
            <el-table v-loading="dialogLoading" :data="goods" class="dialog-goods">
                <el-table-column property="id" label="ID" width="120"></el-table-column>
                <el-table-column property="name" :label="lookGoods ? '商品名称':'分类名称'"></el-table-column>
            </el-table>
            <div flex="main:center" style="margin-top: 10px;">
                <el-pagination
                        :page-size="pagination.pageSize" hide-on-single-page style="display: inline-block;float: right;" background @current-change="pageChange" layout="prev, pager, next" :total="pagination.total_count">
                </el-pagination>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button size='small' @click="showDialog= false">取 消</el-button>
                <el-button size='small' type="primary" @click="showDialog= false">确 定</el-button>
            </span>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: {
                    name: '',
                    type_info_detail: {
                        all: false,
                        goods: [],
                        cats: [],
                    },
                    detail: []
                },
                keyword: '',
                goods: [],
                id: null,
                showDialog:false,
                dialogLoading: false,
                lookGoods: false,
                lookCat: false,
                sort: 0,
                pagination: {},
                listLoading: false,
                table_pagination:{}
            };
        },
        methods: {
            pageChange(page) {
                this.search(page);
            },
            toLook(num) {
                this.showDialog = true;
                this.dialogLoading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/right',
                        type: num
                    },
                }).then(e => {
                    let { code, data, msg } = e.data;
                    if (code === 0) {
                        this.dialogLoading = false;
                        this.goods = data.list;
                        this.pagination = data.pagination;
                        num == 1 ? this.lookGoods = true : this.lookCat = true;
                    } else {
                        this.$message.error(msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },

            search(page) {
                this.dialogLoading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/right',
                        type: this.lookGoods ? 1: 2,
                        keyword: this.keyword,
                        page: page
                    },
                }).then(e => {
                    let { code, data, msg } = e.data;
                    if (code === 0) {
                        this.dialogLoading = false;
                        this.goods = data.list;
                        this.pagination = data.pagination;
                    } else {
                        this.$message.error(msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },

            quit() {
                this.id = null;
            },
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },
            change() {
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/edit-sort'
                    },
                    data: {
                        id: this.id,
                        sort: this.sort
                    },
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        this.id = null;
                        this.$message.success(e.data.msg);
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },
            toAdd(id) {
                navigateTo({
                    r: 'plugin/vip_card/mall/card/edit',
                    id: id
                });
            },

            edit() {
                navigateTo({
                    r: 'plugin/vip_card/mall/card/edit',
                });
            },

            destroy(row) {
                this.$confirm('确认删除该子卡？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/vip_card/mall/card/detail-destroy'
                        },
                        data: {
                            id: row.id
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    });
                })
            },


            remove() {
                let that = this;
                this.$confirm('确认删除主会员卡？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/vip_card/mall/card/destroy'
                        },
                        data: {},
                        method: 'post',
                    }).then(e => {
                        if (e.data.code === 0) {
                            that.$message.success(e.data.msg);
                            location.reload();
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                    });
                })
            },

            changeStatus(res) {
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/switch-detail-status'
                    },
                    data: {
                        id: res.id,
                        status: res.status != 0 ? 1 : 0
                    },
                    method: 'post',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },

            getList(page) {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/index',
                        page: page ? page : 1
                    },
                    method: 'get',
                }).then(e => {
                    this.listLoading = false;
                    this.list = e.data.data.list;
                    this.table_pagination = e.data.data.pagination;
                });
            },

            tablePageChange(page) {
                this.getList(page);
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

