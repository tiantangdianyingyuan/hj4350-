<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 14:12
 */
Yii::$app->loadViewComponent('goods/app-batch');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 350px;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
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

    .content {
        padding: 0 5px;
        line-height: 20px;
        color: #E6A23C;
        background-color: #FCF6EB;
        width: auto;
        display: inline-block;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
    .zero-stock {
        color: #ff4544;
    }

    .dialog-extra-title {
        position: absolute;
        top: 24px;
        left: 110px;
        font-size: 15px;
    }
    .info>div {
        width: 300px;
        height: 100px;
        background-color: #ECF5FE;
        margin-right: 20px;
        color: #999;
        padding-left: 30px;
        margin-bottom: 20px;
    }

    .info .about {
        margin-top: 8px;
        font-size: 16px;
        color: #353535;
    }
    .pdf-area {
        font-size: 36px;
        color: #353535;
        text-align: center;
        position: fixed;
        top: 100%;
        left: 100%;
        width: 100%;
    }

    .pdf-area .pdf-list {
        padding: 200px 150px;
    }

    .pdf-area .pdf-title {
        font-size: 60px;
        font-weight: 600;
    }
    .pdf-area .pdf-time {
        margin-top: 20px;
    }

    .pdf-table {
        width: 100%;
    }

    .pdf-table {
        border-right:4px solid #353535;
        border-bottom:4px solid #353535
    } 
    .pdf-table td {
        border-left:4px solid #353535;
        border-top:4px solid #353535;
        height: 120px;
    }

    .sort-active {
        color: #3399ff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span style="color: #3399ff;cursor: pointer;" @click="routeGo('plugin/community/mall/activity/index')">社区团购</span>
            <span style="margin: 0 5px;">/</span>
            <span>活动详情</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <app-export-dialog :action_url="'index.php?r=plugin/community/mall/activity/detail&id=' + id" :field_list='export_list' :params="search">
                    </app-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <div class="pdf-area">
            <div v-for="(list,index) in goods_list" class="pdf-list" :style="{'height': height + 'px'}">
                <div class="pdf-title">{{activity.title}} -出货单(第{{index+1}}页，共{{goods_list.length}}页)</div>
                <div class="pdf-time">团购时间: {{activity.start_at}} - {{activity.end_at}}</div>
                <div style="text-align: left;">
                    <div>团长信息</div>
                    <div>姓名：{{middleman.name}}</div>
                    <div>手机号：{{middleman.mobile}}</div>
                    <div>省市区：{{middleman.province}}<span v-if="middleman.province != middleman.city">{{middleman.city}}</span>{{middleman.district}}</div>
                    <div>所在地址：{{middleman.province}}<span v-if="middleman.province != middleman.city">{{middleman.city}}</span>{{middleman.district}}{{middleman.location}}</div>
                    <div>提货地址：{{middleman.detail}}</div>
                    <div style="margin-top: 60px;margin-bottom: 5px">商品清单</div>
                </div>
                <table class="pdf-table" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td style="width: 10%;">序号</td>
                    <td style="width: 80%;">商品</td>
                    <td style="width: 10%;">数量</td>
                  </tr>
                  <tr v-for="(item in list" :key="item.index">
                    <td style="width: 10%;">{{item.index}}</td>
                    <td style="width: 80%;text-align: left;padding: 0 5px;">
                        <span v-if="item.no">{{item.no}} </span>
                    {{item.name}}——{{item.attr}}
                    </td>
                    <td style="width: 10%;">{{item.goods_num}}</td>
                  </tr>
                </table>
                <div v-if="index == goods_list.length - 1" style="text-align: right;margin: 10px 0">打印时间: {{time}}</div>
                <div v-if="index == goods_list.length - 1" style="text-align: right">出货确认_______________________</div>
            </div>
        </div>
        <div class="table-body">
            <div flex="dir:left cross:center" class="info">
                <div flex="dir:top main:center">
                    <div>活动名称</div>
                    <div class="about">{{activity.title}}</div>
                </div>
                <div flex="dir:top main:center">
                    <div>最低成团</div>
                    <div v-if="activity.condition == 1" class="about">参团人数至少满{{activity.num}}人</div>
                    <div v-else-if="activity.condition == 2" class="about">商品件数至少满{{activity.num}}件</div>
                    <div v-else class="about">无</div>
                </div>
            </div>
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div class="input-item">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable @clear="toSearch">
                        <el-select size="small" v-model="search.type" slot="prepend" class="select">
                            <el-option key="4" label="团长ID" value="1"></el-option>
                            <el-option key="1" label="团长昵称" value="2"></el-option>
                            <el-option key="2" label="姓名" value="3"></el-option>
                            <el-option key="3" label="手机号" value="4"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <el-table @sort-change="changeSort" :data="list" border v-loading="loading" style="margin-bottom: 15px;">
                <el-table-column label="团长ID" prop="middleman" width="80">
                    <template slot-scope="scope">
                        <span>{{scope.row.user_id}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="团长昵称" prop="title" width="250">
                    <template slot-scope="scope">
                        <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.middleman.avatar"></app-image>
                        <div style="margin-top: 25px;">{{scope.row.middleman.nickname}}</div>
                    </template>
                </el-table-column>
                <el-table-column width="120" label="姓名" prop="name">
                    <el-table-column label="手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.address.name}}</div>
                            <div>{{scope.row.address.mobile}}</div>
                        </template>
                    </el-table-column>
                </el-table-column>
                <el-table-column label="所在小区" prop="address">
                    <template slot-scope="scope">
                        <span>{{scope.row.address.location}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="提货地址" prop="address">
                    <template slot-scope="scope">
                        <span>{{scope.row.address.detail}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="订单实付金额(元)" width="200" prop="order_price" :label-class-name="prop == 'order_price' ? 'sort-active': ''" sortable='custom'>
                    <template slot-scope="scope">
                        <span>{{scope.row.order_price ? scope.row.order_price : '0'}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="支付订单数" prop="order_num" :label-class-name="prop == 'order_num' ? 'sort-active': ''" sortable='custom'>
                    <template slot-scope="scope">
                        <span>{{scope.row.order_num ? scope.row.order_num : '0'}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="参团人数" prop="user_num" :label-class-name="prop == 'user_num' ? 'sort-active': ''" sortable='custom'>
                    <template slot-scope="scope">
                        <span>{{scope.row.user_num ? scope.row.user_num : '0'}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="活动状态" prop="activity_status" width="140">
                    <template slot-scope="scope">
                        <el-tag size="small" type="warning" v-if="activity.activity_status == 3">下架中</el-tag>
                        <el-tag size="small" type="success" v-else-if="activity.activity_status == 1">进行中</el-tag>
                        <el-tag size="small" type="info" v-else-if="scope.row.is_success == 1">已结束</el-tag>
                        <el-tag size="small" type="danger" v-else>{{scope.row.is_refund == 1 ? '活动失败' :  '活动失败待退款'}}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" v-if="activity.activity_status == 2" width="250px">
                    <template slot-scope="scope">
                        <el-button v-if="scope.row.is_success == 1 && scope.row.order_num > 0" @click="loadGoods(scope.row.
                        user_id)" type="text" circle
                                   size="mini">
                            <el-tooltip class="item" effect="dark" content="下载出货单" placement="top">
                                <img src="statics/img/plugins/download.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="scope.row.is_success == 0 && scope.row.user_num > 0" @click="toRefundLog(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="退款日志" placement="top">
                                <img src="statics/img/plugins/refund.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
                <el-table-column v-if="activity.activity_status == 1 && activity.condition > 0" width="250px">
                    <template slot="header" slot-scope="scope">
                        <span>锁定成功</span>
                        <el-tooltip effect="dark" placement="top">
                            <div slot="content">锁定成功开启，表明活动结束时，无论活动<br/>是否满足最低成团标准，都视为活动成功</div>
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <template slot-scope="scope">
                        <el-switch @change="switchStatus(scope.row,scope.$index)" v-model="scope.row.is_locking" :active-value="1"
                                                 :inactive-value="0"></el-switch>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next" 
                            :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
            <el-dialog title="退款日志" :visible.sync="refundLogDialog">
                <el-button @click="toRefund" :loading="refundLoading" type="primary" style="margin-bottom: 20px;" size="small">一键手动退款</el-button>
                <el-table :data="refundLog" v-loading="refundLogLoading" border :header-cell-style="{backgroundColor: '#f5f7fa'}">
                    <el-table-column prop="order_no" label="订单号">
                    </el-table-column>
                    <el-table-column label="用户" prop="nickname" width="250">
                        <template slot-scope="scope">
                            <div flex="dir:left cross:center" style="margin: 10px 0">
                                <app-image width="25" height="25" style="margin-right: 5px;" mode="aspectFill" :src="scope.row.avatar"></app-image>
                                <div>{{scope.row.nickname}}</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="订单金额" prop="total_pay_price">
                        <template slot-scope="prop">
                            <div>￥{{prop.row.total_pay_price}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="退款状态" prop="status">
                        <template slot-scope="prop">
                            <div v-if="prop.row.status == '成功'" style="color: #1ed200">{{prop.row.status}}</div>
                            <div v-else style="color: #ff4544">{{prop.row.status}}</div>
                        </template>
                    </el-table-column>
                </el-table>
                <div flex="box:last cross:center" style="margin-top: 20px;">
                    <div></div>
                    <div>
                        <el-pagination
                                v-if="list.length > 0"
                                style="display: inline-block;float: right;"
                                background :page-size="refundPagination.pageSize"
                                @current-change="refundPageChange"
                                layout="prev, pager, next" 
                                :current-page="refundPagination.current_page"
                                :total="refundPagination.totalCount">
                        </el-pagination>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button type="primary" size="small" @click="refundLogDialog = false">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </el-card>
</div>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/jspdf.debug.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/jspdf.min.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/html2canvas.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                refundLogLoading: false,
                refundLoading: false,
                id: null,
                middleman_id: null,
                height: 842,
                middleman: {},
                activity: {},
                refundLog: [],
                list: [],
                export_list: [],
                goods_list: [],
                refundLogDialog: false,
                search: {
                    keyword: '',
                    type: '1'
                },
                order: '',
                time: '',
                prop: '',
                refundPagination: {
                    pageSize: 1
                },
                pagination: {
                    pageSize: 1
                }
            }
        },
        created() {
            this.id = getQuery('id');
            this.loadData();
            var now = new Date();
            var year = now.getFullYear(); //得到年份
            var month = now.getMonth();//得到月份
            var date = now.getDate();//得到日期
            var day = now.getDay();//得到周几
            var hour = now.getHours();//得到小时
            var minu = now.getMinutes();//得到分钟
            var sec = now.getSeconds();//得到秒
            var MS = now.getMilliseconds();//获取毫秒
            month = month + 1;
            if (month < 10) month = "0" + month;
            if (date < 10) date = "0" + date;
            if (hour < 10) hour = "0" + hour;
            if (minu < 10) minu = "0" + minu;
            if (sec < 10) sec = "0" + sec;
            if (MS < 100) MS = "0" + MS;
            this.time = year + "-" + month + "-" + date + " " + hour + ":" + minu + ":" + sec;
            this.height = document.body.clientWidth / 592.28 * 841.89;
        },
        methods: {
            toRefund() {
                this.refundLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/refund',
                        activity_id: this.id,
                        middleman_id: this.middleman_id,
                    },
                    method: 'get',
                }).then(e => {
                    this.refundLoading = false;
                    console.log(e.data.code)
                    if (e.data.code == 0) {
                        this.$message({
                            type: 'success',
                            message: e.data.msg
                        });
                        this.refundPageChange(1);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.refundLoading = false;
                });
            },
            toRefundLog(row) {
                this.refundLogDialog = !this.refundLogDialog;
                this.middleman_id = row.user_id;
                this.refundLogLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/refund-list',
                        activity_id: this.id,
                        middleman_id: row.user_id,
                        page: 1,
                    },
                    method: 'get',
                }).then(e => {
                    this.refundLogLoading = false;
                    if (e.data.code == 0) {
                        this.refundLog = e.data.data.list;
                        this.refundPagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.refundLogLoading = false;
                });
            },
            refundPageChange(page) {
                this.refundLogLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/refund-list',
                        activity_id: this.id,
                        middleman_id: this.middleman_id,
                        page: page,
                    },
                    method: 'get',
                }).then(e => {
                    this.refundLogLoading = false;
                    if (e.data.code == 0) {
                        this.refundLog = e.data.data.list;
                        this.refundPagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.refundLogLoading = false;
                });
            },
            routeGo(r) {
                this.$navigate({
                    r: r
                });
            },
            switchStatus(row,index) {
                request({
                    params: {
                        r: 'plugin/community/mall/activity/locking',
                        activity_id: this.id,
                        middleman_id: row.user_id,
                        is_locking: this.list[index].is_locking
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.$message({
                            type: 'success',
                            message: e.data.msg
                        });
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            changeSort(column) {
                this.loading = true;
                if(column.order == "descending") {
                    this.order = column.prop + ' DESC'
                }else if (column.order == "ascending") {
                    this.order = column.prop
                }else {
                    this.order = null
                }
                this.prop = column.prop;
                this.loadData();
            },
            loadGoods(middleman_id) {
                console.log(middleman_id);
                let that = this;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/order-goods',
                        id: that.id,
                        middleman_id: middleman_id
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        let list = e.data.data.list;
                        if(list.length > 0) {
                            for(let i in list) {
                                list[i].index = +i + 1;
                            }
                            let index = 0;
                            that.goods_list = [];
                            while(index < list.length) {
                                that.goods_list.push(list.slice(index, index += 12));
                            }
                            console.log(that.goods_list);
                            that.middleman = e.data.data.middleman;
                            setTimeout(function(){
                                that.createdPdf();
                            },500)
                        }else {
                            that.$message.error('该活动下暂无成功的订单');
                        }
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
            createdPdf(){
                var now = new Date();
                var year = now.getFullYear(); //得到年份
                var month = now.getMonth();//得到月份
                var date = now.getDate();//得到日期
                var day = now.getDay();//得到周几
                var hour = now.getHours();//得到小时
                var minu = now.getMinutes();//得到分钟
                var sec = now.getSeconds();//得到秒
                var MS = now.getMilliseconds();//获取毫秒
                month = month + 1;
                if (month < 10) month = "0" + month;
                if (date < 10) date = "0" + date;
                if (hour < 10) hour = "0" + hour;
                if (minu < 10) minu = "0" + minu;
                if (sec < 10) sec = "0" + sec;
                if (MS < 100) MS = "0" + MS;
                this.time = year + "-" + month + "-" + date + " " + hour + ":" + minu + ":" + sec
                var target = document.getElementsByClassName("pdf-area")[0];
                target.style.background = "#FFFFFF";
                html2canvas(target).then((canvas) => {
                    console.log(canvas)
                    var contentWidth = canvas.width;
                    var contentHeight = canvas.height;
                    //一页pdf显示html页面生成的canvas高度;
                    var pageHeight = contentWidth / 592.28 * 841.89;
                    //未生成pdf的html页面高度
                    var leftHeight = contentHeight;
                    //页面偏移
                    var position = 0;
                    //a4纸的尺寸[595.28,841.89]，html页面生成的canvas在pdf中图片的宽高
                    var imgWidth = 595.28;
                    var imgHeight = 592.28/contentWidth * contentHeight;

                    var pageData = canvas.toDataURL('image/jpeg', 1.0);

                    var pdf = new jsPDF('', 'pt', 'a4');

                    //有两个高度需要区分，一个是html页面的实际高度，和生成pdf的页面高度(841.89)
                    //当内容未超过pdf一页显示的范围，无需分页
                    console.log(leftHeight,pageHeight)
                    if (leftHeight < pageHeight) {
                        pdf.addImage(pageData, 'JPEG', 0, 0, imgWidth, imgHeight );
                    } else {
                        while(leftHeight > 0) {
                            pdf.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight)
                            leftHeight -= pageHeight;
                            position -= 841.89;
                            //避免添加空白页
                            if(leftHeight > 1) {
                              pdf.addPage();
                            }
                        }
                    }
                    let name = this.middleman.name + '—出货单.pdf'
                    pdf.save(name);
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/detail',
                        id: this.id,
                        keyword: this.search.keyword,
                        keyword_label: this.search.type,
                        order_by: this.order,
                        page: this.search.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    console.log(this.loading)
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.activity = e.data.data.activity;
                        this.pagination = e.data.data.pagination;
                        this.export_list = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            }
        }
    });
</script>