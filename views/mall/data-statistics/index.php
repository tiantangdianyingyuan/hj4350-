<?php defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
$mch_id = Yii::$app->user->identity->mch_id;
Yii::$app->loadViewComponent('statistics/app-search');
Yii::$app->loadViewComponent('statistics/app-table');
Yii::$app->loadViewComponent('statistics/app-notice');
Yii::$app->loadViewComponent('statistics/app-features');
Yii::$app->loadViewComponent('statistics/app-order-info');
Yii::$app->loadViewComponent('statistics/app-manage');
Yii::$app->loadViewComponent('statistics/app-plugin');
?>
<style>
    .el-tabs__nav-wrap::after {
        height: 1px;
    }

    .table-body {
        padding-top: 50px;
        background-color: #fff;
        position: relative;
        padding-bottom: 50px;
        margin-bottom: 10px;
        border: 1px solid #EBEEF5;
    }

    .table-body .el-tabs {
        margin-left: 10px;
    }

    .table-body .el-tabs__nav-scroll {
        width: 120px;
        margin-left: 30px;
    }

    .table-body .el-tabs__item {
        height: 32px;
        line-height: 32px;
    }

    .tab-pay {
        position: absolute;
        bottom: 0;
        right: 50px;
    }

    .tab-pay .el-tabs__item {
        height: 56px;
        line-height: 56px;
    }

    .table-area {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
    }

    .table-area .el-card {
        width: 49.5%;
        color: #303133;
    }

    .el-tabs__header {
        margin-bottom: 0 !important;
    }

    .el-card__header {
        position: relative;
    }

    .sort-active {
        color: #3399ff;
    }

    .select-item {
        border: 1px solid #3399ff;
        margin-top: -1px !important;
    }

    .el-popper .popper__arrow, .el-popper .popper__arrow::after {
        display: none;
    }

    .el-select-dropdown__item.hover, .el-select-dropdown__item:hover {
        background-color: #3399ff;
        color: #fff;
    }

    .table-area .el-card__header {
        padding: 14px 20px;
    }

    .text-omit {
        width: 380px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .item {
        color: #92959B;
        margin-left: 1px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>数据概况</span>
        </div>
        <!-- 公告 -->
        <app-notice></app-notice>
        <!-- 常用功能 -->
        <app-features></app-features>
        <!-- 订单统计 -->

        <app-order-info :is-user="Boolean(<?= $mch_id == 0 ?>)"></app-order-info>
        <!-- 经营状况 -->
        <app-manage></app-manage>
        <!-- 趋势概况 -->
        <app-table></app-table>
        <!-- 排行 -->
        <div class="table-area" style="margin-bottom: 10px">
            <el-card shadow="never">
                <div slot="header">
                    <form target="_blank" :action="goods_url" method="post">
                        <span style="float: left;height: 32px;line-height: 32px">商品购买力TOP排行</span>
                        <div>
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="flag" type="hidden" value="EXPORT">
                            <input name="date_start" type="hidden" :value="search.date_start">
                            <input name="date_end" type="hidden" :value="search.date_end">
                            <input name="mch_id" type="hidden" :value="search.mch_id">
                            <input name="goods_order" type="hidden" :value="sort_order">
                        </div>
                        <div flex="dir:right" style="">
                            <button type="submit" class="el-button el-button--primary el-button--small">导出TOP100
                            </button>
                        </div>
                    </form>
                </div>
                <el-table v-loading="goods_loading" :row-style="{height:'63px'}" @sort-change="changeGoods"
                          :header-cell-style="{background:'#F3F5F6','color':'#303133'}" :data="goods_top_list">
                    <el-table-column align="center" label="排名">
                        <template slot-scope="scope">
                            <img style="margin-top: 3px" v-if="scope.$index == 0"
                                 src="statics/img/mall/statistic/first.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 1"
                                 src="statics/img/mall/statistic/sec.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 2"
                                 src="statics/img/mall/statistic/third.png" alt="">
                            <span v-else-if="scope.$index < 9">0{{scope.$index+1}}</span>
                            <span v-else>{{scope.$index+1}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" width="400" label="商品">
                        <template slot-scope="scope">
                            <div class="text-omit">{{scope.row.name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_price" label="销售额" width="150" sortable='custom'
                                     :label-class-name="goodsProp == 'total_price' ? 'sort-active': ''">
                    </el-table-column>
                    <el-table-column prop="num" label="销量" width="150" sortable='custom'
                                     :label-class-name="goodsProp == 'num' ? 'sort-active': ''">
                    </el-table-column>
                </el-table>
            </el-card>
            <el-card shadow="never">
                <div slot="header">
                    <form target="_blank" :action="user_url" method="post">
                        <span style="float: left;height: 32px;line-height: 32px">用户购买力TOP排行</span>
                        <div>
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="flag" type="hidden" value="EXPORT">
                            <input name="date_start" type="hidden" :value="search.date_start">
                            <input name="date_end" type="hidden" :value="search.date_end">
                            <input name="mch_id" type="hidden" :value="search.mch_id">
                            <input name="user_order" type="hidden" :value="sort_order">
                        </div>
                        <div flex="dir:right" style="">
                            <button type="submit" class="el-button el-button--primary el-button--small">导出TOP100
                            </button>
                        </div>
                    </form>
                </div>
                <el-table v-loading="user_loading" :row-style="{height:'63px'}" @sort-change="changeUser"
                          :header-cell-style="{background:'#F3F5F6','color':'#303133'}" :data="user_top_list">
                    <el-table-column align="center" label="排名">
                        <template slot-scope="scope">
                            <img style="margin-top: 3px" v-if="scope.$index == 0"
                                 src="statics/img/mall/statistic/first.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 1"
                                 src="statics/img/mall/statistic/sec.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 2"
                                 src="statics/img/mall/statistic/third.png" alt="">
                            <span v-else-if="scope.$index < 9">0{{scope.$index+1}}</span>
                            <span v-else>{{scope.$index+1}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="用户" width="400">
                        <template slot-scope="scope">
                            <app-image style="margin-right: 10px;float: left;" :src="scope.row.avatar" width="32px"
                                       height="32px">
                            </app-image>
                            <span class="text-omit"
                                  style="height: 32px;line-height: 32px;display: inline-block;width: 280px">{{scope.row.nickname}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_price" label="支付金额" width="150"
                                     :label-class-name="userProp == 'total_price' ? 'sort-active': ''"
                                     sortable='custom'>
                    </el-table-column>
                    <el-table-column prop="num" label="支付件数" width="150"
                                     :label-class-name="userProp == 'num' ? 'sort-active': ''" sortable='custom'>
                    </el-table-column>
                </el-table>
            </el-card>
        </div>
        <!-- 插件统计 -->
        <app-plugin :mch-id="<?= $mch_id ?>"></app-plugin>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                // 加载动画
                loading: false,
                goods_loading: false,
                user_loading: false,
                // 销售情况选中情况

                order: true,
                price: true,
                people: true,
                num: true,
                // 日期选中状态
                activeName: '',
                // 搜索内容
                search: {
                    mch: null,
                    time: null,
                    date_start: null,
                    date_end: null,
                    platform: '',
                },
                // 店铺列表
                mch_list: [],

                // 商品排行
                goods_top_list: [],
                // 用户排行
                user_top_list: [],
                sort_order: null,
                userProp: '',
                goodsProp: '',
                user_url: '<?= $urlManager->createUrl('mall/data-statistics/users_top')?>',
                goods_url: '<?= $urlManager->createUrl('mall/data-statistics/goods_top')?>',
                is_mch_role: true,
            };
        },
        methods: {
            // 修改用户购买力排行榜排序
            changeUser(column) {
                this.user_loading = true;
                this.goodsProp = null;
                if (column && column.order == "descending") {
                    this.sort_order = column.prop + ' DESC'
                } else if (column && column.order == "ascending") {
                    this.sort_order = column.prop + ' ASC'
                }
                this.userProp = column ? column.prop : '';
                let params = {
                    r: 'mall/data-statistics/users_top',
                }
                let para = {
                    mch_id: this.search.mch,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    user_order: this.sort_order,
                    platform: this.search.platform,
                }
                request({
                    params: params,
                    data: para,
                    method: 'post',
                }).then(res => {
                    this.user_loading = false;
                    if (res.data.code == 0) {
                        this.user_top_list = res.data.data.user_top_list;
                    }
                }).catch(res => {
                    this.user_loading = false;
                })
            },
            // 修改商品排序
            changeGoods(column) {
                this.userProp = null;
                this.goods_loading = true;
                if (column.order == "descending") {
                    this.sort_order = column.prop + ' DESC'
                } else if (column.order == "ascending") {
                    this.sort_order = column.prop + ' ASC'
                }
                this.goodsProp = column.prop;
                let params = {
                    r: 'mall/data-statistics/goods_top',
                }
                let para = {
                    mch_id: this.search.mch,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    goods_order: this.sort_order,
                }
                request({
                    params: params,
                    data: para,
                    method: 'post',
                }).then(res => {
                    this.goods_loading = false;
                    if (res.data.code === 0) {
                        this.goods_top_list = res.data.data.goods_top_list;
                    }
                }).catch(res => {
                    this.goods_loading = false;
                })
            },
            // 获取数据
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/data-statistics/index',
                    },
                    data: {
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        mch_id: this.search.mch,
                        platform: this.search.platform,
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.mch_list = e.data.data.mch_list;
                        this.goods_top_list = e.data.data.goods_top_list;
                        this.user_top_list = e.data.data.user_top_list;
                        this.table_list = e.data.data.table_list;
                        this.is_mch_role = e.data.data.is_mch_role;
                        this.getTable();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        },
        created() {
            this.getList();
        },
    })
</script>