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
<div id="app" v-cloak>
    <div class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                      @click="$navigate({r:'plugin/integral_mall/mall/goods/index'})">商品管理</span></el-breadcrumb-item>
            <el-breadcrumb-item v-if="form.id>0">编辑商品</el-breadcrumb-item>
            <el-breadcrumb-item v-else>添加商品</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <app-goods
            ref="appGoods"
            :is_member="0"
            :is_attr="1"
            :is_show="0"
            :is_info="0"
            :is_detail="0"
            :form="form"
            :rule="rule"
            :is_cats="0"
            sign="integral_mall"
            :no_price="0"
            :is_display_setting="0"
            :extra_attr_require="extra_attr_require"
            url="plugin/integral_mall/mall/goods/edit"
            :preview-info="previewInfo"
            @handle-preview="handlePreview"
            @goods-success="childrenGoods"
            get_goods_url="plugin/integral_mall/mall/goods/edit"
            referrer="plugin/integral_mall/mall/goods/index">
        <!-- 显示设置 -->
        <template slot="before_attr">
            <el-card shadow="never" class="mt-24">
                <div slot="header">
                    <span>显示设置</span>
                </div>
                <el-row>
                    <el-col :xl="12" :lg="16">
                        <el-form-item label="放置首页">
                            <el-tooltip class="item" effect="dark" content="开启后,商品会在积分商城首页展示"
                                        placement="top">
                                <el-switch
                                        :active-value="1"
                                        :inactive-value="0"
                                        v-model="form.is_home">
                                </el-switch>
                            </el-tooltip>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>
        </template>

        <template slot="before_price">
            <!-- 兑换价格 -->
            <el-form-item label="兑换价格" prop="price" >
                <el-input type="number"
                          oninput="this.value = this.value.replace(/[^0-9\.]/, '');" :min="0"
                          v-model="form.price">
                    <template slot="append">元</template>
                </el-input>
            </el-form-item>

            <!-- 兑换积分 -->
            <el-form-item label="兑换积分" prop="integral_num" >
                <el-input type="number"
                          oninput="this.value = this.value.replace(/[^0-9\.]/, '');" :min="0"
                          v-model="form.integral_num">
                    <template slot="append">分</template>
                </el-input>
            </el-form-item>
        </template>

        <template slot="preview">
            <div v-if="previewData" flex="dir:top">
                <div class="goods">
                    <div class="goods-name">{{previewData.name}}</div>
                    <div flex="dir:left" style="font-size:14px">
                        <div flex="dir:left" style="font-size: 18px;height:22px;color:#ff4544;margin-top:15px">
                            <div flex="dir:top">
                                <div style="font-size:15px;margin-bottom:3px">
                                    {{previewData.integral_num}}积分+{{previewData.price}}元
                                </div>
                                <div style="font-size: 8px;text-decoration: line-through;color:#888">
                                    ￥{{previewData.original_price}}
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
    </app-goods>
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

                rule: {
                    integral_num: [
                        {required: true, message: '请输入兑换积分', trigger: 'change'},
                    ],
                    price: [
                        {required: true, message: '请输入兑换价格', trigger: 'change'},
                    ],
                },
                extra_attr_require: ['integral_num'],
                form: {
                    is_home: 1,
                    integral_num: '',
                    price: '',
                    extra: {
                        integral_num: '积分',
                    }
                },
            };
        },
        created() {
            let id = getQuery('id');
            if (id) {
                this.form.id = getQuery('id');
            }
        },
        methods: {
            handlePreview(e) {
                this.previewData = e;
            },
            // 监听子组件事件
            childrenGoods(e) {
                let self = this;
                self.form.extra = {
                    integral_num: '积分',
                };
                if (getQuery('id')) {
                    this.id = getQuery('id');
                    self.form.integral_num = e.integral_num;
                    self.form.price = e.price;
                    self.form.is_home = e.integralMallGoods.is_home
                }
            },
        }
    });
</script>
<style>
    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
</style>