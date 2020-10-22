<?php
defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-select-member');

?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/recharge/index'})">充值管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>充值编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="form" v-loading="loading" label-width="10rem" :rules="FormRules" ref="form">
                <el-form-item prop="name">
                    <template slot='label'>
                        <span>充值名称</span>
                        <el-tooltip effect="dark" content="在充值管理显示"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input size="small" v-model="form.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item prop="pay_price">
                    <template slot='label'>
                        <span>支付金额</span>
                        <el-tooltip effect="dark" content="用户支付多少就充值多少"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input size="small" type="number"
                              oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                              v-model="form.pay_price" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="" prop="send_price">
                    <template slot='label'>
                        <span>赠送金额</span>
                        <el-tooltip effect="dark" content="用户充值时，赠送的金额，默认为0"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input size="small" type="number"
                              oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                              v-model="form.send_price" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item prop="send_price">
                    <template slot='label'>
                        <span>赠送积分</span>
                        <el-tooltip effect="dark" content="用户充值时，赠送的积分，默认为0"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input size="small" type="number"
                              oninput="this.value = this.value.match(/\d*/)"
                              v-model="form.send_integral"
                              autocomplete="off"
                    ></el-input>
                </el-form-item>
                <el-form-item prop="send_member_id" label="赠送会员">
                    <div flex="dir:left cross:center">
                        <el-tag @close="closeMember" v-if="tempMemberName" closable style="margin-right: 12px">
                            {{tempMemberName}}
                        </el-tag>
                        <app-select-member v-model="form.send_member_id" @change="changeSendMemberId">
                            <el-button type="small">选择会员等级</el-button>
                        </app-select-member>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" size='mini' :loading=btnLoading @click="onSubmit">保存</el-button>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                loading: false,
                btnLoading: false,
                FormRules: {
                    name: [
                        {required: true, message: '充值名称不能为空', trigger: 'blur'},
                    ],
                    pay_price: [
                        {required: true, message: '支付金额不能为空', trigger: 'blur'},
                    ]
                },
                tempMemberName: '',
            };
        },
        methods: {
            closeMember() {
                this.tempMemberName = '';
                this.form.send_member_id = 0;
            },
            changeSendMemberId(e) {
                if (e) {
                    this.tempMemberName = e.name;
                    this.form.send_member_id = e.id;
                }
            },
            // 提交数据
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign(this.form);
                        request({
                            params: {
                                r: 'mall/recharge/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                  message: e.data.msg,
                                  type: 'success'
                                });
                                setTimeout(function(){
                                    navigateTo({ r: 'mall/recharge/index' });
                                },300);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },

            //获取列表
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/recharge/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.list.id > 0) {
                            this.form = e.data.data.list;
                            this.tempMemberName = e.data.data.list.member.name;
                        }
                    }
                }).catch(e => {

                });
            },
        },

        created() {
            this.getList();
        }
    })
</script>