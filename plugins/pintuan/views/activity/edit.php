<?php
/**
 * Created by PhpStorm.
 * User: fjt
 * Date: 2019/12/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-goods');
Yii::$app->loadViewComponent('goods/app-attr');
?>
<style>
    .active-color {
        color: #409eff;
    }
    .el-switch__label {
        color: #c6c6c6;
    }

    #pane-four .el-card>.el-card__body>.el-form-item>.el-form-item__content {
        margin-left: 0 !important;
    }

    .header-require:before {
        content: '*';
        color: #F56C6C;
        margin-right: 2px;
    }
    .robot_time .el-input-group--append{
        width: 130px;
    }
    .robot_text {
        color: #c6c6c6;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/pintuan/mall/activity/index'})">拼团活动</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.id > 0">编辑活动</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新建活动</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods ref="appGoods"
                   :is_info="0"
                   :is_show="0"
                   :is_cats="0"
                   :is_display_setting="0"
                   sign="pintuan"
                   :is_detail="0"
                   :url="url"
                   :form="form"
                   :rule="rule"
                   :is_edit="is_edit"
                   :is_virtual_sales="0"
                   :referrer="referrerUrl"
                   get_goods_url="plugin/pintuan/mall/activity/edit"
                   :preview-info="previewInfo"
                   @handle-preview="handlePreview"
                   @set-attr="setAttr"
                   @goods-success="childrenGoods">

            <template slot="before_info">
                <el-card shadow="never" style="margin-bottom: 24px">
                    <div slot="header">活动设置</div>
                    <el-col :span="24">
                        <el-form-item label="开始时间" prop="start_time">
                            <el-date-picker
                                v-model="form.start_time"
                                type="datetime"
                                :disabled="form.status_cn == '进行中'"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                placeholder="选择开始时间">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label="结束时间">
                            <el-date-picker
                                v-model="form.end_time"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                type="datetime"
                                placeholder="选择结束时间">
                            </el-date-picker>
                        </el-form-item>
                    </el-col>
                </el-card>
            </template>


            <template slot="member_route_setting">
                 <span class="red">注：必须在“
                    <el-button type="text" @click="$navigate({r: 'plugin/pintuan/mall/index'}, true)">拼团设置=>基本设置=>优惠叠加设置</el-button>
                    ”中开启，才能使用
                </span>
            </template>

<!--            <template slot="tab_pane">-->
<!--                <el-tab-pane label="分销价设置" name="second">-->
<!--                    <el-row>-->
<!--                        <template v-if="form.individual_share == 1">-->
<!--                            <template v-if="form.group_list.length > 0">-->
<!--                                <el-card v-for="(item, index) in form.group_list" :key="index">-->
<!--                                    <el-tag type="danger" slot="header">-->
<!--                                        {{item.people_num}}人团-->
<!--                                    </el-tag>-->
<!--                                    <app-goods-share v-model="form.group_list[index]" :attr-groups="attrGroups"-->
<!--                                                     :attr_setting_type="form.attr_setting_type"-->
<!--                                                     :share_type="form.share_type"-->
<!--                                                     :use_attr="form.use_attr"-->
<!--                                                     ></app-goods-share>-->
<!--                                </el-card>-->
<!--                            </template>-->
<!--                            <template v-else>-->
<!--                                <el-form-item>-->
<!--                                    <el-tag type="danger">请先添加阶梯团</el-tag>-->
<!--                                </el-form-item>-->
<!--                            </template>-->
<!--                        </template>-->
<!--                        <template v-else>-->
<!--                            <el-tag style="margin: 15px 0;" type="danger">拼团商品未开启分销功能,请先开启</el-tag>-->
<!--                        </template>-->
<!--                    </el-row>-->
<!--                </el-tab-pane>-->
<!--            </template>-->
            <template slot="before_services">
                <el-form-item label="是否开启虚拟成团" >
                    <el-switch :active-value="1" :inactive-value="0"  v-model="form.is_auto_add_robot" active-text="">
                    </el-switch>
                    <div class="robot_text">开启虚拟成团后，系统将模拟“匿名买家”凑满人数促成成团，商家仅需对真实拼团买家发货</div>
                </el-form-item>
                <el-form-item prop="virtual_sales">
                    <template slot='label'>
                        <span>已团商品数</span>
                        <el-tooltip effect="dark" content="前端展示的销量=实际销量+已团商品数" placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/, '')" min="0" v-model="form.virtual_sales">
                        <template slot="append">{{form.unit}}</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="是否允许单独购买">
                    <el-switch :active-value="1" :inactive-value="0"  v-model="form.is_alone_buy" >
                    </el-switch>
                </el-form-item>
            </template>

            <template slot="before_basic_tab_pane">
                <el-tab-pane label="阶梯团设置" name="four"  class="app-attr">

                    <el-form-item label-width="0px" prop="desc">
                            <div>阶梯团设置</div>
                        <el-table
                                style="margin-bottom: 15px;"
                                v-if="form.group_list.length > 0"
                                :data="form.group_list"
                                border
                                style="width: 100%">
                            <el-table-column
                                    width="200">
                                <template slot="header">
                                    <div class="header-require">拼团人数</div>
                                </template>
                                <template slot-scope="scope">
                                    <el-input v-model="scope.row.people_num"
                                              type="text"
                                              maxlength="5"
                                              oninput="this.value = this.value.replace(/[^0-9]/, '')"
                                              placeholder="请输入拼团人数"></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    label="团长优惠"
                                    width="200">
                                <template slot-scope="scope">
                                        <el-input type="text"
                                                  @input="changeSalary(scope.row,scope.$index)"
                                                  v-model="scope.row.preferential_price"
                                                  placeholder="请输入团长优惠"></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column
                                    width="400">
                                <template slot="header">
                                    <div class="header-require">拼团时间</div>
                                </template>
                                <template slot-scope="scope">
                                    <el-input v-model="scope.row.pintuan_time" maxlength="4" type="text"
                                            oninput="this.value = this.value.replace(/[^0-9]/, '')"
                                            placeholder="请输入拼团时间">
                                        <template slot="append">小时</template>
                                    </el-input>
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="最多开团数量"
                                :render-header="delegation"
                                width="200">
                                <template slot-scope="scope">
                                    <el-input
                                        v-model="scope.row.group_num"
                                        type="text"
                                        maxlength="5"
                                        oninput="this.value = this.value.replace(/[^0-9]/, '')"
                                        placeholder="请输入团长数量">
                                        <template slot="append">个</template>
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

                    <el-card v-for="(item, index) in form.group_list" style="margin-top: 24px;" shadow="never">
                        <div slot="header">
                            <el-tag type="danger">{{item.people_num}}人团</el-tag>
                        </div>
                        <el-form-item v-if="new_attr_groups.length" >
                            <div>价格与库存</div>
                            <app-attr :attr-groups="new_attr_groups" v-model="item.attr"
                                      :list="{price: '拼团价', stock: '拼团库存'}"></app-attr>
                        </el-form-item>
                    </el-card>
                </el-tab-pane>
            </template>

            <template slot="preview">
                <div v-if="previewData" flex="dir:top">
                    <el-image style="height:44px"
                              src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/453.png"></el-image>
                    <div class="goods">
                        <div class="goods-name">{{previewData.name}}</div>
                        <div flex="dir:left" style="font-size:14px">
                            <div flex="dir:top" style="font-size: 10px">
                                <div style="font-size:26px;color:#ff4544;" :class="previewData.t_type">{{previewData.actualPrice}}</div>
                                <div flex="dir:left">
                                    <div style="color: #999999;text-decoration: line-through;">￥{{previewData.original_price}}</div>
                                    <div style="color: #999999;margin-left: 6px">销量{{previewData.virtual_sales}}{{previewData.unit}}</div>
                                </div>
                            </div>
                            <div class="share" flex="dir:top main:center cross:center">
                                <el-image src="statics/img/mall/goods/icon-share.png"></el-image>
                                <div>分享</div>
                            </div>
                        </div>
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
                previewInfo: {
                    is_head: false,
                    is_cart: false,
                    is_attr: false,
                },
                previewData: null,
                is_add: 1,
                goods_id: -1,
                form: {
                    buy_limit: -1,
                    start_time: '',
                    end_time: '',
                    virtual_miaosha_num: 0,
                    activity_status: 1,
                    id: 0,
                    status_cn: '',
                    group_list: [

                    ],
                    is_auto_add_robot: 0,
                    add_robot_time: 0,
                    unit: '件',
                    virtual_sales: 0
                },
                rule: {
                    is_alone_buy: [
                        {required: true},
                    ],
                    is_auto_add_robot: [
                        {required: true},
                    ],
                    add_robot_time: [
                        {required: true, message: '请添加机器人参与时间', trigger: 'change'},
                    ],
                    start_time: [
                        {required: true, message: '开始时间必填', trigger: 'change'},
                    ],
                    price: [
                        {required: true, message: '请输入活动售价'}
                    ]
                },
                referrerUrl: 'plugin/pintuan/mall/activity/index',
                url: 'plugin/pintuan/mall/activity/edit',
                get_goods_url: 'plugin/pintuan/mall/activity/edit',

                checkAll: false,

                isIndeterminate: false,
                // 批量设置
                batch: {},
                // 默认操作
                isBuyLimit: false,
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() > Date.now() + 30 * 24 * 60 * 60 * 1000 ||
                            time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
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
                memberLevel: [],
                attr: [],
                use_attr: 0,
                attr_groups: [],
                newAttr: {},
                new_attr_groups: [],
                goods_stock: 0,
                price: 0,
                goods_no: '',
                goods_weight: '',
                attr_default_name: '',
                loading: false,
                is_edit: 0
            };
        },

        methods: {
            // 预览
            handlePreview(e) {
                const price = Number(e.price);
                const attr = e.attr;
                let arr = [];
                attr.map(v => {
                    arr.push(Number(v.price));
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

                this.previewData = Object.assign({},e,{
                    actualPrice,
                    t_type:type,
                });
            },

            handleCheckAllChange(val) {
                let arr = [];
                this.options.forEach(function (item) {
                    arr.push(item.value)
                });
                this.form.open_time = val ? arr : [];
                this.isIndeterminate = false;
            },
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.options.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.options.length;
            },
            // 监听子组件事件
            childrenGoods(e) {
                let { attr, use_attr, attr_groups, goods_stock, price, goods_no, goods_weight, attr_default_name } = this.$refs.appGoods.cForm;

                this.attr = attr;
                this.use_attr = use_attr;
                this.attr_groups = attr_groups;
                this.form.goods_warehouse_id = e.goods_warehouse_id;
                this.goods_id = e.goods_warehouse.goods_id;
                this.goods_stock = goods_stock;
                this.price = price;
                this.goods_weight = goods_weight;
                this.goods_no = goods_no;
                this.attr_default_name = attr_default_name;
                if (getQuery('id')) {
                    let { start_time, end_time, is_auto_add_robot, add_robot_time, is_alone_buy } = e.plugin;
                    this.form.status_cn = e.status_cn;
                    this.form.start_time = start_time;
                    this.form.end_time = end_time;
                    if (end_time === '0000-00-00 00:00:00') {
                        this.form.end_time = '';
                    }
                    this.form.is_auto_add_robot = is_auto_add_robot;
                    this.form.add_robot_time = add_robot_time;
                    this.form.is_alone_buy = is_alone_buy;
                    this.form.virtual_sales = e.virtual_sales;
                    this.form.group_list = e.group_list;
                    console.log(e.group_list);
                    this.new_attr_groups = attr_groups;
                    this.referrerUrl = {
                        r: 'plugin/pintuan/mall/activity/index',
                        id: e.goods_warehouse_id
                    };
                    this.form.activity_status = 1;
                }
            },
            filter_color(item) {
                let active = false;
                this.form.open_time.map((it) => {
                    if (item.value === it) {
                        active = true;
                    }
                });
                return active;
            },

            delegation(h, {column}) {
                return h(
                    "el-popover",
                    {
                        props: {
                            placement: "top",
                            trigger: "hover",
                            popperClass : "popperClassResOut"
                        }
                    },
                    [
                        h(
                            "div",
                            [
                                `可发起拼团的次数(包括正在拼团中、拼团完成),`,
                                h('br'),
                                `0代表不限制次数`,
                            ]
                        ),
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
                        ),
                    ]
                )
            },

            destroyPintuan(index) {
                this.form.group_list.splice(index, 1);
            },

            addPintuan() {
                let data = {};
                if (this.newAttr.length === 0) {
                    data = [
                        {
                            attr_list: [
                                {
                                    attr_group_id: -1,
                                    attr_group_name: '规格',
                                    attr_name: this.attr_default_name ? this.attr_default_name : '默认',
                                    attr_id: -1,
                                }
                            ],
                            stock: this.goods_stock,
                            price: this.price,
                            no: this.goods_no,
                            weight: this.goods_weight,
                            pic_url: '',
                            pintuan_stock: this.goods_stock,
                            pintuan_price: this.price,
                            goodsAttr: {
                                id: undefined
                            }
                        }
                    ];
                } else {
                    data = JSON.parse(JSON.stringify(this.newAttr));
                    data.forEach((item) => {
                        item.pintuan_price = item.price;
                        item.pintuan_stock = item.stock;
                        item.goodsAttr = {
                            id: item.id
                        }
                    });
                }

                // this.$set(this.form.group_list, this.form.group_list.length-1, )
                this.form.group_list.push({
                    people_num: 2,
                    preferential_price: 1,
                    pintuan_time: 1,
                    goods_id: 0,
                    attr: JSON.parse(JSON.stringify(data)),
                    member_price: JSON.parse(JSON.stringify(this.defaultMemberPrice)),
                    shareLevelList: {
                        share_commission_first: 0,
                        share_commission_second: 0,
                        share_commission_third: 0,
                    },
                });
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
                    if (e.data.code === 0) {
                        self.memberLevel = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            setAttr(attr, attrGroups) {
                this.form.group_list = [];
                this.attr = attr;
                this.attr_groups = attrGroups;
            },

            changeSalary(row, index, type) {
                this.$nextTick(() => {
                    // 先把非数字的都替换掉(空)，除了数字和.
                    this.form.group_list[index].preferential_price = this.form.group_list[index].preferential_price.replace(/[^\d.]/g, "");
                    // 必须保证第一个为数字而不是.
                    this.form.group_list[index].preferential_price = this.form.group_list[index].preferential_price.replace(/^\./g, "");
                    // 保证只有出现一个.而没有多个.
                    this.form.group_list[index].preferential_price =this.form.group_list[index].preferential_price.replace(/\.{3,}/g, "");
                    // 保证.只出现一次，而不能出现两次以上
                    this.form.group_list[index].preferential_price= this.form.group_list[index].preferential_price
                        .replace(".", "$#$")
                        .replace(/\./g, "")
                        .replace("$#$", ".");
                    // 限制几位小数
                    let subscript = -1;
                    for (let i in this.form.group_list[index].preferential_price) {
                        if (this.form.group_list[index].preferential_price[i] === ".") {
                            subscript = i;
                        }
                        if (subscript !== -1) {
                            if (i - subscript > 2) {
                                this.form.group_list[index].preferential_price =this.form.group_list[index].preferential_price.substring(0, this.form.group_list[index].preferential_price.length - 1);
                            }
                        }
                    }
                });
            },
        },
        mounted() {
            let id = getQuery('id');
            this.getMembers();
            if (id) {
                this.get_goods_url = 'plugin/miaosha/mall/activity/edit';
                this.is_edit = 1;
            } else {
                this.get_goods_url = 'mall/goods/edit';
            }
        },
        computed: {
            defaultMemberPrice() {
                let self = this;
                let defaultMemberPrice = {};
                // 以下数据用于默认规格情况下的 会员价设置
                self.memberLevel.forEach(function (item, index) {
                    // let obj = {};
                    // obj['id'] = index;
                    // obj['name'] = item.name;
                    // obj['level'] = parseInt(item.level);
                    //
                    // let memberPriceValue = 0;
                    // if (self.form.use_attr == 0 && self.form.attr.length > 0) {
                    //     let key = 'level' + item.level;
                    //     let value = self.form.attr[0]['member_price'][key];
                    //     memberPriceValue = value ? value : memberPriceValue;
                    // }
                    // obj['value'] = memberPriceValue;
                    defaultMemberPrice['level' + (index + 1)]  = 0;
                });
                return defaultMemberPrice;
            },

        },

        watch: {
            attr: {
                handler: function(data) {
                    this.newAttr = JSON.parse(JSON.stringify(data));
                },
                deep: true,
                immediate: true
            },
            attr_groups: {
                handler: function(data) {
                    this.new_attr_groups = JSON.parse(JSON.stringify(data));
                    if (this.new_attr_groups.length === 0 ) {
                        this.new_attr_groups = [
                            {
                                attr_group_id: 1,
                                attr_group_name: "规格",
                                attr_list: {
                                    attr_id: 0,
                                    attr_name: '默认',
                                    pic_url: '',
                                },
                            }
                        ]
                    }
                },
                deep: true,
                immediate: true
            }
        },

    });
</script>
