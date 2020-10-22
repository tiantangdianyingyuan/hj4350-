<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-goods');
?>
<style>
    .card-body {
        padding: 20px;
        background-color: #fff;
    }

    .mt-24 {
        margin-bottom: 24px;
    }

    .el-form-item__label {
        padding: 0 20px 0 0;
        width: 180px;
    }

    .button > .el-button {
        margin-left: 150px;
    }

    .jieti .el-form-item__content {
        margin: 0 !important;
    }

    .el-scrollbar .el-scrollbar__wrap .el-scrollbar__view {
        white-space: nowrap;
        padding-top: 2px;
    }
</style>
<div id="app" v-cloak>
    <el-card body-style="background-color: #f3f3f3;padding: 10px 0 0;min-width: 900px;">
        <div slot="header" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item @click.native="returnBack">
                    <a style="color: #409EFF">商品管理</a></el-breadcrumb-item>
                <el-breadcrumb-item>{{edit ? '修改商品' : '添加商品'}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods
                sign="advance"
                ref="appGoods"
                sign="advance"
                :is_member="1"
                :is_attr="1"
                :is_show="0"
                :is_info="0"
                :is_detail="0"
                :form="form"
                :is_cats="0"
                :rule="rules"
                :no_price="0"
                :is_display_setting="0"
                url="plugin/advance/mall/goods/edit"
                get_goods_url="plugin/advance/mall/goods/edit"
                referrer="plugin/advance/mall/goods/index"
                @change="changeGoods"
                :preview-info="previewInfo"
                @handle-preview="handlePreview"
                @goods-success="childrenGoods">
            <!-- 售价 -->
            <template slot="before_price">
                <el-form-item label="售价" prop="price" >
                    <el-input type="number"
                              oninput="this.value = this.value.replace(/[^0-9\.]/, '');" :min="0"
                              v-model="form.price">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
            </template>

            <template slot="member_route_setting">
                 <span class="red">注：必须在“
                    <el-button type="text" @click="$navigate({r: 'plugin/advance/mall/setting/index'}, true)">预售设置=>基本设置=>优惠叠加设置</el-button>
                    ”中开启，才能使用
                </span>
            </template>

            <template slot="before_detail">
                <el-card shadow="never" style="margin-bottom: 24px">
                    <div slot="header">预售设置</div>
                    <el-col :span="24">
                        <el-form-item label="定金金额">
                            <div flex="dir:left">
                                <el-row span="12">
                                    <el-input type="number" placeholder="请输入定金金额"
                                              :disabled="ruleForm.use_attr == 1"
                                              v-model="form.deposit">
                                    </el-input>
                                </el-row>
                            </div>
                            <div style="font-size: 12px;color: #909399;">
                                注：定金金额＜任何设置的售价或者会员价，该价格为不开启规格时的定金金额
                            </div>
                        </el-form-item>
                        <el-form-item label="定金膨胀金">
                            <div flex="dir:left">
                                <el-row span="12">
                                    <el-input type="number" placeholder="请输入定金膨胀金"
                                              :disabled="ruleForm.use_attr == 1"
                                              v-model="form.swell_deposit">
                                    </el-input>
                                </el-row>
                            </div>
                            <div style="font-size: 12px;color: #909399;">
                                注：定金金额≤定金膨胀金＜任何设置的售价或者会员价，该价格为不开启规格时的定金膨胀金
                            </div>
                        </el-form-item>
                        <el-form-item label="定金与定金膨胀金" v-if="ruleForm.use_attr == 1" :rules="[
                                          { type: 'number', message: '定金与定金膨胀金必须为数字值'}
                                        ]">
                            <div flex="dir:left">
                                <div style="width: 70px;margin-right: 20px;color: #606266;">
                                    批量设置
                                </div>
                                <el-row style="display: flex">
                                    <el-input placeholder="请输入内容" v-model="input3" class="input-with-select">
                                        <el-select v-model="select" slot="prepend" style="width: 120px;"
                                                   placeholder="请选择">
                                            <el-option label="定金" value="1"></el-option>
                                            <el-option label="定金膨胀金" value="2"></el-option>
                                        </el-select>
                                        <template slot="append">
                                            <el-button @click="batchSetting">确定</el-button>
                                        </template>
                                    </el-input>
                                </el-row>
                            </div>
                            <div>
                                <el-table
                                        :data="ruleForm.attr"
                                        style="width: 100%">
                                    <el-table-column
                                            prop="attr_name"
                                            v-for="(item, ind) in ruleForm.attr_groups"
                                            :label="item.attr_group_name"
                                    >
                                        <template slot-scope="scope">
                                            <span>{{scope.row.attr_list[ind].attr_name}}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                            label="定金"
                                            prop="deposit"
                                            width="150">
                                        <template slot-scope="scope">
                                            <el-input type="number" v-model="scope.row.deposit"></el-input>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                            prop="swell_deposit"
                                            width="150"
                                            label="定金膨胀金">
                                        <template slot-scope="scope">
                                            <el-input type="number" v-model="scope.row.swell_deposit"></el-input>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                        </el-form-item>
                        <el-form-item label="定金支付时间">
                            <el-date-picker
                                    v-model="form.open_date"
                                    type="datetimerange"
                                    align="left"
                                    unlink-panels
                                    :disabled="time_disabled"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期"
                                    :picker-options="pickerOptions">
                            </el-date-picker>
                            <div style="font-size: 12px;color: #909399;color: #ff4544;">
                                注：定金支付时间一旦保存无法修改，请认真填写！
                            </div>
                        </el-form-item>
                        <el-form-item label="尾款支付时间"
                                      :rules="[
                                          { required: true, message: '尾款支付时间不能为空'},
                                          { type: 'number', message: '尾款支付时间必须为数字值'}
                                        ]">
                            <div flex="dir:left">
                                <el-row span="24">
                                    <el-input :disabled="isBuyLimit" type="number" placeholder="请输入天数"
                                              v-model.number="form.pay_limit">
                                        <template slot="prepend">尾款支付时间结束</template>
                                        <template slot="append">天内</template>
                                    </el-input>
                                </el-row>
                                <el-checkbox style="margin-left: 5px;" @change="itemChecked(1)" v-model="isBuyLimit">无限制
                                </el-checkbox>
                            </div>
                        </el-form-item>
                    </el-col>
                </el-card>
            </template>

            <template slot="after_detail">
                <el-card shadow="never" style="margin-bottom: 24px">
                    <div slot="header">阶梯设置</div>
                    <el-row :span="24">

                        <el-table
                                style="margin-bottom: 15px;"
                                v-if="form.ladder_rules.length > 0"
                                :data="form.ladder_rules"
                                border
                                style="width: 100%">
                            <el-table-column
                                    label="达标件数"
                                    sortable
                                    width="546">
                                <template slot-scope="scope">
                                    <el-input type="age" v-model.number="scope.row.num" placeholder="请输入件数"
                                              @change="change_ladder"></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    label="折扣（0.1~10）"
                                    width="520">
                                <template slot-scope="scope">
                                    <el-input type="number" v-model.number="scope.row.discount" min="0.1" max="10"
                                              placeholder="请输入折扣" @input="e => scope.row.discount = inputMe(e)"
                                              @change="(e) => {change_ladder_dis(e)}"></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    label="操作"
                                    width="520">
                                <template slot-scope="scope">
                                    <el-button size="small" @click="destroyRules(scope.$index)" type="text" circle>
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top ">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-button type="text" @click="addRules">
                            <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                            <span style="color: #353535;font-size: 14px">添加新区间</span>
                        </el-button>
                    </el-row>
                </el-card>
            </template>

            <template slot="preview">
                <div v-if="previewData" flex="dir:top">
                    <el-image style="height:44px"
                              src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/555.png"></el-image>
                    <div class="goods" style="padding-top:12px">
                        <div class="goods-name">{{previewData.name}}</div>
                        <div flex="dir:left" style="font-size:14px">
                            <div flex="dir:top" style="font-size: 10px;color:#999999">
                                <div flex="dir:left" style="color:#ff4544">
                                    <div style="font-size:26px;color:#ff4544;" :class="previewData.t_type">{{previewData.actualPrice}}</div>
                                    <div style="font-size:14px;margin-top: auto;margin-bottom:3px;margin-left:8px;">
                                        预售价
                                    </div>
                                </div>
                                <div style="font-size:12px;text-decoration: line-through;padding-top:8px">
                                    ￥{{previewData.original_price}}
                                </div>
                                <div style="color:#ff4544;padding-top:8px;font-size:13px;">{{previewData.title_a}}</div>
                                <div style="padding-top:6px;font-size:11px">{{previewData.title_b}}</div>
                            </div>
                            <div class="share" flex="dir:top main:center cross:center">
                                <el-image src="statics/img/mall/goods/icon-share.png"></el-image>
                                <div>分享</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="form.ladder_rules && form.ladder_rules.length"
                         style="margin-top:12px;padding:16px;12px;color:#FFFFFF;background-image: linear-gradient(to right, #ff8c40 , #ff6d40);">
                        <div flex="dir:left cross:center">
                            <div flex="dir:top">
                                <div style="font-size:14px;">再抢购0件</div>
                                <div flex="dir:left" style="margin-top:5px">
                                    <div>可享</div>
                                    <div style="font-size:20px;line-height:1;margin-top:-2px">
                                        {{form.ladder_rules[0].discount}}
                                    </div>
                                    <div>折优惠</div>
                                </div>
                            </div>
                            <div style="margin-left:auto">
                                <div style="padding:5px 12px;border-radius:28px;border:1px solid #ffffff">邀请好友购买</div>
                            </div>
                        </div>

                        <el-scrollbar style="width:100%;margin-top:16px;height:25px">
                            <div id="progress"
                                 style="height:5px;border-radius:5px;background:rgba(0, 0, 0, 0.2);width:auto"></div>
                            <div flex="dir:left" v-if="form.ladder_rules.length === 1">
                                <div>0</div>
                                <div style="margin-left:auto">
                                    满{{form.ladder_rules[0].num}}件，享{{form.ladder_rules[0].discount}}折
                                </div>
                            </div>
                            <div v-else flex="dir:left">
                                <div>0</div>
                                <div id="plimit" flex="dir:left">
                                    <div style="position:relative;margin-left:120px"
                                         v-for="(item,index) in form.ladder_rules"
                                         :key="index">
                                        满{{item.num}}件，享{{item.discount * 10}}折
                                        <div style="position: absolute;height:10px;width:10px; border-radius: 50%;background:rgba(0, 0, 0, 0.2);top: -7px;left: calc(50% - 5px)"></div>
                                    </div>
                                </div>
                            </div>
                        </el-scrollbar>
                    </div>
                </div>
            </template>
        </app-goods>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                previewData: null,
                previewInfo: {
                    is_head: false,
                },

                input3: '',
                select: '1',
                form: {
                    pay_limit: 0,
                    ladder_rules: [],
                    open_date: [],
                    pic_url: [],
                    advanceGoods: {
                        deposit: '0',
                        swell_deposit: '0',
                        pay_limit: -1,
                    },
                    deposit: 0,
                    swell_deposit: 0,
                    price: ''
                },
                use_attr: 0,
                // 默认操作
                isBuyLimit: false,
                pickerOptions: {
                    disabledDate(time) {
                        let before = (new Date()).getTime();
                        let halfYear = 365 / 2 * 24 * 3600 * 1000;
                        let pastResult = before - halfYear;
                        let after = before + halfYear;
                        return time.getTime() < pastResult || time.getTime() > after;
                    },
                    shortcuts: [{
                        text: '未来一周',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            end.setTime(start.getTime() + 3600 * 1000 * 24 * 6);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '未来半月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            end.setTime(start.getTime() + 3600 * 1000 * 24 * 14);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '未来一个月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            end.setTime(start.getTime() + 3600 * 1000 * 24 * 29);
                            picker.$emit('pick', [start, end]);
                        }
                    }]
                },
                rules: {
                    open_date: [
                        {required: true, message: '请输入预付时间', trigger: 'change',},
                    ],
                    pay_limit: [
                        {required: true, message: '请输入尾款支付天数', trigger: 'change'},
                    ],
                    price: [
                        {required: true, message: '请输入售价', trigger: 'change'},
                    ],
                },
                activeName: 'one',
                ruleForm: {
                    attr: [],
                    attr_groups: []
                },
                is_mch: 0,
                edit: false,
                newForm: {},
                time_disabled: false,
            };
        },
        created() {
            let id = getQuery('id');
            if (id) {
                this.form.id = getQuery('id');
            }
            const index = window.location.search.indexOf('&');
            if (index !== -1) {
                let url = window.location.search.substring(index + 1);
                if (url.split('&')[0].split('=')[0] === 'id') {
                    this.edit = true;
                }
            }
        },
        mounted() {
            this.$nextTick(() => {
                this.ruleForm = this.$refs.appGoods.ruleForm;
            })
        },
        methods: {
            handlePreview(e) {
                const price = Number(e.price);
                const attr = e.attr;
                let arr = [];
                let deposit = []; //定金
                let swell_deposit = []; //定金pz
                attr.map(v => {
                    arr.push(Number(v.price));
                    deposit.push(Number(v.deposit));
                    swell_deposit.push(Number(v.swell_deposit));
                });
                let max = Math.max.apply(null, arr);
                let min = Math.min.apply(null, arr);

                let actualPrice = -1;
                let type = 'text-price';
                if (max > min && min >= 0) {
                    actualPrice = min + '-' + max;
                } else if (max == min && min >= 0) {
                    actualPrice = min;
                } else if (price > 0) {
                    actualPrice = price;
                } else if (price == 0) {
                    actualPrice = '免费';
                    type = '';
                }

                let title_a = '';
                let title_b = '';
                {
                    function func(info) {
                        let max_info = Math.max.apply(null, info);
                        let min_info = Math.min.apply(null, info);
                        if (max_info > max_info) {
                            return '￥' + min_info + '-' + max_info;
                        } else if (isNaN(min_info) || isFinite(min_info)) {
                            return ''
                        } else {
                            return '￥' + min_info;
                        }
                    }

                    title_a = '定金' + func(deposit) + '抵扣' + func(swell_deposit);
                }
                {
                    let date = '无限制';
                    const start = this.form.open_date[1] ? this.form.open_date[1] : '2000-01-01 00:00:00';
                    if (this.form.pay_limit > 0) {
                        let timestamp = parseInt(new Date(start).getTime() / 1000) + parseInt(this.form.pay_limit) * 24 * 60 * 60;

                        function format(time) {
                            return time < 10 ? ('0' + time) : time;
                        }

                        const d = new Date(timestamp * 1000);
                        date = (d.getFullYear()) + "-" +
                            format(d.getMonth() + 1) + "-" +
                            format(d.getDate()) + " " +
                            format(d.getHours()) + ":" +
                            format(d.getMinutes()) + ":" +
                            format(d.getSeconds());
                    }
                    title_b = '支付尾款时间:' + start + '~' + date;
                }
                this.previewData = Object.assign({},e,{
                    actualPrice,
                    t_type:type,
                    title_a,
                    title_b,
                });
                setTimeout(() => {
                    const limit_width = document.getElementById("plimit").offsetWidth;
                    document.getElementById("progress").style.width = parseInt(limit_width + 10) + 'px';
                })
            },

            r(rule, value, callback) {
                value = value.replace(/[^\d.]/g, "");  //清除“数字”和“.”以外的字符
                value = value.replace(/\.{2,}/g, "."); //只保留第一个. 清除多余的
                value = value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
                value = value.replace(/^(\-)*(\d+)\.(\d\d).*$/, '$1$2.$3');//只能输入两个小数
                if (value.indexOf(".") < 0 && value != "") {//以上已经过滤，此处控制的是如果没有小数点，首位不能为类似于 01、02的金额
                    value = parseFloat(value);
                    callback();
                }
            },
            changeGoods(data) {
            },
            // 监听子组件事件
            childrenGoods(e) {
                let self = this;
                self.form.price = e.price;
                if (getQuery('id')) {
                    self.form.open_date = [];
                    self.form.open_date.push(e.advanceGoods.start_prepayment_at, e.advanceGoods.end_prepayment_at);
                    self.form.pay_limit = e.advanceGoods.pay_limit;
                    self.form.ladder_rules = e.ladder_rules;
                    self.form.deposit = e.deposit;
                    // self.form.is_level = e.is_level;
                    self.form.swell_deposit = e.swell_deposit;
                    self.time_disabled = true;
                    self.form.price = e.price;
                    // self.form.attr_groups = e.attr_groups;
                    // self.form.extra = {
                    //     deposit: '定金',
                    //     swell_deposit: '膨胀金',
                    // };
                    if (e.advanceGoods.pay_limit === -1) {
                        this.isBuyLimit = true;
                    }
                    self.use_attr = e.use_attr;
                }
            },

            itemChecked(type) {
                if (type === 1) {
                    Vue.set(this.form, 'pay_limit', this.isBuyLimit ? -1 : 0);
                } else {
                }
            },

            // 添加阶梯规则
            addRules() {
                this.form.ladder_rules = this.form.ladder_rules ? this.form.ladder_rules : [];
                this.form.ladder_rules.push({
                    num: '',
                    discount: '',
                })
            },
            // 删除阶梯
            destroyRules(index) {
                this.form.ladder_rules.splice(index, 1);
            },
            returnBack() {
                this.$historyGo(-1);
            },
            batchSetting() {
                if (this.select === '1') {
                    for (let i = 0; i < this.ruleForm.attr.length; i++) {
                        this.$set(this.ruleForm.attr[i], 'deposit', this.input3);
                    }
                } else {
                    for (let i = 0; i < this.ruleForm.attr.length; i++) {
                        this.$set(this.ruleForm.attr[i], 'swell_deposit', this.input3);
                    }
                }
            },
            change_ladder() {
                let obj = {};
                this.form.ladder_rules = this.form.ladder_rules.reduce((item, next) => {
                    if (!obj[next.num]) {
                        obj[next.num] = true;
                        item.push(next);
                    } else {
                        this.$message({
                            message: '达标件数不可重复设置相同件数',
                            type: 'warning'
                        });
                    }
                    return item;
                }, []);
            },
            change_ladder_dis(v, data) {
                console.log(v);
                console.log(data);
                let compare = function (obj1, obj2) {
                    let val1 = Number(obj1.num);
                    let val2 = Number(obj2.num);
                    if (val1 < val2) {
                        return -1;
                    } else if (val1 > val2) {
                        return 1;
                    } else {
                        return 0;
                    }
                };
                this.form.ladder_rules = this.form.ladder_rules.sort(compare);
            },
            inputMe(v) {
                let value = v;
                value = value.replace(/[^\d.]/g, "");  //清除“数字”和“.”以外的字符
                value = value.replace(/\.{2,}/g, "."); //只保留第一个. 清除多余的
                value = value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
                value = value.replace(/^(\-)*(\d+)\.(\d).*$/, '$1$2.$3');//只能输入两个小数
                if (value.indexOf(".") < 0 && value != "") {//以上已经过滤，此处控制的是如果没有小数点，首位不能为类似于 01、02的金额
                    value = parseFloat(value);
                    if (value > 10) {
                        return 10;
                    }
                    return value;
                }
                if (value > 10) {
                    return 10;
                }
                return value;
            }
        },
        computed: {
            attr: function () {
            }
        }
    });
</script>
