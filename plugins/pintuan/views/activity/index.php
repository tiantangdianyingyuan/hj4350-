<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
Yii::$app->loadViewComponent('app-activity-list');
?>
<style>
    .groups {
        cursor: pointer;
        color: #409EFF;
    }
    
</style>
<div id="app" v-cloak>
    <app-activity-list
        activity_name="拼团"
        :tabs="tabs"
        newActiveName="1"
        activity_url="plugin/pintuan/mall/activity/index"
        activity_detail_url="plugin/pintuan/mall/activity/detail"
        edit_activity_url='plugin/pintuan/mall/activity/edit'
        edit_activity_status_url="plugin/pintuan/mall/activity/batch-update-status"
        edit_activity_destroy_url="plugin/pintuan/mall/activity/batch-destroy"
    >
        <el-table-column
             slot="after_status"
             prop="groups"
             label="拼团组/拼团价"
             width="200"
        >
            <template slot-scope="scope"><div flex="dir:top"  >
                    <div v-for="(it, i) in scope.row.groups.slice(0,2)">
                        <el-tag  style="margin-bottom: 10px">
                            {{it.people_num}}人|￥{{it.price}}
                        </el-tag>
                    </div>
                </div>
                    <el-popover
                        placement="top-start"
                        width="200"
                        trigger="hover">
                        <div v-for="(it, i) in scope.row.groups">
                            <el-tag  style="margin-bottom: 10px">
                                {{it.people_num}}人|￥{{it.price}}
                            </el-tag>
                        </div>
                            <span v-if="scope.row.groups.length > 2" class="groups" slot="reference">查看全部</span>
                    </el-popover>
            </template>
        </el-table-column>
    </app-activity-list>
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
                        name: '下架中',
                        value: '5'
                    }
                ],
            };
        },
        created() {
        },
        methods: {
        }
    });
</script>
