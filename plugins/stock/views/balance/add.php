<?php defined('YII_ENV') or exit('Access Denied'); ?>
<?php Yii::$app->loadViewComponent('app-rich-text') ?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .show {
        margin-left: 30px;
        color: #409EFF;
        height: 50px;
        line-height: 50px;
        cursor: pointer;
    }

    .select {
        position: relative;
        margin-left: 10px;
    }

    .select:first-of-type {
        margin-left: 0;
    }

    .select .select-info {
        padding-left: 30px;
    }

    .select .date-icon {
        position: absolute;
        height: 100%;
        width: 25px;
        left: 5px;
        top: 0;
        color: #C0C4CC;
    }

    .select .date-icon i {
        line-height: 36px;
        height: 100%;
        width: 28px;
        text-align: center;
    }
    .el-select .el-input .el-select__caret {
        display: none;
    }

    .show-info {
        border-radius: 6px;
        border: 1px solid #e2e2e2;
        padding: 10px 25px;
        width: 375px;
        margin-left: 20px;
        margin-top: 10px;
    }

    .show-info div {
        height: 32px;
        line-height: 32px;
    }

    .el-dialog__body {
        padding: 10px 30px 10px 20px;
        font-size: 15px;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 25px;
    }
    .date .el-form-item__label:before {
        content: '*';
        color: #F56C6C;
        margin-right: 4px;
    }
    .el-form-item {
        margin-bottom: 6px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/stock/mall/balance/index'})">分红结算</span></el-breadcrumb-item>
                <el-breadcrumb-item>添加结算</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <div class="show" @click="dialogVisible = true">点击查看《股东分红细则计算》</div>
            <el-form :model="form" label-width="100px" :rules="FormRules" ref="form">
                <el-form-item label="周期设置" prop="title">
                    <el-form-item label="结算周期" prop="type">
                        <el-radio-group @change="toggle" v-model="type">
                            <el-radio label="1">按周</el-radio>
                            <el-radio label="2">按月</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="结算时间" prop="date" class="date">
                        <div v-if="detail.is_bonus == 1" flex="dir:left cross:center">
                            <div class="select">
                                <div class="select-info">{{detail.year}}</div>
                                <div class="date-icon">
                                    <i class="el-input__icon el-icon-date"></i>
                                </div>
                            </div>
                            <div class="select">
                                <div class="select-info">{{detail.mouth}}</div>
                                <div class="date-icon">
                                    <i class="el-input__icon el-icon-date"></i>
                                </div>
                            </div>
                            <div v-if="type == 1" class="select">
                                <div class="select-info">{{detail.week}}</div>
                                <div class="date-icon">
                                    <i class="el-input__icon el-icon-date"></i>
                                </div>
                            </div>
                        </div>
                        <div v-else style="color: #ff4544;">当前无可结算周期</div>
                    </el-form-item>
                </el-form-item>
                <el-form-item label="分红数据" prop="title">
                    <div class="show-info">
                        <div>时间段: <span v-if="detail.is_bonus == 1">{{detail.first_day}}~{{detail.last_day}}</span></div>
                        <div>订单数量: <span v-if="detail.is_bonus == 1">{{detail.order_num}}</span></div>
                        <div>订单总金额: <span v-if="detail.is_bonus == 1">￥{{detail.total_pay_price}}</span></div>
                        <div>分红总金额: <span v-if="detail.is_bonus == 1">￥{{detail.bonus_price}}(分红比例{{detail.stock_rate}}%)</span></div>
                        <div>股东数量: <span v-if="detail.is_bonus == 1">{{detail.stock_num}}</span></div>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <el-button type="primary" v-if="detail.is_bonus == 1" @click="loadData(1)" :loading="submitLoading" class="button-item">结算</el-button>
    </el-card>
    <el-dialog title="股东分红计算细则" :visible.sync="dialogVisible" width="35%">
        <div>股东是基于分销商身份新建立起来的一种全新身份，股东可参与自营商品全部订单的分红。商家在后台设置分红订单比例，决定了可被所有股东瓜分的最高分红金额。根据股东的业务能力，可将股东划分为多个等级，各等级股东的分红计算细则如下：</div>
        <div style="margin: 15px 0">
            <div>案例：商城某订单的实付金额为1000元，订单分红比例为10%，则分红总金额为100元；</div>
            <div>等级1股东的股东分红比例为10%，等级1共有2个股东；</div>
            <div>等级2股东的股东分红比例为20%，等级2共有5个股东；</div>
            <div>等级3股东的股东分红比例为30%，等级3共有10个股东；</div>
        </div>
        <div>
            <div>计算方式：</div>
            <div>等级1每个股东可得：10%*100元/（10%*2+20%*5+30%*10）=2.38元</div>
            <div>等级2每个股东可得：20%*100元/（10%*2+20%*5+30%*10）=4.76元</div>
            <div>等级3每个股东可得：30%*100元/（10%*2+20%*5+30%*10）=7.14元</div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" type="primary" size="small" @click="dialogVisible = false">我知道了</el-button>
        </span>
    </el-dialog>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let validatePass = (rule, value, callback) => {
                if (!this.year) {
                    callback('请选择年份');
                } else if (!this.month) {
                    callback('请选择月份');
                } else if (!this.week && this.type == 1) {
                    callback('请选择第几周');
                } else {
                    callback();
                }
            };
            return {
                dialogVisible: false,
                loading: false,
                form: {
                    title: ''
                },
                detail: {},
                type: '1',
                year: '',
                month: '',
                week: '',
                time: '',
                choose: '',
                order_num: '',
                order_total_price: '',
                share_price: '',
                stock_num: '',
                submitLoading: false,
                FormRules: {
                    type: [
                        { required: true, message: '请设置结算周期', trigger: 'blur' }
                    ],
                    // date: [
                    //     { validator: validatePass, trigger: 'change' }
                    // ],
                }
            };
        },
        methods: {
            chooseYear(e) {
                this.choose = e;
            },
            toggle(e) {
                this.loadData();
            },
            // 获取数据
            loadData(is_save) {
                if(is_save) {
                    this.submitLoading = true;
                }else {
                    this.loading = true;
                }
                request({
                    params: {
                        r: 'plugin/stock/mall/bonus/bonus-data',
                        type: this.type,
                        is_save: is_save ? is_save : 0
                    },
                    method: 'get',
                }).then(e => {
                    if(is_save) {
                        this.get(e.data.queue_id)
                    }else {
                        this.loading = false;
                        if (e.data.code == 0) {
                            this.detail = e.data.data;
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            get(queue_id) {
                request({
                    params: {
                        r: 'plugin/stock/mall/bonus/bonus-status',
                        queue_id: queue_id
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        if(e.data.data.retry == 1) {
                            this.get(queue_id);
                        }else {
                            this.submitLoading = false;
                            this.$message.success(e.data.msg);
                            this.loadData();
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.submitLoading = false;
                });
            },

        },

        created() {
            this.loadData();
        }
    })
</script>
