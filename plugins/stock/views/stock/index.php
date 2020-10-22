<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 14:12
 */
?>
<style>
    .el-tabs__header {
        font-size: 16px;
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

    .input-item {
        display: inline-block;
        width: 350px;
        margin-left: 25px;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
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

    .content {
        padding: 0 5px;
        line-height: 20px;
        color: #E6A23C;
        background-color: #FCF6EB;
        width: auto;
        display: inline-block;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .el-message-box__message {
        text-align: center;
        font-size: 16px;
        margin: 10px 0 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>股东管理</span>
            <el-form size="small" :inline="true" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <app-export-dialog action_url='index.php?r=plugin/stock/mall/stock/index' :field_list='exportList' :params="search" @selected="confirmSubmit">
                    </app-export-dialog>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="addStock" size="small">新增股东</el-button>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div flex="dir:left cross:center">
                    <div style="margin-right: 10px;">申请时间</div>
                    <el-date-picker
                            size="small"
                            @change="changeTime"
                            v-model="search.time"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </div>
                <div style="margin-left: 20px;" flex="dir:left cross:center">
                    <div style="margin-right: 10px;">股东等级</div>
                    <el-select size="small" v-model="level_id" @change="loadData" class="select">
                        <el-option key="0" label="全部股东" value="0"></el-option>
                        <el-option v-for="item in level_list" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                </div>
                <div class="input-item">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable @clear="toSearch">
                        <el-select size="small" v-model="search.type" slot="prepend" class="select">
                            <el-option key="4" label="用户ID" value="4"></el-option>
                            <el-option key="1" label="昵称" value="1"></el-option>
                            <el-option key="2" label="姓名" value="2"></el-option>
                            <el-option key="3" label="手机号" value="3"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未审核" name="0"></el-tab-pane>
                <el-tab-pane label="已通过" name="1"></el-tab-pane>
                <el-tab-pane label="已拒绝" name="2"></el-tab-pane>
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                    <el-table-column label="ID" prop="user_id" width="80"></el-table-column>
                    <el-table-column label="基本信息" width="400">
                        <template slot-scope="scope">
                            <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.avatar"></app-image>
                            <div style="margin-top: 25px;">{{scope.row.nickname}}</div>
                            <div v-if="scope.row.remark" class="content">
                                {{scope.row.remark}}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="股东等级" width="150" prop="level_name">
                        <template slot-scope="scope">
                            <div>
                                <span style="font-size: 14px">{{scope.row.level_name}}</span>
                                <el-button type="text" circle @click="changeLevel(scope.row,scope.$index)">
                                    <img src="statics/img/mall/order/edit.png" alt="">
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="姓名" prop="name">
                        <el-table-column label="手机号" prop="phone">
                            <template slot-scope="scope">
                                <div>{{scope.row.name}}</div>
                                <div>{{scope.row.phone}}</div>
                            </template>
                        </el-table-column>
                    </el-table-column>
                    <el-table-column label="累计分红(元)" prop="name">
                        <el-table-column label="可提现分红(元)" prop="phone">
                            <template slot-scope="scope">
                                <div>{{scope.row.all_bonus}}</div>
                                <div>{{scope.row.total_bonus}}</div>
                            </template>
                        </el-table-column>
                    </el-table-column>
                    <el-table-column label="时间" width="250">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status >= 0 && scope.row.applyed_at != '0000-00-00 00:00:00'">申请时间：{{scope.row.applyed_at}}</div>
                            <div v-if="scope.row.status == 1 || scope.row.status == 2">审核时间：{{scope.row.agreed_at}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" width="80" prop="status">
                        <template slot-scope="scope">
                            <el-tag size="small" type="info" v-if="scope.row.status == 0">待审核</el-tag>
                            <el-tag size="small" v-if="scope.row.status == 1">已通过</el-tag>
                            <el-tag size="small" type="danger" v-if="scope.row.status == 2">拒绝</el-tag>
                            <el-tag size="small" type="warning" v-if="scope.row.status == 3">处理中</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="250px" fixed="right">
                        <template slot-scope="scope">
                            <el-button v-if="scope.row.status == 0" type="text" size="mini" circle style="margin-top: 10px" @click.native="agree(scope.row)">
                                <el-tooltip class="item" effect="dark" content="通过申请" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 0" type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="apply(scope.row)">
                                <el-tooltip class="item" effect="dark" content="拒绝申请" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 1" type="text" size="mini" circle style="margin-top: 10px" @click.native="toRelease(scope.row)">
                                <el-tooltip class="item" effect="dark" content="解除股东" placement="top">
                                    <img src="statics/img/plugins/release.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 2" type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="deleteShare(scope.row.user_id)">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="openContent(scope.row)">
                                <el-tooltip class="item" effect="dark" content="备注" placement="top">
                                    <img src="statics/img/mall/order/add_remark.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tabs>
            <div flex="dir:right" style="margin-top: 20px;">
                    <el-pagination
                            hide-on-single-page
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>

            </div>
        </div>
    </el-card>
    <el-dialog :title="title" :visible.sync="dialogContent" width="30%">
        <el-form>
            <el-form-item>
                <el-input type="textarea" :rows="5" v-model="content" :placeholder="placeholder" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogContent = false">取 消</el-button>
            <el-button size="small" type="primary" v-if="title == '拒绝理由'" @click="beApply" :loading="contentBtnLoading">确 定</el-button>
            <el-button size="small" type="primary" v-else-if="title == '解除理由'" @click="beRelease" :loading="contentBtnLoading">确 定</el-button>
            <el-button size="small" type="primary" v-else @click="beRemark" :loading="contentBtnLoading">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="添加新股东" :visible.sync="toChange" width="30%" :before-close="handleClose">
        <el-form :model="addList" :rules="rules" size="small" ref="addForm" label-width="120px">
            <el-form-item label="分销商选择" prop="nickname">
                <el-autocomplete size="small" style="width: 70%;" v-model="addList.nickname" value-key="nickname" :fetch-suggestions="querySearchAsync" placeholder="请选择分销商" @select="shareClick"></el-autocomplete>
            </el-form-item>
            <el-form-item label="股东等级" prop="level">
                <el-select size="small" @change="chooseLevel" style="width: 70%;" v-model="value" placeholder="请选择">
                    <el-option v-for="item in level_list" :key="item.id" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="姓名">
                <el-input type="text" placeholder="请输入股东姓名" style="width: 70%;" size="small" v-model="addList.name" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="手机号">
                <el-input type="text" placeholder="请输入手机号" style="width: 70%;" size="small" v-model="addList.phone" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="handleClose">取 消</el-button>
            <el-button size="small" type="primary" @click="changeSubmit('addForm')" :loading="contentBtnLoading">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="修改股东等级" :visible.sync="toChangeLevel" width="30%">
        <el-form>
            <el-form-item label="股东等级">
                <el-select size="small" style="width: 70%;" v-model="value" placeholder="请选择">
                    <el-option
                        v-for="item in level_list"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                    </el-option>
                </el-select>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="toChangeLevel = false">取 消</el-button>
            <el-button size="small" type="primary" @click="submitChangeLevel" :loading="contentBtnLoading">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                if (this.value === '' || this.value === undefined) {
                    callback(new Error('请选择股东等级'));
                } else {
                    callback();
                }
            };
            return {
                search: {
                    date_start: '',
                    date_end: '',
                    keyword: '',
                    type: '4',
                    page: 1,
                    time: []
                },
                rules: {
                    nickname: [
                        { required: true, message: '请选择分销商', trigger: 'change' }
                    ],
                    level: [
                        { required: true, validator: validateRate, trigger: 'change' }
                    ],
                },
                level_list: [],
                addList: {
                    nickname: '',
                    user_id: '',
                    level: '',
                    name: '',
                    phone: '',
                },
                level_id: '0',
                title:'',
                placeholder: '',
                loading: false,
                activeName: '-1',
                list: [],
                value: null,
                toChangeLevel: false,
                pagination: {},
                dialogLoading: false,
                dialogContent: false,
                toChange: false,
                content: "",
                detail: {},
                contentBtnLoading: false,
                exportList: [],
                member: [],
                index: -1,
                keyword: {},
                status: null
            }
        },
        mounted() {
            this.loadData();
        },
        methods: {
            changeLevel(e,index) {
                this.toChangeLevel = true;
                this.detail = e;
                this.index = index;
                this.value = e.level_id;
            },
            submitChangeLevel() {
                let that = this;
                let level_name;
                for(let i in that.level_list) {
                    if(that.level_list[i].id == that.value) {
                        level_name = that.level_list[i].name
                    }
                }
                that.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/add'
                    },
                    data: {
                        user_id: that.detail.user_id,
                        level_id: that.value,
                        name: that.detail.name,
                        phone: that.detail.phone,
                    },
                    method: 'post',
                }).then(e => {
                    that.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        that.toChangeLevel = false;
                        that.detail = {};
                        console.log(that.list[that.index])
                        that.list[that.index].level_id = that.value;
                        that.list[that.index].level_name = level_name;
                        that.value = null;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.contentBtnLoading = false;
                });
            },

            addStock() {
                this.toChange = true;
                this.$nextTick(()=>{
                   this.$refs['addForm'].clearValidate();
                })
            },

            handleClose(formName) {
                this.addList = {
                    nickname: '',
                    user_id: '',
                    level: '',
                    name: '',
                    phone: '',
                };
                this.value = null;
                this.toChange = false;
            },
            //搜索
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.shareUser(cb);
            },

            shareClick(row) {
                this.addList.user_id = row.id;
                console.log(this.addList)
            },

            chooseLevel(e) {
                this.addList.level = e;
            },

            shareUser(cb) {
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/share',
                        nickname: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {});
            },
            changeSubmit(formName) {
                let that = this;
                that.$refs[formName].validate((valid) => {
                    if (valid) {
                        if(that.addList.phone && (that.addList.phone.length != 11 || !(/0?(1)[0-9]{10}/.test(that.addList.phone)))) {
                            that.$message.error('请输入正确的手机号码');
                        }else {
                            that.contentBtnLoading = true;
                            request({
                                params: {
                                    r: 'plugin/stock/mall/stock/add'
                                },
                                data: {
                                    user_id: that.addList.user_id,
                                    level_id: that.addList.level,
                                    name: that.addList.name,
                                    phone: that.addList.phone,
                                },
                                method: 'post',
                            }).then(e => {
                                that.contentBtnLoading = false;
                                if (e.data.code == 0) {
                                    that.toChange = false;
                                    that.addList = {
                                        nickname: '',
                                        user_id: '',
                                        level: '',
                                        name: '',
                                        phone: '',
                                    };
                                    that.value = null;
                                    that.loadData();
                                } else {
                                    that.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                that.contentBtnLoading = false;
                            });
                        }
                    }
                })
            },
            // 通过审核
            agree(e) {
                this.$confirm('是否确认通过审核', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消'
                }).then(res => {
                    this.detail = e;
                    this.status = 1;
                    this.content = '后台管理员审核通过';
                    this.detail.status = 3;
                    this.beApply();
                }).catch(res => {
                    this.$message({
                        type: 'info',
                        message: '取消了操作'
                    });
                });
            },
            // 发送审核消息
            beApply() {
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/apply',
                    },
                    data: {
                        user_id: this.detail.user_id,
                        status: this.status,
                        reason: this.content,
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if(this.status == 1) {
                            this.detail = {};
                            this.status = null;
                            this.content = '';
                            // let queue_id = e.data.data.queue_id;
                            // this.passStatus(queue_id)
                            this.$message.success('操作成功');
                            this.loadData();
                            this.contentBtnLoading = false;
                            this.dialogContent = false;
                        }else {
                            this.$message.success(e.data.data);
                            this.loadData();
                            this.detail = {};
                            this.status = null;
                            this.content = '';
                            this.dialogContent = false;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 时间筛选
            changeTime() {
                if(this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                }else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.toSearch();
            },
            // 搜索
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            // 获取状态
            confirmSubmit() {
                this.search.status = this.activeName
            },
            // 获取数据
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/index',
                        status: this.activeName,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        keyword: this.search.keyword,
                        search_type: this.search.type,
                        level_id: this.level_id,
                        page: this.search.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.level_list = e.data.data.level_list;
                        this.exportList = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 分页
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            // 获取数据状态
            handleClick(tab, event) {
                this.search.status = this.activeName;
                this.toSearch();
            },
            // 审核拒绝发起
            apply(e) {
                this.dialogContent = true;
                this.title = '拒绝理由';
                this.placeholder = '请填写拒绝理由';
                this.content = '';
                this.detail = e;
                this.status = 2;
            },
            // 备注
            beRemark() {
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/remark',
                    },
                    data: {
                        user_id: this.detail.user_id,
                        remark: this.content,
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.loadData();
                        this.detail = {};
                        this.content = '';
                        this.dialogContent = false;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toRelease(e) {
                this.dialogContent = true;
                this.title = '解除理由';
                this.placeholder = '请填写解除理由';
                this.content = '';
                this.detail = e;
            },
            // 解除股东
            beRelease() {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/remove',
                    },
                    data:{
                        user_id: this.detail.user_id,
                        reason: this.content,
                    },
                    method: 'post'
                }).then(e => {
                    this.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        this.$message.success('解除成功');
                        this.loadData();
                        this.contentBtnLoading = false;
                        this.dialogContent = false;
                        // let queue_id = e.data.data.queue_id;
                        // this.remove(queue_id)
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },
            // 删除记录
            deleteShare(id) {
                this.$confirm('是否删除该条记录', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'plugin/stock/mall/stock/delete',
                                },
                                data:{
                                    user_id: id
                                },
                                method: 'post'
                            }).then(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                if (e.data.code == 0) {
                                    this.loadData();
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                this.$message.error(e.data.msg);
                            });
                        } else {
                            done();
                        }
                    }
                }).then(() => {
                }).catch(e => {
                    this.$message({
                        type: 'info',
                        message: '取消了操作'
                    });
                });
            },
            // 申请添加备注
            openContent(res) {
                this.dialogContent = true;
                this.title = '添加备注';
                this.placeholder = '请填写备注内容';
                this.detail = res;
                this.content = '';
                if(res.remark) {
                    this.title = '修改备注';
                    this.content = res.remark
                }
            },
        }
    });
</script>