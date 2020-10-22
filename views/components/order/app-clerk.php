<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>

</style>

<template id="app-clerk">
    <div class="app-clerk">
        <!-- 核销员选择 -->
        <el-dialog title="选择核销员" :visible.sync="dialogVisible" width="30%" @close="closeDialog">
            <el-form v-loading="dialogLoading" label-position="left" :model="clerk_user" ref="clerk_user"
                     :rules="clerkRules" label-width="90px">
                <el-form-item label="核销员" prop="user.nickname">
                    <el-autocomplete
                            size="small"
                            v-model="clerk_user.user.nickname"
                            :fetch-suggestions="clerkSearch"
                            placeholder="请选择核销员"
                            @select="dialogFormChooseClerk"
                    ></el-autocomplete>
                </el-form-item>
                <el-form-item label="所属门店" v-if="clerk_user.store.length > 0">
                    <div v-for="store in clerk_user.store" :key="store.id">{{ store.name }}</div>
                </el-form-item>
                <el-form-item label="核销备注" prop="clerk_remark">
                    <el-input type="textarea" v-model="clerk_remark" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right;margin-bottom: 0">
                    <el-button size="small" type="primary" @click="dialogVisible = false">取消</el-button>
                    <el-button :loading="loading" size="small" type="primary" @click="send_clerk(clerk_user)">
                        确 定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-clerk', {
        template: '#app-clerk',
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
                clerk_list: [],
                clerk_user: {
                    store: [],
                    user: {},
                },
                dialogLoading: false,
                loading: false,
                clerkRules: {
                    user: {
                        nickname: [
                            {required: true, message: '请选择核销员', trigger: 'change'},
                        ]
                    },
                },
                keyword: '',
                clerk_remark: '',// 核销备注
            }
        },
        methods: {
            // 打开核销员选项菜单
            openDialog() {
                if (this.order.is_pay == 1) {
                    this.dialogVisible = true;
                    this.clerk_user = {
                        store: [],
                        user: {},
                    };
                    this.clerk_remark = '';
                    this.getClerkUser();
                } else {
                    this.$confirm('是否确认该订单已线下收款？', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        this.clerkAffirmPay();
                    }).catch(() => {
                        this.closeDialog();
                    });
                }
            },
            clerkAffirmPay() {
                let self = this;
                request({
                    params: {
                        r: 'mall/order/clerk-affirm-pay',
                    },
                    data: {
                        order_id: this.order.id,
                        action_type: 2,// 后台确认收款
                    },
                    method: 'post',
                }).then(e => {
                    self.closeDialog();
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                        self.$emit('submit');
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            getClerkUser() {
                request({
                    params: {
                        r: 'mall/user/clerk-user',
                        store_id: this.order.store.id,
                        keyword: this.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    this.dialogLoading = false;
                    if (e.data.code === 0) {
                        this.clerk_list = e.data.data.list;
                        for (let i = 0; i < this.clerk_list.length; i++) {
                            this.clerk_list[i].value = this.clerk_list[i].user.nickname
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.dialogLoading = false;
                });
            },
            closeDialog() {
                this.$emit('close')
            },
            clerkSearch(queryString, cb) {
                this.keyword = queryString;
                this.getClerkUser();

                let timeout = setTimeout(() => {
                    cb(this.clerk_list);
                    clearTimeout(timeout);
                }, 1000);
            },
            // 选中核销员
            dialogFormChooseClerk(e) {
                this.clerk_user = e;
            },
            send_clerk(e) {
                let self = this;
                this.$refs['clerk_user'].validate((valid) => {
                    if (valid) {
                        self.loading = true;
                        request({
                            params: {
                                r: 'mall/order/order-clerk',
                            },
                            data: {
                                order_id: this.order.id,
                                clerk_id: this.clerk_user.user.id,
                                action_type: 2,// 后台确认核销
                                clerk_remark: self.clerk_remark,
                            },
                            method: 'post',
                        }).then(e => {
                            self.loading = false;
                            if (e.data.code === 0) {
                                self.dialogVisible = false;
                                self.$message.success(e.data.msg);
                                self.$emit('submit');
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
        }
    })
</script>