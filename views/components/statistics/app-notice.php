<?php
$user = Yii::$app->user->identity->identity;
$mch_id = Yii::$app->user->identity->mch_id;

?>
<style>
    .app-notice .table-body {
        padding-top: 50px;
        background-color: #fff;
        position: relative;
        padding-bottom: 50px;
        margin-bottom: 10px;
        border: 1px solid #EBEEF5;
    }

    .app-notice .table-body.notice {
        background: #F5FAFF;
        padding: 0 20px;
        overflow-x: auto;
    }

    .app-notice .notice .blue {
        color: #409EFF;
        width: 84px;
    }

    .app-notice .notice .notice-content {
        max-width: 722px;

    }

    .app-notice .notice .notice-content > div {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<template id="app-notice">
    <div class="app-notice" v-if="(mallAccount === 'sub-account' || mallAccount === 'super-account') && noticeForm.list && noticeForm.list.length">
        <div class="table-body notice">
            <div v-for="notice in noticeForm.list" flex="dir:left cross:center" style="margin: 20px 0">
                <div v-if="notice.type === `update`" flex="dir:left cross:center">
                    <img src="statics/img/mall/statistic/icon_notice_1.png" alt=""/>
                    <span class="blue">【更新公告】</span>
                </div>
                <div v-if="notice.type == `important`" flex="dir:left cross:center">
                    <img src="statics/img/mall/statistic/icon_notice_2.png" alt=""/>
                    <span class="blue">【重要通知】</span>
                </div>
                <div v-if="notice.type == `urgent`" flex="dir:left cross:center">
                    <img src="statics/img/mall/statistic/icon_warning.png" alt=""/>
                    <span class="blue">【紧急维护】</span>
                </div>
                <div class="notice-content">
                    <div v-text="notice.content_text"></div>
                </div>
                <el-button style="padding: 0;margin-left: 12px" type="text" @click="openDialog(notice)">查看详情</el-button>
            </div>
            <div v-if="false && noticeForm.mall_notice" flex="dir:left cross:center" style="margin: 20px 0">
                <img src="statics/img/mall/statistic/icon_remind.png" alt=""/>
                <div style="color: #FF0000;margin-left: 8px">{{noticeForm.mall_notice}}</div>
            </div>
        </div>
    </div>
</template>

<script>
    Vue.component('app-notice', {
        template: '#app-notice',
        data() {
            return {
                noticeForm: {
                    list: [],
                    mall_notice: '',
                },
            }
        },
        mounted() {
            this.getNotice();
        },
        computed: {
            mallAccount() {
                if ("<?= $mch_id ?>" > 0) {
                    return 'mch';
                }
                if ("<?= $user['is_admin'] ?>" == 1) {
                    return 'sub-account';
                }
                if ("<?= $user['is_super_admin'] ?>" == 1) {
                    return 'super-account';
                }
                if ("<?= $user['is_operator'] ?>" == 1) {
                    return 'operator';
                }
            },
        },
        methods: {
            openDialog(row) {
                navigateTo({r: 'mall/notice/detail', id: row.id});
            },
            getNotice() {
                request({
                    params: {
                        r: 'mall/data-statistics/notice',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.noticeForm = e.data.data;
                    }
                })
            },
        }
    })
</script>