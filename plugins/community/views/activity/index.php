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
        margin-left: 25px;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>社区团购</span>
            <el-form size="small" :inline="true" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <el-button type="primary" @click="edit" size="small">新建活动</el-button>
                </el-form-item>
            </el-form>
        </div>
        <div class="pdf-area">
            <div v-for="(list,index) in goods_list" class="pdf-list" :style="{'height': height + 'px'}">
                <div class="pdf-title">{{activity.title}} -出货单(第{{index+1}}页，共{{goods_list.length}}页)</div>
                <div class="pdf-time">团购时间: {{activity.start_at}} - {{activity.end_at}}</div>
                <div style="text-align: left;">
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
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未开始" name="0"></el-tab-pane>
                <el-tab-pane label="进行中" name="1"></el-tab-pane>
                <el-tab-pane label="已结束" name="2"></el-tab-pane>
                <el-tab-pane label="下架中" name="3"></el-tab-pane>
                <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                    <div flex="dir:left cross:center">
                        <div style="margin-right: 10px;">活动时间</div>
                        <el-date-picker
                                size="small"
                                @change="changeTime"
                                v-model="search.time"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </div>
                    <div class="input-item">
                        <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入活动名称搜索" v-model="search.keyword" clearable @clear="toSearch">
                            <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                        </el-input>
                    </div>
                </div>
                <app-batch :choose-list="choose_list"
                   @to-search="toSearch"
                   :batch-update-status-url="batch_update_status_url"
                   :is-show-batch-button="isShowBatchButton">
                </app-batch>
                <el-table :data="list" border v-loading="loading" @selection-change="handleSelectionChange" style="margin-bottom: 15px;">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column label="活动名称" prop="title" width="250"></el-table-column>
                    <el-table-column label="商品件数" width="100" prop="kind">
                        <template slot-scope="scope">
                            <el-button style="font-size: 14px" type="text" size="mini" circle @click.native="lookGoods(scope.row)">
                                {{scope.row.kind}}
                            </el-button>
                        </template>
                    </el-table-column>
                    <el-table-column label="订单实付金额" width="150" prop="order_price">
                        <template slot-scope="scope">
                            <span>{{scope.row.order_price ? scope.row.order_price : '0'}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="支付订单数" prop="order_num">
                        <template slot-scope="scope">
                            <span>{{scope.row.order_num ? scope.row.order_num : '0'}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="支付商品数" propp="goods_num">
                        <template slot-scope="scope">
                            <span>{{scope.row.goods_num ? scope.row.goods_num : '0'}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="参团人数" prop="user_num">
                        <template slot-scope="scope">
                            <span>{{scope.row.user_num ? scope.row.user_num : '0'}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="活动时间" width="210">
                        <template slot-scope="scope">
                            <div style="padding-right: 30px;">
                                <span>{{scope.row.start_at}}</span>至<span>{{scope.row.end_at}}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="活动状态" width="100" prop="activity_status">
                        <template slot-scope="scope">
                            <el-tag size="small" type="info" v-if="scope.row.activity_status == 0">未开始</el-tag>
                            <el-tag size="small" type="success" v-if="scope.row.activity_status == 1">进行中</el-tag>
                            <el-tag size="small" type="info" v-if="scope.row.activity_status == 2">已结束</el-tag>
                            <el-tag size="small" type="warning" v-if="scope.row.activity_status == 3">下架中</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="250px" fixed="right">
                        <template slot-scope="scope">
                            <el-button v-if="scope.row.activity_status != 2 " @click="edit(scope.row, '0')" type="text" circle
                                       size="mini">
                                <el-tooltip class="item" effect="dark" content="编辑活动" placement="top">
                                    <img src="statics/img/plugins/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.activity_status != 2" @click="edit(scope.row, '1')" type="text" circle
                                       size="mini">
                                <el-tooltip class="item" effect="dark" content="编辑商品" placement="top">
                                    <img src="statics/img/plugins/edit-goods.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="toDetail(scope.row)" v-if="scope.row.activity_status != 0" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="活动详情" placement="top">
                                    <img src="statics/img/mall/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle @click.native="delItem(scope.row.id,scope.$index)">
                                <el-tooltip effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.has_success == 1" @click="loadGoods(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="下载出货单" placement="top">
                                    <img src="statics/img/plugins/download.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tabs>
            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog title="活动商品" :visible.sync="visible">
        <div class="dialog-extra-title">共计{{detail.kind}}款商品</div>
        <el-input v-model="goods.keyword" placeholder="根据名称搜索" @keyup.enter.native="getCommunityGoods">
            <el-button slot="append" @click="getCommunityGoods">搜索</el-button>
        </el-input>
        <el-table border v-loading="listLoading" :data="communityGoods" style="margin-top: 24px;" height="500">
            <el-table-column width="80px" label="ID" prop="goods_warehouse_id"></el-table-column>
            <el-table-column label="名称">
                <template slot-scope="props">
                    <div flex="dir:left cross:center">
                        <img style="height: 50px;width: 50px;margin-right: 10px;" :src="props.row.cover_pic" alt="">
                        <app-ellipsis :line="2">{{props.row.name}}</app-ellipsis>
                    </div>
                </template>
            </el-table-column>
            <el-table-column width="200" label="售价" prop="price">
                <template slot-scope="props">
                    ￥{{props.row.price}}
                </template>
            </el-table-column>
            <el-table-column width="200" label="剩余库存" prop="goods_stock">
                <template slot-scope="props">
                    <div>
                        <span :class="props.row.goods_stock < 50 ? 'zero-stock':''">{{props.row.goods_stock}}</span>
                    </div>
                </template>
            </el-table-column>
        </el-table>
        <div style="margin-top: 24px;">
            <el-row>
                <el-pagination
                        v-if="goodsPagination"
                        style="display: inline-block;"
                        background
                        :page-size="goodsPagination.pageSize"
                        @current-change="pageGoodsChange"
                        layout="prev, pager, next"
                        :total="goodsPagination.total_count">
                </el-pagination>
                <el-button type="primary" size="small" style="float: right" @click="visible=false">确定</el-button>
            </el-row>
        </div>
    </el-dialog>
</div>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/jspdf.debug.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/jspdf.min.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/html2canvas.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                search: {
                    date_start: '',
                    date_end: '',
                    keyword: '',
                    type: '4',
                    page: 1,
                    time: []
                },
                height: 842,
                goods: {
                    keyword: '',
                    page: 1
                },
                communityGoods: [],
                goods_list: [],
                time: '',
                activity: {},
                detail: {},
                pagination: {
                    pageSize: null
                },
                goodsPagination: {
                    pageSize: null
                },
                isShowBatchButton: false,
                batch_update_status_url: 'plugin/community/mall/activity/edit-status',
                loading: false,
                listLoading: false,
                visible: false,
                choose_list: [],
                activeName: '-1',
            }
        },
        mounted() {
            localStorage.removeItem("community");
            this.loadData();
            this.height = document.body.clientWidth / 592.28 * 841.89;
        },
        methods: {
            loadGoods(row) {
                let that = this;
                that.activity = row;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/order-goods',
                        id: row.id,
                        middleman_id: 0
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
                            setTimeout(function(){
                                that.createdPdf(row.title);
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
            createdPdf(title){
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
                    let name = title + '—出货单.pdf'
                    pdf.save(name);
                });
            },
            delItem(id,index) {
                let self = this;
                self.$confirm('是否确认删除选中的活动?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: self.batch_update_status_url,
                        },
                        data:{
                            ids: [id],
                            type: 'del',
                        },
                        method: 'post'
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.loading = false;
                        console.log(e);
                    });
                }).catch(() => {
                    self.loading = false;
                    self.$message.info('已取消删除')
                });
            },
            handleSelectionChange(val) {
                this.choose_list = val;
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            pageGoodsChange(page) {
                this.goods.page = page;
                this.getCommunityGoods();
            },
            getCommunityGoods() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/goods',
                        limit: 10,
                        id: this.detail.id,
                        keyword: this.goods.keyword,
                        page: this.goods.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        this.communityGoods = e.data.data.list;
                        this.goodsPagination = e.data.data.pagination;
                        this.goodsPagination.pageSize = +e.data.data.pagination.pageSize;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            lookGoods(row) {
                this.visible = true;
                this.detail = row;
                this.communityGoods = [];
                this.getCommunityGoods();
            },
            toDetail(row) {
                this.$navigate({
                    r: 'plugin/community/mall/activity/detail',
                    id: row.id
                });
            },
            edit(row, tab) {
                let r = 'plugin/community/mall/activity/edit';
                if (row) {
                    this.$navigate({
                        r: r,
                        id: row.id,
                        tab: tab
                    });
                } else {
                    this.$navigate({
                        r: r
                    });
                }
            },
            changeTime() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.loadData();
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/activity/index',
                        status: this.activeName,
                        start_at: this.search.date_start,
                        end_at: this.search.date_end,
                        keyword: this.search.keyword,
                        keyword_label: 'title',
                        page: this.search.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            handleClick() {
                this.search.page = 1;
                this.loadData();
            },
        }
    });
</script>