<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>

</style>

<template id="app-edit-seller-remark">
    <div class="app-edit-seller-remark">
        <!-- 备注 -->
        <el-dialog :title="title" :visible.sync="dialogVisible" width="30%" @close="closeDialog">
            <el-form>
                <el-form-item :label="content">
                    <el-input type="textarea" v-model="seller_remark" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible = false">取消</el-button>
                    <el-button size="small" type="primary" :loading="submitLoading" @click="toSumbit">确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-edit-seller-remark', {
        template: '#app-edit-seller-remark',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            order: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            url: {
                type: String,
                default: 'mall/order/seller-remark'
            }
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openDialog()
                }
            }
        },
        data() {
            return {
                dialogVisible: false,
                seller_remark: '',
                title: '',
                content: '添加备注',
                submitLoading: false,
            }
        },
        methods: {
            // 打开备注
            openDialog() {
                this.title = '备注';
                this.seller_remark = this.order.seller_remark;
                if (this.seller_remark) {
                    this.content = '修改备注'
                }
                this.dialogVisible = true;
            },
            closeDialog() {
                this.$emit('close')
            },
            toSumbit() {
                this.submitLoading = true;
                request({
                    params: {
                        r: this.url
                    },
                    data: {
                        order_id: this.order.id,
                        seller_remark: this.seller_remark,
                        remark: this.seller_remark,// TODO 部分接口参数不一样,可优化接口
                    },
                    method: 'post'
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.dialogVisible = false;
                        this.$message.success(e.data.msg);
                        this.$emit('submit')
                    } else {
                        this.$message.error(e.data.msg);
                    }

                }).catch(e => {
                });
            }
        }
    })
</script>