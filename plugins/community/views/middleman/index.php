<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 14:12
 */
?>
<style>
    .el-tabs__header {
        font-size: 16px;
    }


    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
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
    .content {
        padding: 0 5px;
        line-height: 20px;
        color: #E6A23C;
        background-color: #FCF6EB;
        width: auto;
        display: inline-block;
    }

    .t-omit-two {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        white-space: normal !important;
        width: 100%;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .el-message-box__message {
        text-align: center;
        font-size: 16px;
        margin: 10px 0 20px;
    }

    .el-tooltip__popper {
        max-width: 320px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>团长管理</span>
        </div>
        <div class="table-body">
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div flex="dir:left cross:center">
                    <div style="margin-right: 10px;">申请时间</div>
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
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable @clear="toSearch">
                        <el-select size="small" v-model="search.type" slot="prepend" class="select">
                            <el-option key="id" label="团长ID" value="id"></el-option>
                            <el-option key="nickname" label="昵称" value="nickname"></el-option>
                            <el-option key="name" label="姓名" value="name"></el-option>
                            <el-option key="mobile" label="手机号" value="mobile"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <el-tabs v-model="activeName" @tab-click="toSearch" :before-leave="beforeLeave">
                <el-tab-pane label="全部" name="-2"></el-tab-pane>
                <el-tab-pane label="未支付" name="-1"></el-tab-pane>
                <el-tab-pane label="未审核" name="0"></el-tab-pane>
                <el-tab-pane label="已通过" name="1"></el-tab-pane>
                <el-tab-pane label="已拒绝" name="2"></el-tab-pane>
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                    <el-table-column label="ID" prop="user_id" width="80"></el-table-column>
                    <el-table-column label="基本信息" width="200">
                        <template slot-scope="scope">
                            <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.avatar"></app-image>
                            <div style="margin-top: 25px;">{{scope.row.nickname}}</div>
                            <el-tooltip class="item" v-if="scope.row.showRemark" effect="dark" :content="scope.row.content" placement="top">
                                <div class="content t-omit-two" style="height:50px;line-height: 25px;">{{scope.row.content}}</div>
                            </el-tooltip>
                            <div v-else class="content" >{{scope.row.content}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="姓名" prop="name">
                        <el-table-column label="手机号" prop="phone">
                            <template slot-scope="scope">
                                <div>{{scope.row.name}}</div>
                                <div>{{scope.row.mobile}}</div>
                            </template>
                        </el-table-column>
                    </el-table-column>
                    <el-table-column label="所在小区" width="100" prop="location">
                    </el-table-column>
                    <el-table-column label="省市区" width="150" prop="province">
                            <template slot-scope="scope">
                                <div>{{scope.row.province}}<span v-if="scope.row.province != scope.row.city">{{scope.row.city}}</span>{{scope.row.district}}</div>
                            </template>
                    </el-table-column>
                    <el-table-column label="提货地址" prop="detail">
                    </el-table-column>
                    <el-table-column label="销售金额(元)" prop="total_price">
                        <template slot-scope="scope">
                            <div :style="{color: `${scope.row.status > 0 ? '#606266':'#C9C9C9'}`}">
                                {{scope.row.status > 0 ? scope.row.total_price : '——'}}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="已结算(元)" prop="total_money">
                        <template slot-scope="scope">
                            <div :style="{color: `${scope.row.status > 0 ? '#606266':'#C9C9C9'}`}">
                                {{scope.row.status > 0 ? scope.row.total_money : '——'}}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="订单数" prop="order_count">
                        <template slot-scope="scope">
                            <div :style="{color: `${scope.row.status > 0 ? '#606266':'#C9C9C9'}`}">
                                {{scope.row.status > 0 ? scope.row.order_count : '——'}}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status >= 0 && scope.row.applyed_at != '0000-00-00 00:00:00'">申请时间：{{scope.row.apply_at}}</div>
                            <div v-if="scope.row.status == -1">申请时间：{{scope.row.apply_at}}</div>
                            <div v-if="scope.row.status == 1 || scope.row.status == 2">审核时间：{{scope.row.become_at}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" width="80" prop="status">
                        <template slot-scope="scope">
                            <el-tag size="small" type="info" v-if="scope.row.status == 0">未审核</el-tag>
                            <el-tag size="small" type="info" v-if="scope.row.status == -1">未支付</el-tag>
                            <el-tag size="small" v-if="scope.row.status == 1">已通过</el-tag>
                            <el-tag size="small" type="danger" v-if="scope.row.status == 2">拒绝</el-tag>
                            <el-tag size="small" type="warning" v-if="scope.row.status == 3">处理中</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="支付费用(元)" prop="pay_price">
                    </el-table-column>
                    <el-table-column label="操作" width="220px" fixed="right">
                        <template slot-scope="scope">
                            <el-button v-if="scope.row.status == 0" type="text" size="mini" circle style="margin-top: 10px" @click.native="agree(scope.row)">
                                <el-tooltip class="item" effect="dark" content="通过申请" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 0" type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="apply(scope.row)">
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 1" type="text" size="mini" circle style="margin-top: 10px" @click.native="toEdit(scope.row)">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 1" type="text" size="mini" circle style="margin-top: 10px" @click.native="toRelease(scope.row)">
                                <el-tooltip class="item" effect="dark" content="解除团长" placement="top">
                                    <img src="statics/img/plugins/release.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="openContent(scope.row)">
                                <el-tooltip class="item" effect="dark" content="备注" placement="top">
                                    <img src="statics/img/mall/order/add_remark.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 1" type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="openDetail(scope.row)">
                                <el-tooltip class="item" effect="dark" content="团长详情" placement="top">
                                    <img src="statics/img/mall/order/detail.png" alt="">
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
    <el-dialog :title="title" :visible.sync="dialogContent" width="30%">
        <el-form>
            <el-form-item>
                <el-input type="textarea" :rows="5" v-model="content" :placeholder="placeholder" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogContent = false">取 消</el-button>
            <el-button size="small" type="primary" v-if="title == '拒绝理由'" @click="beApply(2)" :loading="contentBtnLoading">确 定</el-button>
            <el-button size="small" type="primary" v-else-if="title == '解除理由'" @click="beApply(3)" :loading="contentBtnLoading">确 定</el-button>
            <el-button size="small" type="primary" v-else @click="beRemark" :loading="contentBtnLoading">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="编辑团长信息" :visible.sync="editDialog" width="670px">
        <el-dialog title="选择地区" :visible.sync="dialogVisible" width="50%" append-to-body>
            <div style="margin-bottom: 1rem;">
                <app-district :edit="area" :radio="true" @selected="selectDistrict" :level="3"></app-district>
                <div style="text-align: right;margin-top: 1rem;">
                    <el-button type="primary" @click="districtConfirm">
                        确定选择
                    </el-button>
                </div>
            </div>
        </el-dialog>
        <el-form :model="user" size="small" label-width="90px">
            <el-form-item label="ID">
                {{user.user_id}}
            </el-form-item>
            <el-form-item label="昵称">
                {{user.nickname}}
            </el-form-item>
            <el-form-item label="手机号">
                <el-input v-model="user.mobile" type="tel" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></el-input>
            </el-form-item>
            <el-form-item label="所在小区">
                <div flex="dir:left cross:center">
                    <div style="margin-right: 10px">{{user.location}}</div>
                    <app-map @map-submit="mapEvent"
                             :address="user.location"
                             :lat="user.latitude"
                             :long="user.longitude">
                        <el-button size="mini">定位</el-button>
                    </app-map>
                </div>
            </el-form-item>
            <el-form-item label="省市区">
                <el-tag type="info" style="margin:5px;margin-top:0;border:0" >
                    {{user.province}}
                </el-tag>
                <el-tag type="info" style="margin:5px;margin-top:0;border:0" >
                    {{user.city}}
                </el-tag>
                <el-tag type="info" style="margin:5px;margin-top:0;border:0" >
                    {{user.district}}
                </el-tag>
                <el-button @click="districtChoose" size="small">选择</el-button>
            </el-form-item>
            <el-form-item label="提货地址">
                <el-input v-model="user.detail"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="editDialog = false">取 消</el-button>
            <el-button size="small" type="primary" @click="beEdit" :loading="contentBtnLoading">确定修改</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                dialogContent: false,
                contentBtnLoading: false,
                dialogVisible: false,
                editDialog: false,
                title:'',
                placeholder: '',
                content: "",
                list: [],
                detail: {},
                user: {},
                area: [],
                search: {
                    date_start: '',
                    date_end: '',
                    keyword: '',
                    type: 'id',
                    page: 1,
                    time: []
                },
                temp: [],
                exportList: [],
                pagination: {
                    pageSize: null
                },
                activeName: '-2',
            }
        },
        created() {
            this.loadData();
        },
        methods: {
            districtConfirm(e) {
                if(this.temp.length == 0) {
                    this.$message({
                        type: 'warning',
                        message: '请选择地区'
                    });
                    return false;
                }
                this.user.province_id = this.temp[0].id;
                this.user.province = this.temp[0].name;
                this.user.city_id = this.temp[1].id;
                this.user.city = this.temp[1].name;
                this.user.district_id = this.temp[2].id;
                this.user.district = this.temp[2].name;
                this.dialogVisible = false;
            },
            districtChoose() {
                let para = {
                    id: this.user.district_id,
                    name: this.user.district
                }
                this.area = [para];
                this.temp = [];
                this.dialogVisible = true;
            },
            selectDistrict(e) {
                this.temp = e;
            },
            mapEvent(e) {
                this.user.location = e.address;
                this.user.latitude = e.lat;
                this.user.longitude = e.long;
            },
            beEdit() {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/middleman/edit',
                    },
                    data: this.user,
                    method: 'post',
                }).then(e => {
                    this.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        this.editDialog = false;
                        this.loadData();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.contentBtnLoading = false;
                });
            },
            toEdit(row) {
                this.editDialog = true;
                this.user = JSON.parse(JSON.stringify(row));
            },
            strlen(str){
                var len = 0;
                for (var i=0; i<str.length; i++) {
                 var c = str.charCodeAt(i);
                //单字节加1
                 if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
                   len++;
                 }
                 else {
                  len+=2;
                 }
                }
                return len;
            },
            beforeLeave(a,b) {
                if(this.loading) {
                    return false
                }
            },
            toRelease(e) {
                this.dialogContent = true;
                this.title = '解除理由';
                this.placeholder = '请填写解除理由';
                this.content = '';
                this.detail = e;
            },
            apply(e) {
                this.dialogContent = true;
                this.title = '拒绝理由';
                this.placeholder = '请填写拒绝理由';
                this.content = '';
                this.detail = e;
            },
            // 通过审核
            agree(e) {
                this.$confirm('是否确认通过审核', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消'
                }).then(res => {
                    this.detail = e;
                    this.content = '后台管理员审核通过';
                    this.beApply(1);
                }).catch(res => {
                    this.$message({
                        type: 'info',
                        message: '取消了操作'
                    });
                });
            },
            // 发送审核消息
            beApply(status) {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/middleman/check',
                    },
                    data: {
                        id: this.detail.id,
                        status: status,
                        reason: this.content,
                    },
                    method: 'post',
                }).then(e => {
                    this.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        if(status == 1) {
                            this.detail = {};
                            this.content = '';
                            this.$message.success('操作成功');
                            this.toSearch();
                            this.dialogContent = false;
                        }else {
                            this.$message.success(e.data.msg);
                            this.toSearch();
                            this.detail = {};
                            this.content = '';
                            this.dialogContent = false;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.contentBtnLoading = false;
                });
            },
            toSearch(page) {
                if(this.loading) {
                    return false
                }
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/middleman/index',
                        status: this.activeName,
                        page: page > 1 ? page : 1,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        prop: this.search.type,
                        prop_value: this.search.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        for(item of this.list) {
                            item.showRemark = false;
                            if(this.strlen(item.content) > 56) {
                                item.showRemark = true;
                            }
                        }
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.toSearch(page)
            },

            confirmSubmit() {
                this.search.status = this.activeName
            },
            changeTime() {
                if(this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                }else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.toSearch();
            },
            // 添加备注
            openContent(res) {
                this.dialogContent = true;
                this.title = '添加备注';
                this.placeholder = '请填写备注内容';
                this.detail = res;
                this.content = '';
                if(res.content) {
                    this.title = '修改备注';
                    this.content = res.content
                }
            },
            openDetail(row) {
                this.$navigate({
                    r: 'plugin/community/mall/middleman/detail',
                    id: row.user_id
                });
            },
            // 备注
            beRemark() {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/middleman/check',
                    },
                    data: {
                        id: this.detail.id,
                        status: -1,
                        content: this.content,
                    },
                    method: 'post',
                }).then(e => {
                    this.contentBtnLoading = false;
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.toSearch();
                        this.detail = {};
                        this.content = '';
                        this.dialogContent = false;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/middleman/index',
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        for(item of this.list) {
                            item.showRemark = false;
                            if(this.strlen(item.content) > 56) {
                                item.showRemark = true;
                            }
                        }
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            }
        }
    });
</script>