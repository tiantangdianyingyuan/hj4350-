<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        position: relative;
        z-index: 1
    }

    .input-item {
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

    .table-body .el-button {
        padding: 0!important;
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
            <span>步数挑战</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="addOne">添加挑战
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear='search'>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="title" width='220' label="名称"></el-table-column>
                <el-table-column width='220' prop="begin_at" label="活动时间">
                    <template slot-scope="scope">
                        <div>{{scope.row.begin_at}}至{{scope.row.end_at}}</div>
                    </template>
                </el-table-column>
                <el-table-column align="center" prop="people_num" label="参与人数"></el-table-column>
                <el-table-column align="center" prop="bail_currency" label="缴纳金"></el-table-column>
                <el-table-column align="center" prop="currency" label="奖金池">
                    <template slot-scope="scope">
                        <div v-if="scope.row.currency_num > 0">{{+scope.row.currency + +scope.row.currency_num}}</div>
                        <div v-else>{{scope.row.currency}}</div>
                    </template>
                </el-table-column>                
                </el-table-column>
                <el-table-column align="center" prop="step_num" label="挑战步数"></el-table-column>
                <el-table-column align="center" width='120' prop="status" label="状态">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.type == 0 && scope.row.activity_status == 'expired'" content="已过期" placement="top">
                            <img src="statics/img/mall/expired.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.type == 0" :content="scope.row.activity_status == 'no_start' ? '未开始' : '进行中'" placement="top">
                            <img v-if="scope.row.activity_status == 'no_start'" src="statics/img/mall/unstart.png" alt="">
                            <img v-else src="statics/img/mall/ing.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.type == 2" content="已解散" placement="top">
                            <img src="statics/img/mall/disband.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else content="已结算" placement="top">
                            <img src="statics/img/mall/already.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column align="center" width='120' prop="status" label="开关">
                    <template slot-scope="scope">
                        <el-switch :disabled="scope.row.type != 0" v-model="scope.row.status" active-value="1" inactive-value="0" @change="handleStatus(scope.row)"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column width='220' fixed="right" label="操作">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="about(1,scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="参与详情" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" v-if="scope.row.people_num == 0 && scope.row.type == 0" @click="edit(scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" v-if="scope.row.type == 0 && scope.row.people_num > 0" @click="disband(scope.row)">
                            <el-tooltip class="item" effect="dark" content="解散" placement="top">
                                <img src="statics/img/mall/toDisband.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" v-if="scope.row.type != 0 || scope.row.people_num == 0" @click="handleDel(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
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
    <el-dialog :title="title" :visible.sync="add" width="30%">
        <el-form :model="form" label-width="100px" style="width: 90%">
            <el-form-item label="活动名称">
                <el-input size="small" v-model="form.title" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="活动时间">
                <el-date-picker
                    style="width: 100%;"
                    v-model="time"
                    size="small"
                    type="daterange"
                    range-separator="至"
                    start-placeholder="开始日期"
                    value-format="yyyy-MM-dd"
                    end-placeholder="结束日期">
                </el-date-picker>
            </el-form-item>
            <el-form-item label="挑战步数">
                <el-input size="small" v-model="form.step_num" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="缴纳金">
                <el-input size="small" v-model="form.bail_currency" autocomplete="off">
                    <template slot="append">活力币</template>
                </el-input>
            </el-form-item>
            <el-form-item label="初始奖金池">
                <el-input size="small" v-model="form.currency" autocomplete="off">
                    <template slot="append">活力币</template>
                </el-input>
            </el-form-item>
            <el-form-item label="状态">
                <el-switch v-model="form.status" active-value="1" inactive-value="0"></el-switch>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="add = false">取 消</el-button>
            <el-button size="small" type="primary" @click="submit">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="参与详情" :visible.sync="join" width="60%">
        <el-table :data="detail" v-loading="detail_loading">
            <el-table-column prop="id" label="ID" width="100"></el-table-column>
            <el-table-column prop="avatar" label="用户信息">
                <template slot-scope="scope">
                    <app-image mode="" width="40px" height="40px" :src="scope.row.avatar" style="float: left;margin-right: 10px"></app-image>
                    <div style="line-height: 40px;height: 40px;">{{scope.row.step.user.nickname}}</div>
                </template>
            </el-table-column>
            <el-table-column align="center" width='100' prop="from" label="所属平台">
                <template slot-scope="scope">
                    <div v-if="scope.row.platform == 'wxapp'">微信</div>
                    <div v-if="scope.row.platform == 'aliapp'">支付宝</div>
                </template>
            </el-table-column>
            <el-table-column align="center" width='150' prop="reward_currency" label="收支情况(活力币)">
                <template slot-scope="scope">
                    <div style="font-size: 16px;color: #67C23A" v-if="scope.row.reward_currency > -1"><span v-if="scope.row.reward_currency > 0">+</span>{{scope.row.reward_currency}}</div style="font-size: 16px;color: #ff4544">
                    <div style="font-size: 16px;color: #ff4544" v-else><span>-</span>{{scope.row.reward_currency}}</div style="font-size: 16px;color: #ff4544">
                </template>
            </el-table-column>
            <el-table-column align="center" width='150' prop="total_num" label="当前步数">
                <template slot-scope="scope">
                    {{scope.row.total_num}}步
                </template>
            </el-table-column>
            <el-table-column align="center" width='250' prop="created_at" label="创建时间">
            </el-table-column>
        </el-table>
        <div flex="box:last cross:center" style="margin-top: 10px">
            <div style="visibility: hidden">
                <el-button plain type="primary" size="small">批量操作1</el-button>
                <el-button plain type="primary" size="small">批量操作2</el-button>
            </div>
            <div>
                <el-pagination v-if="joinPagination"
                               :page-size="joinPagination.pageSize" style="display: inline-block;float: right;"
                               background @current-change="joinPageChange" layout="prev, pager, next, jumper"
                               :total="joinPagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-dialog>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            detail_loading: false,
            add: false,
            join: false,
            time: [],
            list: [],
            id: 82,
            keyword: null,
            pagination: {},
            joinPagination: null,
            detail:[],
            title: '创建活动',
            form:{
                title:'',
                step_num: '',
                time: [],
                bail_currency: '',
                currency: '',
                status: '0'
            }
        };
    },

    methods: {
        //分页
        pageChange(page) {
            this.getList(page)
        },
        joinPageChange(page) {
            this.about(page,this.id);
        },
        // 新增
        addOne() {
            this.add = true;
            this.title = '创建活动';
            this.time = [];
            this.form = {
                title:'',
                step_num: '',
                time: [],
                bail_currency: '',
                currency: '',
                status: '0'
            }
        },
        search() {
            this.getList(1);
        },
        // 编辑
        edit(row) {
            this.time = [];
            this.time[0] = row.begin_at;
            this.time[1] = row.end_at;
            this.form = row;
            this.title = '编辑活动';
            this.add = true;
        },

        // 查看详情
        about(page,id) {
            let that = this;
            that.id = id;
            that.join = true;
            that.detail_loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/activity/partake-list',
                    page: page,
                    id: this.id
                },
                method: 'get',
            }).then(e => {
                that.detail_loading = false;
                if (e.data.code === 0) {
                    that.detail = e.data.data.list;
                    that.joinPagination = e.data.data.pagination
                } else {
                    that.$message.error(e.data.msg);
                }
            }).catch(e => {
                that.detail_loading = false;
            });
        },
        // 编辑保存
        submit() {
            let that = this;
            let para = that.form;
            that.form.begin_at = that.time[0];
            that.form.end_at = that.time[1];
            that.detail_loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/activity/edit',
                },
                data: para,
                method: 'post',
            }).then(e => {
                if (e.data.code === 0) {
                    that.$message({
                        message: e.data.msg,
                        type: 'success'
                    });
                    setTimeout(function(){
                        that.add = false;
                        that.getList(1);
                    },500);
                } else {
                    that.$message.error(e.data.msg);
                }
            }).catch(e => {
                that.detail_loading = false;
            });
        },
        // 删除
        handleDel: function(row) {
            let that = this;
            that.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                let para = { id: row.id};
                request({
                    params: {
                        r: 'plugin/step/mall/activity/destroy'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                    const h = that.$createElement;
                    that.$message({
                        message: '删除成功',
                        type: 'success'
                    });
                    setTimeout(function(){
                        that.getList(1);
                    },300);
                }else{
                    that.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                }
                }).catch(e => {
                    that.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });
            })
        },
        // 解散活动
        disband: function(row) {
            let that = this;
            that.$confirm('是否解散？(将返还缴纳金)', '提示', {
                type: 'warning'
            }).then(() => {
                let para = { id: row.id};
                request({
                    params: {
                        r: 'plugin/step/mall/activity/disband'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                    const h = that.$createElement;
                    that.$message({
                        message: '解散成功',
                        type: 'success'
                    });
                    setTimeout(function(){
                        that.getList(1);
                    },300);
                }else{
                    that.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                }
                }).catch(e => {
                    that.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });
            })
        },
        // 切换状态
        handleStatus(row) {
            let that = this;
            that.loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/activity/edit-status',
                },
                data: {
                    id: row.id,
                    status: row.status,
                },
                method: 'post',
            }).then(e => {
                that.loading = false;
                if (e.data.code === 0) {
                    that.$message({
                        message: e.data.msg,
                        type: 'success'
                    });
                    that.getList();
                } else {
                    that.getList();
                    that.$message.error(e.data.msg);
                }
            }).catch(e => {
                that.loading = false;
            });
        },
        // 获取列表
        getList(page) {
            let self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/activity',
                    page: page,
                    keyword: self.keyword
                },
                method: 'get',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                    self.list = e.data.data.list;
                    self.pagination = e.data.data.pagination;
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },
    },
    created() {
        this.getList(1);
    }
})
</script>