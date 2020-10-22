<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<div id="app">
    <app-template url="plugin/aliapp/template-msg/setting" submit-url='plugin/aliapp/template-msg/setting'
                  sign="aliapp"
                  add-url="plugin/aliapp/template-msg/add-template" :one-key="isShow"></app-template>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                isShow: false,
            };
        },
    });
</script>
