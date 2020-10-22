<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/14 13:49
 */
?>
<template id="app-ellipsis">
    <div class="app-ellipsis">
        <div style="word-break: break-all;" v-line-clamp="line">
            <slot></slot>
        </div>
    </div>
</template>
<script>
Vue.use(VueLineClamp, {
    importCss: true,
});

Vue.component('app-ellipsis', {
    template: '#app-ellipsis',
    props: {
        line: Number,
    },
    computed: {
    },
    methods: {},
});
</script>
