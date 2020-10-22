<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/11/22
 * Time: 9:55
 */

Yii::$app->loadViewComponent('app-integral');
?>

<style>

</style>
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0;">
        <div slot="header">
            <span>用户积分设置</span>
        </div>
        <el-row>
            <el-col :span="24">
                <app-integral></app-integral>
            </el-col>
        </el-row>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {

            };
        },
        methods: {
            handleClick(tab, event) {
                console.log(tab, event);
            },
        }
    });
</script>

