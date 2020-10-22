<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 15:31
 */
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
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

    .el-dialog {
        min-width: 800px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" v-loading="loading"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>起送规则</span>
        </div>
        <el-form ref="ruleForm" label-width="150px" :model="ruleForm" :rules="rules"
                 class="demo-ruleForm" position-label="right">
            <div class="form-body">
                <el-form-item label="是否开启" prop="is_enable" required>
                    <el-switch
                            v-model="ruleForm.is_enable"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
                <template v-if="ruleForm.is_enable == 1">
                    <el-form-item label="全地区起送金额设置" prop="is_total_price" required>
                        <el-switch
                                v-model="ruleForm.is_total_price"
                                :active-value="1"
                                :inactive-value="0">
                        </el-switch>
                    </el-form-item>

                    <el-form-item v-if="ruleForm.is_total_price" label="全地区起送金额" prop="total_price" required>
                        <el-input size="small" v-model.number="ruleForm.total_price" type="number">
                            <template slot="append">元</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="起送规则" prop="detail">
                        <el-card shadow="never" style="margin-bottom: 12px;" v-if="ruleForm.detail"
                                 v-for="(item, index) in ruleForm.detail" :key="item.id">
                            <div flex="dir:left box:last">
                                <div>起送金额：{{item.total_price}}元
                                </div>
                                <div style="text-align: right">
                                    <el-button size="small" type="text" circle @click="openDistrict(index)">
                                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                            <img src="statics/img/mall/edit.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                    <el-button style="margin-left: -5px" size="small" type="text" circle
                                               @click="deleteDistrict(index)">
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </div>
                            </div>
                            <div flex="dir:left" style="flex-wrap: wrap">
                                <div>区域：</div>
                                <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in item.list"
                                        :key="key.id">
                                    {{value.name}}
                                </el-tag>
                            </div>
                        </el-card>
                        <el-button size="small" type="text" @click="openDistrict()"><i class="el-icon-plus">新增规则</i>
                        </el-button>
                    </el-form-item>
                </template>
            </div>
            <el-form-item class="form-button">
                <el-button class="button-item" :loading="submitLoading" type="primary" @click="onSubmit('ruleForm')">
                    保存
                </el-button>
            </el-form-item>
        </el-form>
        <el-dialog title="新增规则" :visible.sync="dialogVisible" width="50%">
            <el-form ref="detail" @submit.native.prevent :model="detail" label-width="150px" :rules="dialogRules">
                <el-form-item label="起送金额" prop="total_price">
                    <el-input v-model="detail.total_price" type="number">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="地区选择" prop="list">
                    <div style="margin-bottom: 1rem;">
                        <app-district :detail="ruleForm.detail" @selected="selectDistrict" :level="3"
                                      :edit="editDistrict.list"></app-district>
                        <div style="text-align: right;margin-top: 1rem;">
                            <el-button type="primary" @click="districtConfirm('detail')">
                                添加规则
                            </el-button>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let min_number = (rule, value, callback) => {
                if (value < 0) {
                    callback(new Error('输入值必须大于等于0'));
                } else {
                    callback();
                }
            };
            return {
                loading: false,
                submitLoading: false,
                dialogVisible: false,
                ruleForm: {
                    is_enable: 0,
                    total_price: 0,
                    detail: [],
                    is_total_price: 1,
                },
                rules: {
                    is_enable: [
                        {type: 'required', message: '请选择是否开启'}
                    ],
                    total_price: [
                        {validator: min_number, trigger: 'change'},
                        {required: true, type: 'number', trigger: 'change', message: '请输入起送金额'},
                    ],
                },
                dialogRules: {
                    total_price: [
                        {required: true, message: '请输入起送金额', trigger: 'change'},
                        {validator: min_number, trigger: 'change'},
                    ],
                    list: [
                        {required: true, message: '请选择起送规则'}
                    ]
                },
                detail: {
                    total_price: 0,
                    list: []
                },
                editDistrict: {
                    total_price: 0,
                    list: []
                },
                editIndex: -1
            }
        },
        mounted() {
            this.load();
        },
        methods: {
            load() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/offer-price/index'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = Object.assign(this.ruleForm, e.data.data.model);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                    this.$message.error('获取数据出错');
                });
            },
            openDistrict(index) {
                if (typeof index != 'undefined') {
                    this.editDistrict.list = this.ruleForm.detail[index].list;
                    this.editDistrict.total_price = this.ruleForm.detail[index].total_price;
                    this.editIndex = index;
                } else {
                    this.editDistrict.list = [];
                    this.editDistrict.total_price = 0;
                    this.editIndex = -1;
                }
                this.detail = JSON.parse(JSON.stringify(this.editDistrict));
                this.dialogVisible = true;
            },
            deleteDistrict(index) {
                this.ruleForm.detail.splice(index, 1);
            },
            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.detail.list = list;
            },
            districtConfirm(formName) {
                let that = this;
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        let detail = JSON.parse(JSON.stringify(this.detail));
                        if (this.editIndex != -1) {
                            this.ruleForm.detail[this.editIndex] = detail;
                        } else {
                            if (!this.ruleForm.detail) {
                                this.ruleForm.detail = [];
                            }
                            this.ruleForm.detail.push(detail)
                        }
                        this.dialogVisible = false;
                        setTimeout(function () {
                            that.detail = {
                                list: [],
                                total_price: 0
                            };
                            that.$refs['detail'].clearValidate();
                        }, 500)
                    }
                });
                this.$refs['ruleForm'].validate(valid => {
                });
            },
            onSubmit(formName) {
                this.submitLoading = true;
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        request({
                            params: {
                                r: 'mall/offer-price/index'
                            },
                            method: 'post',
                            data: {
                                form: this.ruleForm
                            }
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.submitLoading = false;
                            this.$message.error(e.data.msg);
                        });
                    } else {
                        this.submitLoading = false;
                        return false;
                    }
                })
            }
        }
    });
</script>