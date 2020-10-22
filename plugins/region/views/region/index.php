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
    .required-icon .el-form-item__label:before {
        content: '*';
        color: #F56C6C;
        margin-right: 4px;
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
    .el-dialog {
        min-width: 460px;
    }

    .el-tooltip__popper {
        max-width: 260px;
    }

    .look-info .el-form-item--small.el-form-item {
        margin-bottom: 6px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>代理列表</span>
            <el-form size="small" :inline="true" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <app-export-dialog action_url='index.php?r=plugin/region/mall/region/index' :field_list='exportList' :params="search" @selected="confirmSubmit">
                    </app-export-dialog>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="addregion" size="small">新增代理</el-button>
                </el-form-item>
            </el-form>
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
                <div style="margin-left: 20px;" flex="dir:left cross:center">
                    <div style="margin-right: 10px;">代理级别</div>
                    <el-select style="width: 120px" size="small" v-model="level" @change="loadData" class="select">
                        <el-option key="0" label="全部" value="0"></el-option>
                        <el-option key="1" label="区/县代理" value="3"></el-option>
                        <el-option key="2" label="市代理" value="2"></el-option>
                        <el-option key="3" label="省代理" value="1"></el-option>
                    </el-select>
                </div>
                <div style="margin-left: 20px;" flex="dir:left cross:center">
                    <div style="margin-right: 10px;">代理区域</div>
                    <el-select size="small" v-model="province_id" @change="loadData" class="select">
                        <el-option key="0" label="全部" value="0"></el-option>
                        <el-option v-for="item in provinceList" :key="item.id" :label="item.province" :value="item.id"></el-option>
                    </el-select>
                </div>
                <div class="input-item">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable @clear="toSearch">
                        <el-select size="small" v-model="search.type" slot="prepend" class="select">
                            <el-option key="4" label="用户ID" value="4"></el-option>
                            <el-option key="1" label="昵称" value="1"></el-option>
                            <el-option key="2" label="姓名" value="2"></el-option>
                            <el-option key="3" label="手机号" value="3"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未审核" name="0"></el-tab-pane>
                <el-tab-pane label="已通过" name="1"></el-tab-pane>
                <el-tab-pane label="已拒绝" name="2"></el-tab-pane>
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                    <el-table-column label="ID" prop="user_id" width="80"></el-table-column>
                    <el-table-column label="基本信息" width="300">
                        <template slot-scope="scope">
                            <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.user.userInfo.avatar"></app-image>
                            <div style="margin-top: 25px;">{{scope.row.user.nickname}}</div>
                            <div v-if="scope.row.regionInfo.remark" class="content">
                                {{scope.row.regionInfo.remark}}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="归属省市" width="150" prop="attr">
                        <template slot-scope="scope">
                            <div>
                                <span style="font-size: 14px">{{scope.row.attr}}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="代理级别" width="150" prop="level">
                        <template slot-scope="scope">
                            <div>
                                <span style="font-size: 14px">{{scope.row.level_desc}}</span>
                                <el-tooltip v-if="scope.row.level > 1" class="item" effect="dark" :content="scope.row.relationList" placement="top">
                                    <el-button type="text">
                                        [详情]
                                    </el-button>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="姓名" prop="name">
                        <el-table-column label="手机号" prop="phone">
                            <template slot-scope="scope">
                                <div>{{scope.row.regionInfo.name}}</div>
                                <div>{{scope.row.regionInfo.phone}}</div>
                            </template>
                        </el-table-column>
                    </el-table-column>
                    <el-table-column label="累计分红(元)" prop="name">
                        <el-table-column label="已提现分红(元)" prop="phone">
                            <template slot-scope="scope">
                                <div>{{scope.row.regionInfo.all_bonus}}</div>
                                <div>{{scope.row.regionInfo.out_bonus}}</div>
                            </template>
                        </el-table-column>
                    </el-table-column>
                    <el-table-column label="时间" width="250">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status >= 0 && scope.row.applyed_at != '0000-00-00 00:00:00'">申请时间：{{scope.row.applyed_at}}</div>
                            <div v-if="scope.row.status == 1 || scope.row.status == 2">审核时间：{{scope.row.agreed_at}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" width="80" prop="status">
                        <template slot-scope="scope">
                            <el-tag size="small" type="info" v-if="scope.row.status == 0">待审核</el-tag>
                            <el-tag size="small" v-if="scope.row.status == 1">已通过</el-tag>
                            <el-tag size="small" type="danger" v-if="scope.row.status == 2">拒绝</el-tag>
                            <el-tag size="small" type="warning" v-if="scope.row.status == 3">处理中</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="250px" fixed="right">
                        <template slot-scope="scope">
                            <el-button v-if="scope.row.level_up" type="text" size="mini" circle style="margin-top: 10px" @click.native="update(scope.row,1)">
                                <el-tooltip class="item" effect="dark" content="通过升级" placement="top">
                                    <img src="statics/img/mall/update.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.level_up" type="text" size="mini" circle style="margin-top: 10px" @click.native="update(scope.row,2)">
                                <el-tooltip class="item" effect="dark" content="拒绝升级" placement="top">
                                    <img src="statics/img/mall/refuse.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 1 && !scope.row.level_up" type="text" size="mini" circle style="margin-top: 10px" @click.native="agree(scope.row)">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 0" type="text" size="mini" circle style="margin-top: 10px" @click.native="agree(scope.row)">
                                <el-tooltip class="item" effect="dark" content="通过申请" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 0" type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="toRelease(scope.row,false)">
                                <el-tooltip class="item" effect="dark" content="拒绝申请" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 1" type="text" size="mini" circle style="margin-top: 10px" @click.native="toRelease(scope.row,true)">
                                <el-tooltip class="item" effect="dark" content="解除代理" placement="top">
                                    <img src="statics/img/plugins/release.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button v-if="scope.row.status == 2" type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="deleteShare(scope.row.user_id)">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px" @click.native="openContent(scope.row)">
                                <el-tooltip class="item" effect="dark" content="备注" placement="top">
                                    <img src="statics/img/mall/order/add_remark.png" alt="">
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
                            layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog class="look-info" :title="is_remove? '解除代理':'申请成为代理'" :visible.sync="dialogContent" width="30%">
        <el-form :model="detail" :rules="removeRulues" size="small" ref="remove" label-width="120px">
            <el-form-item label="昵称">
                {{detail.user ? detail.user.nickname : ''}}
            </el-form-item>
            <el-form-item label="代理级别">
                {{detail.level_desc}}
            </el-form-item>
            <el-form-item label="归属省">
                {{detail.province}}
            </el-form-item>
            <el-form-item :label="detail.level_desc == '市代理' ? '代理区域' : '归属市'" v-if="detail.level > 1">
                <div v-if="detail.level == 3">{{detail.city}}</div>
                <el-tag v-if="detail.level == 2" type="info" style="margin:5px;margin-top:0;border:0" v-for="(value, key) in detail.relation" :key="key.id">
                    {{value.name}}
                </el-tag>
            </el-form-item>
            <el-form-item label="代理区域" v-if="detail.level > 2">
                <el-tag type="info" style="margin:5px;margin-top:0;border:0" v-for="(value, key) in detail.relation" :key="key.id">
                    {{value.name}}
                </el-tag>
            </el-form-item>
            <el-form-item :label="is_remove? '解除理由':'拒绝理由'" prop="content">
                <el-input type="textarea" :rows="5" v-model="content" :placeholder="is_remove? '请填写解除代理理由':'请填写拒绝理由'" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogContent = false">取 消</el-button>
            <el-button size="small" type="primary" v-if="is_remove" @click="beRelease('remove')" :loading="contentBtnLoading">确认解除</el-button>
            <el-button size="small" type="primary" v-else @click="beApply" :loading="contentBtnLoading">拒绝申请</el-button>
        </div>
    </el-dialog>
    <el-dialog class="look-info" :title="detail.regionInfo.remark.length > 0 ? '修改备注': '添加备注'" :visible.sync="dialogRemark" width="30%">
        <el-form>
            <el-form-item>
                <el-input type="textarea" :rows="5" v-model="remark" placeholder="请填写备注内容" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogRemark = false">取 消</el-button>
            <el-button size="small" type="primary" @click="beRemark" :loading="contentBtnLoading">确认</el-button>
        </div>
    </el-dialog>
    <el-dialog :title="lookApply ? addList.status == 1 ?'编辑代理信息' : '申请成为代理':'添加新代理'" :visible.sync="toChange" width="30%" :before-close="handleClose">
        <el-dialog width="20%" title="选择代理区域" :visible.sync="innerVisible" append-to-body>
            <el-card style="max-height: 400px;overflow-y: auto;" shadow="never">
                <template v-if="district.length > 0" v-for="(item, index) in district">
                    <div class="el-checkbox" style="margin: 10px 0 40px;display: block;">
                        <el-checkbox @change="pickerChange(item)"
                                     v-model="item.checked"
                                     :disabled="item.unchecked">
                            <span style="display: none;" class="el-checkbox__label">{{item.name}}</span>
                        </el-checkbox>
                        <span style="font-size: 16px;" class="el-checkbox__label">{{item.name}}</span>
                    </div>
                </template>
                <template v-if="city.length > 0 && district.length == 0" v-for="(item, index) in city">
                    <div class="el-checkbox" style="margin: 10px 0 40px;display: block;">
                        <el-checkbox @change="pickerChange(item)"
                                     v-model="item.checked"
                                     :disabled="item.unchecked">
                            <span style="display: none;" class="el-checkbox__label">{{item.name}}</span>
                        </el-checkbox>
                        <span style="font-size: 16px;" class="el-checkbox__label">{{item.name}}</span>
                    </div>
                </template>
            </el-card>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="changePlace">确认选择</el-button>
            </div>
        </el-dialog>
        <el-form :model="addList" :rules="rules" size="small" ref="addForm" label-width="120px">
            <el-form-item v-if="!lookApply" label="分销商选择" prop="nickname">
                <el-autocomplete size="small" style="width: 70%;" v-model="addList.nickname" value-key="nickname" :fetch-suggestions="querySearchAsync" placeholder="请输入分销商昵称" @select="shareClick"></el-autocomplete>
            </el-form-item>
            <el-form-item v-else label="昵称">
                {{addList.user ? addList.user.nickname : ''}}
            </el-form-item>
            <el-form-item label="代理级别" prop="level">
                <el-select size="small" @change="chooseLevel" style="width: 70%;" v-model="addList.level" placeholder="请选择">
                    <el-option label="省代理" :value="1"></el-option>
                    <el-option label="市代理" :value="2"></el-option>
                    <el-option label="区/县代理" :value="3"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="归属省" prop="province">
                <el-select size="small" @change="chooseProvince" style="width: 70%;" v-model="addList.province" placeholder="请选择">
                    <el-option v-for="item in province" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item :label="addList.level == 2 ? '代理区域' : '归属市'" class="required-icon" v-if="addList.level > 1" prop="city">
                <el-select v-if="addList.level == 3" size="small" @change="chooseCity" style="width: 70%;" v-model="addList.city" placeholder="请选择">
                    <el-option v-for="item in city" :label="item.name" :value="item.id"></el-option>
                </el-select>
                <div v-else>
                    <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in addList.city" :key="key.id">
                        {{value.name}}
                    </el-tag>
                    <el-button @click="choosePlace">选择</el-button>
                </div>
            </el-form-item>
            <el-form-item label="代理区域" class="required-icon" v-if="addList.level == 3" prop="district">
                    <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in addList.district" :key="key.id">
                        {{value.name}}
                    </el-tag>
                    <el-button @click="choosePlace">选择</el-button>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="handleClose">取 消</el-button>
            <el-button size="small" type="primary" @click="changeSubmit('addForm')" :loading="contentBtnLoading">{{lookApply ? addList.status == 1 ?'确认修改':'通过申请' : '确 定'}}</el-button>
        </div>
    </el-dialog>
    <el-dialog title="升级代理" :visible.sync="toUpdate" width="30%" :before-close="handleClose">
        <el-form :rules="removeRulues" :model="updateList" size="small" ref="updateFrom" label-width="150px">
            <el-form-item label="昵称">
                {{updateList.user.nickname}}
            </el-form-item>
            <el-form-item label="申请升级代理级别" prop="level">
                <div v-if="showLevel == 1">省代理</div>
                <div v-if="showLevel == 2">市代理</div>
            </el-form-item>
            <el-form-item label="申请升级代理区域" prop="province" v-if="showLevel == 1">
                <el-select disabled size="small" @change="chooseProvince" style="width: 70%;" v-model="updateList.province_id" placeholder="请选择">
                    <el-option v-for="item in province" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="申请升级代理区域" v-if="showLevel == 2" prop="city">
                <el-tag type="info" style="margin:0 5px;border:0" v-for="(value, key) in updateList.level_up.relation" :key="key.id">
                    {{value.name}}
                </el-tag>
            </el-form-item>
            <el-form-item v-if="upStatus == 2" label="拒绝理由" prop="content">
                <el-input type="textarea" :rows="5" v-model="content" placeholder="请填写拒绝理由" autocomplete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="handleClose">取 消</el-button>
            <el-button size="small" type="primary" @click="updateSubmit('updateFrom')" :loading="contentBtnLoading">{{upStatus == 1 ?'通过申请':'拒绝申请'}}</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                if (this.value === '' || this.value === undefined) {
                    callback(new Error('请选择代理等级'));
                } else {
                    callback();
                }
            };
            var validateRate2 = (rule, value, callback) => {
                if (this.content === '' || this.content === undefined) {
                    callback(new Error('请填写理由'));
                } else {
                    callback();
                }
            };

            return {
                search: {
                    date_start: '',
                    date_end: '',
                    keyword: '',
                    type: '4',
                    page: 1,
                    time: []
                },
                rules: {
                    nickname: [
                        { required: true, message: '请选择分销商', trigger: 'change' }
                    ],
                    province: [
                        { required: true, message: '请选择归属省', trigger: 'change' }
                    ],
                    level: [
                        { required: true, validator: validateRate, trigger: 'change' }
                    ],
                },
                removeRulues: {
                    content: [
                        { required: true, validator: validateRate2, trigger: 'blur' }
                    ],
                },
                level_list: [],
                chooseList: [],
                addList: {},
                updateList: {
                    user: {
                        nickname: ''
                    },
                    level_up: {}
                },
                level: '0',
                showLevel: 1,
                province_id: '0',
                is_remove: false,
                loading: false,
                activeName: '-1',
                list: [],
                provinceList: [],
                value: null,
                toChangeLevel: false,
                toUpdate: false,
                pagination: null,
                dialogLoading: false,
                dialogRemark: false,
                dialogContent: false,
                innerVisible: false,
                toChange: false,
                lookApply: false,
                content: "",
                remark: '',
                detail: {
                    regionInfo: {
                        remark: ''
                    }
                },
                contentBtnLoading: false,
                exportList: [],
                member: [],
                index: -1,
                upStatus: 1,
                province: [],
                city: [],
                district: [],
                keyword: {},
                status: null
            }
        },
        mounted() {
            this.loadData();
        },
        methods: {
            updateSubmit(formName) {
                let that = this;
                that.$refs[formName].validate((valid) => {
                    console.log(valid)
                    if (valid) {
                        let city = [];
                        if(that.updateList.level_up.level == '2') {
                            if(that.updateList.level_up.relation.length > 0) {
                                for(let i in that.chooseList) {
                                    city.push(that.chooseList[i].id)
                                }
                            }else {
                                that.$message.error('请选择申请升级代理区域');
                                return false;
                            }
                        }
                        that.contentBtnLoading = true;
                        request({
                            params: {
                                r: 'plugin/region/mall/region/level-up'
                            },
                            data: {
                                user_id: that.updateList.user_id,
                                level: that.updateList.level_up.level,
                                city_id: city,
                                status: this.upStatus,
                                reason: that.content
                            },
                            method: 'post',
                        }).then(e => {
                            that.contentBtnLoading = false;
                            if (e.data.code == 0) {
                                that.addList = {
                                    user_id: '',
                                    level: '',
                                    province: '',
                                    city: '',
                                    district: '',
                                };
                                that.updateList = {
                                    user: {
                                        nickname: ''
                                    },
                                    level_up: {}
                                };
                                that.content = '';
                                that.value = null;
                                that.toChange = false;
                                that.toUpdate = false;
                                that.lookApply = false;
                                that.chooseList = [];
                                that.city = [];
                                that.district = [];
                                that.loadData();
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.contentBtnLoading = false;
                        });
                    }
                })
            },
            update(e,status) {
                this.updateList = e;
                this.showLevel = e.level_up.level;
                this.chooseList = [];
                this.upStatus = status;
                this.toUpdate = true;
                this.updateList.city = [];
                this.content = ''
                if(this.showLevel == 2) {
                    for(let i in this.province) {
                        if(this.province[i].id == e.province_id) {
                            this.city = this.province[i].list
                        }
                    }
                    this.district = [];
                    for(let i in this.city) {
                        this.city[i].checked = false;
                    }
                    for(let i in e.level_up.relation) {
                        let para = {
                            id: e.level_up.relation[i].district_id,
                            name: e.level_up.relation[i].name
                        }
                        for(let j in this.city) {
                            if(this.city[j].id == e.level_up.relation[i].district_id) {
                                this.city[j].checked = true;
                                this.chooseList.push(this.city[j])
                            }
                        }
                        this.updateList.city.push(para)
                    }
                }
            },
            choosePlace() {
                if(this.addList.level == 2) {
                    if(this.addList.province) {
                        this.innerVisible=true
                    }else {
                        this.$message.error('请选择归属省');
                    }
                }
                if(this.addList.level == 3) {
                    if(this.addList.city) {
                        this.innerVisible=true
                    }else {
                        this.$message.error('请选择归属市');
                    }
                }
                if(this.showLevel == 2) {
                    this.innerVisible=true
                }
            },
            changePlace() {
                if(this.toChange) {
                    if(this.district.length > 0) {
                        this.addList.district = this.chooseList;
                    }else {
                        this.addList.city = this.chooseList;
                    }
                }
                if(this.toUpdate) {
                    this.updateList.level_up.relation = this.chooseList;
                }
                this.innerVisible = false;
            },
            pickerChange(e) {
                let that = this;
                if(e.checked) {
                    that.chooseList.push(e);
                }else {
                    let idx = -1;
                    for(let i in that.chooseList) {
                        if(that.chooseList[i].id == e.id) {
                            idx = i;
                        }
                    }
                    if(that.district.length > 0) {
                        for(let i in that.district) {
                            if(e.id == that.district[i].id) {
                                that.chooseList.splice(idx,1)
                            }
                        }
                    }else {
                        for(let i in that.city) {
                            if(e.id == that.city[i].id) {
                                that.chooseList.splice(idx,1)
                            }
                        }
                    }
                }
                that.$forceUpdate();
            },
            chooseProvince(e) {
                let that = this;
                if(that.addList.level == 2) {
                    that.addList.city = [];
                    that.addList.district = [];
                }else if(that.addList.level == 3) {
                    that.addList.city = '';
                    that.addList.district = [];
                }
                that.chooseList = [];
                for(let i in that.province) {
                    if(that.province[i].id == e) {
                        that.city = that.province[i].list
                        for(let j in that.city) {
                            that.city[j].checked = false;
                        }
                    }
                }
            },
            chooseCity(e) {
                let that = this;
                that.addList.district = [];
                that.chooseList = [];
                for(let i in that.city) {
                    if(that.city[i].id == e) {
                        that.district = that.city[i].list
                        for(let j in that.district) {
                            that.district[j].checked = false;
                        }
                    }
                }
            },
            changeLevel(e,index) {
                this.toChangeLevel = true;
                this.detail = e;
                this.index = index;
                this.value = e.level_id;
            },
            submitChangeLevel() {
                let that = this;
                let level_name;
                for(let i in that.level_list) {
                    if(that.level_list[i].id == that.value) {
                        level_name = that.level_list[i].name
                    }
                }
                that.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/region/mall/region/add'
                    },
                    data: {
                        user_id: that.detail.user_id,
                        level_id: that.value,
                        name: that.detail.name,
                        phone: that.detail.phone,
                    },
                    method: 'post',
                }).then(e => {
                    that.contentBtnLoading = false;
                    if (e.data.code == 0) {
                        that.toChangeLevel = false;
                        that.detail = {
                            regionInfo: {
                                remark: ''
                            }
                        };
                        that.list[that.index].level_id = that.value;
                        that.list[that.index].level_name = level_name;
                        that.value = null;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.contentBtnLoading = false;
                });
            },

            addregion() {
                if(this.province.length == 0) {
                    this.$message.error('请先添加代理级别');
                    return false
                }
                this.toChange = true;
                this.lookApply = false;
                this.city = [];
                this.$nextTick(()=>{
                   this.$refs['addForm'].clearValidate();
                })
            },

            handleClose() {
                let that = this;
                that.value = null;
                that.toChange = false;
                that.toUpdate = false;
                setTimeout(function(){
                    that.addList = {
                        user_id: '',
                        level: '',
                        province: '',
                        city: '',
                        district: '',
                    };
                    that.updateList = {
                        user: {
                            nickname: ''
                        },
                        level_up: {}
                    };
                },400)
            },
            //搜索
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.shareUser(cb);
            },

            shareClick(row) {
                this.addList.user_id = row.id;
            },

            chooseLevel(e) {
                this.addList.level = e;
                this.chooseList = [];
                if(e == 2) {
                    this.addList.city = [];
                    this.addList.district = [];
                    this.district = [];
                }else if(e == 3) {
                    this.addList.city = '';
                    this.addList.district = [];
                }
            },

            shareUser(cb) {
                request({
                    params: {
                        r: 'plugin/region/mall/region/share',
                        nickname: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {});
            },
            // 添加代理
            changeSubmit(formName) {
                let that = this;
                that.$refs[formName].validate((valid) => {
                    if (valid) {
                        that.contentBtnLoading = true;
                        let city = [];
                        let district = [];
                        if(that.addList.level == '2') {
                            for(let i in that.addList.city) {
                                city.push(that.addList.city[i].id)
                            }
                        }else {
                            city.push(that.addList.city)
                        }
                        if(that.addList.level == '3') {
                            for(let i in that.addList.district) {
                                district.push(that.addList.district[i].id)
                            }
                        }
                        let para = {
                            user_id: that.addList.user_id,
                            level: that.addList.level,
                            province_id: that.addList.province,
                            city_id: city,
                            district_id: district,
                        }
                        if(that.lookApply && that.addList.status != 1) {
                            para.status = 1
                        }
                        request({
                            params: {
                                r: that.lookApply && that.addList.status != 1 ? 'plugin/region/mall/region/apply' : 'plugin/region/mall/region/add'
                            },
                            data: para,
                            method: 'post',
                        }).then(e => {
                            that.contentBtnLoading = false;
                            if (e.data.code == 0) {
                                that.toChange = false;
                                that.addList = {
                                    user_id: '',
                                    level: '',
                                    province: '',
                                    city: '',
                                    district: '',
                                };
                                that.lookApply = false;
                                that.chooseList = [];
                                that.city = [];
                                that.district = [];
                                that.loadData();
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.contentBtnLoading = false;
                        });
                    }
                })
            },
            // 通过审核
            agree(e) {
                let that = this;
                that.$nextTick(()=>{
                   that.$refs['addForm'].clearValidate();
                })
                that.addList = JSON.parse(JSON.stringify(e));
                that.addList.province = e.province_id;
                that.chooseList = [];
                for(let i in that.province) {
                    if(that.province[i].id == e.province_id) {
                        that.city = that.province[i].list
                    }
                }
                for(let i in that.city) {
                    that.city[i].checked = false;
                }
                that.addList.city = [];
                that.addList.district = [];
                if(e.level == '3') {
                    that.addList.city = e.city_id;
                    for(let i in that.city) {
                        if(that.city[i].id == e.city_id) {
                            that.district = that.city[i].list
                        }
                    }
                    for(let i in that.district) {
                        that.district[i].checked = false;
                    }
                    for(let i in e.relation) {
                        let para = {
                            id: e.relation[i].district_id,
                            name: e.relation[i].name
                        }
                        for(let j in that.district) {
                            if(that.district[j].id == e.relation[i].district_id) {
                                that.district[j].checked = true;
                                that.chooseList.push(that.district[j])
                            }
                        }
                        that.addList.district.push(para)
                    }
                }else {
                    that.district = [];
                    for(let i in e.relation) {
                        let para = {
                            id: e.relation[i].district_id,
                            name: e.relation[i].name
                        }
                        for(let j in that.city) {
                            if(that.city[j].id == e.relation[i].district_id) {
                                that.city[j].checked = true;
                                that.chooseList.push(that.city[j])
                            }
                        }
                        that.addList.city.push(para)
                    }
                }
                that.lookApply = true;
                that.toChange = true;
            },
            // 发送审核消息
            beApply() {
                let that = this;
                that.contentBtnLoading = true;
                let city = [];
                let district = [];
                if(that.detail.level == '2') {
                    for(let i in that.detail.relation) {
                        city.push(that.detail.relation[i].district_id)
                    }
                }else {
                    city.push(that.detail.city_id)
                }
                if(that.detail.level == '3') {
                    for(let i in that.detail.relation) {
                        district.push(that.detail.relation[i].district_id)
                    }
                }
                request({
                    params: {
                        r: 'plugin/region/mall/region/apply',
                    },
                    data: {
                        user_id: that.detail.user_id,
                        province_id: that.detail.province_id,
                        level: that.detail.level,
                        city_id: city,
                        district_id: district,
                        status: that.status,
                        reason: that.content,
                    },
                    method: 'post',
                }).then(e => {
                    that.contentBtnLoading = false;
                    that.loading = false;
                    if (e.data.code == 0) {
                        if(that.status == 1) {
                            that.detail = {
                                regionInfo: {
                                    remark: ''
                                }
                            };
                            that.status = null;
                            that.content = '';
                            // let queue_id = e.data.data.queue_id;
                            // that.passStatus(queue_id)
                            that.$message.success('操作成功');
                            that.loadData();
                            that.contentBtnLoading = false;
                            that.dialogContent = false;
                        }else {
                            that.$message.success(e.data.data);
                            that.loadData();
                            that.detail = {
                                regionInfo: {
                                    remark: ''
                                }
                            };
                            that.status = null;
                            that.content = '';
                            that.dialogContent = false;
                        }
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.contentBtnLoading = false;
                    that.loading = false;
                });
            },
            // 时间筛选
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
            // 搜索
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            // 获取状态
            confirmSubmit() {
                this.search.status = this.activeName
            },
            // 获取数据
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/region/mall/region/index',
                        status: this.activeName,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        keyword: this.search.keyword,
                        search_type: this.search.type,
                        province_id: this.province_id,
                        level: this.level,
                        page: this.search.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.provinceList = e.data.data.province;
                        this.province = e.data.data.allow_province;
                        this.exportList = e.data.data.export_list;
                        for(let i in this.list) {
                            this.list[i].relationList = '';
                            for(let j in this.list[i].relation) {
                                if(j > 0) {
                                    this.list[i].relationList += '、' + this.list[i].relation[j].name
                                }else {
                                    this.list[i].relationList += this.list[i].relation[j].name
                                }
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 分页
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            // 获取数据状态
            handleClick(tab, event) {
                this.search.status = this.activeName;
                this.toSearch();
            },
            // 审核拒绝发起
            toRelease(e,is_remove) {
                this.dialogContent = true;
                this.is_remove = is_remove;
                this.content = '';
                this.detail = e;
                for(let i in this.province) {
                    if(this.province[i].id == e.province_id) {
                        this.detail.province = this.province[i].name
                    }
                }
                if(e.level == 3) {
                    this.detail.city = this.detail.attr.replace(this.detail.province,'')
                }
                this.status = 2;
            },
            // 备注
            beRemark() {
                this.contentBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/region/mall/region/remark',
                    },
                    data: {
                        user_id: this.detail.user_id,
                        remark: this.remark,
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.contentBtnLoading = false;
                        this.loadData();
                        this.detail = {
                            regionInfo: {
                                remark: ''
                            }
                        };
                        this.remark = '';
                        this.dialogRemark = false;
                    } else {
                        this.contentBtnLoading = false;
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.contentBtnLoading = false;
                    this.loading = false;
                });
            },
            // 解除代理
            beRelease(formName) {
                console.log(formName)
                let that = this;
                that.$refs[formName].validate((valid) => {
                    if (valid) {
                        that.contentBtnLoading = true;
                        request({
                            params: {
                                r: 'plugin/region/mall/region/remove',
                            },
                            data:{
                                user_id: that.detail.user_id,
                                reason: that.content,
                            },
                            method: 'post'
                        }).then(e => {
                            that.contentBtnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success('解除成功');
                                that.loadData();
                                that.contentBtnLoading = false;
                                that.dialogContent = false;
                                // let queue_id = e.data.data.queue_id;
                                // that.remove(queue_id)
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error(e.data.msg);
                        });
                    }
                })
            },
            // 删除记录
            deleteShare(id) {
                this.$confirm('是否删除该条记录', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'plugin/region/mall/region/delete',
                                },
                                data:{
                                    user_id: id
                                },
                                method: 'post'
                            }).then(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                if (e.data.code == 0) {
                                    this.loadData();
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                this.$message.error(e.data.msg);
                            });
                        } else {
                            done();
                        }
                    }
                }).then(() => {
                }).catch(e => {
                    this.$message({
                        type: 'info',
                        message: '取消了操作'
                    });
                });
            },
            // 申请添加备注
            openContent(res) {
                this.dialogRemark = true;
                this.detail = res;
                this.remark = '';
                if(res.regionInfo.remark) {
                    this.remark = res.regionInfo.remark
                }
            },
        }
    });
</script>