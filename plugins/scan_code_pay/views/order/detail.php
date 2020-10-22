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
<div id="app" v-cloak v-loading="loading">
    <app-order-detail get-order-list-url="plugin/scan_code_pay/mall/order/index" :order="order" :active="active"></app-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
              loading: false,
              active:1,
              order:{},
            };
        },
        created() {
        },
        methods: {
        }
    })
</script>