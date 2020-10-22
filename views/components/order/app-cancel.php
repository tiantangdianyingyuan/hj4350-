<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-cancel .el-dialog__body {
        padding: 20px 20px 30px;
    }

    .app-cancel .div-item {
        margin-bottom: 10px;
    }

    .app-cancel .image {
        width: 80px;
        height: 80px;
        margin-right: 5px;
        cursor: pointer;
    }

    .app-cancel .click-img {
        width: 100%;
    }
</style>

<template id="app-cancel">
    <div class="app-cancel">
        <!-- 备注 -->
        <el-dialog :title="title" :visible.sync="dialogVisible" width="30%" @close="closeDialog">
            <el-form>
                <template v-if="order.cancel_data && cancelType != 2">
                <div v-if="order.cancel_data.cause" flex="dir:left" class="div-item">
                    <div flex-box="0">退款理由：</div>
                    <div flex-box="1">{{order.cancel_data.cause}}</div>
                </div>
                <div flex="dir:left" class="div-item">
                    <div flex-box="0">退款金额：</div>
                    <div flex-box="1">￥{{order.total_pay_price}}</div>
                </div>
                <div v-if="order.cancel_data.remark" flex="dir:left" class="div-item">
                    <div flex-box="0">备注信息：</div>
                    <div flex-box="1">{{order.cancel_data.remark}}</div>
                </div>
                <div v-if="order.cancel_data.mobile" flex="dir:left" class="div-item">
                    <div flex-box="0">联系方式：</div>
                    <div flex-box="1">{{order.cancel_data.mobile}}</div>
                </div>
                <div v-if="order.cancel_data.image_list && order.cancel_data.image_list.length" flex="dir:left" class="div-item">
                    <div flex-box="0">图片凭证：</div>
                    <div flex-box="1">
                        <img @click="openBig(item)" class="image" :src="item" v-for="(item, index) in order.cancel_data.image_list">
                    </div>
                </div>
                </template>
                <el-form-item :label="content">
                    <el-input type="textarea" v-model="remark" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible = false">取消</el-button>
                    <el-button size="small" type="primary" :loading="submitLoading" @click="toSumbit">确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>

        <!-- 查看大图 -->
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img :src="click_img" class="click-img" alt="">
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-cancel', {
        template: '#app-cancel',
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
            cancelType: {
                type: Number,
                default: -1,
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
                status: '',
                content: '',
                title: '',
                remark: '',
                submitLoading: false,
                dialogImg: false,
                click_img: '',
            }
        },
        methods: {
            openDialog() {
                // 申请取消的判断
                this.dialogVisible = true;
                if (this.cancelType == 1) {
                    this.status = 1;
                    this.title = '同意取消';
                    this.content = '填写同意理由：';
                } else if (this.cancelType == 0) {
                    this.status = 2;
                    this.title = '拒绝取消';
                    this.content = '填写拒绝理由：';
                } else if (this.cancelType == 2) {
                    this.status = 1;
                    this.title = '订单取消';
                    this.content = '填写取消理由：';
                }
            },
            closeDialog() {
                this.$emit('close')
            },
            toSumbit() {
                this.submitLoading = true;
                request({
                    params: {
                        r: 'mall/order/cancel'
                    },
                    data: {
                        order_id: this.order.id,
                        remark: this.remark,
                        status: this.status,
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
            },
            // 显示大图
            openBig(e) {
                this.click_img = e;
                this.dialogImg = true;
            },
        }
    })
</script>