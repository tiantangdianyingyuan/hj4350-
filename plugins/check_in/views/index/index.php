<?php
/**
 * Created by PhpStorm.
 * User: fjt
 * Date: 2020/8/6
 * Time: 13:51
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    @media screen and (min-width:1370px){
        .form-body {
            padding: 20px 0;
            padding-right: 50%;
            min-width: 1400px;
        }
    }

    @media screen and (max-width:1369px){
        .form-body {
            padding: 20px 0;
            padding-right: 20%;
        }
    }

    .form-body {
        background-color: #fff;
        margin-bottom: 20px;
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
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">签到设置</div>
        <div v-if="load">
            <div class="form-body">
                <el-form ref="form" :model="form" :rules="rule" label-width="120px" size="small">
                    <el-form-item label="是否开启" prop="status">
                        <el-switch v-model="form.status" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>

                    <el-form-item label="签到提醒" prop="status">
                        <el-switch style="margin-right: 20px;" v-model="form.is_remind" :active-value="1" :inactive-value="0"></el-switch>
                        <el-time-select :disabled="form.is_remind == 0"
                                        v-model="form.time"
                                        value-format="HH:mm"
                                        :picker-options="time"
                                        placeholder="选择时间">
                        </el-time-select>
                    </el-form-item>
                    <el-form-item label="普通签到" prop="normal">
                        <label slot="label">普通签到
                            <el-tooltip class="item" effect="dark"
                                        content="每天签到赠送"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <el-input v-model.number="form.normal" type="number" placeholder="每天签到赠送">
                            <template slot="append">
                                <el-radio-group v-model="form.normal_type">
                                    <el-radio label="integral">积分</el-radio>
                                    <el-radio label="balance">余额</el-radio>
                                </el-radio-group>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="连续签到周期" prop="continue_type">
                        <label slot="label">连续签到周期
                            <el-tooltip class="item" effect="dark"
                                        content="清除连续签到天数"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <el-radio-group v-model.number="form.continue_type" class="ml-24">
                            <el-radio :label="1">不限</el-radio>
                            <el-radio :label="2">每周末</el-radio>
                            <el-radio :label="3">每月末</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="连续签到" prop="continue">
                        <label slot="label">连续签到
                            <el-tooltip class="item" effect="dark"
                                        content="连续签到额外送XX积分/余额，
                                            只要达到要求才能领取奖励，每个连续时间内只能领取一次连续签到奖励"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <el-card style="margin-bottom: 10px" shadow="never" v-if="form.continue && form.continue.length > 0" v-for="(item, index) in form.continue" :key="item.id">
                                <el-row type="flex">
                                    <el-col :span="21">
                                        <el-form-item label="连续签到天数" required>
                                            <el-input v-model.number="item.day" type="number"></el-input>
                                        </el-form-item>
                                        <el-form-item label="赠送数量" required>
                                            <el-input v-model.number="item.number" type="number">
                                                <template slot="append">
                                                    <el-radio-group v-model="item.type">
                                                        <el-radio label="integral">积分</el-radio>
                                                        <el-radio label="balance">余额</el-radio>
                                                    </el-radio-group>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="1"></el-col>
                                    <el-col :span="2">
                                        <el-button type="text" circle @click="continueDel(index)">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </el-col>
                            </el-row>
                        </el-card>
                        <el-button @click="continueAdd" size="small" type="text">
                            <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                            <span style="color: #353535;font-size: 14px">新增规则</span>
                        </el-button>
                    </el-form-item>
                    <el-form-item label="累计签到" prop="total">
                        <label slot="label">累计签到
                            <el-tooltip class="item" effect="dark"
                                        content="累计签到额外送XX积分/余额，只送一次"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <el-card shadow="never" v-if="form.total && form.total.length > 0" v-for="(item, index) in form.total" :key="item.id">
                            <el-row type="flex">
                                <el-col :span="23">
                                    <el-form-item label="累计签到天数" required>
                                        <el-input v-model.number="item.day" type="number"></el-input>
                                    </el-form-item>
                                    <el-form-item label="赠送数量" required>
                                        <el-input v-model.number="item.number" type="number">
                                            <template slot="append">
                                                <el-radio-group v-model="item.type">
                                                    <el-radio label="integral">积分</el-radio>
                                                    <el-radio label="balance">余额</el-radio>
                                                </el-radio-group>
                                            </template>
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="1"></el-col>
                                <el-col :span="2">
                                    <el-button size="small" type="text" @click="totalDel(index)">
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </el-col>
                            </el-row>
                        </el-card>
                        <el-button @click="totalAdd" size="small" type="text">
                            <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                            <span style="color: #353535;font-size: 14px">新增规则</span>
                        </el-button>
                    </el-form-item>
                    <el-form-item label="规则" prop="rule">
                        <div style="width: 458px; min-height: 458px;">
                            <app-rich-text v-model="form.rule"></app-rich-text>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <el-button class="button-item" :loading="btnLoading" type="primary" size="small" @click="store('form')">保存</el-button>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            function findEle(value, arr) {
                for (let i in arr) {
                    if (arr[i] == value) {
                        return true;
                    }
                }
                return false;
            }

            let listValidate = (rule, value, callback) => {
                let dayArr = [];
                for (let i in value) {
                    if (findEle(value[i].day, dayArr)) {
                        callback(new Error('不能设置相同的天数'));
                    }
                    dayArr.push(value[i].day);
                }
                callback();
            };
            return {
                loading: false,
                load: false,
                btnLoading: false,
                form: {
                    status: 0,
                    is_remind: 0,
                    time: '',
                    normal_type: 'integral',
                    normal: 0,
                    continue: [],
                    total: [],
                    continue_type: 1,
                    rule: ''
                },
                rule: {
                    continue: [
                        {validator: listValidate}
                    ],
                    total: [
                        {validator: listValidate}
                    ],
                    rule: [
                        {required: true, message: '请填写规则', trigger: 'change'},
                    ],
                    normal: [
                        {required: true, message: '请填写普通签到', trigger: 'change'}
                    ]
                },
                time: {
                    start: '00:00',
                    step: '00:15',
                    end: '23:45'
                },
                continue_temp: {
                    day: 0,
                    number: 0,
                    type: 'integral'
                }
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/check_in/mall/index/index'
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.config) {
                            this.form = e.data.data.config;
                        }
                        this.load = true;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e);
                    this.btnLoading = false;
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/check_in/mall/index/index'
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(this.form),
                                ruleForm: this.form
                            }
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg)
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        });
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                })
            },
            continueAdd() {
                if (!this.form.continue) {
                    this.form.continue = [];
                }
                this.form.continue.push(JSON.parse(JSON.stringify(this.continue_temp)));
            },
            totalAdd() {
                if (!this.form.total) {
                    this.form.total = [];
                }
                this.form.total.push(JSON.parse(JSON.stringify(this.continue_temp)));
            },
            continueDel(index) {
                if (this.form.continue && this.form.continue.length > index) {
                    this.form.continue.splice(index, 1);
                }
            },
            totalDel(index) {
                if (this.form.total && this.form.total.length > index) {
                    this.form.total.splice(index, 1);
                }
            }
        }
    });
</script>
