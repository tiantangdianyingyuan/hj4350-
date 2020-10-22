<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<template id="app-select-print">
    <div class="app-select-print">
        <el-dialog width="25%" title="选择打印机" :visible.sync="printDialogVisible">
            <el-form label-width="120px" :model="printForm" ref="printForm" :rules="printFormRules"
                     @submit.native.prevent>
                <el-form-item label="打印机" prop="print_id">
                    <el-select v-model="printForm.print_id" placeholder="请选择">
                        <el-option size="small"
                                   v-for="item in printList"
                                   :key="item.id"
                                   :label="item.id + `-`+ item.printer.name"
                                   :value="item.id"
                                   :disabled="item.status == 0">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" :loading="btnLoading" @click="printDialogVisible = false">取消</el-button>
                <el-button size="small" :loading="btnLoading" type="primary" @click="print">打印</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-select-print', {
        template: '#app-select-print',
        props: {
            value: {
                type: Boolean,
                default: false,
            },
            orderId: {
                type: Number,
                default: 0,
            },
        },
        data() {
            return {
                printDialogVisible: this.value,
                printList: [],
                btnLoading: false,
                printForm: {
                    order_id: this.orderId,
                    print_id: '',
                },
                printFormRules: {
                    print_id: [
                        {required: true, message: '请选择打印机',}
                    ],
                }
            }
        },
        watch: {
            'value'(newData, oldData) {
                this.printDialogVisible = newData;
            },
            'printDialogVisible'(newData, oldData) {
                this.$emit('input', newData);
            }
        },
        mounted() {
            this.getList();
        },
        methods: {
            print() {
                this.$refs.printForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/order/order-print',
                                order_id: this.orderId,
                                print_id: this.printForm.print_id,
                            },
                            method: 'get',
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                                this.getDetail();
                            }
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        });
                    }
                });
            },
            getList() {
                request({
                    params: {
                        r: 'mall/printer/setting',
                        page_size: 99,
                        status: 1,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.printList = e.data.data.list;
                    }
                })
            },
        },
    })
</script>