<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/20
 * Time: 11:32
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-goods-share .box {
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 16px;
    }

    .app-goods-share .box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }

    .app-goods-share .el-select .el-input {
        width: 130px;
    }

    .app-goods-share .detail {
        width: 100%;
    }

    .app-goods-share .detail .el-input-group__append {
        padding: 0 10px;
    }

    .app-goods-share input::-webkit-outer-spin-button,
    .app-goods-share input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .app-goods-share input[type="number"] {
        -moz-appearance: textfield;
    }

    .app-goods-share .el-table .cell {
        text-align: center;
    }

    .app-goods-share .el-table thead.is-group th {
        background: #ffffff;
    }
</style>
<template id="app-goods-share" v-cloak>
    <div v-loading="loading" class="app-goods-share">
        <el-form-item :label="pintuan_sign ? '' : '分销佣金'">

            <template v-if="shareLevel.length == 0 && loading === false">
                <el-button type="danger" @click="$navigate({r: 'mall/share/basic'})">
                    请先开启商城分销功能
                </el-button>
            </template>

            <template v-else-if="loading === false && shareLevel.length> 0">
                <div class="box">
                    <div style="display: inline-block">
                        <el-tag type="danger" v-if="sign === 'pintuan'">
                            {{pintuan_sign}}
                        </el-tag>
                    </div>
                    <label style="margin-bottom:0;padding:18px 10px;">批量设置</label>
                    <el-select v-model="selectLevel" slot="prepend" placeholder="请选择等级"
                               v-if="attr_setting_type == 1">
                        <el-option v-for="(item, index) in ruleForm.shareLevelList"
                                   :value="index"
                                   :key="item.id"
                                   :label="item.name">{{item.name}}
                        </el-option>
                    </el-select>
                    <el-select v-model="selectData" slot="prepend" placeholder="请选择层级">
                        <el-option v-for="(item, index) in shareLevel" :value="item.value"
                                   :key="item.id"
                                   :label="item.label">{{item.label}}
                        </el-option>
                    </el-select>
                    <el-input @keyup.enter.native="enter" type="number" style="width: 150px;"
                              oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                              v-model="batchShareLevel">
                        <span slot="append">{{share_type == 1 ? '%' : '元'}}</span>
                    </el-input>
                    <el-button type="primary" size="small" @click="batchAttr">设置</el-button>
                </div>
                <!--普通分销佣金设置 -->
                <template v-if="attr_setting_type == 0 || use_attr == 0">
                    <el-table ref="normal" :data="ruleForm.shareLevelList" border stripe style="width: 90%;"
                              @selection-change="handleSelectionChange">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column width="100" label="等级名称" prop="name"></el-table-column>
                        <el-table-column :label="item.label" :prop="item.value" :property="item.value"
                                         v-for="(item, index) in shareLevel" :key="index">
                            <template slot-scope="scope">
                                <el-input type="number" v-model="scope.row[scope.column.property]">
                                    <span slot="append">{{share_type == 1 ? '%' : '元'}}</span>
                                </el-input>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>
                <template v-else>
                    <!-- 详细分销佣金设置 -->
                    <template v-if="ruleForm.attr.length > 0">
                        <el-table ref="detail" :data="ruleForm.attr" border class="detail"
                                  @selection-change="handleSelectionChange">
                            <el-table-column type="selection" width="55"></el-table-column>
                            <el-table-column width="100" v-for="(item, index) in attrGroups" :key="item.id"
                                             :label="item.attr_group_name"
                                             :prop="'attr_list['+index+'].attr_name'">
                            </el-table-column>
                            <el-table-column v-for="(item, index) in ruleForm.shareLevelList" :key="item.id"
                                             :label="item.name" min-width="300px">
                                <el-table-column :label="value.label" type="index" :index="index"
                                                 :prop="value.value"
                                                 v-for="(value, key) in shareLevel" :key="key" width="100">
                                    <template slot-scope="scope">
                                        <el-input type="number"
                                                  v-model="scope.row.shareLevelList[scope.column.index][scope.column.property]">
                                        </el-input>
                                    </template>
                                </el-table-column>
                            </el-table-column>
                        </el-table>
                    </template>
                    <!-- 默认规格 分销佣金-->
                    <template v-else>
                        <el-tag style="margin-top: 10px;" type="danger">如需设置多规格分销价, 请先添加商品规格</el-tag>
                    </template>
                </template>
            </template>
        </el-form-item>
    </div>
</template>
<script>
    Vue.component('app-goods-share', {
        template: '#app-goods-share',
        props: {
            // is_mch: Number,
            value: Object,
            attrGroups: Array,
            attr_setting_type: Number,
            share_type: Number,
            use_attr: Number,
            sign: String,
            pintuan_sign: String
        },
        data() {
            return {
                shareLevel: [],
                loading: false,
                selectList: [],
                batchShareLevel: 0,
                selectData: '',
                selectLevel: '',
            };
        },
        computed: {
            ruleForm() {
                return this.value;
            }
        },
        mounted() {
            this.loadData();
        },
        methods: {
            // 获取分销设置
            loadData() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'mall/share/goods-share-config'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.loading = false;
                    if (e.data.code === 0) {
                        self.shareLevel = e.data.data.shareArray;
                        let shareLevelList = e.data.data.shareLevelList;
                        if (self.use_attr == 1) {
                            self.ruleForm.attr.forEach((attr, index) => {
                                attr.shareLevelList = this.setShareLevel(shareLevelList, attr.shareLevelList);
                            });
                        }
                        console.log(shareLevelList)
                        self.ruleForm.shareLevelList = this.setShareLevel(shareLevelList, self.ruleForm.shareLevelList);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            setShareLevel(shareLevelList, list) {
                let newShareLevelList = [];
                shareLevelList.forEach((item) => {
                    let newItem = {
                        level: item.level,
                        name: item.name,
                        share_commission_first: 0,
                        share_commission_second: 0,
                        share_commission_third: 0,
                    };
                    for (let i in list) {
                        if (list[i].level == item.level) {
                            newItem.share_commission_first = list[i].share_commission_first;
                            newItem.share_commission_second = list[i].share_commission_second;
                            newItem.share_commission_third = list[i].share_commission_third;
                        }
                    }
                    newShareLevelList.push(newItem);
                });
                return JSON.parse(JSON.stringify(newShareLevelList));
            },
            handleSelectionChange(data) {
                this.selectList = data;
            },
            enter() {
                console.log('enter');
            },
            batchAttr() {
                if (this.attr_setting_type == 0) {
                    if (!this.selectList || this.selectList.length === 0) {
                        this.$message.warning('请勾选分销商等级');
                        return;
                    }
                    if (this.selectData === '') {
                        this.$message.warning('请选择分销层级');
                        return;
                    }
                    this.ruleForm.shareLevelList.forEach((item, index) => {
                        let sign = false;
                        this.selectList.map((item1) => {
                            if (JSON.stringify(item1) === JSON.stringify(item)) {
                                sign = true;
                            }
                        });
                        if (sign) {
                            item[this.selectData] = this.batchShareLevel
                        }
                    })
                } else {
                    if (!this.selectList || this.selectList.length === 0) {
                        this.$message.warning('请勾选商品规格');
                        return;
                    }
                    if (this.selectLevel === '') {
                        this.$message.warning('请选择分销商等级');
                        return;
                    }
                    if (this.selectData === '') {
                        this.$message.warning('请选择分销层级');
                        return;
                    }
                    this.ruleForm.attr.forEach((item, index) => {
                        let sign = false;
                        this.selectList.map((item1) => {
                            if (JSON.stringify(item1.attr_list) === JSON.stringify(item.attr_list)) {
                                sign = true;
                            }
                        });
                        if (sign) {
                            item.shareLevelList[this.selectLevel][this.selectData] = this.batchShareLevel
                        }
                    })
                }
            }
        }
    });
</script>
