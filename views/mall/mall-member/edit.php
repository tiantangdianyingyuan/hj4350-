<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
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
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/mall-member/index'})">会员等级</span></el-breadcrumb-item>
                <el-breadcrumb-item>会员设置</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="会员等级" prop="level">
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
                        <el-form-item label="等级名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入等级名称"></el-input>
                        </el-form-item>
                        <el-form-item prop="discount" prop="discount">
                            <template slot='label'>
                                <span>折扣</span>
                                <el-tooltip effect="dark" content="请输入0.1~10之间的数字"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input placeholder="请输入折扣" min="0.1" type="number" v-model="ruleForm.discount">
                                <template slot="append">折</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="会员状态" prop="status">
                            <el-switch
                                    v-model="ruleForm.status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="会员图标" prop="pic_url">
                            <app-attachment :multiple="false" :max="1" @selected="picUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸44*44" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px" height="80px" mode="aspectFill" :src="ruleForm.pic_url"></app-image>
                        </el-form-item>
                        <el-form-item label="会员背景图" prop="bg_pic_url">
                            <app-attachment :multiple="false" :max="1" @selected="bgPicUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸660*320" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px" height="80px" mode="aspectFill" :src="ruleForm.bg_pic_url"></app-image>
                        </el-form-item>
                        <el-form-item label="累计金额自动升级">
                            <el-switch
                                    v-model="ruleForm.auto_update"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.auto_update == 1" label="升级条件" prop="money">
                            <el-input placeholder="请输入金额" min="0" type="number" v-model="ruleForm.money">
                                <template slot="prepend">累计完成订单金额满</template>
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="会员是否可购买">
                            <el-switch
                                    v-model="ruleForm.is_purchase"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.is_purchase == 1" label="购买价格" prop="price">
                            <el-input placeholder="请输入购买价格" min="0" type="number" v-model="ruleForm.price">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="会员权益(多条)" prop="rights" style="padding-right: 0">
                    <el-table
                            style="margin-bottom: 15px;"
                            v-if="ruleForm.rights.length > 0"
                            :data="ruleForm.rights"
                            border
                            style="width: 100%;">
                        <el-table-column
                                label="权益标题"
                                width="180">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.title" placeholder="请输入标题"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="权益图标"
                                width="180">
                            <template slot-scope="scope">
                                <div flex="box:first">
                                    <div flex="cross:center" style="margin-right: 10px;">
                                        <app-attachment :multiple="false" :params="scope.row" :max="1"
                                                        @selected="rightsPicUrl">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸80*80" placement="top">
                                                <el-button size="mini">选择图片</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                    </div>
                                    <div>
                                        <app-image mode="aspectFill" :src="scope.row.pic_url">
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="权益内容" width="600">
                            <template slot-scope="scope">
                                <el-input type="textarea"
                                          v-model="scope.row.content"
                                          placeholder="请输入内容">
                                </el-input>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="操作">
                            <template slot-scope="scope">
                                <el-button size="small" @click="destroyRigths(scope.$index)" type="text" circle>
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-button type="text" @click="addRights">
                        <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                        <span style="color: #353535;font-size: 14px">新增权益</span>
                    </el-button>
                </el-form-item>

                <el-form-item label="会员规则" prop="rules">
                    <app-rich-text style="width: 455px" v-model="ruleForm.rules"></app-rich-text>
                </el-form-item>
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
                    money: '',
                    discount: '',
                    status: '0',
                    price: '',
                    rights: [],
                    is_purchase: '1',
                    auto_update: '1',//累计满金额自动升级
                    rules: '',
                },
                rules: {
                    level: [
                        {required: true, message: '请选择会员等级', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入会员名称', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请选择会员图标', trigger: 'change'},
                    ],
                    bg_pic_url: [
                        {required: true, message: '请选择会员背景图', trigger: 'change'},
                    ],
                    money: [
                        {required: true, message: '请输入会员升级条件金额', trigger: 'change'},
                    ],
                    discount: [
                        {required: true, message: '请输入会员折扣', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '请选择会员状态', trigger: 'change'},
                    ],
                    price: [
                        {required: true, message: '请输入会员价格', trigger: 'change'},
                    ],
//                    rights: [
//                        {required: true, message: '请添加会员权益', trigger: 'change'},
//                    ],
//                    rules: [
//                        {required: true, message: '请添加会员规则', trigger: 'change'},
//                    ],
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
                                r: 'mall/mall-member/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'mall/mall-member/index'
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/mall-member/edit',
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
            rightsPicUrl(e, params) {
                if (e.length) {
                    params.pic_url = e[0].url;
                }
            },
            // 会员等级列表
            getOptions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/mall-member/options',
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
            // 添加权益
            addRights() {
                this.ruleForm.rights.push({
                    id: 0,
                    title: '',
                    pic_url: '',
                    content: '',
                })
            },
            // 删除权益
            destroyRigths(index) {
                this.ruleForm.rights.splice(index, 1);
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getOptions();
        }
    });
</script>
