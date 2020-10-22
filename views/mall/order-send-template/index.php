<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
Yii::$app->loadViewComponent('app-order');
?>

<style>
    .app-order-icon {
        width: 33px;
        height: 33px;
    }

    .backgroundmous {
        background-color: #ecf4ff !important;
    }

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

    .el-input-group__append .el-button {
        margin: 0;
    }

    .el-tabs__header {
        background-color: #ffffff;
        padding: 10px 10px 0 10px;
    }

    .goodsInf {
        height: 105px;
        border: 1px solid #e8eaee;
        width: 770px;
        background-color: #f5f7fa;
        line-height: 40px;
        padding: 10px 0 10px 18px;
    }

    .tem-name .el-form-item {
        margin: 10px 50px 10px 0;
    }

    .template-name .el-form-item {
        margin: 10px 50px 10px 0;
    }

    .invoice-title .el-form-item__label {
        font-size: 10px;
        width: 100px;
        padding: 0;
    }

    .invoice-title .el-form-item {
        margin: 0;
    }

    .invoice-title {
        padding: 0 10px;
    }

    .blue-text {
        color: #409eff;
    }

    .decoration {
        text-decoration: line-through;
    }

    .offset .el-input__inner {
        height: 30px;
        line-height: 30px;
        padding: 0;
        text-align: center;
    }

    .order-template .el-input__inner {
        height: 30px;
        line-height: 30px;
    }

    .del-btn.el-button--mini.is-circle {
        position: absolute;
        top: -8px;
        right: -8px;
        padding: 4px;
    }

    .textarea textarea {
        height: 88px;
    }

    .input-1 .el-icon-search {
        margin: 0px;
    }
    .print-div {
        position: absolute;top: 0; z-index: -1;
    }
</style>

<div id="app" v-cloak>
    <el-dialog :title="address_list.length > 0 ? '修改网点信息' : '添加网点信息'" :visible.sync="outletInformation">
        <el-form :model="outletform" :rules="rules" label-width="100px" ref="ruleForm">
            <div style="width: 95%" flex="main:justify">
                <el-form-item style="width: 50%" label="网点名称" props="name" required>
                    <el-input v-model="outletform.name"></el-input>
                </el-form-item>
                <el-form-item style="width: 50%" label="联系人" props="username" required>
                    <el-input v-model="outletform.username"></el-input>
                </el-form-item>
            </div>
            <div style="width: 95%" flex="main:justify">
                <el-form-item style="width: 50%" label="联系方式" props="mobile" required>
                    <el-input v-model="outletform.mobile" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="width: 50%" label="网点邮编" props="code" required>
                    <el-input v-model="outletform.code" autocomplete="off"></el-input>
                </el-form-item>
            </div>
            <div style="width: 95%" flex="">
                <el-form-item style="width: 50%" label="网点地址" props="area" required>
                    <el-cascader
                            :options="district"
                            :props="props"
                            v-model="outletform.area">
                    </el-cascader>
                </el-form-item>
            </div>
            <el-form-item style="width: 50%" label="详细地址" props="address" required>
                <el-input
                        type="textarea"
                        :rows="2"
                        placeholder="请输入内容"
                        v-model="outletform.address">
                </el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="smill" @click="cancelOut()">取 消</el-button>
            <el-button size="smill" type="primary" @click="saveOutlet('ruleForm')">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="打印设置" :visible.sync="settingVisible" width="30%">
        <el-form  label-width="100px" ref="ruleForm" @submit.native.prevent>
            <el-form-item  label="页面设置" props="username" >
                <div>
                    <div>
                        <el-radio v-model="printSetting.page_type"  :label="1">按间距连续打印</el-radio>
                    </div>
                    <div>
                        <el-radio v-model="printSetting.page_type"  :label="2">每版打印一页</el-radio>
                    </div>
                </div>
            </el-form-item>
            <el-form-item style="width: 80%" label="发货单间距" props="name" >
                <el-input v-model="printSetting.space" @keyup.enter.native="handleQuery">
                    <template slot="append">
                        mm
                    </template>
                </el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="smill" @click="settingVisible = false">取 消</el-button>
            <el-button size="smill" type="primary" @click="saveSetting">确 定</el-button>
        </div>
    </el-dialog>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="发货单管理" name="first">
                <el-card shadow="never" class="mt-24">
                    <template v-if="!edit">
                        <div flex="main:justify">
                            <span>网点信息</span>
                            <el-button type="primary" size="small" @click="openOutlet">{{address_list.length > 0 ?
                                '修改网点信息' : '添加网点信息'}}
                            </el-button>
                        </div>
                        <div style="height: 40px;background-color: #f3f5f6;line-height: 40px;text-align: center;marginBottom: 15px;marginTop: 30px">
                            发货地址
                        </div>
                        <el-table
                                max-height="360"
                                :data="address_list"
                                border
                                style="width: 100%">
                            <el-table-column
                                    prop="name"
                                    label="网点名称"
                                    width="180">
                            </el-table-column>
                            <el-table-column
                                    prop="username"
                                    label="联系人"
                                    width="180">
                            </el-table-column>
                            <el-table-column
                                    prop="mobile"
                                    width="180"
                                    label="联系方式">
                            </el-table-column>
                            <el-table-column
                                    label="地址">
                                <template slot-scope="scope">
                                    {{scope.row.province}} {{scope.row.city}} {{scope.row.district}}
                                    {{scope.row.address}}
                                </template>
                            </el-table-column>
                            <el-table-column
                                    prop="code"
                                    width="120"
                                    label="网点邮编">
                            </el-table-column>
                        </el-table>
                        <div style="height: 40px;background-color: #f3f5f6;line-height: 40px;text-align: center;marginBottom: 15px;marginTop: 30px">
                            {{template_name_1}}打印项目总览
                        </div>
                        <div style="height: 308px;border: 1px solid #ebeef5;padding:16px" flex="">
                            <div style="width: 20%; height: 100%;">
                                <p style="margin-bottom: 30px;" flex="main:left">
                                    网点名称：
                                    <span :class="global.sellerInf.branch ? 'blue-text' : 'decoration'">{{global.sellerInf.branch  | conversion}}</span>
                                </p>
                                <p style="margin-bottom: 30px;" flex="main:left">
                                    网点联系人：
                                    <span :class="global.sellerInf.name ? 'blue-text' : 'decoration'">{{global.sellerInf.name  | conversion}}</span>
                                </p>
                                <p style="margin-bottom: 30px;" flex="main:left">
                                    网点联系方式：
                                    <span :class="global.sellerInf.phone ? 'blue-text' : 'decoration'">{{global.sellerInf.phone  | conversion}}</span>
                                </p>
                                <p style="margin-bottom: 30px;" flex="main:left">
                                    网点地址：
                                    <span :class="global.sellerInf.address ? 'blue-text' : 'decoration'">{{global.sellerInf.address  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    网点邮编：
                                    <span :class="global.sellerInf.postcode ? 'blue-text' : 'decoration'">{{global.sellerInf.postcode   | conversion}}</span>
                                </p>
                            </div>
                            <div style="width: 20%; height: 100%;">
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    订单号：
                                    <span :class="global.order.orderNumber ? 'blue-text' : 'decoration'">{{global.order.orderNumber  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    订单时间：
                                    <span :class="global.order.time ? 'blue-text' : 'decoration'">{{global.order.time  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    打印日期：
                                    <span :class="global.order.date ? 'blue-text' : 'decoration'">{{global.order.date   | conversion}}</span>
                                </p>
                            </div>
                            <div style="width: 20%; height: 100%;">
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    商品序号：
                                    <span :class="global.goodsInf.serial ? 'blue-text' : 'decoration'">{{global.goodsInf.serial  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    商品名称：
                                    <span :class="global.goodsInf.name ? 'blue-text' : 'decoration'">{{global.goodsInf.name  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    规格：
                                    <span :class="global.goodsInf.attr ? 'blue-text' : 'decoration'">{{global.goodsInf.attr  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    数量：
                                    <span :class="global.goodsInf.number ? 'blue-text' : 'decoration'">{{global.goodsInf.number  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    小计：
                                    <span :class="global.goodsInf.univalent ? 'blue-text' : 'decoration'">{{global.goodsInf.univalent  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    货号：
                                    <span :class="global.goodsInf.article_number ? 'blue-text' : 'decoration'">{{global.goodsInf.article_number   | conversion}}</span>
                                </p>
                            </div>
                            <div style="width: 20%; height: 100%;">
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    单位：
                                    <span :class="global.goodsInf.unit  ? 'blue-text' : 'decoration'">{{global.goodsInf.unit  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    订单金额：
                                    <span :class="global.goodsInf.amount ? 'blue-text' : 'decoration'">{{global.goodsInf.amount  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    总数量：
                                    <span :class="global.goodsInf.totalAmount ? 'blue-text' : 'decoration'">{{global.goodsInf.totalAmount  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    优惠：
                                    <span :class="global.goodsInf.discount ? 'blue-text' : 'decoration'">{{global.goodsInf.discount  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    实付：
                                    <span :class="global.goodsInf.actually_paid ? 'blue-text' : 'decoration'">{{global.goodsInf.actually_paid  | conversion}}</span>
                                </p>
                                <p style="width:170px;margin-bottom: 30px;" flex="main:left">
                                    运费：
                                    <span :class="global.goodsInf.fare ? 'blue-text' : 'decoration'">{{global.goodsInf.fare  | conversion}}</span>
                                </p>
                            </div>
                            <div style="width: 20%; height: 100%;">
                                <p flex="main:left">
                                    收货人昵称：
                                    <span :class="global.personalInf.nickname ? 'blue-text' : 'decoration'">{{global.personalInf.nickname  | conversion}}</span>
                                </p>
                                <p flex="main:left">
                                    收货人姓名：
                                    <span :class="global.personalInf.name ? 'blue-text' : 'decoration'">{{global.personalInf.name  | conversion}}</span>
                                </p>
                                <p style="width:170px;" flex="main:left">
                                    联系方式：
                                    <span :class="global.personalInf.payMethod ? 'blue-text' : 'decoration'">{{global.personalInf.payMethod  | conversion}}</span>
                                </p>
                                <p flex="main:left">
                                    支付方式：
                                    <span :class="global.personalInf.phone ? 'blue-text' : 'decoration'">{{global.personalInf.phone  | conversion}}</span>
                                </p>
                                <p style="width:170px;" flex="main:left">
                                    发货方式：
                                    <span :class="global.personalInf.shipMethod ? 'blue-text' : 'decoration'">{{global.personalInf.shipMethod  | conversion}}</span>
                                </p>
                                <p style="width:170px;" flex="main:left">
                                    收货地址：
                                    <span :class="global.personalInf.address ? 'blue-text' : 'decoration'">{{global.personalInf.address  | conversion}}</span>
                                </p>
                                <p style="width:170px;" flex="main:left">
                                    买家留言：
                                    <span :class="global.personalInf.leaveComments? 'blue-text' : 'decoration'">{{global.personalInf.leaveComments  | conversion}}</span>
                                </p>
                                <p style="width:170px;" flex="main:left">
                                    商家自定义：
                                    <span :class="global.sellerInf.remark ? 'blue-text' : 'decoration'">{{global.sellerInf.remark  | conversion}}</span>
                                </p>
                            </div>
                        </div>
                    </template>

                    <template v-if="edit">
                        <div style="margin-bottom: 40px">
                            <span>添加发货单模板</span>
                        </div>
                        <el-form label-width="125px">
                            <div class="tem-name"
                                 style="max-width:770px; height: 68px;line-height: 68px;border: 1px solid #e8eaee">
                                <el-form-item label="模板名称" style="700px">
                                    <el-input maxlength="8" v-model="template_name" show-word-limit></el-input>
                                </el-form-item>
                            </div>
                            <el-table
                                    :data="[{}]"
                                    style="width: 770px;margin-bottom: 15px"
                                    row-key="id"
                                    border
                                    lazy
                                    :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
                                <el-table-column
                                        prop="date"
                                        label="模板宽（mm）"
                                        width="180">
                                    <template slot-scope="scope">
                                        <el-input
                                                size="small"
                                                class="input-with-select"
                                                v-model="stencil_width"
                                        >
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="name"
                                        label="模板高（mm）"
                                        width="180">
                                    <template slot-scope="scope">
                                        <el-input
                                                size="small"
                                                class="input-with-select"
                                                v-model="stencil_high"
                                        >
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="address"
                                        label="左边距（mm）">
                                    <template slot-scope="scope">
                                        <el-input
                                                size="small"
                                                class="input-with-select"
                                                v-model="left_right_margins"
                                        >
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="address"
                                        label="模板边框宽度（mm）">
                                    <template slot-scope="scope">
                                        <el-input
                                                size="small"
                                                class="input-with-select"
                                                v-model="border_width"
                                        >
                                        </el-input>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="height: 140px;border: 1px solid #e8eaee;width: 770px;background-color: #f5f7fa;margin-bottom: 25px;"
                                 v-if="show === 0">
                                <div class="template-name"
                                     style="width:100%; height: 68px;line-height: 68px;border: 1px solid #e8eaee">
                                    <el-form-item label="发货单标题">
                                        <el-input maxlength="6" v-model="headline.name" show-word-limit></el-input>
                                    </el-form-item>
                                </div>
                                <el-form class="invoice-title" style="margin-top: 18px;" :inline="true" size="mini"
                                         flex="">
                                    <img @click="headline.bold = !headline.bold"
                                         :src="headline.bold ? 'statics/img/mall/bold-act.png' : 'statics/img/mall/bold.png'"
                                         style="width: 25px; height: 25px;margin-right:7px;cursor: pointer">
                                    <img @click="headline.italic = !headline.italic"
                                         :src="headline.italic ? 'statics/img/mall/italic-act.png':'statics/img/mall/italic.png'"
                                         style="width:25px;height: 25px;margin-right: 7px;cursor: pointer">
                                    <img @click="headline.underline = !headline.underline"
                                         :src="headline.underline ? 'statics/img/mall/underline-act.png': 'statics/img/mall/underline.png'"
                                         style="width: 25px; height: 25px;margin-right:7px;cursor: pointer">
                                    <el-form-item label="字体：" style="width: 155px" flex="">
                                        <el-select v-model="headline.fimaly">
                                            <el-option label="宋体" value="宋体"></el-option>
                                            <el-option label="微软雅黑" value="微软雅黑"></el-option>
                                            <el-option label="黑体" value="黑体"></el-option>
                                            <el-option label="华文中宋" value="华文中宋"></el-option>
                                            <el-option label="幼圆" value="幼圆"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="字大小：" flex="" style="width: 150px">
                                        <el-select v-model="headline.font">
                                            <el-option label="8pt" :value="8"></el-option>
                                            <el-option label="9pt" :value="9"></el-option>
                                            <el-option label="10pt" :value="10"></el-option>
                                            <el-option label="11pt" :value="11"></el-option>
                                            <el-option label="12pt" :value="12"></el-option>
                                            <el-option label="13pt" :value="13"></el-option>
                                            <el-option label="14pt" :value="14"></el-option>
                                            <el-option label="15pt" :value="15"></el-option>
                                            <el-option label="16pt" :value="16"></el-option>
                                            <el-option label="17pt" :value="17"></el-option>
                                            <el-option label="18pt" :value="18"></el-option>
                                            <el-option label="19pt" :value="19"></el-option>
                                            <el-option label="20pt" :value="20"></el-option>
                                            <el-option label="21pt" :value="21"></el-option>
                                            <el-option label="22pt" :value="22"></el-option>
                                            <el-option label="23pt" :value="23"></el-option>
                                            <el-option label="24pt" :value="24"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="对齐：" flex="" style="width: 135px">
                                        <el-select v-model="headline.align">
                                            <el-option label="居中" :value="0"></el-option>
                                            <el-option label="靠左" :value="1"></el-option>
                                            <el-option label="靠右" :value="2"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="字间距：" flex="" style="width: 155px">
                                        <el-select v-model="headline.space">
                                            <el-option label="-100pt" :value="-100"></el-option>
                                            <el-option label="-75pt" :value="-75"></el-option>
                                            <el-option label="-50pt" :value="-50"></el-option>
                                            <el-option label="-25pt" :value="-25"></el-option>
                                            <el-option label="-5pt" :value="-5"></el-option>
                                            <el-option label="0pt" :value="0"></el-option>
                                            <el-option label="5pt" :value="5"></el-option>
                                            <el-option label="10pt" :value="10"></el-option>
                                            <el-option label="25pt" :value="25"></el-option>
                                            <el-option label="50pt" :value="50"></el-option>
                                            <el-option label="75pt" :value="75"></el-option>
                                            <el-option label="100pt" :value="100"></el-option>
                                        </el-select>
                                    </el-form-item>
                                </el-form>
                            </div>
                            <div style="height: 70px;border: 1px solid #e8eaee;width: 770px;background-color: #f5f7fa;line-height:70px;padding-left: 18px;margin-bottom: 25px;"
                                 v-if="show === 1">
                                <el-checkbox v-model="order.orderNumber" label="订单号"></el-checkbox>
                                <el-checkbox v-model="order.time" label="订单时间"></el-checkbox>
                                <el-checkbox v-model="order.date" label="打印日期"></el-checkbox>
                            </div>
                            <div style="margin-bottom: 25px;height: 105px;border: 1px solid #e8eaee;width: 770px;background-color: #f5f7fa;line-height:40px;padding-left: 18px;"
                                 v-if="show === 2">
                                <el-checkbox v-model="personalInf.name" label="收货人姓名"></el-checkbox>
                                <el-checkbox v-model="personalInf.nickname" label="收货人昵称"></el-checkbox>
                                <el-checkbox v-model="personalInf.phone" label="联系方式"></el-checkbox>
                                <el-checkbox v-model="personalInf.payMethod" label="支付方式"></el-checkbox>
                                <el-checkbox v-model="personalInf.shipMethod" label="发货方式"></el-checkbox>
                                <el-checkbox v-model="personalInf.address" label="收货地址"></el-checkbox>
                                <el-checkbox v-model="personalInf.mention_address" label="自提门店地址"></el-checkbox>
                                <el-checkbox v-model="personalInf.leaveComments" label="买家留言"></el-checkbox>
                            </div>
                            <div style="margin-bottom: 25px;" class="goodsInf" v-if="show === 3">
                                <el-checkbox v-model="goodsInf.serial" label="序号"></el-checkbox>
                                <el-checkbox v-model="goodsInf.name" label="商品名称"></el-checkbox>
                                <el-checkbox v-model="goodsInf.attr" label="规格"></el-checkbox>
                                <el-checkbox v-model="goodsInf.number" label="数量"></el-checkbox>
                                <el-checkbox v-model="goodsInf.unit" label="单位"></el-checkbox>
                                <el-checkbox v-model="goodsInf.univalent" label="小计"></el-checkbox>
                                <el-checkbox v-model="goodsInf.article_number" label="货号"></el-checkbox>
                                <el-checkbox v-model="goodsInf.amount" label="订单金额"></el-checkbox>
                                <el-checkbox v-model="goodsInf.totalAmount" label="总数量"></el-checkbox>
                                <el-checkbox v-model="goodsInf.fare" label="运费"></el-checkbox>
                                <el-checkbox v-model="goodsInf.discount" label="优惠"></el-checkbox>
                                <el-checkbox v-model="goodsInf.actually_paid" label="实付"></el-checkbox>
                            </div>
                            <div style="margin-bottom: 25px;height: 70px;border: 1px solid #e8eaee;width: 770px;background-color: #f5f7fa;line-height:70px;padding-left: 18px;"
                                 v-if="show === 4">
                                <el-checkbox v-model="sellerInf.branch" label="网点名称"></el-checkbox>
                                <el-checkbox v-model="sellerInf.name" label="联系人"></el-checkbox>
                                <el-checkbox v-model="sellerInf.phone" label="联系方式"></el-checkbox>
                                <el-checkbox v-model="sellerInf.postcode" label="网点邮编"></el-checkbox>
                                <el-checkbox v-model="sellerInf.address" label="网点地址"></el-checkbox>
                                <el-checkbox v-model="sellerInf.remark" label="卖家备注"></el-checkbox>
                            </div>
                            <div style="margin-bottom: 25px;border: 1px solid #e8eaee;width: 770px;background-color: #f5f7fa;"
                                 v-if="show === 5">
                                <div style="width: 100%;height: 73px;border-right: 1px solid #e4e5eb;padding-left: 15px;padding: 20px 0 0 20px;"
                                     flex="dir:top main:center ">
                                        <span style="font-size: 13px;color: #999999;margin-bottom: 5px;"
                                              flex="cross:center">
                                            <span>图片上传</span>
                                            <el-tooltip effect="dark" content="最多六张" placement="top-start">
                                                <image src="statics/img/mall/order/prompt.png"
                                                       style="width:14px;height:14px;margin-left: 5px;"></image>
                                            </el-tooltip>
                                        </span>
                                    <app-attachment @selected="upadtePic">
                                        <el-button
                                                style="width: 80px;height:28px;padding: 0; text-align: center;margin-top: 5px;position: relative">
                                            选择图片
                                        </el-button>
                                    </app-attachment>
                                </div>
                                <div style="width: 100%; margin-top: 10px;padding: 5px 20px 20px 0px;" flex="">
                                    <draggable @change="draggaleChange" class="goods-list" flex v-model="img_url"
                                               ref="parentNode">
                                        <div class="goods-item drag-drop"
                                             style="margin: 10px 0 10px 20px;position: relative;width: 80px;"
                                             v-for="(item, index) in img_url">
                                            <app-image width="80px"
                                                       height="80px"
                                                       mode="aspectFill"
                                                       :src="item.url"
                                            >
                                            </app-image>
                                            <el-button class="del-btn" size="mini" @click="deleteImg(index, item.id)"
                                                       type="danger" icon="el-icon-close" circle></el-button>
                                        </div>
                                    </draggable>
                                </div>
                                <div style="height: 95px;border-bottom: 1px solid #e4e5eb;border-top: 1px solid #e4e5eb;"
                                     flex="">
                                    <div class="order-template"
                                         style="width: 33%;height: 100%;border-right: 1px solid #e4e5eb;padding-left: 15px;"
                                         flex="dir:top main:center ">
                                        <span style="font-size: 13px;color: #999999;margin-bottom: 5px;"
                                              flex="cross:center">
                                            <span>图片宽度（mm）：</span>
                                            <el-tooltip effect="dark" content="最大图片宽度限制为47mm" placement="top-start">
                                                <image src="statics/img/mall/order/prompt.png"
                                                       style="width:14px;height:14px;margin-left: 5px;"></image>
                                            </el-tooltip>
                                        </span>
                                        <el-input v-model.number="setImage.width" @input="numberChange"
                                                  @change="numberChange"
                                                  style="width: 120px; height: 30px;margin-top: 5px;"></el-input>
                                    </div>
                                    <div class="order-template"
                                         style="width: 33%;height: 100%;border-right: 1px solid #e4e5eb;padding-left: 15px;"
                                         flex="dir:top main:center ">
                                        <span style="font-size: 13px;color: #999999;margin-bottom: 5px;">上边距（mm）：</span>
                                        <el-input v-model="setImage.top"
                                                  style="width: 120px; height: 30px;margin-top: 5px;">
                                        </el-input>
                                    </div>
                                    <div class="order-template" style="width: 33%;height: 100%;padding-left: 15px;"
                                         flex="dir:top main:center ">
                                        <span style="font-size: 13px;color: #999999;margin-bottom: 5px;">左边距（mm）：</span>
                                        <el-input v-model="setImage.left"
                                                  style="width: 120px; height: 30px;margin-top: 5px;"></el-input>
                                    </div>
                                </div>
                                <div style="height: 122px;padding-left:15px;" flex="cross:center">
                                    <div style="height: 88px;"><span>备注</span></div>
                                    <div id="textarea"
                                         style="width: 580px;height:88px;border: 1px solid #e9ebf0;margin-left:13px;background-color: white"
                                         contenteditable></div>
                                    <!--                                    <el-input type="textarea" class="textarea" maxlength="100" v-model="customize" placeholder="最多输入100个字符" style="width: 580px;height:88px;border: 1px solid #e9ebf0;margin-left:13px;background-color: white"></el-input>-->
                                </div>
                            </div>
                        </el-form>
                        <div id="canvas"
                             :style="{padding: `0 ${mmConversionPx(left_right_margins) + 'px'}`, width: `${mmConversionPx(Number(left_right_margins) + Number(left_right_margins) + Number(stencil_width) + Number(border_width)+ Number(border_width)) + 'px'}`}">
                            <div id="invoice"
                                 :style="{width: mmConversionPx(stencil_width) + 'px',overflow: 'hidden',minHeight:mmConversionPx(stencil_high) + 'px', cursor: 'pointer', border: `${mmConversionPx(border_width)}px solid #000000`, boxSizing: 'content-box', margin: 0}">
                                <div flex="">
                                    <div :style="{
                                textAlign: headline.align === 0 ? 'center' : headline.align === 1 ? 'left' : 'right',
                                fontFamily: headline.fimaly,
                                textDecoration: headline.underline ? 'underline' : 'none',
                                fontWeight: headline.bold ? 'bold' : 'normal',
                                fontStyle: headline.italic ? 'italic' : 'normal',
                                width: '62%',
                                fontSize: headline.font / (4/3) + 'px',height: '50px',lineHeight: '50px', letterSpacing: headline.space / (4/3)+'px',backgroundColor: `${show === 0? '#ecf4ff' : '#ffffff'}`, borderBottom: `${!order.date && !order.time && !order.orderNumber ? '1px solid #000000' : 'none'}`}"
                                         class="title"
                                         @click="showTab(0)"
                                         @mouseenter="mouseenter(0, true)"
                                         @mouseleave="mouseleave(0, false)"
                                         :class="{backgroundmous: mouseIndex === 0}"
                                    >{{headline.name}}
                                    </div>
                                    <div flex="cross:center main:center" style="width: 38%;height: 50px;border-left: 1px solid #000000;text-align: center">
                                        <img style="width: 157px; height: 34px;line-height: 50px;" src="statics/img/mall/order/bar-code.png">
                                    </div>
                                </div>
                                <div
                                        @mouseenter="mouseenter(1, true)"
                                        @mouseleave="mouseleave(1, false)"
                                        :class="{backgroundmous: mouseIndex === 1}"
                                        v-if="order.date || order.time || order.orderNumber"
                                        :style="{display: 'flex',flexWrap:'wrap',borderTop: '1px solid #000000',borderBottom: '1px solid #000000',padding:'10px 10px 10px 0.5%', backgroundColor: `${show === 1? '#ecf4ff' : '#ffffff'}`}"
                                        @click="showTab(1)">
                                    <div style="width: 50%;font-size:10px;line-height:1;margin-bottom: 6px"
                                         v-if="order.date">打印日期：2020年2月10日
                                    </div>
                                    <div style="width: 50%;font-size:10px;line-height:1;" v-if="order.time">
                                        订单时间：2020年1月8日11:35:28
                                    </div>
                                    <div style="width: 50%;font-size:10px;line-height:1;" v-if="order.orderNumber">
                                        订单号：20200103113524825998
                                    </div>
                                </div>
                                <div @mouseenter="mouseenter(2, true)"
                                     @mouseleave="mouseleave(2, false)"
                                     :class="{backgroundmous: mouseIndex === 2}"
                                     :style="{display: 'flex',backgroundColor: `${show === 2? '#ecf4ff' : '#ffffff'}`}"
                                     v-if="personalInf.name || personalInf.nickname || personalInf.phone || personalInf.address || personalInf.leaveComments || personalInf.payMethod || personalInf.shipMethod"
                                     @click="showTab(2)">
                                    <div v-if="personalInf.name || personalInf.nickname || personalInf.phone || personalInf.address || personalInf.payMethod || personalInf.shipMethod"

                                         :style="{width: `${personalInf.leaveComments ? '62%' : '100%'}`,borderBottom:'1px solid #000000',borderRight: `${ personalInf.leaveComments ? '1px solid #000000': 'none'}`, padding:' 10px 10px 10px 0.5%'}"
                                    >
                                        <div style="font-size:10px;line-height:1.5;" v-if="personalInf.name">收货人信息：张三
                                        </div>
                                        <div style="font-size:10px;line-height:1.5;" v-if="personalInf.nickname">
                                            收货人昵称：屋顶上的小猫咪
                                        </div>
                                        <div style="font-size:10px;line-height:1.5;" v-if="personalInf.phone">
                                            联系方式：0573-82261300
                                        </div>
                                        <div style="font-size:10px;line-height:1.5;" v-if="personalInf.payMethod">
                                            支付方式：线上支付
                                        </div>
                                        <div style="font-size:10px;line-height:1.5;" v-if="personalInf.shipMethod">
                                            发货方式：快递配送
                                        </div>
                                        <div style="font-size:10px;line-height:1.5;" v-if="personalInf.address">
                                            收货地址：湖北省长沙市蔡锷北路三单元201号
                                        </div>
                                    </div>
                                    <div :style="{width: `${personalInf.name || personalInf.nickname || personalInf.phone || personalInf.address || personalInf.payMethod || personalInf.shipMethod? '38%' : '100%'}`,borderBottom:'1px solid #000000',padding: '10px 10px 10px 0.5%', fontSize:'10px', lineHeight:'1.2'}"
                                         v-if="personalInf.leaveComments">
                                        买家留言：尽快发货，发中通快递
                                    </div>
                                </div>
                                <div @click="showTab(3)"
                                     @mouseenter="mouseenter(3, true)"
                                     @mouseleave="mouseleave(3, false)"
                                     :class="{backgroundmous: mouseIndex === 3}"
                                     :style="{backgroundColor: `${show === 3? '#ecf4ff' : '#ffffff'}`}">
                                    <div style="display: flex;border-bottom:1px solid #000000;position: relative;left: -0.5px;"
                                         v-if="goodsInf.serial || goodsInf.name || goodsInf.attr || goodsInf.number || goodsInf.univalent || goodsInf.article_number || goodsInf.unit">
                                        <div style="width: 6%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;"
                                             v-if="goodsInf.serial">序号
                                        </div>
                                        <div  style="display: flex;min-width: 0;-webkit-box-flex: 1;flex-grow: 1;flex-shrink: 1;" v-if="goodsInf.name || goodsInf.attr">
                                            <div :style="{width: goodsInf.name && goodsInf.attr ? '60%' : '100%'}" v-if="goodsInf.name" style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;">商品名称
                                            </div>
                                            <div :style="{width: goodsInf.name && goodsInf.attr ? '40%' : '100%'}" v-if="goodsInf.attr" style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;">规格
                                            </div>
                                        </div>
                                        <div style="width: 8%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 10px;font-size:10px;"
                                             v-if="goodsInf.number">数量
                                        </div>
                                        <div style="width: 12%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 10px;font-size:10px;"
                                             v-if="goodsInf.univalent">小计
                                        </div>
                                        <div style="width: 18%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 10px;font-size:10px;"
                                             v-if="goodsInf.article_number">货号
                                        </div>
                                        <div style="width: 8%;height: 30px;line-height: 30px;border-left: 1px solid #000000;padding-left: 10px;font-size:10px;"
                                             v-if="goodsInf.unit">单位
                                        </div>
                                    </div>
                                    <div style="display: flex;border-bottom: 1px solid #000000;position: relative;left: -0.5px;"
                                         v-if="goodsInf.serial || goodsInf.name || goodsInf.attr || goodsInf.number || goodsInf.univalent || goodsInf.article_number || goodsInf.unit">
                                        <div style="width: 6%;word-wrap: break-word;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                             v-if="goodsInf.serial">
                                            <span>1</span>
                                        </div>
                                       <div style="display: flex;min-width: 0;-webkit-box-flex: 1;flex-grow: 1;flex-shrink: 1;" v-if="goodsInf.name || goodsInf.attr">
                                           <div :style="{width: goodsInf.name && goodsInf.attr ? '60%' : '100%'}" style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap: break-word;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                                v-if="goodsInf.name">
                                               <span>男士羊毛衫</span>
                                           </div>
                                           <div :style="{width: goodsInf.name && goodsInf.attr ? '40%' : '100%'}" style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap: break-word;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                                v-if="goodsInf.attr">
                                            <span style="position: relative;transform:translate(-50%,-50%);width: calc(100% - 20px);word-wrap: break-word;"
                                            >默认</span>
                                           </div>
                                       </div>
                                        <div style="width: 8%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                             v-if="goodsInf.number">
                                            <span style="position: relative;transform:translate(-50%,-50%);width: calc(100% - 20px);;word-wrap: break-word;">1</span>
                                        </div>
                                        <div style="width: 12%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                             v-if="goodsInf.univalent">
                                            <span style="position: relative;transform:translate(-50%,-50%);word-wrap: break-word;width: calc(100% - 20px);">￥110</span>
                                        </div>
                                        <div style="width: 18%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                             v-if="goodsInf.article_number">
                                            <span style="position: relative;transform:translate(-50%,-50%);word-wrap: break-word;width: calc(100% - 20px);">56</span>
                                        </div>
                                        <div style="width: 8%;word-wrap: break-word;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                             v-if="goodsInf.unit">
                                            <span style="position: relative;transform:translate(-50%,-50%);">件</span>
                                        </div>
                                    </div>

                                    <div style="display: flex;height: 30px;padding-left: .5%;border-bottom:1px solid #000000;font-size: 10px"
                                         v-if="goodsInf.amount || goodsInf.fare || goodsInf.discount || goodsInf.actually_paid || goodsInf.totalAmount">
                                        <div style="width: 24%;height: 30px;line-height:30px;" v-if="goodsInf.amount">
                                            订单金额：￥110.00
                                        </div>
                                        <div style="width: 16%;height: 30px;line-height:30px;" v-if="goodsInf.totalAmount">
                                            总数量：1
                                        </div>
                                        <div style="width: 21%;height: 30px;line-height:30px;" v-if="goodsInf.fare">
                                            运费：￥0.00
                                        </div>
                                        <div style="width: 20%;height: 30px;line-height:30px;" v-if="goodsInf.discount">
                                            优惠：￥0.00
                                        </div>
                                        <div style="width: 19%;height: 30px;line-height:30px;"
                                             v-if="goodsInf.actually_paid">实付：￥110.00
                                        </div>
                                    </div>
                                </div>
                                <div @click="showTab(4)"
                                     @mouseenter="mouseenter(4, true)"
                                     @mouseleave="mouseleave(4, false)"
                                     :class="{backgroundmous: mouseIndex === 4}"
                                     :style="{display:'flex',borderBottom:'1px solid #000000',backgroundColor: `${show === 4? '#ecf4ff' : '#ffffff'}`}"
                                     v-if="sellerInf.branch || sellerInf.name || sellerInf.phone || sellerInf.postcode || sellerInf.address || sellerInf.remark">
                                    <div :style="{width:`${!sellerInf.remark ? '100%': '62%'}`,padding: '10px 10px 10px .5%', fontSize: '10px',borderRight: `${!sellerInf.remark ? 'none' : '1px solid #000000'}`}"
                                         v-if="sellerInf.branch || sellerInf.name || sellerInf.phone || sellerInf.postcode || sellerInf.address">
                                        <div v-if="sellerInf.branch">网点名称：中国建设银行一营业网点</div>
                                        <div v-if="sellerInf.name">联系人：小王</div>
                                        <div v-if="sellerInf.phone">联系方式：15795411230</div>
                                        <div v-if="sellerInf.postcode">网点邮编：351102</div>
                                        <div v-if="sellerInf.address">网点地址：咸阳市迎宾大道202研究所家属区门口</div>
                                    </div>
                                    <div :style="{width: `${!sellerInf.branch && !sellerInf.name && !sellerInf.phone && !sellerInf.postcode && !sellerInf.address ? '100%' : '38%'}`,padding: '10px 10px 10px .5%', fontSize: '10px'}"
                                         v-if="sellerInf.remark">
                                        卖家备注：欢迎光临本店，祝亲购物愉快！
                                    </div>
                                </div>
                                <div
                                        @mouseenter="mouseenter(5, true)"
                                        @mouseleave="mouseleave(5, false)"
                                        :class="{backgroundmous: mouseIndex === 5}"
                                        :style="{padding: '10px 10px 10px .5%', backgroundColor: `${show === 5? '#ecf4ff' : '#ffffff'}`, fontSize: '10px'}"
                                        @click="showTab(5)">
                                    <div style="width: 100%;" flex="">
                                        <div v-html="customize" style="width: 100%;word-wrap:break-word;">
                                            {{customize}}
                                        </div>
                                    </div>
                                    <div v-html="customize_image"
                                         style="width: 100%;margin-top: 10px;word-wrap:break-word;">{{customize_image}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-card>
                <el-card v-if="!edit" style="marginTop: 15px;">
                    <div flex="main:justify" style="margin-bottom: 35px;">
                        <span>发货单模板</span>
                        <el-button type="primary" size="small" @click="addInvoiceTemplate">添加发货单模板</el-button>
                    </div>
                    <el-table
                            max-height="360"
                            :data="template_list"
                            border
                            @cell-click="cellClick"
                            style="width: 100%">
                        <el-table-column
                                prop="name"
                                label="模板名称"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="username"
                                label="模板大小（mm）"
                                width="180">
                            <template slot-scope="scope">
                                {{scope.row.params.stencil_width}}*{{scope.row.params.stencil_high}}
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="mobile"
                                label="左边距（mm）">
                            <template slot-scope="scope">
                                <div class="offset">
                                    <span style="margin-right: 30px;">
                                        左：
                                        <el-input @change="changeOffset(scope.row.id)" type="text"
                                                  style="height: 30px;width: 40px;"
                                                  v-model="scope.row.params.left_right_margins">
                                    </span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="code"
                                label="操作">
                            <template slot-scope="scope">
                                <div flex="">
                                    <el-tooltip v-if="scope.row.is_default !== 1" class="item" effect="dark"
                                                content="编辑" placement="top">
                                        <img class="app-order-icon"
                                             @click.stop="editTemplate(scope.row)"
                                             src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="打印测试" placement="top">
                                        <img class="app-order-icon"
                                             @click.stop="printTest(scope.row)"
                                             src="statics/img/mall/order/print.png" alt="">
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="打印预览" placement="top">
                                        <img class="app-order-icon"
                                             @click.stop="printPreview(scope.row)"
                                             src="statics/img/mall/order/preview.png" alt="">
                                    </el-tooltip>
                                    <el-tooltip v-if="scope.row.is_default !== 1" class="item" effect="dark"
                                                content="删除" placement="top">
                                        <img class="app-order-icon"
                                             @click.stop="deleteTemp(scope.row)"
                                             src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                    <el-tooltip  class="item" effect="dark"
                                                content="打印设置" placement="top">
                                        <img class="app-order-icon"
                                             @click.stop="setting(scope.row, scope.$index)"
                                             src="statics/img/plugins/setting.png" alt="">
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-card>
                <el-dialog
                        title="预览"
                        :visible.sync="isPrintPreview"
                        width="40%"
                >
                    <div style="width: 100%; position: relative;">
                        <image :src="temp_url.src"
                               :style="{width: '100%', position: 'relative',transform: 'translateX(-50%)' , left: '50%'}"></image>
                    </div>
                    <span slot="footer" class="dialog-footer">
                        <el-button type="primary" @click="isPrintPreview = false">确 定</el-button>
                    </span>
                </el-dialog>
            </el-tab-pane>
            <el-tab-pane label="批量打印" name="second">
                <el-card>
                    <app-order
                        :tabs="tabs"
                        :is-show-check-box="true"
                        :is-show-cancel="false"
                        :is-show-platform="false"
                        :is-show-order-type="false"
                        :is-show-print-action="true"
                        :is-show-action="false"
                        :is-show-header="false"
                        active-name="-1"
                        :is-show-export="false"
                        :is-show-recycle="false"
                        :is-show-order-plugin="true"
                        :is-show-print-invoice="true"
                        @select_template="select_template"
                        @print_invoice="print_invoice"
                        :is-send-template="true"
                        :select-list="selectList"
                        order-url="mall/order-send-template/order"
                    ></app-order>
                </el-card>
            </el-tab-pane>
        </el-tabs>
        <el-button type="primary" @click="save" :loading="saveloading" v-if="edit && !showList">保存</el-button>
        <div id="print" v-show="false" class="print-div">
            <div v-for="(item) in printData"
                 :style="{padding: `0 ${mmConversionPx(printPar.left_right_margins) + 'px'}`,
                 pageBreakBefore: `${printPar.printSetting.page_type === 2 ? 'always' : 'auto'}`,
                 width: `${mmConversionPx(Number(printPar.left_right_margins) + Number(printPar.left_right_margins) + Number(printPar.stencil_width) + Number(printPar.border_width)+ Number(printPar.border_width)) + 'px'}`, marginBottom: `${printPar.printSetting.page_type === 1 ? printPar.printSetting.space + 'px' : '0'}`}">
                <div id="invoice"
                     :style="{width: mmConversionPx(printPar.stencil_width) + 'px', marginLeft: printPar.offset.left + 'px', marginRight: printPar.offset.right + 'px',minHeight:mmConversionPx(printPar.stencil_high) + 'px', cursor: 'pointer', border: `${mmConversionPx(printPar.border_width)}px solid #000000`, boxSizing: 'content-box', margin: 0}">
                   <div style="display: inline-block;position: relative;white-space: nowrap;width: 100%;height: 50px">
                       <div :style="{
                                textAlign: printPar.headline.align === 0 ? 'center' : printPar.headline.align === 1 ? 'left' : 'right',
                                fontFamily: printPar.headline.fimaly,
                                textDecoration: printPar.headline.underline ? 'underline' : 'none',
                                fontWeight: printPar.headline.bold ? 'bold' : 'normal',
                                fontStyle: printPar.headline.italic ? 'italic' : 'normal',
                                width: '62%',
                                fontSize: printPar.headline.font / (4/3) + 'px',
                                height: '50px',
                                boxSizing: 'border-box',
                                display: 'inline-block',
                                lineHeight: '50px',
                                position: 'absolute',
                                top: 0,
                                letterSpacing: printPar.headline.space / (4/3)+'px',
                                borderBottom: `${!printPar.order.date && !printPar.order.time && !printPar.order.orderNumber ? '1px solid #000000' : 'none'}`
                             }"
                            class="title"
                       >{{printPar.headline.name}}
                       </div>
                       <div style="width: 38%;height: 50px;border-left: 1px solid #000000;padding: 8px 0;text-align: center; box-sizing: border-box;display: inline-block;position: absolute;right: 0;">
                           <img :id="'code_' + item.order_no" style="height: 34px;"/>
                       </div>
                   </div>
                    <div
                        v-if="printPar.order.date || printPar.order.time || printPar.order.orderNumber"
                        :style="{display: 'flex',flexWrap:'wrap',borderTop: '1px solid #000000',borderBottom: '1px solid #000000',padding:'10px 10px 10px 0.5%', boxSizing: 'border-box'}"
                    >
                        <div style="width: 50%;font-size:10px;line-height:1;margin-bottom: 6px"
                             v-if="printPar.order.date">打印日期：{{printTime}}
                        </div>
                        <div style="width: 50%;font-size:10px;line-height:1;" v-if="printPar.order.time">
                            订单时间：{{item.pay_time}}
                        </div>
                        <div style="width: 50%;font-size:10px;line-height:1;" v-if="printPar.order.orderNumber">
                            订单号：{{item.order_no}}
                        </div>
                    </div>
                    <div :style="{display: 'flex', boxSizing: 'border-box'}"
                         v-if="printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address || printPar.personalInf.leaveComments || printPar.personalInf.payMethod || printPar.personalInf.shipMethod">
                        <div v-if="printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod"
                             :style="{width: `${printPar.personalInf.leaveComments ? '62%' : '100%'}`, boxSizing: 'border-box', borderBottom:'1px solid #000000',borderRight: `${ printPar.personalInf.leaveComments ? '1px solid #000000': 'none'}`, padding:'10px 10px 10px 0.5%'}"
                        >
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.name">
                                收货人信息：{{item.name}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.nickname">
                                收货人昵称：{{item.nickname}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.phone">
                                联系方式：{{item.mobile}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.payMethod">
                                支付方式：{{item.pay_type == 1 ? '在线支付' : item.pay_type == 2 ? '货到付款' : item.pay_type == 3 ?
                                '余额支付' : ''}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;"
                                 v-if="printPar.personalInf.shipMethod && item.send_type != 1">发货方式：{{item.send_type ==
                                0 ? '快递配送' : item.send_type == 1 ? '到店自提' : item.send_type == 2 ? '同城配送' : ''}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;"
                                 v-if="printPar.personalInf.address && item.send_type != 1">收货地址：{{item.address}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;"
                                 v-if="printPar.personalInf.mention_address && item.send_type == 1">
                                自提门店地址：{{item.store_address}}
                            </div>
                        </div>
                        <div :style="{width: `${printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod ? '38%' : '100%'}`,borderBottom:'1px solid #000000',padding: '10px 10px 10px 0.5%', fontSize:'10px', boxSizing: 'border-box', lineHeight:'1.2', boxSizing: 'border-box'}"
                             v-if="printPar.personalInf.leaveComments">
                            买家留言：{{item.remark}}
                        </div>
                    </div>
                    <div style="width: 100%;box-sizing:border-box;">
                        <div style="display: flex;border-bottom:1px solid #000000;width: 100%;position: relative;left: -0.5px;"
                             v-if="printPar.goodsInf.serial || printPar.goodsInf.name || printPar.goodsInf.attr || printPar.goodsInf.number || printPar.goodsInf.univalent || printPar.goodsInf.article_number || printPar.goodsInf.unit">
                            <div style="width: 6%;border-left: 1px solid #000000;box-sizing:border-box;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;"
                                 v-if="printPar.goodsInf.serial">序号
                            </div>
                            <div :style="tableWidth" style="display: flex;" v-if="printPar.goodsInf.name || printPar.goodsInf.attr">
                                <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;width: 60%;border-left: 1px solid #000000;box-sizing:border-box;height: 30px;line-height: 30px;padding-left: 10px;font-size:10px;"
                                     :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '60%' : '100%'}"
                                     v-if="printPar.goodsInf.name"
                                >商品名称
                                </div>
                                <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;width: 40%;border-left: 1px solid #000000;box-sizing:border-box;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;"
                                     :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '40%' : '100%'}"
                                     v-if="printPar.goodsInf.attr">规格
                                </div>
                            </div>
                            <div style="width: 8%;border-left: 1px solid #000000;box-sizing:border-box;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;"
                                 v-if="printPar.goodsInf.number">数量
                            </div>
                            <div style="width: 12%;border-left: 1px solid #000000;box-sizing:border-box;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;"
                                 v-if="printPar.goodsInf.univalent">小计
                            </div>
                            <div style="width: 18%;border-left: 1px solid #000000;box-sizing:border-box;height: 30px;line-height: 30px;padding-left: .5%;font-size:10px;"
                                 v-if="printPar.goodsInf.article_number">货号
                            </div>
                            <div style="width: 8%;height: 30px;line-height: 30px;box-sizing:border-box;border-left: 1px solid #000000;padding-left: 10px;font-size:10px;"
                                 v-if="printPar.goodsInf.unit">单位
                            </div>
                        </div>
                        <div v-for="good in item.detail"
                             style="display: flex;border-bottom: 1px solid #000000;width: 100%;position: relative;left: -0.5px;"
                             v-if="printPar.goodsInf.serial || printPar.goodsInf.name || printPar.goodsInf.attr || printPar.goodsInf.number || printPar.goodsInf.univalent || printPar.goodsInf.article_number || printPar.goodsInf.unit">
                            <div style="word-wrap:break-word;width: 6%;box-sizing:border-box;border-left: 1px solid #000000;padding: 10px 10px 10px .5%;font-size:10px;position: relative"
                                 v-if="printPar.goodsInf.serial">
                                {{good.id}}
                            </div>
                           <div :style="tableWidth" style="display: flex;" v-if="printPar.goodsInf.name || printPar.goodsInf.attr">
                               <div v-if="printPar.goodsInf.name"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '60%' : '100%'}"
                                    style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;width: 60%;word-wrap:break-word;box-sizing:border-box;border-left: 1px solid #000000;padding: 10px 10px 10px .5%;font-size:10px;position: relative">
                                   {{good.name}}
                               </div>
                               <div v-if="printPar.goodsInf.attr"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '40%' : '100%'}"
                                    style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap:break-word;box-sizing:border-box;border-left: 1px solid #000000;font-size:10px ;padding: 10px 10px 10px .5%;position: relative">
                                   {{good.attr}}
                               </div>
                           </div>
                            <div style="word-wrap:break-word;width: 8%;box-sizing:border-box;border-left: 1px solid #000000;font-size:10px;padding: 10px 0 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.number">
                                {{good.num}}
                            </div>
                            <div style="word-wrap:break-word;width: 12%;box-sizing:border-box;border-left: 1px solid #000000;font-size:10px;padding: 10px 0 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.univalent">
                                ￥{{good.price}}
                            </div>
                            <div style="word-wrap:break-word;width: 18%;box-sizing:border-box;border-left: 1px solid #000000;font-size:10px;padding: 10px 0 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.article_number">
                                {{good.goods_no}}
                            </div>
                            <div style="word-wrap:break-word;width: 8%;border-left: 1px solid #000000;box-sizing:border-box;word-wrap: break-word;font-size:10px;padding: 10px 0 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.unit">
                                {{good.unit}}
                            </div>
                        </div>

                        <div style="display: flex;height: 30px;padding-left: 0.5%;border-bottom:1px solid #000000;font-size: 10px"
                             v-if="printPar.goodsInf.amount || printPar.goodsInf.totalAmount || printPar.goodsInf.fare || printPar.goodsInf.discount || printPar.goodsInf.actually_paid">
                            <div style="width: 24%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.amount">
                                订单金额：￥{{item.total_goods_original_price}}
                            </div>
                            <div style="width: 16%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.totalAmount">
                                总数量：{{item.goods_num}}
                            </div>
                            <div style="width: 21%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.fare">
                                运费：￥{{item.express_price}}
                            </div>
                            <div style="width: 20%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.discount">
                                优惠：￥{{item.send_template_discount_price}}
                            </div>
                            <div style="width: 19%;height: 30px;line-height:30px;"
                                 v-if="printPar.goodsInf.actually_paid">实付：￥{{item.total_pay_price}}
                            </div>
                        </div>
                    </div>
                    <div :style="{display:'flex',borderBottom:'1px solid #000000', boxSizing: 'border-box'}"
                         v-if="printPar.sellerInf.branch || printPar.sellerInf.name || printPar.sellerInf.phone || printPar.sellerInf.postcode || printPar.sellerInf.address || printPar.sellerInf.remark">
                        <div v-if="address_list.length>0"
                             :style="{width:`${!printPar.sellerInf.remark ? '100%': '62%'}`,padding: ' 10px 10px 10px .5%', fontSize: '10px',borderRight: `${!printPar.sellerInf.remark ? 'none' : '1px solid #000000'}`, boxSizing: 'border-box'}"
                             v-if="printPar.sellerInf.branch || printPar.sellerInf.name || printPar.sellerInf.phone || printPar.sellerInf.postcode || printPar.sellerInf.address">
                            <div v-if="printPar.sellerInf.branch">网点名称：{{address_list[0].name}}</div>
                            <div v-if="printPar.sellerInf.name">联系人：{{address_list[0].username}}</div>
                            <div v-if="printPar.sellerInf.phone">联系方式：{{address_list[0].mobile}}</div>
                            <div v-if="printPar.sellerInf.postcode">网点邮编：{{address_list[0].code}}</div>
                            <div v-if="printPar.sellerInf.address">
                                网点地址：{{address_list[0].province}}{{address_list[0].city}}{{address_list[0].district}}{{address_list[0].address}}
                            </div>
                        </div>
                        <div :style="{boxSizing: 'border-box',width: `${!printPar.sellerInf.branch && !printPar.sellerInf.name && !printPar.sellerInf.phone && !printPar.sellerInf.postcode && !printPar.sellerInf.address ? '100%' : '38%'}`,padding: ' 10px 10px 10px .5%', fontSize: '10px'}"
                             v-if="printPar.sellerInf.remark">
                            卖家备注：{{item.seller_remark}}
                        </div>
                    </div>
                    <div flex="" :style="{padding: '10px 10px 10px 0.5%', fontSize: '10px', boxSizing: 'border-box'}">
                        <div style="width: 100%;" flex="">
                            <div v-html="printPar.customize" style="width: 100%;word-wrap:break-word;">
                                {{printPar.customize}}
                            </div>
                        </div>
                        <div v-html="printPar.customize_image"
                             style="width: 100%;margin-top: 10px;word-wrap:break-word;">{{printPar.customize_image}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </el-card>
</div>

<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/html2canvas.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/JsBarcode.all.min.js"></script>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                tabs: [
                    {value: '-1', name: '全部'},
                    {value: '1', name: '待发货'},
                    {value: '2', name: '待收货'}
                ],
                selectList: [
                    {value: '1', name: '订单号'},
                    {value: '5', name: '商品名称'}
                ],
                edit: false,
                showList: false,
                activeName: 'first',
                show: 0,
                template_name: '模板',
                stencil_width: 204,
                stencil_high: 142,
                left_right_margins: 0,
                border_width: 1,
                address_list: [],
                template_list: [],
                global: {
                    order: {
                        orderNumber: true,
                        time: true,
                        date: true
                    },
                    personalInf: {
                        name: true,
                        nickname: true,
                        phone: true,
                        address: true,
                        shipMethod: true,
                        payMethod: true,
                        mention_address: true,
                        leaveComments: true
                    },
                    goodsInf: {
                        serial: true,
                        name: true,
                        attr: true,
                        number: true,
                        unit: true,
                        univalent: true,
                        article_number: true,
                        amount: true,
                        totalAmount: true,
                        fare: true,
                        discount: true,
                        actually_paid: true
                    },
                    sellerInf: {
                        branch: true,
                        name: true,
                        phone: true,
                        postcode: true,
                        address: true,
                        remark: true
                    },
                },
                headline: {
                    name: '发货单',
                    fimaly: "微软雅黑",
                    font: 16,
                    align: 0,
                    line: 48,
                    space: 0,
                    bold: true,
                    italic: false,
                    underline: false
                },
                order: {
                    orderNumber: true,
                    time: true,
                    date: true
                },
                personalInf: {
                    name: true,
                    nickname: true,
                    phone: true,
                    shipMethod: true,
                    address: true,
                    payMethod: true,
                    mention_address: true,
                    leaveComments: true
                },
                goodsInf: {
                    serial: true,
                    name: true,
                    attr: true,
                    number: true,
                    unit: true,
                    univalent: true,
                    article_number: true,
                    amount: true,
                    fare: true,
                    discount: true,
                    totalAmount: true,
                    actually_paid: true
                },
                sellerInf: {
                    branch: true,
                    name: true,
                    phone: true,
                    postcode: true,
                    address: true,
                    remark: true
                },
                outletInformation: false,
                outletform: {
                    address: '',
                    name: '',
                    username: '',
                    mobile: '',
                    code: ''
                },
                district: [],
                props: {
                    value: 'name',
                    label: 'name',
                    children: 'list'
                },
                rules: {
                    name: [
                        {required: true, message: '请输入网点名称', trigger: 'blur'}
                    ],
                    username: [
                        {required: true, message: '请输入网点名称', trigger: 'blur'}
                    ],
                    mobile: [
                        {required: true, message: '请输入网点名称', trigger: 'blur'}
                    ],
                    area: [
                        {required: true, message: '请输入网点名称', trigger: 'blur'}
                    ],
                    address: [
                        {required: true, message: '请输入网点名称', trigger: 'blur'}
                    ]
                },
                isPrintPreview: false,
                temp_url: {
                    src: ''
                },
                template_name_1: '默认模板',
                printPar: {
                    printSetting: {
                        page_type: 1,
                        space: 10
                    },
                    order: {
                        orderNumber: true,
                        time: true,
                        date: true
                    },
                    personalInf: {
                        name: true,
                        shipMethod: true,
                        nickname: true,
                        payMethod: true,
                        mention_address: true,
                        phone: true,
                        address: true,
                        leaveComments: true
                    },
                    goodsInf: {
                        serial: true,
                        name: true,
                        attr: true,
                        number: true,
                        unit: true,
                        univalent: true,
                        article_number: true,
                        amount: true,
                        fare: true,
                        discount: true,
                        actually_paid: true,
                        totalAmount: true
                    },
                    sellerInf: {
                        branch: true,
                        name: true,
                        phone: true,
                        postcode: true,
                        address: true,
                        remark: true
                    },
                    headline: {
                        name: '发货单',
                        fimaly: "微软雅黑",
                        font: 16,
                        align: 0,
                        line: 48,
                        space: -100
                    },
                    stencil_width: 204,
                    stencil_high: 142,
                    left_right_margins: 0,
                    border_width: 1,
                    customize_image: '',
                    offset: {
                        left: 0,
                        right: 0
                    },
                },
                printData: [],
                printTime: '2020年2月10日',
                changeOffsetId: 0,
                saveloading: false,
                setImage: {
                    width: 47,
                    top: 0,
                    left: 0,
                },
                customize: '',
                customize_image: '',
                is_customize: false,
                img_url: [],
                mouseIndex: 0,
                template_id: -1,
                settingVisible: false,
                printSetting: {
                    page_type: 1,
                    space: 10,
                },
                settingIndex: 0,
                tableWidth: 'width:48%'
            };
        },

        methods: {
            editTemplate(item) {
                let {params, name, id} = item;
                this.template_id = id;
                this.edit = true;
                this.order = params.order;
                this.personalInf = params.personalInf;
                this.goodsInf = params.goodsInf;
                this.sellerInf = params.sellerInf;
                this.headline = params.headline;
                this.customize = params.customize;
                this.customize_image = params.customize_image;
                this.img_url = params.img_url;
                this.border_width = params.border_width;
                this.left_right_margins = params.left_right_margins;
                this.stencil_high = params.stencil_high;
                this.stencil_width = params.stencil_width;
                this.template_name = name;
            },
            mouseenter(index) {
                this.mouseIndex = index;
            },
            mouseleave() {
                this.mouseIndex = -1;
            },
            addInvoiceTemplate() {
                this.edit = true;
                this.customize = '';
                this.customize_image = '';
                this.img_url = [];
            },
            deleteImg(index, id) {
                this.$delete(this.img_url, index);
                let child = document.getElementById('customize_image_' + id);
                child.parentNode.removeChild(child);
                this.customize_image = '';
                for (let i = 0; i < this.img_url.length; i++) {
                    let srt = `style='width:${this.img_url[i].width}px;margin-left:${this.img_url[i].marginLeft}px;margin-top: ${this.img_url[i].marginTop}px'`;
                    this.customize_image += `<image id='customize_image_${this.img_url[i].id}' src='${this.img_url[i].local_url}' ${srt}/>`;
                }
            },
            change() {
            },
            getImage(e) {
            },
            print_invoice(express_list, order_list) {
                this.printData = [];
                if (order_list.length === 0) {
                    this.$message({
                        message: '请先勾选订单',
                        type: 'warning'
                    });
                    return;
                }
                let date = new Date();
                let Y = date.getFullYear() + '年';
                let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
                let D = date.getDate() + '日';
                this.printPar = express_list.params;
                this.printTime = Y + M + D;
                for (let i = 0; i < order_list.length; i++) {
                    let discount_price = Number(order_list[i].member_discount_price) + Number(order_list[i].coupon_discount_price);
                    if (order_list[i].detailExpress.length > 0) {
                        let new_detail = JSON.parse(JSON.stringify(order_list[i].detail));
                        let detail = order_list[i].detail;
                        for (let j = 0; j < detail.length; j++) {
                            for (let k = 0; k < order_list[i].detailExpress.length; k++) {
                                for (let m = 0; m < order_list[i].detailExpress[k].expressRelation.length; m++) {
                                    if (order_list[i].detailExpress[k].expressRelation[m].order_detail_id === detail[j].id) {
                                        for (let p = 0; p < new_detail.length; p++) {
                                            if (new_detail[p].id === order_list[i].detailExpress[k].expressRelation[m].order_detail_id) {
                                                this.$set(order_list[i].detailExpress[k].expressRelation[m], 'num', new_detail[p].num);
                                                this.$set(order_list[i].detailExpress[k].expressRelation[m], 'goods', new_detail[p].goods);
                                                this.$set(order_list[i].detailExpress[k].expressRelation[m], 'total_price', new_detail[p].total_price);
                                                new_detail.splice(p, 1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        for (let k = 0; k < order_list[i].detailExpress.length; k++) {
                            let detail = [];
                            if (order_list[i].detailExpress[k].expressRelation.length === 0) break;
                            for (let j = 0; j < order_list[i].detailExpress[k].expressRelation.length; j++) {
                                detail.push({
                                    name: order_list[i].detailExpress[k].expressRelation[j].goods.goodsWarehouse.name,
                                    num: order_list[i].detailExpress[k].expressRelation[j].num,
                                    unit: order_list[i].detailExpress[k].expressRelation[j].goods.goodsWarehouse.unit,
                                    price: order_list[i].detailExpress[k].expressRelation[j].total_price,
                                    id: i + 1,
                                    goods_no: order_list[i].detailExpress[k].expressRelation[j].orderDetail.goods_no,
                                    attr: this.getGoodsAttr(order_list[i].detailExpress[k].expressRelation[j].orderDetail.goods_info.attr_list)
                                });
                            }
                            let data = {
                                order_no: order_list[i].order_no,
                                pay_time: order_list[i].pay_time,
                                name: order_list[i].name,
                                nickname: order_list[i].nickname,
                                mobile: order_list[i].mobile,
                                address: order_list[i].address,
                                remark: order_list[i].remark,
                                seller_remark: order_list[i].seller_remark,
                                words: order_list[i].words,
                                pay_type: order_list[i].pay_type,
                                total_goods_price: order_list[i].total_goods_price,
                                total_goods_original_price: order_list[i].total_goods_original_price,
                                total_pay_price: order_list[i].total_pay_price,
                                express_price: order_list[i].express_price,
                                discount_price: discount_price,
                                send_type: order_list[i].send_type,
                                goods_num: order_list[i].goods_num,
                                detail: detail,
                                send_template_discount_price: order_list[i].send_template_discount_price
                            };
                            if (order_list[i].send_type == 1) {
                                data.store_address = order_list[i].store.address;
                            }
                            this.printData.push(data);
                        }
                    } else {
                        // 不发货的情况
                        let detail = [];
                        for (let j = 0; j < order_list[i].detail.length; j++) {
                            detail.push({
                                name: order_list[i].detail[j].goods.goodsWarehouse.name,
                                num: order_list[i].detail[j].num,
                                unit: order_list[i].detail[j].goods.goodsWarehouse.unit,
                                price: order_list[i].detail[j].total_price,
                                id: j + 1,
                                goods_no: order_list[i].detail[j].goods_no,
                                attr: this.getGoodsAttr(order_list[i].detail[j].goods_info.attr_list)
                            })
                        }
                        let data = {
                            order_no: order_list[i].order_no,
                            pay_time: order_list[i].pay_time,
                            name: order_list[i].name,
                            nickname: order_list[i].nickname,
                            mobile: order_list[i].mobile,
                            address: order_list[i].address,
                            remark: order_list[i].remark,
                            seller_remark: order_list[i].seller_remark,
                            words: order_list[i].words,
                            pay_type: order_list[i].pay_type,
                            total_goods_price: order_list[i].total_goods_price,
                            total_goods_original_price: order_list[i].total_goods_original_price,
                            total_pay_price: order_list[i].total_pay_price,
                            express_price: order_list[i].express_price,
                            send_type: order_list[i].send_type,
                            discount_price: discount_price,
                            goods_num: order_list[i].goods_num,
                            detail,
                            send_template_discount_price: order_list[i].send_template_discount_price
                        };
                        if (order_list[i].send_type == 1) {
                            data.store_address = order_list[i].store.address;
                        }
                        this.printData.push(data);
                    }
                }
                document.getElementById('print').style.display = 'block';
                this.nameWidth(this.printPar);
               setTimeout(() => {
                   for (let i = 0; i < this.printData.length; i++) {
                       JsBarcode('#code_' + this.printData[i].order_no, this.printData[i].order_no, {
                           format: "CODE39",//选择要使用的条形码类型
                           width:3.5,//设置条之间的宽度
                           height:200,//高度
                           displayValue:false,//是否在条形码下方显示文字
                           background:"#ffffff",//设置条形码的背景
                           lineColor:"#000000"//设置条和文本的颜色。
                       });
                   }
                   setTimeout(() => {
                       let newWindow = window.open("打印窗口", "_blank");//打印窗口要换成页面的url
                       let docStr = document.getElementById('print').outerHTML;
                       newWindow.document.write(docStr);
                       newWindow.document.close();
                       newWindow.print();
                       newWindow.close();
                       document.getElementById('print').style.display = 'none';
                   }, 1000);
               })

            },
            select_template(params, select_order, order) {
                this.printData = [];
                let {detail} = order;
                let new_detailExpress = JSON.parse(JSON.stringify(order.detailExpress));
                let new_select_order = [];
                for (let i = 0; i < detail.length; i++) {
                    if (select_order.indexOf(detail[i]) === -1) {
                        new_select_order.push(detail[i]);
                    }
                }
                let order_list = JSON.parse(JSON.stringify(detail));
                for (let i = 0; i < detail.length; i++) {
                    for (let j = 0; j < new_detailExpress.length; j++) {
                        for (let k = 0; k < new_detailExpress[j].expressRelation.length; k++) {
                            if (new_detailExpress[j].expressRelation[k].order_detail_id === detail[i].id) {
                                for (let m = 0; m < order_list.length; m++) {
                                    if (order_list[m].id === new_detailExpress[j].expressRelation[k].order_detail_id) {
                                        this.$set(new_detailExpress[j].expressRelation[k], 'num', order_list[m].num);
                                        this.$set(new_detailExpress[j].expressRelation[k], 'goods', order_list[m].goods);
                                        this.$set(new_detailExpress[j].expressRelation[k], 'total_price', order_list[m].total_price);
                                        order_list.splice(m, 1);
                                    }
                                }
                            }
                        }
                    }
                }
                for (let m = 0; m < new_select_order.length; m++) {
                    for (let i = 0; i < new_detailExpress.length; i++) {
                        for (let k = 0; k < new_detailExpress[i].expressRelation.length; k++) {
                            if (new_select_order[m].id === new_detailExpress[i].expressRelation[k].order_detail_id) {
                                new_detailExpress[i].expressRelation.splice(k, 1);
                            }
                        }
                    }
                }
                for (let m = 0; m < new_select_order.length; m++) {
                    for (let i = 0; i < order_list.length; i++) {
                        if (new_select_order[m].id === order_list[i].id) {
                            order_list.splice(i, 1);
                        }
                    }
                }
                let date = new Date();
                let Y = date.getFullYear() + '年';
                let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
                let D = date.getDate() + '日';
                this.printPar = params.params;
                this.printTime = Y + M + D;
                let discount_price = Number(order.member_discount_price) + Number(order.coupon_discount_price);
                for (let i = 0; i < new_detailExpress.length; i++) {
                    let detail = [];
                    if (new_detailExpress[i].expressRelation.length === 0) break;
                    for (let j = 0; j < new_detailExpress[i].expressRelation.length; j++) {
                        detail.push({
                            name: new_detailExpress[i].expressRelation[j].goods.goodsWarehouse.name,
                            num: new_detailExpress[i].expressRelation[j].num,
                            unit: new_detailExpress[i].expressRelation[j].goods.goodsWarehouse.unit,
                            price: new_detailExpress[i].expressRelation[j].total_price,
                            id: i + 1,
                            goods_no: new_detailExpress[i].expressRelation[j].orderDetail.goods_no,
                            attr: this.getGoodsAttr(new_detailExpress[i].expressRelation[j].orderDetail.goods_info.attr_list)
                        });
                    }
                    let data = {
                        order_no: order.order_no,
                        pay_time: order.pay_time,
                        name: order.name,
                        nickname: order.nickname,
                        mobile: order.mobile,
                        address: order.address,
                        remark: order.remark,
                        seller_remark: order.seller_remark,
                        words: order.words,
                        pay_type: order.pay_type,
                        total_goods_price: order.total_goods_price,
                        total_goods_original_price: order.total_goods_original_price,
                        total_pay_price: order.total_pay_price,
                        express_price: order.express_price,
                        send_type: order.send_type,
                        discount_price: discount_price,
                        goods_num: order.goods_num,
                        detail: detail,
                        send_template_discount_price: order.send_template_discount_price
                    };
                    if (order.send_type == 1) {
                        data.store_address = order.store.address;
                    }
                    this.printData.push(data);
                }
                let order_detail = [];
                for (let i = 0; i < order_list.length; i++) {
                    order_detail.push({
                        name: order_list[i].goods.goodsWarehouse.name,
                        num: order_list[i].num,
                        unit: order_list[i].goods.goodsWarehouse.unit,
                        price: order_list[i].total_price,
                        id: i + 1,
                        goods_no: order_list[i].goods_no,
                        attr: this.getGoodsAttr(order_list[i].goods_info.attr_list)
                    });
                }
                if (order_detail.length > 0) {
                    let data = {
                        order_no: order.order_no,
                        pay_time: order.pay_time,
                        name: order.name,
                        nickname: order.nickname,
                        mobile: order.mobile,
                        address: order.address,
                        remark: order.remark,
                        seller_remark: order.seller_remark,
                        words: order.words,
                        pay_type: order.pay_type,
                        total_goods_price: order.total_goods_price,
                        total_goods_original_price: order.total_goods_original_price,
                        total_pay_price: order.total_pay_price,
                        express_price: order.express_price,
                        send_type: order.send_type,
                        discount_price: discount_price,
                        goods_num: order.goods_num,
                        detail: order_detail,
                        send_template_discount_price: order.send_template_discount_price
                    };
                    if (order.send_type == 1) {
                        data.store_address = order.store.address;
                    }
                    this.printData.push(data);
                }
                document.getElementById('print').style.display = 'block';
                this.nameWidth(this.printPar);
                setTimeout(() => {
                    for (let i = 0; i < this.printData.length; i++) {
                        JsBarcode('#code_' + this.printData[i].order_no, this.printData[i].order_no, {
                            format: "CODE39",//选择要使用的条形码类型
                            width:3.5,//设置条之间的宽度
                            height:200,//高度
                            displayValue:false,//是否在条形码下方显示文字
                            background:"#ffffff",//设置条形码的背景
                            lineColor:"#000000"//设置条和文本的颜色
                        });
                    }
                    setTimeout(() => {
                        let newWindow = window.open("打印窗口", "_blank");//打印窗口要换成页面的url
                        let docStr = document.getElementById('print').outerHTML;
                        newWindow.document.write(docStr);
                        newWindow.document.close();
                        newWindow.print();
                        newWindow.close();
                        document.getElementById('print').style.display = 'none';
                    }, 1000);
                })
            },
            printTest(e) {
                let date = new Date();
                this.printData = [
                    {
                        order_no: '20200103113524825998',
                        pay_time: '2020-02-14 16:24:30',
                        name: '张三',
                        nickname: '屋顶上的小猫咪',
                        mobile: '0573-82261300',
                        address: '湖北省长沙市蔡锷北路三单元201号',
                        remark: '尽快发货，发中通快递',
                        total_goods_price: '110',
                        total_pay_price: '110',
                        express_price: '0',
                        discount_price: '0',
                        detail: [
                            {
                                name: '男士羊毛衫',
                                num: '1',
                                unit: '件',
                                price: '110',
                                id: 1,
                                goods_no: 'A1',
                                attr: '规格:默认'
                            }
                        ],
                        send_type: 0,
                        pay_type: 1
                    }
                ];
                let Y = date.getFullYear() + '年';
                let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
                let D = date.getDate() + '日';
                this.printPar = e.params;
                this.printTime = Y + M + D;
                document.getElementById('print').style.display = 'block';
                this.nameWidth(this.printPar);
                setTimeout(() => {
                    JsBarcode('#code_20200103113524825998', '20200103113524825998', {
                        format: "CODE39",//选择要使用的条形码类型
                        width:3.5,//设置条之间的宽度
                        height:200,//高度
                        displayValue:false,//是否在条形码下方显示文字
                        background:"#ffffff",//设置条形码的背景
                        lineColor:"#000000"//设置条和文本的颜色。
                    });
                    setTimeout(() => {
                        let newWindow = window.open("打印窗口", "_blank");//打印窗口要换成页面的url
                        let docStr = document.getElementById('print').outerHTML;
                        newWindow.document.write(docStr);
                        newWindow.document.close();
                        newWindow.print();
                        newWindow.close();
                        document.getElementById('print').style.display = 'none';
                    })
                }, 1000);
            },
            printPreview(e) {
                let img = new Image();
                let _this = this;
                img.src = e.cover_pic;
                this.temp_url.src = e.cover_pic;
                img.onload = function () {
                    // alert('width:'+img.width+',height:'+img.height);
                    _this.temp_url.width = img.width;
                    _this.temp_url.height = img.height;
                    _this.isPrintPreview = true;
                };

            },
            deleteTemp(e) {
                this.$confirm('此操作将永久删除该模板, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: '/mall/order-send-template/destroy'
                        },
                        method: 'post',
                        data: {
                            id: e.id
                        }
                    }).then(res => {
                        if (res.data.code === 0) {
                            for (let i = 0; i < this.template_list.length; i++) {
                                if (this.template_list[i].id === e.id) {
                                    this.$delete(this.template_list, i);
                                }
                            }
                            this.global = {
                                order: {
                                    orderNumber: true,
                                    time: true,
                                    date: true
                                },
                                personalInf: {
                                    name: true,
                                    nickname: true,
                                    phone: true,
                                    address: true,
                                    shipMethod: true,
                                    payMethod: true,
                                    mention_address: true,
                                    leaveComments: true
                                },
                                goodsInf: {
                                    serial: true,
                                    name: true,
                                    attr: true,
                                    number: true,
                                    unit: true,
                                    univalent: true,
                                    article_number: true,
                                    amount: true,
                                    totalAmount: true,
                                    fare: true,
                                    discount: true,
                                    actually_paid: true
                                },
                                sellerInf: {
                                    branch: true,
                                    name: true,
                                    phone: true,
                                    postcode: true,
                                    address: true,
                                    remark: true
                                },
                            };
                            this.template_name_1 = '默认模板';
                            this.$message({
                                type: 'success',
                                message: '删除成功!'
                            });
                        } else {
                            this.$message.error(res.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error(e.data.msg);
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            handleClick(e) {
                if (e.index === '0') {
                    this.edit = false;
                    this.showList = false;
                } else {
                    this.showList = true;
                }
            },
            cellClick(e) {
                this.changeOffsetId = e.id;
                this.global = e.params;
                this.template_name_1 = e.name;
            },
            changeOffset(e) {
                let template = {};
                for (let i = 0; i < this.template_list.length; i++) {
                    if (this.template_list[i].id === this.changeOffsetId) {
                        template = this.template_list[i];
                    }
                }
                request({
                    params: {
                        r: '/mall/order-send-template/edit'
                    },
                    method: 'post',
                    data: {
                        name: template.name,
                        params: JSON.stringify(template.params),
                        file: template.cover_pic,
                        id: template.id,
                        is_edit_cover_pic: 0
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message({
                            type: 'success',
                            message: '修改成功!'
                        });
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },
            cancelOut() {
                this.outletInformation = false;
                this.outletform = {};
            },
            saveOutlet(formName) {
                let {address, name, username, mobile, code, area} = this.outletform;
                if (!address) {
                    this.$message({
                        type: 'warning',
                        message: '详细地址不能为空'
                    });
                    return;
                }
                if (!name) {
                    this.$message({
                        type: 'warning',
                        message: '网点名称不能为空'
                    });
                    return;
                }
                if (!username) {
                    this.$message({
                        type: 'warning',
                        message: '联系人不能为空'
                    });
                    return;
                }
                if (!mobile) {
                    this.$message({
                        type: 'warning',
                        message: '联系方式不能为空'
                    });
                    return;
                }
                if (!code) {
                    this.$message({
                        type: 'warning',
                        message: '网点邮编不能为空'
                    });
                    return;
                }
                if (area.length < 3) {
                    this.$message({
                        type: 'warning',
                        message: '网点地址不能为空'
                    });
                    return;
                }
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    let data = {
                        address: this.outletform.address,
                        name: this.outletform.name,
                        username: this.outletform.username,
                        mobile: this.outletform.mobile,
                        code: this.outletform.code,
                        province: this.outletform.area[0],
                        city: this.outletform.area[1],
                        district: this.outletform.area[2]
                    };
                    if (valid) {
                        request({
                            params: {
                                r: '/mall/order-send-template/address-edit'
                            },
                            method: 'post',
                            data: data
                        }).then(e => {
                            if (e.data.code === 0) {
                                self.$message.success(e.data.msg);
                                let data = {
                                    address: this.outletform.address,
                                    name: this.outletform.name,
                                    username: this.outletform.username,
                                    mobile: this.outletform.mobile,
                                    code: this.outletform.code,
                                    province: this.outletform.area[0],
                                    city: this.outletform.area[1],
                                    district: this.outletform.area[2]
                                };
                                this.address_list = [data];
                                this.outletInformation = false;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                        });
                    } else {
                        return false;
                    }
                });
            },
            openOutlet() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        this.district = e.data.data.district;
                        this.outletInformation = true;
                        if (this.address_list.length > 0) {
                            this.outletform = JSON.parse(JSON.stringify(this.address_list[0]));
                            this.outletform.area = [JSON.parse(JSON.stringify(this.address_list[0].province)), JSON.parse(JSON.stringify(this.address_list[0].city)), JSON.parse(JSON.stringify(this.address_list[0].district))];
                        }
                    }
                }).catch(e => {
                });
            },
            mmConversionPx(value) {
                let inch = value * 2.834;
                return inch;
            },

            upadtePic(d) {
                for (let i = 0; i < d.length; i++) {
                    if (this.img_url.length >= 6) {
                        break;
                    }
                    this.img_url.push({
                        url: d[i].url,
                        marginLeft: this.setImage.left,
                        marginTop: this.setImage.top,
                        width: this.setImage.width,
                        id: d[i].id
                    });
                }
                let _this = this;
                this.customize_image = '';
                _this.uploadPic();
            },
            // TODO 临时解决图片跨域问题，有更好方案再优化
            uploadPic() {
                let _this = this;
                request({
                    params: {
                        r: 'mall/order-send-template/upload-image',
                    },
                    data: {
                        img_list: this.img_url
                    },
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        this.img_url = e.data.data.list;
                        for (let i = 0; i < this.img_url.length; i++) {
                            let srt = `style='width:${_this.img_url[i].width}px;margin-left:${_this.img_url[i].marginLeft}px;margin-top: ${_this.img_url[i].marginTop}px'`;
                            this.customize_image += `<image id='customize_image_${_this.img_url[i].id}' src='${_this.img_url[i].local_url}' ${srt}/>`;
                        }
                    }
                });
            },
            showTab(status) {
                this.show = status;
                this.mouseIndex = -1;
                let _this = this;
                if (status == 5) {
                    setTimeout(() => {
                        document.getElementById('textarea').addEventListener('input', function (e) {
                            _this.customize = e.target.innerHTML;
                        })
                    }, 10)
                }
            },
            draggaleChange() {
                this.customize_image = '';
                for (let i = 0; i < this.img_url.length; i++) {
                    let srt = `style='width:${this.img_url[i].width}px;margin-left:${this.img_url[i].marginLeft}px;margin-top: ${this.img_url[i].marginTop}px'`;
                    this.customize_image += `<image id='customize_image_${this.img_url[i].id}' src='${this.img_url[i].local_url}' ${srt}/>`;
                }
            },
            getAddressList() {
                request({
                    params: {
                        r: `/mall/order-send-template/address`
                    }
                }).then(e => {
                    let detail = e.data.data.detail;
                    this.address_list = [];
                    if (e.data.data.detail) {
                        this.address_list.push(detail);
                    }
                });
            },
            getTemplateList() {
                request({
                    params: {
                        r: `/mall/order-send-template/index`
                    }
                }).then(e => {
                    this.template_list = e.data.data.list;
                })
            },
            numberChange(e) {
                let integer = /^[+-]?(0|([1-9]\d*))(\.\d+)?$/g;
                if (integer.test(e)) {
                    if (parseFloat(e) > 47) {
                        this.setImage.width = 47;
                        return;
                    } else if (parseFloat(e) < 0) {
                        this.setImage.width = 0;
                    }

                }
            },
            async save() {

                let {
                    order,
                    personalInf,
                    goodsInf,
                    sellerInf,
                    template_name,
                    stencil_width,
                    stencil_high,
                    customize,
                    customize_image,
                    border_width,
                    left_right_margins,
                    headline,
                    img_url,
                    template_id
                } = this;
                if (!template_name) {
                    this.$message({
                        message: '模板名称不能为空',
                        type: 'warning'
                    });
                    return;
                }
                this.saveloading = true;
                this.show = -1;
                const canvas = await html2canvas(document.getElementById('canvas'), {
                    useCORS: true
                });
                let c = canvas.toDataURL();
                let params = {
                    order,
                    personalInf,
                    goodsInf,
                    sellerInf,
                    stencil_width: stencil_width,
                    stencil_high: stencil_high,
                    left_right_margins: left_right_margins,
                    border_width: border_width,
                    headline,
                    offset: {
                        left: 0,
                        right: 0
                    },
                    customize,
                    customize_image,
                    img_url
                };
                let data = {
                    name: template_name,
                    params: JSON.stringify(params),
                    file: c
                };
                if (template_id !== -1) {
                    data.id = template_id;
                }
                const e = await request({
                    params: {
                        r: 'mall/order-send-template/edit',
                    },
                    data: data,
                    method: 'post'
                });
                this.saveloading = false;
                if (e.data.code === 0) {
                    this.edit = false;
                    this.show = 0;
                    this.getTemplateList();
                    this.template_name = '模板';
                    this.$message.success('保存成功');
                    this.order = {
                        orderNumber: true,
                        time: true,
                        date: true
                    };
                    this.template_id = -1;
                    this.personalInf = {
                        name: true,
                        nickname: true,
                        phone: true,
                        address: true,
                        shipMethod: true,
                        payMethod: true,
                        mention_address: true,
                        leaveComments: true
                    };
                    this.goodsInf = {
                        serial: true,
                        name: true,
                        attr: true,
                        number: true,
                        unit: true,
                        univalent: true,
                        article_number: true,
                        amount: true,
                        totalAmount: true,
                        fare: true,
                        discount: true,
                        actually_paid: true
                    };
                    this.sellerInf = {
                        branch: true,
                        name: true,
                        phone: true,
                        postcode: true,
                        address: true,
                        remark: true
                    };
                    this.headline = {
                        name: '发货单',
                        fimaly: "微软雅黑",
                        font: 16,
                        align: 0,
                        line: 48,
                        space: 0,
                        bold: true,
                        italic: false,
                        underline: false
                    };
                    this.setImage = {
                        width: 47,
                        top: 0,
                        left: 0
                    };
                    this.customize = '';
                    this.customize_image = '';
                    this.is_customize = false;
                    this.img_url = [];
                }
            },
            getGoodsAttr(attrList) {
                let attr = '';
                attrList.forEach(item => {
                    attr += item.attr_group_name + ':' + item.attr_name + ';'
                });
                return attr;
            },

            setting(row,index) {
                this.printSetting = JSON.parse(JSON.stringify(row.params.printSetting));
                this.settingVisible = true;
                this.settingIndex = index;
            },
            saveSetting() {
                this.template_list[this.settingIndex].params.printSetting = this.printSetting;
                this.settingVisible = false;
                request({
                    params: {
                        r: '/mall/order-send-template/edit'
                    },
                    method: 'post',
                    data: {
                        name: this.template_list[this.settingIndex].name,
                        params: JSON.stringify(this.template_list[this.settingIndex].params),
                        file: this.template_list[this.settingIndex].cover_pic,
                        id: this.template_list[this.settingIndex].id,
                        is_edit_cover_pic: 0
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message({
                            type: 'success',
                            message: '修改成功!'
                        });
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },
            handleQuery() {},
            nameWidth(data) {
                let per = 48;
                let { serial, number, univalent, article_number, unit } = data.goodsInf;
                if (!serial) {
                    per += 6;
                }
                if (!number) {
                    per += 8;
                }
                if (!univalent) {
                    per += 12;
                }
                if (!article_number) {
                    per += 18;
                }
                if (!unit) {
                    per += 13;
                }
                this.tableWidth = `width: ${per}%`;
            }
        },
        created() {
            this.getAddressList();
            this.getTemplateList();
        },

        filters: {
            conversion(data) {
                let str = '';
                data ? str = '打印' : str = '不打印';
                return str;
            }
        },
        computed: {

        }
    })
</script>
