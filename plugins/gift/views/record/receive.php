<?php defined('YII_ENV') or exit('Access Denied');
$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
?>

<style>


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

    .search-bar {
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
        padding: 0 !important;
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

    .gift-user {
        width: 130px;
        height: 50px;
    }

    .gift-user .name {
        width: 70px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 15px;
        color: #747474;
    }

    .av {
        width: 50px;
        height: 50px;
    }

    .left-image {
        width: 12px;
        height: 20px;
        margin: 0 10px;
    }

    .taber {
        overflow: auto;
    }

    .bot {
        margin-top: 10px;
    }
    .attr-text {
        white-space: nowrap;
    }
</style>

<div id="app">
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="listLoading">
        <div slot="header">
            领取记录
        </div>
        <div class="card-body">
            <el-form size="small" :model="form" ref="form" label-width="80px" :inline="true">
                <div class="search-bar">

                    <el-form-item label="参与时间">
                        <label slot="label">
                            参与时间:
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


                    <el-form-item>
                        <div class="input-item">
                            <el-input v-model="search.keyword_1" placeholder="请输入搜索内容" clearable @clear="searchOrder"
                                      @keyup.enter.native="searchOrder">
                                <el-select style="width: 120px" slot="prepend" v-model="search.keyword">
                                    <el-option key="name" label="商品名称" value="name"></el-option>
                                    <el-option key="nickname" label="用户名称" value="nickname"></el-option>
                                    <el-option key="user_id" label="用户ID" value="user_id"></el-option>
                                    <el-option key="gift_order_no" label="订单号" value="gift_order_no"></el-option>
                                </el-select>
                            </el-input>
                        </div>
                    </el-form-item>
                </div>
                <div class="taber">
                    <el-table
                            :data="tableData"
                            border
                            style="width: 100%">
                        <el-table-column
                                prop="giftLog"
                                label="商品信息"
                                width="270">
                            <template slot-scope="scope">
                                <div class="good-detail">
                                    <div flex="" style="padding: 10px 10px 10px 10px;"
                                         v-if="scope.row.detail.length > 1" class="detail-item"
                                         v-for="(item, index) in scope.row.detail" :key="index">
                                        <image style="width: 50px; height: 50px;margin-right: 10px"
                                               :src="item.cover_pic"></image>
                                        <div style="width: 170px;min-height: 50px">
                                            <p class="name"
                                                :title="item.name"
                                               style="font-size: 14px;line-height: 1.2;color:#74767b;margin: 0;">
                                                {{item.name}}</p>
                                            <div flex="" class="attr" style="margin: 0;" :title="item.attr">
                                                <div class="attr-text">
                                                    规格：
                                                </div>
                                               <div>
                                                   <el-tag size="small" class="tag">
                                                       {{item.attr}}
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
                                         v-if="scope.row.detail.length === 1">
                                        <image style="width: 50px; height: 50px;margin-right: 10px"
                                               :src="scope.row.detail[0].cover_pic"></image>
                                        <div style="width: 170px;min-height: 50px">
                                            <p class="name"
                                            :title="scope.row.detail[0].name"
                                               style="font-size: 14px;line-height: 1.2;color:#74767b;margin: 0;">
                                                {{scope.row.detail[0].name}}</p>
                                            <div flex="" class="attr" style="margin: 0;" :title="scope.row.detail[0].attr">
                                                <div class="attr-text">
                                                    规格：
                                                </div>
                                               <div>
                                                   <el-tag size="small" class="tag">
                                                       {{scope.row.detail[0].attr}}
                                                   </el-tag>
                                               </div>
                                            </div>
                                            <p style="margin: 0;">
                                                数量
                                                <span style="color: #ff4544">{{scope.row.detail[0].num}}</span>
                                                件
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="user"
                                label="用户名"
                                width="180">
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
                                prop="turn_list"
                                width="550"
                                label="转赠过程">
                            <template slot-scope="scope">
                                <div flex="cross:center">
                                    <div flex="" class="gift-user">
                                        <image class="av" :src="scope.row.turn_list.gift_user.avatar"></image>
                                        <div style="margin-left: 10px;">
                                            <span class="name">{{scope.row.turn_list.gift_user.nickname}}</span>
                                            <div>
                                                <img src="statics/img/mall/ali.png"
                                                     v-if="scope.row.turn_list.gift_user.platform == 'aliapp'" alt="">
                                                <img src="statics/img/mall/wx.png"
                                                     v-else-if="scope.row.turn_list.gift_user.platform == 'wxapp'"
                                                     alt="">
                                                <img src="statics/img/mall/toutiao.png"
                                                     v-else-if="scope.row.turn_list.gift_user.platform == 'ttapp'"
                                                     alt="">
                                                <img src="statics/img/mall/baidu.png"
                                                     v-else-if="scope.row.turn_list.gift_user.platform == 'bdapp'"
                                                     alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <image class="left-image" :src="pic_url"></image>
                                    <div v-if="scope.row.turn_list.turn_num > 0" class="gift-user"
                                         style="text-align: center; line-height: 50px">
                                        转增{{scope.row.turn_list.turn_num}}人
                                    </div>
                                    <image class="left-image" v-if="scope.row.turn_list.turn_num > 0"
                                           :src="pic_url"></image>
                                    <div flex="" class="gift-user" v-if="scope.row.turn_list.parent_user">
                                        <image class="av" :src="scope.row.turn_list.parent_user.avatar"></image>
                                        <div style="margin-left: 10px;">
                                            <span class="name">{{scope.row.turn_list.parent_user.nickname}}</span>
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
                                    <image class="left-image" v-if="scope.row.turn_list.parent_user"
                                           :src="pic_url"></image>
                                    <div flex="" class="gift-user">
                                        <image class="av" :src="scope.row.turn_list.self_user.avatar"></image>
                                        <div style="margin-left: 10px;">
                                            <span class="name">{{scope.row.turn_list.self_user.nickname}}</span>
                                            <div>
                                                <img src="statics/img/mall/ali.png"
                                                     v-if="scope.row.turn_list.self_user.platform == 'aliapp'" alt="">
                                                <img src="statics/img/mall/wx.png"
                                                     v-else-if="scope.row.turn_list.self_user.platform == 'wxapp'"
                                                     alt="">
                                                <img src="statics/img/mall/toutiao.png"
                                                     v-else-if="scope.row.turn_list.self_user.platform == 'ttapp'"
                                                     alt="">
                                                <img src="statics/img/mall/baidu.png"
                                                     v-else-if="scope.row.turn_list.self_user.platform == 'bdapp'"
                                                     alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="状态"
                                prop="giftOrder"
                        >
                            <template slot-scope="scope">
                                <el-tag type="info" v-if="scope.row.giftOrder[0].order && scope.row.giftOrder[0].order.is_send == 0 && scope.row.giftOrder[0].order.is_confirm == 0">未发货</el-tag>
                                <el-tag  v-if="scope.row.giftOrder[0].order &&  scope.row.giftOrder[0].order.is_send == 1 && scope.row.giftOrder[0].order.is_confirm == 0">已发货</el-tag>
                                <el-tag  v-if="scope.row.giftOrder[0].is_refund == 1">已退款</el-tag>
                                <el-tag type="success"  v-if="scope.row.giftOrder[0].order && scope.row.giftOrder[0].order.is_confirm == 1">已收货</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="order_no"
                                label="订单号">
                        </el-table-column>
                        <el-table-column
                                prop="address_status"
                                label="地址填写">
                            <template slot-scope="scope">
                                <el-tag :type="scope.row.address_status === '未填写' ? 'danger' : scope.row.address_status === '部分填写' ? 'warning' : 'success'">
                                    {{scope.row.address_status}}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="created_at"
                                label="参与时间">
                        </el-table-column>
                        <el-table-column
                            fixed="right"
                            label="操作">
                            <template slot-scope="scope">
                                <el-button type="text" circle
                                           v-if="scope.row.giftOrder[0].order_id == 0 && scope.row.giftOrder[0].is_refund == 0"
                                           @click="refund(scope.row.id, scope.row)" size="mini">
                                    <el-tooltip class="item" effect="dark" content="退款" placement="top">
                                        <img src="statics/img/plugins/refund.png" alt="">
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
    const _currentPluginBaseUrl = `<?=$_currentPluginBaseUrl?>`;

    const app = new Vue({
        el: '#app',
        data() {
            return {
                pic_url: _currentPluginBaseUrl + `/img/gift-left.png`,

                listLoading: false,
                form: {},
                search: {
                    keyword: 'name',
                    keyword_1: '',
                    time: [],
                    start_date: '',
                    end_date: '',
                    page: 1,
                    gift_id: ''
                },
                tableData: [],
                pagination: {}
            }
        },

        created() {
            this.gift_id = getQuery('gift_id');
            this.request();
        },

        methods: {
            // 搜索
            searchOrder() {
                this.request();
            },

            async request() {
                this.listLoading = true;
                let search = this.search;
                if (this.search.time && this.search.time.length > 0) {
                    search.start_date = this.search.time[0];
                    search.end_date = this.search.time[1];
                } else {
                    search.start_date = '';
                    search.end_date = '';
                }
                if (this.gift_id && this.gift_id.length > 0) {
                    search.gift_id = this.gift_id;
                } else {
                    search.gift_id = '';
                }
                try {
                    const response = await request({
                        params: {
                            r: 'plugin/gift/mall/record/receive',
                            keyword: this.search.keyword,
                            keyword_1: this.search.keyword_1,
                            time: this.search.time,
                            start_date: this.search.start_date,
                            end_date: this.search.end_date,
                            page: this.search.page,
                            gift_id: this.search.gift_id
                        },
                        method: 'get'
                    });
                    this.listLoading = false;
                    if (response.data.code === 0) {
                        this.tableData = response.data.data.list;
                        this.pagination = response.data.data.pagination;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                } catch (err) {
                    throw new Error(err);
                }
            },

            pageChange(e) {
                this.search.page = e;
                this.request();
            },

            refund(id, data) {
                this.$confirm('确认对商品进行退款吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: `plugin/gift/mall/record/refund`,
                            id: id
                        },
                        method: 'get'

                    }).then(res => {
                        if (res.data.code === 0) {
                            data.giftOrder[0].is_refund = 1;
                            this.$message({
                                type: 'success',
                                message: '退款成功!'
                            });
                        } else {
                            this.$message.error(res.data.msg);
                        }
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消退款'
                    });
                });
            }
        }
    })
</script>
