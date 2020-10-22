<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .list {
        margin-top: 10px;
        width: 100%;
    }

    .choose {
        border: 1px solid #e2e2e2;
        height: 125px;
        width: 120px;
        padding-top: 15px;
        text-align: center;
        margin: 0 10px 20px 0;
        border-radius: 0 !important;
        position: relative;
    }

    .choose i {
        position: absolute;
        top: 5px;
        right: 5px;
        cursor: pointer;
    }

    .choose .name {
        margin-top: -10px;
    }

    .item .el-checkbox-button__inner {
        border: 1px solid #e2e2e2;
        height: 125px;
        width: 120px;
        padding-top: 15px;
        text-align: center;
        margin: 0 20px 20px 0;
        cursor: pointer;
        border-radius: 0 !important;
    }

    .item.active {
        background-color: #50A0E4;
        color: #fff;
    }

    .list .img {
        height: 60px;
        width: 60px;
        border-radius: 30px;
    }

    .name {
        margin-top: 10px;
        font-size: 13px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 20px;
        min-width: 1000px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px !important;
    }
    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    .el-card, .el-message {
        border-radius: 0;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .special .el-radio {
        display: inline-flex;
        flex-direction: row;
        align-items: center;
    }

    .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .app-share .app-share-bg {
        position: relative;
        width: 403px;
        height: 319px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .app-share .app-share-bg .title {
        width: 160px;
        height: 29px;
        line-height: 1;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .customize-share-title {
         margin-top: 10px;
         width: 80px;
         height: 80px;
         position: relative;
         cursor: move;
     }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .can_receive_count .el-form-item__label {
        line-height: 1;
        vertical-align: auto;
    }
</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                      @click="$navigate({r:'mall/coupon/index'})">优惠券管理</span></el-breadcrumb-item>
            <el-breadcrumb-item>优惠券{{id > 0 ? '编辑' : '新建'}}</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="171px" :rules="FormRules" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础设置" name="first">
                    <div class="form-body">
                        <el-form-item label="优惠券名称" prop="name">
                            <el-input size="small" v-model="form.name" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="优惠券类型" prop="type">
                            <el-radio v-model="form.type" label="2" @change="typeChange">满减券</el-radio>
                            <el-radio v-model="form.type" label="1" @change="typeChange">打折券</el-radio>
                        </el-form-item>
                        <el-form-item label="指定商品类别或商品" prop="appoint_type">
                            <el-radio v-model="form.appoint_type" @click.native="catsStatus" label="1">指定商品类别</el-radio>
                            <el-radio v-model="form.appoint_type" @click.native="goodsListVisible = true" label="2">
                                指定商品
                            </el-radio>
                            <el-radio v-model="form.appoint_type" label="3">全场通用</el-radio>
                            <el-radio v-if="isScanCodePayShow" v-model="form.appoint_type" label="4">当面付</el-radio>
                            <el-radio v-if="isExchange" v-model="form.appoint_type" label="5">礼品卡</el-radio>
                            <div v-if="form.appoint_type ==1">
                                <el-tag v-for="(item,index) in form.cat"
                                        :key="index"
                                        type="warning"
                                        closable
                                        style="margin-right: 20px;"
                                        effect="plain"
                                        @close="catDestroy(index)"
                                >{{item.name}}
                                </el-tag>
                            </div>
                            <div v-else-if="form.appoint_type == 2" flex="dif:left" style="flex-wrap:wrap;margin-top:20px">
                                <div v-for="(item,index) in form.goodsWarehouse" :key="index"
                                     @mouseenter="enter(index)"
                                     @mouseleave="leave"
                                     style="margin-right: 20px;position: relative;cursor: pointer;">
                                    <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                        <app-image mode="aspectFill"
                                                   width="50px"
                                                   height='50px'
                                                   :src="item.cover_pic">
                                        </app-image>
                                    </el-tooltip>

                                    <el-button class="del-btn"
                                               v-if="goods_status == index"
                                               size="mini" type="danger" icon="el-icon-close"
                                               circle
                                               @click="goodsDestroy(index)"></el-button>
                                </div>
                            </div>
                        </el-form-item>


                        <el-form-item prop="min_price">
                            <template slot='label'>
                                <span>最低消费金额（元）</span>
                                <el-tooltip effect="dark" content="购物金额（不含运费）达到最低消费金额才可使用优惠券，无门槛优惠券请填0"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input size="small" type="number" v-model="form.min_price" autocomplete="off"></el-input>
                        </el-form-item>
                        <template v-if="form.type == 2">
                            <el-form-item prop="sub_price">
                                <template slot='label'>
                                    <span>优惠金额（元）</span>
                                    <el-tooltip effect="dark" content="优惠券只能抵消商品金额，不能抵消运费"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-input size="small" type="number" v-model="form.sub_price" autocomplete="off"></el-input>
                            </el-form-item>
                        </template>

                        <block v-show="form.type == 1">
                            <el-form-item prop="discount">
                                <template slot='label'>
                                    <span>优惠折扣</span>
                                </template>
                                <el-input size="small" type="number" v-model="form.discount" autocomplete="off">
                                    <template slot="append">折</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item prop="discount_limit">
                                <template slot='label'>
                                    <span>优惠上限</span>
                                </template>
                                <el-input size="small" type="number" v-model="form.discount_limit" autocomplete="off">
                                    <template slot="prepend">最多优惠</template>
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                        </block>

                        <el-form-item label="优惠券有效期" prop="expire_type">
                            <el-radio v-model="form.expire_type" @change="toExpirce" label="1">领取后N天内有效</el-radio>
                            <el-radio v-model="form.expire_type" @change="toExpirce" label="2">时间段</el-radio>
                        </el-form-item>
                        <el-form-item label="有效天数" v-if="form.expire_type == 1" prop="expire_day">
                            <el-input size="small" v-model="form.expire_day" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="有效期范围" v-if="form.expire_type == 2" prop="time">
                            <el-date-picker size='small' v-model="time" value-format="yyyy-MM-dd HH:mm:ss" type="datetimerange"
                                            range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item prop="total_count">
                            <template slot='label'>
                                <span>优惠券库存</span>
                                <el-tooltip effect="dark" content="优惠券库存为0则无法领取或发放,-1为不限制张数"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input size="small" type="number" v-model.number="form.total_count"
                                      :disabled="form.total_count == -1" :min="-1"
                                      oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                      autocomplete="off"></el-input>
                            <el-checkbox v-model="form.total_count" :true-label="-1" :false-label="0">无限制</el-checkbox>
                        </el-form-item>
                        <el-form-item label="每人限领次数" prop="can_receive_count" class="can_receive_count">
                            <el-radio-group v-model="receive_count_type" class="special">
                                <div>
                                    <el-radio :label="0">不限次数</el-radio>
                                </div>
                                <div style="margin-top: 10px;" @click="receive_count_type = 1">
                                    <el-radio :label="1">
                                        <el-input size="small" type="number" v-model.number="form.can_receive_count"
                                                  :min="-1" :disabled="receive_count_type == 0" placeholder="请输入次数"
                                                  oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                  autocomplete="off" @focus="receive_count_type = 1">
                                            <template slot="prepend">限</template>
                                            <template slot="append">次</template>
                                        </el-input>
                                    </el-radio>
                                </div>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item prop="app_share_title">
                            <label slot="label">
                                <span>自定义分享标题</span>
                                <el-tooltip class="item" effect="dark" content="分享给好友时，作为商品名称"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input placeholder="请输入分享标题" size="small"
                                      v-model="form.app_share_title"></el-input>
                            <el-button @click="app_share.dialog = true;app_share.type = 'name_bg'"
                                       type="text">查看图例
                            </el-button>
                        </el-form-item>

                        <el-form-item  prop="app_share_pic">
                            <label slot="label">
                                <span>自定义分享图片</span>
                                <el-tooltip class="item" effect="dark" content="分享给好友时，作为分享图片"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <app-attachment v-model="form.app_share_pic" :multiple="false" :max="1">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <div class="customize-share-title">
                                <app-image mode="aspectFill" width='80px' height='80px'
                                           :src="form.app_share_pic ? form.app_share_pic : ''"></app-image>
                                <el-button v-if="form.app_share_pic" class="del-btn" size="mini"
                                           type="danger" icon="el-icon-close" circle
                                           @click="form.app_share_pic = ''"></el-button>
                            </div>
                            <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                                       type="text">查看图例
                            </el-button>
                        </el-form-item>
                        <el-dialog :title="app_share['type'] == 'pic_bg' ? `查看自定义分享图片图例`:`查看自定义分享标题图例`"
                                   :visible.sync="app_share.dialog" width="30%">
                            <div flex="dir:left main:center" class="app-share">
                                <div class="app-share-bg"
                                     :style="{backgroundImage: 'url('+app_share[app_share.type]+')'}"></div>
                            </div>
                            <div slot="footer" class="dialog-footer">
                                <el-button @click="app_share.dialog = false" type="primary">我知道了</el-button>
                            </div>
                        </el-dialog>
                        <el-form-item label="" prop="sort">
                            <template slot='label'>
                                <span>排序</span>
                                <el-tooltip effect="dark" content="排序按升序排列"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input size="small" type="number" v-model="form.sort" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="使用说明" prop="rule">
                            <el-input type="textarea" v-model="form.rule"></el-input>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="领取方式设置" name="second">
                    <div class="form-body">
                        <el-form-item label="领取方式">
                            <el-checkbox :true-label="1" :false-label="0" v-model="form.is_member">会员专享</el-checkbox>
                            <el-checkbox :true-label="1" :false-label="0" v-model="form.is_join">领券中心</el-checkbox>
                        </el-form-item>
                        <el-form-item label="会员专享" prop="couponMember" v-if="form.is_member == 1">
                            <template v-if="members.length > 0">
                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll"
                                             @change="handleCheckAllChange">全选
                                </el-checkbox>
                                <div style="margin: 15px 0;"></div>
                                <el-checkbox-group v-model="couponMember" @change="handleCheckedMemberChange">
                                    <el-checkbox v-for="item in members" :label="item.level" :key="item.level">
                                        {{item.name}}
                                    </el-checkbox>
                                </el-checkbox-group>
                            </template>
                            <template v-else>
                                <div flex>
                                    <div style="color: #ff4544;font-size: 12px;margin-left: 24px;">注：必须在“
                                        <el-button type="text"
                                                   @click="$navigate({r:'mall/mall-member/index'}, true)">
                                            用户管理=>会员等级
                                        </el-button>
                                        ”中设置了会员，才能使用
                                    </div>
                                </div>
                            </template>
                        </el-form-item>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" type="primary" size="small" :loading=submitLoading @click="onSubmit">保存
            </el-button>
        </el-form>
        <!--指定商品-->
        <el-dialog title="已指定商品" :visible.sync="goodsListVisible">
            <el-dialog title="添加商品" :visible.sync="goodsAddVisible" append-to-body>
                <el-form @submit.native.prevent>
                    <el-form-item>
                        <el-input size="small" v-model="keyword" autocomplete="off" placeholder="商品名称"
                                  style="width: 40%"></el-input>
                        <el-button size="small" :loading="btnLoading"
                                   @click="search('mall/coupon/search-goods','goods')">查找商品
                        </el-button>
                        <el-button size="small" style="float:right" @click="goodsBatchAdd">批量添加</el-button>
                    </el-form-item>
                </el-form>
                <el-table v-loading="btnLoading" :data="goods_list" @selection-change="selsChange" style="width: 100%"  height="500">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop="id" label="ID" width="80"></el-table-column>
                    <el-table-column prop="cover_pic" label="商品图" width="180">
                        <template slot-scope="scope">
                            <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="商品名称">
                        <template slot-scope="scope">
                            <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button v-if="form.goods_id_list.indexOf(scope.row.id) == -1" plain size="mini"
                                       type="primary" @click="goodsAdd(scope.$index, scope.row.id)">添加
                            </el-button>
                            <el-button v-else plain size="mini" type="primary" disabled>已添加</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div slot="footer" class="dialog-footer">
                    <el-pagination
                            v-if="pagination"
                            background
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper"
                            :page-size="20"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </el-dialog>
            <el-button style="float: right" type="primary" size="small" @click="goodsAddVisible = true">新增</el-button>
            <el-table :data="form.goodsWarehouse" style="width: 100%" del-max-height="99999px">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="cover_pic" label="商品图" width="80">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="商品名称">
                    <template slot-scope="scope">
                        <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button plain size="mini" type="danger" @click="goodsDestroy(scope.$index)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
        <!--指定商品分类-->
        <el-dialog title="已指定商品类别" :visible.sync="catsListVisible" width="35%">
            <el-tree
                    style="margin-left:30px"
                    :indent="40"
                    :data="cats"
                    show-checkbox
                    default-expand-all
                    :default-checked-keys="form.cat_id_list"
                    node-key="id"
                    ref="tree"
                    highlight-current
                    :props="defaultProps"
            ></el-tree>
            <div slot="footer" class="dialog-footer">
                <el-button plain type="submit" @click="catsListVisible = false">取消</el-button>
                <el-button plain type="primary" @click="catsAdd">确定</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validDate = (rule, value, callback) => {
                if (this.form.expire_type == 2 && (!this.time || !this.time[0] || !this.time[1])) {
                    callback(new Error("优惠券有效期不能为空"))
                } else {
                    callback()
                }
            };
            var checkAge = (rule, value, callback) => {
                if (!value) {
                    return callback(new Error('优惠金额不能为空'));
                }
                setTimeout(() => {
                    if (value < 0.01) {
                        callback(new Error('优惠金额需大于零'));
                    } else {
                        callback();
                    }
                }, 0);
            };
            let check_can_receive_count = (rule, value, callback) => {
                if (this.receive_count_type === 1 && value === '') {
                    callback(new Error('每人限领次数不能为空'));
                } else {
                    callback();
                }
            };
            let discountData = (rule, value, callback) => {
                console.log(value)
                if (this.form.type == 1 && value === '') {
                    callback(new Error('优惠券折扣不能为空'));
                } else {
                    callback();
                }
            }

            return {
                goods_status: -1,
                //添加商品
                pagination: null,
                goodsListVisible: false,
                goodsAddVisible: false,
                goodsBatchList: [],
                //添加分类
                catsListVisible: false,
                cats: [],
                defaultProps: {
                    children: 'child',
                    label: 'name'
                },

                members: [],
                activeName: 'first',
                form: {
                    type: '2',
                    min_price: '0',
                    sub_price: '0',
                    expire_day: '1',
                    expire_type: '1',
                    is_join: 1,
                    appoint_type: '1',
                    total_count: -1,
                    cat: [],
                    goodsWarehouse: [],
                    sort: 100,
                    appoint_type: '3',
                    is_member: 0,
                    couponMember: [],
                    cat_id_list: [],
                    goods_id_list: [],
                    discount: '',
                    discount_limit: '',
                    can_receive_count: 1,
                    app_share_title: '',
                    app_share_pic: '',
                },
                btnLoading: false,
                submitLoading: false,
                loading: false,
                cats_list: [],
                goods_list: [],
                cat: [],
                keyword: null,
                time: [],
                goods: [],
                cat_id: null,
                goods_id: null,
                cat_id_list: [],
                goods_id_list: [],
                checkAll: false,
                couponMember: [],
                membersList: [],
                isIndeterminate: false,
                FormRules: {
                    name: [
                        {required: true, message: '优惠券名称不能为空', trigger: 'blur'},
                        {min: 1, max: 30, message: "优惠券名称长度在1-30个字符内"},
                    ],
                    type: [
                        {required: true, message: '优惠券类型不能为空', trigger: 'blur'}
                    ],
                    min_price: [
                        {required: true, message: '最低消费金额不能为空', trigger: 'blur'}
                    ],
                    sub_price: [
                        {validator: checkAge, trigger: 'blur'},
                    ],
                    expire_day: [
                        {required: true, message: '优惠券有效天数不能为空', trigger: 'blur'}
                    ],
                    time: [
                        {validator: validDate, trigger: 'change', required: true}
                    ],
                    is_join: [
                        {required: true, trigger: 'blur'}
                    ],
                    total_count: [
                        {required: true, message: '优惠券库存不能为空', trigger: 'change'}
                    ],
                    can_receive_count: [
                        {type: 'number', required: true, validator: check_can_receive_count, trigger: 'change'}
                    ],
                    sort: [
                        {required: true, pattern: /^[0-9]\d{0,8}$/, message: '排序必须在9位整数内'}
                    ],
                    is_member: [
                        {required: true, message: '指定会员领取不能为空', trigger: 'blur'}
                    ],
                    discount: [
                        {validator: discountData, required: true, message: '优惠折扣不能为空', trigger: 'change'},
                    ]
                },
                receive: [],
                isScanCodePayShow: false,
                isExchange: false,
                receive_count_type: 1,
                app_share: {
                    dialog: false,
                    type: '',
                    bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share.png",
                    name_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/coupon/app-share-name.png",
                    pic_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/coupon/app-share-pic.png",
                },
                id: 0,
            };
        },
        methods: {
            toExpirce() {
                this.time = [];
                this.$nextTick(() => {
                    this.$refs.form.clearValidate();
                })
            },
            catsStatus() {
                this.catsListVisible = true;
                console.log(typeof this.form.cat_id_list, this.form.cat_id_list);
                setTimeout(() => {
                    this.$refs.tree.setCheckedKeys(this.form.cat_id_list);
                })
            },
            enter(index) {
                this.goods_status = index;
            },
            leave() {
                this.goods_status = -1;
            },
            catDestroy(index) {
                this.form.cat_id_list.splice(index, 1);
                this.form.cat.splice(index, 1);
            },

            handleCheckAllChange(val) {
                this.couponMember = val ? this.membersList : [];
                this.isIndeterminate = false;
            },
            handleCheckedMemberChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.members.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.members.length;
            },
            //分页
            pageChange(page) {
                this.search('mall/coupon/search-goods', 'goods', page);
            },

            catsAdd() {
                let cat_id_list = this.$refs.tree.getCheckedKeys();
                this.form.cat_id_list = cat_id_list;
                let array = [];
                this.cats.map(v => {
                    if (cat_id_list.indexOf(v.id) != -1) {
                        array.push(v);
                    }
                    v.child.map(v1 => {
                        if (cat_id_list.indexOf(v1.id) != -1) {
                            array.push(v1);
                        }
                        v1.child.map(v2 => {
                            if (cat_id_list.indexOf(v2.id) != -1) {
                                array.push(v2);
                            }
                        })
                    })
                })
                this.form.cat = array;
                this.$message({
                    message: '添加成功',
                    type: 'success'
                });

                this.catsListVisible = false;
            },
            //指定商品
            goodsDestroy(index) {
                this.form.goods_id_list.splice(index, 1);
                this.form.goodsWarehouse.splice(index, 1);
            },
            selsChange(row) {
                this.goodsBatchList = row;
            },
            goodsBatchAdd() {
                this.goodsBatchList.forEach(goods => {
                    this.setGoods(goods);
                })
            },
            goodsAdd(index, row) {
                let goods = this.goods_list[index];
                this.setGoods(goods);
            },

            setGoods(goods) {
                let sentinel = true;
                this.form.goodsWarehouse.forEach(v => {
                    if (v.id == goods.id) {
                        sentinel = false;
                    }
                })
                if (sentinel) {
                    this.form.goodsWarehouse.push(goods);
                    this.form.goods_id_list.push(goods.id);
                    this.$message({
                        message: '添加成功',
                        type: 'success'
                    });
                }
            },
            //分类
            catsDestroy(index, row) {
                this.form.cat.splice(index, 1);
            },
            // 返回上一页
            Cancel() {
                window.history.go(-1)
            },

            // 点击获取商品分类列表或者商品列表
            search(url, attr, page = 1) {
                this.page = page;
                this.btnLoading = true;
                request({
                    params: {
                        r: url,
                        keyword: this.keyword,
                        page: this.page,
                    },
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 0) {
                        if (attr == 'cats') {
                            this.cats_list = e.data.data.list;
                        } else if (attr == 'goods') {
                            this.pagination = e.data.data.pagination;
                            this.goods_list = e.data.data.list;
                        }
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
            },

            // 提交数据
            onSubmit() {
                if (this.form.appoint_type == 1) {
                    if (this.form.cat_id_list.length == 0) {
                        this.loading = false;
                        this.$alert('请添加指定商品类型', '提示', {
                            confirmButtonText: '确定'
                        })
                        return false;
                    }
                } else if (this.form.appoint_type == 2) {
                    if (this.form.goods_id_list.length == 0) {
                        this.loading = false;
                        this.$alert('请添加指定商品', '提示', {
                            confirmButtonText: '确定'
                        })
                        return false;
                    }
                }
                this.form.begin_time = this.time[0];
                this.form.end_time = this.time[1];
                this.form.couponMember = this.couponMember;
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.submitLoading = true;
                        let para = Object.assign({}, this.form, {
                            couponCat: '',
                            cat: '',
                            goodsWarehouse: '',
                            couponGoods: '',
                        });
                        request({
                            params: {
                                r: 'mall/coupon/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                                setTimeout(function () {
                                    navigateTo({r: 'mall/coupon/index'});
                                }, 300);
                            } else {
                                this.$alert(e.data.msg, '提示', {
                                    confirmButtonText: '确定'
                                })
                            }
                        }).catch(e => {
                            this.submitLoading = false;
                            this.$alert(e.data.msg, '提示', {
                                confirmButtonText: '确定'
                            })
                        });
                    } else {
                        this.loading = false;
                    }
                });
            },

            //获取列表
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/coupon/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.list && e.data.data.list.id > 0) {
                            this.form = e.data.data.list;
                            this.time = [e.data.data.list.begin_time, e.data.data.list.end_time];
                            if (e.data.data.list.begin_time == '0000-00-00 00:00:00' &&  e.data.data.list.end_time == '0000-00-00 00:00:00') {
                                this.time = [];
                            }
                            this.couponMember = this.form.couponMember;
                            if (this.form.can_receive_count == -1) {
                                this.receive_count_type = 0;
                                this.form.can_receive_count = '';
                            } else {
                                this.receive_count_type = 1;
                            }
                        }
                        this.members = e.data.data.members;
                        this.cats = e.data.data.cats;
                        if (this.couponMember.length == this.members.length) {
                            this.checkAll = true;
                            this.isIndeterminate = false;
                        } else if (this.couponMember.length > 0) {
                            this.isIndeterminate = true;
                        }
                        for (let i = 0; i < this.members.length; i++) {
                            this.membersList.push(this.members[i].level)
                        }
                    }
                }).catch(e => {
                });
            },
            getPermsssions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/index/mall-permissions'
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.isScanCodePayShow = 0;
                        e.data.data.permissions.forEach(function (item) {
                            if (item == 'scan_code_pay') {
                                self.isScanCodePayShow = 1;
                            }
                            if (item === 'exchange') {
                                self.isExchange = 1;
                            }
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            typeChange(type) {
                if (type == 1) {
                    this.form.discount = null;
                }
            },
        },

        created() {
            if (getQuery('id')) {
                this.id = getQuery('id');
            }
            this.getList();
            this.getPermsssions();
            this.search('mall/coupon/search-goods', 'goods')
        },
        watch: {
            receive_count_type() {
                if (this.receive_count_type === 1) {
                    this.form.can_receive_count = 1;
                } else {
                    this.form.can_receive_count = '';
                }
            }
        }
    })
</script>
