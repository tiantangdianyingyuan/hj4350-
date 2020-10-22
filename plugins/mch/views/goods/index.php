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
            :is_action="false"
            :is_add_goods="false"
            goods_url="plugin/mch/mall/goods/index"
            edit_goods_url='plugin/mch/mall/goods/edit'
            :is-show-batch-button="false"
            batch_update_status_url="plugin/mch/mall/goods/batch-switch-status"
            edit_goods_status_url="plugin/mch/mall/goods/switch-status">

        <template slot="column-col-first">
            <el-table-column prop="store.name" label="店铺信息" width="200">
                <template slot-scope="scope">
                    <div flex="box:first">
                        <app-image style="margin-right: 5px" width="25" height="25" mode="aspectFill"
                                   :src="scope.row.store.cover_url"></app-image>
                        <app-ellipsis :line="1">{{scope.row.store.name}}</app-ellipsis>
                    </div>
                </template>
            </el-table-column>
        </template>

        <template slot="column-col">
            <el-table-column width="150" prop="status" label="上架申请">
                <template slot-scope="scope">
                    <div v-if="scope.row.mchGoods.status == 1">
                        <el-button @click="auditStatus(scope.row, 1)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                <img src="statics/img/mall/pass.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="auditStatus(scope.row, 0)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                <img src="statics/img/mall/nopass.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </div>
                    <div v-if="scope.row.mchGoods.status == 2">已通过</div>
                    <div v-if="scope.row.mchGoods.status == 3">已拒绝</div>
                    <div v-if="scope.row.mchGoods.status == 0">未申请</div>
                </template>
            </el-table-column>
        </template>

        <template slot="action" slot-scope="item">
            <el-button @click="edit(item.item)" type="text" circle size="mini">
                <el-tooltip class="item" effect="dark" content="详情" placement="top">
                    <img class="app-order-icon" src="statics/img/mall/order/detail.png"
                         alt="">
                </el-tooltip>
            </el-button>
            <el-button @click="destroy(item.item)" type="text" circle size="mini">

                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                    <img class="app-order-icon" src="statics/img/mall/del.png" alt="">
                </el-tooltip>
            </el-button>
        </template>
    </app-goods-list>
    <el-dialog title="处理上架申请" :visible.sync="dialogFormVisible" width="30%">
        <el-form :model="auditForm">
            <el-form-item label="备注">
                <el-input v-model="auditForm.remark" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogFormVisible = false">取 消</el-button>
            <el-button :loading="btnLoading" type="primary" @click="auditSubmit">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                dialogFormVisible: false,
                auditForm: {
                    remark: '',
                    mch_id: '',
                    id: '',
                    type: '',
                },
                id: null,
                btnLoading: false,
            };
        },
        methods: {
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/goods/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                            mch_id: row.mch.id
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            // 商品上下架
            switchStatus(row) {
                let self = this;
                request({
                    params: {
                        r: 'plugin/mch/mall/goods/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                        mch_id: row.mch.id,
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            auditStatus(row, type) {
                let self = this;
                self.dialogFormVisible = true;
                self.auditForm.id = row.id;
                self.auditForm.mch_id = row.mch.id;
                self.auditForm.type = type;
            },
            auditSubmit(row) {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/goods/audit-submit',
                    },
                    method: 'post',
                    data: {
                        form: self.auditForm
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        self.dialogFormVisible = false;
                        self.$refs.goodsList.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(row) {
                if (row) {
                    navigateTo({
                        r: 'plugin/mch/mall/goods/edit',
                        id: row.id,
                        mch_id: row.mch_id
                    });
                } else {
                    navigateTo({
                        r: 'plugin/mch/mall/goods/edit',
                    });
                }
            },
        }
    });
</script>
