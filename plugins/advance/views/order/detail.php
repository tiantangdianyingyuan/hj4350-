<?php
Yii::$app->loadViewComponent('app-order-detail');
?>

<style>
    [v-cloak] {
        display: none;
    }

    .table-body {
        padding: 10px;
        background-color: #fff;
    }
</style>

<div id="app" v-cloak>
    <app-order-detail
            get-order-list-url="plugin/advance/mall/order/index"
            @get-detail="getDetail"
            :active="active">
    </app-order-detail>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                active: 2
            }
        },
        created() {
        },
        methods: {
            getDetail(order) {
                if (order.cancel_status == 1) {
                    this.active = 5;
                }
                if (order.is_pay == 1) {
                    this.active = 2;
                }
                if (order.is_send == 1) {
                    this.active = 3;
                }
                if (order.is_confirm == 1) {
                    this.active = 4;
                }
                if (order.is_sale == 1) {
                    this.active = 5;
                }
            }
        }
    })
</script>

