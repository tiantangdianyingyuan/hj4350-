<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

defined('YII_ENV') or exit('Access Denied');
$url = Yii::$app->urlManager->createUrl(Yii::$app->controller->route);
Yii::$app->loadViewComponent('statistics/app-header');
Yii::$app->loadViewComponent('statistics/app-table');
Yii::$app->loadViewComponent('statistics/app-manage');
Yii::$app->loadViewComponent('statistics/app-order-info');
$isAdmin = Yii::$app->user->identity->identity->is_admin;
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
    }
    .item {
        color: #92959B;
        margin-left: 1px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>门店统计</span>
        </div>
        <!-- 门店选择 -->
        <div class="table-body">
            <span>选择门店</span>
            <el-select
                    style="margin-left: 15px"
                    size="small"
                    v-model="search.mch_id"
            >
                <el-option
                        v-for="item in storeList"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                </el-option>
            </el-select>
        </div>
        <!-- 订单统计 -->
        <app-order-info :is-user="false" :is-goods="false" :store-id="search.mch_id"></app-order-info>
        <!-- 经营状况 -->
        <app-manage :store-id="search.mch_id"></app-manage>
        <!-- 支付数据 -->
        <app-table :is-show-type="false" :store-id="search.mch_id"></app-table>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                /** 搜索 **/
                storeList: null,
                search: {
                    mch_id: null,
                },
                listLoading: false,
            };
        },
        methods: {
            getStore() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/order-statistics/index',
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.storeList = e.data.data.store_list;
                        let currency = this.storeList[0];
                        if (currency) {
                            this.search.mch_id = currency.id;
                        }
                    }
                }).catch(e => {
                    this.listLoading = false;
                })
            }
        },
        mounted: function () {
            this.getStore();
        }
    });
</script>
