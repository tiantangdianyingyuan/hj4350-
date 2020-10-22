<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
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

    .el-input-group__append .el-button {
        margin: 0;
    }

    .el-dialog {
        z-index: 999;
        min-width: 600px;
    }

    .el-table {
        z-index: 99;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>电子面单</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" size="small" @click.native="senderFormVisible = true">设置默认发件人信息
                    </el-button>
                    <el-button type="primary" size="small" @click="$navigate({r: 'mall/express/edit'})">新增</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="getList">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border v-loading="listLoading">
                <el-table-column prop="express_name" width="100" label="快递公司"></el-table-column>
                <el-table-column prop="outlets_name" label="网点名称"></el-table-column>
                <el-table-column prop="outlets_code" label="网点编码"></el-table-column>
                <el-table-column prop="customer_account" label="客户号"></el-table-column>
                <el-table-column label="发件人信息" width="500%">
                    <template slot-scope="scope">
                        <div>名称： {{scope.row.name}}</div>
                        <div>联系方式： {{scope.row.tel ? scope.row.tel : scope.row.mobile}}</div>
                        <div>地址： {{scope.row.province}}{{scope.row.city}}{{scope.row.district}}{{scope.row.address}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button circle size="small" type="text"
                                   @click="$navigate({r:'mall/express/edit', id:scope.row.id})">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="small" type="text" @click="destroy(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
        <!--默认发件人信息-->
        <el-dialog title="默认发件人信息" :visible.sync="senderFormVisible" width="50%" :close-on-click-modal="false">
            <el-form :model="senderForm" label-width="100px" :rules="senderFormRules" ref="senderForm">
                <el-form-item label="发件人公司" prop="company">
                    <el-input size="small" v-model="senderForm.company" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="发件人名称" prop="name">
                    <el-input size="small" v-model="senderForm.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="发件人电话" prop="tel">
                    <el-input size="small" v-model="senderForm.tel" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="发件人手机" prop="mobile">
                    <el-input size="small" v-model="senderForm.mobile" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="发件人邮编" prop="zip_code">
                    <el-input size="small" v-model="senderForm.zip_code" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="发件人地区" prop="default">
                    <el-cascader style="width: 100%;" size="small" @change="handleChange" v-model="senderForm.default" placeholder="请选择地址"
                                 :options="district" filterable></el-cascader>
                </el-form-item>
                <el-form-item label="发件人地址" prop="address">
                    <el-input size="small" v-model="senderForm.address" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" type="primary" @click.native="senderSubmit" :loading="btnLoading">提交</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: [],
                pageCount: 0,
                listLoading: false,
                district: [],

                //默认
                senderFormVisible: false,
                senderForm: [],
                btnLoading: false,
                keyword: '',
                senderFormRules: {
                    name: [
                        {required: true, message: '发件人名称不能为空', trigger: 'blur'},
                    ],
                    address: [
                        {required: true, message: '发件人地址不能为空', trigger: 'blur'},
                    ],
                    default: [
                        {required: true, message: '发件人地区不能为空', trigger: 'blur'},
                        {
                            validator(rule, value, callback) {
                                if (value[0]) {
                                    callback();
                                } else {
                                    callback('发件人地区不能为空')
                                }
                            }
                        }
                    ],
                    mobile: [
                        {required: false, pattern: /^1\d{10}$/, message: '发件人格式不正确'},
                    ],
                    tel: [
                        {required: true, message: '发件人电话不能为空', trigger: 'blur'},
                    ],
                },

            };
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },

            handleChange(row) {
                this.senderForm.province = row[0];
                this.senderForm.city = row[1];
                this.senderForm.district = row[2];
            },
            senderSubmit() {
                this.$refs.senderForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.senderForm);
                        request({
                            params: {
                                r: 'mall/express/default-sender',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                location.reload();
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                })
            },
            //删除
            destroy: function (column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/express/destroy'
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        location.reload();
                        this.listLoading = false;
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },

            //
            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },

            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/express/index',
                        page: this.page,
                        keyword: this.keyword
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.senderForm = e.data.data.sender_default;
                        this.district = e.data.data.district;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>