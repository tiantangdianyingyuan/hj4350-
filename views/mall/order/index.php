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
        <app-order :active-name="activeName" :is-goods-type="true" :is-show-profit="true" :is-show-order-plugin="true"></app-order>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                activeName: getQuery('status') === null ? '-1' : getQuery('status')
            };
        },
        created() {

        },
        methods: {
        }
    });
</script>