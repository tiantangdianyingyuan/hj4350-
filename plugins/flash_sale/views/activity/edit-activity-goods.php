<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-goods');
$mchId = Yii::$app->user->identity->mch_id;
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/flash_sale/mall/activity/index'})">
                        限时抢购
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>商品编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods
            :is_show="0"
            :is_info="0"
            :is_mch="is_mch"
            :mch_id="mch_id"
            :is_member="1"
            :is_video_url="0"
            :is_original_price="0"
            :is_copy_id="0"
            :is_cats="0"
            :is_name="0"
            :is_pic_url="0"
            :is_detail="0"
            sign="flash_sale"
            :is_show="0"
            :is_display_setting="0"
            :is_product_info="0"
            url="plugin/flash_sale/mall/activity/edit-activity-goods"
            get_goods_url="plugin/flash_sale/mall/activity/edit-activity-goods"
            :referrer="url"
            ref="appGoods">
            <template slot="member_route_setting">
                 <span class="red">注：必须在“
                    <el-button type="text" @click="$navigate({r: 'plugin/flash_sale/mall/setting'}, true)">限时抢购设置=>基本设置=>优惠叠加设置</el-button>
                    ”中开启，才能使用
                </span>
            </template>
        </app-goods>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                url: '',
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
                mch_id: <?= $mchId ?>,
                goods_id: null
            }
        },
        created() {
            if(getQuery('page') > 1) {
                this.url = {
                    r: 'plugin/flash_sale/mall/activity/edit-activity-goods',
                    page: getQuery('page')
                }
            }
            this.url = {
                r: 'plugin/flash_sale/mall/activity/edit',
                id: getQuery('activity_id'),
                edit: getQuery('edit')
            }
        },

    });
</script>
