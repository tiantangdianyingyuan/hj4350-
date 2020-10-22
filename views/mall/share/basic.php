<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/14
 * Time: 13:31
 */
Yii::$app->loadViewComponent('app-dialog-select');
Yii::$app->loadViewComponent('app-select-cat');
?>
<style>
    .form_box {
        background-color: #f3f3f3;
        padding: 0 0 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card shadow="never">
                    <div slot="header">
                        <div>
                            <span>分销设置</span>
                        </div>
                    </div>
                    <div>
                        <el-row>
                            <el-col :span="16">
                                <el-form-item label="分销层级" prop="level" required>
                                    <el-radio-group v-model.number="ruleForm.level">
                                        <el-radio :label="0">关闭</el-radio>
                                        <el-radio :label="1">一级分销</el-radio>
                                        <el-radio :label="2">二级分销</el-radio>
                                        <el-radio :label="3">三级分销</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item label="分销内购" prop="is_rebate" required>
                                    <label slot="label">分销内购
                                        <el-tooltip class="item" effect="dark"
                                                    content="开启分销内购，分销商自己购买商品，享受一级佣金，上级享受二级佣金，上上级享受三级佣金"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-switch v-model="ruleForm.is_rebate" :active-value="1" :inactive-value="0">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item label="成为下线的条件" prop="condition" required>
                                    <el-radio-group v-model="ruleForm.condition">
                                        <el-radio :label="1">首次点击链接</el-radio>
                                        <el-radio :label="2">首次下单</el-radio>
                                        <el-radio :label="3">首次付款</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item label="申请成为分销商" prop="condition" required>
                                    <el-radio-group v-model="ruleForm.share_condition">
                                        <el-radio :label="2">申请（填信息）需审核</el-radio>
                                        <el-radio :label="4">申请（填信息）无需审核</el-radio>
                                        <el-radio :label="1">申请（不填信息）需审核</el-radio>
                                        <el-radio :label="3">申请（不填信息）无需审核</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item label="成为分销商的条件" required>
                                    <div>
                                        <el-radio-group v-model="ruleForm.become_condition">
                                            <el-radio :label="1">单次消费</el-radio>
                                            <el-radio :label="2">购买商品</el-radio>
                                            <el-radio :label="3">无条件</el-radio>
                                        </el-radio-group>
                                    </div>
                                    <div v-if="ruleForm.become_condition == 1">
                                        <el-form-item prop="auto_share_val">
                                            <el-input type="number" v-model.number="ruleForm.auto_share_val">
                                                <template slot="prepend">消费金额</template>
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>
                                    </div>
                                    <div v-if="ruleForm.become_condition == 2">
                                        <el-radio-group v-model="ruleForm.share_goods_status" flex="cross:center">
                                            <el-radio :label="1">任意商品</el-radio>
                                            <el-radio :label="2">
                                                <div style="display: inline-block;">
                                                    <div flex="cross:center">
                                                        <div>指定商品</div>
                                                        <div style="margin-left: 10px;"
                                                             v-if="ruleForm.share_goods_status==2">
                                                            <app-dialog-select :multiple="true" @selected="goodsSelect"
                                                                               title="商品选择">
                                                                <el-button type="text">选择商品</el-button>
                                                            </app-dialog-select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-radio>
                                            <el-radio :label="3">
                                                <div style="display: inline-block;">
                                                    <div flex="cross:center">
                                                        <div>指定分类</div>
                                                        <div style="margin-left: 10px;"
                                                             v-if="ruleForm.share_goods_status==3">
                                                            <div style="display: inline-block">
                                                                <el-button type="text" @click="cat_show=true">选择分类
                                                                </el-button>
                                                            </div>
                                                        </div>
                                                        <app-select-cat :show="cat_show" v-model="ruleForm.cat_list"
                                                                        @cancel="cat_show=false"></app-select-cat>
                                                    </div>
                                                </div>
                                            </el-radio>
                                        </el-radio-group>
                                        <el-form-item prop="share_goods_warehouse_id">
                                            <template v-if="ruleForm.share_goods_status==2">
                                                <div style="color: #ff4544;">最多可添加20个商品</div>
                                                <div style="max-height: 300px;overflow-y: auto">
                                                    <el-table :data="ruleForm.goods_list" :show-header="false" border>
                                                        <el-table-column label="">
                                                            <template slot-scope="scope">
                                                                <div flex>
                                                                    <div style="padding-right: 10px;flex-grow: 0">
                                                                        <app-image mode="aspectFill"
                                                                                   :src="scope.row.cover_pic"></app-image>
                                                                    </div>
                                                                    <div style="flex-grow: 1;">
                                                                        <app-ellipsis :line="2">{{scope.row.name}}
                                                                        </app-ellipsis>
                                                                    </div>
                                                                    <div style="flex-grow: 0;">
                                                                        <el-button @click="deleteGoods(scope.$index)"
                                                                                   type="text" circle size="mini">
                                                                            <el-tooltip class="item" effect="dark"
                                                                                        content="删除" placement="top">
                                                                                <img src="statics/img/mall/del.png" alt="">
                                                                            </el-tooltip>
                                                                        </el-button>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                </div>
                                            </template>
                                            <template v-if="ruleForm.share_goods_status == 3">
                                                <label>已选择分类：</label>
                                                <el-tag style="margin-right: 5px;margin-bottom: 5px;"
                                                        v-for="(item,index) in ruleForm.cat_list"
                                                        :key="item.value"
                                                        v-model="ruleForm.cat_list"
                                                        type="warning"
                                                        closable
                                                        disable-transitions
                                                        @close="destroyCat(item.value,index)">
                                                    {{item.label}}
                                                </el-tag>
                                            </template>
                                        </el-form-item>
                                    </div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">
                        <div>分销佣金设置
                            <el-tooltip class="item" effect="dark"
                                        content="需要开启分销层级，才能设置对应的分销佣金"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </div>
                    </div>
                    <el-col :span="14">
                        <el-form-item label="分销佣金类型" prop="price_type" v-if="ruleForm.level > 0">
                            <el-radio-group v-model="ruleForm.price_type">
                                <el-radio :label="1">百分比</el-radio>
                                <el-radio :label="2">固定金额</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="一级佣金" prop="first" v-if="ruleForm.level > 0">
                            <el-input v-model.number="ruleForm.first" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="二级佣金" prop="second" v-if="ruleForm.level > 1">
                            <el-input v-model.number="ruleForm.second" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="三级佣金" prop="third" v-if="ruleForm.level > 2">
                            <el-input v-model.number="ruleForm.third" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-card>
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">
                        <div>分销商等级设置</div>
                    </div>
                    <el-col :span="14">
                        <el-form-item label="分销商等级入口" style="margin-bottom: 0">
                            <div>
                                <el-switch v-model="ruleForm.is_show_share_level" :active-value="1" :inactive-value="0">
                                </el-switch>
                            </div>
                        </el-form-item>
                        <el-form-item>
                            <label slot="label">
                                <el-button type="text" @click="show_share_level = true">查看图例</el-button>
                            </label>
                            <el-dialog
                                    title="查看分销商等级入口图例"
                                    :visible.sync="show_share_level"
                                    width="30%">
                                <div style="text-align: center">
                                    <image src="statics/img/mall/is_show_share_level.png"></image>
                                </div>
                                <div slot="footer" class="dialog-footer">
                                    <el-button type="primary" @click="show_share_level = false">我知道了</el-button>
                                </div>
                            </el-dialog>
                        </el-form-item>
                    </el-col>
                </el-card>
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">提现设置</div>
                    <div>
                        <el-row>
                            <el-col :span="14">
                                <el-form-item label="提现方式" prop="pay_type" required>
                                    <label slot="label">提现方式
                                        <el-tooltip class="item" effect="dark"
                                                    content="自动打款支付，需要申请相应小程序的相应功能，
                                                    例如：微信需要申请企业付款到零钱功能"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-checkbox-group v-model="ruleForm.pay_type">
                                        <el-checkbox label="auto">自动打款</el-checkbox>
                                        <el-checkbox label="wechat">微信线下转账</el-checkbox>
                                        <el-checkbox label="alipay">支付宝线下转账</el-checkbox>
                                        <el-checkbox label="bank">银行卡线下转账</el-checkbox>
                                        <el-checkbox label="balance">余额提现</el-checkbox>
                                    </el-checkbox-group>
                                </el-form-item>
                                <el-form-item label="最少提现额度" prop="min_money" required>
                                    <el-input type="number" v-model.number="ruleForm.min_money">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="每日提现上限" prop="cash_max_day" required>
                                    <label slot="label">每日提现上限
                                        <el-tooltip class="item" effect="dark"
                                                    content="-1元表示不限制每日提现金额"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="number" v-model.number="ruleForm.cash_max_day">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="提现手续费" prop="cash_service_charge" required>
                                    <label slot="label">提现手续费
                                        <el-tooltip class="item" effect="dark"
                                                    content="0表示不设置提现手续费"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="number" v-model.number="ruleForm.cash_service_charge">
                                        <template slot="append">%</template>
                                    </el-input>
                                    <div>
                                        <span class="text-danger">提现手续费额外从提现中扣除</span><br>
                                        例如：<span style="color: #F56C6C;font-size: 12px">10%</span>的提现手续费：<br>
                                        提现<span style="color: #F56C6C;font-size: 12px">100</span>元，扣除手续费<span
                                                style="color: #F56C6C;font-size: 12px">10</span>元，
                                        实际到手<span style="color: #F56C6C;font-size: 12px">90</span>元
                                    </div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">页面设置</div>
                    <div>
                        <el-form-item label="申请协议" prop="agree">
                            <el-input style="width: 800px" type="textarea"
                                      :rows="4"
                                      placeholder="申请协议"
                                      v-model="ruleForm.agree">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="用户须知" prop="content">
                            <el-input style="width: 800px" type="textarea"
                                      :rows="4"
                                      placeholder="用户须知"
                                      v-model="ruleForm.content">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="待审核页面背景图片" prop="pic_url_status" title="选择图片">
                            <app-attachment :multiple="false" :max="1" @selected="picUrlStatus">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 300"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-gallery :show-delete="true" @deleted="deleteUrlStatus"
                                         :url="ruleForm.pic_url_status"></app-gallery>
                        </el-form-item>
                        <el-form-item label="首页背景图片" prop="pic_url_home_head" title="选择图片">
                            <app-attachment :multiple="false" :max="1" @selected="picUrlHomeHead">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 312"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-gallery :show-delete="true" @deleted="deleteHomeHead"
                                         :url="ruleForm.pic_url_home_head"></app-gallery>
                        </el-form-item>
                        <el-form-item label="自定义表单" prop="form">
                            <el-switch v-model="ruleForm.form_status" :active-value="1" :inactive-value="0"></el-switch>
                            <div style="margin-top: 15px">
                                <app-form @update:value="updateForm" v-if="ruleForm.form_status == 1" :value="ruleForm.form"></app-form>
                            </div>
                        </el-form-item>
                    </div>
                </el-card>
            </el-form>
        </div>
        <el-button :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"
                   @click="store('ruleForm')" size="small">保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let firstValidate = (rule, value, callback) => {
                if (this.ruleForm.level > 0 && !value && value !== 0) {
                    callback(new Error('一级佣金不能为空'));
                }
                callback();
            };
            let secondValidate = (rule, value, callback) => {
                if (this.ruleForm.level > 1 && !value && value !== 0) {
                    callback(new Error('二级佣金不能为空'));
                }
                callback();
            };
            let thirdValidate = (rule, value, callback) => {
                if (this.ruleForm.level > 2 && !value && value !== 0) {
                    callback(new Error('三级佣金不能为空'));
                }
                callback();
            };
            return {
                loading: false,
                btnLoading: false,
                cat_show: false,
                show_share_level: false,
                ruleForm: {
                    level: 0,
                    is_rebate: 0,
                    price_type: 1,
                    first: 0,
                    second: 0,
                    third: 0,
                    share_condition: 1,
                    condition: 1,
                    auto_share_val: 0,
                    share_goods_status: 1,
                    share_goods_warehouse_id: '',
                    pay_type: ['auto'],
                    cash_max_day: -1,
                    min_money: 0,
                    cash_service_charge: 0,
                    agree: '',
                    content: '',
                    pic_url_apply: '',
                    pic_url_status: '',
                    pic_url_home_head: '',
                    share_goods_name: '',
                    become_condition: 3,
                    goods_list: [],
                    cat_list: [],
                    form_status: 0,
                    form: [],
                    is_show_share_level: 1,
                },
                rules: {
                    level: [
                        {message: '请选择分销层级', trigger: 'blur', required: true}
                    ],
                    is_rebate: [
                        {message: '请选择分销内购', trigger: 'blur', required: true}
                    ],
                    price_type: [
                        {message: '请选择分销佣金类型', trigger: 'blur', required: true}
                    ],
                    first: [
                        {validator: firstValidate, trigger: 'blur'},
                        {type: 'number', message: '一级佣金必须为数字', trigger: 'blur'},
                    ],
                    second: [
                        {validator: secondValidate, trigger: 'blur'},
                        {type: 'number', message: '二级佣金必须为数字', trigger: 'blur'},
                    ],
                    third: [
                        {validator: thirdValidate, trigger: 'blur'},
                        {type: 'number', message: '三级佣金必须为数字', trigger: 'blur'},
                    ],
                    share_condition: [
                        {message: '请选择成为分销商的条件', trigger: 'blur', required: true}
                    ],
                    condition: [
                        {message: '请选择成为下线的条件', trigger: 'blur', required: true}
                    ],
                    auto_share_val: [
                        {type: 'number', message: '消费自动成为分销商金额必须为数字', trigger: 'blur'},
                    ],
                    pay_type: [
                        {message: '请选择提现方式', required: true}
                    ],
                    cash_max_day: [
                        {message: '必须填写每日提现上限', required: true},
                        {type: 'number', message: '每日提现上限必须是数字', trigger: 'blur'}
                    ],
                    min_money: [
                        {message: '必须填写最少提现金额', required: true},
                        {type: 'number', message: '最少提现金额必须是数字', trigger: 'blur'}
                    ],
                    cash_service_charge: [
                        {message: '必须填写提现手续费', required: true},
                        {type: 'number', message: '提现手续费必须是数字', trigger: 'blur'}
                    ],
                }
            }
        },
        mounted: function () {
            this.loadData();
        },
        methods: {
            updateForm(form) {
                this.ruleForm.form = form;
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/share/basic',
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = Object.assign(this.ruleForm, e.data.data.list);
                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            store(formName) {
                if(this.ruleForm.form_status == 1 && this.ruleForm.form.length == 0) {
                    this.$message.error('请填写自定义表单');
                    return false;
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        console.log(this.ruleForm.form)
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/share/basic',
                            },
                            method: 'post',
                            data: this.ruleForm
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        })
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            picUrlApply(list) {
                this.ruleForm.pic_url_apply = list[0].url;
            },
            deleteUrlApply() {
                this.ruleForm.pic_url_apply = '';
            },
            picUrlStatus(list) {
                this.ruleForm.pic_url_status = list[0].url;
            },
            deleteUrlStatus() {
                this.ruleForm.pic_url_status = '';
            },
            picUrlHomeHead(list) {
                this.ruleForm.pic_url_home_head = list[0].url;
                console.log(this.ruleForm.pic_url_home_head);
            },
            deleteHomeHead() {
                this.ruleForm.pic_url_home_head = '';
            },
            goodsSelect(param) {
                for (let j in param) {
                    let item = param[j];
                    if (this.ruleForm.share_goods_warehouse_id.length >= 20) {
                        this.$message.error('指定商品不能大于20个');
                        return ;
                    }
                    let flag = true;
                    for (let i in this.ruleForm.share_goods_warehouse_id)  {
                        if (this.ruleForm.share_goods_warehouse_id[i] == item.goods_warehouse_id) {
                            flag = false;
                            break;
                        }
                    }
                    if (flag) {
                        this.ruleForm.share_goods_warehouse_id.push(item.goods_warehouse_id);
                        this.ruleForm.goods_list.push({
                            id: item.goods_warehouse_id,
                            name: item.name,
                            cover_pic: item.goodsWarehouse.cover_pic,
                        });
                    }
                }
            },
            deleteGoods(index) {
                this.ruleForm.goods_list.splice(index, 1);
                this.ruleForm.share_goods_warehouse_id.splice(index, 1);
            },
            destroyCat(value, index) {
                this.ruleForm.cat_list.splice(index, 1);
            },
        }
    });
</script>
