<?php defined('YII_ENV') or exit('Access Denied');?>
<div id="app" v-cloak>
    <app-banner url="plugin/lottery/mall/banner/index" submit_url="plugin/lottery/mall/banner/edit"></app-banner>
</div>
<script>
    const app = new Vue({
        el: '#app'
    })
</script>
