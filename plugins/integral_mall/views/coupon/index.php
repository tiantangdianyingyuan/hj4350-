<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        position: relative;
        z-index: 1;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
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
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .el-dialog {
        min-width: 600px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>优惠券管理</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="add">选择优惠券
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入优惠券名称搜索" v-model="keyword" clearable
                          @clear='search'>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width='70' prop="id" label="ID"></el-table-column>
                <el-table-column prop="name" label="优惠券名称">
                    <template slot-scope="scope">
                        <span>{{scope.row.coupon.name}}</span>
                    </template>
                </el-table-column>
                <el-table-column width='180' prop="sub_price" label="优惠方式">
                    <template slot-scope="scope">
                        <div v-if="scope.row.coupon.type == 2">优惠:{{scope.row.coupon.sub_price}}元</div>
                        <div v-if="scope.row.coupon.type == 1">{{scope.row.coupon.discount}}折</div>
                        <div v-if="scope.row.coupon.discount_limit && scope.row.coupon.type == 1">优惠上限:{{scope.row.coupon.discount_limit}}</div>
                    </template>
                </el-table-column>
                <el-table-column width='180' prop="integral_num" label="兑换积分/兑换金额">
                    <template slot-scope="scope">
                        <span>{{scope.row.integral_num}}/￥{{scope.row.price}}</span>
                    </template>
                </el-table-column>
                <el-table-column width='150' prop="send_count" label="已领/剩余">
                    <template slot-scope="scope">
                        <span>{{scope.row.get_num}}/{{scope.row.not_get_num}}</span>
                    </template>
                </el-table-column>
                <el-table-column width='150' prop="use_num" label="已使用">
                </el-table-column>
                <el-table-column width='120' prop="exchange_num" label="单个用户可领数">
                </el-table-column>
                <el-table-column width='350' prop="expire_type" label="有效时间">
                    <template slot-scope="scope">
                        <span v-if="scope.row.coupon.expire_type == 1">
                            领取{{scope.row.coupon.expire_day}}天后过期
                        </span>
                        <span v-else-if="scope.row.coupon.expire_type == 2">
                            {{scope.row.coupon.begin_time}}至{{scope.row.coupon.end_time}}
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="handleEdit(scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="handleDel(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="toDetail(scope.row)">
                            <el-tooltip class="item" effect="dark" content="领取详情" placement="top">
                                <img src="statics/img/mall/order/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    :page-size="pagination.pageSize"
                    background
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper"
                    :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog :title="choose_coupon ?'添加优惠券' : '编辑优惠券'" :visible.sync="add_list" width="30%">
        <el-form v-loading="form_loading" :model="form" label-width="150px" ref="form" :rules="formRules"
                 style="width: 80%">
            <el-form-item label="选择优惠券" v-if="choose_coupon" prop="coupon_id">
                <el-select style="width: 100%;" size="small" v-model="form.coupon_id" placeholder="请选择优惠券">
                    <el-option v-for="item in coupon" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="兑换金额" prop="price">
                <el-input size="small" type="number" v-model="form.price" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="兑换积分" prop="integral_num">
                <el-input size="small" type="number" v-model="form.integral_num" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="发放总数" prop="send_count">
                <el-input size="small" type="number" v-model="form.send_count" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="每人限制兑换数量" prop="exchange_num">
                <el-input size="small" type="number" v-model="form.exchange_num" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="add_list = false">取 消</el-button>
            <el-button :loading="form_loading" size="small" type="primary" @click="submit">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let validcode = (rule, value, callback) => {
                if (value < 0) {
                    callback(new Error('数量不能小于零'));
                } else {
                    callback();
                }
            };
            return {
                loading: false,
                form_loading: false,
                list: [],
                keyword: '',
                choose_coupon: true,
                coupon: [],
                form: {
                    coupon_id: '',
                    exchange_num: '',
                    integral_num: '',
                    send_count: '',
                    price: '',
                },
                add_list: false,
                pagination: {},
                params: { // get请求参数
                    r: 'plugin/integral_mall/mall/coupon/index'
                },
                formRules: {
                    price: [
                        {required: true, message: '售价不能为空', trigger: 'blur'},
                        {validator: validcode, trigger: 'blur'}
                    ],
                    integral_num: [
                        {required: true, message: '所需积分不能为空', trigger: 'blur'},
                        {validator: validcode, trigger: 'blur'}
                    ],
                    send_count: [
                        {required: true, message: '发放总数不能为空', trigger: 'blur'},
                        {validator: validcode, trigger: 'blur'}
                    ],
                    exchange_num: [
                        {required: true, message: '兑换数量不能为空', trigger: 'blur'},
                        {validator: validcode, trigger: 'blur'}
                    ],
                    coupon_id: [
                        {required: true, message: '请选择优惠券', trigger: 'change'},
                    ]
                },
            };
        },

        methods: {
            toDetail(detail) {
                this.$navigate({
                    r: 'plugin/integral_mall/mall/user-coupon/index',
                    coupon: detail.coupon.name,
                });
            },

            search() {
                this.params.keyword = this.keyword;
                this.params.page = 1;
                this.loadData();
            },

            add: function () {
                this.choose_coupon = true;
                this.add_list = true;
                this.form_loading = true;
                this.form = {
                    coupon_id: '',
                    exchange_num: '',
                    integral_num: '',
                    send_count: '',
                    price: '',
                };
                request({
                    params: {
                        r: 'mall/coupon/options'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.form_loading = false;
                        this.coupon = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.listLoading = false;
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },


            submit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.form_loading = true;
                        let para = Object.assign(this.form);
                        if (!this.choose_coupon) {
                            para.coupon_id = this.form.coupon.id;
                        }
                        request({
                            params: {
                                r: 'plugin/integral_mall/mall/coupon/edit'
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.form_loading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                                this.add_list = false;
                                this.loadData();
                            } else {
                                this.listLoading = false;
                                this.$message({
                                    message: e.data.msg,
                                    type: 'error'
                                });
                            }
                        }).catch(e => {
                            this.listLoading = false;
                        });
                    }
                })
            },

            //带着ID前往编辑页面
            handleEdit: function (row) {
                this.form = row;
                this.form.coupon_id = row.coupon.coupon_id;
                this.add_list = true;
                this.choose_coupon = false;
            },

            //分页
            pageChange(page) {
                this.params.page = page;
                this.loadData();
            },

            //删除
            handleDel: function (row) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.loading = true;
                    let para = {id: row.id};
                    request({
                        params: {
                            r: 'plugin/integral_mall/mall/coupon/destroy'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code === 0) {
                            const h = this.$createElement;
                            this.$message({
                                message: '删除成功',
                                type: 'success'
                            });
                            setTimeout(() => {
                                this.loadData()
                            }, 300);
                        } else {
                            this.$alert(e.data.msg, '提示', {
                                confirmButtonText: '确定'
                            })
                        }
                    }).catch(e => {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    });
                })
            },
            // 根据参数获取请求信息
            loadData() {
                this.list = [];
                this.loading = true;
                request({
                    params: this.params,
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.listLoading = false;
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            }
        },
        created() {
            this.loadData();
        }
    })
</script>