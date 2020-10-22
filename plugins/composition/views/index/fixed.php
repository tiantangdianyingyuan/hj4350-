<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-12
 * Time: 14:29
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('goods/app-dialog-select')
?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
        min-width: 820px;
    }

    .list {
        border: 1px solid #eeeeee;
        padding: 10px;
    }

    .list .item {
        border-bottom: 1px solid #eeeeee;
        padding: 5px 0;
    }

    .cover-pic {
        height: 50px;
        width: 50px;
        margin-right: 10px
    }

    .button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="loading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/composition/mall/index/list'})">套餐组合</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="id > 0">编辑固定套餐</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新建固定套餐</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div style="background: #ffffff;padding: 20px;">
            <el-form @submit.native.prevent :model="ruleForm" :rules="rules" size="small"
                     ref="ruleForm" label-width="120px">
                <el-card header="固定套餐设置" style="margin-bottom: 10px" shadow="never">
                    <div class="form-body">
                        <el-form-item label="套餐名称" prop="name">
                            <el-input v-model="ruleForm.name" size="small" placeholder="请输入套餐名称"></el-input>
                        </el-form-item>
                        <el-form-item label="选择商品" prop="list">
                            <div>
                                <div flex="dir:left">
                                    <app-dialog-select :default="ruleForm.list" :id="true" title="选择商品" :multiple="true" @selected="selectGoods" :max="max"
                                                       url="plugin/composition/mall/index/get-goods" :params="{type: 1}">
                                        <el-button size="mini">选择商品</el-button>
                                    </app-dialog-select>
                                    <div style="font-size: 10px;color: #b7b7b7;margin-left: 10px">最多选择5款商品</div>
                                </div>
                                <div class="list" v-if="ruleForm.list.length > 0">
                                    <div class="item" v-for="(item, index) in ruleForm.list"
                                         flex="dir:left cross:center">
                                        <div style="flex-grow: 0">
                                            <el-image class="cover-pic" :src="item.goodsWarehouse.cover_pic"></el-image>
                                        </div>
                                        <div flex="dir:top main:justify" style="flex-grow: 1">
                                            <app-ellipsis :line="1">{{item.name}}</app-ellipsis>
                                            <div style="color: #ff4544;">¥{{item.min_price}}{{item.max_price != item.min_price ? '~'+item.max_price : ''}}</div>
                                        </div>
                                        <div style="flex-grow: 0" v-if="item.is_delete != 0">
                                            <el-tag type="danger">商品不存在，请删除</el-tag>
                                        </div>
                                        <div style="flex-grow: 0" v-else-if="item.stock == 0">
                                            <el-tag type="danger">0库存</el-tag>
                                        </div>
                                        <div style="flex-grow: 0" flex="cross:center">
                                            <el-button class="button" type="text" @click="destroy(index)" size="small"
                                                       circle>
                                                <el-tooltip effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </div>
                                    </div>
                                    <div>合计金额
                                        <span style="color: #ff4544;">¥{{min_price}}{{max_price != min_price ? '~'+max_price : ''}}</span>
                                    </div>
                                </div>
                                <el-button v-else type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                    商城还未添加商品？点击前往
                                </el-button>
                            </div>
                        </el-form-item>
                        <el-form-item label="优惠金额" prop="price">
                            <div>
                                <el-input v-model="ruleForm.price" type="number" size="small">
                                    <span slot="append">元</span>
                                </el-input>
                            </div>
                            <div v-if="!error" style="font-size: 12px;color: #b7b7b7;margin-top: -8px">优惠金额不大于套餐金额</div>
                        </el-form-item>
                    </div>
                </el-card>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')"
                           size="small">保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                if (this.ruleForm.price === '') {
                    this.error = false;
                    callback(new Error('优惠金额不能为空'));
                } else if (+this.ruleForm.price < 0) {
                    this.error = false;
                    callback(new Error('优惠金额不能为负'));
                } else if (+this.ruleForm.price > +this.total_price) {
                    this.error = true;
                    callback(new Error('优惠金额不大于套餐金额'));
                } else {
                    this.error = false;
                    callback();
                }
            };
            var validateRate2 = (rule, value, callback) => {
                if (this.ruleForm.list.length < 2) {
                    callback(new Error('请至少选择两个商品'));
                } else {
                    callback();
                }
            };
            return {
                loading: false,
                btnLoading: false,
                ruleForm: {
                    name: '',
                    list: [],
                    price: '',
                    stock: '',
                },
                id: '',
                total_price: 0,
                max_price: 0,
                error: false,
                rules: {
                    name: [
                        {required: true, message: '请输入套餐名'}
                    ],
                    list: [
                        {required: true, validator: validateRate2, trigger: 'change'}
                    ],
                    price: [
                        {required: true, validator: validateRate, trigger: 'blur' }
                    ],
                    stock: [
                        {required: true, message: '库存不能为空'}
                    ]
                }
            };
        },
        created() {
            if (getQuery('id')) {
                this.id = getQuery('id');
                this.loadData();
            }
        },
        computed: {
            max() {
                return 5 - this.ruleForm.list.length;
            },
            maxPrice() {
                let price = 0;
                this.ruleForm.list.forEach(item => {
                    price += parseFloat(item.price);
                });
                return price.toFixed(2);
            }
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/composition/mall/index/fixed',
                        id: getQuery('id'),
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data;
                        for(let i in this.ruleForm.list) {
                            this.total_price += +this.ruleForm.list[i].min_price
                            if(this.ruleForm.list[i].max_price > 0) {
                                this.max_price += +this.ruleForm.list[i].max_price
                            }
                        }
                        this.min_price = this.total_price.toFixed(2);
                        this.max_price = this.max_price.toFixed(2);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            selectGoods(list) {
                for (let i in list) {
                    let join = true;
                    for (let x in this.ruleForm.list) {
                        if (this.ruleForm.list[x].id == list[i].id) {
                            join = false;
                        }
                    }
                    if (join) {
                        this.ruleForm.list.push(list[i]);
                        this.total_price = 0;
                        this.max_price = 0;
                        for(let i in this.ruleForm.list) {
                            this.total_price += +this.ruleForm.list[i].min_price
                            if(this.ruleForm.list[i].max_price > 0) {
                                this.max_price += +this.ruleForm.list[i].max_price
                            }
                        }
                        this.min_price = this.total_price.toFixed(2);
                        this.max_price = this.max_price.toFixed(2);
                    }
                }
                this.$refs['ruleForm'].clearValidate('list')
            },
            edit(item) {
                navigateTo({
                    r: 'mall/goods/edit',
                    id: item.id
                }, true);
            },
            destroy(index) {
                this.ruleForm.list.splice(index, 1);
                this.total_price = 0;
                this.max_price = 0;
                for(let i in this.ruleForm.list) {
                    this.total_price += +this.ruleForm.list[i].min_price
                    if(this.ruleForm.list[i].max_price > 0) {
                        this.max_price += +this.ruleForm.list[i].max_price
                    }
                }
                this.min_price = this.total_price.toFixed(2);
                this.max_price = this.max_price.toFixed(2);
                this.$refs['ruleForm'].validateField('list')
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        let form = self.ruleForm;
                        let price = 0;
                        for(let i in form.list) {
                            price += +form.list[i].min_price
                        }
                        if(+form.price > price) {
                            self.$message.error('优惠金额超过了套餐金额');
                            return false;
                        }
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/composition/mall/index/fixed'
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(form),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/composition/mall/index/list'
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
            }
        }
    });
</script>
