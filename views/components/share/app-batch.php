<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/10
 * Time: 11:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-batch {
    }

    .app-batch .batch-box {
        margin-left: 10px;
    }

    .app-batch .batch-remark {
        margin-top: 5px;
        color: #999999;
        font-size: 14px;
    }

    .app-batch .select-count {
        font-size: 14px;
        margin-left: 10px;
    }

    .app-batch .batch-title {
        font-size: 18px;
    }

    .app-batch .batch-box-left {
        width: 120px;
        border-right: 1px solid #e2e2e2;
        padding: 0 20px;
    }

    .app-batch .batch-box-left div {
        padding: 5px 0;
        margin: 5px 0;
        cursor: pointer;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }

    .app-batch .batch-div-active {
        background-color: #e2e2e2;
    }

    .app-batch .el-dialog__body {
        padding: 15px 20px;
    }

    .app-batch .batch-box-right {
        padding: 5px 20px;
    }

    .app-batch .express-dialog .el-dialog {
        min-width: 250px;
    }
</style>


<template id="app-batch">
    <div class="app-batch" flex="dir:left cross:center">
        <el-button type="primary" size="small" @click="batchSetting" style="padding: 9px 15px !important;" >批量设置</el-button>
        <el-dialog
            :visible.sync="dialogVisible"
            width="50%">
            <div slot="title">
                <div flex="dir:left">
                    <div class="batch-title">批量修改</div>
                    <div flex="cross:center" class="select-count">{{dialogTitle}}</div>
                </div>
                <div class="batch-remark">注：每次只能修改一项，修改后点击确定即可生效。如需修改多项，需多次操作。</div>
            </div>
            <div flex="dir:left box:first">
                <div class="batch-box-left" flex="dir:top">
                    <div v-for="(item, index) in newBatchList"
                         :key='item.key'
                         :class="{'batch-div-active': currentBatch === item.key ? true : false}"
                         @click="currentBatch = item.key"
                         flex="main:center">
                        {{item.name}}
                    </div>
                </div>
                <div class="batch-box-right">
                    <el-form>
                        <el-form-item hidden>
                            <el-input></el-input>
                        </el-form-item>
                        <template v-if="currentBatch === 'level'">
                            <el-form-item label="分销商等级">
                                <el-select size="small" v-model="level" class="select">
                                    <el-option :key="index" :label="item.name" :value="item.level"
                                               v-for="(item, index) in shareLevelList"></el-option>
                                </el-select>
                            </el-form-item>
                        </template>
                    </el-form>
                </div>
            </div>
            <div slot="footer">
                <el-button size="small" @click="dialogVisible = false">取 消</el-button>
                <el-button size="small" :loading="btnLoading" type="primary" @click="dialogSubmit">确 定
                </el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-batch', {
        template: '#app-batch',
        props: {
            // 列表选中的数据
            chooseList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            batchLevelUrl: {
                type: String,
                default: 'mall/share/batch-level',
            },
            shareLevelList: Array,
        },
        data() {
            return {
                isAllChecked: false,
                btnLoading: false,
                dialogVisible: false,
                currentBatch: '',
                dialogTitle: '',
                newBatchList: [],
                baseBatchList: [
                    {
                        name: '分销商等级',
                        key: 'level',// 唯一
                    },
                ],
                level: 0
            }
        },
        methods: {
            // 打开批量设置框
            batchSetting() {
                let self = this;
                if (!self.checkChooseList()) {
                    return false;
                }
                self.newBatchList = [];
                self.baseBatchList.forEach(function (item) {
                    self.newBatchList.push(item);
                });
                self.currentBatch = self.newBatchList[0].key;
                self.dialogVisible = true;
            },
            checkChooseList() {
                if (this.isAllChecked) {
                    this.dialogTitle = '已选所有分销商';
                    return true;
                }
                if (this.chooseList.length > 0) {
                    this.dialogTitle = '已选分销商' + this.chooseList.length + '个';
                    return true;
                }
                this.$message.warning('请先勾选要设置的分销商');
                return false;
            },
            batchAction(data) {
                let self = this;
                self.$confirm(data.content, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true
                    request({
                        params: {
                            r: data.url
                        },
                        data: data.params,
                        method: 'post'
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.dialogVisible = false;
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.$message.error(e.data.msg);
                        self.btnLoading = false;
                    });
                }).catch(() => {
                });
            },
            getList() {
                this.isAllChecked = false;
                this.$emit('to-search')
            },
            dialogSubmit() {
                let self = this;
                let params = {
                    batch_ids: this.chooseList,
                    is_all: this.isAllChecked ? 1 : 0,
                };
                switch (this.currentBatch) {
                    case 'level':
                        if (this.level === '') {
                            self.$message.warning('请选择分销商等级');
                            return false;
                        }
                        params.level = this.level;
                        this.batchAction({
                            url: self.batchLevelUrl,
                            content: '批量设置分销商等级,是否继续',
                            params: params
                        });
                        break;
                    default:
                        break;
                }
            },
        },
    })
</script>
