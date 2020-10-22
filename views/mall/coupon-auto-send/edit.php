<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 30%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .user-item {
        border: 1px #eeeeee solid;
        padding: 20px;
        margin-right: 20px;
        margin-bottom: 20px;
        width: 120px;
        height: 120px;
        position: relative;
    }

    .user-item .avatar {
        width: 50px;
        height: 50px;
    }

    .user-item .nickname {
        display: inline-block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }

    .user-item .close {
        position: absolute;
        right: -10px;
        top: -10px;
        padding: 0;
        border-radius: 100px;
        width: 20px;
        height: 20px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/coupon-auto-send/index'})">自动发放优惠券</span></el-breadcrumb-item>
                <el-breadcrumb-item>自动发放优惠券编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="12rem" v-loading="loading" :rules="FormRules" ref="form">
                <el-form-item label="触发事件" prop="event">
                  <el-select style="width: 215px" size='small' v-model="form.event" placeholder="请选择">
                    <el-option
                      v-for="item in event"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value">
                    </el-option>
                  </el-select>
                </el-form-item>
                <el-form-item label="发放的优惠券" prop="coupon_id">
                  <el-select style="width: 215px" size='small' v-model="form.coupon_id" placeholder="请选择">
                    <el-option
                      v-for="item in coupon_list"
                      :key="item.id"
                      :label="item.name"
                      :value="item.id">
                    </el-option>
                  </el-select>
                </el-form-item>
                <el-form-item label="" prop="send_count" v-if="form.event < 3">
                    <template slot='label'>
                        <span>发放次数</span>
                        <el-tooltip effect="dark" content="每个用户可发放次数；如不限制发放次数，请填写0"
                                placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input size="small" style="width: 215px" type="number" :disabled="form.send_count == 0"
                              v-model.number="form.send_count" autocomplete="off"></el-input>
                    <el-checkbox v-model="form.send_count" :true-label="0" :false-label="1">无限制</el-checkbox>
                </el-form-item>
                <el-form-item label="领取人" v-if="form.event != 3">
                    <el-radio-group v-model="form.type">
                        <el-radio :label="0">所有用户</el-radio>
                        <el-radio :label="1">
                            <span>指定用户</span>
                            <el-button  v-if="form.type == 1" type="text" @click="selectUser">选择用户</el-button>
                        </el-radio>
                    </el-radio-group>
                    <el-dialog title="选择用户" :visible.sync="dialog.show" width="30%">
                        <el-input v-model="dialog.keyword" placeholder="输入用户ID、昵称搜索"
                                  size="small" @keyup.enter.native="selectUser">
                            <template slot="append">
                                <el-button @click="selectUser">搜索</el-button>
                            </template>
                        </el-input>
                        <el-table :data="dialog.list" v-loading="dialog.loading"
                                  @selection-change="handleSelectionChange">
                            <el-table-column type="selection"></el-table-column>
                            <el-table-column prop="user_id" label="ID"></el-table-column>
                            <el-table-column prop="platform" label="所在平台">
                                <template slot-scope="scope">
                                    <img class="platform-img" v-if="scope.row.platform == 'wxapp'"
                                         src="statics/img/mall/wx.png" alt="">
                                    <img class="platform-img" v-if="scope.row.platform == 'aliapp'"
                                         src="statics/img/mall/ali.png" alt="">
                                    <img class="platform-img" v-if="scope.row.platform == 'bdapp'"
                                         src="statics/img/mall/baidu.png" alt="">
                                    <img class="platform-img" v-if="scope.row.platform == 'ttapp'"
                                         src="statics/img/mall/toutiao.png" alt="">
                                </template>
                            </el-table-column>
                            <el-table-column prop="nickname" label="昵称"></el-table-column>
                        </el-table>
                        <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                                       :page-count="dialog.pageCount" :current-page="dialog.currentPage">
                        </el-pagination>
                        <div slot="footer" class="dialog-footer">
                            <el-button @click="dialog.show = false">取 消</el-button>
                            <el-button type="primary" @click="selectUserSubmit">确 定</el-button>
                        </div>
                    </el-dialog>
                    <div v-if="form.type == 1" flex="dir:left" style="flex-wrap: wrap">
                        <div class="user-item" v-for="(item, index) in form.user_list" flex="dir:top cross:center">
                            <img class="avatar" :src="item.avatar">
                            <div class="nickname">{{item.nickname}}</div>
                            <el-button @click="deleteUser(index)" circle class="close"
                                       type="danger"
                                       icon="el-icon-close"></el-button>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" size="small" :loading=btnLoading @click="onSubmit">提交</el-button>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {event:1,coupon_id:null, user_list: [], type: 0},
                coupon_list:{},
                loading: false,
                btnLoading: false,
                FormRules: {
                    event: [
                        { required: true },
                    ],
                    coupon_id: [
                        { required: true }
                    ],
                    send_count: [
                        { required: true, message: '最多发放次数不能为空', trigger: 'blur' }
                    ]},
                event:[{
                      value: 1,
                      label: '分享'
                    }, {
                      value: 2,
                      label: '购买并付款'
                    }, {
                      value: 3,
                      label: '新人领券'
                    }],
                dialog: {
                    show: false,
                    list:[],
                    loading: false,
                    page: 1,
                    pageCount: 0,
                    currentPage: null,
                    keyword: '',
                    waitSelectUsers: [],
                }
            };
        },
        methods: {
            // 提交
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign(this.form);
                        request({
                            params: {
                                r: 'mall/coupon-auto-send/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                  message: e.data.msg,
                                  type: 'success'
                                });
                                setTimeout(function(){
                                    navigateTo({ r: 'mall/coupon-auto-send/index' });
                                },300);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            // 获取数据
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/coupon-auto-send/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.coupon_list = e.data.data.coupon_list;
                        this.form.coupon_id = this.coupon_list[0].id;
                        if (e.data.data.list.id > 0) {
                            this.form = e.data.data.list;
                        }
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                }).catch(e => {
                    this.loading = false;
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                });
            },
            selectUser() {
                this.dialog.show = true;
                this.dialog.loading = true;
                request({
                    params: {
                        r: 'mall/user/index',
                        page: this.dialog.page,
                        keyword: this.dialog.keyword,
                    }
                }).then(response => {
                    this.dialog.loading = false;
                    if (response.data.code === 0) {
                        this.dialog.waitSelectUsers = [];
                        this.dialog.list = response.data.data.list;
                        this.dialog.pageCount = response.data.data.pagination.page_count;
                        this.dialog.currentPage = response.data.data.pagination.current_page;
                    } else {
                        this.$alert(response.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                });
            },
            pagination(currentPage) {
                this.dialog.page = currentPage;
                this.selectUser();
            },
            // 多选
            handleSelectionChange(val) {
                this.dialog.waitSelectUsers = val;
            },
            selectUserSubmit() {
                if (!this.form.user_list) {
                    this.form.user_list = [];
                }
                this.form.user_list = this.form.user_list.concat(this.dialog.waitSelectUsers);
                this.dialog.show = false;
            },
            deleteUser(index) {
                this.form.user_list.splice(index, 1);
            }
        },
        created() {
            this.getList();
        }
    })
</script>
