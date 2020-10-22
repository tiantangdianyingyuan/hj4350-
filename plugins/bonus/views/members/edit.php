<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/1
 * Time: 11:11
 */
Yii::$app->loadViewComponent('app-rich-text')

?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 50%;
        min-width: 850px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/bonus/mall/members/index'})">队长等级设置</span></el-breadcrumb-item>
                <el-breadcrumb-item>添加队长等级</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="队长等级选择" prop="level">
                            <el-select style="width: 100%" v-model="ruleForm.level" placeholder="请选择">
                                <el-option
                                        v-for="item in options"
                                        :key="item.level"
                                        :label="item.name"
                                        :value="item.level"
                                        :disabled="item.disabled">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="队长等级名称" prop="name">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.auto_update == 1" label="升级条件" prop="update_type" style="padding-right: 10%;">
                            <el-radio-group @change="tabType" v-model="ruleForm.update_type">
                                <el-radio label="0" >分销佣金</el-radio>
                                <el-radio label="1">已提现佣金</el-radio>
                                <el-radio label="2">下线人数</el-radio>
                                <el-radio label="3">下线分销商数</el-radio>
                                <el-radio label="4">下级队长数</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item class="switch" v-if="ruleForm.auto_update == 1 && ruleForm.update_type == 4" label="下级队长数" prop="title">
                            <el-input type="number" size="small" v-model="ruleForm.update_condition" autocomplete="off">
                                <template slot="append">人</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="ruleForm.auto_update == 1 && ruleForm.update_type == 2" label="下线人数" prop="title">
                            <el-input type="number" size="small" v-model="ruleForm.update_condition" autocomplete="off">
                                <template slot="append">人</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="ruleForm.auto_update == 1 && ruleForm.update_type == 3" label="下线分销商数" prop="title">
                            <el-input type="number" size="small" v-model="ruleForm.update_condition" autocomplete="off">
                                <template slot="append">人</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="ruleForm.auto_update == 1 && ruleForm.update_type == 0" label="累计佣金总额" prop="title">
                            <el-input type="number" size="small" v-model="ruleForm.update_condition" autocomplete="off">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item class="switch" v-if="ruleForm.auto_update == 1 && ruleForm.update_type == 1" label="已提现佣金总额" prop="title">
                            <el-input type="number" size="small" v-model="ruleForm.update_condition" autocomplete="off">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item prop="rate" prop="rate">
                            <template slot='label'>
                                <span>分红比例</span>
                                <el-tooltip effect="dark" content="分红=商品实付金额*分红比例"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input placeholder="请输入分红比例" min="0.1" max="100" type="number" v-model="ruleForm.rate">
                                <template slot="append">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="是否启用" prop="status">
                            <el-switch
                                    v-model="ruleForm.status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                options: [],//会员等级列表
                ruleForm: {
                    pic_url: '',
                    bg_pic_url: '',
                    level: '',
                    name: '',
                    update_type: '0',
                    update_condition: '0',
                    rate: '',
                    status: '0',
                    price: '',
                    auto_update: '1',//累计满金额自动升级
                    rules: '',
                },
                rules: {
                    level: [
                        {required: true, message: '请选择队长等级', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入队长名称', trigger: 'change'},
                    ],
                    rate: [
                        {required: true, message: '请输入分红比例', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
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
                                r: 'plugin/bonus/mall/members/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'plugin/bonus/mall/members/index'
                                })
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

            tabType() {
                this.ruleForm.update_condition = 0;
            },

            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/bonus/mall/members/edit',
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
            bgPicUrl(e) {
                if (e.length) {
                    this.ruleForm.bg_pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('bg_pic_url');
                }
            },
            // 会员等级列表
            getOptions() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/bonus/mall/members/options',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.options = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getOptions();
        }
    });
</script>
