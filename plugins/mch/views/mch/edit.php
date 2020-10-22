<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span v-if="is_audit == 1" style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/mch/mall/mch/edit'})">入驻审核</span>
                    <span v-if="is_audit == 0" style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/mch/mall/mch/index'})">商户列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="is_audit == 1">审核</el-breadcrumb-item>
                <el-breadcrumb-item v-if="is_audit == 0">添加商户</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">
                <el-row>
                    <el-card shadow="never" style="margin-bottom: 20px">
                        <div slot="header">
                            <span>基本信息</span>
                        </div>
                        <el-col :span="12">
                            <el-form-item label="小程序用户" prop="user_id">
                                <el-input style="display: none;" v-model="ruleForm.user_id"></el-input>
                                <el-input disabled v-model="nickname">
                                    <template slot="append">
                                        <el-button @click="getUsers" type="primary">选择</el-button>
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="商户账号" prop="username">
                                <el-input :disabled="isNewEdit ? false : true"
                                          v-model="ruleForm.username">
                                </el-input>
                            </el-form-item>
                            <el-form-item v-if="!ruleForm.id" label="商户密码" prop="password">
                                <el-input type="password" v-model="ruleForm.password"></el-input>
                            </el-form-item>
                            <el-form-item label="联系人" prop="realname">
                                <el-input v-model="ruleForm.realname"></el-input>
                            </el-form-item>
                            <el-form-item label="联系电话" prop="mobile">
                                <el-input v-model="ruleForm.mobile"></el-input>
                            </el-form-item>
                            <el-form-item label="微信号" prop="wechat">
                                <el-input v-model="ruleForm.wechat"></el-input>
                            </el-form-item>
                            <el-form-item label="所售类目" prop="mch_common_cat_id">
                                <el-select v-model="ruleForm.mch_common_cat_id" placeholder="请选择">
                                    <el-option
                                            v-for="item in commonCats"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="是否开业" prop="status">
                                <el-switch
                                        v-model="ruleForm.status"
                                        active-value="1"
                                        inactive-value="0">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="好店推荐" prop="is_recommend">
                                <el-switch
                                        v-model="ruleForm.is_recommend"
                                        active-value="1"
                                        inactive-value="0">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="手续费(千分之)" prop="transfer_rate">
                                <label slot="label">手续费(千分之)
                                    <el-tooltip class="item" effect="dark"
                                                content="商户每笔订单交易金额扣除的手续费，请填写0~1000范围的整数"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input min="0" max="1000" type="number"
                                          v-model.number="ruleForm.transfer_rate"></el-input>
                            </el-form-item>
                            <el-form-item label="排序" prop="sort">
                                <label slot="label">排序
                                    <el-tooltip class="item" effect="dark"
                                                content="升序，数字越小排的越靠前"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input type="number" v-model.number="ruleForm.sort"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-card>
                    <el-card shadow="never" style="margin-bottom: 20px">
                        <div slot="header">
                            <span>商户信息</span>
                        </div>
                        <el-col :span="12">
                            <el-form-item label="店铺名称" prop="name">
                                <el-input v-model="ruleForm.name"></el-input>
                            </el-form-item>
                            <el-form-item label="店铺Logo" prop="logo">
                                <app-attachment :multiple="false" :max="1" v-model="ruleForm.logo">
                                    <el-tooltip class="item"
                                                effect="dark"
                                                content="建议尺寸:240 * 240"
                                                placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image mode="aspectFill" width='80px' height='80px' :src="ruleForm.logo">
                                </app-image>
                            </el-form-item>
                            <el-form-item label="店铺背景图" prop="bg_pic_url">
                                <app-attachment :multiple="false" :max="1" @selected="picUrl">
                                    <el-tooltip class="item"
                                                effect="dark"
                                                content="建议尺寸:750 * 200"
                                                placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image mode="aspectFill" width='80px' height='80px'
                                           :src="ruleForm.bg_pic_url && ruleForm.bg_pic_url.length ? ruleForm.bg_pic_url[0].pic_url : ''">
                                </app-image>
                            </el-form-item>
                            <el-form-item label="省市区" prop="district">
                                <el-cascader
                                        :options="district"
                                        :props="props"
                                        v-model="ruleForm.district">
                                </el-cascader>
                            </el-form-item>
                            <el-form-item label="店铺地址" prop="address">
                                <el-input v-model="ruleForm.address"></el-input>
                            </el-form-item>
                            <el-form-item label="客服电话" prop="service_mobile">
                                <el-input v-model="ruleForm.service_mobile"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-card>

                    <el-card v-if="is_audit == 1 && ruleForm.form_data.length > 0" shadow="never"
                             style="margin-bottom: 20px">
                        <div slot="header">
                            <span>自定义审核资料</span>
                        </div>
                        <el-col :span="12">
                            <template v-for="item in ruleForm.form_data">
                                <el-form-item v-if="item.key == 'text'" :label="item.label">
                                    <el-input disabled v-model="item.value" type="text"></el-input>
                                </el-form-item>
                                <el-form-item v-if="item.key == 'textarea'" :label="item.label">
                                    <el-input disabled v-model="item.value" type="textarea"></el-input>
                                </el-form-item>
                                <el-form-item v-if="item.key == 'date'" :label="item.label">
                                    <el-input disabled v-model="item.value" type="text"></el-input>
                                </el-form-item>
                                <el-form-item v-if="item.key == 'time'" :label="item.label">
                                    <el-input disabled v-model="item.value" type="text"></el-input>
                                </el-form-item>
                                <el-form-item v-if="item.key == 'radio'" :label="item.label">
                                    <el-radio disabled v-model="item.value" :label="item.value">{{item.value}}
                                    </el-radio>
                                </el-form-item>
                                <el-form-item v-if="item.key == 'checkbox'" :label="item.label">
                                    <el-checkbox disabled v-for="cItem in item.value" :checked='true'>{{cItem}}
                                    </el-checkbox>
                                </el-form-item>
                                <el-form-item v-if="item.key == 'img_upload'" :label="item.label">
                                    <template v-if="item.img_type == 2 || Array.isArray(item.value)">
                                        <div flex="dir:left">
                                            <div v-for="imgItem in item.value" @click="dialogImgShow(imgItem)">
                                                <app-image style="margin-right: 10px;"
                                                           mode="aspectFill"
                                                           width="100px"
                                                           height='100px'
                                                           :src="imgItem">
                                                </app-image>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div @click="dialogImgShow(item.value)">
                                            <app-image mode="aspectFill"
                                                       width="100px"
                                                       height='100px'
                                                       :src="item.value">
                                            </app-image>
                                        </div>
                                    </template>
                                </el-form-item>
                            </template>
                        </el-col>
                    </el-card>

                    <el-card v-if="is_audit == 1" shadow="never">
                        <div slot="header">
                            <span>审核信息</span>
                        </div>
                        <el-col :span="12">
                            <template v-if="is_audit == 1 && is_review == 0">
                                <el-form-item label="审核状态" prop="review_status">
                                    <el-tag v-if="ruleForm.review_status == 0" type="info">待审核</el-tag>
                                    <el-tag v-if="ruleForm.review_status == 1" type="success">审核通过</el-tag>
                                    <el-tag v-if="ruleForm.review_status == 2" type="danger">审核不通过</el-tag>
                                </el-form-item>
                                <el-form-item label="审核结果" prop="review_remark">
                                    {{ruleForm.review_remark}}
                                </el-form-item>
                            </template>
                            <template v-if="is_review == 1">
                                <el-form-item label="审核状态" prop="review_status">
                                    <el-radio v-model="ruleForm.review_status" label="1">审核通过</el-radio>
                                    <el-radio v-model="ruleForm.review_status" label="2">审核不通过</el-radio>
                                </el-form-item>
                                <el-form-item label="审核结果">
                                    <el-input v-model="ruleForm.review_remark" type="textarea" :row="5"></el-input>
                                </el-form-item>
                            </template>
                        </el-col>
                    </el-card>

                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
        <el-dialog title="用户列表" :visible.sync="dialogUsersVisible">
            <template>
                <el-input clearable style="width: 260px;margin-bottom: 20px;" size="small" :disabled="ruleForm.is_all"
                          v-model="ruleForm.keyword"
                          @keyup.enter.native="getUsers"
                          @clear="getUsers"
                          placeholder="输入用户ID、昵称搜索">
                    <template slot="append">
                        <el-button size="small" @click="getUsers">搜索</el-button>
                    </template>
                </el-input>
                <el-table
                        v-loading="tableLoading"
                        :data="users"
                        tooltip-effect="dark"
                        style="width: 100%">
                    <el-table-column
                            prop="id"
                            label="ID"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            label="头像">
                        <template slot-scope="scope">
                            <app-image mode="aspectFill" :src="scope.row.userInfo.avatar"></app-image>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="nickname"
                            label="昵称">
                        <template slot-scope="scope">
                            <div flex="dir:left">
                                <img src="statics/img/mall/ali.png" v-if="scope.row.userInfo.platform == 'aliapp'" alt="">
                                <img src="statics/img/mall/wx.png" v-else-if="scope.row.userInfo.platform == 'wxapp'" alt="">
                                <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.userInfo.platform == 'ttapp'" alt="">
                                <img src="statics/img/mall/baidu.png" v-else-if="scope.row.userInfo.platform == 'bdapp'" alt="">
                                <span style="margin-left: 10px;">{{scope.row.nickname}}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="操作"
                            width="120">
                        <template slot-scope="scope">
                            <el-button @click="selectUser(scope.row)" type="primary" plain size="mini">添加</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </el-dialog>
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img :src="click_img" class="click-img" alt="">
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    user_id: 0,
                    status: '0',
                    is_recommend: '0',
                    realname: '',
                    review_status: '',
                    review_remark: '',
                    wechat: '',
                    mobile: '',
                    address: '',
                    mch_common_cat_id: '',
                    name: '',
                    logo: '',
                    bg_pic_url: [],
                    transfer_rate: 0,
                    account_money: 0,
                    sort: 100,
                    longitude: '',
                    latitude: '',
                    service_mobile: '',
                    district: [],
                    form_data: [],
                },
                rules: {
                    username: [
                        {required: true, message: '商户账号', trigger: 'change'},
                    ],
                    password: [
                        {required: true, message: '商户密码', trigger: 'change'},
                    ],
                    realname: [
                        {required: true, message: '联系人', trigger: 'change'},
                    ],
                    mobile: [
                        {required: true, message: '联系人电话', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '店铺名称', trigger: 'change'},
                    ],
                    logo: [
                        {required: true, message: '店铺Logo', trigger: 'change'},
                    ],
                    bg_pic_url: [
                        {required: true, message: '店铺背景图', trigger: 'change'},
                    ],
                    address: [
                        {required: true, message: '店铺详细地址', trigger: 'change'},
                    ],
                    district: [
                        {required: true, message: '店铺省市区', trigger: 'change'},
                    ],
                    transfer_rate: [
                        {required: true, message: '店铺手续费', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '店铺排序', trigger: 'change'},
                    ],
                    service_mobile: [
                        {required: true, message: '客服电话', trigger: 'change'},
                    ],
                    mch_common_cat_id: [
                        {required: true, message: '所售类目', trigger: 'change'},
                    ],
                    is_recommend: [
                        {required: true, message: '好店推荐', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '是否开业', trigger: 'change'},
                    ],
                    review_status: [
                        {required: true, message: '审核状态', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                commonCats: [],
                district: [],
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                dialogUsersVisible: false,
                users: [],
                nickname: '',//用户展示的用户名
                is_review: 0,
                is_audit: 0,//审核状态是否显示,添加商户时不显示
                navigateToUrl: 'plugin/mch/mall/mch/index',
                isNewEdit: 1,
                dialogImg: false,
                click_img: '',
            };
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.detail;
                        this.nickname = this.ruleForm.user.nickname;
                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mch/mall/mch/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                                is_review: self.is_review,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: self.navigateToUrl
                                })
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
            // 获取类目列表
            getCommonCatList() {
                request({
                    params: {
                        r: 'plugin/mch/mall/common-cat/all-list',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.commonCats = e.data.data.list;
                    }
                }).catch(e => {
                });
            },
            addShareTitle() {
                let self = this;
                if (self.shareTitle) {
                    if (self.ruleForm.share_title.indexOf(self.shareTitle) === -1) {
                        self.ruleForm.share_title.push(self.shareTitle);
                        self.shareTitle = '';
                    }
                }
            },
            deleteShareTitle(index) {
                this.ruleForm.share_title.splice(index);
            },
            itemChecked(type) {
                if (type === 1) {
                    this.ruleForm.sponsor_num = this.isSponsorNum ? -1 : 0
                } else if (type === 2) {
                    this.ruleForm.help_num = this.isHelpNum ? -1 : 0
                } else if (type === 3) {
                    this.ruleForm.sponsor_count = this.isSponsorCount ? -1 : 0
                } else {
                }
            },
            getUsers() {
                let self = this;
                self.btnLoading = true;
                if (!self.ruleForm.is_all) {
                    self.dialogUsersVisible = true;
                    self.tableLoading = true;
                }
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/search-user',
                        keyword: self.ruleForm.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.btnLoading = false;
                    self.tableLoading = false;
                    if (e.data.code == 0) {
                        self.users = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                    self.tableLoading = false;
                });
            },
            selectUser(row) {
                this.ruleForm.user_id = row.id;
                this.nickname = row.nickname;
                this.dialogUsersVisible = false;
            },
            // 店铺背景图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    self.ruleForm.bg_pic_url = [];
                    e.forEach(function (item, index) {
                        self.ruleForm.bg_pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            // 获取省市区列表
            getDistrict() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            dialogImgShow(imgUrl) {
                this.dialogImg = true;
                this.click_img = imgUrl;
            }
        },
        mounted: function () {
            if (getQuery('is_review')) {
                this.is_review = getQuery('is_review');
                this.navigateToUrl = 'plugin/mch/mall/mch/review';
            }
            if (getQuery('id')) {
                this.getDetail();
                this.isNewEdit = 0;
            }
            this.is_audit = getQuery('id') ? 1 : 0;
            this.getCommonCatList();
            this.getDistrict();
        }
    });
</script>
