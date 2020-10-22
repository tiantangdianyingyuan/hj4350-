<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */

Yii::$app->loadViewComponent('refund/app-remark');
Yii::$app->loadViewComponent('refund/app-cancel');
Yii::$app->loadViewComponent('refund/app-agree-refund');
?>

<style>
    /*    步骤条 start*/
    .iconfont {
        font-size: 22px;
    }

    /*    步骤条 end*/
    .app-order-count-price {
        float: right;
        margin-right: 55px;
        font-size: 12px;
        text-align: right;
    }

    .app-order-status {
        padding: 50px 120px;
        margin-bottom: 30px;
    }

    .app-order-status .el-step__icon.is-text {
        border: 0px;
        width: 40px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    /*新的*/
    .card-box {
        border: 1px solid #EBEEF5;
        border-radius: 3px;
        padding: 10px;
        height: 300px;
        overflow-y: auto;
    }

    .card-box .label {
        margin-right: 5px;
        color: #999999;
        height: 100%;
    }

    .action-box {
        padding: 10px 20px;
    }

    .app-order-count-price .el-form-item {
        margin-bottom: 0;
    }

    .small-img {
        width: 45px;
        height: 45px;
        cursor: pointer;
        margin-right: 5px;
    }

    .item-box {
        margin-bottom: 5px;
    }

    .list-item-box {
        border-bottom: 1px solid #E3E3E3;
        margin-bottom: 10px;
        padding-bottom: 10px;
    }
</style>

<div id="app" v-cloak>
    <div class="app">
        <app-remark
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="remarkVisible"
                :refund-order="newOrder">
        </app-remark>
        <app-remark
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="cancelVisible"
                :refund-order="newOrder">
        </app-remark>
        <app-agree-refund
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="agreeRefundVisible"
                :is-show-confirm="refundConfirmVisible"
                :address="address"
                :refund-order="newOrder">
        </app-agree-refund>

        <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <!-- 标题栏 -->
            <div slot="header">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                              @click="$navigate({r:'mall/order/refund'})">售后订单</span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>售后订单详情</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
            <!-- 订单进度 -->
            <div class="table-body" v-loading="loading">
                <el-card class="app-order-status" shadow="never">
                    <el-steps :active="stepActive" align-center>
                        <el-step v-for="(item, index) in steps" :key="index" :title="item.title"
                                 :description="item.description">
                            <template slot="icon">
                                <img :src="item.icon">
                            </template>
                        </el-step>
                    </el-steps>
                </el-card>
                <el-row :gutter="12">
                    <el-col :span="order.type == 3 ? 12 : 8">
                        <div flex="dir:top" class="card-box">
                            <h3>售后信息</h3>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">售后类型:</span>
                                <div>{{ order.refund_type_text}}</div>
                            </div>
                            <div v-if="order.refund_data && order.refund_data.goods_status" class="item-box" flex="dir:left cross:center">
                                <span class="label">货物状态:</span>
                                <div>{{ order.refund_data.goods_status}}</div>
                            </div>
                            <div v-if="order.refund_data && order.refund_data.cause" class="item-box" flex="dir:left cross:center">
                                <span class="label" flex-box="0">申请理由:</span>
                                <div flex-box="1">{{ order.refund_data.cause }}</div>
                            </div>
                            <div v-if="order.pic_list && order.pic_list.length > 0" class="item-box" flex="dir:left">
                                <span flex-box="0" class="label">图片凭证:</span>
                                <div flex-box="1">
                                    <img v-for="(item, index) in order.pic_list" :key="index" class="small-img"
                                         :src="item" alt="" @click="openBig(item)"
                                         slot="reference">
                                </div>
                            </div>
                            <div v-if="order.remark" class="item-box" flex="dir:left cross:center">
                                <span class="label">备注信息:</span>
                                <div>{{ order.remark}}</div>
                            </div>
                            <div v-if="order.mobile" class="item-box" flex="dir:left cross:center">
                                <span class="label">联系方式:</span>
                                <div>{{ order.mobile}}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label" flex-box="0">订单号:</span>
                                <div flex-box="1">{{ order.order_no }}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label" flex-box="0">实付金额:</span>
                                <div flex-box="1">
                                    {{ order.order.total_pay_price }}
                                    <span v-if="order.order.express_price > 0" style="color: #999999;">(含运费￥{{order.order.express_price}})</span>
                                </div>
                            </div>
                            <template v-if="order.type == 1 || order.type == 3">
                                <div class="item-box" flex="dir:left cross:center">
                                    <span class="label" flex-box="0">申请退款:</span>
                                    <div flex-box="1">￥{{ order.refund_price }}</div>
                                </div>
                                <div v-if="order.is_refund == 1" class="item-box" flex="dir:left cross:center">
                                    <span class="label" flex-box="0">实际退款:</span>
                                    <div flex-box="1">￥{{ order.reality_refund_price }}</div>
                                </div>
                            </template>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label" flex-box="0">买家:</span>
                                <div flex-box="1">{{ order.user.nickname }}</div>
                            </div>
                        </div>
                    </el-col>
                    <el-col v-show="order.type" v-if="order.type != 3" :span="8">
                        <div flex="dir:top" class="card-box">
                            <h3>售后物流</h3>
                            <template v-if='order.order.send_type == 1'>
                                <div>到店自提</div>
                            </template>
                            <template v-else-if='order.order.send_type == 2'>
                                <div>同城配送</div>
                            </template>
                            <template v-else-if="(order.type == 1 || order.type == 2) && order.status == 2">
                                <div v-if="order.merchant_express && order.merchant_express_no">
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">收货人:</span>
                                        <div>{{order.user ? order.user.nickname : '未知用户'}}</div>
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">快递信息:</span>
                                        <div>
                                            <el-tag size="mini">{{order.merchant_express}}</el-tag>
                                            <span></span>
                                            <a :href="'https://www.baidu.com/s?wd='+ order.merchant_express + order.merchant_express_no"
                                               target="_blank" title='点击搜索运单号'>{{order.merchant_express_no}}</a>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="order.express && order.express_no">
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">收货人:</span>
                                        <div>{{order.refundAddress.name}}</div>
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">快递信息:</span>
                                        <div>
                                            <el-tag size="mini">{{order.express}}</el-tag>
                                            <a :href="'https://www.baidu.com/s?wd='+ order.express + order.express_no"
                                               target="_blank" title='点击搜索运单号'>{{order.express_no}}</a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </el-col>
                    <el-col :span="order.type == 3 ? 12 : 8">
                        <div flex="dir:top" class="card-box">
                            <h3>售后过程</h3>
                            <div v-for="(pItem, pIndex) in order.refund_process" class="list-item-box">
                                <div class="item-box" flex="dir:left cross:center">
                                    <span class="label">{{pItem.name}}:</span>
                                    <div class="label">{{pItem.time}}</div>
                                </div>
                                <div>{{pItem.text}}</div>
                            </div>
                        </div>
                    </el-col>
                </el-row>
                <slot :order="order"></slot>
                <el-card shadow="never" style="margin-top: 15px;">
                    <el-table stripe border :data="order.detail" style="width: 100%;margin-bottom: 15px;">
                        <el-table-column prop="goods" label="商品标题">
                            <template slot-scope="scope">
                                <div flex="dir:left cross:center">
                                    <img :src="scope.row.goods_info && scope.row.goods_info.goods_attr && scope.row.goods_info.goods_attr.pic_url ?
                                     scope.row.goods_info.goods_attr.pic_url : scope.row.goods_info.goods_attr.cover_pic"
                                         alt="" style="height: 60px;width: 60px;margin-right: 5px">
                                    <app-ellipsis :line="1">
                                        {{scope.row.goods_info && scope.row.goods_info.goods_attr &&
                                        scope.row.goods_info.goods_attr.name ?
                                        scope.row.goods_info.goods_attr.name : ''}}
                                    </app-ellipsis>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="attr" label="规格" width="220">
                            <template slot-scope="scope">
                                <el-tag size="mini" style="margin-right: 5px;"
                                        v-for="attr in scope.row.goods_info.attr_list"
                                        :key="attr.id">{{attr.attr_group_name}}:{{attr.attr_name}}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="unit_price" label="小计" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.goods_info.goods_attr.price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="num" label="数量" width="80"></el-table-column>
                        <el-table-column align="center" prop="total_original_price" label="原价" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.goods_info.goods_attr.original_price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="total_price" label="折扣后" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.total_price}}
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-form label-width="100px" :model="order" class="app-order-count-price">
                        <el-form-item label="商品小计">
                            <span>￥{{ order.order.total_goods_original_price }}</span>
                        </el-form-item>
                        <el-form-item label="运费">
                            <span>￥{{ order.order.express_original_price }}</span>
                        </el-form-item>
                        <el-form-item label="实付款">
                            <span style="color:#ff4544;">￥<b>{{ order.order.total_pay_price }}</b></span>
                        </el-form-item>
                    </el-form>
                </el-card>
                <div class="action-box" flex="dir:right">
                    <div>
                        <el-button v-if="order.action_status.is_show_cancel_refund" size="small" type="primary"
                                   @click="openDialog(order, cancelVisible = true)">取消售后
                        </el-button>
                        <el-button v-if="order.action_status.is_show_apply == 1" size="small" type="primary"
                                   @click="openDialog(order, agreeRefundVisible = true)">同意
                        </el-button>
                        <el-button v-if="order.action_status.is_show_apply == 1" size="small" type="danger"
                                   @click="openDialog(order, remarkVisible = true)">拒绝
                        </el-button>
                        <el-button v-if="order.action_status.is_show_confirm == 1" size="small"
                                   type="primary" @click="shouHuo(order)">确认收货
                        </el-button>
                        <el-button
                                v-if="order.action_status.is_show_refund == 1"
                                size="small" type="primary" @click="openDialog(order, refundConfirmVisible = true)">打款
                        </el-button>
                        <el-button size="small" type="primary" @click="toDetail(order.order_id)">订单详情</el-button>
                    </div>
                </div>
            </div>
        </el-card>
        <!-- 查看大图 -->
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img style="width: 100%;height: 100%;" :src="click_img" class="click-img" alt="">
        </el-dialog>
    </div>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                submitLoading: false,
                pay: true,
                send: true,
                dialogFormVisible: false,
                address_detail: '',
                activeNames: [],
                pay_time: '',
                send_time: '',
                confirm_time: '',
                // 新的
                // 查看大图
                dialogImg: false,
                click_img: false,
                newOrder: {},
                remarkVisible: false,
                cancelVisible: false,
                agreeRefundVisible: false,
                refundConfirmVisible: false,
                order: {
                    user: {},
                    order: {},
                    detail: [],
                    action_status: {},
                    refund_data: {},
                },
                address: [],
                steps: [],
                stepActive: 1,
            };
        },
        created() {
            this.getDetail();
        },
        methods: {
            //获取列表
            getDetail() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/order/refund-detail',
                        refund_order_id: getQuery('refund_order_id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.order = e.data.data.detail;
                        // 将detail转成数组 发货展示用
                        if (this.order.detail) {
                            let newDetail = [this.order.detail];
                            this.order.detail = newDetail;
                        }
                        this.address = e.data.data.address;
                        this.setStepActive();
                    }
                }).catch(e => {
                });
            },
            setStepActive() {
                this.steps = [
                    {
                        icon: 'statics/img/mall/order/refund_status/status_1_active.png',
                        title: '买家申请售后',
                        description: this.order.created_at,
                    },
                    {
                        icon: 'statics/img/mall/order/refund_status/status_2.png',
                        title: '待卖家受理',
                        description: '',
                    },
                ];
                if (this.order.status != 3) {
                    this.steps.push({
                        icon: 'statics/img/mall/order/refund_status/status_3.png',
                        title: '未完成售后',
                        description: '',
                    })
                }
                if (this.order.status != 1) {
                    this.stepActive = 2;
                    if (this.order.status != 1 && this.order.status == 2) {
                        this.steps[1].title = '卖家同意售后';
                        this.steps[1].icon = 'statics/img/mall/order/refund_status/status_2_1_active.png';
                    } else {
                        this.steps[1].title = '卖家拒绝售后';
                        this.steps[1].icon = 'statics/img/mall/order/refund_status/status_2_2_active.png';
                    }
                    this.steps[1].description = this.stepActive >= 2 ? this.order.status_time : '';
                }
                if (this.order.is_confirm == 1 && this.order.status == 2) {
                    if ((this.order.type == 1 || this.order.type == 3) && (this.order.is_refund == 1 || this.order.is_refund == 2)) {
                        this.stepActive = 3;
                        this.steps[2].title = '已完成售后';
                        this.steps[2].icon = 'statics/img/mall/order/refund_status/status_3_active.png';
                        this.steps[2].description = this.order.is_refund == 2 ? this.order.confirm_time : this.order.refund_time;
                    }
                    if (this.order.type == 2 && (this.order.is_confirm == 1 || this.order.is_refund == 2)) {
                        this.stepActive = 3;
                        this.steps[2].title = '已完成售后';
                        this.steps[2].icon = 'statics/img/mall/order/refund_status/status_3_active.png';
                        this.steps[2].description = this.order.confirm_time;
                    }
                }
            },
            // 新的
            openDialog(order) {
                this.newOrder = order;
            },
            dialogClose() {
                this.addressVisible = false;
                this.sellerRemarkVisible = false;
                this.clerkVisible = false;
                this.sendVisible = false;
                this.changePriceVisible = false;
            },
            dialogSubmit() {
                this.getDetail()
            },
            // 售后详情 新的
            // 显示大图
            openBig(e) {
                this.click_img = e;
                this.dialogImg = true;
            },
            dialogClose() {
                this.remarkVisible = false;
                this.cancelVisible = false;
                this.agreeRefundVisible = false;
                this.refundConfirmVisible = false;
            },
            // 商家确认收货
            shouHuo(refundOrder) {
                // 换货的确认收货
                if (refundOrder.status == 2 && refundOrder.type == 2 && refundOrder.order.send_type != 1  && refundOrder.order.send_type != 2) {
                    this.newOrder = refundOrder;
                    this.refundConfirmVisible = true;
                    return;
                }
                // 退款的确认收货
                this.$confirm('是否确认收货?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/shou-huo',
                        },
                        data: {
                            refund_order_id: refundOrder.id
                        },
                        method: 'post'
                    }).then(e => {
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.getDetail();
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
                }).catch(() => {
                });
            },
            // 进入商品详情
            toDetail(id) {
                this.$navigate({
                    r: 'mall/order/detail',
                    order_id: id
                })
            },
        }
    })
</script>