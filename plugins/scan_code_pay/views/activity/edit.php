<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
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

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin: 0px;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .title {
        padding: 10px 15px;
        background-color: #F4F4F5;
        color: #909399;
        margin-bottom: 20px;
        margin-left: 20px;
        font-size: 15px;
        display: inline-block;
    }

    .key {
        width: 80px;
        margin: 0px 10px 0px 30px;
    }

    .scan-box {
        background: #f3f3f3;
        margin-bottom: 20px;
        padding: 0px 20px 20px 20px;
    }

    .scan-key {
        height: 44px;
        color: #909090;
        font-size: 15px;
        line-height: 44px;
    }

    .scan-rules {
        margin-left: 10px;
        background: #fff3f4;
        margin-right: 16px;
        text-align: center;
        line-height: 24px;
        width: 68px;
        color: #f56c6c;
        font-size: 13px;
    }

    .scan-sendBatch {
        margin-right: 16px;
        cursor: pointer;
        text-align: center;
        line-height: 24px;
        width: 68px;
        color: #606266;
        font-size: 13px;
        border: 1px solid #ebeef5
    }
    .t-omit {
        display: block;
        white-space: nowrap;
        width: 360px;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: -4px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" style="border:0" shadow="never"
             body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/scan_code_pay/mall/activity/index'})">
                        买单设置
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.id > 0">编辑活动</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加活动</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div style="height:15px;background:none"></div>
        <el-form :model="form" label-width="10rem" ref="form" :rules="rules">
            <el-tabs v-model="activeName">
                <!-- 基础设置 -->
                <el-tab-pane label="基础设置" class="form-body" name="first">
                    <div class="form-body">
                        <el-form-item label="活动名称" prop="name">
                            <el-input size="small" v-model="form.name" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="活动日期" prop="time">
                            <el-date-picker v-model="form.time" unlink-panels type="datetimerange" size="small"
                                            value-format="yyyy-MM-dd HH:mm:ss" range-separator="至"
                                            start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                        </el-form-item>
                        <el-form-item label="赠送规则" prop="send_type">
                            <label slot="label">赠送规则
                                <el-tooltip class="item" effect="dark" slot="content" placement="top">
                                    <div slot="content">设置了100，300，500这样3个等级的赠送规则，下单金额400 <br/>
                                        若选择赠送满足的所有规则，则获得100，300对应的赠送</br>
                                        若选择只赠送满足的最高级规则，则只获得300对应的赠送
                                    </div>
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-radio-group v-model="form.send_type">
                                <el-radio :label="1">赠送满足的所有规则</el-radio>
                                <el-radio :label="2">只赠送满足的最高级规则</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="买单规则" prop="rules">
                            <el-input :rows="10" type="textarea" v-model="form.rules" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item class="switch" label="是否启用" prop="status">
                            <el-switch v-model="form.status" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <!-- 规则设置 -->
                <el-tab-pane label="规则设置" class="form-body" style="padding-right:0%" name="second">
                    <div class="title">若设置了单独会员，则该会员等级享受会员自己的优惠与赠送，其他会员等级使用普通用户规则</div>
                    <div flex="dir:left">
                        <span class="key">用户组</span>
                        <div style="width:100%">
                            <el-table style="margin-bottom:20px"
                                      v-if="form.groups && form.groups.length"
                                      :data="form.groups" border>
                                <el-table-column prop="name" label="用户组名称"></el-table-column>
                                <el-table-column prop="member" label="用户组成员">
                                    <template slot-scope="scope">
                                        <span v-for="member in scope.row.members">
                                            {{member.name}}、
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="操作">
                                    <template slot-scope="scope">
                                        <el-button circle size="mini" type="text"
                                                   @click="editUserGroup(scope.$index,scope.row)">
                                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                <img src="statics/img/mall/edit.png" alt="">
                                            </el-tooltip>
                                        </el-button>

                                        <el-button type="text" size="mini"
                                                   @click="DestroyUserGroup(scope.$index,scope.row)" circle>
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-link v-if="userGroupBtnStatus" @click="addUserGroup" type="primary" :underline="false">
                                +新增用户组
                            </el-link>
                        </div>
                    </div>

                    <!-- 规则设定 -->
                    <div v-if="form.groups && form.groups.length" flex="dir:left" style="margin-top: 40px">
                        <span class="key">规则设定</span>
                        <div style="width:100%;">
                            <div v-for="(group,index) in form.groups" :key="index" class="scan-box">
                                <div class="scan-key" v-text="group.name"></div>
                                <!---------------->
                                <div style="background:#FFFFFF;width:100%">
                                    <div flex="dir:left cross:center" style="height:48px;">
                                        <div class="scan-rules">赠送规则</div>
                                        <div @click="sendBatchSet(index)"
                                             v-if="group.send_rules && group.send_rules.length"
                                             class="scan-sendBatch">批量设置
                                        </div>
                                        <el-link @click="sendRules(index)" type="primary" :underline="false">
                                            +新增赠送规则
                                        </el-link>
                                    </div>
                                    <el-table v-if="group.send_rules && group.send_rules.length"
                                              :data="group.send_rules" border>
                                        <el-table-column prop="consume_money" label="实际付款金额（元）"
                                                         width="180"></el-table-column>
                                        <el-table-column prop="send_integral_num" label="赠送积分" width="180">
                                            <template slot-scope="scope">
                                                <div v-if="scope.row.send_integral_type == 1">
                                                    {{scope.row.send_integral_num}}积分
                                                </div>
                                                <div v-if="scope.row.send_integral_type == 2">
                                                    {{scope.row.send_integral_num}}%
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="send_money" label="赠送余额（元）"
                                                         width="180"></el-table-column>
                                        <el-table-column label="赠送优惠券">
                                            <template slot-scope="scope">
                                                <el-tag style="margin:5px;" v-for="(tag,i) in scope.row.coupons"
                                                        :key="tag.name" closable
                                                        @close="couponClose(scope.row,i)">
                                                    {{tag.send_num}}张 | {{tag.name}}
                                                </el-tag>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="赠送卡券">
                                            <template slot-scope="scope">
                                                <el-tag style="margin:5px" v-for="(tag,i) in scope.row.cards"
                                                        :key="tag.name" closable
                                                        @close="cardClose(scope.row,i)">
                                                    {{tag.send_num}}张 | {{tag.name}}
                                                </el-tag>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="操作" width="250">
                                            <template slot-scope="scope">
                                                <el-button circle size="mini" type="text"
                                                           @click="sendEdit(index,scope.$index,scope.row)">
                                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                        <img src="statics/img/mall/edit.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                                <el-button type="text" size="mini"
                                                           @click="sendDestroy(index,scope.$index)" circle>
                                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                        <img src="statics/img/mall/del.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </div>
                                <div style="background:#FFFFFF;width:100%;margin-top:20px">
                                    <div flex="dir:left cross:center" style="height:48px;">
                                        <div class="scan-rules">优惠规则</div>
                                        <div @click="preferentialBatchSet(index)"
                                             v-if="group.preferential_rules && group.preferential_rules.length"
                                             class="scan-sendBatch">批量设置
                                        </div>
                                        <el-link @click="preferentialRules(index)" type="primary" :underline="false">
                                            +新增优惠规则
                                        </el-link>
                                        <div style="margin-left: 20px;font-size:13px;color:#f56c6c">注：优惠规则无法叠加</div>
                                    </div>
                                    <el-table :data="group.preferential_rules"
                                              v-if="group.preferential_rules && group.preferential_rules.length"
                                              border>
                                        <el-table-column prop="consume_money" label="单次消费金额（元）"
                                                         width="180"></el-table-column>
                                        <el-table-column prop="preferential_money" label="优惠金额（元）"></el-table-column>
                                        <el-table-column prop="integral_deduction" label="积分最多抵扣（元）">
                                            <template slot-scope="scope">
                                                <div v-if="scope.row.integral_deduction_type == 1">
                                                    {{scope.row.integral_deduction}}
                                                </div>
                                                <div v-if="scope.row.integral_deduction_type == 2">
                                                    {{scope.row.integral_deduction}}%
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="是否可以使用优惠券">
                                            <template slot-scope="scope">
                                                <el-switch v-model="scope.row.is_coupon"
                                                           :active-value="1"
                                                           :inactive-value="0"></el-switch>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="操作" width="250">
                                            <template slot-scope="scope">
                                                <el-button circle size="mini" type="text"
                                                           @click="preferentialEdit(index,scope.$index,scope.row)">
                                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                        <img src="statics/img/mall/edit.png" alt="">
                                                    </el-tooltip>
                                                </el-button>

                                                <el-button type="text" size="mini"
                                                           @click="preferentialDestroy(index,scope.$index)" circle>
                                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                        <img src="statics/img/mall/del.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
        </el-form>

        <!--用户组-->
        <el-dialog title="新增用户组" :visible.sync="userGroupVisible" width="50%" :close-on-click-modal="false">
            <el-form :model="userGroupForm" label-width="120px" :rules="userGroupRules" ref="userGroupForm"
                     @submit.native.prevent>
                <el-form-item label="用户组名称" prop="name">
                    <el-input size="small" v-model="userGroupForm.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="用户组成员" prop="members">
                    <div v-if="userGroupForm.members" style="border:1px solid #DCDFE6;padding:10px 0 10px 10px">
                        <el-checkbox v-for="member in userGroupForm.members"
                                     v-model="member.check_status"
                                     size="mini">{{member.name}}
                        </el-checkbox>
                    </div>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="userGroupVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click="userGroupSubmit">提交</el-button>
            </div>
        </el-dialog>

        <!-- 赠送规则 -->
        <el-dialog title="新增赠送规则" :visible.sync="sendRulesVisible" width="50%" :close-on-click-modal="false">

            <el-dialog title="新增优惠券" :visible.sync="couponVisible" width="30%" append-to-body>
                <div class="input-item">
                    <el-input @keyup.enter.native="couponSearch" size="small" placeholder="请输入优惠券名称搜索"
                              v-model="couponKeyword"
                              clearable @clear='couponSearch'>
                        <el-button slot="append" icon="el-icon-search" @click="couponSearch"></el-button>
                    </el-input>
                </div>

                <div v-loading="couponLoading" style="padding-bottom:10px">
                    <div v-for="coupon in coupons" style="margin-top:15px">
                        <el-checkbox v-model="coupon.send_status"><div class="t-omit">{{coupon.name}}</div></el-checkbox>
                        <el-input-number :step="1" step-strictly style="float:right" size="mini" :min="1"
                                         v-model="coupon.send_num"
                        ></el-input-number>
                    </div>
                </div>
                <div style="text-align: right;margin: 20px 0;">
                    <el-pagination v-if="couponPagination"
                                   @current-change="couponPageChange"
                                   background
                                   layout="prev, pager, next, jumper"
                                   :page-size="couponPagination.pageSize"
                                   :total="couponPagination.total_count"></el-pagination>
                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button size="small" @click="couponVisible = false">取消</el-button>
                    <el-button size="small" type="primary" @click="senderCouponSubmit">添加</el-button>
                </div>

            </el-dialog>

            <el-dialog title="新增卡券" :visible.sync="cardsVisible" width="30%" append-to-body>
                <div class="input-item">
                    <el-input @keyup.enter.native="cardsSearch" size="small" placeholder="请输入卡券名称搜索"
                              v-model="cardsKeyword"
                              clearable @clear='cardsSearch'>
                        <el-button slot="append" icon="el-icon-search" @click="cardsSearch"></el-button>
                    </el-input>
                </div>
                <div v-loading="cardsLoading" style="padding-bottom:10px">
                    <div v-for="card in cardsList" style="margin-top:15px">
                        <el-checkbox v-model="card.send_status"><div class="t-omit">{{card.name}}</div></el-checkbox>
                        <el-input-number :step="1" step-strictly style="float:right" size="mini" :min="1"
                                         v-model="card.send_num"
                        ></el-input-number>
                    </div>
                </div>
                </el-form>
                <div style="text-align: right;margin: 20px 0;">
                    <el-pagination v-if="cardsPagination"
                                   @current-change="cardsPageChange"
                                   background
                                   layout="prev, pager, next, jumper"
                                   :page-size="cardsPagination.pageSize"
                                   :total="cardsPagination.total_count"></el-pagination>
                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button size="small" @click="cardsVisible = false">取消</el-button>
                    <el-button size="small" type="primary" @click="senderCardSubmit">提交
                    </el-button>
                </div>
            </el-dialog>

            <el-form :model="senderRulesForm" label-width="120px" :rules="senderRulesFormRules" ref="senderRulesForm"
                     @submit.native.prevent>
                <el-form-item label="实际付款" prop="consume_money">
                    <el-input size="small" type="number" v-model="senderRulesForm.consume_money">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="赠送积分" prop="send_integral_num">
                    <el-input size="small" type="number" v-model="senderRulesForm.send_integral_num">
                        <template slot="append">
                            <el-radio-group v-model="senderRulesForm.send_integral_type">
                                <el-radio :label="1">固定值</el-radio>
                                <el-radio :label="2">百分比</el-radio>
                            </el-radio-group>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="赠送余额" prop="send_money">
                    <el-input size="small"  type="number" v-model="senderRulesForm.send_money">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="赠送优惠券" prop="coupons">
                    <el-tag style="margin:5px"
                            v-for="(tag,i) in senderRulesForm.coupons"
                            :key="i"
                            closable
                            @close="couponClose(senderRulesForm,i)">
                        {{tag.send_num}}张 | {{tag.name}}
                    </el-tag>
                    <el-button class="button-new-tag" size="small" @click="handleCoupon">新增优惠券</el-button>
                </el-form-item>
                <el-form-item label="赠送卡券" prop="cards">
                    <el-tag style="margin:5px"
                            v-for="(tag,i) in senderRulesForm.cards"
                            :key="i"
                            closable
                            @close="cardClose(senderRulesForm,i)">
                        {{tag.send_num}}张 | {{tag.name}}
                    </el-tag>
                    <el-button class="button-new-tag" size="small" @click="handleCard">新增卡券</el-button>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="sendRulesVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click="senderSubmit">提交</el-button>
            </div>
        </el-dialog>

        <!-- 优惠规则 -->
        <el-dialog title="新增优惠规则" :visible.sync="preferentialVisible" width="50%" :close-on-click-modal="false">
            <el-form :model="preferentialForm" label-width="120px" :rules="preferentialFormRules"
                     ref="preferentialForm" @submit.native.prevent>
                <el-form-item label="单次消费" prop="consume_money">
                    <el-input size="small" type="number" v-model="preferentialForm.consume_money">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="优惠金额" prop="preferential_money">
                    <el-input size="small" type="number" min="0" v-model="preferentialForm.preferential_money">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="积分抵扣" prop="integral_deduction">
                    <el-input size="small" type="number" min="0" v-model="preferentialForm.integral_deduction">
                        <template slot="prepend">最多抵扣</template>
                        <template slot="append">
                            <el-radio-group v-model="preferentialForm.integral_deduction_type">
                                <el-radio :label="1">固定值</el-radio>
                                <el-radio :label="2">百分比</el-radio>
                            </el-radio-group>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item class="switch" label="使用优惠券" prop="is_coupon">
                    <el-switch v-model="preferentialForm.is_coupon" :active-value="1" :inactive-value="0"></el-switch>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="preferentialVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click="preferentialSubmit">提交</el-button>
            </div>
        </el-dialog>

        <!-- 赠送批量设置 -->
        <el-dialog title="批量设置" :visible.sync="sendBatchVisible" width="50%" :close-on-click-modal="false">
            <el-dialog title="新增优惠券" :visible.sync="batchCouponVisible" width="30%" append-to-body>
                <div class="input-item">
                    <el-input @keyup.enter.native="couponSearch" size="small" placeholder="请输入优惠券名称搜索"
                              v-model="couponKeyword"
                              clearable @clear='couponSearch'>
                        <el-button slot="append" icon="el-icon-search" @click="couponSearch"></el-button>
                    </el-input>
                </div>

                <div v-loading="couponLoading" style="padding-bottom:10px">
                    <div v-for="coupon in coupons" style="margin-top:15px">
                        <el-checkbox v-model="coupon.send_status"><div class="t-omit">{{coupon.name}}</div></el-checkbox>
                        <el-input-number :step="1" step-strictly style="float:right" size="mini" :min="1"
                                         v-model="coupon.send_num"
                        ></el-input-number>
                    </div>
                </div>
                <div style="text-align: right;margin: 20px 0;">
                    <el-pagination v-if="couponPagination"
                                   @current-change="couponPageChange"
                                   background
                                   layout="prev, pager, next, jumper"
                                   :page-size="couponPagination.pageSize"
                                   :total="couponPagination.total_count"></el-pagination>
                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button size="small" @click="batchCouponVisible = false">取消</el-button>
                    <el-button size="small" type="primary" @click="senderBatchCouponSubmit">添加</el-button>
                </div>

            </el-dialog>

            <el-dialog title="新增卡券" :visible.sync="batchCardsVisible" width="30%" append-to-body>
                <div class="input-item">
                    <el-input @keyup.enter.native="cardsSearch" size="small" placeholder="请输入卡券名称搜索"
                              v-model="cardsKeyword"
                              clearable @clear='cardsSearch'>
                        <el-button slot="append" icon="el-icon-search" @click="cardsSearch"></el-button>
                    </el-input>
                </div>
                <div v-loading="cardsLoading" style="padding-bottom:10px">
                    <div v-for="card in cardsList" style="margin-top:15px">
                        <el-checkbox v-model="card.send_status"><div class="t-omit">{{card.name}}</div></el-checkbox>
                        <el-input-number :step="1" step-strictly style="float:right" size="mini" :min="1"
                                         v-model="card.send_num"
                        ></el-input-number>
                    </div>
                </div>
                </el-form>
                <div style="text-align: right;margin: 20px 0;">
                    <el-pagination v-if="cardsPagination"
                                   @current-change="cardsPageChange"
                                   background
                                   layout="prev, pager, next, jumper"
                                   :page-size="cardsPagination.pageSize"
                                   :total="cardsPagination.total_count"></el-pagination>
                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button size="small" @click="batchCardsVisible = false">取消</el-button>
                    <el-button size="small" type="primary" @click="senderBatchCardSubmit">提交
                    </el-button>
                </div>
            </el-dialog>

            <el-form :model="sendBatchForm" label-width="120px" :rules="sendBatchFormRules" ref="sendBatchForm"
                     @submit.native.prevent>
                <el-form-item label="规则选择" prop="send_type">
                    <el-radio-group v-model="sendBatchForm.send_type">
                        <el-radio :label="1">赠送积分</el-radio>
                        <el-radio :label="2">赠送余额</el-radio>
                        <el-radio :label="3">赠送优惠券</el-radio>
                        <el-radio :label="4">赠送卡券</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="sendBatchForm.send_type == 1" label="赠送积分" prop="send_integral_num">
                    <el-input size="small" type="number" v-model="sendBatchForm.send_integral_num">
                        <template slot="append">
                            <el-radio-group v-model="sendBatchForm.send_integral_type">
                                <el-radio :label="1">固定值</el-radio>
                                <el-radio :label="2">百分比</el-radio>
                            </el-radio-group>
                        </template>
                    </el-input>
                </el-form-item>

                <el-form-item v-if="sendBatchForm.send_type == 2" label="赠送余额" prop="send_money">
                    <el-input size="small" type="number" v-model="sendBatchForm.send_money">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>

                <el-form-item v-if="sendBatchForm.send_type == 3" label="赠送优惠券" prop="coupons">
                    <el-tag style="margin:5px"
                            v-for="(tag,i) in sendBatchForm.coupons"
                            :key="i"
                            closable
                            @close="couponClose(sendBatchForm,i)">
                        {{tag.send_num}}张 | {{tag.name}}
                    </el-tag>
                    <el-button class="button-new-tag" size="small" @click="batchCouponVisible = true">新增优惠券</el-button>
                </el-form-item>
                <el-form-item v-if="sendBatchForm.send_type == 4" label="赠送卡券" prop="cards">
                    <el-tag style="margin:5px"
                            v-for="(tag,i) in sendBatchForm.cards"
                            :key="i"
                            closable
                            @close="cardClose(sendBatchForm,i)">
                        {{tag.send_num}}张 | {{tag.name}}
                    </el-tag>
                    <el-button class="button-new-tag" size="small" @click="batchCardsVisible = true">新增卡券</el-button>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="sendBatchVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click="sendBatchSubmit">提交</el-button>
            </div>
        </el-dialog>
        <!-- 优惠批量设置 -->
        <el-dialog title="批量设置" :visible.sync="preferentialBatchVisible" width="50%" :close-on-click-modal="false">
            <el-form :model="preferentialBatchForm" label-width="120px" :rules="preferentialBatchFormRules"
                     ref="preferentialBatchForm" @submit.native.prevent>
                <el-form-item label="规则选择" prop="company">
                    <el-radio-group v-model="preferentialBatchForm.send_type">
                        <el-radio :label="1">优惠金额</el-radio>
                        <el-radio :label="2">积分抵扣</el-radio>
                        <el-radio :label="3">使用优惠券</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item v-if="preferentialBatchForm.send_type == 1" label="优惠金额" prop="preferential_money">
                    <el-input size="small" type="number" v-model="preferentialBatchForm.preferential_money">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item v-if="preferentialBatchForm.send_type == 2" label="积分抵扣" prop="integral_deduction">
                    <el-input size="small" type="number" v-model="preferentialBatchForm.integral_deduction">
                        <template slot="prepend">最多抵扣</template>
                        <template slot="append">
                            <el-radio-group v-model="preferentialBatchForm.integral_deduction_type">
                                <el-radio :label="1">固定值</el-radio>
                                <el-radio :label="2">百分比</el-radio>
                            </el-radio-group>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item v-if="preferentialBatchForm.send_type == 3" class="switch" label="使用优惠券" prop="is_coupon">
                    <el-switch v-model="preferentialBatchForm.is_coupon" :active-value="1"
                               :inactive-value="0"></el-switch>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="preferentialBatchVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click="preferentialBatchSubmit">提交</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
                form: {
                    groups: [],
                    send_type: 2,
                    status: 0,
                },
                rules: {
                    name: [
                        {required: true, message: '活动名称不能为空', trigger: 'blur'},
                    ],
                    time: [
                        {required: true, message: '日期不能为空', trigger: 'blur'},
                    ],
                    type: [
                        {required: true, message: '买单规则不能为空', trigger: 'blur'},
                    ],
                    send_type: [
                        {required: true, message: '赠送规则不能为空', trigger: 'blur'},
                    ],
                    status: [
                        {required: true, message: '是否启用不能为空', trigger: 'blur'},
                    ]
                },
                tags: [],
                otherIndex: '',
                index: '',

                listLoading: true,
                btnLoading: false,

                /* 用户组 */
                userGroupBtnStatus: true,
                membersList: [],
                userGroupVisible: false,
                userGroupForm: {
                    name: '',
                    members: null,
                },
                userGroupRules: {
                    name: [
                        {required: true, message: '用户组名称不能为空', trigger: 'blur'},
                    ],
                    members: [
                        {required: true, message: '用户组成员不能为空', trigger: 'blur'},
                        {
                            validator(rule, value, callback, source, options) {
                                let sentry = false;
                                value.forEach(v => {
                                    if (v.check_status)
                                        sentry = true;
                                })
                                if (sentry) {
                                    callback();
                                } else {
                                    callback('用户组成员不能为空');
                                }
                            }
                        }
                    ],
                },

                /* 赠送规则 */
                sendRulesVisible: false,
                senderRulesForm: {},
                senderRulesFormRules: {
                    consume_money: [
                        {required: true, message: '实际付款不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9]\d*)(\.\d{1,2})?$/, message: '实际付款不合规范'}
                    ],
                    send_integral_num: [
                        {required: true, message: '赠送积分不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9][0-9]*)$/, message: '赠送积分不合规范'}
                    ],
                    send_money: [
                        {required: true, message: '赠送余额不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9]\d*)(\.\d{1,2})?$/, message: '赠送余额不合规范'}
                    ],
                },
                /* 优惠规则 */
                preferentialVisible: false,
                preferentialForm: {},
                preferentialFormRules: {
                    consume_money: [
                        {required: true, message: '单次消费不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9]\d*)(\.\d{1,2})?$/, message: '单次消费不合规范'}
                    ],
                    preferential_money: [
                        {required: true, message: '优惠金额不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9]\d*)(\.\d{1,2})?$/, message: '优惠金额不合规范'}
                    ],
                    integral_deduction: [
                        {required: true, message: '积分抵扣不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9][0-9]*)$/, message: '积分抵扣不合规范'}
                    ],
                    is_coupon: [
                        {required: true, message: '使用优惠券不能为空', trigger: 'change'},
                    ]
                },
                /* 卡券 */
                cardsList: [],
                cardsVisible: false,
                cardsKeyword: '',
                cardsPage: 1,
                cardsPagination: null,
                cardsLoading: false,
                cardsPageCount: 0,
                batchCardsVisible: false,

                /* 优惠券 */
                coupons: [],
                couponList: [],
                couponVisible: false,
                couponKeyword: '',
                couponPage: 1,
                couponPagination: null,
                couponLoading: false,
                couponPageCount: 0,
                batchCouponVisible: false,

                /*  赠送批量 */
                sendBatchVisible: false,
                sendBatchForm: {
                    send_type: 1, //1积分 2余额 3优惠券 4卡券
                    send_integral_type: 1,
                    cards: [],
                    coupons: [],
                },
                sendBatchFormRules: {
                    send_integral_num: [
                        {required: true, message: '赠送积分不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9][0-9]*)$/, message: '赠送积分不合规范'}
                    ],
                    send_money: [
                        {required: true, message: '赠送余额不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9]\d*)(\.\d{1,2})?$/, message: '赠送余额不合规范'}
                    ],
                },
                /*  优惠批量 */
                preferentialBatchVisible: false,
                preferentialBatchForm: {
                    send_type: 1,
                    integral_deduction_type: 1,
                },
                preferentialBatchFormRules: {
                    preferential_money: [
                        {required: true, message: '优惠金额不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9]\d*)(\.\d{1,2})?$/, message: '优惠金额不合规范'}
                    ],
                    integral_deduction: [
                        {required: true, message: '积分抵扣不能为空', trigger: 'blur'},
                        {required: true, pattern: /^(0|[1-9][0-9]*)$/, message: '积分抵扣不合规范'}
                    ],
                    is_coupon: [
                        {required: true, message: '使用优惠券不能为空', trigger: 'change'},
                    ]
                },
            };
        },
        methods: {
            /* 用户组 */
            DestroyUserGroup(index, row) {
                this.form.groups.splice(index, 1);
                let members = this.otherMember();
                this.userGroupBtnStatus = members && members.length;
            },
            editUserGroup(index, row) {
                let members = JSON.parse(JSON.stringify(row.members));
                members.forEach(v => {
                    v.check_status = true;
                })
                this.userGroupForm = {
                    name: row.name,
                    members: members.concat(this.otherMember()),
                }
                this.index = index;
                this.userGroupVisible = true;
            },
            addUserGroup() {
                this.userGroupForm = {
                    name: '',
                    members: this.otherMember(),
                }
                this.index = -1;
                this.userGroupVisible = true;
            },
            userGroupSubmit() {
                this.$refs.userGroupForm.validate((valid) => {
                    if (valid) {
                        let list = [];
                        this.userGroupForm.members.forEach(v => {
                            if (v.check_status)
                                list.push(v);
                        });
                        if (this.index === -1) {
                            let groups = {
                                name: this.userGroupForm.name,
                                members: list,
                                send_rules: [],
                                preferential_rules: [],
                            };
                            this.form.groups.push(groups);
                        } else {
                            let groups = {
                                activity_id: this.form.groups[this.index].preferential_rules.activity_id,
                                id: this.form.groups[this.index].preferential_rules.id,
                                name: this.userGroupForm.name,
                                members: list,
                                send_rules: this.form.groups[this.index].send_rules,
                                preferential_rules: this.form.groups[this.index].preferential_rules,
                            };
                            this.form.groups.splice(this.index, 1, groups);
                        }
                        let members = this.otherMember();
                        this.userGroupBtnStatus = members && members.length;
                        this.userGroupVisible = false;
                    }
                });
            },
            otherMember() {
                let members = JSON.parse(JSON.stringify(this.membersList));
                this.form.groups.forEach(i => {
                    i.members.forEach(j => {
                        for (let k in members) {
                            if (members[k].level == j.level) {
                                members.splice(k, 1);
                            }
                        }
                    })
                })
                return members;
            },

            /* 赠送规则 */
            couponClose(row, index) {
                row.coupons.splice(index, 1);
            },
            cardClose(row, index) {
                row.cards.splice(index, 1);
            },
            sendRules(index) {
                this.senderRulesForm = {
                    rules_type: 1,
                    consume_money: '',
                    send_integral_num: 0,
                    send_integral_type: 1,//1固定 2部分比
                    send_money: 0,
                    preferential_money: 0,
                    integral_deduction: 0,
                    integral_deduction_type: 1,
                    is_coupon: 0,
                    coupons: [],
                    cards: [],
                };
                this.index = index;
                this.otherIndex = -1;
                this.sendRulesVisible = true;
            },
            sendEdit(index, groupindex, row) {
                this.senderRulesForm = JSON.parse(JSON.stringify(row));
                this.index = index;
                this.otherIndex = groupindex;
                this.sendRulesVisible = true;
            },
            sendDestroy(index, groupindex) {
                this.form.groups[index].send_rules.splice(groupindex, 1);
            },
            senderSubmit() {
                this.$refs.senderRulesForm.validate((valid) => {
                    if (valid) {
                        if (this.otherIndex == -1) {
                            this.form.groups[this.index].send_rules.push(Object.assign({}, this.senderRulesForm));
                        } else {
                            this.form.groups[this.index].send_rules.splice(this.otherIndex, 1, Object.assign({}, this.senderRulesForm));
                        }
                        this.sendRulesVisible = false;
                    }
                });
            },

            /* 批量*/
            sendBatchSet(index) {
                this.index = index;
                this.senderRulesForm.coupons = [];
                this.senderRulesForm.cards = [];
                this.sendBatchForm.coupons = [];
                this.sendBatchForm.cards = [];
                this.sendBatchVisible = true;
            },
            sendBatchSubmit() {
                this.$refs.sendBatchForm.validate((valid) => {
                    if (valid) {
                        let sendBatchForm = Object.assign({}, this.sendBatchForm);
                        let send = this.form.groups[this.index].send_rules;
                        send.forEach(v => {
                            switch (sendBatchForm.send_type) {
                                case 1:
                                    v.send_integral_num = sendBatchForm.send_integral_num;
                                    v.send_integral_type = sendBatchForm.send_integral_type;
                                    break;
                                case 2:
                                    v.send_money = sendBatchForm.send_money;
                                    break;
                                case 3:
                                    v.coupons = sendBatchForm.coupons;
                                    break;
                                case 4:
                                    v.cards = sendBatchForm.cards;
                                    break;
                                default:
                            }
                        })
                        this.sendBatchVisible = false;
                    }
                });
            },

            preferentialBatchSet(index) {
                this.index = index;
                this.preferentialBatchVisible = true;
            },
            preferentialBatchSubmit() {
                this.$refs.preferentialBatchForm.validate((valid) => {
                    if (valid) {
                        let preferentialBatchForm = this.preferentialBatchForm;
                        let send = this.form.groups[this.index].preferential_rules;
                        send.forEach(v => {
                            switch (preferentialBatchForm.send_type) {
                                case 1:
                                    v.preferential_money = preferentialBatchForm.preferential_money;
                                    break;
                                case 2:
                                    v.integral_deduction = preferentialBatchForm.integral_deduction;
                                    v.integral_deduction_type = preferentialBatchForm.integral_deduction_type;
                                    break;
                                case 3:
                                    v.is_coupon = preferentialBatchForm.is_coupon;
                                    break;
                                default:
                            }
                        })
                        this.preferentialBatchVisible = false;
                    }
                });
            },

            senderBatchCouponSubmit() {
                let list = [];
                this.coupons.map(v => {
                    let sentry = this.sendBatchForm.coupons.every((item, key, array) => {
                        if (item.coupon_id == v.id) {
                            if (v.send_num > 0 && v.send_status) {
                                this.sendBatchForm.coupons[key].send_num = v.send_num;
                            } else {
                                this.sendBatchForm.coupons.splice(key, 1);
                            }
                            return false;
                        }
                        return true;
                    });
                    if (v.send_num > 0 && v.send_status && sentry) {
                        list.push({
                            id: 0,
                            coupon_id: v.id,
                            send_num: v.send_num,
                            name: v.name,
                        })
                    }
                })
                this.sendBatchForm.coupons = [].concat(this.sendBatchForm.coupons, list);
                this.batchCouponVisible = false;
            },

            senderBatchCardSubmit() {
                let list = [];
                this.cardsList.map(v => {
                    let sentry = this.sendBatchForm.cards.every((item, key, array) => {
                        if (item.card_id == v.id) {
                            if (v.send_num > 0 && v.send_status) {
                                this.sendBatchForm.cards[key].send_num = v.send_num;
                            } else {
                                this.sendBatchForm.cards.splice(key, 1);
                            }
                            return false;
                        }
                        return true;
                    });
                    if (v.send_num > 0 && v.send_status && sentry) {
                        list.push({
                            id: 0,
                            card_id: v.id,
                            send_num: v.send_num,
                            name: v.name,
                        })
                    }
                })
                this.sendBatchForm.cards = [].concat(this.sendBatchForm.cards, list);
                this.batchCardsVisible = false;
            },

            /* 优惠规则 */
            preferentialRules(index) {
                this.preferentialForm = {
                    rules_type: 2,
                    consume_money: '',
                    send_integral_num: 0,
                    send_integral_type: 1,//1固定 2部分比
                    send_money: 0,
                    preferential_money: 0,
                    integral_deduction: 0,
                    integral_deduction_type: 1,
                    is_coupon: 0,
                    coupons: [],
                    cards: [],
                }
                this.index = index;
                this.otherIndex = -1;
                this.preferentialVisible = true;
            },
            preferentialEdit(index, groupindex, row) {
                this.preferentialForm = Object.assign({}, row);
                this.index = index;
                this.otherIndex = groupindex;
                this.preferentialVisible = true;
            },
            preferentialDestroy(index, groupindex) {
                this.form.groups[index].preferential_rules.splice(groupindex, 1);
            },
            preferentialSubmit() {
                this.$refs.preferentialForm.validate((valid) => {
                    if (valid) {
                        if (this.otherIndex == -1) {
                            this.form.groups[this.index].preferential_rules.push(Object.assign({}, this.preferentialForm));
                        } else {
                            this.form.groups[this.index].preferential_rules.splice(this.otherIndex, 1, this.preferentialForm)
                        }
                        this.preferentialVisible = false;
                    }
                });
            },
            /* 优惠券 */
            checkCoupon() {
                if (this.coupons && this.senderRulesForm.coupons) {
                    this.coupons.forEach(v => {
                        this.senderRulesForm.coupons.map(v1 => {
                            if (v.id === v1.coupon_id) {
                                v.send_num = v1.send_num;
                                v.send_status = true;
                            }
                        })
                    })
                }
            },
            handleCoupon() {
                this.checkCoupon();
                this.couponVisible = true;
            },
            couponSearch() {
                this.couponPage = 1;
                this.getCoupon(() => {
                    this.checkCoupon();
                });
            },
            couponPageChange(currentPage) {
                this.couponPage = currentPage;
                this.getCoupon(() => {
                    this.checkCoupon();
                });
            },
            senderCouponSubmit() {
                let list = [];
                this.coupons.map(v => {
                    let sentry = this.senderRulesForm.coupons.every((item, key, array) => {
                        if (item.coupon_id == v.id) {
                            if (v.send_num > 0 && v.send_status) {
                                this.senderRulesForm.coupons[key].send_num = v.send_num;
                            } else {
                                this.senderRulesForm.coupons.splice(key, 1);
                            }
                            return false;
                        }
                        return true;
                    });

                    if (v.send_num > 0 && v.send_status && sentry) {
                        list.push({
                            id: 0,
                            coupon_id: v.id,
                            send_num: v.send_num,
                            name: v.name,
                        })
                    }
                });
                this.senderRulesForm.coupons = [].concat(this.senderRulesForm.coupons, list);
                this.couponVisible = false;
            },
            /* 卡券 */
            checkCard() {

                if (this.cardsList && this.senderRulesForm.cards) {
                    this.cardsList.forEach(v => {
                        this.senderRulesForm.cards.map(v1 => {
                            if (v.id === v1.card_id) {
                                v.send_num = v1.send_num;
                                v.send_status = true;
                            }
                        })
                    })
                }
            },

            handleCard() {
                this.checkCard();
                this.cardsVisible = true;
            },
            cardsSearch() {
                this.cardsPage = 1;
                this.getCard(() => {
                    this.checkCard();
                });
            },
            cardsPageChange(currentPage) {
                this.cardsPage = currentPage;
                this.getCard(() => {
                    this.checkCard();
                });
            },
            senderCardSubmit() {
                let list = [];
                this.cardsList.map(v => {
                    let sentry = this.senderRulesForm.cards.every((item, key, array) => {
                        if (item.card_id == v.id) {
                            if (v.send_num > 0 && v.send_status) {
                                this.senderRulesForm.cards[key].send_num = v.send_num;
                            } else {
                                this.senderRulesForm.cards.splice(key, 1);
                            }
                            return false;
                        }
                        return true;
                    });
                    if (v.send_num > 0 && v.send_status && sentry) {
                        list.push({
                            id: 0,
                            card_id: v.id,
                            send_num: v.send_num,
                            name: v.name,
                        })
                    }
                })
                this.senderRulesForm.cards = [].concat(this.senderRulesForm.cards, list);
                this.cardsVisible = false;
            },

            /****************************************************************/
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.form);
                        para.groups = JSON.stringify(para.groups);
                        para.start_time = para.time[0];
                        para.end_time = para.time[1];
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/scan_code_pay/mall/activity/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                if (!getQuery('activity_id')) {
                                    navigateTo({r: 'plugin/scan_code_pay/mall/activity/index'});
                                }
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.$message.error(e.data.msg);
                            this.btnLoading = false;
                        });
                    }
                });
            },

            getList() {
                if (!getQuery('activity_id')) {
                    this.listLoading = false;
                    return;
                }
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/activity/edit',
                        activity_id: getQuery('activity_id')
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        let form = e.data.data.activity;
                        form.time = [form.start_time, form.end_time];
                        this.form = form;

                        let members = this.otherMember();
                        this.userGroupBtnStatus = members && members.length;
                    } else {
                        this.$message.error(e.data.msg);
                        navigateTo({
                            r: 'plugin/scan_code_pay/mall/activity/index',
                        });
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                    this.listLoading = false;
                });
            },

            getCoupon(args = () => {
            }) {
                this.couponLoading = true;
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/activity/coupons',
                        page: this.couponPage,
                        keyword: this.couponKeyword
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.couponLoading = false;
                        //couponList
                        let couponList = e.data.data.coupons;
                        couponList.forEach(v => {
                            v.send_num = 1;
                            v.send_status = false;
                        })
                        this.coupons = couponList;
                        args();
                        this.couponPagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.couponLoading = false;
                    this.$message.error(e.data.msg);
                });
            },
            getCard(args = () => {
            }) {
                this.cardsLoading = true;
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/activity/cards',
                        page: this.cardsPage,
                        keyword: this.cardsKeyword
                    },
                }).then(e => {
                    this.cardsLoading = false;
                    if (e.data.code == 0) {
                        let cardsList = e.data.data.cards;
                        cardsList.forEach(v => {
                            v.send_num = 1;
                            v.send_status = false;
                        })
                        this.cardsList = cardsList;
                        args();
                        this.cardsPagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.cardsLoading = false;
                    this.$message.error(e.data.msg);
                });
            },

            getUserGroup() {
                request({
                    params: {
                        r: 'plugin/scan_code_pay/mall/activity/members',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.userGroupForm.members = e.data.data.members;
                        this.membersList = e.data.data.members;
                        this.membersList.forEach(v => {
                            v.check_status = false;
                        })
                        this.getList();
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            }
        },

        mounted: function () {
            this.getUserGroup();
            this.getCoupon();
            this.getCard();
        }
    })
</script>