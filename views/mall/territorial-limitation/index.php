<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 14:34
 */
?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
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

    .doit {
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .el-dialog {
        min-width: 800px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">
            <span>区域允许购买</span>
        </div>
        <el-form :model="ruleForm" ref="ruleForm" :rules="rules" label-width="150px">
            <div class="form-body">
                <el-form-item label="是否开启" prop="is_enable" required>
                    <el-switch
                        style="margin-left: 20px;"
                        v-model="ruleForm.is_enable"
                        :active-value="1"
                        :inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="允许购买区域" v-if="ruleForm.is_enable" prop="detail" required>
                    <el-button style="margin-left: 20px;" size="small" v-if="ruleForm.detail[0].list.length == 0" type="text" @click="openDistrict"><i class="el-icon-plus">添加地区</i>
                    </el-button>
                    <el-card style="margin-left: 20px;position: relative" shadow="never" style="margin-bottom: 12px;width: 650px" v-else
                             v-for="(item, index) in ruleForm.detail" :key="item.id">
                        <div flex="dir:left box:last">
                            <div>
                                <div flex="dir:left" style="flex-wrap: wrap;width: 70%">
                                    <div>区域：</div>
                                    <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in item.list" :key="key.id">
                                        {{value.name}}
                                    </el-tag>
                                </div>
                            </div>
                            <div class="doit">
                                <el-button size="small" type="text" circle @click="openDistrict(index)">
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button size="small" circle type="text" @click="deleteDistrict(index)">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </div>
                        </div>
                    </el-card>
                </el-form-item>
            </div>
            <el-form-item class="form-button">
                <el-button sizi="mini" class="button-item" :loading="submitLoading" type="primary" @click="onSubmit('ruleForm')">
                    保存
                </el-button>
            </el-form-item>
        </el-form>
        <el-dialog title="选择地区" :visible.sync="dialogVisible" width="50%">
            <div style="margin-bottom: 1rem;">
                <app-district :edit="detail" @selected="selectDistrict" :level="3"></app-district>
                <div style="text-align: right;margin-top: 1rem;">
                    <el-button type="primary" @click="districtConfirm">
                        确定选择
                    </el-button>
                </div>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                submitLoading: false,
                dialogVisible: false,
                ruleForm: {
                    is_enable: 0,
                    detail: [
                        {list: []}
                    ]
                },
                rules: {
                    is_enable: [
                        {type: 'integer', trigger: 'blur', required: true, message: '请选择是否开启'}
                    ],
                    detail: [
                        {trigger: 'blur', required: true, message: '请选择地区'}
                    ]
                },
                detail: {
                    list: []
                }
            };
        },
        mounted() {
            this.load();
        },
        methods: {
            load() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/territorial-limitation/index'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.model;
                        if(this.ruleForm.detail == null) {
                            this.ruleForm.detail = [this.detail]
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },
            openDistrict(index) {
                this.detail = JSON.parse(JSON.stringify(this.ruleForm.detail));
                this.dialogVisible = true;
            },
            deleteDistrict(index) {
                this.ruleForm.detail[0].list = [];
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
                this.detail[0].list = list;
            },
            districtConfirm() {
                 this.ruleForm.detail = JSON.parse(JSON.stringify(this.detail));
                 this.detail = [];
                this.dialogVisible = false;
            },
            onSubmit(formName) {
                this.submitLoading = true;
                this.$refs[formName].validate(valid => {
                    if(valid) {
                        request({
                            params: {
                                r: 'mall/territorial-limitation/index'
                            },
                            method: 'post',
                            data: {
                                form: this.ruleForm
                            }
                        }).then(e => {
                            this.submitLoading = false;
                            if(e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.submitLoading = false;
                            this.$message.error(e.data.msg);
                        });
                    } else {
                        console.log('error submit!!');
                        this.submitLoading = false;
                        return false;
                    }
                })
            }
        }
    });
</script>