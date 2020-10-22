<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .app-user-batch {
        display: inline-block;
    }

    .app-user-batch .batch-box {
        margin-left: 10px;
    }

    .app-user-batch .batch-remark {
        margin-top: 5px;
        color: #999999;
        font-size: 14px;
    }

    .app-user-batch .select-count {
        font-size: 14px;
        margin-left: 10px;
    }

    .app-user-batch .batch-title {
        font-size: 18px;
    }

    .app-user-batch .batch-box-left {
        width: 120px;
        border-right: 1px solid #e2e2e2;
        padding: 0 20px;
    }

    .app-user-batch .batch-box-left div {
        padding: 5px 0;
        margin: 5px 0;
        cursor: pointer;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }

    .app-user-batch .batch-div-active {
        background-color: #e2e2e2;
    }

    .app-user-batch .el-dialog__body {
        padding: 15px 20px;
    }

    .app-user-batch .batch-box-right {
        padding: 5px 20px;
    }

    .app-user-batch .express-dialog .el-dialog {
        min-width: 250px;
    }

    .app-user-batch .add-express-rule {
        margin-left: 20px;
        cursor: pointer;
        color: #419EFB;
    }

    .app-user-batch .confine-box .label {
        margin-right: 10px;
    }
</style>


<template id="app-user-batch">
    <div class="app-user-batch">
        <el-button @click="batchSetting" size="small" type="primary">批量设置</el-button>

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
                        <div v-if="currentBatch === 'member-level'">
                            <el-form-item label-width="120px" label="选择会员等级">
                                <el-select size="small" v-model="member.level" placeholder="请选择会员等级">
                                    <el-option key="普通用户" label="普通用户" :value="0"></el-option>
                                    <el-option
                                      v-for="item in mallMembers"
                                      :key="item.name"
                                      :label="item.name"
                                      :value="item.level">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'integral'">
                            <el-form-item label="操作" prop="type" label-width="80px">
                                <el-radio v-model="integral.type" label="1">充值</el-radio>
                                <el-radio v-model="integral.type" label="2">扣除</el-radio>
                            </el-form-item>
                            <el-form-item label="积分数" prop="num" size="small" label-width="80px">
                                <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" v-model="integral.num" :max="999999999"></el-input>
                            </el-form-item>
                            <el-form-item label="充值图片" prop="pic_url" label-width="80px">
                                <app-attachment :multiple="false" :max="1" @selected="integralPicUrl">
                                    <el-button size="mini">选择文件</el-button>
                                </app-attachment>
                                <app-image width="80px" height="80px" mode="aspectFill" :src="integral.pic_url"></app-image>
                            </el-form-item>
                            <el-form-item label="备注" prop="remark" size="small" label-width="80px">
                                <el-input v-model="integral.remark"></el-input>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'balance'">
                            <el-form-item label="操作" prop="type" label-width="80px">
                                <el-radio v-model="balance.type" label="1">充值</el-radio>
                                <el-radio v-model="balance.type" label="2">扣除</el-radio>
                            </el-form-item>
                            <el-form-item label="金额" prop="num" size="small" label-width="80px">
                                <el-input type="number" v-model="balance.price" :max="99999999"></el-input>
                            </el-form-item>
                            <el-form-item label="充值图片" prop="pic_url" label-width="80px">
                                <app-attachment :multiple="false" :max="1" @selected="integralPicUrl">
                                    <el-button size="mini">选择文件</el-button>
                                </app-attachment>
                                <app-image width="80px" height="80px" mode="aspectFill" :src="balance.pic_url"></app-image>
                            </el-form-item>
                            <el-form-item label="备注" prop="remark" size="small" label-width="80px">
                                <el-input v-model="balance.remark"></el-input>
                            </el-form-item>
                        </div>
                        <slot name="batch" :item="currentBatch"></slot>
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
    Vue.component('app-user-batch', {
        template: '#app-user-batch',
        props: {
            // 列表选中的数据
            chooseList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            /**
             * 批量设置参数
             * 参数例子：baseBatchList
             */
            batchList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            // 批量操作额外参数
            batchExtraParams: {
                type: Array,
                default: function () {
                    return []
                }
            },
            mallMembers: {
                type: Array,
                default: function() {
                    return []
                }
            }
        },
        data() {
            return {
                isAllChecked: false,
                btnLoading: false,
                dialogVisible: false,
                currentBatch: '',
                integral: {
                    pic_url: '',
                    num: '',
                    remark: '',
                    type: '1'
                },
                balance: {
                    pic_url: '',
                    price: '',
                    remark: '',
                    type: '1'
                },
                member: {
                    level: '',
                },
                dialogTitle: '',
                newBatchList: [],
                baseBatchList: [
                    {
                        name: '会员等级',
                        key: 'member-level',// 唯一
                    },
                    {
                        name: '积分',
                        key: 'integral',
                    },
                    {
                        name: '余额',
                        key: 'balance',
                    }
                ]
            }
        },
        methods: {
            checkChooseList() {
                if (this.isAllChecked) {
                    this.dialogTitle = '已选所有用户';
                    return true;
                }
                if (this.chooseList.length > 0) {
                    this.dialogTitle = '已选用户' + this.chooseList.length + '人';
                    return true;
                }
                this.$message.warning('请先勾选要设置的用户');
                return false;
            },
            batchAction(data) {
                let self = this;
                self.batchExtraParams.forEach(function (item) {
                    data.params[item.key] = item.value;
                });
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
                this.$emit('to-search')
            },
            dialogSubmit() {
                let self = this;
                let params = {
                    batch_ids: this.chooseList,
                };
                switch (this.currentBatch) {
                    case 'member-level':
                        if (self.member.level === '') {
                            self.$message.warning('请选择会员等级');
                            return false;
                        }
                        params.member_level = this.member.level;
                        var text = this.isAllChecked ? '警告:批量设置所有用户会员等级,是否继续' : '批量设置会员等级,是否继续';
                        this.batchAction({
                            url: 'mall/user/batch-update-member-level',
                            content: text,
                            params: params
                        });
                        break;
                    case 'integral':
                        var text = this.isAllChecked ? '警告:批量设置所有用户积分,是否继续' : '批量设置积分,是否继续';
                        params.pic_url = self.integral.pic_url;
                        params.num = self.integral.num;
                        params.remark = self.integral.remark;
                        params.type = self.integral.type;
                        this.batchAction({
                            url: 'mall/user/batch-update-integral',
                            content: text,
                            params: params
                        });
                        break;
                    case 'balance':
                        var text = this.isAllChecked ? '警告:批量设置所有用户余额,是否继续' : '批量设置余额,是否继续';
                        params.pic_url = self.balance.pic_url;
                        params.price = self.balance.price;
                        params.remark = self.balance.remark;
                        params.type = self.balance.type;
                        this.batchAction({
                            url: 'mall/user/batch-update-balance',
                            content: text,
                            params: params
                        });
                        break;
                    // 自定义批量设置
                    default:
                        self.batchList.forEach(function (item) {
                            if (self.currentBatch === item.key) {
                                Object.assign(params, item.params);
                                self.batchAction({
                                    url: item.url,
                                    content: item.content,
                                    params: params
                                });
                            }
                        });
                        break;
                }
            },
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
                self.batchList.forEach(function (item) {
                    self.newBatchList.push(item)
                });
                self.currentBatch = self.newBatchList[0].key;
                self.dialogVisible = true;
                self.checkedChange(self.isAllChecked);
            },
            checkedChange(e) {
                this.$emit('get-all-checked', e)
            },
            integralPicUrl(e) {
                if (e.length) {
                    this.integral.pic_url = e[0].url;
                }
            }
        },
        created() {

        }
    })
</script>