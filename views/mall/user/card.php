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

    .table-info .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 300px;
        margin-right: 10px;
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

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .el-input-group__prepend {
        background-color: #fff;
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

    .line {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .history-clerk {
        color: #409EFF;
        cursor: pointer;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>用户卡券</span>
            </div>
        </div>
        <div class="table-body">
            <div>
                <el-select size="small" v-model="status" @change="handleCommand" class="select">
                    <el-option key="0" label="全部" :value="0"></el-option>
                    <el-option key="1" label="未使用" :value="1"></el-option>
                    <el-option key="2" label="已使用" :value="2"></el-option>
                </el-select>
                <div class="input-item">
                    <el-input size="small" @keyup.enter.native="search" clearable @clear="search" placeholder="请输入搜索内容"
                              v-model="key_name">
                        <el-select style="width: 100px" slot="prepend" v-model="key_code">
                            <el-option label="门店" value="0"></el-option>
                            <el-option label="昵称" value="2"></el-option>
                            <el-option label="卡券ID" value="3"></el-option>
                            <el-option label="卡券名称" value="1"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
                <el-button type="primary" @click="batchDestroy" size="small">批量删除</el-button>
            </div>
            <div style="display: flex;margin: 10px 0;">
                <div style="margin:5px 10px 5px 0;">
                    <el-date-picker
                            @change="search"
                            size="small"
                            v-model="send_date"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="发放开始日期"
                            end-placeholder="发放结束日期"
                    ></el-date-picker>
                    <br>
                </div>
                <div style="margin:5px 0;">
                    <el-date-picker
                            @change="search"
                            size="small"
                            v-model="clerk_date"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="核销开始日期"
                            end-placeholder="核销结束日期"
                    ></el-date-picker>
                </div>
            </div>
            <el-table border :data="form" style="width: 100%" @selection-change="selsChange" v-loading="listLoading">
                <el-table-column align='center' type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="nickname" label="昵称" width="150"></el-table-column>
                <el-table-column label="卡券名称" width="150">
                    <template slot-scope="scope">
                        <el-tooltip effect="dark" placement="top">
                            <div slot="content" style="width: 200px;">
                                {{scope.row.name}}
                            </div>
                            <div class="line">{{scope.row.name}}</div>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column label="卡券信息" width="340">
                    <template slot-scope="scope">
                        <div>
                            <div class="info p-2" style="border: 1px solid #ddd;">
                                <div flex="dir:left box:first">
                                    <app-image style="border-radius:50%;margin:auto 5px" mode="aspectFill"
                                               :src="scope.row.pic_url"></app-image>
                                    <el-tooltip effect="dark" placement="top">
                                        <div slot="content" style="width: 300px;">
                                            {{scope.row.content}}
                                        </div>
                                        <div class="line">{{scope.row.content}}</div>
                                    </el-tooltip>
                                </div>
                            </div>
                            <div flex="dir:left box:first cross:top" style="margin-top: 8px;">
                                <el-tag size="small">来源于</el-tag>
                                <app-image style="border-radius:50%;margin:0 5px;flex-grow: 0" mode="aspectFill"
                                           width="24px" height="24px"
                                           :src="platform[scope.row.from.platform]"></app-image>
                                <div>
                                    <app-ellipsis :line="1">{{scope.row.from.name}}</app-ellipsis>
                                    <el-tag size="mini" v-if="scope.row.from.id" :disable-transitions="true">原卡券id：{{scope.row.from.id}}</el-tag>
                                </div>
                            </div>
                            <div flex="dir:left box:first" v-if="scope.row.receive.name" style="margin-top: 8px;">
                                <el-tag size="small" type="warning">已转赠</el-tag>
                                <app-image style="border-radius:50%;margin:auto 5px;flex-grow: 0" mode="aspectFill"
                                           width="24px" height="24px"
                                           :src="platform[scope.row.receive.platform]"></app-image>
                                <div>
                                    <app-ellipsis :line="1">{{scope.row.receive.name}}</app-ellipsis>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="发放时间" width="180"></el-table-column>
                <el-table-column width="250" label="状态(剩余次数/已核销次数/总次数)">
                    <template slot-scope="scope">
                        {{scope.row.is_use == 1 ? '已使用' : '未使用'}}({{scope.row.number -
                        scope.row.use_number}}/{{scope.row.use_number}}/{{scope.row.number}})
                    </template>
                </el-table-column>
                <el-table-column prop="store_name" label="门店"></el-table-column>
                <el-table-column label="核销日期" width="180">
                    <template slot-scope="scope">
                        <div flex="dir:top">
                            <span v-if="scope.row.clerked_at != '0000-00-00 00:00:00'">{{scope.row.clerked_at}}</span>
                            <span @click="openDialog(scope.row)" v-if="scope.row.is_show_history == 1"
                                  class="history-clerk">查看历史核销</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="80">
                    <template slot-scope="scope">
                        <el-button type="text" circle size="mini" @click="destroy(scope.row)">
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
    </el-card>

    <el-dialog title="核销记录" :visible.sync="historyForm.dialogVisible">
        <el-table v-loading="historyForm.listLoading" :data="historyForm.list" border>
            <el-table-column property="clerked_at" label="核销时间" width="180"></el-table-column>
            <el-table-column property="store_name" label="门店" width="150"></el-table-column>
            <el-table-column property="clerk_user" label="核销员" width="150"></el-table-column>
            <el-table-column property="use_number" label="核销次数"></el-table-column>
            <el-table-column property="surplus_number" label="剩余次数"></el-table-column>
        </el-table>
        <div style="margin: 20px 0;" flex="dir:left box:last">
            <el-pagination
                    @current-change="historyPagination"
                    :current-page="historyForm.page"
                    background layout="prev, pager, next, jumper"
                    :page-count="historyForm.pageCount">
            </el-pagination>

            <el-button type="primary" size="small" @click="historyForm.dialogVisible = false">确定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: [],
                listLoading: false,
                pageCount: 0,
                ids: [],
                send_date: '',
                clerk_date: '',
                status: 0,
                store_id: 0,
                card_name: '',
                store_name: '',
                clerk_id: '',
                user_name: '',
                key_code: '0',
                key_name: '',
                historyForm: {
                    dialogVisible: false,
                    list: [],
                    pageCount: 0,
                    userCardId: 0,
                    page: 1,
                    listLoading: false,
                },
                platform: {
                    wxapp: 'statics/img/mall/wx.png',
                    aliapp: 'statics/img/mall/ali.png',
                    bdapp: 'statics/img/mall/baidu.png',
                    ttapp: 'statics/img/mall/toutiao.png',
                    webapp: 'statics/img/mall/webapp.png',
                },
                id: '',
            };
        },
        methods: {
            selsChange(row) {
                this.ids = row;
            },

            handleCommand(row) {
                this.status = row;
                this.page = 1;
                this.getList();
            },

            search() {
                this.saveUserId(0)
                if (this.key_code == 0) {
                    this.store_name = this.key_name
                    this.user_name = ''
                    this.card_name = '';
                    this.id = '';
                } else if (this.key_code == 1) {
                    this.card_name = this.key_name
                    this.user_name = ''
                    this.store_name = ''
                    this.id = '';
                } else if (this.key_code == 2) {
                    this.user_name = this.key_name
                    this.store_name = ''
                    this.card_name = ''
                    this.id = '';
                } else if (this.key_code == 3) {
                    this.user_name = '';
                    this.store_name = '';
                    this.card_name = '';
                    this.id = this.key_name;
                }
                this.page = 1;
                this.getList();
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },

            batchDestroy: function () {
                if (this.ids.length == 0) {
                    this.$message.error('请先勾选商品');
                    return;
                }
                let ids = [];
                this.ids.forEach(v => {
                    ids.push(v.id);
                });
                this.$confirm('确认删除选中记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/user/card-batch-destroy'
                        },
                        data: {ids: ids},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg)
                            location.reload();
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },
            //删除
            destroy: function (column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/user/card-destroy'
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg)
                            this.getList();
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },

            getList() {
                this.listLoading = true;
                let userId = 0;
                let uId = getCookieValue('cardUserId')
                if (uId) {
                    userId = uId;
                }
                request({
                    params: {
                        r: 'mall/user/card',
                        page: this.page,
                        user_id: userId,
                        store_id: getQuery('store_id'),
                        clerk_id: getQuery('clerk_id'),
                        card_name: this.card_name,
                        store_name: this.store_name,
                        user_name: this.user_name,
                        status: this.status,
                        send_date: this.send_date,
                        clerk_date: this.clerk_date,
                        id: this.id,
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        this.form = e.data.data.list;
                        this.pageCount = e.data.data.pagination.page_count;
                        if (e.data.data.by_username) {
                            this.key_name = e.data.data.by_username
                            this.key_code = '2';
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            saveUserId(userId) {
                let Days = 1;
                let exp = new Date();
                exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
                document.cookie = "cardUserId=" + userId + ";expires=" + exp.toGMTString();
            },
            openDialog(row) {
                this.historyForm.dialogVisible = true;
                this.historyForm.userCardId = row.id;
                this.historyForm.page = 1;
                this.getHistoryList()
            },
            getHistoryList() {
                this.historyForm.listLoading = true;
                request({
                    params: {
                        r: 'mall/card/history-list',
                        user_card_id: this.historyForm.userCardId,
                        page: this.historyForm.page,
                    },
                }).then(e => {
                    this.historyForm.listLoading = false;
                    if (e.data.code === 0) {
                        this.historyForm.list = e.data.data.list;
                        this.historyForm.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            historyPagination(currentPage) {
                this.historyForm.page = currentPage;
                this.getHistoryList();
            },
        },

        mounted() {
            if (getQuery('user_id')) {
                this.saveUserId(getQuery('user_id'))
                navigateTo({
                    r: 'mall/user/card',
                });
            }
            this.getList();
        }
    })
</script>
