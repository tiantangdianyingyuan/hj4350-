<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('goods/app-attr');
Yii::$app->loadViewComponent('goods/app-goods-share');
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .button-item {
        padding: 9px 25px;
    }
    .app-attr .box .el-form-item .el-form-item__label {
        width: 160px;
        float:left;
        text-align:right;
        padding:0 12px 0 0;
    }
    .app-attr .box .el-form-item .el-form-item__content {
        margin-left: 160px;
    }

    .app-attr .box .el-form-item .el-form-item__content > .el-input{
        width: 50%;
    }
</style>
<div id="app" v-cloak>
    <el-card class="app-goods" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/pintuan/mall/goods/index'})">商品列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>阶梯团</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm"
                     :rules="rule"
                     ref="ruleForm"
                     label-position="top"
                     label-width="160px"
                     size="small">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="拼团设置" name="first">
                        <el-form-item label-width="120px" prop="desc">
                            <el-table
                                    style="margin-bottom: 15px;"
                                    v-if="pintuan.length > 0"
                                    :data="pintuan"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        label="拼团人数"
                                        width="200">
                                    <template slot-scope="scope">
                                        <el-input v-model.number="scope.row.people_num"
                                                  placeholder="请输入拼团人数"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="团长优惠"
                                        width="200">
                                    <template slot-scope="scope">
                                        <el-input type="number" v-model="scope.row.preferential_price"
                                                  placeholder="请输入团长优惠"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="拼团时间"
                                        width="400">
                                    <template slot-scope="scope">
                                        <el-input v-model.number="scope.row.pintuan_time" placeholder="请输入拼团时间">
                                            <template slot="append">小时</template>
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="团长数量"
                                        :render-header="renderHeader"
                                        width="200">
                                    <template slot-scope="scope">
                                        <el-input v-model.number="scope.row.group_num" placeholder="请输入团长数量">
                                            <template slot="append">人</template>
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="操作">
                                    <template slot-scope="scope">
                                        <el-button size="small" @click="destroyPintuan(scope.$index)" circle
                                                   type="text">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-button type="text" @click="addPintuan">
                                <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                                <span style="color: #353535;font-size: 14px">新增阶梯团</span>
                            </el-button>
                        </el-form-item>
                        <el-card v-for="(item,index) in pintuan" style="margin-top: 24px;"
                                 shadow="never">
                            <div slot="header">
                                <el-tag type="danger">{{item.people_num}}人团</el-tag>
                            </div>
                            <el-form-item v-if="attrGroups.length" label="价格与库存">
                                <app-attr :attr-groups="attrGroups" v-model="item.attr"
                                          :list="{pintuan_price: '拼团价', pintuan_stock: '拼团库存'}"></app-attr>
                            </el-form-item>
                        </el-card>
                    </el-tab-pane>
                    <el-tab-pane label="分销价设置" name="second">
                        <el-row>
                            <template v-if="ruleForm.individual_share == 1">
                                <template v-if="pintuan.length > 0">
                                    <el-card v-for="(item, index) in pintuan" :key="index">
                                        <el-tag type="danger" slot="header">
                                            {{item.people_num}}人团
                                        </el-tag>
                                        <app-goods-share v-model="pintuan[index]" :attr-groups="attrGroups"
                                                         :attr_setting_type="ruleForm.attr_setting_type"
                                                         :share_type="ruleForm.share_type"
                                                         :use_attr="ruleForm.use_attr"
                                                         v-if="activeName == 'second'"></app-goods-share>
                                    </el-card>
                                </template>
                                <template v-else>
                                    <el-form-item>
                                        <el-tag type="danger">请先添加阶梯团</el-tag>
                                    </el-form-item>
                                </template>
                            </template>
                            <template v-else>
                                <el-tag style="margin: 15px 0;" type="danger">拼团商品未开启分销功能,请先开启</el-tag>
                            </template>
                        </el-row>
                    </el-tab-pane>
                    <el-tab-pane label="会员价设置" name="third">
                        <template v-if="ruleForm.is_level == 1">
                            <template v-if="ruleForm.is_level_alone == 1">
                                <template v-if="pintuan.length > 0">
                                    <el-card v-for="(item, index) in pintuan">
                                        <el-tag style="float: left;margin-right: 50px" type="danger">
                                            {{item.people_num}}人团
                                        </el-tag>
                                        <template v-if="ruleForm.use_attr == 1 && memberLevel.length > 0">
                                            <!--多规格会员价设置-->
                                            <el-form-item label="会员价设置">
                                                <app-attr :attr-groups="attrGroups" v-model="item.attr"
                                                          :is-level="true" :members="memberLevel"></app-attr>
                                            </el-form-item>
                                        </template>
                                        <!-- 无规格默认会员价 -->
                                        <template v-if="ruleForm.use_attr == 0 && memberLevel.length > 0">
                                            <el-form-item label="默认规格会员价设置">
                                                <el-col :span="12">
                                                    <el-input v-for="mItem in item.defaultMemberPrice"
                                                              :key="mItem.id"
                                                              type="number"
                                                              v-model="mItem.value">
                                                        <span slot="prepend">{{mItem.name}}</span>
                                                        <span slot="append">元</span>
                                                    </el-input>
                                                </el-col>
                                            </el-form-item>
                                            <el-form-item>
                                                <el-tag type="danger">如需设置多规格会员价,请先添加商品规格</el-tag>
                                            </el-form-item>
                                        </template>

                                        <el-form-item v-if="memberLevel.length == 0" label="会员价设置">
                                            <el-button type="danger"
                                                       @click="$navigate({r: 'mall/mall-member/edit'})">
                                                如需设置,请先添加会员
                                            </el-button>
                                        </el-form-item>
                                    </el-card>
                                </template>
                                <template v-else>
                                    <el-form-item v-if="ruleForm.is_level == 1">
                                        <el-tag type="danger">请先添加阶梯团</el-tag>
                                    </el-form-item>
                                </template>
                            </template>
                            <template v-else>
                                <el-tag style="margin: 15px 0;" type="danger">拼团商品享受全局会员折扣</el-tag>
                            </template>
                        </template>
                        <template v-else>
                            <el-tag style="margin: 15px 0;" type="danger">拼团商品未开启会员功能,请先开启</el-tag>
                        </template>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" style="margin-top: 24px" size="small"
                   @click="store('ruleForm')">保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
                ruleForm: {},
                pintuan: [],//阶梯团
                attrGroups: [],
                rule: {},
                btnLoading: false,
                cardLoading: false,
                // 会员等级
                memberLevel: [],
                // 批量设置
                batch: {},
            };
        },
        created() {
            let id = getQuery('id');
            this.getDetail(id);
            this.getMembers();
        },
        computed: {
            defaultMemberPrice() {
                let self = this;
                let defaultMemberPrice = [];
                // 以下数据用于默认规格情况下的 会员价设置
                self.memberLevel.forEach(function (item, index) {
                    let obj = {};
                    obj['id'] = index;
                    obj['name'] = item.name;
                    obj['level'] = parseInt(item.level);

                    let memberPriceValue = 0;
                    if (self.ruleForm.use_attr == 0 && self.ruleForm.attr.length > 0) {
                        let key = 'level' + item.level;
                        let value = self.ruleForm.attr[0]['member_price'][key];
                        memberPriceValue = value ? value : memberPriceValue;
                    }
                    obj['value'] = memberPriceValue;
                    defaultMemberPrice.push(obj);
                });
                return defaultMemberPrice;
            }
        },
        methods: {
            store(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        let goods = {
                            id: getQuery('id'),
                            use_attr: this.ruleForm.use_attr,
                            individual_share: this.ruleForm.individual_share,
                            attr_setting_type: this.ruleForm.attr_setting_type,
                            is_level: this.ruleForm.is_level,
                            is_level_alone: this.ruleForm.is_level_alone,
                            share_type: this.ruleForm.share_type,
                        }
                        request({
                            params: {
                                r: 'plugin/pintuan/mall/goods/pintuan'
                            },
                            method: 'post',
                            data: {
                                goods: goods,
                                form: JSON.stringify(self.pintuan),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/pintuan/mall/goods/index'
                                })
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        self.$message.error('请填写必填参数');
                        return false;
                    }
                });
            },
            getDetail(id) {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/pintuan/mall/goods/pintuan',
                        id: id
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.detail;
                        this.attrGroups = e.data.data.detail.attr_groups;
                        this.pintuan = e.data.data.detail.groups;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.cardLoading = false;
                });
            },
            // 标签页
            handleClick(tab, event) {
                if (tab.name == "third") {
                    this.getMembers();
                }
            },
            // 获取会员列表
            getMembers() {
                let self = this;
                request({
                    params: {
                        r: 'mall/mall-member/all-member'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    if (e.data.code == 0) {
                        self.memberLevel = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 批量设置
            batchAttr(key, index) {
                let self = this;
                if (self.batch[key] && self.batch[key] >= 0) {
                    self.pintuan[index].attr.forEach(function (item) {
                        // 批量设置会员价
                        // 判断字符串是否出现过，并返回位置
                        if (key.indexOf('level') !== -1) {
                            let newKey = key.replace('_' + index, "")
                            console.log(newKey)
                            item['member_price'][newKey] = self.batch[key];
                        }
                        if (key.indexOf('share_commission') !== -1) {
                            let newKey = key.replace(index, "")
                            item[newKey] = self.batch[key]
                        }
                        if (key.indexOf('pintuan_') !== -1) {
                            let newKey = key.replace(index, "")
                            item[newKey] = self.batch[key]
                        }
                    });
                }
            },
            // 添加阶梯团
            addPintuan() {
                let attr = JSON.parse(JSON.stringify(this.ruleForm.attr));
                attr.forEach(function (item, index) {
                    item.pintuan_price = item.price;
                    item.pintuan_stock = item.stock;
                    item.goodsAttr = {
                        id: item.id
                    }
                });

                this.pintuan.push({
                    people_num: 2,
                    preferential_price: 1,
                    pintuan_time: 1,
                    attr: attr,
                    defaultMemberPrice: JSON.parse(JSON.stringify(this.defaultMemberPrice)),
                    shareLevelList: [],
                })
            },
            // 删除阶梯团
            destroyPintuan(index) {
                this.pintuan.splice(index, 1);
            },
            renderHeader(h, {column, $index}){
                return h(
                    "el-popover",
                    {
                        props: {
                            placement: "top",
                            trigger: "hover",
                            popperClass : "popperClassResOut"
                        }
                    },//此对象是定义el-popover的各属性
                    [
                        // h(
                        //     "div",
                        //     [`解释1：即巴拉巴拉爸爸不啦啦啦。`]
                        // ),
                        // h(
                        //     "div",
                        //     [`解释2：即哈哈化歘持续哈哈航爱占计划。`]
                        // ),
                        // h(
                        //     "div",
                        //     [`解释1及解释2的反鸟返很久烦烦烦烦分行恒`]
                        // ),
                        h(
                            "div",
                            [
                               `可发起拼团的次数(包括正在拼团中、拼团完成),`,
                                h('br'),
                                `0代表不限制次数`,
                            ]
                        ),//这个h函数可以替代上方注释的代码
                        h(
                            "span",
                            {
                                slot: "reference"
                            },
                            [
                                column.label,
                                h("i", {
                                    class: "el-icon-info",
                                    style: {
                                        marginLeft: "4px",
                                        cursor: "pointer",
                                    }
                                })
                            ]
                        ),//这个h函数是渲染问号图标和被解释的表头字段文字
                    ]
                )
            }
        }
    });
</script>
