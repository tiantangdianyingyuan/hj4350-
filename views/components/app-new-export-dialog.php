<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$url = Yii::$app->urlManager->createUrl(Yii::$app->controller->route);
?>
<style>
    .app-new-export-dialog .el-dialog__body .el-checkbox + .el-checkbox {
        margin-left: 0;
    }

    .app-new-export-dialog .el-date-editor .el-range-separator {
        padding: 0;
    }

    .app-new-export-dialog .modal-body {
        border: 1px solid #F0F2F7;
    }

    .app-new-export-dialog .all-choose {
        height: 50px;
        line-height: 50px;
        background-color: #F3F5F6;
        width: 100%;
        padding-left: 20px;
    }

    .app-new-export-dialog .choose-list {
        padding: 10px 25px 20px;
    }

    .app-new-export-dialog .choose-list .export-checkbox {
        width: 135px;
        height: 30px;
        line-height: 30px;
    }
</style>
<template id="app-new-export-dialog">
    <div class="app-new-export-dialog" style="display: inline-block">
        <el-button @click="confirm" type="primary" size="small">{{text}}</el-button>
        <el-dialog
                title="选择导出信息"
                :visible.sync="dialogVisible"
                width="50%">
            <el-form>
                <div class="modal-body">
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
                    <el-button size="small" type="primary" @click="submit">导出</el-button>
                </div>
            </el-form>
        </el-dialog>

        <el-dialog
                :title="title"
                :visible.sync="progressBarVisible"
                width="25%">
                <div class="modal-body">
                    <el-progress :text-inside="true" :stroke-width="18" :percentage="percentage"></el-progress>
                </div>
                <div v-if="percentage == 100 && download_url" flex="dir:right" style="margin-top: 20px;">
                    <a target="_blank" :href="download_url">
                    <input οnclick="this.parentNode.click();" type="button" value="点击下载" class="el-button el-button--primary el-button--small">
                    </a>
                </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-new-export-dialog', {
        template: '#app-new-export-dialog',
        props: {
            text: {
                type: String,
                default: '批量导出'
            },
            title: {
                type: String,
                default: '订单导出'
            },
            directly: {
                type: Boolean,
                default: false
            },
            field_list: Array,
            params: Object,
            action_url: {
                type: String,
                default: '<?=$url?>'
            },//跳转路由
        },
        data() {
            return {
                dialogVisible: false,
                isIndeterminate: false,
                checkAll: false,
                checkedFields: [],

                progressBarVisible: false,
                percentage: 0,
                number: 0,
                download_url: '',
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
                if(this.directly) {
                    this.checkedFields = this.field_list
                    this.submit();
                }else {
                    this.dialogVisible = true;
                }
                this.$emit('selected');
            },
            submit() {
                let self = this;
                self.progressBarVisible = true;
                self.dialogVisible = false;

                self.params.flag = 'EXPORT';
                self.params.fields = self.checkedFields;
                self.params.page = 1;

                self.percentage = 0;

                self.submitRequest();
            },
            submitRequest() {
                let self = this;
                request({
                    params: {
                        r: self.action_url
                    },
                    data: self.params,
                    method: 'post'
                }).then(e => {
                    if (e.data.code == 0) {
                        let response = e.data.data;
                        let pagination = response.pagination;

                        if (response.is_finish == false) {
                            self.number = parseFloat((100 / pagination.page_count).toFixed(2));
                            self.percentage = parseFloat((self.percentage + self.number).toFixed(2));
                            self.params.page = self.params.page + 1;
                            self.submitRequest();
                        } else {
                            self.percentage = 100;
                            self.download_url = response.download_url;
                        }
                    }

                }).catch(e => {
                });
            }
        },
        created() {
            console.log(self.action_url)
        }
    });
</script>
