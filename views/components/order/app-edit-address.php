<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>

</style>

<template id="app-edit-address">
    <div class="app-edit-address">
        <el-dialog @close="dialogClose" :title="title" :visible.sync="openDialog" width="30%">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="80px">
                <el-form-item prop="name" label="收件人">
                    <el-input type="text" size="small" v-model="ruleForm.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item prop="mobile" label="电话">
                    <el-input size="small" v-model="ruleForm.mobile" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item prop="provinceName" label="所在区域">
                    <el-select v-model="ruleForm.provinceName" size="small" placeholder="请选择" @change="getCity"
                               style="width:30%;">
                        <el-option
                                v-for="item in address"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                    <el-select v-model="cityName" size="small" placeholder="请选择" @change="getDistrict"
                               style="width:30%;">
                        <el-option
                                v-for="item in city"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                    <el-select v-model="districtName" size="small" placeholder="请选择" style="width:30%;">
                        <el-option
                                v-for="item in district"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item prop="address_detail" label="详细地址">
                    <el-input type="text" size="small" v-model="ruleForm.address_detail" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="openDialog = false">取消</el-button>
                    <el-button :loading="btnLoading" size="small" type="primary" @click="changAddress('ruleForm')">确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-edit-address', {
        template: '#app-edit-address',
        props: {
            order: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            isShow: {
                type: Boolean,
                default: false
            },
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    if (this.order.send_type == 1 && this.order.is_send == 0) {
                        this.title = '添加收货地址'
                    }
                    this.openAddress();
                }
            }
        },
        data() {
            return {
                openDialog: false,
                name: '',
                mobile: '',
                address_detail: '',
                cityName: '',
                districtName: '',
                provinceName: '',
                address: [],
                city: [],
                district: [],
                btnLoading: false,
                title: '修改收货信息',
                rules: {
                    name: [
                        { required: true, message: '请填写收件人', trigger: 'change' }
                    ],
                    mobile: [
                        { required: true, message: '请填写电话', trigger: 'change' }
                    ],
                    provinceName: [
                        { required: true, message: '请填写所在区域', trigger: 'change' }
                    ],
                    address_detail: [
                        { required: true, message: '请填写详细地址', trigger: 'change' }
                    ],
                },
                ruleForm: {
                    name: '',
                    mobile: '',
                    provinceName: '',
                    address_detail: '',
                },
            }
        },
        methods: {
            // 打开改地址
            openAddress() {
                this.openDialog = true;
                let address = this.order.address;
                let first = address.indexOf(' ')
                let second = address.indexOf(' ', first + 1)
                let third = address.indexOf(' ', second + 1)
                this.cityName = address.substring(first + 1, second)
                this.districtName = address.substring(second + 1, third)

                this.ruleForm.name = this.order.name;
                this.ruleForm.mobile = this.order.mobile;
                this.ruleForm.provinceName = address.substring(0, first);
                this.ruleForm.address_detail = address.substring(third + 1);
                request({
                    params: {
                        r: 'mall/order/address-list',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.address = e.data.data.list;
                        for (let i = 0; i < this.address.length; i++) {
                            if (this.ruleForm.provinceName == this.address[i].name) {
                                this.city = this.address[i].list
                            }
                        }
                        for (let i = 0; i < this.city.length; i++) {
                            if (this.cityName == this.city[i].name) {
                                this.district = this.city[i].list
                            }
                        }
                        this.getDistrict();
                        this.cityName = address.substring(first + 1, second)
                        this.districtName = address.substring(second + 1, third)
                    }
                }).catch(e => {
                });
            },
            // 获取城市列表
            getCity() {
                for (let i = 0; i < this.address.length; i++) {
                    if (this.ruleForm.provinceName == this.address[i].name) {
                        this.city = this.address[i].list
                        this.cityName = this.city[0].name
                        this.district = this.city[0].list
                        this.districtName = this.district[0].name
                    }
                }
            },
            // 获取区县
            getDistrict() {
                for (let i = 0; i < this.city.length; i++) {
                    if (this.cityName == this.city[i].name) {
                        this.district = this.city[i].list
                        this.districtName = this.district[0].name
                    }
                }
            },
            // 改地址
            changAddress(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/order/update-address',
                            },
                            data: {
                                order_id: this.order.id,
                                name: this.ruleForm.name,
                                mobile: this.ruleForm.mobile,
                                province: this.ruleForm.provinceName,
                                city: this.cityName,
                                district: this.districtName,
                                address: this.ruleForm.address_detail,
                            },
                            method: 'post',
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.openDialog = false;
                                this.$emit('submit')
                                this.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                            } else  {
                                this.$message.error(e.data.msg)
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            dialogClose() {
                this.$emit('close');
            }
        },
    })
</script>
