<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-send .title-box {
        margin: 15px 0;
    }

    .app-send .title-box .text {
        background-color: #FEFAEF;
        color: #E6A23C;
        padding: 6px;
    }

    .app-send .get-print {
        width: 100%;
        height: 100%;
    }

    .app-send .el-table__header-wrapper th {
        background-color: #f5f7fa;
    }

    .app-send .el-dialog__body {
        padding: 5px 20px 10px;
    }
</style>

<template id="app-send">
    <div class="app-send">
        <!-- 发货 -->
        <el-dialog title="发货" :visible.sync="dialogVisible" width="35%" @close="closeDialog">
            <div class="title-box">
                <span class="text">选择发货商品</span>
                <span>(默认全选)</span></div>
            <el-table
                    ref="multipleTable"
                    :data="orderDetail"
                    tooltip-effect="dark"
                    style="width: 100%"
                    max-height="250"
                    @selection-change="handleSelectionChange">
                <el-table-column
                        type="selection"
                        :selectable="selectInit"
                        width="55">
                </el-table-column>
                <el-table-column
                        label="图片"
                        width="60">
                    <template slot-scope="scope">
                        <app-image width="30" height="30" :src="scope.row.goods_info.goods_attr.cover_pic"></app-image>
                    </template>
                </el-table-column>
                <el-table-column
                        label="名称"
                        show-overflow-tooltip>
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.expressRelation" type="success" size="mini">已发货</el-tag>
                        <span>{{scope.row.goods_info.goods_attr.name}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="goods_info.goods_attr.number"
                        label="数量"
                        width="80"
                        show-overflow-tooltip>
                </el-table-column>
                <el-table-column
                        label="规格"
                        width="120"
                        show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-for="attrItem in scope.row.goods_info.attr_list">
                            {{attrItem.attr_group_name}}:{{attrItem.attr_name}}
                        </span>
                    </template>
                </el-table-column>
            </el-table>
            <div class="title-box">
                <span class="text">物流信息</span>
            </div>
            <el-form label-width="130px"
                     @submit.native.prevent="prev"
                     class="sendForm"
                     :model="express"
                     :rules="rules"
                     ref="sendForm">
                <template v-if="order.send_type == 0 || order.send_type == 1">
                    <el-form-item label="物流选择">
                        <el-radio @change="resetForm('sendForm')" v-model="express.is_express" label="1">快递</el-radio>
                        <el-radio @change="resetForm('sendForm')" v-model="express.is_express" label="2">其它方式</el-radio>
                    </el-form-item>
                    <el-form-item label="快递公司" prop="express" v-if="express.is_express == 1">
                        <el-autocomplete
                                size="small"
                                v-model="express.express"
                                @select="getCustomer"
                                :fetch-suggestions="querySearch"
                                placeholder="请选择快递公司"
                        ></el-autocomplete>
                    </el-form-item>
                    <el-form-item label="收件人邮编" v-if="express.is_express == 1">
                        <el-input type="number" placeholder="请输入收件人邮编" size="small" v-model="express.code"
                                  autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="商家编码" prop="customer_name"
                                  v-if="express.is_express == 1 && (express.express === '京东物流' || express.express === '京东快运')">

                        <el-input placeholder="请输入商家编码" size="small" v-model="express.customer_name"
                                  autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="快递单号" prop="express_no" class="express-no" v-if="express.is_express == 1">
                        <el-input placeholder="请输入快递单号" size="small" v-model.trim="express.express_no"
                                  autocomplete="off">
                            <template v-if="isShowPrint" slot="append">
                                <div flex="main:center" style="width: 100px">
                                    <el-button :loading="submitLoading" size="small" type="text" class="get-print"
                                               @click="getPrint(express)">获取面单
                                    </el-button>
                                </div>
                            </template>
                        </el-input>
                    </el-form-item>
                    <!--售后发货应该用插槽的方式-->
                    <el-form-item v-if="express.is_express == 1 && isRefund" prop="merchant_remark" label="商家留言">
                        <el-input type="textarea" size="small" v-model="express.merchant_remark"
                                  autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item v-if="express.is_express == 1 && !isRefund" label="商家留言">
                        <el-input type="textarea" size="small" v-model="express.merchant_remark"
                                  autocomplete="off">
                        </el-input>
                    </el-form-item>
                    <el-form-item v-if="express.is_express == 2" prop="express_content" label="物流内容">
                        <el-input type="textarea" size="small" v-model="express.express_content"
                                  autocomplete="off"></el-input>
                    </el-form-item>
                </template>
                <template v-else>
                    <el-form-item label="配送方式">
                        <el-radio @change="resetForm('sendForm')" v-model="city.is_express" :label="2">商家配送</el-radio>
                        <el-radio @change="resetForm('sendForm')" v-model="city.is_express" :label="1">第三方配送</el-radio>
                    </el-form-item>
                    <!-- 商家配送 -->
                    <el-form-item v-if="city.is_express== 2" label="配送员" prop="city_name">
                        <template v-if="city.list.length > 0">
                            <el-autocomplete @keyup.enter.native="prevD"
                                         size="small"
                                         v-model="city.man"
                                         :fetch-suggestions="searchCity"
                                         placeholder="请选择配送员">
                            </el-autocomplete>
                        </template>
                        <template v-else>
                            <div>未设置配送员，
                                <el-button type="text" @click="$navigate({r:'mall/delivery/index'}, true)">请前往同城配送设置
                                </el-button>
                            </div>
                        </template>
                    </el-form-item>
                    <!-- 第三方配送 -->
                    <template v-else>
                        <el-form-item label="选择配送" prop="delivery_name">
                            <template v-if="city.city_service_list.length > 0">
                                <el-autocomplete @keyup.enter.native="prevD"
                                             size="small"
                                             v-model="city.city_service"
                                             :fetch-suggestions="searchCityService"
                                             @select="handleSelectCityService"
                                             placeholder="请选择配送名称">
                                </el-autocomplete>
                            </template>
                            <template v-else>
                                <div>未设置配送商家，
                                    <el-button type="text" @click="$navigate({r:'mall/city-service/edit'}, true)">请前往配送商家设置
                                    </el-button>
                                </div>
                            </template>
                        </el-form-item>
                        <template v-if="city.is_preview">
                            <div style="margin: 5px 0 5px 60px;color: #62BC0F">{{city.name}} 可接单</div>
                            <div style="margin: 5px 0 5px 60px;">预计订单费用 ￥{{city.fee}}</div>
                        </template>
                    </template>
                </template>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible=false">取 消</el-button>
                    <!-- 第三方配送 -->
                    <template v-if="city.is_express == 1">
                        <el-button v-if="city.is_preview == 0" size="small" type="primary" :loading="sendLoading"
                               @click="send_order(express,'sendForm')">
                            下单
                        </el-button>
                        <el-button v-else size="small" type="primary" :loading="sendLoading"
                               @click="send_order(express,'sendForm')">
                            确认发货
                        </el-button>
                    </template>
                    <template v-else>
                        <!-- 售后发货应该用插槽的方式-->
                        <el-button v-if="isRefund" size="small" type="primary" :loading="sendLoading"
                               @click="refundSend(express,'sendForm')">
                            确定
                        </el-button>
                        <el-button v-else size="small" type="primary" :loading="sendLoading"
                                   @click="send_order(express,'sendForm')">
                            确定
                        </el-button>
                    </template>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-send', {
        template: '#app-send',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            order: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            sendType: {
                type: String,
                default: '',
            },
            isRefund: {
                type: Boolean,
                default: false,
            },
            isShowPrint: {
                type: Boolean,
                default: true,
            },
            expressId: {
                type: Number,
                default: 0,
            },
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openExpress()
                    this.getExpressData();
                    this.getExpressId();
                } else {
                    this.dialogVisible = false;
                }
            },
        },
        data() {
            return {
                dialogVisible: false,
                express: {},
                send_type: null,
                mhc_id: 0,
                sendLoading: false,
                submitLoading: false,
                rules: {
                    express: [
                        {required: true, message: '快递公司不能为空', trigger: 'change'},
                    ],
                    express_no: [
                        {required: true, message: '快递单号不能为空', trigger: 'change'},
                        {pattern: /^[0-9a-zA-Z]+$/, message: '仅支持数字与英文字母'}
                    ],
                    merchant_remark: [
                        {required: true, message: '商家留言不能为空', trigger: 'change'},
                    ],
                    customer_name: [
                        {required: true, message: '商家编码不能为空', trigger: 'change'},
                    ],
                    express_content: [
                        {required: true, message: '物流内容不能为空', trigger: 'change'},
                    ],
                    // city_name: [
                    //     {required: true, message: '请选择配送员', trigger: 'change'},
                    // ],
                    // delivery_name: [
                    //     {required: true, message: '请选择配送名称', trigger: 'change'},
                    // ],
                },
                express_list: [],
                multipleSelection: [],
                orderDetail: [],
                expressSingle: {},
                city: {
                    list: [],
                    man: null,
                    is_express: 2,// 2.商家配送|1.第三方配送
                    city_service_list:[],
                    city_service: null,
                    is_preview: 0,
                    fee: 0,
                    name: '',
                    delivery_no: '',
                }
            }
        },
        methods: {
            // 打开发货框
            openExpress() {
                let self = this;
                self.dialogVisible = true;
                self.send_type = self.sendType;
                if (self.order.send_type == 0 || self.order.send_type == 1) {
                    self.getExpress();
                    self.expressSend();
                } else if (self.order.send_type == 2) {
                    self.city.is_preview = 0;
                    self.citySend();
                }

            },
            expressSend() {
                let self = this;
                if (self.send_type === 'change') {
                    self.order.detailExpress.forEach(function (item) {
                        if (item.id == self.expressId) {
                            self.express = {
                                is_express: item.send_type,
                                order_id: self.order.id,
                                express: item.express,
                                code: self.order.code,
                                express_no: item.express_no,
                                words: self.order.words,
                                customer_name: item.customer_name,
                                mch_id: self.order.mch_id,
                                merchant_remark: item.merchant_remark,
                                express_content: item.express_content,
                                express_id: self.expressId,
                            };
                        }
                    })
                } else {
                    self.express = {
                        is_express: '1',
                        order_id: self.order.id,
                        express_no: '',
                        mch_id: self.order.mch_id,
                        express_content: '',
                    };
                }
            },
            getExpressData() {
                let self = this;
                self.orderDetail = self.order.detail;
                // 默认全选
                self.orderDetail.forEach(row => {
                    if (!row.expressRelation) {
                        setTimeout(() => {
                            self.$refs.multipleTable.toggleRowSelection(row, true);
                        }, 1)
                    }
                });
            },
            getExpressId: function (newVal) {
                let self = this;
                if (self.expressId > 0) {
                    self.orderDetail = [];
                    self.order.detailExpress.forEach(function (item) {
                        if (item.id == self.expressId) {
                            item.expressRelation.forEach(function (item2) {
                                self.orderDetail.push(item2.orderDetail)
                            })
                        }
                    })

                    // 默认全选
                    self.orderDetail.forEach(row => {
                        if (row.expressRelation) {
                            setTimeout(() => {
                                self.$refs.multipleTable.toggleRowSelection(row);
                            }, 1)
                        }
                    });
                }
            },
            closeDialog() {
                this.$refs['sendForm'].clearValidate();
                this.$emit('close')
            },
            // 发货
            send_order(e, formName) {
                let self = this;
                let res = self.getOrderDetailId();
                if (res.length <= 0) {
                    this.$message.error('请选择发货商品');
                    // this.closeDialog()
                    return false;
                }
                e.order_detail_id = res;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.sendLoading = true;
                        if (self.order.send_type == 0 || self.order.send_type == 1) {
                            self.expressSendSubmit(e);
                        } else if (self.order.send_type == 2) {
                            self.citySendSubmit(e);
                        }
                    }
                });
            },
            expressSendSubmit(e){
                let self = this;
                if (self.express.is_express == 1) {
                    // 电子面单ID
                    e.express_single_id = self.expressSingle.id;
                    request({
                        params: {
                            r: 'mall/order/send',
                        },
                        data: e,
                        method: 'post',
                    }).then(e => {
                        self.sendLoading = false;
                        if (e.data.code === 0) {
                            self.dialogVisible = false;
                            self.$emit('submit');
                            if (self.send_type == "send") {
                                self.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                            } else if (self.send_type == "change") {
                                self.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                            }
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.sendLoading = false;
                    });
                } else {
                    request({
                        params: {
                            r: 'mall/order/send',
                        },
                        data: e,
                        method: 'post',
                    }).then(e => {
                        self.sendLoading = false;
                        if (e.data.code === 0) {
                            self.dialogVisible = false;
                            self.$emit('submit');
                            if (self.send_type == "send") {
                                self.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                            } else if (self.send_type == "change") {
                                self.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                            }
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.sendLoading = false;
                    });
                }
            },
            getOrderDetailId() {
                // 选中的订单商品
                let orderDetailId = [];
                this.multipleSelection.forEach(function (item) {
                    orderDetailId.push(item.id)
                });
                return orderDetailId;
            },
            // 搜索建议
            querySearch(queryString, cb) {
                var express_list = this.express_list;
                var results = queryString ? express_list.filter(this.createFilter(queryString)) : express_list;
                cb(results);
            },
            createFilter(queryString) {
                return (express_list) => {
                    return (express_list.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0);
                };
            },
            getCustomer() {
                let express = this.express.express;
                if (express !== '京东物流' && express !== '京东快运') {
                    this.express.customer_name = '';
                    return;
                }
                request({
                    params: {
                        _mall_id: <?php echo \Yii::$app->mall->id; ?>,
                        r: 'api/express/get-customer',
                        keyword: express
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data.customer_account) {
                            let info = JSON.parse(JSON.stringify(this.express));
                            info.customer_name = e.data.data.customer_account;
                            this.express = info;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getExpress() {
                request({
                    params: {
                        r: 'mall/express/express-list'
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.express_list = e.data.data.list;
                        for (let i = 0; i < this.express_list.length; i++) {
                            this.express_list[i].value = this.express_list[i].name
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 获取面单
            getPrint(e) {
                this.submitLoading = true;
                request({
                    params: {
                        r: 'mall/order/print',
                        order_id: e.order_id,
                        express: e.express,
                        zip_code: e.code,
                        customer_name: e.customer_name,
                        order_detail_ids: this.getOrderDetailId(),
                    },
                    method: 'get',
                }).then(e => {
                    e.visible = false;
                    this.submitLoading = false;
                    if (e.data.code == 0) {
                        this.$message({
                            message: '获取成功',
                            type: 'success'
                        });
                        this.express.express_no = e.data.data.Order.LogisticCode;
                        this.expressSingle = e.data.data.express_single;
                    } else {
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                    }
                }).catch(e => {
                });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            selectInit(row, index) {
                if (row.expressRelation) {
                    return false;
                } else {
                    return true;
                }
            },
            resetForm(formName) {
                this.$refs[formName].clearValidate();
                this.city.man = null;
                this.city.city_service = null;
            },
            // 售后发货方法 start
            refundSend(row, formName) {
                let res = this.getOrderDetailId();
                if (res.length <= 0) {
                    this.$message.error('请选择发货商品');
                    return false;
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        let para = row;
                        para.order_refund_id = this.order.id;
                        para.type = 2;
                        para.is_agree = '1';
                        para.refund = '2';
                        this.para = para;
                        this.refundOver();
                    }
                });
            },
            refundOver() {
                if (this.para.type == 2) {
                    this.sendLoading = true;
                    let para = this.para;
                    request({
                        params: {
                            r: 'mall/order/refund-handle',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        this.sendLoading = false;
                        if (e.data.code === 0) {
                            this.refundConfirmVisible = false;
                            this.$emit('submit');
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        }
                    }).catch(e => {
                    });
                }
            },
            // 售后发货方法 end
            // 同城配送方法 start
            citySend() {
                let self = this;
                self.city.order_id = self.order.id;
                if (self.sendType === 'change') {
                    let sign = true;
                    self.order.detailExpress.forEach(function (item) {
                        if (item.id == self.expressId) {
                            sign = false;
                            self.city.is_express = parseInt(item.send_type);
                            let deliveryman = JSON.parse(item.city_info);
                            if (deliveryman.city_info) {
                                self.city.man = '(' + deliveryman.city_info.id + ')' + item.city_name;
                                self.city.city_service = '(' + deliveryman.city_info.id + ')' + deliveryman.city_info.name;
                            }
                        }
                    });
                    // 兼容旧数据
                    if (sign) {
                        let deliveryman = JSON.parse(self.order.city_info);
                        self.city.man = '(' + deliveryman.id + ')' + self.order.city_name;
                    }
                }
                self.getDeliveryman();
            },
            getDeliveryman() {
                let self = this;
                self.dialogLoading = true;
                request({
                    params: {
                        r: 'mall/delivery/man-list'
                    },
                }).then(e => {
                    self.dialogLoading = false;
                    if (e.data.code == 0) {
                        // 商家配送员列表
                        self.city.list = e.data.data.list;
                        for (let i = 0; i < self.city.list.length; i++) {
                            self.city.list[i].value = '(' + self.city.list[i].id + ')' + self.city.list[i].name
                        }
                        // 第三方配送列表
                        let new_city_list = [];
                        self.city.city_service_list = e.data.data.city_service_list;
                        self.city.city_service_list.forEach(function(item, index) {
                            self.city.city_service_list[index].value = '(' + item.id + ')' + item.name;
                            if (item.service_type == '第三方') {
                                new_city_list.push(item)
                            } else if (self.order.platform == 'wxapp' && item.service_type == '微信') {
                                new_city_list.push(item)
                            }
                        })
                        self.city.city_service_list = new_city_list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            searchCity(queryString, cb) {
                let deliveryman = this.city.list;
                let results = queryString ? deliveryman.filter((deliveryman) => {
                    return (deliveryman.value.toLowerCase().indexOf(queryString.toLowerCase()) != -1);
                }) : deliveryman;
                cb(results);
            },
            searchCityService(queryString, cb) {
                let deliveryman = this.city.city_service_list;
                let results = queryString ? deliveryman.filter((deliveryman) => {
                    return (deliveryman.value.toLowerCase().indexOf(queryString.toLowerCase()) != -1);
                }) : deliveryman;
                cb(results);
            },
            handleSelectCityService(e){
                this.city.is_preview = 0;
            },
            citySendSubmit(e) {
                let self = this;
                request({
                    params: {
                        r: 'mall/order/send',
                    },
                    data: {
                        man: this.city.man,
                        city_service: this.city.city_service ? this.city.city_service : '',
                        is_express: this.city.is_express,
                        order_id: this.city.order_id,
                        order_detail_id: e.order_detail_id,
                        express_id: this.expressId ? this.expressId : 0,
                        is_preview: this.city.is_preview ? 0 : 1,
                        delivery_no: this.city.delivery_no,
                    },
                    method: 'post',
                }).then(e => {
                    this.sendLoading = false;
                    if (e.data.code === 0) {
                        let resultData = e.data.data;
                        if (resultData && resultData.preview_success == 1) {
                            self.city.fee = resultData.fee;
                            self.city.is_preview = 1;
                            self.city.name = resultData.name;
                            self.city.delivery_no = resultData.delivery_no;
                        } else {
                            self.dialogVisible = false;
                            self.$emit('submit');
                        }
                        self.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                    } else {
                        self.$alert(e.data.msg, {
                            confirmButtonText: '确定'
                        });
                    }
                }).catch(e => {
                    self.sendLoading = false;
                });
            },
            prevD() {

            },
            // 同城配送方法 end
        },
        created() {

        },
    })
</script>