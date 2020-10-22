<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2019 浙江禾匠信息科技有限公司
 * author: fjt
 */
?>
<style>

</style>

<div id="app" v-cloak>
    <el-card show="never" v-loading="loading">
        <div slot="header"></div>
        <div slot="body"></div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: 'app',
        data() {
            return {
                loading: false,
            }
        }
    });

</script>
