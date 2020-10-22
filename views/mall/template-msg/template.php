<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 14:11
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<div id="app" v-cloak>
    <app-template url="<?= $url?>" add-url="<?= $addUrl?>"
                  submit-url="<?= $submitUrl?>"></app-template>
</div>
<script>
    const app = new Vue({
        el: '#app',
    });
</script>
