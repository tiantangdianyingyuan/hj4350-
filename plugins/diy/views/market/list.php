<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/1/2
 * Time: 10:54
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-dialog-select')
?>
<style>
    .list {
        width: 207.5px;
        height: 370px;
        padding: 20px;
        padding-right: 0;
        padding-top: 0;
    }
    .item {
        width: 187.5px;
        position: relative;
        top: 0;
        border: 1px solid #e2e2e2;
        border-radius: 5px;
        height: 350px;
    }

    .item.more {
        height: 340px;
    }

    .list:hover .item {
        top: -10px;
        box-shadow: 0 4px 4px 4px #ECECEC;
    }

    .info {
        padding: 0 10px;
        width: 100%;
        background-color: #fff;
    }

    .item-name {
        font-size: 16px;
        margin: 5px 0;
        width: 80%;
    }

    .info-about {
        color: rgb(144, 147, 153);
        margin-left: 5px;
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .show-img {
        width: 187.5px;
        height: 270px;
        overflow: hidden;
        background-size: cover;
        background-position: 0 0;
        background-repeat: no-repeat;
    }

    .price {
        color: #ff4544;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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

    .dialog-img {
        background-color: rgba(0, 0, 0, .4);
        position: absolute;
        left: 0;
        top: 0;
        z-index: 10;
        width: 187.5px;
        height: 270px;
        display: none;
    }

    .dialog-img .choose-btn {
        cursor: pointer;
        border-radius: 6px;
        height: 40px;
        line-height: 38px;
        width: 120px;
        margin: 10px auto;
        text-align: center;
        border: 1px solid #fff;
        color: #fff;
        font-size: 16px;
    }

    .choose-btn.use {
        border: 1px solid #3399ff;
        background-color: #3399ff;
    }

    .item:hover .dialog-img {
        display: flex;
    }

    .loading-dialog .el-dialog {
        margin-top: 0 !important;
        min-width: 350px;
    }

    .loading-dialog .el-dialog__header {
        padding: 15px;
    }

    .loading-dialog .el-dialog__body {
        padding: 20px 20px 35px;
    }

    .choose-btn-group {
        margin-top: 20px;
    }

    .choose-btn-group .choose-btn {
        cursor: pointer;
        display: inline-block;
        border: 1px solid #E2E2E2;
        border-radius: 3px;
        margin: 5px;
        padding: 0 5px;
        width: 160px;
        height: 300px;
    }

    .choose-btn-group .choose-btn .el-radio {
        height: 40px;
        line-height: 40px;
        margin: 0;
    }

    .choose-btn-group .choose-btn:first-child {
        margin-right: 50px;
    }

    .choose-btn-group .choose-btn.active {
        border: 1px solid #409eff;
    }

</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header" flex style="justify-content: space-between;align-items: center">
            <div>
                <span>模板市场</span>
            </div>
<!--                        <el-button type="primary" size="small" @click="issue">发布</el-button>-->
        </div>
        <div style="background-color: #fff;padding: 20px 0;">
            <div style="padding: 0 20px;">
                <el-form size="small" :inline="true">
                    <el-input style="display: none"></el-input>
                    <el-form-item>
                        <el-select @change="onSubmit" style="width: 150px" v-model="status">
                            <el-option value="" label="全部模板"></el-option>
                            <el-option :value="1" label="已拥有模板"></el-option>
                            <el-option :value="0" label="未拥有模板"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="false">
                        <div class="input-item">
                            <el-input @keyup.enter.native="onSubmit" size="small" placeholder="请输入模板名称"
                                      v-model="keyword" clearable @clear="onSubmit">
                                <el-button slot="append" icon="el-icon-search" @click="onSubmit"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <div flex="dir:left" style="flex-wrap: wrap" ref="list" v-if="list.length > 0">
                <div class="list" v-for="(item,index) in list">
                    <div :class="[item.is_show == 0 ? 'item more':'item']">
                        <div class="dialog-img" flex="dir:top cross:center main:center">
                            <div @click="toUse(item)" v-if="item.is_use" class="choose-btn use"
                                 :loading="useLoading">加载模板
                            </div>
                            <div @click="show(item)" class="choose-btn">预览模板</div>
                        </div>
                        <div class="show-img" :style="{backgroundImage: 'url('+item.pics[0]+')'}"
                             v-if="item.pics.length >= 1"></div>
                        <div class="info">
                            <div flex="dir:top main:center">
                                <div flex="main:justify">
                                    <div class="item-name">{{item.name}}</div>
                                    <el-button v-if="item.is_show == 1" type="text" style="margin-left: 3px;padding: 0"
                                               circle @click="change(item)">
                                        <img src="statics/img/mall/order/edit.png" alt="">
                                    </el-button>
                                </div>
                                <div flex="dir:left box:first cross:center" style="margin: 5px 0;" v-if="item.is_show == 1">
                                    <div style="color: #a9abaf;font-size: 12px">
                                        <div>原价 <span class="price">￥{{item.cloud_price}}</span></div>
                                        <div>售价 <span class="price">￥{{item.price}}</span></div>
                                    </div>
                                    <div class="info-about" flex="main:right">
                                        <template v-if="item.order">
                                            <template v-if="item.order.is_pay == 1">
                                                <template v-if="item.template">
                                                    <template v-if="item.template.is_update == 1">
                                                        <el-button type="primary" class="set-el-button" size="mini"
                                                                   :loading="installLoading && chooseIndex == index"
                                                                   @click="install(item,index)">更新
                                                        </el-button>
                                                    </template>
                                                    <template v-else>
                                                        <el-button class="set-el-button" size="mini" type="primary" plain>
                                                            已安装
                                                        </el-button>
                                                    </template>
                                                </template>
                                                <template v-else>
                                                    <el-button type="primary" class="set-el-button" size="mini"
                                                               :loading="installLoading && chooseIndex == index"
                                                               @click="install(item,index)">安装
                                                    </el-button>
                                                </template>
                                            </template>
                                            <template v-else>
                                                <el-button size="mini" type="warning" @click="pay(item.id,index)"
                                                           :loading="payLoading && chooseIndex == index">付款
                                                </el-button>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <el-button size="mini" type="warning" @click="buy(item.id,index)"
                                                       :loading="buyLoading && chooseIndex == index">购买
                                            </el-button>
                                        </template>
                                    </div>
                                </div>
                                <div v-else>
                                    <div flex="cross:center main:justify">
                                        <div class="price">
                                            ￥{{item.price}}
                                        </div>
                                        <el-button size="mini" type="primary" @click="toUse(item)" v-if="item.is_use">加载
                                        </el-button>
                                        <el-button size="mini" type="warning" @click="buyTip" v-else>购买
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div flex="dir:top main:center cross:center" style="height: 500px;" ref="list" v-else>
                <img src="statics/img/mall/no-template.png" style="width: 200px;height: 140px;">
                <div style="color: #999999;margin-top: 20px;">暂无模板</div>
            </div>
            <div style="margin-top: 24px;" flex="dir:right">
                <el-pagination
                    v-if="pagination && pagination.totalCount > pagination.pageSize"
                    style="display: inline-block;"
                    background
                    :page-size="pagination.pageSize"
                    @current-change="getDetail"
                    layout="prev, pager, next, jumper"
                    :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>
    </el-card>

    <el-dialog :visible.sync="payDialogVisible" width="480px" :before-close="closePay">
        <template v-if="template && template.order">
            <div style="margin-bottom: 20px">请联系管理员完成付款操作</div>
            <div flex="box:first" style="margin-bottom: 12px">
                <div style="width: 80px">订单号：</div>
                <div>{{template.order.order_no}}</div>
            </div>
            <div flex="box:first" style="margin-bottom: 12px">
                <div style="width: 80px">金额：</div>
                <div>{{template.order.pay_price}}元</div>
            </div>
            <div flex="box:first">
                <div style="width: 80px">状态：</div>
                <div v-if="template.order.is_pay==0" style="color: #E6A23C">待付款</div>
                <div v-if="template.order.is_pay==1" style="color: #67C23A">已付款</div>
            </div>
        </template>
    </el-dialog>
    <el-dialog title="手机端预览" :visible.sync="dialogVisible" width="30%" :before-close="handleClose" v-if="template">
        <div style="height: 600px;overflow-y: auto;text-align: center">
            <img style="width: 375px;" :src="template.pics[0]" alt="">
        </div>
        <span slot="footer" class="dialog-footer" flex="dir:right cross:center">
            <template v-if=" template.is_show">
            <div v-if="template.order">
                <div v-if="template.order.is_pay == 1">
                    <div v-if="template.template">
                        <div v-if="template.template.is_update == 1">
                            <el-button type="primary" class="set-el-button" size="small"
                                       :loading="installLoading"
                                       @click="install(template)">更新
                            </el-button>
                        </div>
                    </div>
                    <div v-else>
                        <el-button type="primary" class="set-el-button" size="small"
                                   :loading="installLoading"
                                   @click="install(template)">安装
                        </el-button>
                    </div>
                </div>
                <div v-else>
                    <el-button size="small" type="warning" @click="pay(template.id)"
                               :loading="payLoading">付款
                    </el-button>
                </div>
            </div>
            <div v-else>
                <el-button size="small" type="warning" @click="buy(template.id)"
                           :loading="buyLoading">购买
                </el-button>
            </div>
            </template>
            <el-button type="primary" @click="toUse(template)" class="set-el-button" v-if="template.is_use"
                       size="small">加载模板</el-button>
            <el-button size="small" @click="dialogVisible = false;template=null"
                       style="margin-right: 10px;">取 消</el-button>
        </span>
    </el-dialog>
    <app-dialog-select title="选择商城" url="plugin/diy/mall/market/get-mall" @selected="confirm" :display="display1" @close="close">
    </app-dialog-select>
    <app-dialog-select title="选择模板" url="plugin/diy/mall/market/get-template" :params="params" @selected="confirm" @close="close"
                       :display="display2">
    </app-dialog-select>
    <el-dialog title="修改模板信息" width="35%" :visible.sync="changeModel" :before-close="handleClose">
        <el-form ref="form" :rules="detailRule" label-width="80px" :model="detail">
            <el-form-item label="模板名称" prop="name">
                <el-input size="small" v-model="detail.name" :maxlength="8" show-word-limit></el-input>
            </el-form-item>
            <el-form-item label="原价" prop="cloud_price">
                <el-input :disabled="true" size="small" v-model="detail.cloud_price">
                    <template slot="append">元</template>
                </el-input>
            </el-form-item>
            <el-form-item label="售价" prop="price">
                <el-input size="small" v-model="detail.price">
                    <template slot="append">元</template>
                </el-input>
            </el-form-item>
        </el-form>
        <div style="text-align: center;width: 100%;color: #c9c9c9;">注：原价为总账号购买模板价格，售价为子账号页面展示价格</div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" :loading="submitLoading" @click="changeModel = false;detail = {}">取消</el-button>
            <el-button size="small" type="primary" :loading="submitLoading" @click="toChange('form')">确定修改</el-button>
        </span>
    </el-dialog>
    <el-dialog class="loading-dialog" flex="cross:center main:center" title="提示" width="350px"
               :visible.sync="installLoading">
        <div style="text-align: center;font-size: 16px;"><i style="font-size: 20px;margin-right: 10px;color: #3399ff"
                                                            class="el-icon-loading"></i>正在安装中，请稍后...
        </div>
    </el-dialog>
    <el-dialog class="loading-dialog" flex="cross:center main:center" title="提示" width="350px"
               :visible.sync="useLoading">
        <div style="text-align: center;font-size: 16px;"><i style="font-size: 20px;margin-right: 10px;color: #3399ff"
                                                            class="el-icon-loading"></i>正在加载中，请稍后...
        </div>
    </el-dialog>
    <el-dialog class="loading-dialog" flex="cross:center main:center" title="加载模板" width="500px"
               :visible.sync="chooseLoading">
        <div style="text-align: center;font-size: 16px;">
            <div>请选择加载到的页面</div>
            <el-radio-group v-model="template_type" class="choose-btn-group" flex="dir:left main:center">
                <div class="choose-btn" :class="[template_type === 'page'  ? `active` : ``]" flex="dir:top" @click="template_type = 'page'">
                    <el-radio label="page">微页面</el-radio>
                    <img src="statics/img/mall/diy/page.png" alt=""/>
                </div>
                <div class="choose-btn" :class="[template_type === '' ? `active` : ``]" flex="dir:top" @click="template_type = ''">
                    <el-radio label="">自定义模块</el-radio>
                    <img src="statics/img/mall/diy/module.png" alt=""/>
                </div>
            </el-radio-group>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="chooseLoading = false;template_type = 'page';detail = {}">取消</el-button>
            <el-button size="small" type="primary" :loading="submitLoading" @click="loadTip(detail)">加载模板</el-button>
        </span>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                step: 1,
                params: {},
                detail: {},
                display1: false,
                display2: false,
                loading: false,
                changeModel: false,
                submitLoading: false,
                list: [],
                pagination: null,
                page: 1,
                buyLoading: false,
                payDialogVisible: false,
                template: null,
                useLoading: false,
                payLoading: false,
                installLoading: false,
                chooseLoading: false,
                keyword: '',
                type: '',
                detailRule: {
                    name: [
                        {required: true, message: '请填写模板名称。'},
                    ],
                    cloud_price: [
                        {required: true, message: '请填写原价。'},
                    ],
                    price: [
                        {required: true, message: '请选择售价。   '},
                    ]
                },
                status: '',
                chooseIndex: null,
                template_type: 'page',
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            toUse(item) {
                if (!item.is_use) {
                    this.$alert('没有该模板的使用权限，请联系管理员', '提示', {
                        type: 'warning'
                    });
                    return;
                } else {
                    if (item.template_type.length > 1) {
                        this.chooseLoading = true;
                        this.detail = item;
                    } else {
                        this.template_type = item.template_type[0];
                        this.loadTip(item);
                    }
                }
            },
            loadTip(item) {
                if (item.type == 'home') {
                    this.$confirm('选择加载的是首页布局的模板，会覆盖当前的首页布局们是否确定加载？', '提示', {
                        confirmButtonText: '确认',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        this.load(item);
                    })
                } else {
                    this.load(item);
                }
            },
            load(item) {
                this.useLoading = true;
                this.chooseLoading = false;
                this.detail = {};
                let template_type = this.template_type;
                this.template_type = 'page';
                request({
                    params: {
                        r: 'plugin/diy/mall/market/loading',
                        template_id: item.id,
                        type: template_type,
                    }
                }).then(response => {
                    this.useLoading = false;
                    if (response.data.code == 0) {
                        this.$confirm('模板加载成功，是否前往编辑？', '提示', {
                            confirmButtonText: '确认',
                            cancelButtonText: '取消',
                            type: 'success'
                        }).then(() => {
                            if (item.type == 'diy') {
                                if (template_type === 'page') {
                                    navigateTo({
                                        r: 'plugin/diy/mall/template/edit',
                                        id: response.data.data.id
                                    }, true);
                                } else {
                                    navigateTo({
                                        r: 'plugin/diy/mall/module/edit',
                                        id: response.data.data.id
                                    }, true);
                                }
                            } else {
                                navigateTo({
                                    r: 'mall/home-page/setting',
                                }, true);
                            }
                        })
                    } else {
                        this.$alert(response.data.msg, '提示', {
                            type: 'warning'
                        });
                    }
                }).catch(response => {
                    this.useLoading = false;
                });
            },
            toChange(formName) {
                let that = this;
                that.$refs[formName].validate(valid => {
                    if (valid) {
                        that.submitLoading = true;
                        request({
                            params: {
                                r: 'plugin/diy/mall/market/update-template',
                            },
                            data: {
                                template_id: that.detail.id,
                                name: that.detail.name,
                                price: that.detail.price
                            },
                            method: 'post'
                        }).then(response => {
                            that.submitLoading = false;
                            if (response.data.code == 0) {
                                that.$message.success(response.data.msg);
                                that.detail = {};
                                that.changeModel = false;
                                that.loadData();
                            } else {
                                that.$alert(response.data.msg, '提示', {
                                    type: 'warning'
                                });
                            }
                        }).catch(response => {
                            that.submitLoading = false;
                        })
                    }
                })
            },
            change(item) {
                this.detail = JSON.parse(JSON.stringify(item));
                this.changeModel = true;
            },
            show(item) {
                this.dialogVisible = true;
                this.template = item;
            },
            loadData() {
                this.loading = true;
                let rank = Math.floor(this.$refs['list'].offsetWidth / 208);
                request({
                    params: {
                        r: 'plugin/diy/mall/market/list',
                        page: this.page,
                        keyword: this.keyword,
                        status: this.status,
                        limit: Math.floor(20 / rank) * rank
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code == 0) {
                        this.list = response.data.data.list;
                        this.pagination = response.data.data.pagination;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.loading = false;
                });
            },
            issue() {
                this.display1 = true;
            },
            confirm(data) {
                if (this.step === 1) {
                    this.step = 2;
                    this.params = {mall_id: data.id};
                    this.display1 = false;
                    this.display2 = true;
                } else {
                    this.step = 1;
                    this.display2 = false;
                    request({
                        params: {
                            r: 'plugin/diy/mall/market/issue',
                            mall_id: this.params.mall_id,
                            template_id: data.id,
                            type: 'diy',
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success('发布成功');
                            window.location.href = e.data.data
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                }
            },
            close() {
                this.step = 1;
                this.display1 = false;
                this.display2 = false;
            },
            getDetail(currentPage) {
                this.page = currentPage;
                this.loadData();
            },
            handleClose() {
                this.template = null;
                this.detail = {};
                this.dialogVisible = false;
                this.changeModel = false;
            },
            buy(id,index) {
                if(index) {
                    this.chooseIndex = index;
                }
                this.$confirm('确认购买该模板？', '提示', {
                    confirmButtonText: '确认',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.buyLoading = true;
                    this.$request({
                        params: {
                            r: 'plugin/diy/mall/market/buy',
                            template_id: id,
                        },
                    }).then(e => {
                        this.buyLoading = false;
                        if (e.data.code === 0) {
                            this.$alert(e.data.msg, '提示', {
                                type: 'success',
                                callback: action => {
                                    location.reload();
                                }
                            });
                        } else {
                            this.$alert(e.data.msg, '提示', {
                                type: 'error',
                            });
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            pay(id,index) {
                if(index) {
                    this.chooseIndex = index;
                }
                this.$confirm('确认付款该模板？', '提示', {
                    confirmButtonText: '确认',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.payLoading = true;
                    this.$request({
                        params: {
                            r: 'plugin/diy/mall/market/pay',
                            template_id: id,
                        },
                    }).then(e => {
                        this.payLoading = false;
                        if (e.data.code === 0) {
                            this.template = e.data.data;
                            this.payDialogVisible = true;
                        } else {
                            this.$alert(e.data.msg, '提示', {
                                type: 'error',
                                callback: action => {
                                    location.reload();
                                }
                            });
                        }
                    }).catch(e => {
                        this.payLoading = false;
                    });
                }).catch(() => {
                });
            },
            closePay(done) {
                this.template = null;
                done();
            },
            install(item,index) {
                if(index) {
                    this.chooseIndex = index;
                }
                this.$confirm('安装过程请勿关闭或刷新浏览器！确认安装请点击确定开始下载模板。', '注意', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    closeOnClickModal: false,
                }).then(() => {
                    this.download(item);
                }).catch(() => {
                });
            },
            download(item) {
                this.installLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/market/install',
                        template_id: item.id,
                    },
                }).then(e => {
                    this.installLoading = false;
                    if (e.data.code === 0) {
                        item.template = {
                            data: true,
                        };
                        this.$confirm('安装完成，您可以使用该模板了', '提示', {
                            confirmButtonText: '加载模板',
                            cancelButtonText: '取消',
                            type: 'success',
                            closeOnClickModal: false,
                        }).then(() => {
                            item.is_use = 1;
                            this.toUse(item);
                        }).catch(() => {
                            this.loadData()
                        });
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            type: 'warning',
                        }).then(() => {
                            this.loadData()
                        });
                    }
                }).catch(e => {
                    this.installLoading = false;
                });
            },
            onSubmit() {
                this.page = 1;
                this.loadData();
            },
            buyTip() {
                this.$confirm('请咨询商城管理员开通', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    showCancelButton: false,
                    showConfirmButton: false,
                    type: 'warning',
                    closeOnClickModal: false,
                }).then(() => {
                }).catch(() => {
                });
            },
            syncTemplateType() {
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/sync-template-type',
                    },
                }).then(e => {
                }).catch(e => {
                });
            }
        },
        created() {
            this.syncTemplateType();
        },
    });
</script>
