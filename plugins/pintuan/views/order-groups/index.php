<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */

$iconBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('pintuan') . '/img/';
?>

<style scoped>
    .table-body {
        padding: 20px;
        background-color: #fff;
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

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .leader {
        display: flex;
    }

    .leader img {
        margin: 0 10px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <!-- 标题栏 -->
        <div slot="header">
            <span>拼团管理</span>
        </div>
        <div class="table-body">
            <el-form @submit.native.prevent size="small" :inline="true" :model="search">
                <el-form-item style="margin-bottom: 0">
                    <div class="input-item">
                        <el-input @keyup.enter.native="searchOrder" v-model="search.keyword" placeholder="请输入商品名/订单号"
                                  clearable @clear='searchOrder'>
                            <el-button slot="append" icon="el-icon-search" @click="searchOrder"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <!-- 订单状态选择 -->
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="0"></el-tab-pane>
                <el-tab-pane label="拼团中" name="1"></el-tab-pane>
                <el-tab-pane label="拼团成功" name="2"></el-tab-pane>
                <el-tab-pane label="拼团失败" name="3"></el-tab-pane>
            </el-tabs>
            <!-- 订单内容 -->
            <el-table stripe border v-loading="loading" :data="list">
                <el-table-column prop="id" width="60" label="ID"></el-table-column>
                <el-table-column label="商品信息">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px">
                                <app-image width="60" height="60" mode="aspectFill"
                                           :src="scope.row.goods_info.goods_attr.pic_url ? scope.row.goods_info.goods_attr.pic_url : scope.row.goods.goodsWarehouse.cover_pic"></app-image>
                            </div>
                            <div flex="dir:top">
                                <app-ellipsis :line="1">{{scope.row.goods.goodsWarehouse.name}}</app-ellipsis>
                                <div flex="dir:left">
                                    规格:
                                    <span v-for="(gItem, index) in scope.row.goods_info.attr_list">
                                    <span>{{gItem.attr_name}}  </span>
                                </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="拼团信息" width="250">
                    <template slot-scope="scope">
                        <div class="leader">
                            团长:
                            <el-tooltip class="item" effect="dark"
                                        v-if="scope.row.order.user.userInfo.platform == 'wxapp'" content="微信"
                                        placement="top">
                                <img src="statics/img/mall/wx.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark"
                                        v-else-if="scope.row.order.user.userInfo.platform == 'aliapp'" content="支付宝"
                                        placement="top">
                                <img src="statics/img/mall/ali.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark"
                                        v-else-if="scope.row.order.user.userInfo.platform == 'ttapp'" content="抖音/头条"
                                        placement="top">
                                <img src="statics/img/mall/toutiao.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark"
                                        v-else-if="scope.row.order.user.userInfo.platform == 'bdapp'" content="百度"
                                        placement="top">
                                <img src="statics/img/mall/baidu.png" alt="">
                            </el-tooltip>
                            <el-tag size="mini" v-else>未知</el-tag>
                            {{scope.row.order.user.nickname}}
                        </div>
                        <div>团长优惠: {{scope.row.preferential_price}}元</div>
                    </template>
                </el-table-column>
                <el-table-column label="拼团状态" width="150">
                    <template slot-scope="scope">
                        <div>
                            <el-tooltip class="item" effect="dark" v-if='scope.row.status == 1' content="拼团中"
                                        placement="top">
                                <img src="statics/img/mall/ing.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark" v-else-if='scope.row.status == 2' content="拼团成功"
                                        placement="top">
                                <img src="statics/img/mall/already.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark" v-else-if='scope.row.status == 3' content="拼团失败"
                                        placement="top">
                                <img src="statics/img/plugins/gameover.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark" v-else-if='scope.row.status == 4' content="待退款"
                                        placement="top">
                                <img src="statics/img/plugins/wait-refund.png" alt="">
                            </el-tooltip>
                            <el-tooltip class="item" effect="dark" v-else content="状态未知"
                                        placement="top">
                                <img src="statics/img/plugins/unknown.png" alt="">
                            </el-tooltip>
                            <el-tooltip v-if="scope.row.robot_num > 0" class="item" effect="dark"
                                        :content="'机器人数：' + scope.row.robot_num"
                                        placement="top">
                                <img src="<?= $iconBaseUrl ?>/robot.png" alt="">
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="拼团人数" width="220">
                    <template slot-scope="scope">
                        <div>总成团数：{{scope.row.people_num}}</div>
                        <div>当前人数：{{scope.row.order_count}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="开团时间" width="250"></el-table-column>
                <el-table-column label="操作" width="150">
                    <template slot-scope="scope">
                        <el-button v-if="scope.row.status == 1" @click="showRobotDialog(scope.row)" type="text" circle
                                   size="mini">
                            <el-tooltip class="item" effect="dark" content="添加机器人" placement="top">
                                <img src="<?= $iconBaseUrl ?>robot-add.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="拼团详情" placement="top">
                                <img src="statics/img/mall/order/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;float: right;"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper"
                            :current-page="search.page"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog @open="getRobots" title="为拼团添加机器人" :visible.sync="robotDialog.visible" :close-on-click-modal="false">
        <el-input clearable @clear="searchRobot" class="input-item" style="width: 250px" @keyup.enter.native="searchRobot" size="small" placeholder="请输入名称搜索" v-model="robotDialog.keyword">
            <el-button slot="append" icon="el-icon-search" @click="searchRobot"></el-button>
        </el-input>
        <el-table :data="robotDialog.list" v-loading="robotDialog.loading" @selection-change="goodsSelectionChange">
            <el-table-column label="选择" type="selection"></el-table-column>
            <el-table-column label="ID" prop="id" width="100px"></el-table-column>
            <el-table-column label="头像" prop="avatar">
                <template slot-scope="scope">
                    <app-image :src="scope.row.avatar"></app-image>
                </template>
            </el-table-column>
            <el-table-column label="昵称" prop="nickname"></el-table-column>
        </el-table>
        <div style="text-align: center; margin-top: 15px;">
            <el-pagination
                    v-if="robotDialog.pagination"
                    style="display: inline-block"
                    background
                    @current-change="robotDialogPageChange"
                    layout="prev, pager, next, jumper"
                    :page-size.sync="robotDialog.pagination.pageSize"
                    :total="robotDialog.pagination.totalCount">
            </el-pagination>
        </div>
        <div slot="footer">
            <el-button size="small" @click="robotDialog.visible = false">取 消</el-button>
            <el-button size="small" :loading="robotDialog.loading" type="primary" @click="addGoods">确 定</el-button>
        </div>

    </el-dialog>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    time: null,
                    r: 'plugin/pintuan/mall/order-groups/index',
                    keyword: '',
                    keyword_1: '1',
                    date_start: '',
                    date_end: '',
                    status: 0,
                    page: 1
                },
                loading: false,
                pagination: null,
                activeName: 0,
                list: [],
                address: [],
                export_list: [],
                robotDialog: {
                    visible: false,
                    page: 1,
                    loading: null,
                    pagination: null,
                    list: null,
                    index: null,
                    selectedList: null,
                    robot_list: [],
                    keyword: '',
                },
            };
        },
        created() {
            this.loading = true;
            // 获取列表
            loadList('plugin/pintuan/mall/order-groups/index').then(e => {
                this.loading = false;
                this.list = e.list;
                this.pagination = e.pagination;
                this.address = e.address;
                this.export_list = e.export_list;
            });
        },
        methods: {
            // 分页
            pageChange(page) {
                this.loading = true;
                this.list = [];
                this.search.page = page;
                request({
                    params: this.search,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }

                }).catch(e => {
                });
            },
            // 搜索
            searchOrder() {
                this.loading = true;
                this.search.page = 1;
                this.list = [];
                request({
                    params: this.search,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }

                }).catch(e => {
                });
            },
            // 切换订单状态
            handleClick(e) {
                this.list = [];
                this.search.status = e.name;
                this.search.page = 1;
                this.searchOrder();
            },
            edit(id) {
                navigateTo({
                    r: 'plugin/pintuan/mall/order-groups/detail',
                    id: id,
                });
            },
            showRobotDialog(row) {
                this.robotDialog.visible = true;
                this.robotDialog.index = row.id;
            },
            goodsSelectionChange(e) {
                this.robotDialog.selectedList = e;
            },
            getRobots() {
                let self = this;
                self.robotDialog.loading = true;
                self.robotDialog.robot_list = [];
                request({
                    params: {
                        r: 'plugin/pintuan/mall/robot/index',
                        page: self.robotDialog.page,
                        keyword: self.robotDialog.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.robotDialog.loading = false;
                    self.robotDialog.list = e.data.data.list;
                    self.robotDialog.pagination = e.data.data.pagination;
                }).catch(e => {
                    console.log(e);
                });
            },
            addGoods() {
                let self = this;
                for (let i in self.robotDialog.selectedList) {
                    self.robotDialog.robot_list.push(self.robotDialog.selectedList[i].id)
                }

                if (self.robotDialog.robot_list.length <= 0) {
                    self.$message.warning('请先选择机器人');
                    return;
                }

                self.robotDialog.loading = true;
                request({
                    params: {
                        r: 'plugin/pintuan/mall/order/add-robot',
                    },
                    method: 'post',
                    data: {
                        robots: self.robotDialog.robot_list,
                        pintuan_order_id: self.robotDialog.index
                    }
                }).then(e => {
                    self.robotDialog.visible = false;
                    self.robotDialog.loading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg)
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            robotDialogPageChange(page) {
                this.robotDialog.page = page;
                this.getRobots();
            },
            searchRobot() {
                this.robotDialog.page = 1;
                this.getRobots();
            }
        }
    });
</script>
