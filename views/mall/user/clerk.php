<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

$mchId = \Yii::$app->user->identity->mch_id;

?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
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

    .select {
        float: left;
        width: 150px;
        margin-right: 10px;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
    .user-info-box {
        margin-left: 5px;
    }
    .platform-img {
        width: 24px;
        height: 24px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>核销员</span>
                <div style="float: right;" flex="dir:left cross:center">
                    <el-button style="margin-right: 10px;"  type="primary" @click="dialogAdd = true" size="small">
                    添加核销员
                    </el-button>
                    <app-export-dialog :field_list='exportList' :params="searchData"
                                       @selected="exportConfirm">
                    </app-export-dialog>
                </div>
            </div>
        </div>
        <div class="table-body" v-loading="listLoading">
            <el-select size="small" v-model="shop" @change="search" class="select">
                <el-option key="0" label="全部核销员" value="0"></el-option>
                <el-option :key="item.id" :label="item.name" :value="item.id" v-for="item in store_list"></el-option>
            </el-select>
            <el-select size="small" v-model="platform" @change='search' class="select">
                <el-option key="all" label="全部平台" value=""></el-option>
                <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                <el-option key="bdapp" label="百度" value="bdapp"></el-option>
            </el-select>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入用户昵称搜索" v-model="keyword" @clear="search" clearable>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%">
                <el-table-column prop="user_id" label="用户ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="用户" width="180">
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <div>
                                <app-image mode="aspectFill" width="47" height="47" :src="scope.row.user.userInfo.avatar"></app-image>
                            </div>
                            <div flex="dir:top" class="user-info-box">
                                <img class="platform-img" v-if="scope.row.user.userInfo.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                                <img class="platform-img" v-if="scope.row.user.userInfo.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                                <img class="platform-img" v-if="scope.row.user.userInfo.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                                <img class="platform-img" v-if="scope.row.user.userInfo.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                                <div style="width: 120px;display: -webkit-box;height:25px;line-height: 25px;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">
                                    {{scope.row.user.nickname}}
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="store.name" width="200" label="所属门店" >
                    <template slot-scope="scope">
                        <div style="display: -webkit-box;height:50px;line-height: 25px;-webkit-box-orient: vertical;-webkit-line-clamp: 2;">
                            <div v-for="item in scope.row.store" >
                                {{item.name}}
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <!--                <el-table-column :Formatter="clerkFormatter" label="身份"></el-table-column>-->
                <el-table-column prop="order_count" label="核销订单数">
                    <template slot-scope="scope">
                        <el-button type="text"
                                   @click="$navigate({r: 'mall/order/index', clerk_id:scope.row.id, is_offline: 1})"
                                   v-text="scope.row.order_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="total_price" label="核销总额"></el-table-column>
                <el-table-column v-if="mchId == 0" prop="card_count" label="核销卡券次数">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/card', clerk_id:scope.row.user_id})"
                                   v-text="scope.row.card_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="180"  fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" circle size="mini" @click="handleClerkEdit(scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" circle size="mini" @click="destroy(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination @current-change="pagination" hide-on-single-page background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
        <!-- 修改门店 -->
        <el-dialog title="修改门店" :visible.sync="dialogEdit" width="30%">
            <el-form :model="editForm" label-width="100px" :rules="editFormRules" ref="editForm">
                <el-form-item label="门店选择" prop="store_id">
                    <el-select v-model="editForm.store_id">
                        <el-option v-for="item in store_list" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button :loading="btnLoading" type="primary" @click="editSubmit">提交</el-button>
                    <el-button @click="dialogEdit = false">取消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>

        <!-- 设置核销员 -->
        <el-dialog title="设置核销员" :visible.sync="dialogAdd" width="30%">
            <el-form :model="addForm" label-width="100px" :rules="addFormRules" ref="addForm">
                <el-form-item label="门店选择" prop="store_id">
                    <el-select style="width: 50%" size="small" v-model="addForm.store_id" placeholder="无">
                        <el-option v-for="item in store_list" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="昵称搜索" prop="nickname">
                    <el-autocomplete style="width: 50%" size="small" v-model="addForm.nickname" value-key="nickname"
                                     :fetch-suggestions="querySearchAsync" placeholder="请输入内容"
                                     @select="clerkClick"></el-autocomplete>
                </el-form-item>
                <el-form-item>
                    <el-button size="small" style="float: right;padding: 0;width: 80px;height: 32px;margin-right: 20px" type="primary" @click="addSubmit" :loading="btnLoading">保存</el-button>
                    <el-button size="small" style="float: right;padding: 0;width: 80px;height: 32px;margin-right: 20px" @click="dialogAdd = false">取消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let validatePass = (rule, value, callback) => {
                if (!this.addForm.hasOwnProperty('user_id') || !this.addForm.user_id) {
                    callback('请选择用户');
                } else {
                    callback();
                }
            };
            return {
                shop: '0',
                keyword: '',
                platform: '',
                userKeyword: '',
                form: [],
                store_list: [],
                pageCount: 0,
                listLoading: false,
                btnLoading: false,
                //添加核销员
                dialogAdd: false,
                addForm: {},
                addFormRules: {
                    store_id: [
                        {required: true, message: '门店不能为空', trigger: 'blur'},
                    ],
                    nickname: [
                        {required: true, message: '用户不能为空', trigger: ['blur']},
                        {validator: validatePass, trigger: 'blur'}
                    ],
                },
                //修改门店
                dialogEdit: false,
                editForm: {},
                editFormRules: {
                    store_id: [
                        {required: true, message: '门店不能为空', trigger: 'blur'},
                    ],
                },
                mchId: '<?= $mchId ?>',

                // 导出
                exportList: [],
                searchData: {
                    keyword: '',
                },
            };
        },
        methods: {
            clerkFormatter(row) {
                return row.identity.is_clerk == 1 ? '核销员' : '--';
            },
            //搜索
            querySearchAsync(queryString, cb) {
                this.userKeyword = queryString;
                this.clerkUser(cb);
            },

            clerkClick(row) {
                //this.addForm = Object.assign({id: row.id}, this.addForm);
                this.addForm.user_id = row.id;
                this.$refs.addForm.validateField('nickname');
            },

            clerkUser(cb) {
                request({
                    params: {
                        r: 'mall/user/search-user',
                        keyword: this.userKeyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            //编辑
            handleClerkEdit(row) {
                let store_id = row.store ? row.store.id : '';
                this.editForm = Object.assign({store_id: store_id}, row);
                this.dialogEdit = true;
            },

            editSubmit() {
                this.$refs.editForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.editForm);
                        this.edit(para);
                    }
                });
            },
            addSubmit() {
                this.$refs.addForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.addForm);
                        this.edit(para);
                    }
                });
            },

            edit(para) {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'mall/user/clerk-edit'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code === 0) {
                        location.reload();
                    } else {
                        this.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
            },

            destroy(row) {
                this.$confirm('确认解除核销员?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/user/clerk-destroy'
                        },
                        data: {
                            id: row.id
                        },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            location.reload();
                        } else {
                            this.$message.error(e.data.msg)
                        }
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

            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/clerk',
                        page: this.page,
                        keyword: this.keyword,
                        store_id: this.shop,
                        platform: this.platform,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.exportList = e.data.data.exportList;
                        this.form = e.data.data.list;
                        this.store_list = e.data.data.store_list;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>