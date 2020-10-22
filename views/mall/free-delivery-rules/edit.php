<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 14:04
 */
Yii::$app->loadViewComponent('app-district');

?>
<style>
    .form-body {
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-item-header {
        height: 60px;
        line-height: 60px;
        padding-left: 22px;
        border-bottom: 1px solid #ededef;
    }

    .form-item {
        padding: 20px;
        padding-right: 50%;
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
        min-width: 600px;
    }

    .free-ship {
        border: 1px solid #ebeef5;
        position: relative;
        padding: 20px 24px 20px 0;
        margin-bottom: 10px;
        min-width: 500px;
    }
    .form-free {
        padding-left: 90px;
        position: relative;
    }
    .form-free .el-delete {
        position: absolute;
        right: -30px;
        top: 1px;
    }
    .form-free .el-delete .el-button {
        border-radius: 0;
        padding: 7px;
    }
    .form-free>.el-button {
        border: 1px solid #409eff;
        color: #409eff;
    }
    .el-card-app {
        background-color: #f3f3f3;
    }
    .el-card .el-card__header {
        background-color: #fff;
    }
    .el-warning {
        color: #ff4040;
        font-size: 13px;
    }
    .is_red_condition .el-input .el-input__inner {
        border: 1px solid #ff4040;
    }
    .is_red_condition .el-input .el-input-group__append {
        border-top: 1px solid #ff4040;
        border-bottom: 1px solid #ff4040;
        border-right: 1px solid #ff4040;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" class="el-card-app" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="loading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span
                        style="color: #409EFF;cursor: pointer"
                        @click="$navigate({r:'mall/index/rule',tab: 'third'})"
                    >包邮规则</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{edit ? '编辑规则':'添加规则'}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form :model="ruleForm" ref="ruleForm" :rules="rules" size="small" label-width="150px">
            <div class="form-body">
                <div class="form-item-header">
                    规则信息
                </div>
                <div class="form-item">
                    <el-form-item label="规则名称" prop="name" >
                        <el-input size='small' v-model="ruleForm.name" placeholder="请输入名称，最多输入15个字"></el-input>
                    </el-form-item>
                    <el-form-item label="包邮类型" prop="type" >
                        <el-radio-group v-model="ruleForm.type">
                            <el-radio :label="1">订单满额包邮</el-radio>
                            <el-radio :label="2">订单满件包邮</el-radio>
                            <el-radio :label="3">单商品满额包邮</el-radio>
                            <el-radio :label="4">单商品满件包邮</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </div>
            </div>
            <div class="form-body">
                <div class="form-item-header">
                    包邮信息
                </div>
                <div class="form-item form-free">
                    <div v-for="(item, index) in ruleForm.detail" :key="index" class="free-ship">
                        <el-form-item label="包邮地区"  required style="margin: 0">
                            <div shadow="never" style="margin-bottom: 12px;" v-if="item.list.length > 0">
                                <div flex="dir:left box:last" >
                                    <div>
                                        <div flex="dir:left" style="flex-wrap: wrap">
                                            <el-tag style="margin:5px;border:0" type="info"
                                                    v-for="(item, index) in item.list" :key="item.id">
                                                {{item.name}}
                                            </el-tag>
                                        </div>
                                    </div>
                                    <div style="text-align: right">
                                        <el-button type="text" @click="openDistrict(index)" size="small" circle>
                                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                <img src="statics/img/mall/edit.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                        <el-button type="text" @click="space(index)" size="small" circle>
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                            <el-button type="text" v-if="item.list.length === 0" @click="selectFree(index)">+选择包邮区域</el-button>
                            <div v-if="item.is_list" class="el-warning">必须选择包邮地区</div>
                        </el-form-item>
                        <el-form-item :label="ruleForm.type == 1 ? '订单满额包邮' : ruleForm.type == 2 ? '订单满件包邮' : ruleForm.type == 3 ? '单商品满额包邮' : '单商品满件包邮'" required>
                            <div flex="main:left" style="width: 100%">
                                <span style="margin-right: 10px;">满</span>
                                <div style="width: 100%" :class="item.is_condition ? 'is_red_condition' : ''">
                                    <el-input v-model="item.condition" @input="changeCondition(index)">
                                        <template slot="append">
                                            {{ruleForm.type == 2 || ruleForm.type == 4 ? '件' : '元'}}
                                        </template>
                                    </el-input>
                                </div>
                                <span style="margin-left: 10px;width: 80px">免运费</span>
                            </div>
                            <div v-if="item.is_condition" class="el-warning">
                                包邮{{ruleForm.type == 2 || ruleForm.type == 4 ? '数量' : '金额'}}必须>0
                            </div>
                        </el-form-item>
                        <div class="el-delete" @click="deleteFree(index)">
                            <el-button icon="el-icon-delete" type="primary">

                            </el-button>
                        </div>
                    </div>
                    <div v-if="ruleForm.detail.length === 0 && is_detail" class="el-warning">请添加包邮地区</div>
                    <el-button style="margin-top: 10px;" @click="addFreeShip">+添加包邮区域</el-button>
                </div>
            </div>
            <el-form-item class="form-button">
                <el-button sizi="mini" class="button-item" :loading="submitLoading" type="primary"
                           @click="onSubmit('ruleForm')">
                    保存
                </el-button>
            </el-form-item>
        </el-form>
    </el-card>
    <el-dialog title="包邮地区选择" :visible.sync="dialogVisible" width="50%">
        <div style="margin-bottom: 1rem;">
            <app-district :detail="detail" @selected="selectDistrict" :level="3"
                          :edit="editList"></app-district>
            <div style="text-align: right;margin-top: 1rem;">
                <el-button type="primary" @click="districtConfirm">
                    确定选择
                </el-button>
            </div>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                edit: false,
                submitLoading: false,
                loading: false,
                dialogVisible: false,
                detail: [],
                editList: [],
                ruleForm: {
                    name: '',
                    detail: [],
                    type: 1
                },
                detailIndex: 0,
                rules: {
                    price: [
                        {type: 'number', message: '请输入数字', trigger: 'change', required: true},
                        {type: 'number', message: '请输入数字', trigger: 'blur', required: true},
                    ],
                    detail: [
                        {message: '请选择包邮地区', trigger: 'change', required: true}
                    ],
                    name: [
                        {message: '请填写包邮规则名称', trigger: 'blur', required: true},
                        { min: 1, max: 15, message: '长度在 1 到 15 个字', trigger: 'blur' }
                    ],
                    condition: [
                        {type: 'number', message: '请输入数字', trigger: 'change', required: true},
                    ],
                    type: [
                        {message: '请填写包邮包邮类型', trigger: 'blur', required: true},
                    ]
                },
                is_detail: false
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
                this.edit = true;
            }
        },
        methods: {
            // 单个删除区域
            space(index) {
                for (let i = 0; i < this.ruleForm.detail[index].list.length; i++) {
                    for (let j = 0; j < this.detail.length; j++) {
                        if (this.detail[j].id === this.ruleForm.detail[index].list[i].id) {
                            this.$delete(this.detail, j);
                        }
                    }
                }
                this.$set(this.ruleForm.detail[index], 'list', []);
            },

            // 添加包邮区域
            addFreeShip() {
                this.ruleForm.detail.push({
                    condition: '',
                    list: [],
                    is_list: false,
                    is_condition: false
                });
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
                this.editList = list;
            },
            openDistrict(index) {
                this.editList = this.ruleForm.detail[index].list;
                this.detailIndex = index;
                let list = [];
                for (let i = 0; i < this.ruleForm.detail.length; i++) {
                    for (let j = 0; j < this.ruleForm.detail[i].list.length; j++) {
                        list.push(this.ruleForm.detail[i].list[j]);
                    }
                }
                this.detail = list;
                this.dialogVisible = true;
            },

            selectFree(index) {
                this.dialogVisible = true;
                this.detailIndex = index;
            },

            districtConfirm() {
                this.ruleForm.detail[this.detailIndex].list = JSON.parse(JSON.stringify(this.editList));
                this.ruleForm.detail[this.detailIndex].is_list = false;
                this.dialogVisible = false;
                let list = [];
                for (let i = 0; i < this.ruleForm.detail.length; i++) {
                    for (let j = 0; j < this.ruleForm.detail[i].list.length; j++) {
                        list.push(this.ruleForm.detail[i].list[j]);
                    }
                }
                this.detail = list;
                this.editList = [];
            },
            onSubmit(formName) {
                if (this.ruleForm.detail.length === 0)
                {
                    this.is_detail = true;
                    return;

                }
                let is_list = 0;
                for (let i = 0; i < this.ruleForm.detail.length; i++) {
                    if (this.ruleForm.detail[i].list.length === 0) {
                        this.ruleForm.detail[i].is_list = true;
                        is_list++;
                    }
                    if (!this.ruleForm.detail[i].condition) {
                        this.ruleForm.detail[i].is_condition = true;
                        is_list++;
                    }
                }
                if (is_list > 0)
                {
                    return;
                }
                this.submitLoading = true;
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        request({
                            params: {
                                r: 'mall/free-delivery-rules/edit',
                                id: getQuery('id')
                            },
                            method: 'post',
                            data: {
                                form: this.ruleForm
                            }
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/index/rule',
                                    tab: 'third'
                                });
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
            },
            getDetail(id) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/free-delivery-rules/edit',
                        id: id
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        let model = e.data.data.model;
                        for (let i = 0; i < model.detail.length; i++) {
                            model.detail[i].is_list = false;
                            model.detail[i].is_condition = false;
                            for (let j = 0; j < model.detail[i].list.length; j++) {
                                this.detail.push(model.detail[i].list[j]);
                            }
                        }
                        this.ruleForm = model;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    this.loading = false;
                });
            },

            // 删除包邮信息
            deleteFree(index) {
                for (let i = 0; i < this.ruleForm.detail[index].list.length; i++) {
                    for (let j = 0; j < this.detail.length; j++) {
                        if (this.detail[j].id === this.ruleForm.detail[index].list[i].id) {
                            this.$delete(this.detail, j);
                        }
                    }
                }
                this.$delete(this.ruleForm.detail, index);
            },

            changeCondition(index) {
                this.ruleForm.detail[index].is_condition = false;
                let condition = '' + this.ruleForm.detail[index].condition;
                if (this.ruleForm.type === 1 || this.ruleForm.type === 3) {
                    condition = condition.replace(/[^\d.]/g, '')
                        .replace(/\.{2,}/g, '.')
                        .replace('.', '$#$')
                        .replace(/\./g, '')
                        .replace('$#$', '.')
                        .replace(/^(\-)*(\d+)\.(\d\d).*$/, '$1$2.$3');
                } else {
                    condition = condition.replace(/[^\d]/g, '')
                    if (condition.indexOf('.') < 0 && condition !== '') {
                        condition = parseInt(condition);
                    }
                }
                this.ruleForm.detail[index].condition = condition;
            }
        }
    });
</script>
