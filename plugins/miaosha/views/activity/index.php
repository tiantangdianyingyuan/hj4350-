<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
Yii::$app->loadViewComponent('app-activity-list');
?>

<div id="app" v-cloak>
    <app-activity-list
        activity_name="秒杀"
        :tabs="tabs"
        :no_edit="1"
        sign="miaosha"
        activity_url="plugin/miaosha/mall/activity/index"
        activity_detail_url="plugin/miaosha/mall/activity/detail"
        edit_activity_url='plugin/miaosha/mall/activity/edit'
        edit_activity_status_url="plugin/miaosha/mall/activity/batch-update-status"
        edit_activity_destroy_url="plugin/miaosha/mall/activity/batch-destroy"
    ></app-activity-list>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                tabs: [
                    {
                        name: '全部',
                        value: '1'
                    },
                    {
                        name: '未开始',
                        value: '2'
                    },
                    {
                        name: '进行中',
                        value: '3'
                    },
                    {
                        name: '已结束',
                        value: '4'
                    },
                    {
                        name: '已下架',
                        value: '5'
                    }
                ]
            };
        },
        created() {
        },
        methods: {
        }
    });
</script>
