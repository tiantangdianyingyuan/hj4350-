<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 14:12
 */
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 25px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .el-dialog__body {
        padding-bottom: 0;
    }

    .el-textarea .el-textarea__inner {
        resize: none;
    }

    button img{
        outline:none!important;
    }

    #edui1_iframeholder {
        height: 800px!important;
        overflow: auto!important;
    }

    .required-icon .el-form-item__label:before {
        content: '*';
        color: #F56C6C;
        margin-right: 4px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-form :model="form" label-width="10rem" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="升级依据" name="first">
                    <div class="form-body">
                        <el-form-item class="switch" prop="type" label="股东升级依据" required>
                            <el-radio-group v-model="form.type">
                                <el-radio :label="1">下线总人数</el-radio>
                                <el-radio :label="2">累计佣金总额</el-radio>
                                <el-radio :label="3">已提现佣金总额</el-radio>
                                <el-radio :label="4">分销订单总数</el-radio>
                                <el-radio :label="5">分销订单总金额</el-radio>
                            </el-radio-group>
                            <div style="color: #C0C4CC;height: 20px;margin-top: -10px">注：升级依据被更改后，需对股东等级编辑新的升级标准，否则默认不自动升级</div>
                        </el-form-item>
                        <el-form-item class="switch" label="升级说明" prop="remark">
                            <div style="width: 590px;">
                                <app-rich-text v-model="form.remark"></app-rich-text>
                            </div>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="等级列表" name="second">
                    <div class="table-body">
                        <el-button type="primary" size="small" @click="add">新增股东等级</el-button>
                        <el-table :data="list" border v-loading="tableLoading" style="margin: 15px 0;">
                            <el-table-column label="等级名称" prop="name"></el-table-column>
                            <el-table-column label="分红比例" prop="bonus_rate">
                                <template slot-scope="scope">
                                    {{scope.row.bonus_rate}}%
                                </template>
                            </el-table-column>
                            <el-table-column label="升级条件" width="220">
                                <template slot-scope="scope">
                                    <div v-if="scope.row.is_default == 1">默认等级</div>
                                    <div v-else-if="scope.row.condition == 0">不自动升级</div>
                                    <div v-else>
                                        <span v-if="up_type == 1">下线总人数满</span>
                                        <span v-else-if="up_type == 2">累计佣金总额满</span>
                                        <span v-else-if="up_type == 3">已提现佣金总额满</span>
                                        <span v-else-if="up_type == 4">分销订单总数满</span>
                                        <span v-else-if="up_type == 5">分销订单总金额满</span>                                        
                                        <span>{{scope.row.condition}}</span>
                                        <span v-if="up_type == 1">人</span>
                                        <span v-else-if="up_type == 4">单</span>
                                        <span v-else>元</span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="添加时间" prop="created_at"></el-table-column>
                            <el-table-column label="操作" width="180" fixed="right">
                                <template slot-scope="scope">
                                    <el-button circle size="mini" type="text" @click="editLevel(scope.row,scope.$index)">
                                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                            <img src="statics/img/mall/edit.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                    <el-button v-if="scope.row.is_default != 1" circle size="mini" type="text" @click="destroy(scope.row,scope.$index)">
                                        <el-tooltip v-if="!tableLoading" class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <div flex="dir:right" style="margin-top: 20px;">
                            <el-pagination
                                    hide-on-single-page
                                    style="display: inline-block;float: right;"
                                    background :page-size="pagination.pageSize"
                                    @current-change="pageChange"
                                    layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                                    :total="pagination.totalCount">
                            </el-pagination>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" v-if="activeName == 'first'" type="primary" size="small" :loading=submitLoading @click="submit">保存</el-button>
        </el-form>
    </el-card>
    <el-dialog title="编辑股东等级" :visible.sync="toChange" width="30%">
        <el-form :model="addList" :rules="addRules" size="small" ref="addForm" label-width="100px">
            <el-form-item label="等级名称" prop="name">
                <el-input maxlength="6" type="text" placeholder="请输入等级名称" style="width: 85%;" size="small" v-model="addList.name" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item style="margin-bottom: 8px" label="分红比例" required>
                <el-input @blur="changeBonus" type="text" style="width: 85%;" size="small" v-model="addList.bonus_rate" autocomplete="off">
                    <template slot="append">%</template>
                </el-input>
                <div style="color: #C0C4CC">分红比例不能为0，保留小数点后2位</div>
            </el-form-item>
            <el-form-item v-if="addList.is_default != 1" class="required-icon" style="margin-bottom: 8px" label="升级条件" prop="condition">
                <el-input @input="checkInput" type="text" placeholder="升级条件必须为整数" style="width: 85%;" size="small" v-model="addList.condition" autocomplete="off">
                    <template v-if="up_type == 1" slot="prepend">下线总人数满</template>
                    <template v-else-if="up_type == 2" slot="prepend">累计佣金总额满</template>
                    <template v-else-if="up_type == 3" slot="prepend">已提现佣金总额满</template>
                    <template v-else-if="up_type == 4" slot="prepend">分销订单总数满</template>
                    <template v-else-if="up_type == 5" slot="prepend">分销订单总金额满</template>
                    <template slot="append" v-if="up_type == 1">人</template>
                    <template slot="append" v-else-if="up_type == 4">单</template>
                    <template slot="append" v-else>元</template>
                </el-input>
                <div style="color: #C0C4CC">升级条件必须大于0，不填则默认为不自动升级</div>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="toChange = false">取消</el-button>
            <el-button size="small" type="primary" @click="changeSubmit('addForm')" :loading="contentBtnLoading">提交</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                let condition = this.addList.condition.toString();
                if (!(this.addList.condition > -1 || this.addList.condition == '') || condition.indexOf('.') > -1) {
                    callback(new Error('升级条件必须为整数'));
                } else {
                    callback();
                }
            };
            return {
                addRules: {
                    name: [
                        { required: true, message: '请输入等级名称', trigger: 'blur' }
                    ],
                    condition: [
                        { validator: validateRate, trigger: 'blur' }
                    ],
                },
                activeName: 'first',
                form: {
                    type: 1,
                    remark: ''
                },
                index: -1,
                addList: {
                    name: '',
                    bonus_rate: '',
                    condition: '',
                    is_default: 0
                },
                textarea: '',
                up_type: 0,
                submitLoading: false,
                loading: false,
                tableLoading: false,
                contentBtnLoading: false,
                pagination: {},
                toChange: false,
                rules: {},
                list: []
            }
        },
        created() {
            this.loading = true;
            this.getDetail();
        },
        methods: {
            changeBonus() {
                if(this.addList.bonus_rate > 0) {
                    this.addList.bonus_rate = parseFloat(this.addList.bonus_rate).toFixed(2);
                }
            },
            checkInput(value) {
                value = value.toString();
                if(value.length>8){
                    this.addList.condition=value.slice(0,8)
                }
                let index = value.indexOf('-')
                if(index > -1) {
                    this.addList.condition=value.replace('-','');
                }
            },
            destroy(row,index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.tableLoading = true;
                    request({
                        params: {
                            r: 'plugin/stock/mall/level/level-del',
                            id: row.id
                        },
                    }).then(e => {
                        self.tableLoading = false;
                        if (e.data.code === 0) {
                            self.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            self.list.splice(index,1);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                        self.tableLoading = false;
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            getList(page) {
                let that = this;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/level-list',
                        page: page? page: 1
                    },
                }).then(e => {
                    this.loading = false;
                    this.tableLoading = false;
                    if (e.data.code == 0) {
                        that.list = e.data.data.list;
                        that.up_type = e.data.data.up_type;
                        that.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                    this.tableLoading = false;
                });
            },
            pageChange(page) {
                this.loading = true;
                this.getList(page);
            },
            add() {
                this.toChange = true;
                this.$nextTick(()=>{
                   this.$refs['addForm'].clearValidate();
                })
                this.index = -1;
                this.addList = {
                    name: '',
                    bonus_rate: '',
                    condition: '',
                    is_default: 0
                }
            },
            editLevel(e,index) {
                this.toChange = true;
                this.$nextTick(()=>{
                   this.$refs['addForm'].clearValidate();
                })
                this.addList =JSON.parse(JSON.stringify(e));
                if(this.addList.condition == 0) {
                    this.addList.condition = ''
                }
                this.index = index;
            },
            changeSubmit(formName) {
                let that = this;
                that.$refs[formName].validate((valid) => {
                    if (valid) {
                        if(that.addList.condition != '' && that.addList.condition == 0 && that.addList.is_default != 1) {
                            that.$message.error('升级条件必须大于0');
                        }else {
                            that.contentBtnLoading = true;
                            let para = {};
                            para.r = 'plugin/stock/mall/level/level-add';
                            para.name = that.addList.name;
                            para.bonus_rate = +that.addList.bonus_rate;
                            para.condition = that.addList.condition ? that.addList.condition : 0;
                            if(that.addList.id > 0) {
                                para.id = that.addList.id;
                            }
                            request({
                                params: para,
                            }).then(e => {
                                that.contentBtnLoading = false;
                                if (e.data.code == 0) {
                                    that.toChange = false;
                                    that.$message({
                                        message: e.data.msg,
                                        type: 'success'
                                    });
                                    if(that.index > -1) {
                                        that.list[that.index].name = para.name;
                                        that.list[that.index].bonus_rate = para.bonus_rate;
                                        that.list[that.index].condition = para.condition;
                                    }else {                                    
                                        that.loading = true;
                                        setTimeout(function(){
                                            that.getList();
                                        },500);
                                    }
                                } else {
                                    that.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                that.contentBtnLoading = false;
                                that.loading = false;
                            });
                        }
                    }
                })
            },
            getDetail() {
                let that = this;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/upgrade-condition',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        that.form = e.data.data;
                        that.getList();
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
            submit() {
                let that = this;
                that.submitLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/upgrade-condition',
                    },
                    data: {
                        type: that.form.type,
                        remark: that.form.remark,
                    },
                    method: 'post'
                }).then(e => {
                    that.submitLoading = false;
                    if (e.data.code == 0) {
                        that.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        that.activeName = 'second';
                        that.loading = false;
                        that.getList();
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.submitLoading = false;
                });
            }
        }
    });
</script>