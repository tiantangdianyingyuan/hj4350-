<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadViewComponent('app-order-detail');
?>
<style>
    .bonus-price {
        color: #E6A23C;
    }
</style>
<div id="app" v-cloak v-loading="loading">
    <app-order-detail
            title-label="分红订单"
            get-detail-url="plugin/region/mall/order/detail"
            get-order-list-url="plugin/region/mall/order/index"
            :is-show-edit-address="false"
            :is-show-cancel="false"
            :is-show-remark="false"
            :is-show-finish="false"
            :is-show-confirm="false"
            :is-show-print="false"
            :is-show-clerk="false"
            :is-show-send="false"
            :is-show-steps="false"
            :is-new-request="false"
            :order-data="order">
        <template slot="steps">
            <el-steps :active="active" align-center>
                <el-step :title="active > 0 ? '已付款':'未付款'" v-if="order.cancel_status != 1 || order.is_pay == 1"
                         icon="iconfont icon-fukuan">
                    <template slot="description">
                        <div v-if="order.pay_time != '0000-00-00 00:00:00'">{{order.pay_time}}</div>
                        <div v-if="order.is_pay == 0 && order.pay_type != 2 && order.auto_cancel_time"
                             style="color: #ff4544">预计 {{order.auto_cancel_time}} 自动取消订单
                        </div>
                    </template>
                </el-step>

                <el-step :title="active > 1 ? '已完成':'未完成'" v-if="order.cancel_status != 1 || order.is_sale == 1"
                         icon="iconfont icon-icon-receive">
                    <template slot="description">
                        <div v-if="order.confirm_time != '0000-00-00 00:00:00' && order.is_sale == 1">
                            {{order.confirm_time}}
                        </div>
                        <div v-if="order.is_confirm == 1 && order.is_sale == 0 && order.auto_sales_time"
                             style="color: #ff4544">预计 {{order.auto_sales_time}} 自动完成订单
                        </div>
                    </template>
                </el-step>
            </el-steps>
        </template>
    </app-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                active: 1,
                order: {},
            };
        },
        created() {
        },
        methods: {
            //获取列表
            getDetail() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/region/mall/order/detail',
                        order_id: getQuery('order_id')
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.order = e.data.data.order;
                        if (this.order.is_pay == 1) {
                            this.active = 1;
                        }
                        if (this.order.is_sale == 1) {
                            this.active = 2;
                        }
                    } else {
                        this.$message.error(e.data.data.msg)
                    }
                }).catch(e => {
                });
            }
        }
    })
</script>