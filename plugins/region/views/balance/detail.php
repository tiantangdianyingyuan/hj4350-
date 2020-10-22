<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
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
    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
    .el-tag {
        border-color: #409EFF;
        margin-right: 10px;
    }

    .choose-area {
        margin-bottom: 20px;
    }
    .clean {
        color: #92959B;
        margin-left: 35px;
        cursor: pointer;
        font-size: 15px;
        height: 32px;
        line-height: 32px;
    }
</style>
<div id="app" v-cloak v-loading="loading">
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/region/mall/balance/index'})">分红结算</span></el-breadcrumb-item>
                <el-breadcrumb-item>结算详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <div flex="dir:left cross:bottom" class="choose-area">
                <div v-for="(item,index) in chooseArea">
                    <el-tag v-if="index < 3" :key="item.id" type="success" effect="plain">{{item.name}}</el-tag>
                </div>
                <div v-if="chooseArea.length > 3" style="color: #67c23a;margin-right: 15px">...</div>
                <el-button size="small" @click="openChoose">筛选地区</el-button>
                <div v-if="chooseArea[0].id > 0" class="clean" @click="clean">清空筛选</div>
            </div>
            <el-table :data="detail" border v-loading="loading" style="margin-bottom: 15px;width: 900px">
                <el-table-column label="结算周期" width="300">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <el-tag size="small" v-if="scope.row.bonus_type == 1">按周</el-tag>
                            <el-tag size="small" v-else>按月</el-tag>
                            <div>{{scope.row.start_time}}~{{scope.row.end_time}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="订单数" prop="order_num"></el-table-column>
                <el-table-column label="分红金额" prop="price" width="250">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.bonus_price}}({{scope.row.bonus_rate}}%订单分红比例)</div>
                    </template>
                </el-table-column>
                <el-table-column label="代理数" prop="region_num"></el-table-column>
            </el-table>
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div flex="dir:left cross:center">
                    <div class="input-item">
                        <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="keyword_1" clearable @clear="toSearch">
                            <el-select size="small" v-model="keyword" slot="prepend" class="select">
                                <el-option key="1" label="昵称" :value="1"></el-option>
                                <el-option key="2" label="姓名" :value="2"></el-option>
                                <el-option key="3" label="手机号" :value="3"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                        </el-input>
                    </div>
                </div>
            </div>

            <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                <el-table-column label="基本信息" width="350">
                    <template slot-scope="scope">
                        <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.avatar"></app-image>
                        <div style="margin-top: 25px;">{{scope.row.nickname}}</div>
                        <img src="statics/img/mall/wx.png" v-if="scope.row.platform == 'wxapp'" alt="">
                        <img src="statics/img/mall/ali.png" v-else-if="scope.row.platform == 'aliapp'" alt="">
                        <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.platform == 'ttapp'" alt="">
                        <img src="statics/img/mall/baidu.png" v-else-if="scope.row.platform == 'bdapp'" alt="">
                    </template>
                </el-table-column>
                <el-table-column label="姓名" width="200" prop="name">
                    <el-table-column label="手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.name}}</div>
                            <div>{{scope.row.phone}}</div>
                        </template>
                    </el-table-column>
                </el-table-column>
                <el-table-column label="代理级别" width="200" prop="level">
                </el-table-column>
                <el-table-column label="分红区域" width="400" prop="level_name">
                </el-table-column>
                <el-table-column label="分红比例" prop="price">
                    <template slot-scope="scope">
                        <div>{{scope.row.bonus_rate}}%</div>
                    </template>
                </el-table-column>
                <el-table-column label="分红金额" prop="price">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.price}}</div>
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
                            layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
        </div>
        <el-dialog title="筛选地区" :visible.sync="dialogVisible" width="50%">
            <div style="margin-bottom: 1rem;">
                <app-district :all="all" :edit="area" @selected="selectDistrict" :level="3">
                    <template slot="other">
                        <div class="el-checkbox" style="margin: 0 0 20px;display: block">
                            <el-checkbox @change="allSelect" v-model="other_list.checked">
                                <span style="display: none" class="el-checkbox__label">{{other_list.name}}</span>
                            </el-checkbox>
                            <span class="el-checkbox__label">{{other_list.name}}</span>
                        </div>
                    </template>
                </app-district>
                <div style="text-align: right;margin-top: 1rem;">
                    <el-button type="primary" @click="districtConfirm">
                        确定选择
                    </el-button>
                </div>
            </div>
        </el-dialog>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                type: '1',
                level: '全部代理',
                level_name: '',
                keyword: 1,
                keyword_1: '',
                bonus_id: 0,
                all: true,
                loading: false,
                other_list: {
                    id: 0,
                    name: '全国',
                    checked: true
                },
                list: [],
                area: [{id:0,name:'全国'}],
                chooseArea: [{id:0,name:'全国'}],
                dialogVisible: false,
                detail: [],
                pagination: {}

            };
        },
        created() {
            this.bonus_id = getQuery('id');
            this.loadData();
        },
        methods: {
            clean() {
                this.all = true;
                this.area = [{id:0,name:'全国',checked: true}]
                this.chooseArea = [{id:0,name:'全国',checked: true}]
                this.other_list.checked = true;
                this.loadData();
            },
            allSelect(e) {
                this.all = e;
                if(e) {
                    this.area = [];
                    let obj = {
                        id: 0,
                        name: '全国'
                    };
                    this.area.push(obj);
                }else {
                    for(let i in this.area) {
                        if(this.area[i].id == 0) {
                            this.area.splice(i,1)
                        }
                    }
                }
            },
            changeLevel(e) {
                this.level_name = e != '全部代理' ? e : '';
                this.loadData();
            },
            loadData(page) {
                this.loading = true;
                let address_id = [];
                if(this.chooseArea.length > 0) {
                    for(let i in this.chooseArea) {
                        if(this.chooseArea[i].id > 0) {
                            address_id.push(this.chooseArea[i].id)
                        }
                    }
                }
                request({
                    params: {
                        r: 'plugin/region/mall/balance/detail',
                        bonus_id: this.bonus_id,
                        level_name: this.level_name,
                        keyword: this.keyword_1?this.keyword: '',
                        keyword_1: this.keyword_1,
                        bonus_id: this.bonus_id,
                        address_id: address_id,
                        page: page ? page : 1
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.detail = [];
                        this.detail.push(e.data.data.bonus_data);
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            openChoose() {
                this.dialogVisible = true;
                if(this.chooseArea[0].id == 0) {
                    this.other_list.checked = true;
                    this.all = true
                }
                this.area = JSON.parse(JSON.stringify(this.chooseArea));
            },
            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.area = list;
            },
            toSearch() {
                this.loadData();
            },
            districtConfirm() {
                if(this.area.length > 0) {
                    this.chooseArea = JSON.parse(JSON.stringify(this.area));
                    this.dialogVisible = false;
                    this.loadData();
                }else {
                    this.$message.error('请选择要筛选地区');
                }
            },
            pageChange(page) {
                this.loading = true;
                this.loadData(page);
            }
        } 
    })
</script>