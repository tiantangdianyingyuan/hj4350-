<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/28
 * Time: 17:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-attr .box {
        line-height: 64px;
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 0 16px;
    }

    .app-attr .box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }
    .app-attr .el-select .el-input {
        width: 130px;
    }

    .app-attr .input-with-select .el-input-group__prepend {
        background-color: #fff;
    }

    .app-attr .header-require:before {
        content: '*';
        color: #F56C6C;
        margin-right: 2px;
    }

    .app-attr .header-require.long-header {
        padding-left: 2px;
        overflow: visible;
        line-height: 1;
        white-space: normal;
    }
</style>
<template id="app-attr">
    <div class="app-attr">
        <div class="box">
            <el-checkbox v-model="attrBatch" @change="selectClick" :disabled="!cData || cData.length == 0"
                         style="position:absolute">全选
            </el-checkbox>
            <el-form-item label="批量设置" style="margin-bottom:0;padding:18px 0">
                <el-input @keyup.enter.native="batchAttr(selectData)"
                          :type="selectData == 'no' ? 'text' : 'number'" v-model="batch">
                    <el-select v-model="selectData" slot="prepend">
                        <el-option v-for="(item, index) in cList" :value="index" :key="item" v-if="index!='pic_url'"
                                   :label="item">{{item}}
                        </el-option>
                    </el-select>
                    <template slot="append">
                        <el-button @click="batchAttr(selectData)">
                            确定
                        </el-button>
                    </template>
                </el-input>
            </el-form-item>
        </div>

        <el-table ref="multipleTable" :data="cData" border stripe style="width: 100%"
                  @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="50"></el-table-column>
            <el-table-column
                    v-for="(item, index) in attrGroups"
                    :key="item.id"
                    :prop="'attr_list['+index+'].attr_name'"
                    :label="item.attr_group_name">
            </el-table-column>
            <el-table-column v-if="cList"
                             v-for="(item, key, index) in cList"
                             :key="item.id"
                             :property="key"
                             :label="item + (append ? '(' + append + ')' : '')">
                <template slot="header" v-if="requiredArray.indexOf(key) !== -1">
                    <div class="header-require" :class="{'long-header' : item.length > 6}">{{item}}</div>
                </template>
                <template slot-scope="scope">
                    <template v-if="!isLevel">
                        <div flex="box:first" v-if="scope.column.property == 'pic_url'" style="padding: 10px;">
                            <div flex="cross:center" style="margin-right: 10px;position: relative;">
                                <app-attachment :multiple="false" :params="scope.row" :max="1"
                                                v-model="scope.row[scope.column.property]">
                                    <app-gallery :url="scope.row[scope.column.property]"
                                                 width="50px" height="50px"></app-gallery>
                                </app-attachment>
                                <el-button v-if="scope.row[scope.column.property]" style="position: absolute; right: -8px; top: -8px; padding: 4px 4px;"
                                           size="mini" type="danger" icon="el-icon-close" circle
                                           @click="scope.row[scope.column.property] = ''"></el-button>
                            </div>
                        </div>
                        <el-input v-else-if="scope.column.property == 'no'"
                                  v-model="scope.row[scope.column.property]"></el-input>
                        <el-input v-else-if="scope.column.property == 'stock'"
                                  oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                  v-model="scope.row[scope.column.property]"></el-input>
                        <el-input v-else-if="scope.column.property.indexOf('price') > -1 || scope.column.property.indexOf('level')"
                                  type="number" v-model="scope.row[scope.column.property]"></el-input>
                        <el-input v-else oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                  v-model="scope.row[scope.column.property]">
                            <template v-if="append" slot="append">{{append}}</template>
                        </el-input>
                    </template>
                    <template v-else>
                        <el-input :data-id="scope.row[scope.column.label]" type="number"
                                  v-model="scope.row[paramKey][scope.column.property]">
                        </el-input>
                    </template>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>
<script>
    Vue.component('app-attr', {
        template: '#app-attr',
        props: {
            value: Array, // 商品规格信息
            attrGroups: Array, // 商品规格组
            extra: Object, // 额外的数据信息
            list: Object | Array, // 从排列数据信息
            isLevel: Boolean, // 是否是会员
            members: Array, // 会员等级列表
            share: Array, // 分销等级列表
            append: String, // 输入框后缀
            requiredExtra: {
                type: Array,
                default() {
                    return []
                }
            },
            paramKey: {
                type: String,
                default() {
                    return 'member_price';
                }
            }
        },
        data() {
            return {
                requiredArray: [`price`, `stock`].concat(this.requiredExtra),
                attrBatch: false,
                data: {
                    price: '价格',
                    stock: '库存',
                    weight: '重量(克)',
                    no: '货号',
                    //pic_url: '规格图片',
                },
                selectData: '',
                batch: 0,
                selectList: [],
            };
        },
        created() {
            console.log(this.value);
            if (this.isLevel) {
                for (let i in this.value){
                    if (this.value[i][this.paramKey]) {
                        continue;
                    }
                    let members = JSON.parse(JSON.stringify(this.members));
                    let obj = {};
                    members.forEach(function (memberLevelItem, memberLevelIndex) {
                        let key = 'level' + memberLevelItem.level;
                        obj[key] = '';
                    });
                    this.value[i][this.paramKey] = obj;
                }
                console.log(this.value)
            }
            console.log(this.extra)
            if(this.extra && this.extra.supply_price == '团长供货价(元)') {
                this.data.price = '买家购买价(元)'
            }
        },
        watch: {
            'selectList': function() {
                const self = this;
                let sign = 0;
                this.value.forEach(function (item, index) {
                    self.selectList.map((item1) => {
                        if (JSON.stringify(item1.attr_list) === JSON.stringify(item.attr_list)) {
                            sign++;
                        }
                    });
                });
                self.attrBatch = self.value.length === sign;
            }
        },
        computed: {
            cList() {
                // TODO 分销数据暂时
                if (this.share) {
                    let share = JSON.parse(JSON.stringify(this.share));
                    let obj = {};
                    for (let i = 0; i < share.length; i++) {
                        obj[share[i].value] = share[i].label;
                    }
                    return obj;
                }
                // TODO 会员数据暂时
                if (this.isLevel) {
                    let members = JSON.parse(JSON.stringify(this.members));
                    let obj = {};
                    for (let i = 0; i < members.length; i++) {
                        obj['level' + members[i].level] = members[i].name
                    }
                    return obj;
                } else {
                    if (this.extra) {
                        return Object.assign(this.data, JSON.parse(JSON.stringify(this.extra)));
                    } else if (this.list) {
                        return JSON.parse(JSON.stringify(this.list))
                    } else {
                        return this.data;
                    }
                }
            },
            cData() {
                if (this.attrGroups && this.attrGroups.length > 0 && this.attrGroups[0].attr_list.length === 0) {
                    return [];
                } else {
                    return this.value;
                }
            }
        },
        methods: {
            selectClick() {
                this.$refs.multipleTable.toggleAllSelection();
            },
            handleSelectionChange(data) {
                this.selectList = data;
            },
            batchAttr(param) {
                if (!param) {
                    this.$message.warning('请选择批量设置');
                    return;
                }
                if (!this.selectList || this.selectList.length === 0) {
                    this.$message.warning('请勾选商品规格');
                    return;
                }
                let self = this;
                let batch = self.batch;
                this.value.forEach((item, index) => {
                    let sign = false;
                    self.selectList.map((item1) => {
                        if (JSON.stringify(item1.attr_list) === JSON.stringify(item.attr_list)) {
                            sign = true;
                        }
                    });
                    if (sign) {
                        // 批量设置会员价
                        // 判断字符串是否出现过，并返回位置
                        if (param.indexOf('level') !== -1) {
                            item[this.paramKey][param] = batch;
                        } else {
                            item[param] = batch;
                        }
                    }
                });
            }
        }
    });
</script>
