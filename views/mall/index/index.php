<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/8 18:12
 */
Yii::$app->loadViewComponent('app-test');
?>
<div id="app" v-cloak>
    <app-test></app-test>
    <div v-loading="loading">
        <template v-if="mall">
            <h1>{{mall.name}}</h1>
            <el-button @click="$navigate({r:'mall/demo/index'})">列表样式规范</el-button>
            <el-button @click="$navigate({r:'mall/demo/edit'})">表单样式</el-button>
        </template>
    </div>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                mall: null,
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'mall/index/index',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.mall = e.data.data.mall;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
        }
    });
</script>
