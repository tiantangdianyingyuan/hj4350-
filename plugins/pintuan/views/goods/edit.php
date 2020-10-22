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
    .goods {
        background: #FFFFFF;
        color: #353535;
        padding: 16px 12px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/pintuan/mall/goods/index'})">商品列表</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加商品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods
            sign="pintuan"
            ref="appGoods"
           :is_attr="1"
           :is_show="0"
           url="plugin/pintuan/mall/goods/edit"
           @goods-success="goodsSuccess"
           get_goods_url="plugin/pintuan/mall/goods/edit"
           :form="form"
           :rule="rule"
           referrer="plugin/pintuan/mall/goods/index"
           :goods-head="false"
           :status_change_text="statusChangeText"
           :preview-info="previewInfo"
           @handle-preview="handlePreview">

            <template slot="before_detail">
                <el-card shadow="never" style="margin-top: 24px">
                    <el-form-item label-width="120px" label="阶梯团设置" prop="desc">
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
<!--                    <div slot="header">拼团设置</div>-->
<!--                    <el-col :span="12">-->
<!--                        <el-form-item label="是否允许单独购买" prop="is_alone_buy">-->
<!--                            <el-switch-->
<!--                                    :active-value="1"-->
<!--                                    :inactive-value="0"-->
<!--                                    v-model="form.is_alone_buy">-->
<!--                            </el-switch>-->
<!--                        </el-form-item>-->
<!--                        <el-form-item label="是否热销" prop="is_sell_well">-->
<!--                            <el-switch-->
<!--                                    :active-value="1"-->
<!--                                    :inactive-value="0"-->
<!--                                    v-model="form.is_sell_well">-->
<!--                            </el-switch>-->
<!--                        </el-form-item>-->
<!--                        <el-form-item label="拼团结束时间" prop="end_time">-->
<!--                            <el-date-picker-->
<!--                                    v-model="form.end_time"-->
<!--                                    type="datetime"-->
<!--                                    value-format="yyyy-MM-dd HH:mm:ss"-->
<!--                                    placeholder="选择拼团结束时间">-->
<!--                            </el-date-picker>-->
<!--                        </el-form-item>-->
<!--                        <el-form-item label="拼团次数限制" prop="groups_restrictions">-->
<!--                            <div flex="dir:left">-->
<!--                                <el-input :disabled="isGroupsRestrictions"-->
<!--                                          type="number"-->
<!--                                          v-model.number="form.groups_restrictions">-->
<!--                                </el-input>-->
<!--                                <el-checkbox style="margin-left: 5px;" @change="itemChecked(1)"-->
<!--                                             v-model="isGroupsRestrictions">无限制-->
<!--                                </el-checkbox>-->
<!--                            </div>-->
<!--                        </el-form-item>-->
<!--                    </el-col>-->
                </el-card>
            </template>

            <template slot="preview">
                <div v-if="previewData" flex="dir:top">
                    <el-image style="margin-bottom:12px;height:40px"
                              src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/56.png"></el-image>
                    <div class="goods">
                        <div style="font-size:18px">{{previewData.name}}</div>
                        <div flex="dir:left" style="font-size:14px">
                            <div flex="dir:top" style="font-size: 10px">
                                <div style="font-size:26px;color:#ff4544;" :class="previewData.tType">{{previewData.actualPrice}}</div>
                                <div flex="dir:left">
                                    <div style="padding:3px 6px;background: #feeeee;color:#ff4544;">2人拼团</div>
                                    <div style="margin-left: 3px;padding:3px 6px;background:#feeeee;color:#ff4544">
                                        拼团立省￥{{previewData.original_price - previewData.group_min_price}}
                                    </div>
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

            <template slot="member_route_setting">
                 <span class="red">注：必须在“
                    <el-button type="text" @click="$navigate({r: 'plugin/pintuan/mall/index'}, true)">拼团设置=>基本设置=>优惠叠加设置</el-button>
                    ”中开启，才能使用
                </span>
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
                    is_cart: false,
                },
                is_add: 1,
                form: {
                    is_alone_buy: false,
                    end_time: '',
                    groups_restrictions: -1,
                    is_sell_well: false,
                },
                cats: [],
                attrGroups: [],
                rule: {
                    is_alone_buy: [
                        {required: true, message: '请选择是否允许单独购买', trigger: 'change'},
                    ],
                    end_time: [
                        {required: true, message: '请选择拼团结束时间', trigger: 'change'},
                    ],
                    groups_restrictions: [
                        {required: true, message: '请输入拼团次数限制', trigger: 'change'},
                    ],
                },
                isGroupsRestrictions: true,
                isBuyNumRestrictions: true,
                statusChangeText: '拼团商品至少需要添加一个拼团组,商品才可上架。',
            };
        },
        watch: {
            'form.groups_restrictions'(newVal, oldVal) {
                if (newVal <= -1) {
                    this.isGroupsRestrictions = true;
                } else {
                    this.isGroupsRestrictions = false;
                }
            },
        },
        methods: {
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
                    tType:type,
                });
            },
            goodsSuccess(detail) {
                if (detail.plugin.goods_groups_count > 0) {
                    this.statusChangeText = '';
                }
                this.form = Object.assign(this.form, detail.plugin);
            },
            itemChecked(type) {
                if (type === 1) {
                    this.form.groups_restrictions = this.isGroupsRestrictions ? -1 : 0
                }
            },
        }
    });
</script>
