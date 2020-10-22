<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-user-batch');
?>
<style>
    .table-body {
        padding: 20px 20px 0 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
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
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .remark_name {
        color: #888888;
        font-size: 12px;
        margin-top: -5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
        display: inline-block;
        height: 15px;
        line-height: 15px;
    }
    .remark {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
        overflow: hidden;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>用户管理</span>
                <app-export-dialog style="float: right;margin-top: -5px" :field_list='exportList' :params="searchData"
                                   @selected="exportConfirm">
                </app-export-dialog>
            </div>
        </div>
        <div class="table-body">
            <el-select size="small" v-model="member_level" @change='search' class="select">
                <el-option key="0" label="全部会员" value="0"></el-option>
                <el-option :key="item.level" :label="item.name" :value="item.level" v-for="item in member"></el-option>
            </el-select>
            <el-select size="small" v-model="platform" @change='search' class="select">
                <el-option key="0" label="全部平台" value="0"></el-option>
                <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                <el-option key="bdapp" label="百度" value="bdapp"></el-option>
            </el-select>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入ID/昵称/手机号" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <app-user-batch :choose-list="multipleSelection" :mall-members="mall_members" @to-search="getList">
                <template slot="batch" slot-scope="item">
                    <slot name="batch" :item="item.item"></slot>
                </template>
            </app-user-batch>
            <el-table
            ref="multipleTable"
            class="table-info"
            :data="form"
            border
            style="width: 100%"
            v-loading="listLoading"
            @selection-change="handleSelectionChange">
                <el-table-column
                  type="selection"
                  width="55">
                </el-table-column>
                <el-table-column prop="user_id" label="ID" width="75"></el-table-column>
                <el-table-column label="头像" width="280">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <app-image mode="aspectFill" style="margin-right: 8px;flex-shrink: 0" :src="scope.row.avatar"></app-image>
                            <div style="width: 100%;">
        <!--                             <el-button class="edit-sort" type="text" @click="editRemark(scope.row)">
                                        <img src="statics/img/mall/order/edit.png" alt="">
                                    </el-button> -->
                                <div>{{scope.row.nickname}}</div>
                                <el-tooltip v-if="scope.row.remark_name" effect="dark" placement="bottom-start" :content="`备注名：${scope.row.remark_name}`">
                                    <div class="remark_name">备注名：{{scope.row.remark_name}}</div>
                                </el-tooltip>
                                <div flex="main:justify" style="width: 100%;">
                                    <img class="platform-img" v-if="scope.row.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                                    <img class="platform-img" v-if="scope.row.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                                    <img class="platform-img" v-if="scope.row.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                                    <img class="platform-img" v-if="scope.row.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                                    <img src="statics/img/mall/platform/backstage.png" v-if="scope.row.platform == ''" alt="">
                                    <el-button v-if="scope.row.platform_user_id" @click="openId(scope.$index)" type="success" style="padding:5px !important;">显示OpenId</el-button>
                                </div>
                                <div v-if="scope.row.is_open_id">{{scope.row.platform_user_id}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="remark" label="备注" width="180">
                    <template slot-scope="scope">
                        <el-tooltip class="item" v-if="scope.row.showRemark" effect="dark" :content="scope.row.remark" placement="top">
                            <div class="remark">{{scope.row.remark}}</div>
                        </el-tooltip>
                        <div v-else class="remark">{{scope.row.remark}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机号" width="120">
                </el-table-column>
                <el-table-column prop="member_level" label="会员类型" width="120">
                    <template slot-scope="scope">
                        <div v-if="scope.row.member_level == item.level" v-for="item in mall_members">{{item.name}}</div>
                        <div v-if="scope.row.member_level == 0">普通用户</div>
                    </template>
                </el-table-column>
                <el-table-column prop="order_count" label="订单数">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/order/index', user_id:scope.row.user_id})"
                                   v-text="scope.row.order_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="coupon_count" label="优惠券数量">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/coupon', user_id:scope.row.user_id})"
                                   v-text="scope.row.coupon_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="card_count" label="卡券数量">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/card', user_id:scope.row.user_id})"
                                   v-text="scope.row.card_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="balance" label="余额">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/balance-log', user_id:scope.row.user_id})"
                                   v-text="scope.row.balance"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="integral" label="积分">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/integral-log', user_id:scope.row.user_id})"
                                   v-text="scope.row.integral"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="加入时间" width="180"></el-table-column>
                <el-table-column label="操作" width="180"  fixed="right">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                            <el-button circle type="text" size="mini" @click="$navigate({r: 'mall/user/edit', id:scope.row.user_id, page: page})">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-button>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="充值积分" placement="top">
                            <el-button circle type="text" size="mini" @click="handleIntegral(scope.row)">
                                <img src="statics/img/mall/integral.png" alt="">
                            </el-button>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="充值余额" placement="top">
                            <el-button circle type="text" size="mini" @click="handleBalance(scope.row)">
                                <img src="statics/img/mall/balance.png" alt="">
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination @current-change="pagination" hide-on-single-page background layout="prev, pager, next, jumper"
                               :page-count="pageCount" :current-page="currentPage"></el-pagination>
            </div>
        </div>
        <!-- 充值积分 -->
        <el-dialog title="充值积分" :visible.sync="dialogIntegral" width="30%">
            <el-form :model="integralForm" label-width="80px" :rules="integralFormRules" ref="integralForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="integralForm.type" label="1">充值</el-radio>
                    <el-radio v-model="integralForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="积分数" prop="num" size="small">
                    <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" v-model="integralForm.num" :max="999999999"></el-input>
                </el-form-item>
                <el-form-item label="充值图片" prop="pic_url">
                    <app-attachment :multiple="false" :max="1" @selected="integralPicUrl">
                        <el-button size="mini">选择文件</el-button>
                    </app-attachment>
                    <app-image width="80px" height="80px" mode="aspectFill" :src="integralForm.pic_url"></app-image>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="integralForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogIntegral = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="integralSubmit">充值</el-button>
            </div>
        </el-dialog>
        <!-- 充值余额 -->
        <el-dialog title="充值余额" :visible.sync="dialogBalance" width="30%">
            <el-form :model="balanceForm" label-width="80px" :rules="balanceFormRules" ref="integralForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="balanceForm.type" label="1">充值</el-radio>
                    <el-radio v-model="balanceForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="金额" prop="price" size="small">
                    <el-input type="number" v-model="balanceForm.price" :max="99999999"></el-input>
                </el-form-item>
                <el-form-item label="充值图片" prop="pic_url">
                    <app-attachment :multiple="false" :max="1" @selected="balancePicUrl">
                        <el-button size="mini">选择文件</el-button>
                    </app-attachment>
                    <app-image width="80px" height="80px" mode="aspectFill" :src="balanceForm.pic_url"></app-image>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="balanceForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogBalance = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="balanceSubmit">充值</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                },
                platform: '0',
                member_level: '0',
                mall_members: [],
                keyword: '',
                form: [],
                member: [],
                pageCount: 0,
                page: 1,
                member_page: 1,
                currentPage: null,
                listLoading: false,
                btnLoading: false,

                // 导出
                exportList: [],

                //积分
                dialogIntegral: false,
                integralForm: {
                    type: '1',
                    num: '',
                    pic_url: '',
                    remark: '',
                },
                integralFormRules: {
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    num: [
                        {required: true, message: '积分数不能为空', trigger: 'blur'},
                    ],
                },

                //余额
                dialogBalance: false,
                balanceForm: {
                    type: '1',
                    price: '',
                    pic_url: '',
                    remark: '',
                },
                balanceFormRules: {
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    num: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                },

                multipleSelection: []
            };
        },
        methods: {
            strlen(str){
                var len = 0;
                for (var i=0; i<str.length; i++) {
                 var c = str.charCodeAt(i);
                //单字节加1
                 if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
                   len++;
                 }
                 else {
                  len+=2;
                 }
                }
                return len;
            },
            openId(index) {
                let item = this.form;
                item[index].is_open_id = !item[index].is_open_id;
                this.form = JSON.parse(JSON.stringify(this.form));
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
            },
            //积分
            integralPicUrl(e) {
                if (e.length) {
                    this.integralForm.pic_url = e[0].url;
                }
            },
            handleIntegral(row) {
                this.integralForm = Object.assign(this.integralForm, {user_id: row.user_id});
                this.dialogIntegral = true;
            },
            integralSubmit() {
                this.$refs.integralForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.integralForm);
                        this.btnLoading = true;
                        this.dialogIntegral = false;
                        request({
                            params: {
                                r: 'mall/user/integral',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },

            //余额
            balancePicUrl(e) {
                if (e.length) {
                    this.balanceForm.pic_url = e[0].url;
                }
            },
            handleBalance(row) {
                this.balanceForm = Object.assign(this.balanceForm, {user_id: row.user_id});
                this.dialogBalance = true;
            },
            balanceSubmit() {
                this.$refs.integralForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.balanceForm);
                        this.btnLoading = true;
                        this.dialogBalance = false;
                        request({
                            params: {
                                r: 'mall/user/balance',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            //
            search() {
                this.page = 1;
                this.getList();
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/index',
                        page: this.page,
                        member_level: this.member_level,
                        platform: this.platform,
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        for(item of this.form) {
                            item.showRemark = false;
                            if(this.strlen(item.remark) > 42) {
                                item.showRemark = true;
                            }
                        }
                        this.exportList = e.data.data.exportList;
                        this.pageCount = e.data.data.pagination.page_count;
                        this.currentPage = e.data.data.pagination.current_page;
                        this.mall_members = e.data.data.mall_members;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            getMember() {
                let self = this;
                request({
                    params: {
                        r: 'mall/mall-member/index',
                        page: self.member_page
                    },
                    method: 'get',
                }).then(e => {
                    if(e.data.data.list.length > 0) {
                        if(self.member_page == 1) {
                            self.member = e.data.data.list;
                        }else {
                            self.member = self.member.concat(e.data.data.list);
                        }
                        self.member_page++;
                        self.getMember();
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            toggleSelection(rows) {
                if (rows) {
                  rows.forEach(row => {
                    this.$refs.multipleTable.toggleRowSelection(row);
                  });
                } else {
                  this.$refs.multipleTable.clearSelection();
                }
            },
            handleSelectionChange(val) {
                let self = this;
                self.multipleSelection = [];
                val.forEach(function(item) {
                    self.multipleSelection.push(item.user_id);
                })
            }
        },
        mounted: function () {
            this.page = getQuery('page') ? getQuery('page') : 1;
            this.getList();
            this.getMember();
        }
    });
</script>
