<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style xmlns="">
    .prompt {
        margin: -10px 20px 20px;
        background-color: #F4F4F5;
        padding: 10px 15px;
        color: #909399;
        display: inline-block;
        font-size: 13px
    }

    .btn-submit {
        margin-top: 20px;
        padding: 9px 25px;
    }
</style>
<template id="app-express">
    <div style="margin-top: 10px">
        <el-form ref="ruleForm" label-width="150px" :model="ruleForm" :rules="rules"
                 class="demo-ruleForm" position-label="right">
            <el-card shadow="never">
                <el-form-item label="查询类型选择" prop="express_select_type">
                    <el-radio-group v-model="ruleForm.express_select_type" @change="changePrint">
                        <el-radio label="">快递鸟</el-radio>
                        <el-radio label="kd100">快递100</el-radio>
                        <el-radio label="wd">阿里云接口</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item class="switch" label="阿里云APPCODE" prop="express_aliapy_code"
                              v-if="ruleForm.express_select_type == `wd`">
                    <el-input v-model="ruleForm.express_aliapy_code"></el-input>
                    <span>
                        <span style="color:#666666">用户获取物流信息,</span>
                        <el-link href="https://market.aliyun.com/products/56928004/cmapi023201.html"
                                 type="primary"
                                 target="_blank"
                                 :underline="false"
                        >阿里云接口申请</el-link>
                    </span>
                </el-form-item>

                <el-form-item v-if="ruleForm.express_select_type === 'wd'" label="电子面单选择" prop="print_type">
                    <el-radio-group v-model="ruleForm.print_type">
                        <el-radio label="">快递鸟</el-radio>
                        <el-radio label="kd100">快递100</el-radio>
                    </el-radio-group>
                </el-form-item>
                <!--<div v-if="ruleForm.express_select_type === `wd`" class="prompt">温馨提示：以下为电子面单配置，只能填写快递鸟参数</div>-->
                <el-form-item v-if="ruleForm.print_type === ''" prop="kdniao_mch_id" label="快递鸟用户ID">
                    <el-input v-model="ruleForm.kdniao_mch_id"></el-input>
                </el-form-item>
                <el-form-item v-if="ruleForm.print_type === ''" label="快递鸟API KEY" prop="kdniao_api_key">
                    <el-input v-model="ruleForm.kdniao_api_key"></el-input>
                </el-form-item>

                <el-form-item v-if="ruleForm.print_type === 'kd100'" prop="kd100_key" label="快递100授权KEY">
                    <el-input v-model="ruleForm.kd100_key"></el-input>
                </el-form-item>
                <el-form-item v-if="ruleForm.print_type === 'kd100'" prop="kd100_customer" label="快递100customer">
                    <el-input v-model="ruleForm.kd100_customer"></el-input>
                </el-form-item>
                <el-form-item v-if="ruleForm.print_type === 'kd100'" prop="kd100_secret" label="快递100secret">
                    <el-input v-model="ruleForm.kd100_secret"></el-input>
                </el-form-item>
                <el-form-item v-if="ruleForm.print_type === 'kd100'" prop="kd100_siid" label="快递100打印机编码">
                    <el-input v-model="ruleForm.kd100_siid"></el-input>
                </el-form-item>
            </el-card>
            <el-button :loading="btnLoading" class="btn-submit" size="medium" type="primary" @click="onSubmit">保存
            </el-button>
        </el-form>
    </div>
</template>
<script>
    Vue.component('app-express', {
        template: '#app-express',
        props: {},
        data() {
            return {
                listLoading: false,
                ruleForm: {},
                btnLoading: false,
                rules: {},
                otherInfo: null,
            }
        },
        computed: {},
        methods: {
            changePrint(express_select_type) {
                this.ruleForm.print_type = express_select_type === 'wd' ? '' : express_select_type
            },
            loadData() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/index/setting',
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        let detail = e.data.data.detail;
                        detail.setting.latitude_longitude = detail.setting.latitude + ',' + detail.setting.longitude;

                        this.ruleForm = detail.setting;
                        this.otherInfo = detail;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },

            onSubmit() {
                this.$refs.ruleForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({
                            setting: this.ruleForm
                        }, this.otherInfo);
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/index/setting',
                            },
                            data: {
                                ruleForm: JSON.stringify(para)
                            },
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
        },
        mounted: function () {
            this.loadData();
        },
    });
</script>