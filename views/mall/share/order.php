<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadViewComponent('app-order');
$menuList = \Yii::$app->role->getShareMenu();
$newMenuList[] = [
    'name' => '全部',
    'value' => 'all'
];
foreach ($menuList as $item) {
    $newMenuList[] = [
        'name' => $item['name'],
        'value' => $item['sign']
    ];
}
$newMenuList = Yii::$app->serializer->encode($newMenuList);
?>
<style>
    .app-box-info {
        border-right: 1px solid #EBEEF5;
    }

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <app-order
                active-name="all"
                :order-title="orderTitle"
                :tabs="menuList"
                :new-search="search"
                :select-list="selectList"
                :is-show-recycle="false"
                :is-show-confirm="false"
                :is-show-finish="false"
                :is-show-send="false"
                :is-show-clerk="false"
                :is-show-print="false"
                :is-show-remark="false"
                :is-show-cancel="false"
                :is-show-edit-address="false"
                :is-show-detail="false"
                :is-show-action="false"
                :is-show-edit-express-price="false"
                :is-show-edit-single-price="false"
                :date-type-list="[]"
                order-url="mall/share/order">
            <template slot="orderAction" slot-scope="order">
                <div flex="main:center cross:center" class="app-box-info" :style="{width:orderTitle[2].width}">
                    <div v-if="order.order.is_sale == 1">佣金已发放</div>
                    <div v-if="order.order.is_sale == 0">待结算</div>
                </div>
                <div flex="main:center cross:center" class="app-box-info" style="border-right: 0"
                     :style="{width:orderTitle[3].width}">
                    <div v-if="order.order.first_parent_id == order.order.user_id">
                        <div shadow="never" style="margin: 10px">
                            <div>自购返利<span class="price">￥{{order.order.first_price}}</span></div>
                        </div>
                        <div shadow="never" style="margin: 10px" v-if="order.order.second_parent">
                            <div>一级佣金<span class="price">￥{{order.order.second_price}}</span></div>
                            <div>昵称：{{order.order.second_parent.nickname}}</div>
                            <div v-if="order.order.second_parent.name">姓名：{{order.order.second_parent.name}}</div>
                            <div v-if="order.order.second_parent.mobile">手机：{{order.order.second_parent.mobile}}</div>
                        </div>
                        <div shadow="never" style="margin: 10px" v-if="order.order.third_parent">
                            <div>二级佣金<span class="price">￥{{order.order.third_price}}</span></div>
                            <div>昵称：{{order.order.third_parent.nickname}}</div>
                            <div v-if="order.order.third_parent.name">姓名：{{order.order.third_parent.name}}</div>
                            <div v-if="order.order.third_parent.mobile">手机：{{order.order.third_parent.mobile}}</div>
                        </div>
                    </div>
                    <div v-else>
                        <div shadow="never" style="margin: 10px" v-if="order.order.first_parent">
                            <div>一级佣金<span class="price">￥{{order.order.first_price}}</span></div>
                            <div>昵称：{{order.order.first_parent.nickname}}</div>
                            <div v-if="order.order.first_parent.name">姓名：{{order.order.first_parent.name}}</div>
                            <div v-if="order.order.first_parent.mobile">手机：{{order.order.first_parent.mobile}}</div>
                        </div>
                        <div shadow="never" style="margin: 10px" v-if="order.order.second_parent">
                            <div>二级佣金<span class="price">￥{{order.order.second_price}}</span></div>
                            <div>昵称：{{order.order.second_parent.nickname}}</div>
                            <div v-if="order.order.second_parent.name">姓名：{{order.order.second_parent.name}}</div>
                            <div v-if="order.order.second_parent.mobile">手机：{{order.order.second_parent.mobile}}</div>
                        </div>
                        <div shadow="never" style="margin: 10px" v-if="order.order.third_parent">
                            <div>三级佣金<span class="price">￥{{order.order.third_price}}</span></div>
                            <div>昵称：{{order.order.third_parent.nickname}}</div>
                            <div v-if="order.order.third_parent.name">姓名：{{order.order.third_parent.name}}</div>
                            <div v-if="order.order.third_parent.mobile">手机：{{order.order.third_parent.mobile}}</div>
                        </div>
                    </div>
                </div>
            </template>
        </app-order>
    </el-card>
</div>

<style>
</style>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    time: null,
                    keyword: '',
                    keyword_1: 'order_no',
                    date_start: '',
                    date_end: '',
                    platform: '',
                    status: '',
                    plugin: 'all',
                    send_type: -1,
                    parent_id: 0,
                },
                loading: false,
                pagination: null,
                activeName: 'all',
                list: [],
                address: [],
                exportList: [],
                menuList: JSON.parse('<?= $newMenuList ?>'),
                orderTitle: [
                    {width: '60%', name: '商品信息'},
                    {width: '15%', name: '实付金额'},
                    {width: '10%', name: '分销状态'},
                    {width: '15%', name: '分销情况'}
                ],
                selectList: [
                    {value: 'order_no', name: '订单号'},
                    {value: 'nickname', name: '用户名'},
                    {value: 'user_id', name: '用户ID'},
                    {value: 'goods_name', name: '商品名称'}
                ]
            };
        },
        created() {
            if (getQuery('id')) {
                this.search.parent_id = getQuery('id');
            }

            // this.search.status = this.activeName;
        },
        methods: {
            // 获取列表
            getList() {
                this.loading = true;
                this.list = [];
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                if (getQuery('id')) {
                    this.search.parent_id = getQuery('id');
                }
                this.search.status = this.activeName;
                request({
                    params: this.search,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
                        this.menuList = e.data.data.menu_list;
                    }

                }).catch(e => {
                    this.loading = false;
                });
            },
        }
    });
</script>