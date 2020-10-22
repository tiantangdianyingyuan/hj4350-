<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .goods-info {
        width: 100%;
        margin-top: 20px;
    }

    .goods-name {
        text-overflow: -o-ellipsis-lastline;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }
    
    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 15px;
    }

    .title {
        background-color: #F3F5F6;
        height: 40px;
        line-height: 40px;
        display: flex;
    }

    .title div {
        text-align: center;
    }

    .title+.el-card .el-card__body .el-card {
        border: 0;
    }

    .bargain-info {
        border-right: 1px #e2e2e2 solid;
        padding: 20px;
        display: flex;
        position: relative;
    }

    .bargain-item-head {
        background-color: #F3F5F6;
        padding: 0;
    }

    .platform-img {
        margin-top: -2px;
        float: left;
        display: block;
        margin-right: 5px;
    }

    .price-info {
        display: flex;
        position: absolute;
        bottom: 30px;
        left: 110px;
    }

    .item-center {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .price-info img {
        margin-right: 5px;
    }

    .price-info span {
        margin-right: 20px;
    }

    .detail-item {
        height: 60px;
        padding: 0 15px;
        margin-bottom: 20px;
        font-size: 16px;
        line-height: 60px;
    }

    .detail-info {
        display: inline-block;
        width: 50%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1;
        margin-top: 14px;
    }

    .load-more {
        height: 60px;
        width: 100%;
        text-align: center;
        line-height: 60px;
        font-size: 16px;
        color: #3399ff;
        cursor: pointer;
    }

    .el-dialog__body {
        padding-bottom: 10px;
    }

    .el-dialog {
        min-width: 600px;
    }
</style>
<div id="app" v-cloak>
    <el-card  shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>红包记录列表</span>
            </div>
        </div>
        <div v-loading="listLoading">
            <el-card>
                <!-- 状态选择 -->
                <el-tabs v-model="search.status" @tab-click="handleClick">
                    <el-tab-pane label="全部" name="-1"></el-tab-pane>
                    <el-tab-pane label="进行中" name="0"></el-tab-pane>
                    <el-tab-pane label="成功" name="1"></el-tab-pane>
                    <el-tab-pane label="失败" name="2"></el-tab-pane>
                </el-tabs>
                <div class="title">
                    <div style="width: 35%;">活动详情</div>
                    <div style="width: 15%">总金额</div>
                    <div style="width: 15%">所需人数</div>
                    <div style="width: 10%">状态</div>
                    <div style="width: 25%;">参与详情</div>
                </div>
                <template v-for="(item, index) in list">
                    <el-card shadow="never" style="margin-top: 1rem;background-color: #F3F5F6;" body-style="padding:0;background-color: #fff;">
                        <div slot="header" class="bargain-item-head">
                            <span style="margin-right: 1rem;float: left;">{{item.created_at}}</span>
                            <span style="margin-right: 1rem">
                                <img class="platform-img" v-if="item.user.userInfo.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                                <img class="platform-img" v-if="item.user.userInfo.platform == 'app'" src="statics/img/mall/ali.png" alt="">
                                <span>{{item.user.nickname}}({{item.user_id}})</span>
                            </span>
                        </div>
                        <div style="display: flex;">
                            <div style="width: 35%;">
                                <div class="bargain-info">
                                    <app-image :src="item.activity.pic_url" width="90px" height="90px" style="margin-right: 15px;"></app-image>
                                    <div class="goods-info">
                                        <div class="goods-name">{{item.activity.name}}</div>
                                    </div>
                                </div>
                            </div>
                            <div style="width: 15%;">
                                <div class="item-center">{{item.count_price}}</div>
                            </div>
                            <div style="width: 15%;border-left: 1px #e2e2e2 solid;">
                                <div class="item-center">{{item.number}}</div>
                            </div>
                            <div style="width: 10%;border-left: 1px #e2e2e2 solid;">
                                <div class="item-center">
                                    <el-tooltip class="item" effect="dark" content="进行中" placement="top">    
                                        <img src="statics/img/mall/ing.png" v-if="item.status == 0" alt="">
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="成功" placement="top">    
                                        <img src="statics/img/mall/already.png" v-if="item.status == 1" alt="">
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="失败" placement="top">    
                                        <img src="statics/img/plugins/gameover.png" v-if="item.status == 2" alt="">
                                    </el-tooltip>
                                </div>
                            </div>
                            <div style="width: 25%;border-left: 1px #e2e2e2 solid;padding: 0 35px;">
                                <div class="item-center" style="justify-content: flex-start">
                                    <app-image style="margin-right: 20px" :src="value.user.userInfo.avatar" width="60px" height="60px" v-for="value in item.children"></app-image>

                                    <el-tooltip class="item" effect="dark" content="参与详情" v-if="item.children.length != 0" placement="top">
                                        <img style="cursor: pointer;height: 32px;width: 32px;" @click="openList(item)" src="statics/img/mall/order/detail.png"></img>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </el-card>
                </template>
                <div flex="box:last cross:center" style="margin-top: 20px;">
                    <div></div>
                    <div>
                        <el-pagination
                                v-if="pageCount > 0"
                                @current-change="pagination"
                                background
                                layout="prev, pager, next, jumper"
                                :page-count="pageCount">
                        </el-pagination>
                    </div>
                </div>
                <el-dialog title="参与详情" :visible.sync="dialogVisible" width="30%">
                    <div v-for="value in detail_list" class="detail-item">
                        <app-image style="margin-right: 20px;float: left;" :src="value.user.userInfo.avatar" width="60px" height="60px"></app-image>
                        <span class="detail-info" style="line-height: 1">{{value.user.nickname}}<br/>{{value.user.created_at}}</span>
                        <div style="float: right">￥{{value.get_price}}</div>
                    </div>
                    <div @click="more" v-if="detail.length > 5 && clickMore" class="load-more">加载更多...</div>
                </el-dialog>
            </el-card>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                clickMore: true,
                dialogVisible: false,
                detail: [],
                detail_list: [],
                search: {
                    keyword: '',
                    status: "-1"
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                status: '-1',
                keyword: '',
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
                        r: 'plugin/fxhb/mall/activity/log',
                        page: self.page,
                        status: self.search.status
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    console.log(self.list[0].user.userInfo.avatar)
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(e) {
                this.getList();
            },
            openList(row) {
                this.detail = row.children;
                this.detail_list = row.children.slice(0,5);
                this.dialogVisible = !this.dialogVisible;
            },

            more() {
                this.clickMore = !this.clickMore;
                this.detail_list = this.detail;
            },
            // 搜索
            commonSearch() {
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
