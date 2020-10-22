<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>

</style>

<template id="app-header">
    <div class="app-header" flex="dir:left box:first cross:center">
        <span><slot></slot></span>
        <div flex="dir:right">
            <form target="_blank" :action="url" method="post">
                <div>
                    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <input name="flag" type="hidden" value="EXPORT">
                    <input v-for="(item,index) in search"
                           :name="index"
                           type="hidden"
                           :value="item">
                    <slot name="other"></slot>
                </div>
                <button type="submit" class="el-button el-button--primary el-button--small">导出全部</button>
            </form>
        </div>
    </div>
</template>

<script>
    Vue.component('app-header', {
        template: '#app-header',
        props: {
            url: {
                type: String,
                default: ''
            },
            newSearch: {
                type: String,
                default: '',
            }
        },
        watch: {
            newSearch: function (newVal) {
                let self = this;
                let newSearch = JSON.parse(newVal);
                self.search = newSearch;
            }
        },
        data() {
            return {
                search: [],
            }
        }
    })
</script>
