<?php defined('YII_ENV') or exit('Access Denied');
$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
?>

<style>
    #app {

    }

    .el-card__header {
        height: 60px;
        font-size: 15px;
        padding-left: 15px;
        border-radius: 4px;
        border: 1px solid #ebeef5;
        background-color: white;
    }

    .el-card {
        background-color: #f3f3f3;

    }

    .card-body {
        background-color: white;
        width: 100%;
        margin-top: 10px;
        border-radius: 4px;
        border: 1px solid #ebeef5;
        padding: 30px;
        overflow: auto;
    }


    .good-detail {
        width: 100%;
    }

    .detail-item {
        border-bottom: 1px solid #e2e2e2;
    }

    .border-padding {
        padding: 0;
    }

    td.el-table_1_column_1 {
        padding: 0;
    }

    .el-table__row .el-table_1_column_1 .cell {
        padding: 0 !important;
    }

    .name {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        white-space: normal;
    }

    .attr {

        font-size: 14px;
        color: #47484a;
        line-height: 18px;
    }

    .attr .tag {
        height: 16px;
        line-height: 14px;
        font-size: 12px;
        padding: 0 4px;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
        white-space: normal;
    }

    .attr-text {
        white-space: nowrap;
    }
    .bot {
        margin-top: 10px;
    }
</style>

<div id="app">
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="listLoading">
        <div slot="header">
            礼物记录
        </div>
        <div class="card-body">
            <el-form size="small" :model="form" ref="form" label-width="100px" :inline="true">
                <div class="search-bar">
                    <el-form-item label="礼物生成时间">
                        <label slot="label">
                            礼物生成时间:
                        </label>
                        <el-date-picker
                                @change="searchOrder"
                                v-model="search.time"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="选择玩法">
                        <label slot="label">
                            选择玩法:
                        </label>
                        <el-select style="width: 120px;" size="small" v-model="search.type" @change='searchOrder'>
                            <el-option key="all" label="全部" value=""></el-option>
                            <el-option key="direct_open" label="直接送礼" value="direct_open"></el-option>
                            <el-option key="time_open" label="定时开奖" value="time_open"></el-option>
                            <el-option key="num_open" label="满人开奖" value="num_open"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="所属平台">
                        <label slot="label">
                            所属平台:
                        </label>
                        <el-select style="width: 120px;" size="small" v-model="search.platform" @change='searchOrder'>
                            <el-option key="all" label="全部平台" value=""></el-option>
                            <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                            <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                            <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                            <el-option key="bdapp" label="百度" value="bdapp"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <div class="input-item">
                            <el-input v-model="search.keyword_1" placeholder="请输入搜索内容" clearable @clear="searchOrder"
                                      @keyup.enter.native="searchOrder">
                                <el-select style="width: 120px" slot="prepend" v-model="search.keyword">
                                    <el-option key="name" label="商品名称" value="name"></el-option>
                                    <el-option key="order_no" label="订单号" value="order_no"></el-option>
                                    <el-option key="nickname" label="用户名" value="nickname"></el-option>
                                    <el-option key="user_id" label="用户ID" value="user_id"></el-option>
                                </el-select>
                            </el-input>
                        </div>
                    </el-form-item>
                </div>
                <div class="taber">
                    <el-table
                            :data="list"
                            border
                            style="width: 100%">
                        <el-table-column
                                prop="sendOrder"
                                label="商品信息"
                                padding="0"
                                width="226 ">
                            <template slot-scope="scope">
                                <div class="good-detail">
                                    <div flex="" style="padding: 10px 10px 10px 10px;"
                                         v-if="scope.row.sendOrder[0].detail.length > 1" class="detail-item"
                                         v-for="(item, index) in scope.row.sendOrder[0].detail" :key="index">
                                        <image style="width: 50px; height: 50px;margin-right: 10px"
                                               :src="item.goods.goodsWarehouse.cover_pic"></image>
                                        <div style="width: 170px;min-height: 50px">
                                            <p class="name"
                                                :title="item.goods.goodsWarehouse.name"
                                               style="font-size: 14px;line-height: 1.2;color:#74767b;margin: 0;">
                                                {{item.goods.goodsWarehouse.name}}</p>
                                            <div flex="" class="attr" style="margin: 0;" :title="getTitle(JSON.parse(item.goods_info).attr_list)">
                                                <div class="attr-text">规格：</div>
                                                <div>
                                                    <el-tag size="small" class="tag">
                                                    {{getAttr(JSON.parse(item.goods_info).attr_list)}}
                                                    </el-tag>
                                                </div>
                                            </div>
                                            <p style="margin: 0;">
                                                数量
                                                <span style="color: #ff4544">{{item.num}}</span>
                                                件
                                            </p>
                                        </div>
                                    </div>
                                    <div flex="" style="padding:10px 10px;"
                                         v-if="scope.row.sendOrder[0].detail.length === 1">
                                        <image style="width: 50px; height: 50px;margin-right: 10px"
                                               :src="scope.row.sendOrder[0].detail[0].goods.goodsWarehouse.cover_pic"></image>
                                        <div style="width: 170px;min-height: 50px">
                                            <p class="name"
                                            :title="scope.row.sendOrder[0].detail[0].goods.goodsWarehouse.name"
                                               style="font-size: 14px;line-height: 1.2;color:#74767b;margin: 0;">
                                                {{scope.row.sendOrder[0].detail[0].goods.goodsWarehouse.name}}</p>
                                            <div flex="" class="attr" style="margin: 0;" :title="getTitle(JSON.parse(scope.row.sendOrder[0].detail[0].goods_info).attr_list)">
                                               <div class="attr-text"> 规格：</div>
                                               <div>
                                                   <el-tag size="small" class="tag"
                                                           style="margin-right: 10px;" >
                                                       {{getAttr(JSON.parse(scope.row.sendOrder[0].detail[0].goods_info).attr_list)}}
                                                   </el-tag>
                                               </div>
                                            </div>
                                            <p style="margin: 0;">
                                                数量
                                                <span style="color: #ff4544">{{scope.row.sendOrder[0].detail[0].num}}</span>
                                                件
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="sendOrder[0].order_no"
                                label="订单号"
                                width="228">
                        </el-table-column>
                        <el-table-column
                                prop="user"
                                width="230"
                                label="用户名">
                            <template slot-scope="scope">
                                <div flex="">
                                    <image style="width: 50px; height: 50px"
                                           :src="scope.row.user.userInfo.avatar"></image>
                                    <div style="margin-left: 10px">
                                        <span>{{scope.row.user.nickname}}</span>
                                        <div>
                                            <img src="statics/img/mall/ali.png"
                                                 v-if="scope.row.user.userInfo.platform == 'aliapp'" alt="">
                                            <img src="statics/img/mall/wx.png"
                                                 v-else-if="scope.row.user.userInfo.platform == 'wxapp'" alt="">
                                            <img src="statics/img/mall/toutiao.png"
                                                 v-else-if="scope.row.user.userInfo.platform == 'ttapp'" alt="">
                                            <img src="statics/img/mall/baidu.png"
                                                 v-else-if="scope.row.user.userInfo.platform == 'bdapp'" alt="">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="type"
                                label="玩法">
                            <template slot-scope="scope">
                                <span>{{scope.row.type === 'direct_open'? '直接送礼' : scope.row.type === 'time_open' ? '定时开奖' : '满人开奖'}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="type"
                                label="领取方式">
                            <template slot-scope="scope">
                                <span>{{scope.row.open_type == 1 ? '多人领取' : '一人领取'}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="join_num"
                                label="参与人数">
                        </el-table-column>
                        <el-table-column
                                prop="address_status"
                                width="110"
                                label="地址填写状态">
                            <template slot-scope="scope">
                                <el-tag :type="scope.row.address_status === '未填写' ? 'danger' : scope.row.address_status === '部分填写' ? 'warning' : 'success'">
                                    {{scope.row.address_status}}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="sendOrder[0].total_pay_price"
                                label="实付金额">
                        </el-table-column>
                        <el-table-column
                                prop="sendOrder[0].created_at"
                                width="200"
                                label="生成时间">
                        </el-table-column>
                        <el-table-column
                                width="100"
                                fixed="right"
                                label="操作">
                            <template slot-scope="scope">
                                <el-button type="text" circle @click="detail(scope.row.id)" size="mini">
                                    <el-tooltip class="item" effect="dark" content="领取详情" placement="top">
                                        <img src="statics/img/mall/detail.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </el-form>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    background
                    :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper"
                    :current-page="pagination.current_page"
                    :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>

    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                listLoading: false,
                form: {},
                search: {
                    keyword: 'name',
                    keyword_1: '',
                    platform: '',
                    type: '',
                    time: [],
                    page: 1,
                    start_date: '',
                    end_date: ''
                },
                list: [],
                pagination: {}
            }
        },

        created() {
            if (getQuery('page')) {
                this.search.page = getQuery('page');
            }
            this.requestList();
        },

        methods: {
            getTitle(data) {
                let str = '';
                data.map(item => {
                    str += `${item.attr_group_name}: ${item.attr_name} `
                });
                return str;
            },
            destroy(data, index) {
            },

            pageChange(e) {
                this.search.page = e;
                this.requestList();

            },

            detail(id) {
                navigateTo({
                    r: 'plugin/gift/mall/record/tribute-detail',
                    gift_id: id,
                    page: this.search.page
                });
            },

            async requestList() {
                this.listLoading = true;
                let search = this.search;
                if (this.search.time && this.search.time.length > 0) {
                    search.start_date = this.search.time[0];
                    search.end_date = this.search.time[1];
                } else {
                    search.start_date = "";
                    search.end_date = "";
                }

                try {
                    const res = await request({
                        params: {
                            r: 'plugin/gift/mall/record/tribute',
                            keyword: this.search.keyword,
                            keyword_1: this.search.keyword_1,
                            platform: this.search.platform,
                            type: this.search.type,
                            time: this.search.time,
                            page: this.search.page,
                            start_date: this.search.start_date,
                            end_date: this.search.end_date
                        },
                        method: 'get'
                    });
                    this.listLoading = false;
                    if (res.data.code === 0) {
                        this.list = res.data.data.list;
                        this.pagination = res.data.data.pagination;
                    } else {
                        this.$message.error(res.data.msg);
                    }
                } catch (e) {
                    this.listLoading = false;
                    throw new Error(e);
                }
            },

            searchOrder() {
                this.search.page = 1;
                this.requestList();
            },

            getAttr(data) {
                let str = ``;
                for (let i = 0; i < data.length; i++) {
                    str += `${data[i].attr_group_name} : ${data[i].attr_name} `;
                }
                return str;
            }

        },

        filters: {
            getPic(data) {
                let goods_attr = JSON.parse(data.goods_info).goods_attr;
                if (goods_attr.pic_url) {
                    return goods_attr.pic_url;
                } else {
                    return goods_attr.cover_pic;
                }
            }
        }
    })
</script>
