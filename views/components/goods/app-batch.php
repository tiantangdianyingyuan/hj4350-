<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
try {
    if (Yii::$app->plugin->currentPlugin) {
        $sign = Yii::$app->plugin->currentPlugin->getName();
    } else {
        if (Yii::$app->user->identity->mch_id > 0) {
            $sign = 'mch';
        } else {
            $sign = '';
        }
    }
} catch (Exception $exception) {
    $sign = '';
}
?>
<style>
    .app-batch {
        height: 50px;
        background-color: #f9f9f9;
        padding: 0 22px;
    }

    .app-batch .batch-box {
        margin-left: 10px;
    }

    .app-batch .batch-remark {
        margin-top: 5px;
        color: #999999;
        font-size: 14px;
    }

    .app-batch .select-count {
        font-size: 14px;
        margin-left: 10px;
    }

    .app-batch .batch-title {
        font-size: 18px;
    }

    .app-batch .batch-box-left {
        width: 120px;
        border-right: 1px solid #e2e2e2;
        padding: 0 20px;
    }

    .app-batch .batch-box-left div {
        padding: 5px 0;
        margin: 5px 0;
        cursor: pointer;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }

    .app-batch .batch-div-active {
        background-color: #e2e2e2;
    }

    .app-batch .el-dialog__body {
        padding: 15px 20px;
    }

    .app-batch .batch-box-right {
        padding: 5px 20px;
    }

    .app-batch .express-dialog .el-dialog {
        min-width: 250px;
    }

    .app-batch .add-express-rule {
        margin-left: 20px;
        cursor: pointer;
        color: #419EFB;
    }

    .app-batch .confine-box .label {
        margin-right: 10px;
    }

    .app-batch .goods-price .el-input-group__prepend {
        width: 80px;
    }
</style>


<template id="app-batch">
    <div class="app-batch" flex="dir:left cross:center">
<!--        <el-checkbox @change="checkedChange" v-model="isAllChecked" style="margin-right: 10px;">修改全部</el-checkbox>-->
        <template v-if="isShowUpDown">
            <el-button @click="upDown(1)" size="mini">上架</el-button>
            <el-button @click="upDown(0)" size="mini">下架</el-button>
        </template>
        <el-button v-if="isShowDelete" @click="allDelete" size="mini">删除</el-button>
        <el-button v-if="isShowBatchButton" @click="batchSetting" size="mini">批量设置</el-button>

        <el-dialog
                :visible.sync="dialogVisible"
                width="50%">
            <div slot="title">
                <div flex="dir:left">
                    <div class="batch-title">批量修改</div>
                    <div flex="cross:center" class="select-count">{{dialogTitle}}</div>
                </div>
                <div class="batch-remark">注：每次只能修改一项，修改后点击确定即可生效。如需修改多项，需多次操作。</div>
            </div>
            <div flex="dir:left box:first">
                <div class="batch-box-left" flex="dir:top">
                    <div v-for="(item, index) in newBatchList"
                         :key='item.key'
                         :class="{'batch-div-active': currentBatch === item.key ? true : false}"
                         @click="currentBatch = item.key"
                         flex="main:center">
                        {{item.name}}
                    </div>
                </div>
                <div class="batch-box-right">
                    <el-form>
                        <div v-if="currentBatch === 'goods-price'">
                            <el-form-item label-width="80px" label="调整类型">
                                <div>
                                    <el-radio v-model="goodsPrice.type" :label="1">固定金额</el-radio>
                                    <el-radio v-model="goodsPrice.type" :label="2">百分比</el-radio>
                                </div>
                            </el-form-item>
                            <el-form-item class="goods-price" label-width="80px" label="售价">
                                <el-input size="small" oninput="this.value = this.value.replace(/[^0-9.]/, '');"
                                          type="number" placeholder="请输入价格" v-model="goodsPrice.price">
                                    <el-select v-model="goodsPrice.select_type" slot="prepend" placeholder="请选择">
                                        <el-option label="提高" :value="1"></el-option>
                                        <el-option label="降低" :value="2"></el-option>
                                    </el-select>
                                    <el-button slot="append">{{goodsPrice.type == 1 ? '元' : '%'}}</el-button>
                                </el-input>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'express-price'">
                            <el-form-item label="运费设置">
                                <div style="display: inline-block">
                                    <el-tag v-if="expressRule" :disable-transitions="true" closable
                                            @close="expressRule = null">{{expressRule.name}}
                                    </el-tag>
                                    <el-button size="small" @click="selectExpressRule">选择运费</el-button>
                                    <span @click="$navigate({r:'mall/postage-rule/index'}, true)"
                                          class="add-express-rule">新建</span>
                                </div>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'shipping'">
                            <el-form-item label="包邮规则">
                                <div style="display: inline-block">
                                    <el-tag v-if="shipping" :disable-transitions="true" closable
                                            @close="shipping = null">{{shipping.name}}
                                    </el-tag>
                                    <el-button size="small" @click="selectShipping">选择包邮规则</el-button>
                                    <span @click="$navigate({r:'mall/postage-rule/index'}, true)"
                                          class="add-express-rule">新建</span>
                                </div>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'confine'">
                            <el-form-item label="每人限购">
                                <div flex="dir:top" class="confine-box">
                                    <div flex="dir:left">
                                        <span class="label">商品</span>
                                        <div flex="dir:left">
                                            <div>
                                                <el-input type="number" min="0" size="small" placeholder="请输入限购数量"
                                                          oninput="this.value = this.value.replace(/[^0-9]/, '');"
                                                          :disabled="confine.isGoodsConfine"
                                                          v-model="confine.goodsCount">
                                                    <template slot="append">件</template>
                                                </el-input>
                                            </div>
                                            <el-checkbox style="margin-left: 10px;" v-model="confine.isGoodsConfine">
                                                不限制
                                            </el-checkbox>
                                        </div>
                                    </div>
                                    <div flex="dir:left">
                                        <span class="label">订单</span>
                                        <div flex="dir:left">
                                            <div>
                                                <el-input type="number" size="small" placeholder="请输入限购单数"
                                                          oninput="this.value = this.value.replace(/[^0-9]/, '');"
                                                          min="0"
                                                          :disabled="confine.isOrderConfine"
                                                          v-model="confine.orderCount">
                                                    <template slot="append">单</template>
                                                </el-input>
                                            </div>
                                            <el-checkbox style="margin-left: 10px;" v-model="confine.isOrderConfine">
                                                不限制
                                            </el-checkbox>
                                        </div>
                                    </div>
                                </div>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'integral'">
                            <el-form-item label="积分赠送">
                                <div flex="dir:left">
                                    <div>
                                        <el-tooltip effect="dark"
                                                    placement="top">
                                            <div slot="content">用户购物赠送的积分, 如果不填写或填写0，则默认为不赠送积分，
                                                如果为百分比则为按成交价格的比例计算积分"<br/>
                                                如: 购买2件，设置10 积分, 不管成交价格是多少， 则购买后获得20积分</br>
                                                如: 购买2件，设置10%积分, 成交价格2 * 200= 400， 则购买后获得 40 积分（400*10%）
                                            </div>
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </div>
                                    <div style="margin-left: 5px;width: 100%;">
                                        <el-input type="number"
                                                  size="small"
                                                  oninput="this.value = this.value.replace(/[^0-9]/, '');"
                                                  min="0"
                                                  placeholder="请输入赠送积分数量"
                                                  v-model="integral.give_integral">
                                            <template slot="append">
                                                {{integral.give_integral_type === 1 ? '分' : '%'}}
                                                <el-radio v-model="integral.give_integral_type" :label="1">固定值
                                                </el-radio>
                                                <el-radio v-model="integral.give_integral_type" :label="2">百分比
                                                </el-radio>
                                            </template>
                                        </el-input>
                                    </div>
                                </div>
                            </el-form-item>
                            <el-form-item label="积分抵扣">
                                <div flex="dir:left">
                                    <div>
                                        <el-tooltip effect="dark"
                                                    placement="top">
                                            <div slot="content">如果设置0，则不支持积分抵扣 如果带%则为按成交价格的比例计算抵扣多少元</div>
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </div>
                                    <div style="margin-left: 5px;width: 100%;">
                                        <el-input type="number"
                                                  size="small"
                                                  placeholder="请输入赠送积分数量"
                                                  oninput="this.value = this.value.replace(/[^0-9]/, '');"
                                                  min="0"
                                                  v-model="integral.forehead_integral">
                                            <template slot="prepend">最多抵扣</template>
                                            <template slot="append">
                                                {{integral.forehead_integral_type === 1 ? '元': '%'}}
                                                <el-radio v-model="integral.forehead_integral_type" :label="1">固定值
                                                </el-radio>
                                                <el-radio v-model="integral.forehead_integral_type" :label="2">百分比
                                                </el-radio>
                                            </template>
                                        </el-input>
                                        <el-checkbox
                                                :true-label="1"
                                                :false-label="0"
                                                v-model="integral.accumulative">
                                            允许多件累计抵扣
                                        </el-checkbox>
                                    </div>
                                </div>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'is-vip'">
                            <el-form-item label="是否享受超级会员卡权益">
                                <el-switch v-model="isSvip"></el-switch>
                            </el-form-item>
                        </div>
                        <div v-if="currentBatch === 'is-goods-member'">
                            <el-form-item label="是否享受会员功能">
                                <el-switch v-model="isGoodsMember"></el-switch>
                            </el-form-item>
                        </div>
                        <slot name="batch" :item="currentBatch"></slot>
                    </el-form>
                </div>
            </div>
            <div slot="footer">
                <el-button size="small" @click="dialogVisible = false">取 消</el-button>
                <el-button size="small" :loading="btnLoading" type="primary" @click="dialogSubmit">确 定
                </el-button>
            </div>
        </el-dialog>

        <el-dialog
                class="express-dialog"
                title="选择运费"
                :visible.sync="freight.dialog"
                width="25%">
            <el-card shadow="never" flex="dir:left" style="flex-wrap: wrap"
                     v-loading="freight.loading">
                <el-radio-group flex="dir:top" v-model="freight.checked">
                    <el-radio style="padding: 10px;" v-for="item in freight.list"
                              :label="item" :key="item.id">{{item.name}}
                    </el-radio>
                </el-radio-group>
            </el-card>
            <div slot="footer">
                <el-button size="small" @click="freight.dialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="expressSubmit">确 定</el-button>
            </div>
        </el-dialog>
        <el-dialog
                class="shipping-dialog"
                title="选择包邮规则"
                :visible.sync="shippingData.dialog"
                width="25%">
            <el-card shadow="never" flex="dir:left" style="flex-wrap: wrap"
                     v-loading="shippingData.loading">
                <el-radio-group flex="dir:top" v-model="shippingData.checked">
                    <el-radio style="padding: 10px;" v-for="item in shippingData.list"
                              :label="item" :key="item.id">{{item.name}} {{item.id !== 0 ? '(' +  item.text  +' )' : ''}}
                    </el-radio>
                </el-radio-group>
            </el-card>
            <div slot="footer">
                <el-button size="small" @click="shippingData.dialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="shippingSubmit">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-batch', {
        template: '#app-batch',
        props: {
            // 列表选中的数据
            chooseList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            batchUpdateStatusUrl: {
                type: String,
                default: 'mall/goods/batch-update-status',
            },
            batchDestroyUrl: {
                type: String,
                default: 'mall/goods/batch-destroy',
            },
            batchConfineUrl: {
                type: String,
                default: 'mall/goods/batch-update-confine-count',
            },
            batchFreightUrl: {
                type: String,
                default: 'mall/goods/batch-update-freight',
            },
            batchShippingUrl: {
                type: String,
                default: '/mall/goods/batch-update-free-delivery',
            },
            // 上架弹框提示文字
            statusChangeText: {
                type: String,
                default: '',
            },
            /**
             * 批量设置参数
             * 参数例子：baseBatchList
             */
            batchList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            // 是否开启批量超级会员卡设置
            isShowSvip: {
                type: Boolean,
                default: true
            },
            // 是否开启批量运费设置
            isShowExpress: {
                type: Boolean,
                default: true
            },
            // 是否开启批量运费设置
            isShowIntegral: {
                type: Boolean,
                default: true
            },
            // 是否开启批量设置按钮
            isShowBatchButton: {
                type: Boolean,
                default: true
            },
            // 是否显示批量上下架按钮
            isShowUpDown: {
                type: Boolean,
                default: true
            },
            // 是否显示批量删除按钮
            isShowDelete: {
                type: Boolean,
                default: true
            },
            isShowShipping: {
                type: Boolean,
                default: true
            },
            // 批量操作额外参数
            batchExtraParams: {
                type: Array,
                default: function () {
                    return []
                }
            }
        },
        data() {
            return {
                isAllChecked: false,
                freight: {
                    dialog: false,
                    list: [],
                    checked: {},
                    loading: false,
                },
                shippingData: {
                    dialog: false,
                    list: [],
                    checked: {},
                    loading: false
                },
                btnLoading: false,
                dialogVisible: false,
                currentBatch: '',
                isSvip: false,
                isSvipPermission: false,
                isGoodsMember: false,
                goodsPrice: {
                    type: 1,
                    select_type: 1,
                    price: 0,
                },
                expressRule: {
                    id: 0,
                    name: '默认运费',
                },
                shipping: {
                    id: 0,
                    name: '默认规则',
                },
                confine: {
                    goodsCount: 0,
                    orderCount: 0,
                    isGoodsConfine: true,
                    isOrderConfine: true,
                },
                integral: {
                    give_integral_type: 1,
                    give_integral: 0,
                    forehead_integral: 0,
                    forehead_integral_type: 1,
                    accumulative: 0
                },
                dialogTitle: '',
                newBatchList: [],
                baseBatchList: [
//                    {
//                        name: '价格',
//                        key: 'goods-price',// 唯一
//                    },
                    {
                        name: '运费',
                        key: 'express-price',// 唯一
                    },
                    {
                        name: '包邮规则',
                        key: 'shipping',// 唯一
                    },
                    {
                        name: '限购',
                        key: 'confine',
                    },
                    {
                        name: '积分',
                        key: 'integral',
                    },
                    {
                        name: '会员',
                        key: 'is-goods-member',
                    },
                    {
                        name: '超级会员卡',
                        key: 'is-vip',
                    },
                ]
            }
        },
        methods: {
            checkChooseList() {
                if (this.isAllChecked) {
                    this.dialogTitle = '已选所有商品';
                    return true;
                }
                if (this.chooseList.length > 0) {
                    this.dialogTitle = '已选商品' + this.chooseList.length + '种';
                    return true;
                }
                this.$message.warning('请先勾选要设置的商品');
                return false;
            },
            upDown(command) {
                let self = this;
                if (!self.checkChooseList()) {
                    return false;
                }
                if (command === 1) {
                    this.statusChange(1)
                }
                console.log(this.chooseList);
                let text = this.isAllChecked ? '警告:批量' + (command === 1 ? '上架' : '下架') + '所有商品?' : '批量' + (command === 1 ? '上架' : '下架') + ',是否继续';
                if('<?= $sign ?>' == 'composition' || '<?= $sign ?>' == 'community' ) {
                    let ids = [];
                    for(let i in this.chooseList) {
                        ids.push(this.chooseList[i].id)
                    }
                    if('<?= $sign ?>' == 'composition') {
                        this.batchAction({
                            url: self.batchUpdateStatusUrl,
                            content: text,
                            params: {
                                ids: ids,
                                prop: 'status',
                                prop_value: command,
                            }
                        });
                    }else {
                        this.batchAction({
                            url: self.batchUpdateStatusUrl,
                            content: text,
                            params: {
                                ids: ids,
                                type: command == 1 ? 'up':'down',
                            }
                        });
                    }
                }else {
                    this.batchAction({
                        url: self.batchUpdateStatusUrl,
                        content: text,
                        params: {
                            batch_ids: this.chooseList,
                            is_all: this.isAllChecked ? 1 : 0,
                            status: command,
                            plugin_sign: '<?= $sign ?>',
                        }
                    });
                }
            },
            // 上架状态开关，弹框文字提示
            statusChange(res) {
                let text = this.statusChangeText;
                if (res && text) {
                    this.$alert(text, '提示', {
                        confirmButtonText: '确定',
                        callback: action => {
                        }
                    });
                }
            },
            allDelete() {
                let self = this;
                if (!self.checkChooseList()) {
                    return false;
                }

                let text = this.isAllChecked ? '警告:是否确认删除所有商品?' : '是否确认删除选中的商品?';
                if('<?= $sign ?>' == 'composition' || '<?= $sign ?>' == 'community' ) {
                    let ids = [];
                    for(let i in this.chooseList) {
                        ids.push(this.chooseList[i].id)
                    }
                    if('<?= $sign ?>' == 'composition') {
                        this.batchAction({
                            url: self.batchUpdateStatusUrl,
                            content: text,
                            params: {
                                ids: ids,
                                prop: 'is_delete',
                                prop_value: 1,
                            }
                        });
                    }else {
                        this.batchAction({
                            url: self.batchUpdateStatusUrl,
                            content: '是否确认删除选中的活动',
                            params: {
                                ids: ids,
                                type: 'del',
                            }
                        });
                    }
                }else {
                    this.batchAction({
                        url: self.batchDestroyUrl,
                        content: text,
                        params: {
                            batch_ids: this.chooseList,
                            is_all: this.isAllChecked ? 1 : 0,
                            plugin_sign: '<?= $sign ?>',
                        }
                    });
                }
            },
            batchAction(data) {
                let self = this;
                self.batchExtraParams.forEach(function (item) {
                    data.params[item.key] = item.value;
                });
                self.$confirm(data.content, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true
                    request({
                        params: {
                            r: data.url
                        },
                        data: data.params,
                        method: 'post'
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.dialogVisible = false;
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.$message.error(e.data.msg);
                        self.btnLoading = false;
                    });
                }).catch(() => {
                });
            },
            // 获取运费规则选项
            getFreight() {
                let self = this;
                this.freight.loading = true;
                request({
                    params: {
                        r: 'mall/postage-rule/all-list'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.freight.loading = false;
                    if (e.data.code === 0) {
                        self.freight.list = e.data.data.list;
                        // 添加商品时使用默认运费
                        let obj = {
                            id: 0,
                            name: '默认运费',
                            status: 1
                        };

                        self.freight.list.unshift(obj);
                        self.freight.checked = obj;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getList() {
                this.isAllChecked = false;
                this.$emit('to-search')
            },
            dialogSubmit() {
                let self = this;
                let params = {
                    batch_ids: this.chooseList,
                    is_all: this.isAllChecked ? 1 : 0,
                    plugin_sign: '<?= $sign ?>'
                };
                switch (this.currentBatch) {
                    case 'express-price':
                        if (!this.expressRule) {
                            self.$message.warning('请选择运费规则');
                            return false;
                        }
                        params.freight_id = this.expressRule.id;
                        var text = this.isAllChecked ? '警告:批量设置所有商品运费规则,是否继续' : '批量设置运费规则,是否继续';
                        this.batchAction({
                            url: self.batchFreightUrl,
                            content: text,
                            params: params
                        });
                        break;
                    case 'shipping':
                        if (!this.shipping) {
                            self.$message.warning('请选择包邮规则');
                            return false;
                        }
                        params.shipping_id = this.shipping.id;
                        var text = this.isAllChecked ? '警告:批量设置所有商品包邮规则,是否继续' : '批量设置包邮规则,是否继续';
                        this.batchAction({
                            url: self.batchShippingUrl,
                            content: text,
                            params: params
                        });
                        break;
                    case 'confine':
                        var text = this.isAllChecked ? '警告:批量设置所有商品限购,是否继续' : '批量设置限购,是否继续';
                        params.continue_goods_count = this.confine.goodsCount;
                        params.continue_order_count = this.confine.orderCount;
                        params.is_goods_confine = this.confine.isGoodsConfine ? 1 : 0;
                        params.is_order_confine = this.confine.isOrderConfine ? 1 : 0;
                        this.batchAction({
                            url: self.batchConfineUrl,
                            content: text,
                            params: params
                        });
                        break;
                    case 'integral':
                        var text = this.isAllChecked ? '警告:批量设置所有商品积分,是否继续' : '批量设置积分,是否继续';
                        params.give_integral_type = this.integral.give_integral_type;
                        params.give_integral = this.integral.give_integral;
                        params.forehead_integral = this.integral.forehead_integral;
                        params.forehead_integral_type = this.integral.forehead_integral_type;
                        params.accumulative = this.integral.accumulative;
                        this.batchAction({
                            url: 'mall/goods/batch-update-integral',
                            content: text,
                            params: params
                        });
                        break;
                    case 'is-vip':
                        console.log(params.status)
                        params.status = this.isSvip ? 1 : 0;
                        var text = this.isAllChecked ? '警告:批量设置所有商品' + (params.status ? '享受' : '取消') + '超级会员卡权益,是否继续' : '批量' + (params.status ? '享受' : '取消') + '超级会员卡权益，是否继续';
                        this.batchAction({
                            url: 'plugin/vip_card/mall/setting/batch-update-appoint',
                            content: text,
                            params: params
                        });
                        break;
                    case 'is-goods-member':
                    params.status = this.isGoodsMember ? 1 : 0;
                    var text = this.isAllChecked ? '警告:批量设置所有商品' + (params.status ? '享受' : '取消') + '会员功能,是否继续' : '批量' + (params.status ? '享受' : '取消') + '会员功能，是否继续';
                    this.batchAction({
                        url: 'mall/goods/batch-update-goods-member',
                        content: text,
                        params: params
                    });
                    break;
                    // 修改商品价格
                    case 'goods-price':
                        var text = this.isAllChecked ? '警告:批量设置所有商品售价,是否继续' : '批量设置商品售价,是否继续';
                        params.goods_price = this.goodsPrice.price;
                        params.goods_price_type = this.goodsPrice.type;
                        params.goods_price_update_type = this.goodsPrice.select_type;
                        this.batchAction({
                            url: 'mall/goods/batch-update-goods-price',
                            content: '批量设置商品售价,是否继续',
                            params: params
                        });
                        break;
                    // 插件自定义批量设置
                    default:
                        self.batchList.forEach(function (item) {
                            if (self.currentBatch === item.key) {
                                Object.assign(params, item.params);
                                self.batchAction({
                                    url: item.url,
                                    content: item.content,
                                    params: params
                                });
                            }
                        });
                        break;
                }
            },
            selectExpressRule() {
                this.freight.dialog = true;
                this.getFreight();
            },
            selectShipping() {
                this.shippingData.dialog = true;
                this.getShippingData();
            },
            getShippingData() {
                let self = this;
                this.shippingData.loading = true;
                request({
                    params: {
                        r: 'mall/free-delivery-rules/all-list'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.shippingData.loading = false;
                    if (e.data.code === 0) {
                        self.shippingData.list = e.data.data.list;
                        self.shippingData.list.unshift(obj);
                        self.shippingData.checked = obj;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            expressSubmit() {
                this.expressRule = this.freight.checked;
                this.freight.dialog = false;
            },
            shippingSubmit() {
                this.shipping = this.shippingData.checked;
                this.shippingData.dialog = false;
            },
            // 打开批量设置框
            batchSetting() {
                let self = this;
                if (!self.checkChooseList()) {
                    return false;
                }
                self.newBatchList = [];
                self.baseBatchList.forEach(function (item) {
                    if (item.key === 'is-vip') {
                        if (self.isShowSvip && self.isSvipPermission) {
                            self.newBatchList.push(item);
                        }
                    } else if (item.key === 'express-price') {
                        if (self.isShowExpress) {
                            self.newBatchList.push(item);
                        }
                    } else if (item.key === 'shipping') {
                        if (self.isShowShipping) {
                            self.newBatchList.push(item);
                        }
                    } else if (item.key === 'integral') {
                        if (self.isShowIntegral) {
                            self.newBatchList.push(item);
                        }
                    } else {
                        self.newBatchList.push(item);
                    }
                });
                self.batchList.forEach(function (item) {
                    self.newBatchList.push(item)
                });
                self.currentBatch = self.newBatchList[0].key;
                self.dialogVisible = true;
                self.checkedChange(self.isAllChecked);
            },
            getSvip() {
                request({
                    params: {
                        r: 'mall/mall-member/vip-card-permission',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.isSvipPermission = true;
                    } else {
                        this.isSvipPermission = false;
                    }
                })
            },
            checkedChange(e) {
                this.$emit('get-all-checked', e)
            }
        },
        created() {
            this.getSvip();
        }
    })
</script>