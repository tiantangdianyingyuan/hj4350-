<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .img-type .el-form-item__content {
        width: 100% !important;
    }
</style>

<div id="app" v-cloak v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>多商户设置</span>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="120px" size="small">
                <el-tabs v-if="is_show" v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="基础设置" name="first">
                        <el-row>
                            <el-col :span="12">
                                <el-form-item label="提现方式" prop="cash_type">
                                    <el-checkbox :indeterminate="isIndeterminate"
                                                 v-model="checkAll"
                                                 @change="handleCheckAllChange">
                                        全选
                                    </el-checkbox>
                                    <el-checkbox-group v-model="ruleForm.cash_type" @change="handleCheckedCitiesChange">
                                        <el-checkbox style="margin-right: 15px;"
                                                     v-for="item in cashOptions"
                                                     :label="item.value" :key="item.value">
                                            {{item.label}}
                                        </el-checkbox>
                                    </el-checkbox-group>
                                </el-form-item>
                                <el-form-item label="入驻协议" prop="desc">
                                    <el-input
                                            type="textarea"
                                            :rows="10"
                                            placeholder="请输入驻协议内容"
                                            v-model="ruleForm.desc">
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="客服图标" prop="is_service">
                                    <el-switch v-model="ruleForm.is_service" :onactive-value="0"
                                               :active-value="1"></el-switch>
                                </el-form-item>
                                <el-form-item label="商户距离排序" prop="is_distance">
                                    <el-switch v-model="ruleForm.is_distance" :onactive-value="0"
                                               :active-value="1"></el-switch>
                                </el-form-item>
                                <el-form-item label="商品上架审核" prop="is_goods_audit">
                                    <el-switch v-model="ruleForm.is_goods_audit" :onactive-value="0"
                                               :active-value="1"></el-switch>
                                </el-form-item>
                                <el-form-item label="订单确认收货" prop="is_confirm_order">
                                    <el-switch v-model="ruleForm.is_confirm_order" :onactive-value="0"
                                               :active-value="1"></el-switch>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-tab-pane>
                    <el-tab-pane label="自定义资料审核" name="second">
                        <el-row>
                            <el-col :span="24">
                                <el-form-item label="表单状态" prop="status" style="width: 180px">
                                    <div>
                                        <el-switch
                                                v-model="ruleForm.status"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>
                                <template v-if="ruleForm.status == 1">
                                    <el-form-item label="表单设置" prop="selectedOptions">
                                        <app-form :is_mch="true" :value.sync="ruleForm.form_data"></app-form>
                                    </el-form-item>
                                </template>
                            </el-col>
                        </el-row>
                    </el-tab-pane>
                    <el-tab-pane label="商户登录页设置" name="third">
                        <el-row>
                            <el-col :span="12">
                                <el-form-item label="LOGO图片URL">
                                    <el-input class="currency-width isAppend" v-model="ruleForm.logo">
                                        <template slot="append">
                                            <app-attachment v-model="ruleForm.logo" :multiple="false" :max="1">
                                                <el-tooltip class="item"
                                                            effect="dark"
                                                            content="建议尺寸:325 * 325"
                                                            placement="top">
                                                    <el-button size="mini">上传图片</el-button>
                                                </el-tooltip>
                                            </app-attachment>
                                        </template>
                                    </el-input>
                                    <img class="my-img"
                                         style="background-color: #100a46;border-color: #100a46; height: 36px;"
                                         v-if="ruleForm.logo" :src="ruleForm.logo">
                                    <div v-else class="preview">建议尺寸98*36</div>
                                </el-form-item>
                                <el-form-item label="底部版权信息">
                                    <el-input class="currency-width" v-model="ruleForm.copyright"></el-input>
                                </el-form-item>
                                <el-form-item label="底部版权链接">
                                    <el-input class="currency-width" v-model="ruleForm.copyright_url"
                                              placeholder="例如:https://www.baidu.com"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
    </el-card>
</div>
<script>
    const options = [
        {
            label: '自动打款',
            value: 'auto'
        },
        {
            label: '微信线下转账',
            value: 'wx'
        },
        {
            label: '支付宝线下转账',
            value: 'alipay'
        },
        {
            label: '银行卡线下转账',
            value: 'bank'
        },
        {
            label: '余额',
            value: 'balance'
        },
    ];
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    form_data: [],
                },
                rules: {},
                btnLoading: false,
                cardLoading: false,

                checkAll: false,
                cashOptions: options,
                isIndeterminate: false,
                activeName: 'first',

                selectedOptions: [],
                is_show: false,
            };
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/setting/index',
                    },
                }).then(e => {
                    this.is_show = true;
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.setting;
                        this.handleCheckedCitiesChange(this.ruleForm.cash_type);
                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        if (self.ruleForm.status == 1 && !this.ruleForm.form_data.length) {
                            this.$message.error('至少添加一项表单设置');
                            return;
                        }
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mch/mall/setting/index'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            handleCheckAllChange(val) {
                let self = this;
                self.ruleForm.cash_type = [];
                if (val) {
                    self.cashOptions.forEach(function (item) {
                        self.ruleForm.cash_type.push(item.value)
                    });
                }
                this.isIndeterminate = false;
            },
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.cashOptions.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.cashOptions.length;
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
