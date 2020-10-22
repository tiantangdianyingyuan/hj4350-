<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<div id="app">
    <app-template url="plugin/bdapp/template-msg/setting" submit-url='plugin/bdapp/template-msg/setting'
                  sign="bdapp"
                  add-url="plugin/bdapp/template-msg/add-template"></app-template>
</div>
<script>
    const app = new Vue({
        el: '#app'
    });
</script>
