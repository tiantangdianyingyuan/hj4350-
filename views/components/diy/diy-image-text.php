<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>

<style>
    .diy-image-text {
        width: 100%;
        height: 500px;
        overflow:hidden;
        overflow-y: auto;
    }
</style>

<template id="diy-image-text">
    <div>
        <div class="diy-component-preview">
            <div class="diy-image-text">
                <div v-if="data.content" v-html="data.content"></div>
                <div v-else flex="main:center" style="line-height: 500px;color: rgb(173, 177, 184);">图文详情</div>
            </div>
        </div>
        <div class="diy-component-edit">
            <app-rich-text style="width: 455px" v-model="data.content"></app-rich-text>
        </div>
    </div>
</template>

<script>
    Vue.component('diy-image-text', {
        template: '#diy-image-text',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    content: '',
                },
            }
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
    });
</script>