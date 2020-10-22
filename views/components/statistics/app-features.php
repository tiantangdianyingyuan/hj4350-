<?php

$mchId = Yii::$app->user->identity->mch_id;
?>
<style>
    .app-features .features-box {
        cursor: pointer;
        width: 20%;
    }

    .app-features .features-box img {
        height: 30px;
        width: 30px;
        display: block;
    }

    .app-features .features-box div {
        margin-left: 5px;
    }
</style>
<template id="app-features">
    <div class="app-features">
        <el-card shadow="never" style="margin-bottom: 10px">
            <div slot="header">
                <span>常用功能</span>
            </div>
            <div flex style="padding: 14px 0">
                <div class="features-box"
                     v-for="features in commonMenus"
                     @click="$navigate(Object.assign({r:features.route},features.params),true)"
                     flex="dir:left cross:center"
                >
                    <img :src="features.pic_url" alt="">
                    <div>{{features.name}}</div>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script>
    Vue.component('app-features', {
        template: '#app-features',
        data() {
            return {
                commonMenus: [],
                featuresList: [{
                    'image_url': 'statics/img/mall/statistic/function_icon_add.png',
                    'name': '添加商品',
                    'path': 'mall/goods/edit',
                    'is_show': true,
                }, {
                    'image_url': 'statics/img/mall/statistic/function_icon_Decoration_.png',
                    'name': '店铺装修',
                    'path': 'mall/index/setting',
                    'is_show': "<?= !$mchId?>",
                }, {
                    'image_url': 'statics/img/mall/statistic/function_icon_order.png',
                    'name': '订单管理',
                    'path': 'mall/order/index',
                    'is_show': true,
                }, {
                    'image_url': 'statics/img/mall/statistic/function_icon_Plugin.png',
                    'name': '插件中心',
                    'path': 'mall/plugin/index',
                    'is_show': "<?= !$mchId?>",
                }, {
                    'image_url': 'statics/img/mall/statistic/function_coupon_icon.png',
                    'name': '优惠券',
                    'path': 'mall/coupon/index',
                    'is_show': "<?= !$mchId?>",
                }],
            }
        },
        mounted() {
            this.getData();
        },
        methods: {
            getData() {
                request({
                    params: {
                        r: 'mall/data-statistics/plugin-menus',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.commonMenus = e.data.common_menus;
                    }
                })
            },
        }
        }
    )
</script>