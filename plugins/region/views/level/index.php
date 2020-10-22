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

    .tip-area {
        height: 36px;
        line-height: 36px;
        padding: 0 15px;
        background-color: #F4F4F5;
        display: inline-block;
    }

    .tip-area span {
        color: #999;
        font-size: 15px;
    }

    .el-dialog {
        min-width: 460px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>代理级别</span>
            <el-form v-if="provinces.length < 34" size="small" :inline="true" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <el-button type="primary" @click="addRegion" size="small">新增区域组</el-button>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">
            <div class="tip-area"><span>温馨提示：</span>最多可添加34个区域组</div>
            <el-table :data="list" border v-loading="tableLoading" style="margin: 15px 0;">
                <el-table-column label="区域组名称" prop="name"></el-table-column>
                <el-table-column label="代理区域" prop="list">
                    <template slot-scope="scope">
                        <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in scope.row.detail" :key="key.id">
                            {{value.name}}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="省代理分红比例" prop="province_rate">
                    <template slot-scope="scope">
                        {{scope.row.province_rate}}%
                    </template>
                </el-table-column>
                <el-table-column label="市代理分红比例" prop="city_rate">
                    <template slot-scope="scope">
                        {{scope.row.city_rate}}%
                    </template>
                </el-table-column>
                <el-table-column label="区/县代理分红比例" prop="district_rate">
                    <template slot-scope="scope">
                        {{scope.row.district_rate}}%
                    </template>
                </el-table-column>
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
    <el-dialog :title="addList.id > 0 ? '编辑区域组': '新增区域组'" :visible.sync="toChange" width="45%" :before-close="closeRegion">
        <el-dialog width="20%" title="选择区域" :visible.sync="innerVisible" append-to-body>
            <el-card style="max-height: 400px;overflow-y: auto;" shadow="never">
                <template v-for="(item, index) in province">
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
            <div slot="footer" class="dialog-footer" flex="main:justify cross:center">
                <el-checkbox v-model="checkAll" @change="handleCheckAllChange">
                    全选
                </el-checkbox>
                <el-button type="primary" @click="changeProvince">确认选择</el-button>
            </div>
        </el-dialog>
        <el-form :model="addList" :rules="addRules" size="small" ref="addForm" label-width="150px">
            <el-form-item label="区域组名称" prop="name">
                <el-input maxlength="6" type="text" placeholder="请输入区域组名称" style="width: 85%;" size="small" v-model="addList.name" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="选择区域" class="required-icon" prop="detail">
                <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in addList.detail" :key="key.id">
                    {{value.name}}
                </el-tag>
                <el-button @click="innerVisible=true">选择</el-button>
            </el-form-item>
            <el-form-item style="margin-bottom: 8px" class="required-icon" label="分红比例" prop="bonus_rate">
                <el-input type="text" style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.province_rate" autocomplete="off">
                    <template slot="prepend">省代理</template>
                    <template slot="append">%</template>
                </el-input>
                <el-input type="text" style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.city_rate" autocomplete="off">
                    <template slot="prepend">市代理</template>
                    <template slot="append">%</template>
                </el-input>
                <el-input type="text" style="width: 85%;" size="small" v-model="addList.district_rate" autocomplete="off">
                    <template slot="prepend">区/县代理</template>
                    <template slot="append">%</template>
                </el-input>
            </el-form-item>
            <el-form-item label="成为代理条件" prop="become_type">
                <el-radio-group @change="chooseUpType" v-model="addList.become_type">
                    <el-radio style="height: 32px;line-height: 32px;" :label="1">下线总人数
                        <el-tooltip effect="dark" content="下线总人数=下线分销商数+下线非分销商数"
                                placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </el-radio>
                    <el-radio style="height: 32px;line-height: 32px;" :label="4">分销订单总数</el-radio>
                    <el-radio style="height: 32px;line-height: 32px;" :label="5">分销订单总金额</el-radio>
                    <el-radio style="height: 32px;line-height: 32px;" :label="2">累计佣金总额
                        <el-tooltip effect="dark" content="累计佣金总额=可提现佣金总额+已提现佣金总额"
                                placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </el-radio>
                    <el-radio style="height: 32px;line-height: 32px;" :label="3">已提现佣金总额</el-radio>
                    <el-radio style="height: 32px;line-height: 32px;" :label="6">消费金额</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item style="margin-bottom: 8px" class="required-icon" :label="label" prop="condition" >
                <el-input v-if="addList.become_type == 1 || addList.become_type == 4" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.province_condition" autocomplete="off">
                    <template slot="prepend">省代理满</template>
                    <template slot="append" v-if="addList.become_type == 1">人</template>
                    <template slot="append" v-else-if="addList.become_type == 4">单</template>
                </el-input>
                <el-input v-else style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.province_condition" autocomplete="off">
                    <template slot="prepend">省代理满</template>
                    <template slot="append">元</template>
                </el-input>
                <el-input v-if="addList.become_type == 1 || addList.become_type == 4" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.city_condition" autocomplete="off">
                    <template slot="prepend">市代理满</template>
                    <template slot="append" v-if="addList.become_type == 1">人</template>
                    <template slot="append" v-else-if="addList.become_type == 4">单</template>
                </el-input>
                <el-input v-else style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.city_condition" autocomplete="off">
                    <template slot="prepend">市代理满</template>
                    <template slot="append">元</template>
                </el-input>
                <el-input v-if="addList.become_type == 1 || addList.become_type == 4" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.district_condition" autocomplete="off">
                    <template slot="prepend">区/县代理满</template>
                    <template slot="append" v-if="addList.become_type == 1">人</template>
                    <template slot="append" v-else-if="addList.become_type == 4">单</template>
                </el-input>
                <el-input v-else style="width: 85%;margin-bottom: 5px" size="small" v-model="addList.district_condition" autocomplete="off">
                    <template slot="prepend">区/县代理满</template>
                    <template slot="append">元</template>
                </el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="closeRegion">取消</el-button>
            <el-button size="small" type="primary" @click="changeSubmit('addForm')" :loading="contentBtnLoading">保存</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validateRate = (rule, value, callback) => {
                if(this.addList.province_rate != null && this.addList.city_rate != null && this.addList.district_rate != null) {
                    if (!this.addList.province_rate && this.addList.province_rate != 0) {
                        callback(new Error('请输入省代理分红比例'));
                    } else if(!this.addList.city_rate && this.addList.city_rate != 0){
                        callback(new Error('请输入市代理分红比例'));
                    } else if(!this.addList.district_rate && this.addList.district_rate != 0){
                        callback(new Error('请输入区/县代理分红比例'));
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            };
            var validateCondition = (rule, value, callback) => {
                if(this.addList.province_condition != null && this.addList.city_condition != null && this.addList.district_condition != null) {
                    if (!this.addList.province_condition && this.addList.province_condition != 0) {
                        callback(new Error('请输入成为省代理的条件'));
                    } else if(!this.addList.city_condition && this.addList.city_condition != 0){
                        callback(new Error('请输入成为市代理的条件'));
                    } else if(!this.addList.district_condition && this.addList.district_condition != 0){
                        callback(new Error('请输入成为区/县代理的条件'));
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            };
            return {
                loading: false,
                tableLoading: false,
                contentBtnLoading: false,
                toChange: false,
                innerVisible: false,
                checkAll: false,
                addList: {
                    become_type: 1,
                    detail: [],
                    province_rate: null,
                    city_rate: null,
                    district_rate: null,
                },
                provinces: [],
                pagination: {},
                label: '下线总人数',
                addRules: {
                    name: [
                        { required: true, message: '请输入区域组名称', trigger: 'change' }
                    ],
                    bonus_rate: [
                        { validator: validateRate, trigger: 'blur'}
                    ],
                    become_type: [
                        { required: true, validator: '请选择升级条件',type: 'number', trigger: 'change' }
                    ],
                    condition: [
                        { validator: validateCondition}
                    ],
                },
                province: [],
                chooseProvince: [],
                list: []
            }
        },
        created() {
            this.loading = true;
            this.getList();
        },
        methods: {
            handleCheckAllChange(e) {
                let that = this;
                that.chooseProvince = [];
                for(let item of that.province) {
                    item.checked = false
                    if(!item.unchecked && e) {
                        item.checked = true
                        item.province_id = item.id
                        that.chooseProvince.push(item)
                    }
                }
            },
            // 编辑级别
            editLevel(row) {
                let that = this;
                that.addList = JSON.parse(JSON.stringify(row));
                for(let j in that.province) {
                    for(let i in that.provinces) {
                        if(that.provinces[i].province_id == that.province[j].id) {
                            that.province[j].unchecked = true
                        }
                    }
                }
                that.chooseProvince = [];
                for(let j in that.province) {
                    that.province[j].checked = false;
                    for(let i in row.detail) {
                        if(row.detail[i].province_id == that.province[j].id) {
                            that.province[j].checked = true;
                            that.province[j].unchecked = false;
                            that.chooseProvince.push(row.detail[i])
                        }
                    }
                }
                if(that.provinces.length == 34) {
                    that.checkAll = true;
                }else {
                    that.checkAll = false;
                }
                that.chooseUpType(that.addList.become_type);
                that.toChange = true;
            },
            // 提交修改
            changeSubmit(formName) {
                let that = this;
                that.$refs[formName].validate((valid) => {
                    if (valid) {
                        that.contentBtnLoading = true;
                        let para = that.addList;
                        para.area_ids = [];
                        console.log(para.detail)
                        for(let i in para.detail) {
                            para.area_ids.push(para.detail[i].province_id)
                        }
                        request({
                            params: {
                                r: 'plugin/region/mall/area/edit'
                            },
                            data: para,
                            method: 'post',
                        }).then(e => {
                            that.contentBtnLoading = false;
                            if (e.data.code == 0) {
                                that.toChange = false;
                                that.addList = {
                                    name: '',
                                    become_type: 1,
                                    province_rate: null,
                                    city_rate: null,
                                    district_rate: null,
                                    province_condition: '',
                                    city_condition: '',
                                    district_condition: '',
                                    detail: []
                                };
                                that.loading = true;
                                that.getList();
                                that.chooseProvince = [];
                                for(let i in that.province) {
                                    that.province[i].checked = false;
                                }
                                that.$nextTick(()=>{
                                   that.$refs[formName].clearValidate();
                                })
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.contentBtnLoading = false;
                        });
                    }
                })
            },
            // 修改省份
            changeProvince() {
                this.addList.detail = JSON.parse(JSON.stringify(this.chooseProvince));
                this.innerVisible = false;
            },
            // 选中的省份
            pickerChange(e) {
                let that = this;
                if(e.checked) {
                    e.province_id = e.id;
                    that.chooseProvince.push(e);
                }else {
                    for(let i in that.chooseProvince) {
                        if(e.id == that.chooseProvince[i].province_id) {
                            that.chooseProvince.splice(i,1)
                        }
                    }
                }
                that.$forceUpdate();
            },
            // 获取省份
            getPlace() {
                let self = this;
                request({
                    params: {
                        r: 'district/index',
                        level: 1
                    },
                    method: 'get'
                }).then(function (e) {
                    if (e.data.code == 0) {
                        self.province = e.data.data.district;
                        if(self.provinces.length > 0) {
                            for(let i in self.provinces) {
                                for(let j in self.province) {
                                    if(self.province[j].name == '其他') {
                                        self.province.splice(j,1)
                                    }
                                    if(self.provinces[i].province_id == self.province[j].id) {
                                        self.province[j].unchecked = true
                                    }
                                }
                            }
                        }else {
                            for(let j in self.province) {
                                if(self.province[j].name == '其他') {
                                    self.province.splice(j,1)
                                }
                            }
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                })
            },
            // 切换显示的升级条件
            chooseUpType(e) {
                switch(e) {
                    case 1:
                        this.label = '下线总人数'
                        this.addList.province_condition = this.addList.province_condition.toString();
                        this.addList.city_condition = this.addList.city_condition.toString();
                        this.addList.district_condition = this.addList.district_condition.toString();
                        if(this.addList.province_condition && this.addList.province_condition.indexOf('.') > -1) {
                            this.addList.province_condition = this.addList.province_condition.slice(0,this.addList.province_condition.indexOf('.'))
                        }
                        if(this.addList.city_condition && this.addList.city_condition.indexOf('.') > -1) {
                            this.addList.city_condition = this.addList.city_condition.slice(0,this.addList.city_condition.indexOf('.'))
                        }
                        if(this.addList.district_condition && this.addList.district_condition.indexOf('.') > -1) {
                            this.addList.district_condition = this.addList.district_condition.slice(0,this.addList.district_condition.indexOf('.'))
                        }
                        break;
                    case 4:
                        this.label = '分销订单总数'
                        this.addList.province_condition = this.addList.province_condition.toString();
                        this.addList.city_condition = this.addList.city_condition.toString();
                        this.addList.district_condition = this.addList.district_condition.toString();
                        if(this.addList.province_condition && this.addList.province_condition.indexOf('.') > -1) {
                            this.addList.province_condition = this.addList.province_condition.slice(0,this.addList.province_condition.indexOf('.'))
                        }
                        if(this.addList.city_condition && this.addList.city_condition.indexOf('.') > -1) {
                            this.addList.city_condition = this.addList.city_condition.slice(0,this.addList.city_condition.indexOf('.'))
                        }
                        if(this.addList.district_condition && this.addList.district_condition.indexOf('.') > -1) {
                            this.addList.district_condition = this.addList.district_condition.slice(0,this.addList.district_condition.indexOf('.'))
                        }
                        break;
                    case 5:
                        this.label = '分销订单总金额'
                        break;
                    case 2:
                        this.label = '累计佣金总额'
                        break;
                    case 3:
                        this.label = '已提现佣金总额'
                        break;
                    case 6:
                        this.label = '消费金额'
                        break;
                }
            },
            closeRegion() {
                let that = this;
                that.toChange = false;
                that.chooseProvince = [];
                setTimeout(function(){
                    that.addList = {
                        name: '',
                        become_type: 1,
                        province_rate: null,
                        city_rate: null,
                        district_rate: null,
                        province_condition: '',
                        city_condition: '',
                        district_condition: '',
                        detail: []
                    };
                },100)
            },
            addRegion() {
                let that = this;
                that.toChange = true;
                that.checkAll = false;
                for(let i in that.province) {
                    that.province[i].checked = false;
                    that.province[i].unchecked = false
                    for(let j in that.provinces) {
                        if(that.provinces[j].province_id == that.province[i].id) {
                            that.province[i].unchecked = true
                        }
                    }
                }
                that.$nextTick(()=>{
                   that.$refs['addForm'].clearValidate();
                })
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
                            r: 'plugin/region/mall/area/delete'
                        },
                        data: {
                            id: row.id
                        },
                        method: 'post'
                    }).then(e => {
                        self.tableLoading = false;
                        if (e.data.code === 0) {
                            self.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            self.list.splice(index,1);
                            self.getList();
                        } else {
                            if(e.data.msg.indexOf('有代理') > -1) {
                                self.$confirm(e.data.msg, '提示', {
                                    confirmButtonText: '确定',
                                    cancelButtonText: '取消',
                                    type: 'warning'
                                }).then(() => {
                                    self.tableLoading = false;
                                }).catch(e => {
                                    console.log(e);
                                    self.tableLoading = false;
                                });
                            }else {
                                self.$message.error(e.data.msg);
                            }
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
                        r: 'plugin/region/mall/area/index',
                        page: page? page: 1
                    },
                }).then(e => {
                    this.loading = false;
                    this.tableLoading = false;
                    if (e.data.code == 0) {
                        that.list = e.data.data.list;
                        that.provinces = e.data.data.provinces;
                        that.pagination = e.data.data.pagination;
                        this.getPlace();
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
        }
    });
</script>