<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/26
 * Time: 14:07
 */
Yii::$app->loadViewComponent('order/app-search');
?>
<style>
    .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>提现管理</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <app-export-dialog :field_list='exportList' :params="search" @selected="toExport">
                    </app-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">
            <app-search
                    @search="toSearch"
                    date-label="申请时间"
                    :tabs="tabs"
                    placeholder="请输入昵称或姓名搜索"
                    :is-search-menu="isShowOrderType"
                    :is-show-clear="isShowOrderType"
                    :is-show-platform="isShowOrderType"
                    :is-show-order-type="isShowOrderType"
                    :active-name="activeName"
                    :new-search="search">
                <template slot="extra">
                    <div class="item-box" flex="dir:left cross:center">
                        <div class="label">提现类型</div>
                        <el-select size="small" style="width: 150px" v-model="search.model_type" @change="handleClick">
                            <el-option label="全部" :value="0"></el-option>
                            <el-option v-for="item in permission" :label="item.name" :value="item.key"></el-option>
                        </el-select>
                    </div>
                </template>
            </app-search>
            <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">
                <el-table-column label="ID" prop="id" width="80"></el-table-column>
                <el-table-column label="基本信息">
                    <template slot-scope="scope">
                            <app-image mode="aspectFill" :src="scope.row.user.avatar" style="float: left;margin-right: 10px"></app-image>
                        <div>{{scope.row.user.nickname}}</div>
                        <img src="statics/img/mall/wx.png" v-if="scope.row.user.platform == 'wxapp'" alt="">
                        <img src="statics/img/mall/ali.png" v-else-if="scope.row.user.platform == 'aliapp'" alt="">
                        <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.user.platform == 'ttapp'" alt="">
                        <img src="statics/img/mall/baidu.png" v-else-if="scope.row.user.platform == 'bdapp'" alt="">
                        <el-tooltip :content="'商户信息:' + scope.row.shop_name" placement="top" v-if="scope.row.shop_name">
                            <app-ellipsis :line="1" >商户信息:{{scope.row.shop_name}}</app-ellipsis>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column label="姓名" width="140" prop="name">
                    <el-table-column label="手机号" width="140" prop="phone">
                        <template slot-scope="scope">
                            <div>{{scope.row.user.name}}</div>
                            <div>{{scope.row.user.phone}}</div>
                        </template>
                    </el-table-column>
                </el-table-column>
                <el-table-column label="提现类型" width="150" prop="type">
                    <template slot-scope="scope">
                        <div>{{scope.row.model_text}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="提现方式">
                    <template slot-scope="scope">
                        <div>{{scope.row.pay_type}}</div>
                        <template v-if="scope.row.type == 'wechat'">
                            <div>微信昵称:{{scope.row.extra.name}}</div>
                            <div>微信号:{{scope.row.extra.mobile}}</div>
                        </template>
                        <template v-if="scope.row.type == 'alipay'">
                            <div>支付宝姓名:{{scope.row.extra.name}}</div>
                            <div>支付宝账号:{{scope.row.extra.mobile}}</div>
                        </template>
                        <template v-if="scope.row.type == 'bank'">
                            <div>开户人:{{scope.row.extra.name}}</div>
                            <div>银行卡号:{{scope.row.extra.mobile}}</div>
                            <div>开户行:{{scope.row.extra.bank_name}}</div>
                        </template>
                    </template>
                </el-table-column>
                <el-table-column label="提现信息">
                    <template slot-scope="scope">
                        <div>用户申请提现金额:{{scope.row.cash.price}}元</div>
                        <div>手续费:{{scope.row.cash.service_charge}}元</div>
                        <div>实际打款金额:<span style="color: #ff4544">{{scope.row.cash.actual_price}}</span>元</div>
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="80" prop="status">
                    <template slot-scope="scope">
                        <el-tag size="small" v-if="scope.row.status == 0" type="info">待审核</el-tag>
                        <el-tag size="small" v-else-if="scope.row.status == 1">待打款</el-tag>
                        <el-tag size="small" v-else-if="scope.row.status == 2" type="success">已打款</el-tag>
                        <el-tag size="small" v-else type="danger">驳回</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="时间">
                    <template slot-scope="scope">
                        <div>申请时间:{{scope.row.time.created_at}}</div>
                        <div v-if="scope.row.status == 1 || scope.row.status == 2">审核时间:{{scope.row.time.apply_at}}</div>
                        <div v-if="scope.row.status == 2">打款时间:{{scope.row.time.remittance_at}}</div>
                        <div v-if="scope.row.status == 3">驳回时间:{{scope.row.time.reject_at}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="备注">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status == 1 || scope.row.status == 2">审核备注:{{scope.row.content.apply_content}}</div>
                        <div v-if="scope.row.status == 2">打款备注:{{scope.row.content.remittance_content}}</div>
                        <div v-if="scope.row.status == 3">驳回备注:{{scope.row.content.reject_content}}</div>
                        <div v-if="scope.row.remark">{{scope.row.remark}}</div>
                        <div>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="toRemark(scope.row)">
                                <el-tooltip class="item" effect="dark" content="备注" placement="top">
                                    <img src="statics/img/plugins/remark.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="130" fixed="right">
                    <template slot-scope="scope">
                        <el-button size="mini" circle style="margin-top: 10px" v-if="scope.row.status == 0" @click="agree(scope.row, 1)">
                            <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                <img src="statics/img/mall/pass.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="mini" circle v-if="scope.row.status == 1" style="margin-top: 10px" @click="toTransfer(scope.row, 2)">
                            <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                <img src="statics/img/mall/pay.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="mini" circle style="margin-left: 10px;margin-top: 10px" v-if="scope.row.status < 2" @click="apply(scope.row)">
                            <el-tooltip class="item" effect="dark" content="驳回" placement="top">
                                <img src="statics/img/mall/nopass.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    background :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                    :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>
        <el-dialog :title="title" :visible.sync="dialogContent" width="40%">
            <el-input type="textarea" :rows="8" v-model="content" :placeholder="placeholder" autocomplete="off"></el-input>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogContent = false">取 消</el-button>
                <el-button size="small" type="primary" @click="contentConfirm(3,content)" v-if="title == '驳回理由'" :loading="contentBtnLoading">确 定</el-button>
                <el-button size="small" type="primary" @click="remark" v-if="placeholder == '请填写备注内容'" :loading="contentBtnLoading">确 定</el-button>
            </div>
        </el-dialog>
        <el-dialog title="提示" :visible.sync="dialogAudit" width="30%">
            <div flex="dir:top main-center" style="text-align: center;font-size: 16px;">
                <div style="font-size: 18px;margin-bottom: 10px;">是否确认通过提现申请</div>
                <div>申请提现金额：<span style="color: #FF9C55">￥{{detail.cash.price}}</span></div>
                <div>手续费：<span style="color: #FF9C55">￥{{detail.cash.service_charge}}</span></div>
                <div>实际打款：<span style="color: #FF9C55">￥{{detail.cash.actual_price}}</span></div>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogAudit = false">取 消</el-button>
                <el-button size="small" type="primary" @click="contentConfirm(1)" :loading="contentBtnLoading">确 定</el-button>
            </div>
        </el-dialog>
        <el-dialog title="提示" :visible.sync="dialogTransfer" width="100px">
            <div flex="dir:top main-center" style="text-align: center;font-size: 16px;">
                <div>是否确认打款</div>
                <div>实际打款：<span style="color: #FF9C54">￥{{detail.cash.actual_price}}</span></div>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogTransfer = false">取 消</el-button>
                <el-button size="small" type="primary" @click="contentConfirm(2)" :loading="contentBtnLoading">确 定</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: -1,
                    date_start: '',
                    date_end: '',
                    send_type: -1,
                    model_type: 0,
                    plugin: 'all'
                },
                loading: false,
                activeName: '-1',
                isShowOrderType: false,
                list: [],
                pagination: {},
                exportList: [],
                tabs: [
                    {value: '-1', name: '全部'},
                    {value: '0', name: '待审核'},
                    {value: '1', name: '待打款'},
                    {value: '2', name: '已打款'},
                    {value: '3', name: '驳回'},
                ],
                title:'',
                placeholder: '',
                dialogLoading: false,
                dialogContent: false,
                dialogAudit: false,
                dialogTransfer: false,
                content: '',
                detail: {
                    cash:{}
                },
                contentBtnLoading: false,
                permission: []
            };
        },
        mounted() {
            this.search.status = -1;
            this.loading = true;
            this.loadData();
            this.getPermission();
        },
        methods: {
            agree(e) {
                this.dialogAudit = !this.dialogAudit;
                this.detail = e;
            },

            toTransfer(e) {
                this.dialogTransfer = !this.dialogTransfer;
                this.detail = e;
            },
            toRemark(res) {
                this.dialogContent = true;
                this.title = '添加备注';
                this.placeholder = '请填写备注内容';
                this.detail = res;
                this.content = res.remark;
                if(res.remark) {
                    this.title = '修改备注';
                }
            },
            remark() {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'mall/finance/remark',
                    },
                    data: {
                        content: this.content,
                        model: this.detail.model,
                        id: this.detail.id,
                    },
                    method: 'post'
                }).then(e => {
                    this.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.dialogContent = false;
                        this.content = '';
                        this.loadData();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.contentBtnLoading = false;
                    this.$message.error(e.data.msg);
                });
            },
            getPermission() {
                request({
                    params: {
                        r: '/mall/finance/permission'
                    },
                    method: 'get'
                }).then(e => {
                    if (e.data.code == 0) {
                        this.permission = e.data.data;
                        for(let i in this.permission) {
                            if(this.permission[i].key != 'share') {
                                this.permission[i].name += '提现'
                            }else {
                                this.permission[i].name += '商提现'
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toExport(e) {
                this.loadData();
            },
            toSearch(e) {
                this.search = e;
                this.loading = true;
                this.loadData();
            },
            loadData(page) {
                request({
                    params: {
                        r: 'mall/finance/cash',
                        status: this.search.status,
                        model_type: this.search.model_type,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        keyword: this.search.keyword,
                        page: page,
                        user_id: getQuery('user_id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.loadData(page);
            },
            handleClick(tab, event) {
                this.list = [];
                this.loading = true;
                this.loadData()
            },
            apply(res) {
                this.dialogContent = true;
                this.title = '驳回理由';
                this.placeholder = '请填写驳回理由';
                this.content = '';
                this.detail = res
            },

            contentConfirm(status, content) {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'mall/finance/cash-apply',
                    },
                    method: 'post',
                    data: {
                        id: this.detail.id,
                        model: this.detail.model,
                        status: status,
                        content: content,
                    }
                }).then(e => {
                    this.contentBtnLoading = false;
                    if (e.data.code === 0) {
                        this.loadData();
                        this.dialogContent = false;
                        this.content = '';
                        this.dialogTransfer = false;
                        this.dialogAudit = false;
                        this.detail = {
                            cash:{}
                        };
                    } else {
                        this.contentBtnLoading = false;
                        this.$message.error(e.data.msg);
                    }
                })
            }
        }
    })
</script>
