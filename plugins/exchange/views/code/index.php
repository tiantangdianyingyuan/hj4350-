
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

    .table-info .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin-left: 15px;
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


    .user-list{
        margin-top: 10px;
        width: 100%;
    }

    .user-item .el-checkbox-button__inner{
        border: 1px solid #e2e2e2;
        height: 125px;
        width: 120px;
        padding-top: 15px;
        text-align: center;
        margin: 0 20px 20px 0;
        cursor: pointer;
        border-radius: 0!important;
    }

    .user-item.active{
        background-color: #50A0E4;
        color: #fff;
    }

    .user-list .avatar{
        height: 60px;
        width: 60px;
        border-radius: 30px;
    }

    .username{
        margin-top: 10px;
        font-size: 13px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        height: 20px;
    }

    .el-icon-warning:before {
        content: "\e7a3"
    }
    .no-label .el-checkbox {
        display: none;
    }
    .table-page {
        width: 900px;
        padding: 10px 0;
        text-align: right;
        border: 1px solid #EBEEF5;
        margin-top: -1px;
    }

    .el-radio__label {
        display: none;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>兑换中心</span>
            </div>
        </div>
        <div class="table-body">
            <div flex="wrap:wrap cross:center" style="margin-bottom: 15px;">
                <div>兑换</div>
                <div class="input-item">
                    <el-input maxlength="12" @keyup.enter.native="toSearch" size="small" placeholder="请输入兑换码" v-model="keyword" clearable @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <el-form v-show="form.rewards.length > 0" @submit.native.prevent size="small" ref="form" :model="form" label-width="80px">
                <el-form-item label="兑换用户" prop="user">
                    <div flex="dir:left cross:center">
                        <div v-if="form.user && form.user.id > 0" flex="dir:left cross:center" style="margin-right: 15px;">
                            <app-image mode="aspectFill" :src="form.user.avatar"></app-image>
                            <div style="margin-left: 10px;">
                                <app-ellipsis :line="1">{{form.user.nickname}}</app-ellipsis>
                            </div>
                        </div>
                        <el-button size="small" @click="dialogVisible=true">{{form.user && form.user.id > 0 ? '修改用户': '选择用户'}}</el-button>
                    </div>
                </el-form-item>
                <el-form-item label="姓名" prop="name">
                    <div style="width: 370px">
                        <el-input v-model="form.name"></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="联系电话" prop="mobile">
                    <div style="width: 370px">
                        <el-input v-model="form.mobile"></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="兑换奖励" prop="rewards">
                    <el-table :header-cell-style="{'background-color': '#F6F7FA','margin-left':'5px'}"  class="table-info" :data="rewards" ref="multipleTable" border style="width: 800px">
                        <el-table-column v-if="form.mode === 1" label-class-name="no-label" width="55">
                            <template slot-scope="props">
                                <el-radio v-model="token" :label="props.row.token"></el-radio>
                            </template>
                        </el-table-column>
                        <el-table-column prop="name" label="类型" width="120"></el-table-column>
                        <el-table-column label="内容">
                            <template slot-scope="props">
                                <div v-if="props.row.type == 'goods'">
                                    <div flex="dir:left cross:center">
                                        <el-image style="height: 50px;width: 50px;margin-right: 10px;" :src="props.row.goods_info.cover_pic"></el-image>
                                        <div>
                                            <app-ellipsis :line="1">{{props.row.goods_info.name}}</app-ellipsis>
                                            <app-ellipsis :line="1">
                                                <span style="color: #999999;">{{props.row.goods_info.attr_str}}</span>
                                            </app-ellipsis>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="props.row.type == 'coupon'">
                                    <div>
                                        <app-ellipsis :line="1">{{props.row.coupon_info.name}}</app-ellipsis>
                                    </div>
                                </div>
                                <div v-if="props.row.type == 'card'">
                                    <div>
                                        <app-ellipsis :line="1">{{props.row.card_info.name}}</app-ellipsis>
                                    </div>
                                </div>
                                <div style="width: 250px" v-if="props.row.type == 'integral'">
                                        {{props.row.integral_num}}积分
                                </div>
                                <div style="width: 250px" v-if="props.row.type == 'balance'">
                                    ￥{{props.row.balance}}
                                </div>
                                <div v-if="props.row.type == 'svip'">
                                    <app-ellipsis :line="1">{{props.row.svip_info.name}}</app-ellipsis>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="数量" width="120">
                            <template slot-scope="scope">
                                {{scope.row.coupon_num}}
                                {{scope.row.goods_num}}
                                {{scope.row.card_num}}
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="width: 800px" v-if="form.rewards.length > 0" class="table-page">
                        <el-pagination @current-change="tablePage" :current-page="currentPage" :page-size="6" background layout="prev, pager, next" :total="form.rewards.length" :page-count="form.rewards.length > 12 ? 3 : form.rewards.length > 6 ? 2 : 1"></el-pagination>
                    </div>
                </el-form-item>
            </el-form>
            <div v-if="msg" flex="dir:left cross:center" style="position: relative;padding: 20px 0;">
                <div class="el-message-box__status el-icon-warning"></div>
                <div style="margin-left: 30px;">
                    <div>{{msg}}</div>
                    <div v-if="other" style="color: #999;font-size: 13px;margin-top: 5px;">{{other}}</div>
                </div>
            </div>
        </div>
        <el-button v-if="form.rewards.length > 0" style="margin-top: 20px;" :loading="btnLoading" class="button-item" type="primary" @click="submit" size="small">确认兑换</el-button>
    </el-card>
    <el-dialog :title="form.user && form.user.id > 0 ? '修改用户': '选择用户'" :visible.sync="dialogVisible" width="980px">
        <el-form @submit.native.prevent size="small" ref="user" label-width="90px">
            <el-form-item prop="list">
                <template slot='label'>
                    <span>兑换用户</span>
                </template>
                    <el-input @keyup.enter.native="search" size="small" v-model="userKeyword" autocomplete="off" placeholder="昵称/ID/手机号" style="width: 20%"></el-input>
                <el-button size="small" :loading=foundLoading @click="search">查找用户</el-button>
                <el-checkbox-group @change="chooseUser" class="user-list" v-model="id" size="medium">
                    <el-checkbox-button class="user-item" v-for="item in user.list" :label="item.id" :key="item.id">
                        <img class="avatar" :src="item.avatar" alt="">
                        <div class="username">{{ item.nickname }}</div>
                    </el-checkbox-button>
                </el-checkbox-group>
                <div v-if="isSearch && user.list.length == 0" flex="main:center cross:bottom" style="height: 80px;color: #999">暂无用户</div>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogVisible = false;id=[]">取 消</el-button>
            <el-button size="small" type="primary" @click="submitUser">确 定</el-button>
        </span>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    rewards: [],
                    mode: 0,
                    name: '',
                    mobile: '',
                    user: {},
                    id: 0
                },
                rewards: [],
                user: {
                    list: []
                },
                id:[],
                token: '',
                keyword: '',
                code: '',
                other: '',
                pagination: {
                    page_count: 0
                },
                activeName: '-1',
                userKeyword: '',
                msg: '',
                currentPage: 1,
                isSearch: false,
                loading: false,
                dialogVisible: false,
                listLoading: false,
                btnLoading: false,
                foundLoading: false
            };
        },
        methods: {
            tablePage(page) {
                this.currentPage = page;
                this.rewards = this.form.rewards.slice((page-1)*6,(page-1)*6+6);
            },
            submitUser() {
                for(let item of this.user.list) {
                    if(item.id == this.id[0]) {
                        this.form.user = item;
                    }
                }
                this.dialogVisible = false;
                this.isSearch = false;
            },
            submit() {
                if(this.form.mode == 1 && !this.token) {
                    this.$message.error('请选择兑换奖励');
                    return false
                }
                for(let item of this.form.rewards) {
                    if(this.form.mode == 0 && item.type != 'goods' && !this.form.user.id) {
                        this.$message.error('请选择用户');
                        return false
                    }
                    if(this.form.mode == 1 && this.token == item.token && item.type != 'goods' && !this.form.user.id) {
                        this.$message.error('请选择用户');
                        return false
                    }
                }
                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/exchange/mall/record/unite'
                    },
                    data: {
                        code: this.code,
                        user_id: this.form.user.id ? this.form.user.id : 0,
                        name: this.form.name,
                        token: this.form.mode == 1 ? this.token : '',
                        mobile: this.form.mobile,
                    },
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 0) {
                        this.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        this.code = '';
                        this.keyword = '';
                        this.form.user = {};
                        this.form.name = '';
                        this.form.mobile = '';
                        this.form.rewards = [];
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            chooseUser(e) {
                this.id = [e.pop()]
            },
            toSearch() {
                let that = this;
                that.form.rewards = [];
                that.msg = '';
                if(!that.keyword) {
                    return false
                }
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/exchange/mall/record/show-info',
                        code: that.keyword,
                    },
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.code = that.keyword;
                        that.form.rewards = e.data.list.rewards;
                        that.form.mode = e.data.list.mode;
                        that.rewards = this.form.rewards.slice((this.currentPage-1)*6,(this.currentPage-1)*6+6);
                        if(that.form.mode == 0) {
                            that.$nextTick(()=>{
                                that.form.rewards.forEach(row => {
                                    that.$refs.multipleTable.toggleRowSelection(row,true);
                                });
                            })
                        }
                    }else {
                        that.msg = e.data.msg;
                        that.other = '';
                        if(e.data.msg == '该兑换码未到使用时间!') {
                            that.other = e.data.data.valid_start_time + ' - ' + e.data.data.valid_end_time + ' 可用'
                        }
                    }
                })
            },
            search(){
                this.foundLoading = true;
                request({
                    params: {
                        r: 'mall/coupon/search-user',
                        keyword: this.userKeyword,
                    },
                }).then(e => {
                    this.isSearch = true;
                    this.foundLoading = false;
                    if (e.data.code == 0) {     
                        this.user.list = e.data.data.list;
                    }
                }).catch(e => {
                    this.foundLoading = false;
                });                
            },
        },
        mounted: function () {
        }
    });
</script>