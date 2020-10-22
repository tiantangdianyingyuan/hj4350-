<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-goods-list');
?>
<style>

</style>
<div id="app" v-cloak>

    <app-goods-list
            ref="goodsList"
            goods_url="plugin/exchange/mall/card-goods/index"
            edit_goods_url='plugin/exchange/mall/card-goods/edit'
            :is-show-batch-button="isShowBatchButton"
            add_goods_title="添加礼品卡"
            header_title="礼品卡"
            text="礼品卡"
            :is-show-cat="false"
            :action-witch="actionWitch"
            sign="exchange">
        <template slot="column-col-sec">
            <el-table-column prop="cardGoods.library_name" label="兑换码库"></el-table-column>
        </template>
        <template slot="action-sec" slot-scope="item">
            <el-button v-if="item.item.status" @click="edit(item.item)" type="text" circle size="mini">
                <el-tooltip class="item" effect="dark" content="下架" placement="top">
                    <img class="app-order-icon" src="statics/img/plugins/take-off.png"
                         alt="">
                </el-tooltip>
            </el-button>
            <el-button v-else @click="edit(item.item)" type="text" circle size="mini">
                <el-tooltip class="item" effect="dark" content="上架" placement="top">
                    <img class="app-order-icon" src="statics/img/plugins/shelves.png" alt="">
                </el-tooltip>
            </el-button>
            <el-button @click="detail(item.item)" type="text" circle size="mini">
                <el-tooltip class="item" effect="dark" content="出售记录" placement="top">
                    <img class="app-order-icon" src="statics/img/mall/detail.png" alt="">
                </el-tooltip>
            </el-button>
        </template>
    </app-goods-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                isAllChecked: false,
                actionWitch: 325,
                isShowBatchButton: false,
            };
        },
        created() {
        },
        methods: {
            edit(row, index) {
                let self = this;
                self.$confirm('确认' + (row.status === 0 ? '上架' : '下架') + '此礼品卡？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/goods/switch-status',
                        },
                        method: 'post',
                        data: {
                            status: row.status,
                            id: row.id
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            row.status = row.status === 0 ? 1:0;
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    });
                })
            },
            detail(row) {
                navigateTo({
                    r: 'plugin/exchange/mall/card-goods/order-log',
                    id: row.id
                });
            }
        }
    });
</script>
