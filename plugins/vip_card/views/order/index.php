<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadViewComponent('app-order');
?>
<style>
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <app-order
                :tabs="tabs"
                :select-list="selectList"
                active-name="3"
                :is-show-print="false"
                :is-show-order-type="false"
                :is-show-order-status="false"
                order-url="plugin/vip_card/mall/order"
                recycle-url="plugin/vip_card/mall/order/destroy-all">
            <template slot="orderTag" slot-scope="order">
                <el-tag size="small" type="success" v-if="order.order.is_sale == 1">已完成</el-tag>
            </template>
            <template slot="attr" slot-scope="order">
                小标题：
                <el-tag size="mini" style="margin-right: 5px;">
                    {{order.item.extra.card_detail_name}}
                </el-tag>
            </template>
        </app-order>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                tabs: [
                    {value: '3', name: '已完成'},
                    {value: '7', name: '回收站'},
                ],
                selectList: [
                    {value: '1', name: '订单号'},
                    {value: '2', name: '用户名'},
                    {value: '3', name: '用户ID'},
                    {value: '4', name: '小标题'},
                ]
            };
        },
        created() {
        },
        methods: {}
    });
</script>
