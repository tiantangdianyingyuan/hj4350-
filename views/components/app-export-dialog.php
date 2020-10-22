<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$url = Yii::$app->urlManager->createUrl(Yii::$app->controller->route);
?>
<style>
    .app-export-dialog .el-dialog__body .el-checkbox + .el-checkbox {
        margin-left: 0;
    }

    .app-export-dialog .el-date-editor .el-range-separator {
        padding: 0;
    }

    .app-export-dialog .modal-body {
        border: 1px solid #F0F2F7;
    }

    .app-export-dialog .all-choose {
        height: 50px;
        line-height: 50px;
        background-color: #F3F5F6;
        width: 100%;
        padding-left: 20px;
    }

    .app-export-dialog .choose-list {
        padding: 10px 25px 20px;
    }

    .app-export-dialog .choose-list .export-checkbox {
        width: 135px;
        height: 30px;
        line-height: 30px;
    }
</style>
<template id="app-export-dialog">
    <div class="app-export-dialog" style="display: inline-block">
        <el-button @click="confirm" type="primary" size="small">批量导出</el-button>
        <el-dialog
                title="选择导出信息"
                :visible.sync="dialogVisible"
                width="50%">
            <form target="_blank" :action="action_url" method="post">
                <div class="modal-body">
                    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <input name="flag" value="EXPORT" type="hidden">
                    <input name="fields" :value="checkedFields" type="hidden">
                    <input v-for="(value,key,index) in params" :name="key" :value="value" type="hidden">
                    <el-checkbox class="all-choose" :indeterminate="isIndeterminate" v-model="checkAll"
                                 @change="exportCheckAll">全选
                    </el-checkbox>
                    <el-checkbox-group class="choose-list" v-model="checkedFields" @change="exportCheck">
                        <el-checkbox class="export-checkbox" v-for="item in field_list" :key="item.key"
                                     :label="item.key">{{item.value}}
                        </el-checkbox>
                    </el-checkbox-group>
                </div>
                <div flex="dir:right" style="margin-top: 20px;">
                    <button type="submit" class="el-button el-button--primary el-button--small">导出</button>
                </div>
            </form>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-export-dialog', {
        template: '#app-export-dialog',
        props: {
            field_list: Array,
            params: Object,
            action_url: {
                type: String,
                default: '<?= $url ?>'
            },//跳转路由
        },
        data() {
            return {
                dialogVisible: false,
                isIndeterminate: false,
                checkAll: false,
                checkedFields: [],

            }
        },
        computed: {},
        methods: {
            exportCheckAll(val) {
                if (val) {
                    let field_list = this.field_list;
                    let array = [];
                    field_list.forEach((item, index) => {
                        array.push(item['key']);
                    });
                    this.checkedFields = array;
                } else {
                    this.checkedFields = [];
                }
                this.isIndeterminate = false;
            },
            exportCheck(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.field_list.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.field_list.length;
            },
            confirm() {
                this.dialogVisible = true;
                this.$emit('selected');
            }
        },
        created() {
            // TODO 此处需获取网址上的所有参数、且需考虑参数名重复问题
            // TODO 已无作用
            this.params.status = getQuery('status');
        }
    });
</script>
