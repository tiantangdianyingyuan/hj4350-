<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-12
 * Time: 14:30
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('goods/app-dialog-select');
Yii::$app->loadViewComponent('goods/app-attr');
?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
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
                <el-breadcrumb-item v-if="id > 0">编辑搭配套餐</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新建搭配套餐</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div style="background: #ffffff;padding: 20px;">
            <el-form @submit.native.prevent :model="ruleForm" :rules="rules" size="small"
                     ref="ruleForm" label-width="120px">
                <el-card header="搭配套餐设置" shadow="never" style="margin-bottom: 10px;">
                    <div class="form-body">
                        <el-form-item label="套餐名称" prop="name">
                            <el-input v-model="ruleForm.name" size="small" placeholder="请输入套餐名称"></el-input>
                        </el-form-item>
                        <el-form-item label="选择主商品" prop="host_list">
                            <div>
                                <div flex="dir:left">
                                    <app-dialog-select :default="ruleForm.list" title="选择主商品" :multiple="false" @selected="selectHostGoods"
                                                       url="plugin/composition/mall/index/get-goods" :params="params">
                                        <el-button size="mini">选择商品</el-button>
                                    </app-dialog-select>
                                    <div style="font-size: 10px;color: #b7b7b7;margin-left: 10px">最多选择1款商品</div>
                                </div>
                                <div class="list" v-if="ruleForm.host_list.length > 0">
                                    <div class="item" v-for="(item, index) in ruleForm.host_list" :key="index"
                                         flex="dir:left cross:center">
                                        <div style="flex-grow: 0">
                                            <el-image class="cover-pic" :src="item.goodsWarehouse.cover_pic"></el-image>
                                        </div>
                                        <div flex="dir:top main:justify" style="flex-grow: 1;margin-right: 15px;">
                                            <app-ellipsis :line="1">{{item.name}}</app-ellipsis>
                                            <div flex="dir:left">
                                                <div style="color: #ff4544;">¥{{item.min_price}}{{item.max_price != item.min_price ? '~'+item.max_price : ''}}</div>
                                                <div style="color: #ffa525;" v-if="item.discounts_price">（优惠金额：￥{{item.discounts_price}}）</div>
                                            </div>
                                        </div>
                                        <div style="flex-grow: 0" v-if="item.is_delete != 0">
                                            <el-tag type="danger">商品不存在，请删除</el-tag>
                                        </div>
                                        <div style="flex-grow: 0" v-else-if="item.stock == 0">
                                            <el-tag type="danger">0库存</el-tag>
                                        </div>
                                        <div style="flex-grow: 0" v-else>
                                            <el-button size="mini" type="primary" plain @click="editPrice(item, -1)">
                                                优惠金额<i class="el-icon-edit el-icon--right"></i>
                                            </el-button>
                                        </div>
                                        <div style="flex-grow: 0;margin-left: 20px" flex="dir:left cross:center">
                                            <el-button class="button" type="text" @click="destroy(index,0)"
                                                       size="small" circle>
                                                <el-tooltip effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                                <el-button v-else type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                    商城还未添加商品？点击前往
                                </el-button>
                            </div>
                        </el-form-item>
                        <el-form-item label="选择搭配商品" prop="list">
                            <div>
                                <div flex="dir:left">
                                    <app-dialog-select :default="ruleForm.list" title="选择搭配商品" :multiple="true" @selected="selectGoods" :max="max"
                                                       url="plugin/composition/mall/index/get-goods" :params="params">
                                        <el-button size="mini">选择商品</el-button>
                                    </app-dialog-select>
                                    <div style="font-size: 10px;color: #b7b7b7;margin-left: 10px">最多选择4款商品</div>
                                </div>
                                <div class="list" v-if="ruleForm.list.length > 0">
                                    <div class="item" v-for="(item, index) in ruleForm.list" :key="index"
                                         flex="dir:left cross:center">
                                        <div style="flex-grow: 0">
                                            <el-image class="cover-pic" :src="item.goodsWarehouse.cover_pic"></el-image>
                                        </div>
                                        <div flex="dir:top main:justify" style="flex-grow: 1;margin-right: 15px;">
                                            <app-ellipsis :line="1">{{item.name}}</app-ellipsis>
                                            <div flex="dir:left">
                                                <div style="color: #ff4544;">¥{{item.min_price}}{{item.max_price != item.min_price ? '~'+item.max_price : ''}}</div>
                                                <div style="color: #ffa525;" v-if="item.discounts_price">（优惠金额：￥{{item.discounts_price}}）</div>
                                            </div>
                                        </div>
                                        <div style="flex-grow: 0" v-if="item.is_delete != 0">
                                            <el-tag type="danger">商品不存在，请删除</el-tag>
                                        </div>
                                        <div style="flex-grow: 0" v-else-if="item.stock == 0">
                                            <el-tag type="danger">0库存</el-tag>
                                        </div>
                                        <div style="flex-grow: 0" v-else>
                                            <el-button size="mini" type="primary" plain @click="editPrice(item, index)">
                                                优惠金额<i class="el-icon-edit el-icon--right"></i>
                                            </el-button>
                                        </div>
                                        <div style="flex-grow: 0;margin-left: 20px" flex="dir:left cross:center">
                                            <el-button class="button" type="text" @click="destroy(index,1)"
                                                       size="small" circle>
                                                <el-tooltip effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                                <el-button v-else type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                    商城还未添加商品？点击前往
                                </el-button>
                            </div>
                        </el-form-item>
                    </div>
                </el-card>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')"
                           size="small">保存
                </el-button>
            </el-form>
            <el-dialog
                    title="设置优惠金额"
                    :visible.sync="dialogVisible"
                    width="30%" :close-on-click-modal="false"
                    :before-close="handleClose">
                <div v-if="goods">
                    <el-form @submit.native.prevent :model="goods" ref="goods" size="small" label-width="20%" :rules="discounts">
                        <el-form-item label="优惠金额" prop="discounts">
                            <el-input style="width: 70%;" v-model="goods.discounts_price" type="number" min="goods.min_price">
                                <span slot="append">元</span>
                            </el-input>
                        </el-form-item>
                    </el-form>

                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button type="primary" @click="goodsConfirm('goods')">确 定</el-button>
                </div>
            </el-dialog>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                console.log(this.goods.discounts_price,this.goods.min_price)
                if (this.goods.discounts_price === '') {
                    callback(new Error('请输入优惠金额'));
                } else if(this.goods.discounts_price < 0) {
                    callback(new Error('优惠金额不得为负数'));
                } else if(this.goods.discounts_price > +this.goods.min_price) {
                    callback(new Error('优惠金额不能大于商品最低价'));
                } else {
                    callback();
                }
            };
            return {
                loading: false,
                btnLoading: false,
                dialogVisible: false,
                ruleForm: {
                    name: '',
                    list: [],
                    price: 0,
                    stock: 0,
                    host_list: [],
                },
                id: '',
                rules: {
                    name: [
                        {required: true, message: '请输入套餐名'}
                    ],
                    list: [
                        {required: true, message: '请至少选择一个商品'}
                    ],
                    host_list: [
                        {required: true, message: '主商品必须选择'}
                    ],
                },
                discounts: {
                    discounts: [
                        {required: true, validator: validateRate,trigger: 'blur'},
                    ]
                },
                goods: null,
                index: -1,
                attrDefault: {
                    price: '套餐价格',
                    stock: '套餐库存'
                }
            };
        },
        created() {
            if (getQuery('id')) {
                this.id = getQuery('id')
                this.loadData();
            }
        },
        computed: {
            max() {
                return 4 - this.ruleForm.list.length;
            },
            maxPrice() {
                let price = 0;
                this.ruleForm.list.forEach(item => {
                    price += parseFloat(item.price);
                });
                return price.toFixed(2);
            },
            params() {
                let host_id = null;
                if (this.ruleForm.host_list.length > 0) {
                    host_id = this.ruleForm.host_list[0].id;
                }
                return {
                    type: 2,
                    host_id: host_id
                };
            }
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/composition/mall/index/goods',
                        id: getQuery('id'),
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            selectHostGoods(list) {
                list.is_host = 1;
                this.ruleForm.host_list = [list];
                this.$refs['ruleForm'].clearValidate('host_list')
            },
            selectGoods(list) {
                // this.ruleForm.list = this.ruleForm.list.concat(list);

                for (let i in list) {
                    let join = true;
                    for (let x in this.ruleForm.list) {
                        if (this.ruleForm.list[x].id == list[i].id) {
                            join = false;
                        }
                    }
                    if (join) {
                        this.ruleForm.list.push(list[i])
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
            destroy(index,type) {
                if(type == 0) {
                    this.ruleForm.host_list.splice(index, 1);
                }else {
                    this.ruleForm.list.splice(index, 1);
                }
                this.$refs['ruleForm'].validateField('list')
            },
            destroyHost() {
                this.ruleForm.host_list = [];
                this.$refs['ruleForm'].validateField('host_list')
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        if(!self.ruleForm.host_list[0].discounts_price) {
                            self.$message.error('请设置主商品的优惠金额');
                            return false;
                        }
                        for(let i in self.ruleForm.list) {
                            if(!self.ruleForm.list[i].discounts_price) {
                                self.$message.error('请设置搭配商品的优惠金额');
                                return false;
                            }
                        }
                        self.btnLoading = true;
                        let form = JSON.parse(JSON.stringify(self.ruleForm));
                        form.list.push(form.host_list[0]);
                        request({
                            params: {
                                r: 'plugin/composition/mall/index/goods'
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
            },
            editPrice(item, index) {
                this.goods = JSON.parse(JSON.stringify(item));
                this.index = index;
                this.dialogVisible = true;
            },
            handleClose() {
                this.goods = null;
                this.index = -1;
                this.dialogVisible = false;
            },
            goodsConfirm(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        this.goods.discounts_price = parseFloat(this.goods.discounts_price).toFixed(2);
                        if (this.index == -1) {
                            this.ruleForm.host_list[0] = JSON.parse(JSON.stringify(this.goods));
                        } else {
                            this.ruleForm.list[this.index] = JSON.parse(JSON.stringify(this.goods));
                        }
                        this.handleClose();
                    }
                })
            }
        }
    });
</script>

