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

    .input-item {
        display: inline-block;
        width: 200px;
        margin: 0 0 20px 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>提现记录</span>
                <span style="margin-left: 15px;">账号余额: <span style="color: #ff4544;">{{mch.account_money}}</span></span>
                <span style="margin-left: 10px;"><el-button @click="dialogFormVisible = true" size="mini">提现</el-button></span>
                <div style="float: right;margin: -5px 0">
                    <app-export-dialog :field_list='export_list' :params="searchData"
                                       @selected="exportConfirm"></app-export-dialog>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item">
                <el-input size="small" placeholder="请输入订单号搜索" v-model="keyword">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column label="提现金额(元)" width="100" prop="money"></el-table-column>
                <el-table-column label="订单号" prop="order_no" width="220"></el-table-column>
                <el-table-column label="提现类型">
                    <template slot-scope="scope">
                        <div v-if="scope.row.type == 'wx'" flex="dir:top">
                            <span>微信</span>
                            <span style="color: #999999;">姓名: {{scope.row.type_data.nickname}}</span>
                            <span style="color: #999999;">账号: {{scope.row.type_data.account}}</span>
                        </div>
                        <div v-if="scope.row.type == 'alipay'" flex="dir:top">
                            <span>支付宝</span>
                            <span style="color: #999999;">姓名: {{scope.row.type_data.nickname}}</span>
                            <span style="color: #999999;">账号: {{scope.row.type_data.account}}</span>
                        </div>
                        <div v-if="scope.row.type == 'auto'" flex="dir:top">
                            <span>自动转账</span>
                        </div>
                        <div v-if="scope.row.type == 'balance'" flex="dir:top">
                            <span>余额</span>
                        </div>
                        <div v-if="scope.row.type == 'bank'" flex="dir:top">
                            <span>银行卡</span>
                            <span style="color: #999999;">开户行: {{scope.row.type_data.bank_name}}</span>
                            <span style="color: #999999;">开户人: {{scope.row.type_data.nickname}}</span>
                            <span style="color: #999999;">账号: {{scope.row.type_data.account}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="提现状态">
                    <template slot-scope="scope">
                        <template v-if="scope.row.status == 0"><span>待处理</span></template>
                        <template v-else-if="scope.row.status == 2">
                            <span style="color: #ff4544;">审核未通过</span>
                        </template>
                        <template v-else>
                            <span v-if="scope.row.transfer_status == 1">已打款</span>
                            <span style="color: #ff4544;" v-else-if="scope.row.transfer_status == 2">拒绝打款</span>
                            <template v-else>
                                <span>待打款</span>
                            </template>
                        </template>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" width="180" label="提现时间"></el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>

    <el-dialog title="申请提现" :visible.sync="dialogFormVisible">
        <el-form :model="dialogForm" :rules="dialogRules" ref="dialogForm" label-width="120px" size="small">
            <el-form-item label="提现金额" prop="money">
                <el-input type="number" v-model="dialogForm.money" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="提现方式" prop="type">
                <el-radio v-for="(item, index) in mchSetting.cash_type" :key="index" v-model="dialogForm.type"
                          :label="item">
                    <template v-if="item == 'auto'">自动打款</template>
                    <template v-if="item == 'wx'">微信</template>
                    <template v-if="item == 'alipay'">支付宝</template>
                    <template v-if="item == 'bank'">银联</template>
                    <template v-if="item == 'balance'">余额</template>
                </el-radio>
            </el-form-item>
            <el-form-item label="提现信息" prop="type_data">
                <div flex="dir:top" v-if="dialogForm.type == 'wx' || dialogForm.type == 'alipay'">
                    <div>
                        <span>姓名</span>
                        <el-input v-model="dialogForm.type_data.nickname"></el-input>
                    </div>
                    <div>
                        <span>账号</span>
                        <el-input v-model="dialogForm.type_data.account"></el-input>
                    </div>
                </div>
                <div flex="dir:top" v-if="dialogForm.type == 'bank'">
                    <div>
                        <span>开户人</span>
                        <el-input v-model="dialogForm.type_data.nickname"></el-input>
                    </div>
                    <div>
                        <span>开户行</span>
                        <el-input v-model="dialogForm.type_data.bank_name"></el-input>
                    </div>
                    <div>
                        <span>账号</span>
                        <el-input v-model="dialogForm.type_data.account"></el-input>
                    </div>
                </div>
                <div flex="dir:top" v-if="dialogForm.type == 'balance'">打款到余额</div>
                <div flex="dir:top" v-if="dialogForm.type == 'auto'">自动打款</div>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogFormVisible = false">取 消</el-button>
            <el-button size="small" :loading="btnLoading" type="primary" @click="dialogSubmit('dialogForm')">确 定
            </el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                    date: '',
                    start_date: '',
                    end_date: '',
                },
                date: '',
                keyword: '',
                form: [],
                pageCount: 0,
                listLoading: false,
                export_list: [],
                mch: {},
                dialogFormVisible: false,
                dialogForm: {
                    type: 'auto',
                    type_data: {
                        nickname: '',
                        account: '',
                        bank_name: '',
                    }
                },
                dialogRules: {
                    money: [
                        {required: true, message: '请填写提现金额', trigger: 'change'},
                    ],
                    type: [
                        {required: true, message: '请选择提现类型', trigger: 'change'},
                    ],
                    type_data: [
                        {required: true, message: '请填写提现信息', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                mchSetting: {
                    cash_type: []
                }
            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.date = this.date;
            },
            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.searchData.start_date = '';
                    this.searchData.end_date = ''
                }
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/mch/cash-log',
                        page: this.page,
                        date: this.date,
                        user_id: getQuery('user_id'),
                        keyword: this.keyword,
                        start_date: this.searchData.start_date,
                        end_date: this.searchData.end_date,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.export_list = e.data.data.export_list;
                        this.pageCount = e.data.data.pagination.page_count;
                        this.mch = e.data.data.mch;
                        this.mchSetting = e.data.data.setting;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            dialogSubmit(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.dialogForm.type_data.cash = self.dialogForm.money;
                        if (self.dialogForm.type !== 'bank') {
                            delete self.dialogForm.type_data.bank_name;
                        }
                        request({
                            params: {
                                r: 'mall/mch/cash-submit'
                            },
                            method: 'post',
                            data: {
                                form: self.dialogForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.dialogFormVisible = false;
                                self.getList();
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            selectDateTime(e) {
                if (e != null) {
                    this.searchData.start_date = e[0];
                    this.searchData.end_date = e[1];
                }
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
