<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/23
 * Time: 17:16
 */
defined('YII_ENV') or exit('Access Denied');
$iconBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('vip_card') . '/img/';
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 350px;
        margin-bottom: 10px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
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

    .table-body .el-table .other .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 10px;
    }

    .change-date .el-dialog__body {
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .right-info {
        height: 60px;
        padding-left: 20px;
        font-size: 14px;
        color: #000000;
        flex-shrink: 0;
    }

    .right-img {
        height: 39px;
        width: 40px;
        margin-right: 18px;
    }

    .look-goods {
        color: #409eff;
        cursor: pointer;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>用户列表</span>
            <app-export-dialog action_url='index.php?r=plugin/vip_card/mall/user/index' style="float: right;margin-top: -5px" :field_list='export_list' :params="searchData"
                               @selected="exportConfirm">
            </app-export-dialog>
        </div>
        <div class="table-body">
            <el-form @submit.native.prevent inline>
                <!-- 搜索框 -->
                <el-form-item>
                    <div class="input-item">
                        <el-input size="small" v-model="keyword" placeholder="请输入搜索内容" clearable @clear="search"
                                  @keyup.enter.native="search">
                            <el-select style="width: 120px" slot="prepend" v-model="search_type">
                                <el-option label="用户ID" :value="1"></el-option>
                                <el-option label="用户昵称" :value="2"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button @click="edit" size="small" type="primary">+添加新会员</el-button>
                </el-form-item>
            </el-form>

            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未过期" name="0"></el-tab-pane>
                <el-tab-pane label="已过期" name="1"></el-tab-pane>
            </el-tabs>

            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width='100' prop="user_id" label="ID"></el-table-column>
                <el-table-column prop="user" label="头像昵称" width="300">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" style="float: left;margin-right: 8px" :src="scope.row.avatar"></app-image>
                        <div>{{scope.row.user.nickname}}</div>
                        <img class="platform-img" v-if="scope.row.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                        <el-button @click="openId(scope.$index)" type="success" style="float:right;padding:5px !important;">显示OpenId</el-button>
                        <div v-if="scope.row.is_open_id">{{scope.row.user.username}}</div>
                    </template>
                </el-table-column>

                <el-table-column width='300' prop="created_at" label="有效期">
                    <template slot-scope="scope">
                        <span>{{scope.row.start_time}} ~ {{scope.row.end_time}}</span>
                    </template>
                </el-table-column>

                <el-table-column prop="created_at" label="当前会员权益">
                    <template slot-scope="scope">
                        <div v-if="scope.row.rights.is_delivery == '1'" class="right-info" flex="dir:left cross:center">
                            <img class="right-img" src="<?= $iconBaseUrl ?>/delivery.png" alt="">
                            <div>
                                <div>自营商品包邮</div>
                            </div>
                        </div>
                        <div v-if="scope.row.rights.all == 1" class="right-info" flex="dir:left cross:center">
                            <img class="right-img" src="<?= $iconBaseUrl ?>/all.png" alt="">
                            <div>
                                <div>全场自营商品{{scope.row.rights.discount}}折</div>
                            </div>
                        </div>
                        <div v-else style="width: auto;" class="right-info right-about" flex="dir:left cross:center">
                            <img class="right-img" src="<?= $iconBaseUrl ?>/all.png" alt="">
                            <div flex="dir:left">
                                <div @click="toLook(2,scope.row.user_id)" class="look-goods" v-if="scope.row.rights.cats.length > 0">指定分类</div>
                                <div v-if="scope.row.rights.cats.length > 0 && scope.row.rights.goods.length > 0">/</div>
                                <div @click="toLook(1,scope.row.user_id)" class="look-goods" v-if="scope.row.rights.goods.length > 0">指定商品</div>
                                <div>{{scope.row.rights.discount}}折</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column  prop="created_at" label="所有赠送">
                    <template slot-scope="scope">
                        <div v-if="scope.row.send_integral_num">积分:{{scope.row.send_integral_num}}积分</div>
                        <div v-if="scope.row.send_balance">余额:{{scope.row.send_balance}}元</div>
                        <div v-if="scope.row.send_coupons.length > 0">
                            <span>优惠券:</span>
                            <el-tag style="margin:5px"
                                    v-for="(tag,i) in scope.row.send_coupons"
                                    :key="i">
                                {{tag.num}}张 | {{tag.name}}
                            </el-tag>
                        </div>
                        <div v-if="scope.row.send_cards.length > 0">
                            <span>卡券:</span>
                            <el-tag style="margin:5px"
                                    v-for="(tag,i) in scope.row.send_cards"
                                    :key="i">
                                {{tag.num}}张 | {{tag.name}}
                            </el-tag>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="操作" fixed="right" class="other" width="160">
                    <template slot-scope="scope">
                        <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="deleteUser(scope.row.user_id)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="showLog(scope.row.order)">
                            <el-tooltip class="item" effect="dark" content="购买记录" placement="top">
                                <img src="<?= $iconBaseUrl ?>/buy-log.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                        :page-size="pagination.pageSize" hide-on-single-page background @current-change="pageChange" layout="prev, pager, next, jumper" :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog title="添加新会员" :visible.sync="dialogFormVisible" width="30%">
        <el-form label-width="150px">
            <el-form-item label="超级会员卡名称">
                <div>{{card.name}}</div>
            </el-form-item>
            <el-form-item label="昵称搜索">
                <el-autocomplete size="small" v-model="name" value-key="nickname" :fetch-suggestions="querySearchAsync" placeholder="请输入搜索内容" @select="shareClick"></el-autocomplete>
            </el-form-item>
            <el-form-item label="选择子卡">
                <el-select size="small" v-model="detail_id" placeholder="请选择">
                    <el-option v-for="item in card.detail" :key="item.id" :label="item.name" :value="item.id">
                    </el-option>
                </el-select>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogFormVisible = false">取 消</el-button>
            <el-button size="small" type="primary" @click="toAdd">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="购买记录" :visible.sync="dialogLog">
        <el-table :data="order">
            <el-table-column property="main_name" label="超级会员卡名称"></el-table-column>
            <el-table-column property="detail_name" label="子卡名称"></el-table-column>
            <el-table-column property="expire" label="有效时长">
                <template slot-scope="scope">
                    <span>{{scope.row.expire}}天</span>
                </template>
            </el-table-column>
            <el-table-column property="price" label="价格"></el-table-column>
            <el-table-column property="created_at" label="下单时间"></el-table-column>
        </el-table>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogLog = false">取 消</el-button>
            <el-button size="small" type="primary" @click="dialogLog = false">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog :title="lookGoods ? '查看商品':'查看分类'" :visible.sync="showDialog">
        <el-input size="small" v-model="GoodsKeyword" placeholder="根据名称搜索">
            <template slot="append">
                <el-button slot="append" @click="toSearch(1)">搜索</el-button>
            </template>
        </el-input>
        <el-table v-loading="dialogLoading" :data="goods" class="dialog-goods">
            <el-table-column property="id" label="ID" width="120"></el-table-column>
            <el-table-column property="name" :label="lookGoods ? '商品名称':'分类名称'"></el-table-column>
        </el-table>
        <div flex="main:center" style="margin-top: 10px;">
            <el-pagination v-if="pagination"
                    :page-size="pagination.pageSize" style="display: inline-block;float: right;" background @current-change="searchPageChange" layout="prev, pager, next" :total="pagination.total_count">
            </el-pagination>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size='small' @click="showDialog= false;goods=[]">取 消</el-button>
            <el-button size='small' type="primary" @click="showDialog= false;goods=[]">确 定</el-button>
        </span>
    </el-dialog>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            detail: '',
            list: [],
            dialogLoading: false,
            id: null,
            pagination: {},
            export_list: [],
            dialogFormVisible: false,
            dialogFormChange: false,
            dialogLog: false,
            keyword: null,
            GoodsKeyword: null,
            order: [],
            searchData: {
                keyword: '',
                search_type: 2,
            },
            search_type:2,
            name: '',
            detail_id: '',
            showDialog:false,
            lookGoods: false,
            lookCat: false,
            pagination: {},
            goods: [],
            user_id: '',
            id: '',
            card: {},
            activeName: '-1',
        };
    },

    methods: {
        showLog(order) {
            this.dialogLog = true;
            this.order = order;
        },
        //搜索
        querySearchAsync(queryString, cb) {
            this.name = queryString;
            this.shareUser(cb);
        },

        searchPageChange(page) {
            this.toSearch(page);
        },
        toLook(num,user_id) {
            let self = this;
            if(num == 1) {
                self.lookGoods = true;
                self.lookCat = false;
            }else {
                self.lookCat = true;
                self.lookGoods = false;
            }
            self.dialogLoading = true;
            self.showDialog = true;
            self.id = user_id;
            request({
                params: {
                    r: 'plugin/vip_card/mall/user/right',
                    type: num,
                    user_id: self.id
                },
            }).then(e => {
                if (e.data.code == 0) {
                    self.dialogLoading = false;
                    self.goods = e.data.data.list;
                    self.pagination = e.data.data.pagination;
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.$message.error(e.data.msg);
            });
        },

        toSearch(page) {
            let self = this;
            self.dialogLoading = true;
            request({
                params: {
                    r: 'plugin/vip_card/mall/user/right',
                    type: this.lookGoods ? 1: 2,
                    keyword: this.GoodsKeyword,
                    user_id: this.id,
                    page: page
                },
            }).then(e => {
                if (e.data.code == 0) {
                    self.dialogLoading = false;
                    self.goods = e.data.data.list;
                    self.pagination = e.data.data.pagination;
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.$message.error(e.data.msg);
            });
        },

        shareUser(cb) {
            request({
                params: {
                    r: 'plugin/vip_card/mall/user/search',
                    keyword: this.name,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    cb(e.data.data.list);
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {});
        },

        shareClick(row) {
            this.user_id = row.id;
        },

        openId(index) {
            let item = this.list;
            item[index].is_open_id = !item[index].is_open_id;
            this.list = JSON.parse(JSON.stringify(this.list));
        },

        exportConfirm() {
            this.searchData.keyword = this.keyword;
            this.searchData.search_type = this.search_type;
            this.searchData.expire_type = this.activeName;
        },
        search() {
            this.getList(1);
        },
        //分页
        pageChange(page) {
            this.getList(page);
        },

        toAdd() {
            let self = this;
            request({
                params: {
                    r: 'plugin/vip_card/mall/user/edit',
                },
                data: {
                    detail_id: self.detail_id,
                    user_id : self.user_id
                },
                method: 'post',
            }).then(e => {
                if (e.data.code === 0) {
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        self.getList();
                        self.dialogFormVisible = false;
                        self.user_id = null;
                        self.name = '';
                    } else {
                        self.$message.error(e.data.msg);
                    }
                } else {
                    this.$message.error(e.data.msg);
                }
            })
        },

        getList(page) {
            let self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/vip_card/mall/user',
                    page: page,
                    search_type: self.search_type,
                    keyword: self.keyword,
                    expire_type: self.activeName
                },
                method: 'get',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                    this.pagination = e.data.data.pagination;
                    this.export_list = e.data.data.export_list;
                    this.list = e.data.data.list;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },

        getCard() {
            let self = this;
            request({
                params: {
                    r: 'plugin/vip_card/mall/card/index',
                },
                method: 'get',
            }).then(e => {
                if (e.data.code === 0) {
                    if(e.data.data != null) {
                        this.card = e.data.data.list;
                        this.detail_id = e.data.data.list.detail[0].id
                    }
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },

        // 删除记录
        deleteUser(id) {
            this.$confirm('是否确认删除此会员', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                center: true,
                beforeClose: (action, instance, done) => {
                    if (action === 'confirm') {
                        instance.confirmButtonLoading = true;
                        instance.confirmButtonText = '执行中...';
                        request({
                            params: {
                                r: 'plugin/vip_card/mall/user/delete',
                            },
                            data:{
                                user_id: id
                            },
                            method: 'post'
                        }).then(e => {
                            done();
                            instance.confirmButtonLoading = false;
                            if (e.data.code == 0) {
                                this.getList(1);
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

        edit(id) {
            this.dialogFormVisible = true;
        },
        // 获取数据状态
        handleClick(tab, event) {
            this.search.status = this.activeName;
            this.search();
        },
    },
    created() {
        this.getList();
        this.getCard();
    }
})
</script>
