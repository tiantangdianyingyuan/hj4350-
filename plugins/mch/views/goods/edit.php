<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-goods');
?>
<style>
    /* STOP */
    #pane-first,#pane-second {
        pointer-events: none !important;
    }
    .el-switch {
        opacity: .6;
    }
    /* input hidden */
    .el-input__inner {
        background-color: #F5F7FA;
        border-color: #E4E7ED;
        color: #C0C4CC;
        cursor: not-allowed
    }

    .el-form-item.is-success .el-input__inner, .el-form-item.is-success .el-input__inner:focus, .el-form-item.is-success .el-textarea__inner, .el-form-item.is-success .el-textarea__inner:focus {
        border-color: #E4E7ED
    }
    /* preview */
    .el-dialog__footer {
        opacity: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/goods/index'})">
                        商品管理
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods :is_member="0"
                   :is_cats="1"
                   :is_show="1"
                   :is_info="1"
                   :form="form"
                   :is_detail="1"
                   sign="mch"
                   :is_marketing="0"
                   :is_mch="is_mch"
                   :mch_id="mch_id"
                   :get_goods_url="'plugin/mch/mall/goods/edit/&mch_id=' + mch_id"
                   url="plugin/mch/mall/goods/edit"
                   ref="appGoods">
        </app-goods>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                url: 'plugin/mch/mall/goods/index',
                is_mch: 1,
                mch_id: parseInt(getQuery('mch_id')),
            }
        },
        created() {
            if (getQuery('page') > 1) {
                this.url = {
                    r: 'plugin/mch/mall/goods/index',
                    page: getQuery('page')
                }
            }
        },
        mounted(){
            let bottom_div= document.getElementsByClassName("bottom-div")[0];
            bottom_div.removeChild(bottom_div.children[0]);
        },
    });
</script>
