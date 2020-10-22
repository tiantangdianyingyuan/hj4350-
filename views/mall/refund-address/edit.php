<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" v-loading="cardLoading" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/refund-address/index'})">地址列表</span></el-breadcrumb-item>
                <el-breadcrumb-item>地址编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
            <div class="form-body">
                <el-form-item label="收件人姓名" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="请输入收件人姓名"></el-input>
                </el-form-item>
                <el-form-item label="联系方式" prop="mobile">
                    <el-input v-model="ruleForm.mobile" placeholder="请输入联系方式"></el-input>
                </el-form-item>
                <el-form-item label="省市区" prop="address">
                    <el-cascader
                            :options="district"
                            :props="props"
                            v-model="ruleForm.address">
                    </el-cascader>
                </el-form-item>
                <el-form-item label="详细地址" prop="address_detail">
                    <el-input
                            type="textarea"
                            :rows="4"
                            placeholder="请输入详细地址"
                            v-model="ruleForm.address_detail">
                    </el-input>
                </el-form-item>
                <el-form-item label="备注" prop="remark">
                    <el-input
                            type="textarea"
                            :rows="4"
                            placeholder="请输入退货备注"
                            v-model="ruleForm.remark">
                    </el-input>
                </el-form-item>
            </div>
            <el-form-item class="form-button">
                <el-button :loading="btnLoading" class='button-item' type="primary" @click="store('ruleForm')" size="small">保存
                </el-button>
            </el-form-item>
        </el-form>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    address: [],
                    address_detail: '',
                    mobile: '',
                    remark: '',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入收件人名称', trigger: 'change'},
                    ],
                    mobile: [
                        {required: true, message: '请输入收件人联系方式', trigger: 'change'},
                    ],
                    address: [
                        {required: true, message: '请选择省市区', trigger: 'change'},
                    ],
                    address_detail: [
                        {required: true, message: '请输入收件人地址', trigger: 'change'},
                        {max: 255, message: '超出最大长度', trigger: 'change'}
                    ],
                    remark: [
                        {max: 255, message: '超出最大长度', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                district: [],
                props: {
                    value: 'name',
                    label: 'name',
                    children: 'list'
                },
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/refund-address/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                window.history.go(-1)
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/refund-address/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            picUrl(e) {
                if (e.length) {
                    this.ruleForm.pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('pic_url');
                }
            },
            // 获取省市区列表
            getDistrict() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getDistrict();
        }
    });
</script>
