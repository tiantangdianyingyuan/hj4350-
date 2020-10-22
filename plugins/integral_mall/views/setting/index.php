<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }

    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
        background-color: #fff;
        margin-bottom: 10px;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 25px;
    }
    .red {
        padding: 0px 25px;
        color: #ff4544;
    }

    .setting-item {
        background-color: #f3f3f3;
        padding-bottom: 10px;
    }

    .setting-item:last-of-type {
        padding-bottom: 0;
    }

    .setting-item .el-card {
        background-color: #fff
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="cardLoading">
        <div class="text item" style="width:100%">
            <el-form :model="ruleForm" label-width="150px" :rules="rules" ref="ruleForm">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础设置" class="form-body" name="first">
                        <div class="setting-item">
                            <app-setting v-model="ruleForm" :is_member_price="false" :is_integral="false"></app-setting>
                        </div>

                        <div class="setting-item">
                            <el-card shadow="never">
                                <div slot="header">
                                    <span>说明设置</span>
                                </div>
                                <el-form-item label="积分说明" prop="desc">
                                    <div style="width: 458px; min-height: 458px;">
                                        <app-rich-text v-model="ruleForm.rule"></app-rich-text>
                                    </div>
<!--                                    <el-table-->
<!--                                            style="margin-bottom: 15px;width: 99%"-->
<!--                                            v-if="ruleForm.desc.length > 0"-->
<!--                                            :data="ruleForm.desc"-->
<!--                                            border>-->
<!--                                        <el-table-column-->
<!--                                                label="标题"-->
<!--                                                width="250">-->
<!--                                            <template slot-scope="scope">-->
<!--                                                <el-input v-model="scope.row.title" placeholder="请输入标题"></el-input>-->
<!--                                            </template>-->
<!--                                        </el-table-column>-->
<!--                                        <el-table-column-->
<!--                                                label="内容">-->
<!--                                            <template slot-scope="scope">-->
<!--                                                <el-input type="textarea" v-model="scope.row.content" placeholder="请输入内容">-->
<!--                                                </el-input>-->
<!--                                            </template>-->
<!--                                        </el-table-column>-->
<!--                                        <el-table-column-->
<!--                                                label="操作">-->
<!--                                            <template slot-scope="scope">-->
<!--                                                <el-button size="small" @click="destroyIntegralDesc(scope.$index)" type="text"-->
<!--                                                           circle>-->
<!--                                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">-->
<!--                                                        <img src="statics/img/mall/del.png" alt="">-->
<!--                                                    </el-tooltip>-->
<!--                                                </el-button>-->
<!--                                            </template>-->
<!--                                        </el-table-column>-->
<!--                                    </el-table>-->
<!--                                    <el-button type="text" @click="addIntegralDesc">-->
<!--                                        <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>-->
<!--                                        <span style="color: #353535;font-size: 14px">新增积分说明</span>-->
<!--                                    </el-button>-->
                                </el-form-item>
                            </el-card>
                        </div>
                    </el-tab-pane>
<!--                    <el-tab-pane v-if="false"  label="自定义海报" class="form-body" name="second">-->
<!--                        <app-poster :rule_form="ruleForm.goods_poster"-->
<!--                                    :goods_component="goodsComponent"-->
<!--                        ></app-poster>-->
<!--                    </el-tab-pane>-->
                    <el-tab-pane label="轮播图" class="form-body" name="third">
                        <app-banner :title="false" url="plugin/integral_mall/mall/banner/index" submit_url="plugin/integral_mall/mall/banner/edit"></app-banner>
                    </el-tab-pane>
                </el-tabs>
                <el-button v-if="activeName !='third'" class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')"
                           size="small">保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    desc: [],
                    is_share: 0,
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    is_territorial_limitation: 0,
                    send_type: ['express', 'offline'],
                    payment_type: ['online_pay'],
                    rule: ''
                },
                rules: {},
                btnLoading: false,
                cardLoading: false,
                is_show: false,
                activeName: 'first',
                FormRules: {
                    is_cat: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_share: [
                        {required: true, message: '分销不能为空', trigger: 'blur'},
                    ],
                    is_sms: [
                        {required: true, message: '短信提醒不能为空', trigger: 'blur'},
                    ],
                    is_mail: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_print: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_form: [
                        {required: true, message: '显示表单不能为空', trigger: 'blur'},
                    ]
                },
                goodsComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_nickname.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '商品名称',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品价格',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'poster_bg',
                        icon_url: 'statics/img/mall/poster/icon-mark.png',
                        title: '标识',
                        is_active: true
                    }
                ],
            };
        },
        computed: {
            send_type_list() {
                let list = [];
                for (let i in this.ruleForm.send_type) {
                    if (this.ruleForm.send_type[i] == 'express') {
                        list.push('快递配送');
                    }
                    if (this.ruleForm.send_type[i] == 'offline') {
                        list.push('到店自提');
                    }
                    if (this.ruleForm.send_type[i] == 'city') {
                        list.push('同城配送');
                    }
                }
                return list;
            }
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/integral_mall/mall/setting/index',
                    },
                }).then(e => {
                    this.cardLoading = false;
                    this.is_show = true;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.setting;
                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/integral_mall/mall/setting/index'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
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
            // 添加权益
            addIntegralDesc() {
                this.ruleForm.desc.push({
                    title: '',
                    content: '',
                })
            },
            // 删除权益
            destroyIntegralDesc(index) {
                this.ruleForm.desc.splice(index, 1);
            }
        },

        mounted: function () {
            this.getDetail();
        }
    })
</script>