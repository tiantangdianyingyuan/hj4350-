<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .qr-code .el-dialog {
        min-width: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>直播管理</span>
            </div>
        </div>
        <div class="table-body">
            <el-button :loading="buttonLoading" style="margin-bottom: 15px;" type="primary" size="small" @click="$navigate({r: 'mall/live/live-edit'})">创建直播间</el-button>
            <el-table
                v-loading="listLoading"
                :data="list"
                border
                style="width: 100%">
                <el-table-column
                        width="80"
                        prop="roomid"
                        label="房间ID"
                        width="120">
                </el-table-column>
                <el-table-column
                    label="房间名"
                    width="220">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                    label="主播信息">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <img style="width: 45px;height: 45px " :src="scope.row.anchor_img">
                            <span style="margin-left: 10px;">{{scope.row.anchor_name}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        width="120"
                    label="封面图片">
                    <template slot-scope="scope">
                        <img :src="scope.row.cover_img" style="width: 45px;height: 45px">
                    </template>
                </el-table-column>
                <el-table-column
                        width="180"
                    label="计划直播时间">
                    <template slot-scope="scope">
                        <div>{{scope.row.start_time}}</div>
                        <div>{{scope.row.end_time}}</div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="状态">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.live_status === 101" type="success">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 102" type="primary">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 103" type="warning">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 104" type="error">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 105">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 106" type="error">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else type="error">{{scope.row.status_text}}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                    label="操作">
                    <template slot-scope="scope">
                        <el-button v-if="scope.row.live_status != 103" @click="addGoods(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑直播商品" placement="top">
                                <img :src="scope.row.goods.length ? 'statics/img/mall/live/edit_goods_n.png' : 'statics/img/mall/live/edit_goods_d.png'" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="scope.row.live_status != 103" @click="getQrCode(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="获取直播二维码" placement="top">
                                <img src="statics/img/mall/qr.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <el-dialog
              title="提示"
              width="20%"
              class="qr-code"
              :visible.sync="qrCodeData.dialogVisible">
              <div v-loading="qrCodeData.loading" flex="cross:center main:center">
                  <img :src="qrCodeData.qrCodeUrl" style="width: 200px;height: 200px;">
              </div>
              <span slot="footer" class="dialog-footer">
                <el-button size="small" type="primary" @click="qrCodeData.dialogVisible = false">确 定</el-button>
              </span>
            </el-dialog>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                buttonLoading: false,
                page: 1,
                pageCount: 0,
                qrCodeData: {
                    loading: false,
                    qrCodeUrl: '',
                    dialogVisible: false
                }
            };
        },
        methods: {
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/live/index',
                        page: self.page,
                        is_refresh: 1,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.buttonLoading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pageCount;
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            addGoods(row) {
                localStorage.setItem('LIVE_GOODS_LIST', JSON.stringify(row.goods));
                this.$navigate({
                    r: 'mall/live/add-goods',
                    room_id: row.roomid
                });
            },
            getQrCode(row) {
                let self = this;
                self.qrCodeData.dialogVisible= true;
                self.qrCodeData.loading = true;
                request({
                    params: {
                        r: 'mall/live/qr-code',
                        room_id: row.roomid,
                    },
                    method: 'get',
                }).then(e => {
                    self.qrCodeData.loading = false;
                    if (e.data.code == 0) {
                        self.qrCodeData.qrCodeUrl = e.data.data.file_path;
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
