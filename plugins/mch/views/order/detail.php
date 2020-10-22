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
<div id="app" v-cloak>
    <app-order-detail
            get-order-list-url="plugin/mch/mall/order/index"
            :is-show-edit-address="false"
            :is-show-cancel="false"
            :is-show-remark="false"
            :is-show-finish="false"
            :is-show-confirm="false"
            :is-show-print="false"
            :is-show-clerk="false"
            :is-show-send="false">
    </app-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {};
        },
        created() {
        },
        methods: {}
    })
</script>