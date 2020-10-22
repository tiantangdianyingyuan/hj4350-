<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-new-export-dialog');
?>
<style>

    .bottom-div {
        border-top: 1px solid #E3E3E3;
        position: fixed;
        bottom: 0;
        left: 200px;
        background-color: #ffffff;
        z-index: 999;
        padding: 0 20px;
        width: 100%;
    }

    .rules {
        padding: 20px;
        background-color: #F4F4F5;
        margin-bottom: 20px;
    }

    .rules.use-rules {
        background-color: #ecf5fe;
        margin-bottom: 0;
    }

    .rules.use-rules>div:first-of-type {
        color: #999999;
        font-size: 13px;
        margin-bottom: 4px;
    }
    .rules div {
        margin: 2px 0;
    }
    .save {
        margin: 12px 0;
    }

    .choose-item {
        height: 160px;
        width: 130px;
        margin-left: 40px;
        cursor: pointer;
        position: relative;
    }
    .choose-item .dialog {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 300;
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 6px;
    }
    .choose-item .dialog img {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        margin-top: -20px;
        margin-left: -20px;
        z-index: 311;
    }
    .choose-item img {
        height: 160px;
        width: 130px;
    }
    .choose-tip {
        margin-left: 44px;
        margin-top: 24px;
        color: #999999;
        width: 490px;
    }

    .dialog-choose-radio .input-item {
        margin-bottom: 20px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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

    .input-item .el-input-group__prepend {
        background-color: #fff;
    }

    .dialog-choose .el-table {
        max-height: 500px;
        overflow: auto;
    }

    .dialog-choose .el-table::before {
        height: 0;
    }

    .dialog-choose .dialog-goods-attr .el-table {
        max-height: 400px;
        overflow: auto;
    }

    .dialog-choose-radio .el-radio__label {
        display: none;
    }

    .expire-day {
        width: 160px;
        margin: 0 10px;
    }

    .height-input.el-input {
        height: 32px;
    }

    .height-input.el-input input {
        height: 32px;
    }

    .height-button {
        height: 32px;
    }
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .input-item {
        margin-left: 10px;
    }

    .table-info .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .item-box .label {
        margin-right: 10px;
        margin-left: 30px;
    }

    .item-box .el-select {
        width: 160px;
    }

    .time-box {
        margin: 0 15px;
    }
    .el-textarea .el-textarea__inner{
        resize: none;
    }

    .el-message-box__status+.el-message-box__message::after {
        content: '这将导致该码无法使用，并且不可恢复';
        color: #ff4544;
        margin-top: 10px;
    }

    .download {
        background-color: #f9f9f9;
        height: 50px;
        padding-left: 20px;
    }
    .table-page {
        width: 900px;
        padding: 10px 0;
        text-align: right;
        border: 1px solid #EBEEF5;
        margin-top: -1px;
    }

    .hidden-code {
        position: absolute;
        top: 100%;
        left: 100%;
        visibility: hidden;
    }
    .qr-code.el-tag {
        height: 44px;
        line-height: 44px;
        padding: 0 30px;
        font-size: 16px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="first" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/exchange/mall/library/list'})">兑换码管理</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="id > 0 && !log">编辑兑换码库</el-breadcrumb-item>
                <el-breadcrumb-item v-else-if="id > 0 && log">查看兑换详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新增兑换码库</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form style="padding-bottom: 77px;" @submit.native.prevent size="small" :rules="rules" ref="form" :model="form" label-width="130px">
            <el-card style="margin-bottom: 10px">
                <div slot="header">基础设置</div>
                <el-form-item label="兑换码库名称" prop="name">
                    <div style="width: 450px">
                        <el-input :disabled="id > 0" v-model="form.name" placeholder="最多输入15个字" maxlength="15"></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="备注说明" prop="remark">
                    <div style="width: 450px">
                        <el-input :disabled="id > 0" v-model="form.remark" type="textarea" :rows="4" :placeholder="id > 0 ? '' : '请输入备注说明'"></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="创建时间" v-if="id > 0" prop="created_at">
                    {{form.created_at}}
                </el-form-item>
                <el-form-item label="兑换规则" v-if="id > 0">
                    <el-button class="height-button" type="text" @click="lookRule">查看规则</el-button>
                </el-form-item>
            </el-card>
            <!-- 兑换码规则 -->
            <el-card v-if="id == 0">
                <div slot="header">兑换码规则<span style="color: #ff4544;font-size: 10px;margin-left: 10px;">创建后不可更改</span></div>
                <div class="rules" style="background-color: #ECF5FE">
                    <div>兑换码库创建后，后续生成的兑换码将按照下述规则执行；</div>
                    <div>兑换码库一经创建，将无法修改规则，请谨慎操作。</div>
                </div>
                <el-form-item label="兑换码有效期">
                    <el-radio v-model="form.expire_type" label="all">永久有效</el-radio>
                    <el-radio v-model="form.expire_type" label="fixed">固定时间</el-radio>
                    <el-radio v-model="form.expire_type" label="relatively">相对时间</el-radio>
                    <div style="margin-top: 10px;" v-if="form.expire_type == 'fixed'">
                        <el-date-picker
                                class="time-box"
                                size="small"
                                v-model="expire_time"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </div>
                    <div style="margin-top: 10px;" v-if="form.expire_type == 'relatively'" flex="dir:left cross:center">
                        <div>从兑换码生成时</div>
                        <div class="expire-day">
                            <el-input min="0" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '')" type="number" v-model="form.expire_start_day">
                                <template slot="append">天</template>
                            </el-input>
                        </div>
                        <div>至</div>
                        <div class="expire-day">
                            <el-input min="0" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '')" type="number" v-model="form.expire_end_day">
                                <template slot="append">天</template>
                            </el-input>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="兑换模式">
                    <el-radio v-model="form.mode" label="0">全部奖励品</el-radio>
                    <el-radio v-model="form.mode" label="1">任选一份奖励品</el-radio>
                </el-form-item>
                <el-form-item label="兑换码格式">
                    <el-radio v-model="form.code_format" label="english_num">英文数字组合</el-radio>
                    <el-radio v-model="form.code_format" label="num">纯数字组合</el-radio>
                </el-form-item>
                <el-form-item label="奖励品">
                    <div flex="dir:left">
                        <el-button v-if="form.rewards.length < 20" class="height-button" @click="chooseDialog=true;chooseType='goods'">添加奖励品</el-button>
                        <div style="margin-left: 20px">({{form.rewards.length}}/20)</div>
                    </div>
                    <el-table v-if="form.rewards.length > 0" :data="rewards" border style="width: 900px;margin-top: 20px">
                        <el-table-column width="120px" label="类型" props="type">
                            <template slot-scope="props">
                                <div v-if="props.row.type == 'goods'">商品</div>
                                <div v-if="props.row.type == 'coupon'">优惠券</div>
                                <div v-if="props.row.type == 'card'">卡券</div>
                                <div v-if="props.row.type == 'integral'">积分</div>
                                <div v-if="props.row.type == 'balance'">余额</div>
                                <div v-if="props.row.type == 'svip'">超级会员卡</div>
                            </template>
                        </el-table-column>
                        <el-table-column label="名称">
                            <template slot-scope="props">
                                <div v-if="props.row.type == 'goods'">
                                    <div flex="dir:left cross:center">
                                        <el-image style="height: 50px;width: 50px;margin-right: 10px;flex-shrink: 0" :src="props.row.goods_info.goodsWarehouse.cover_pic"></el-image>
                                        <div>
                                            <app-ellipsis :line="1">{{props.row.goods_info.name}}</app-ellipsis>
                                            <app-ellipsis :line="1">
                                                <span style="color: #999999;">{{props.row.goods_info.attr[0].attr_list[0].attr_group_name}}:{{props.row.goods_info.attr[0].attr_list[0].attr_name}}</span>
                                            </app-ellipsis>
                                        </div>
                                    </div>
                                    <div>价格：<span style="color: #ff4544">￥{{props.row.goods_info.attr[0].price}}</span></div>
                                    <div>库存：<span style="color: #ff4544">{{props.row.goods_info.attr[0].stock}}</span></div>
                                </div>
                                <div v-if="props.row.type == 'coupon'">
                                    <div>
                                        <app-ellipsis :line="1">{{props.row.coupon_info.name}}</app-ellipsis>
                                    </div>
                                    <div>最低消费金额：<span style="color: #ff4544">￥{{props.row.coupon_info.min_price}}</span></div>
                                    <div>优惠方式：
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.type == 2">优惠:{{props.row.coupon_info.sub_price}}元</span>
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.type == 1">{{props.row.coupon_info.discount}}折</span>
                                    </div>
                                    <div v-if="props.row.coupon_info.discount_limit">优惠上限：<span style="color: #ff4544">{{props.row.coupon_info.discount_limit}}</span></div>
                                    <div>适用范围：
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.appoint_type == 1">指定商品类目</span>
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.appoint_type == 2">指定商品</span>
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.appoint_type == 3">全场通用</span>
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.appoint_type == 4">当面付</span>
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.appoint_type == 5">礼品卡</span>
                                    </div>
                                    <div>有效时间：
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.expire_type == 1">
                                        领取{{props.row.coupon_info.expire_day}}天后过期
                                        </span>
                                        <span style="color: #ff4544" v-else-if="props.row.coupon_info.expire_type == 2">
                                        {{props.row.coupon_info.begin_time}} - {{props.row.coupon_info.end_time}}
                                        </span>
                                    </div>
                                    <div>数量：
                                        <span style="color: #ff4544" v-if="props.row.coupon_info.total_count == -1">无限制</span>
                                        <span style="color: #ff4544" v-else>{{props.row.coupon_info.total_count}}</span>
                                    </div>
                                </div>
                                <div v-if="props.row.type == 'card'">
                                    <div flex="dir:left cross:center">
                                        <app-image mode="aspectFill" :src="props.row.card_info.pic_url"></app-image>
                                        <div style="margin-left: 10px;">
                                            <app-ellipsis :line="1">{{props.row.card_info.name}}</app-ellipsis>
                                        </div>
                                    </div>
                                    <div>核销总次数：<span style="color: #ff4544">{{props.row.card_info.number}}</span></div>
                                    <div>有效时间：
                                        <span style="color: #ff4544" v-if="props.row.card_info.expire_type == 1">
                                        领取{{props.row.card_info.expire_day}}天后过期
                                        </span>
                                        <span style="color: #ff4544" v-else-if="props.row.card_info.expire_type == 2">
                                        {{props.row.card_info.begin_time}} - {{props.row.card_info.end_time}}
                                        </span>
                                    </div>
                                    <div>数量：
                                        <span style="color: #ff4544" v-if="props.row.card_info.total_count == -1">无限制</span>
                                        <span style="color: #ff4544" v-else>{{props.row.card_info.total_count}}</span>
                                    </div>
                                </div>
                                <div style="width: 250px" v-if="props.row.type == 'integral'">
                                    <el-input class="height-input" min="0" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '')" type="number" v-model="props.row.integral_num">
                                        <template slot="append">积分</template>
                                    </el-input>
                                </div>
                                <div style="width: 250px" v-if="props.row.type == 'balance'">
                                    <el-input class="height-input" min="0" oninput="if(this.value)this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" type="number" v-model="props.row.balance">
                                        <template slot="append">元</template>
                                    </el-input>
                                </div>
                                <div v-if="props.row.type == 'svip'">
                                    <div>
                                        <app-ellipsis :line="1">{{props.row.svip_info.name}}</app-ellipsis>
                                    </div>
                                    <div>折扣：<span style="color: #ff4544">{{svip.discount}}折</span></div>
                                    <div>有效期：<span style="color: #ff4544">{{props.row.svip_info.expire_day}}天</span></div>
                                    <div>价格：<span style="color: #ff4544">￥{{props.row.svip_info.price}}</span></div>
                                    <div>库存：<span style="color: #ff4544">{{props.row.svip_info.num != 0 ? props.row.svip_info.num : '告罄'}}</span></div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column width="200px" label="数量" props="number">
                            <template slot-scope="props">
                                <div v-if="props.row.type == 'goods'">
                                    <el-input class="height-input" min="0" @blur="checkInputNumber('goods',props.$index)" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '')" type="number" v-model="props.row.goods_num">
                                    </el-input>
                                </div>
                                <div v-if="props.row.type == 'coupon'">
                                    <el-input class="height-input" min="0" @blur="checkInputNumber('coupon',props.$index)" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '')" type="number" v-model="props.row.coupon_num">
                                    </el-input>
                                </div>
                                <div v-if="props.row.type == 'card'">
                                    <el-input class="height-input" min="0" @blur="checkInputNumber('card',props.$index)" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '')" type="number" v-model="props.row.card_num">
                                    </el-input>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column width="120px" label="操作">
                            <template slot-scope="scope">
                                <el-button circle size="mini" type="text" @click="destroy(scope.$index)">
                                    <el-tooltip effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div v-if="form.rewards.length > 0" class="table-page">
                        <el-pagination @current-change="tablePage" :current-page="currentPage" :page-size="6" background layout="prev, pager, next" :total="form.rewards.length" :page-count="form.rewards.length > 12 ? 3 : form.rewards.length > 6 ? 2 : 1"></el-pagination>
                    </div>
                </el-form-item>
            </el-card>
            <!-- 兑换记录 -->
            <el-card v-if="id > 0 && log">
                <div slot="header">兑换记录</div>
                <div class="table-body">
                    <div flex="wrap:wrap cross:center" style="margin-bottom: 15px;">
                        <div>兑换渠道</div>
                        <div style="margin-left: 10px;" class="item-box" flex="dir:left cross:center">
                            <el-select size="small" v-model="logSearch.origin" @change='search' class="select">
                                <el-option key="0" label="全部" value="0"></el-option>
                                <el-option key="admin" label="后台兑换" value="admin"></el-option>
                                <el-option key="wxapp" label="微信小程序" value="wxapp"></el-option>
                                <el-option key="aliapp" label="支付宝小程序" value="aliapp"></el-option>
                                <el-option key="ttapp" label="抖音/头条小程序" value="ttapp"></el-option>
                                <el-option key="bdapp" label="百度小程序" value="bdapp"></el-option>
                            </el-select>
                        </div>
                        <div style="margin-left: 20px;width: auto;" class="input-item">
                            <el-input style="width: 360px" size="small" v-model="logSearch.keyword" placeholder="请输入搜索内容"  clearable
                                      @clear="search"
                                      @keyup.enter.native="search">
                                <el-select style="width: 100px" slot="prepend" v-model="logSearch.keyword_1">
                                    <el-option label="用户ID" :value="4"></el-option>
                                    <el-option label="昵称" :value="2"></el-option>
                                    <el-option label="兑换码" :value="8"></el-option>
                                </el-select>
                                <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                            </el-input>
                        </div>
                        <div style="margin-left: 30px;">兑换时间</div>
                        <el-date-picker
                                class="time-box"
                                size="small"
                                @change="search"
                                v-model="logSearch.r_raffled_at"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                        <app-new-export-dialog
                                text="导出表格"
                                title="表格导出"
                                :field_list='logField'
                                action_url="plugin/exchange/mall/library/record-log"
                                :directly="true"
                                :params="logSearch">
                        </app-new-export-dialog>
                    </div>
                    <el-table :header-cell-style="{'background-color': '#fff'}" class="table-info" :data="list" border style="width: 100%" v-loading="listLoading">
                        <el-table-column prop="code" label="兑换码" width="220"></el-table-column>
                        <el-table-column prop="origin" label="兑换渠道">
                            <template slot-scope="scope">
                                <div flex="dir:left cross:center" v-if="scope.row.origin == 'admin'">
                                    <img style="margin-right: 10px;" src="statics/img/mall/platform/backstage.png" alt="">
                                    <div>后台兑换</div>
                                </div>
                                <div flex="dir:left cross:center" v-if="scope.row.origin == 'wxapp'">
                                    <img style="margin-right: 10px;" src="statics/img/mall/wx.png" alt="">
                                    <div>微信小程序</div>
                                </div>
                                <div flex="dir:left cross:center" v-if="scope.row.origin == 'aliapp'">
                                    <img style="margin-right: 10px;" src="statics/img/mall/ali.png" alt="">
                                    <div>支付宝小程序</div>
                                </div>
                                <div flex="dir:left cross:center" v-if="scope.row.origin == 'baidu'">
                                    <img style="margin-right: 10px;" src="statics/img/mall/baidu.png" alt="">
                                    <div>百度小程序</div>
                                </div>
                                <div flex="dir:left cross:center" v-if="scope.row.origin == 'toutiao'">
                                    <img style="margin-right: 10px;" src="statics/img/mall/toutiao.png" alt="">
                                    <div>抖音/头条小程序</div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="user_id" label="用户ID" width="120">
                        </el-table-column>
                        <el-table-column prop="user" label="兑换用户">
                            <template slot-scope="scope">
                                <div flex="dir:left cross:center">
                                    <app-image mode="aspectFill" style="margin-right: 8px;flex-shrink: 0" :src="scope.row.avatar"></app-image>
                                    <div>{{scope.row.nickname}}</div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="rewards_text" label="兑换奖励">
                        </el-table-column>
                        <el-table-column prop="r_raffled_at" label="兑换时间" wdith="220"></el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button circle size="mini" @click="exchangeLog(scope.row)" style="margin-right: 20px" type="text">
                                    <el-tooltip effect="dark" content="查看兑换记录" placement="top">
                                        <img src="statics/img/mall/order/detail.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div flex="main:right cross:center" style="margin-top: 20px;">
                        <div v-if="pagination.page_count > 0">
                            <el-pagination
                                    @current-change="changePage"
                                    background
                                    :current-page="pagination.current_page"
                                    layout="prev, pager, next"
                                    :page-count="pagination.page_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>
            </el-card>
            <!-- 兑换码管理 -->
            <el-card v-if="id > 0 && !log">
                <div slot="header" style="width: 100%;" flex="main:justify cross:center">
                    <div>兑换码管理</div>
                    <el-button @click="appendDialog=true;num=''" size="small" type="primary">批量生成兑换码</el-button>
                </div>
                <div class="rules use-rules">
                    <div>使用说明：</div>
                    <div>礼品卡关联兑换码库后，每售出一张礼品卡，则在此处自动生成一个新的兑换码，不需要您主动生成兑换码。</div>
                    <div>批量生成的兑换码，生成方式为“手动”，系统无法直接发售，需要您自行导出使用，可用来帮助线下发售实体卡片或者作为电子卡密出售。</div>
                </div>
                <div class="table-body">
                    <div flex="wrap:wrap cross:center" style="margin-bottom: 15px;">
                        <div>搜索</div>
                        <div class="input-item">
                            <el-input @keyup.enter.native="search" size="small" placeholder="请输入兑换码" v-model="codeSearch.keyword" clearable @clear="search">
                                <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                            </el-input>
                        </div>
                        <div class="item-box" flex="dir:left cross:center">
                            <div class="label">生成方式</div>
                            <el-select size="small" v-model="codeSearch.type" @change='search' class="select">
                                <el-option key="-1" label="全部" :value="-1"></el-option>
                                <el-option key="0" label="手动" :value="0"></el-option>
                                <el-option key="1" label="礼品卡" :value="1"></el-option>
                            </el-select>
                        </div>
                        <div class="item-box" flex="dir:left cross:center">
                            <div class="label">状态</div>
                            <el-select size="small" v-model="codeSearch.status" @change='search' class="select">
                                <el-option key="-2" label="全部" :value="-2"></el-option>
                                <el-option key="1" label="可用" :value="1"></el-option>
                                <el-option key="2" label="已兑换" :value="2"></el-option>
                                <el-option key="-1" label="过期" :value="-1"></el-option>
                                <el-option key="0" label="已禁用" :value="0"></el-option>
                            </el-select>
                        </div>
                        <div style="margin-left: 30px;">生成日期</div>
                        <el-date-picker
                                class="time-box"
                                size="small"
                                @change="search"
                                v-model="codeSearch.created_at"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                        <app-new-export-dialog
                                text="导出表格"
                                title="表格导出"
                                :field_list='codeField'
                                action_url="plugin/exchange/mall/code/list"
                                :directly="true"
                                :params="codeSearch">
                        </app-new-export-dialog>
                    </div>
                    <div flex="cross:center" class="download">
                        <el-button @click="allDownload" size="mini">下载二维码</el-button>
                    </div>
                    <el-table class="table-info" :data="list" border style="width: 100%" v-loading="listLoading && !exchangeVisible" @selection-change="codeChange">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column prop="code" label="兑换码" width="280">
                            <template slot-scope="scope">
                                <span :id="'a'+scope.row.code">{{scope.row.code}}</span>
                                <img class="hidden-code" :id="'id'+scope.row.id">
                                <img class="hidden-code" :id="'qr'+scope.row.id" :src="scope.row.qrcode_url">
                            </template>
                        </el-table-column>
                        <el-table-column prop="type" label="生成方式">
                            <template slot-scope="scope">
                                {{scope.row.type == 0 ? '手动':'礼品卡'}}
                            </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="生成时间" width="260"></el-table-column>
                        <el-table-column prop="validity_type" label="有效期" width="320">
                            <template slot-scope="scope">
                                <div v-if="scope.row.validity_type == 'all'">永久</div>
                                <div style="width: 160px" v-else>{{scope.row.valid_start_time}}至{{scope.row.valid_end_time}}</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="状态">
                            <template slot-scope="scope">
                                <el-tag v-if="scope.row.status == 1" size="small" type="success">可用</el-tag>
                                <el-tag v-if="scope.row.status == 2" size="small" type="info">已兑换</el-tag>
                                <el-tag v-if="scope.row.status == -1" size="small" type="warning">已过期</el-tag>
                                <el-tag v-if="scope.row.status == 0" size="small" type="danger">已禁用</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button class="copy-btn" circle size="mini" type="text" data-clipboard-action="copy" :data-clipboard-target="'#a' + scope.row.code">
                                    <el-tooltip effect="dark" content="复制" placement="top">
                                        <img src="statics/img/plugins/copy.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="ban(scope.row)" v-if="scope.row.status == 1" circle size="mini" type="text">
                                    <el-tooltip effect="dark" content="禁用" placement="top">
                                        <img src="statics/img/mall/order/cancel.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="lookDetail(scope.row)" v-if="scope.row.status == 2" circle size="mini" type="text">
                                    <el-tooltip effect="dark" content="兑换详情" placement="top">
                                        <img src="statics/img/mall/detail.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="openQr(scope.row)" v-if="scope.row.status == 1 || scope.row.status == 0" circle size="mini" type="text">
                                    <el-tooltip effect="dark" content="查看二维码" placement="top">
                                        <img src="statics/img/plugins/look.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="down(scope.row)" v-if="scope.row.status == 1 || scope.row.status == 0" circle size="mini" type="text">
                                    <el-tooltip effect="dark" content="下载二维码" placement="top">
                                        <img src="statics/img/plugins/download.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div flex="main:right cross:center" style="margin-top: 20px;">
                        <div v-if="pagination.page_count > 0">
                            <el-pagination
                                    @current-change="changePage"
                                    background
                                    :current-page="pagination.current_page"
                                    layout="prev, pager, next"
                                    :page-count="pagination.page_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>
            </el-card>
        </el-form>
    </el-card>
    <!-- 保存按钮 -->
    <div class="bottom-div" flex="cross:center" v-if="id == 0">
        <el-button :loading="saveLoading" type="primary" size="small" class="save"  @click="saveEdit('form')">保存</el-button>
    </div>
    <!-- 选择奖励品 -->
    <el-dialog title="选择奖励品类型" :visible.sync="chooseDialog" width="590px">
        <div flex="dir:left cross:center" style="margin-bottom: 24px;" v-for="(item,index) in reward_type" :key="index">
            <div class="choose-item" @click="chooseType = row.id" v-for="row in item">
                <div class="dialog" v-if="chooseType == row.id">
                    <img src="statics/img/mall/goods/finish-2.png" alt="">
                </div>
                <img :src="row.img" alt="">
            </div>
        </div>
        <div class="choose-tip">注：商品、优惠券、卡券、超级会员卡类型的奖励，请保证库存充足，否则买家兑换时，可能会造成奖励无法发放的情况。</div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="chooseDialog = false">取 消</el-button>
            <el-button size="small" type="primary" @click="open">确 定</el-button>
        </span>
    </el-dialog>
    <!-- 选择具体奖励品 -->
    <el-dialog class="dialog-choose" :title="title" :visible.sync="listDialog" width="50%">
        <!-- 商品列表 -->
        <div v-if="chooseType === 'goods' && !setGoods" class="dialog-goods-list">
            <div style="margin-bottom: 27px;">
                <el-input size="small" v-model="keyword"  @change="search" @clear="search" placeholder="根据名称和ID搜索" clearable autocomplete="off">
                    <template slot="append">
                        <el-button @click="search">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <el-table v-loading="listLoading" :data="list" style="width: 100%">
                <el-table-column align="center" width="120px" label="ID" props="id">
                    <template slot-scope="props">
                        <el-radio-group v-model="radioSelection" @change="handleSelectionChange(props.row)">
                            <el-radio :disabled="props.row.select" :label="props.row.id"></el-radio>
                        </el-radio-group>
                    </template>
                </el-table-column>
                <el-table-column label="名称">
                    <template slot-scope="props">
                        <div flex="dir:left cross:center">
                            <el-image v-if="props.row.goodsWarehouse" style="height: 50px;width: 50px;margin-right: 10px;flex-shrink: 0" :src="props.row.goodsWarehouse.cover_pic"></el-image>
                            <app-ellipsis :line="2">{{props.row.name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    v-if="pagination.page_count > 0"
                    style="display: flex;justify-content: center;"
                    @current-change="changePage"
                    background
                    :current-page="pagination.current_page"
                    layout="prev, pager, next"
                    :page-count="pagination.page_count">
            </el-pagination>
        </div>
        <!-- 商品规格 -->
        <div class="dialog-choose-radio" v-else-if="chooseType == 'goods' && setGoods">
            <el-table :data="chooseGoods" border style="width: 100%">
                <el-table-column label="商品名称">
                    <template slot-scope="props">
                        <div flex="dir:left cross:center">
                            <el-image style="height: 50px;width: 50px;margin-right: 10px;" :src="props.row.goodsWarehouse.cover_pic"></el-image>
                            <app-ellipsis :line="2">{{props.row.name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <el-table :data="chooseGoods[0].attr" border style="width: 100%;margin-top: 30px">
                <el-table-column align="center" width="60px">
                    <template slot-scope="props">
                        <el-radio @change="handleSelectionChange(props.row)" v-model="attr_id" :label="props.row.id"></el-radio>
                    </template>
                </el-table-column>
                <el-table-column
                        v-for="(item, index) in chooseGoods[0].attr_groups"
                        :key="item.id"
                        :prop="'attr_list['+index+'].attr_name'"
                        :label="item.attr_group_name">
                </el-table-column>
                <el-table-column label="原价">
                    <template slot-scope="scope">
                        ￥{{scope.row.price}}
                    </template>
                </el-table-column>
                <el-table-column label="库存" prop="stock"></el-table-column>
                <el-table-column label="数量" prop="number">
                    <template slot-scope="scope">
                        <el-input @blur="checkNumber(scope.$index)" min="0" :max="scope.row.stock" oninput="if(this.value)this.value = this.value.replace(/[^0-9]/g, '');" type="number" v-model="scope.row.number">
                        </el-input>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <!-- 优惠券 -->
        <div class="dialog-choose-radio" v-else-if="chooseType == 'coupon'">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入优惠券名称" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="listLoading" border :data="list" style="width: 100%">
                <el-table-column width="60px">
                    <template slot-scope="props">
                        <el-radio @change="handleSelectionChange(props.row)" v-model="coupon_id" :label="props.row.id"></el-radio>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="优惠券名称">
                </el-table-column>
                <el-table-column width='100' prop="min_price" label="最低消费金额（元）">
                </el-table-column>
                <el-table-column prop="type" label="优惠方式">
                    <template slot-scope="scope">
                        <div v-if="scope.row.type == 2">优惠:{{scope.row.sub_price}}元</div>
                        <div v-if="scope.row.type == 1">{{scope.row.discount}}折</div>
                        <div v-if="scope.row.discount_limit && scope.row.type == 1">优惠上限:{{scope.row.discount_limit}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="appoint_type" label="使用范围">
                    <template slot-scope="scope">
                        <span v-if="scope.row.appoint_type == 1">指定商品类目</span>
                        <span v-if="scope.row.appoint_type == 2">指定商品</span>
                        <span v-if="scope.row.appoint_type == 3">全场通用</span>
                        <span v-if="scope.row.appoint_type == 4">当面付</span>
                        <span v-if="scope.row.appoint_type == 5">礼品卡</span>
                    </template>
                </el-table-column>
                <el-table-column width='170' prop="expire_type" label="有效时间">
                    <template slot-scope="scope">
                        <span v-if="scope.row.expire_type == 1">
                        领取{{scope.row.expire_day}}天后过期
                    </span>
                        <span v-else-if="scope.row.expire_type == 2">
                        {{scope.row.begin_time}} - {{scope.row.end_time}}
                    </span>
                    </template>
                </el-table-column>
                <el-table-column width='150' prop="total_count" label="数量">
                    <template slot-scope="scope">
                        <div v-if="scope.row.total_count == -1">
                            <div>总数量：无限制</div>
                            <div>剩余发放数：无限制</div>
                        </div>
                        <div v-else>
                            <div>总数量：{{scope.row.count}}</div>
                            <div>剩余发放数：{{scope.row.total_count}}</div>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    v-if="pagination.page_count > 0"
                    style="display: flex;justify-content: center;"
                    @current-change="changePage"
                    background
                    :current-page="pagination.current_page"
                    layout="prev, pager, next"
                    :page-count="pagination.page_count">
            </el-pagination>
        </div>
        <!-- 卡券 -->
        <div class="dialog-choose-radio" v-else-if="chooseType == 'card'">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入卡券名称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="listLoading" border :data="list" style="width: 100%">
                <el-table-column width="60px">
                    <template slot-scope="props">
                        <el-radio @change="handleSelectionChange(props.row)" v-model="card_id" :label="props.row.id"></el-radio>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="卡券名称">
                </el-table-column>
                <el-table-column label="核销总次数" width="100" prop="number"></el-table-column>
                <el-table-column label="卡券图标" width="100">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.pic_url"></app-image>
                    </template>
                </el-table-column>
                <el-table-column label="有效期" width="320">
                    <template slot-scope="scope">
                        <div v-if="scope.row.expire_type == 1">发放之日起<span
                                    class="text-color">{{scope.row.expire_day}}</span>天内
                        </div>
                        <div v-else>
                            <span class="text-color">{{scope.row.begin_time}}</span>
                            - <span class="text-color">{{scope.row.end_time}}</span>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    v-if="pagination.page_count > 0"
                    style="display: flex;justify-content: center;"
                    @current-change="changePage"
                    background
                    :current-page="pagination.current_page"
                    layout="prev, pager, next"
                    :page-count="pagination.page_count">
            </el-pagination>
        </div>
        <!-- 超级会员卡 -->
        <div class="dialog-choose-radio" v-else-if="chooseType == 'svip'">
            <el-table v-loading="listLoading" border :data="list" style="width: 100%">
                <el-table-column width="60px">
                    <template slot-scope="props">
                        <el-radio v-model="svip_id" :label="props.row.id"></el-radio>
                    </template>
                </el-table-column>
                <el-table-column width="160px" prop="name" label="子卡标题">
                </el-table-column>
                <el-table-column prop="price" label="价格">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.price}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="expire_day" label="有效期">
                    <template slot-scope="scope">
                        <div>{{scope.row.expire_day}}天</div>
                    </template>
                </el-table-column>
                <el-table-column label="折扣">
                    <template slot-scope="scope">
                        <div>{{svip.discount}}</div>
                    </template>
                </el-table-column>
                <el-table-column width="280px" label="赠送">
                    <template slot-scope="scope">
                        <app-ellipsis v-if="scope.row.send_integral_num > 0" :line="1">赠送积分{{scope.row.send_integral_num}}</app-ellipsis>
                        <app-ellipsis v-if="scope.row.send_balance > 0" :line="1">赠送余额{{scope.row.send_balance}}</app-ellipsis>
                        <app-ellipsis v-for="item in scope.row.coupons" :key="item.id" :line="1">赠送优惠券{{item.send_num}}张 {{item.name}}</app-ellipsis>
                        <app-ellipsis v-for="item in scope.row.cards" :key="item.id" :line="1">赠送卡券{{item.send_num}}张 {{item.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="库存">
                    <template slot-scope="scope">
                        <div>{{scope.row.num != 0 ? scope.row.num : '告罄'}}</div>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    v-if="pagination.page_count > 0"
                    style="display: flex;justify-content: center;"
                    @current-change="changePage"
                    background
                    :current-page="pagination.current_page"
                    layout="prev, pager, next"
                    :page-count="pagination.page_count">
            </el-pagination>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button v-if="chooseType == 'goods' && setGoods" size="small" @click="setGoods=false;title='选择商品'">取 消</el-button>
            <el-button v-else size="small" @click="cancel">取 消</el-button>
            <el-button v-if="chooseType == 'goods' && !setGoods" size="small" type="primary" @click="settingGoods">确 定</el-button>
            <el-button v-else size="small" type="primary" @click="addRewards">确 定</el-button>
        </span>
    </el-dialog>

    <el-dialog title="批量生成兑换码" :visible.sync="appendDialog" width="30%">
        <el-form @submit.native.prevent size="small" label-width="100px">
            <el-form-item label="生成数量" prop="num">
                <el-input v-model="num" min="0" oninput="if(this.value.length>3)this.value=1000;this.value = this.value.replace(/[^0-9]/g, '')" type="number" :max="1000">
                    <template slot="append">条</template>
                </el-input>
                <div style="color: #999;font-size: 12px;">单次最多同时生成1000条</div>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="appendDialog = false">取 消</el-button>
            <el-button :loading="appendLoading" size="small" type="primary" @click="append">确 定</el-button>
        </span>
    </el-dialog>

    <el-dialog :title="!exchangeVisible ? '查看兑换规则':'兑换详情'" :visible.sync="dialogVisible" width="960px">
        <div v-if="exchangeVisible && !dialogLoading" style="text-align: center;">
            <div style="margin: 20px;margin-top: 0">
                <el-tag class="qr-code">{{detail.code}}</el-tag>
            </div>
        </div>
        <el-form v-loading="dialogLoading" @submit.native.prevent size="small" ref="form" label-width="130px">
            <el-form-item v-if="exchangeVisible" label="兑换时间" prop="r_raffled_at">
                <div>{{detail.r_raffled_at}}</div>
            </el-form-item>
            <el-form-item v-if="exchangeVisible" label="所属平台" prop="origin">
                <div flex="dir:left cross:center" v-if="detail.origin == 'admin'">
                    <img style="margin-right: 10px;" src="statics/img/mall/platform/backstage.png" alt="">
                    <div>后台兑换</div>
                </div>
                <div flex="dir:left cross:center" v-if="detail.origin == 'wxapp'">
                    <img style="margin-right: 10px;" src="statics/img/mall/wx.png" alt="">
                    <div>微信小程序</div>
                </div>
                <div flex="dir:left cross:center" v-if="detail.origin == 'aliapp'">
                    <img style="margin-right: 10px;" src="statics/img/mall/ali.png" alt="">
                    <div>支付宝小程序</div>
                </div>
                <div flex="dir:left cross:center" v-if="detail.origin == 'bdapp'">
                    <img style="margin-right: 10px;" src="statics/img/mall/baidu.png" alt="">
                    <div>百度小程序</div>
                </div>
                <div flex="dir:left cross:center" v-if="detail.origin == 'ttapp'">
                    <img style="margin-right: 10px;" src="statics/img/mall/toutiao.png" alt="">
                    <div>抖音/头条小程序</div>
                </div>
            </el-form-item>
            <el-form-item v-if="exchangeVisible" label="兑换用户" prop="nickname">
                <div flex="dir:left cross:center">
                    <app-image mode="aspectFill" style="margin-right: 8px;flex-shrink: 0" :src="detail.avatar"></app-image>
                    <div>{{detail.nickname}}</div>
                </div>
            </el-form-item>
            <el-form-item v-if="exchangeVisible && detail.order_name" label="姓名" prop="order_name">
                <div>{{detail.order_name}}</div>
            </el-form-item>
            <el-form-item v-if="exchangeVisible && detail.order_mobile" label="手机号" prop="order_mobile">
                <div>{{detail.order_mobile}}</div>
            </el-form-item>
            <el-form-item v-if="!exchangeVisible" label="兑换有效期" prop="expire_type">
                <div v-if="form.expire_type == 'all'">永久（永久）</div>
                <div v-if="form.expire_type == 'fixed'">固定时间（{{form.expire_start_time}}至{{form.expire_end_time}}）</div>
                <div v-if="form.expire_type == 'relatively'">相对时间（从兑换码生成时第{{form.expire_start_day}}天至第{{form.expire_end_day}}天）</div>
            </el-form-item>
            <el-form-item v-if="!exchangeVisible" label="兑换模式" prop="mode">
                <div v-if="form.mode == 0">全部奖励品</div>
                <div v-if="form.mode == 1">任选一份奖励品</div>
            </el-form-item>
            <el-form-item v-if="!exchangeVisible" label="兑换码格式" prop="code_format">
                <div v-if="form.code_format == 'english_num'">英文数字组合</div>
                <div v-if="form.code_format == 'num'">纯数字组合</div>
            </el-form-item>
            <el-form-item :label="!exchangeVisible ? '奖励品':'兑换奖励'" prop="rewards">
                <el-table class="table-info" :data="rewards" border style="width: 790px">
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
                <div style="width: 790px" v-if="form.rewards.length > 0 && !exchangeVisible" class="table-page">
                    <el-pagination @current-change="tablePage" :current-page="currentPage" :page-size="6" background layout="prev, pager, next" :total="form.rewards.length" :page-count="form.rewards.length > 12 ? 3 : form.rewards.length > 6 ? 2 : 1"></el-pagination>
                </div>
                <div style="width: 790px" v-if="detail.rewards.length > 0 && exchangeVisible" class="table-page">
                    <el-pagination @current-change="tablePage" :current-page="currentPage" :page-size="6" background layout="prev, pager, next" :total="detail.rewards.length" :page-count="detail.rewards.length > 12 ? 3 : detail.rewards.length > 6 ? 2 : 1"></el-pagination>
                </div>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogVisible = false">取 消</el-button>
            <el-button size="small" type="primary" @click="dialogVisible = false">确 定</el-button>
        </span>
    </el-dialog>

    <el-dialog title="查看二维码" :visible.sync="qrVisible" width="30%">
        <div style="text-align: center;">
            <div style="margin: 20px;margin-top: 0">
                <el-tag class="qr-code">{{qrDetail.code}}</el-tag>
            </div>
            <div>
                <img width="200" height="200" :src="qrDetail.qrcode_url" alt="">
            </div>
            <div>
                <img id="code" style="height: 34px;margin-top: 20px"/>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="qrVisible = false">取 消</el-button>
            <el-button size="small" type="primary" @click="download">下 载</el-button>
        </span>
    </el-dialog>
</div>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script src="<?=Yii::$app->request->baseUrl?>/statics/js/JsBarcode.all.min.js"></script>
<script src="<?=Yii::$app->request->baseUrl?>/statics/js/jszip.min.js"></script>
<script src="<?=Yii::$app->request->baseUrl?>/statics/js/FileSaver.min.js"></script>
<script>
    var clipboard = new Clipboard('.copy-btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });
    const app = new Vue({
        el: '#app',
        data() {
            return {
                logField: ['code','origin', 'user_id','nickname','order_name','order_mobile','rewards_text','r_raffled_at'],
                codeField: ['code','type','created_at','validity_type','status'],
                reward_type: [
                    [
                        {id: 'goods', img: './../plugins/exchange/assets/img/goods.png'},
                        {id: 'coupon', img: './../plugins/exchange/assets/img/coupon.png'},
                        {id: 'card', img: './../plugins/exchange/assets/img/card.png'},
                    ],
                    [
                        {id: 'integral', img: './../plugins/exchange/assets/img/point.png'},
                        {id: 'balance', img: './../plugins/exchange/assets/img/balance.png'},
                    ]
                ],
                codeShow: true,
                qrVisible: false,
                dialogVisible: false,
                exchangeVisible: false,
                chooseDialog: false,
                appendLoading: false,
                dialogLoading: false,
                listDialog: false,
                appendDialog: false,
                saveLoading: false,
                setGoods: false,
                listLoading: false,
                first: true,
                log: false,
                chooseType: 'goods',
                chooseGoods: [],
                expire_time: [],
                chooseQrList: [],
                qrList: [],
                created_at: [],
                title: '选择商品',
                url: 'mall/goods/index',
                keyword: '',
                platform: '0',
                num: '',
                currentPage: 1,
                id: 0,
                type: -1,
                status: -2,
                radioSelection: 0,
                attr_id: 0,
                svip_id: 0,
                coupon_id: 0,
                card_id: 0,
                list: [],
                svip: {},
                pagination: {
                    page_count: 0
                },
                detail: {
                    rewards: []
                },
                qrDetail: {},
                form: {
                    name: '',
                    remark: '',
                    expire_type: 'all',
                    mode: '0',
                    code_format: 'english_num',
                    rewards: []
                },
                logSearch: {
                    library_id: '',
                    origin: '0',
                    keyword: '',
                    r_raffled_at: [],
                    keyword_1: 4,
                },
                codeSearch: {
                    library_id: '',
                    type: -1,
                    code: '',
                    status: -2,
                    created_at: [],
                },
                rewards: [],
                rules: {
                    name: [
                        { required: true, message: '请输入兑换码库名称', trigger: 'blur' }
                    ],
                },
            };
        },
        watch: {
            list: {
                handler: function(data) {
                    if(this.id > 0 && !this.log) {
                        data.forEach((item)=>{
                            if(item.status == '1') {
                                setTimeout(()=>{
                                    JsBarcode('#id'+item.id,item.code, {
                                       format: "CODE128",//选择要使用的条形码类型
                                       width:3.5,//设置条之间的宽度
                                       height:157,//高度
                                       displayValue:false,//是否在条形码下方显示文字
                                       background:"#ffffff",//设置条形码的背景
                                       lineColor:"#000000",//设置条和文本的颜色
                                    })
                                })
                            }
                        })
                    }
                },
                deep: true,
                immediate: true
            },
        },
        methods: {
            getBase64Image(img,width = 0,height = 0) {
                const canvas = document.createElement('canvas');
                canvas.width = width ? width : img.width;
                canvas.height = height ? height : img.height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL().replace(/^data:image\/(png|jpg);base64,/, "");
                return dataURL;
            },
            allDownload() {
                if(this.qrList.length == 0) {
                    this.$message.error('请选择要下载的兑换码');
                    return false
                }
                var zip = new JSZip();
                this.qrList.forEach((item,index)=>{
                    let img = document.getElementById('id' + item.id);
                    let imgData = this.getBase64Image(img)
                    let qr = document.getElementById('qr' + item.id);
                    let qrData = this.getBase64Image(qr)
                    zip.file('条形码-' + item.code + '.png',imgData, {base64: true});
                    zip.file('二维码-' + item.code + '.png',qrData, {base64: true});
                    if(index == this.qrList.length -1 ) {
                        setTimeout(()=>{
                            zip.generateAsync({type:"blob"})
                            .then(function(content) {
                                saveAs(content, "二维码.zip");
                            });
                        })
                    }
                })
            },
            codeChange(val) {
                this.chooseQrList = val;
                this.qrList = [];
                for(let item of this.chooseQrList) {
                    if(item.status == '1') {
                        this.qrList.push(item)
                    }
                }
            },
            lookRule() {
                this.dialogVisible=true;
                this.exchangeVisible=false;
                this.currentPage=1
                this.rewards = this.form.rewards.slice(0,6);
            },
            lookDetail(row) {
                this.dialogVisible = true;
                this.exchangeVisible = true;
                this.currentPage = 1;
                this.detail = row;
                this.detail.origin = row.platform;
                this.detail.raffled_at = row.r_raffled_at;
                this.detail.rewards = row.r_rewards;
                this.rewards = this.detail.rewards.slice(0,6);
            },
            exchangeLog(row) {
                this.dialogVisible = true;
                this.exchangeVisible = true;
                this.currentPage = 1;
                this.detail = row;
                this.rewards = this.detail.rewards.slice(0,6);
            },
            download() {
                var alink = document.createElement("a");
                alink.href = this.qrDetail.qrcode_url;
                alink.download = '二维码-' + this.qrDetail.code;
                alink.click();
                setTimeout(()=>{    
                    var img = document.getElementById('code')
                    var code = document.createElement("a");
                    code.href = img.src;
                    code.download = '条形码-' + this.qrDetail.code;
                    code.click();
                    this.qrVisible = false;
                })
            },
            down(item) {
                this.codeShow = false;
                var alink = document.createElement("a");
                alink.href = item.qrcode_url;
                alink.download = '二维码-' + item.code;
                alink.click();
                setTimeout(()=>{
                    var img = document.getElementById('id' + item.id)
                    var code = document.createElement("a");
                    code.href = img.src;
                    code.download = '条形码-' + item.code;
                    code.click();
                })
            },
            openQr(item) {
                this.qrVisible = true;
                this.qrDetail = item;
                setTimeout(()=>{
                    JsBarcode('#code',item.code, {
                       format: "CODE128",//选择要使用的条形码类型
                       width:3.5,//设置条之间的宽度
                       height:157,//高度
                       displayValue:false,//是否在条形码下方显示文字
                       background:"#ffffff",//设置条形码的背景
                       lineColor:"#000000",//设置条和文本的颜色
                    })
                })
            },
            checkInputNumber(type,index) {
                if(type == 'goods') {
                    if(this.rewards[index].goods_num) {
                        if(+this.rewards[index].goods_num > +this.rewards[index].goods_info.attr[0].stock) {
                            this.$message.error('数量不得大于商品库存');
                            this.rewards[index].goods_num = this.rewards[index].goods_info.attr[0].stock
                        }
                    }
                }
                if(type == 'coupon') {
                    if(this.rewards[index].coupon_num) {
                        if(+this.rewards[index].coupon_info.total_count > -1 && +this.rewards[index].coupon_num > +this.rewards[index].coupon_info.total_count) {
                            this.$message.error('数量不得大于优惠券剩余库存');
                            this.rewards[index].coupon_num = this.rewards[index].coupon_info.total_count
                        }
                    }
                }
                if(type == 'card') {
                    if(this.rewards[index].card_num) {
                        if(+this.rewards[index].card_info.total_count > -1 && +this.rewards[index].card_num > +this.rewards[index].card_info.total_count) {
                            this.$message.error('数量不得大于卡券剩余库存');
                            this.rewards[index].card_num = this.rewards[index].card_info.total_count
                        }
                    }
                }
            },
            destroy(index) {
                let idx = index + (this.currentPage-1)*6
                this.rewards.splice(index,1);
                this.form.rewards.splice(idx ,1);
                if(this.currentPage > 1 && this.form.rewards.length%6 == 0 && this.currentPage > this.form.rewards.length/6) {
                    this.currentPage--;
                    this.rewards = this.form.rewards.slice((this.currentPage-1)*6,(this.currentPage-1)*6+6);
                }
            },
            tablePage(page) {
                this.currentPage = page;
                if(this.exchangeVisible) {
                    this.rewards = this.detail.rewards.slice((page-1)*6,(page-1)*6+6);
                }else {
                    this.rewards = this.form.rewards.slice((page-1)*6,(page-1)*6+6);
                }
            },
            // 禁用兑换码
            ban(row) {
                this.$confirm('禁用该条兑换码, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/exchange/mall/code/ban'
                        },
                        data: {
                            id: row.id
                        },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.listLoading = true;
                            this.getCode();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    })
                })
            },
            // 生成兑换码
            append() {
                if(this.num > 0) {
                    this.appendLoading = true;
                    this.num = this.num > 1000 ? 1000 : this.num
                    request({
                        params: {
                            r: 'plugin/exchange/mall/code/append'
                        },
                        data: {
                            library_id: this.id,
                            num: this.num
                        },
                        method: 'post'
                    }).then(e => {
                        this.appendLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.appendDialog = false;
                            this.listLoading = true;
                            this.getCode();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    })
                }else {
                    this.$message({
                        message: '请输入生成数量',
                        type: 'warning'
                    });
                }
            },
            // 取消
            cancel() {
                this.listDialog = false;
                this.chooseDialog = false;
                this.setGoods = false;
                this.attr_id = 0;
                this.coupon_id = 0;
                this.card_id = 0;
                this.svip_id = 0;
                this.chooseType = '';
                this.list = [];
                this.pagination = {
                    page_count: 0
                }
            },
            // 添加奖励品
            addRewards() {
                let para;
                if(this.chooseType == 'goods') {
                    if(this.attr_id > 0) {
                        let goods = this.chooseGoods[0];
                        let num = 0;
                        for(let item of goods.attr) {
                            if(item.id == this.attr_id) {
                                if(!item.number) {
                                    this.$message({
                                        message: '请填写数量',
                                        type: 'warning'
                                    });
                                    return false;
                                }
                                if(item.number == 0) {
                                    this.$message({
                                        message: '数量不能为零',
                                        type: 'warning'
                                    });
                                    return false;
                                }
                                num = item.number
                                goods.attr = [item];
                                break;
                            }
                        }
                        para = {
                            name: '商品',
                            type: 'goods',
                            goods_id: goods.id,
                            goods_num: num,
                            attr_id: this.attr_id,
                            goods_info: goods
                        }
                    }else {
                        this.$message({
                            message: '请选择一个规格',
                            type: 'warning'
                        });
                        return false;
                    }
                }else if(this.chooseType == 'coupon') {
                    let coupon;
                    for(let item of this.list) {
                        if(item.id == this.coupon_id) {
                            coupon = item;
                            break;
                        }
                    }
                    para = {
                        name: '优惠券',
                        type: 'coupon',
                        coupon_id: coupon.id,
                        coupon_num: coupon.total_count == 0? 0 : 1,
                        coupon_info: coupon
                    }
                }else if(this.chooseType == 'card') {
                    let card;
                    for(let item of this.list) {
                        if(item.id == this.card_id) {
                            card = item;
                            break;
                        }
                    }
                    para = {
                        name: '卡券',
                        type: 'card',
                        card_id: this.card_id,
                        card_num: card.total_count == 0? 0 : 1,
                        card_info: card
                    }
                }else if(this.chooseType == 'integral') {
                    para = {
                        name: '积分',
                        type: 'integral',
                        integral_num: ''
                    }
                }else if(this.chooseType == 'balance') {
                    para = {
                        name: '余额',
                        type: 'balance',
                        balance: ''
                    }
                }else if(this.chooseType == 'svip') {
                    let svip;
                    for(let item of this.list) {
                        if(item.id == this.svip_id) {
                            svip = item;
                            break;
                        }
                    }
                    para = {
                        name: '超级会员卡',
                        type: 'svip',
                        child_id: this.svip_id,
                        svip_info: svip
                    }
                }
                this.form.rewards.unshift(para);
                this.currentPage = 1;
                this.rewards = this.form.rewards.slice((this.currentPage-1)*6,(this.currentPage-1)*6+6);
                this.cancel();
            },
            settingGoods() {
                if(this.chooseGoods.length > 0) {
                    this.setGoods = true;
                    this.title = '选择规格';
                    this.attr_id = this.chooseGoods[0].attr[0].id;
                }else {
                    this.$message({
                        message: '请选择一个商品',
                        type: 'warning'
                    });
                }
            },
            checkNumber(index) {
                console.log(this.chooseGoods[0].attr[index].number)
                if(this.chooseGoods[0].attr[index].number) {
                    if(+this.chooseGoods[0].attr[index].number > +this.chooseGoods[0].attr[index].stock) {
                        this.$message.error('数量不得大于规格库存');
                        this.chooseGoods[0].attr[index].number = +this.chooseGoods[0].attr[index].stock
                    }
                }
            },
            // 奖励品分页
            changePage(currentPage) {
                this.listLoading = true;
                this.list = [];
                let para = {
                    r: this.url,
                    keyword: this.keyword,
                    page: currentPage
                };
                if(this.id == 0) {
                    para.keyword = this.keyword
                    if(this.chooseType == 'goods') {
                        para.is_show_attr = 1;
                        para.search = {keyword: this.keyword};
                    }
                }
                if(this.id > 0) {
                    if(!this.log) {
                        para.library_id = this.id;
                        para.code = this.codeSearch.keyword;
                        para.created_at = this.codeSearch.created_at;
                        if(this.codeSearch.type > -1) {
                            para.type = this.codeSearch.type
                        }
                        if(this.codeSearch.status > -2) {
                            para.status = this.codeSearch.status
                        }
                    }else {
                        para.library_id = this.id;
                        para.keyword = this.logSearch.keyword;
                        para.r_raffled_at = this.logSearch.r_raffled_at;
                        para.keyword_1 = this.logSearch.keyword_1;
                        para.origin = this.logSearch.origin;
                    }
                }
                request({
                    params: para
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            // 选择商品/优惠券/超级会员卡
            handleSelectionChange(val) {
                if(this.chooseType == 'goods') {
                    if(!this.setGoods) {
                        this.chooseGoods = [val];
                    }else {
                        this.attr_id = val.id
                    }
                }else if(this.chooseType == 'coupon'){
                    this.coupon_id = val.id;
                }else if(this.chooseType == 'card'){
                    this.card_id = val.id;
                }else if(this.chooseType == 'svip'){
                    this.svip_id = val.id;
                }
            },
            // 选定奖励品类型
            open() {
                this.list = [];
                this.chooseGoods = [];
                this.radioSelection = 0;
                this.pagination = {
                    page_count: 0
                }
                if(this.chooseType == 'integral' || this.chooseType == 'balance') {
                    this.addRewards();
                    return false;
                }
                let para = {
                    r: '',
                    page: 1
                }
                this.keyword = '';
                if(this.chooseType == 'goods') {
                    this.url = 'mall/goods/index';
                    this.title = '选择商品';
                    para.is_show_attr = 1;
                }else if(this.chooseType == 'coupon') {
                    this.url = 'mall/coupon/index';
                    this.title = '选择优惠券'
                    para.is_expired = 1;
                }else if(this.chooseType == 'card') {
                    this.url = 'mall/card/index';
                    this.title = '选择卡券'
                    para.is_expired = 1;
                }else if(this.chooseType == 'svip') {
                    this.url = 'plugin/vip_card/mall/card/index';
                    this.title = '选择子卡'
                }
                para.r = this.url;
                this.chooseDialog = false;
                this.listDialog = true;
                this.listLoading = true;
                request({
                    params: para,
                }).then(e => {
                    if (e.data.code === 0) {
                        if(this.chooseType == 'svip') {
                            for(let item of e.data.data.detail) {
                                if(item.num > 0) {
                                    this.list.push(item)
                                }
                            }
                            this.svip = e.data.data;
                        }else {
                            this.list = e.data.data.list;
                            this.pagination = e.data.data.pagination;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            // 保存设置
            saveEdit(formName) {
                let msg = '';
                if(this.form.rewards.length == 0) {
                    msg = '请添加奖励品'
                }else {
                    for(let item of this.form.rewards) {
                        if(item.type == 'goods') {
                            if(!item.goods_num) {
                                msg = '商品数量不能为空'
                            }else if(item.goods_num == 0) {
                                msg = '商品数量不能为零'
                            }
                        }else if(item.type == 'coupon') {
                            if(!item.coupon_num) {
                                msg = '优惠券数量不能为空'
                            }else if(item.coupon_num == 0) {
                                msg = '优惠券数量不能为零'
                            }
                        }else if(item.type == 'card') {
                            if(!item.card_num) {
                                msg = '卡券数量不能为空'
                            }else if(item.card_num == 0) {
                                msg = '卡券数量不能为零'
                            }
                        }else if(item.type == 'integral') {
                            if(!item.integral_num) {
                                msg = '积分不能为空'
                            }else if(item.integral_num == 0) {
                                msg = '积分不能为零'
                            }
                        }else if(item.type == 'balance') {
                            if(!item.balance) {
                                msg = '余额不能为空'
                            }else if(item.balance == 0) {
                                msg = '余额不能为零'
                            }
                        }
                    }
                }
                if(msg) {
                    this.$message.error(msg);
                    return false
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        if(this.form.expire_type == 'fixed') {
                            this.form.expire_start_time = this.expire_time[0];
                            this.form.expire_end_time = this.expire_time[1];
                        }
                        this.saveLoading =true;
                        request({
                            params: {
                                r: '/plugin/exchange/mall/library/edit',
                            },
                            data: this.form,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message({
                                    type: 'success',
                                    message: e.data.msg
                                });
                                this.$navigate({
                                    r: 'plugin/exchange/mall/library/list'
                                });
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.saveLoading = false;
                        }).catch(e => {
                            this.saveLoading = false;
                        });
                    }
                })
            },
            // 搜索
            search() {
                this.listLoading = true;
                this.list = [];
                let para = {
                    r: this.url,
                    page: 1
                };
                if(this.id == 0) {
                    para.keyword = this.keyword
                    if(this.chooseType == 'goods') {
                        para.is_show_attr = 1;
                        para.search = {keyword: this.keyword};
                    }
                }
                if(this.id > 0) {
                    if(!this.log) {
                        para.library_id = this.id;
                        para.code = this.codeSearch.keyword;
                        para.created_at = this.codeSearch.created_at;
                        if(this.codeSearch.type > -1) {
                            para.type = this.codeSearch.type
                        }
                        if(this.codeSearch.status > -2) {
                            para.status = this.codeSearch.status
                        }
                    }else {
                        para.library_id = this.id;
                        para.keyword = this.logSearch.keyword;
                        para.r_raffled_at = this.logSearch.r_raffled_at;
                        para.keyword_1 = this.logSearch.keyword_1;
                        para.origin = this.logSearch.origin;
                    }
                }
                request({
                    params: para,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/exchange/mall/library/edit',
                        id: this.id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data;
                        this.rewards = this.form.rewards.slice(0,6);
                        if(this.log) {
                            this.logSearch.library_id = this.id;
                            this.url = 'plugin/exchange/mall/library/record-log'
                            this.getLog();
                        }else {
                            this.codeSearch.library_id = this.id;
                            this.url = 'plugin/exchange/mall/code/list'
                            this.getCode();
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            getCode() {
                request({
                    params: {
                        r: 'plugin/exchange/mall/code/list',
                        library_id: this.id,
                    },
                }).then(e => {
                    this.first = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            getLog() {
                request({
                    params: {
                        r: 'plugin/exchange/mall/library/record-log',
                        library_id: this.id
                    },
                }).then(e => {
                    this.first = false;
                    this.dialogLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            getSvip() {
                request({
                    params: {
                        r: 'mall/mall-member/vip-card-permission',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let para = {id: 'svip', img: './../plugins/exchange/assets/img/svip.png'}
                        this.reward_type[1].push(para)
                    }
                })
            },
        },
        mounted: function () {
            this.getSvip();
            if(getQuery('id') > 0) {
                this.id = getQuery('id');
                this.getList();
                if(getQuery('log') > 0) {
                    this.log = true;
                }
            }else {
                this.first = false;
            }
        }
    });
</script>