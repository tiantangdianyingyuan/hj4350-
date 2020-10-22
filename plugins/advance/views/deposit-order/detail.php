<?php
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
    .table-center {
        padding: 20px;
        background-color: #fff;
        /*margin-top: 30px;*/
        display: flex;
    }
    .order-status {
        border:1px solid #e2e2e2;
        width: 810px;
        height: 188px;
        border-radius: 4px;
        padding-left: 38px;
    }
    .form-name {
        width: 805px;
        height: 188px;
        border:1px solid #e2e2e2;
        margin-left: 12px;
        border-radius: 4px;
        padding: 0 20px;
    }
    .text {
        font-size: 14px;
        color: #606266;
    }
    .is_pay {
        display: inline-block;
        height: 24px;
        line-height: 22px;
        border-radius: 5px;
        text-align: center;
        padding: 0 9px;
        background-color: rgba(167, 210, 144, .3);
        border: 1px solid #67c23a;
        color: #67c23a;
        font-size: 12px;
    }
    .table-footer {
        height: 252px;
        padding: 20px 20px 20px 20px;
        background-color: #fff;
    }
    .content {
        height: 212px;
        border-radius: 4px;
        border:1px solid #e2e2e2;
        padding: 20px;
    }
    .attr {
        background-color: rgba(236, 245, 255, .3);
        border: 1px solid #cae4ff;
        display: inline-block;
        height: 20px;
        line-height: 18px;
        font-size: 12px;
        color: #409eff;
        border-radius: 4px;
        padding: 0 5px;
    }
    .cover-pic {
        width: 60px;
        height: 60px;
        margin-right: 20px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item @click.native="returnBack">
                    <a>预售订单</a></el-breadcrumb-item>
                <el-breadcrumb-item>订单详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <div style="border:1px solid #e2e2e2; border-radius: 4px;height: 220px;">
               <div style="width:80%; height: 220px;padding-top: 65px;margin-left: 10%; margin-right: 10%;">
                   <el-steps :active="active" align-center>
                       <el-step title="已下单" icon="el-icon-s-order" :description="order.created_at"></el-step>
                       <el-step :title="order.is_pay === '0' ? '未支付定金' : '已支付定金'" :description="order.is_pay === '1' ? order.pay_time : ''" icon="el-icon-upload"></el-step>
                       <el-step :title="order.is_pay === '0' ? '未结束' : '已结束'" :description="order.is_pay === '1' ? order.pay_time : ''" icon="el-icon-picture"></el-step>
                   </el-steps>
               </div>
            </div>
        </div>
        <div class="table-center">
            <div class="order-status">
                <h3>订单状态</h3>
                <div class="text" style="margin: 26px 0;">
                    <span style="width: 56px; display: inline-block;margin-right: 10px;">订单号</span>
                    <span>{{order.advance_no}}</span>
                </div>
                <template v-if="order.order_no != '0'">
                    <div class="text" style="margin: 26px 0;">
                        <span style="width: 70px; display: inline-block;margin-right: 10px;">尾款订单号</span>
                        <span>{{order.order_no}}</span>
                    </div>
                </template>
                <div class="text" v-if="order.is_pay === '1'">
                    <span style="margin-right: 10px;">支付方式</span>
                    <span class="is_pay">{{order.pay_type === '1' ? '在线支付' : order.pay_type === '2' ? '货到付款' : order.pay_type === '3' ? '余额支付' : ''}}</span>
                </div>
            </div>
            <div class="form-name">
                <h3>表单名称</h3>
                <div>
                    <span style="margin-right: 10px;">商家备注{{order.remark}}</span>
                    <i style=" color: #409eff" @click="open" class="el-icon-edit"></i>
                </div>
            </div>
        </div>
        <div class="table-footer">
            <div class="content">
                <el-table
                        :data="tableData"
                        border
                        style="width: 100%">
                    <el-table-column
                            prop="namePic"
                            label="商品标题"
                            width="650">
                            <template slot-scope="scope">
                               <div style="display: flex; align-items: center;">
                                   <image class="cover-pic" :src="scope.row.namePic.cover_pic"></image>
                                   <span style="margin-left: 10px">{{ scope.row.namePic.name }}</span>
                               </div>
                            </template>
                    </el-table-column>
                    <el-table-column
                            prop="attr"
                            label="规格"
                            align="center"
                            width="220">
                        <template slot-scope="scope">
                            <p class="attr" v-for="(item, index) in scope.row.attr">
                                <span>{{item.attr_group_name}}:</span>
                                <span v-for="(it, ind) in item.attr_list">{{it.attr_name}}</span>
                            </p>
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            prop="price"
                            width="180"
                            label="单价">
                        <template slot-scope="scope">
                            <span>￥{{scope.row.price}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="goods_num"
                            align="center"
                            width="180"
                            label="数量">
                    </el-table-column>
                    <el-table-column
                            prop="original_price"
                            align="center"
                            width="180"
                            label="原价">
                        <template slot-scope="scope">
                            <span>￥{{scope.row.original_price}}</span>
                        </template>
                    </el-table-column>
                </el-table>
                <div style="float: right;margin-top: 26px;font-size: 15px;color: #606266;margin-right: 10px;">
                    商品定金：￥{{order.deposit_num}}
                </div>
            </div>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                order: {
                    goods: {
                        advanceGoods: {}
                    }
                },
                tableData:  []
            }
        },

        created() {
            this.loading = true;
            const index = window.location.search.indexOf('&');
            if (index !== -1) {
                let url = window.location.search.substring(index+1);
                let obj = url.substring(url.indexOf('=')+1);
                this.requestDetail(obj);
            }
        },
        methods: {
            returnBack() {
                this.$historyGo(-1);
            },
            async requestDetail(id) {
                let para = {
                    r: `plugin/advance/mall/deposit-order/detail`,
                    id: id,
                };
                const response = await request({
                    params: para,
                    method: 'get',
                });
                if (response.data.code === 0) {
                    console.log(response.data.data.order);
                    this.order = response.data.data.order;
                    let order = {
                        namePic:  {
                            name: this.order.goods.goodsWarehouse.name,
                            cover_pic: this.order.goods.goodsWarehouse.cover_pic,
                        },
                        attr: JSON.parse(this.order.goods.attr_groups),
                        goods_num: this.order.goods_num,
                        price: this.order.goods.price,
                        original_price: this.order.goods.goodsWarehouse.original_price,
                    };
                    this.tableData.push(order);
                }
                this.loading = false;
            },
            open() {
                this.$prompt('添加备注信息：', '备注', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputType: 'textarea',
                    inputValue: this.order.remark
                }).then(({ value }) => {
                   request({
                       params: {
                           r: 'plugin/advance/mall/deposit-order/remark',
                       },
                       data: {
                           id: this.order.id,
                           remark: value,
                       },
                       method: 'post',
                   }).then(response => {
                       if (response.data.code === 0) {
                           this.order.remark = value;
                           this.$message({
                               message: response.data.msg,
                               type: 'success'
                           });
                       } else {
                           this.$message({
                               message: response.data.msg,
                               type: 'error'
                           });
                       }
                   })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消输入'
                    });
                });
            }
        },
        computed: {
            active() {
                if (this.order.is_pay === '0') {
                    return 1;
                } else if (this.order.is_pay === '1') {
                    return 3;
                }
            }
        }
    })
</script>
