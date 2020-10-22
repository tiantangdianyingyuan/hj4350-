<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/15
 * Time: 11:42
 */
?>
<style>
    .app-test {
        background: #ff4544;
        padding: 10px 20px;
        color: #fff;
        border-radius: 2px;
    }
</style>
<template id="app-test">
    <div class="app-test">
        APPTEST组件
    </div>
</template>
<script>
    Vue.component('app-test', {
        template: '#app-test',
    });
</script>
