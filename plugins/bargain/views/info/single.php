<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/17
 * Time: 16:19
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-info', __DIR__)
?>
<div id="app" v-cloak="">
    <app-info type="single" type="all"></app-info>
</div>
<script>
    const app = new Vue({
        el: '#app'
    });
</script>