<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/19
 * Time: 9:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
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
        padding-right: 25%;
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
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/share/level'})">
                        分销商等级
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑分销商等级</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="分销商等级选择" prop="level">
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
                        <el-form-item label="分销商等级名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入分销商等级名称"></el-input>
                        </el-form-item>
                        <el-form-item label="是否启用自动升级" prop="is_auto_level">
                            <el-switch
                                    v-model="ruleForm.is_auto_level"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <template v-if="ruleForm.is_auto_level == 1">
                            <el-form-item label="升级条件" prop="condition_type">
                                <el-radio-group v-model="ruleForm.condition_type">
                                    <el-radio :label="1">下线用户数</el-radio>
                                    <el-radio :label="2">累计佣金</el-radio>
                                    <el-radio :label="3">已提现佣金</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item prop="condition" required>
                                <template v-if="ruleForm.condition_type == 1">
                                    <label slot="label">下线用户数</label>
                                    <el-input placeholder="请输入下线用户数" type="number" v-model="ruleForm.condition">
                                        <template slot="append">人</template>
                                    </el-input>
                                </template>
                                <template v-if="ruleForm.condition_type == 2">
                                    <label slot="label">累计佣金</label>
                                    <el-input placeholder="请输入累计佣金" type="number" v-model="ruleForm.condition">
                                        <template slot="append">元</template>
                                    </el-input>
                                </template>
                                <template v-if="ruleForm.condition_type == 3">
                                    <label slot="label">已提现佣金</label>
                                    <el-input placeholder="请输入已提现佣金" type="number" v-model="ruleForm.condition">
                                        <template slot="append">元</template>
                                    </el-input>
                                </template>
                            </el-form-item>
                        </template>
                        <el-form-item label="分销佣金类型" prop="price_type" required>
                            <el-radio-group v-model="ruleForm.price_type">
                                <el-radio :label="1">百分比</el-radio>
                                <el-radio :label="2">固定金额</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="一级佣金" prop="first" v-if="level > 0">
                            <el-input v-model.number="ruleForm.first" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="二级佣金" prop="second" v-if="level > 1">
                            <el-input v-model.number="ruleForm.second" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="三级佣金" prop="third" v-if="level > 2">
                            <el-input v-model.number="ruleForm.third" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="是否启用" prop="status">
                            <el-switch
                                v-model="ruleForm.status"
                                :active-value="1"
                                :inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="等级说明" prop="rule">
                            <el-input type="textarea" :rows="3" placeholder="请输入等级说明"
                                      v-model="ruleForm.rule" maxlength="80" show-word-limit></el-input>
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
                level: 0,
                ruleForm: {
                    level: '',
                    name: '',
                    status: 0,
                    condition_type: 1,
                    condition: 0,
                    price_type: 1,
                    first: 0,
                    second: 0,
                    third: 0,
                    is_auto_level: 1,
                    rule: '',
                },
                rules: {
                    level: [
                        {required: true, message: '请选择分销商等级', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入分销商等级名称', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '请选择分销商等级状态', trigger: 'change'},
                    ],
                    rule: [
                        {required: true, message: '等级说明不能为空', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
            this.getOptions();
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/share/level-edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'mall/share/level'
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
            loadData() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'mall/share/level-edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.detail) {
                            this.ruleForm = e.data.data.detail;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getOptions() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'mall/share/options',
                    },
                    method: 'get',
                }).then(response => {
                    this.cardLoading = false;
                    if (response.data.code == 0) {
                        this.options = response.data.data.list;
                        this.level = response.data.data.level
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.cardLoading = false;
                    console.log(e);
                });
            }
        }
    });
</script>
