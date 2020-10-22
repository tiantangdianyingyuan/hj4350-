<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-goods');
$mchId = Yii::$app->user->identity->mch_id;
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/community/mall/activity/index'})">
                        社区团购
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>商品编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods
           :is_show="0"
           sign="community"
           :form="form"
           :referrer="skip"
           :rule="rule"
           :is_mch="is_mch"
           :mch_id="mch_id"
           :is_member="0"
           :no_price="0"
           :is_shipping="0"
           :is_shipping_rules="0"
           :is_video_url="0"
           :is_original_price="1"
           :is_copy_id="0"
           :is_display_setting="0"
           :is_cats="0"
           :is_name="0"
           :is_virtual_sales="0"
           :is_pic_url="0"
           :is_forehead_integral="0"
           :is_detail="0"
           :position="position"
           :extra_attr_require="extra_attr_require"
           price_label="买家购买价"
           url="plugin/community/mall/activity/edit-activity-goods"
           get_goods_url="plugin/community/mall/activity/edit-activity-goods"
           ref="appGoods">

          <template slot-scope="item" slot="after_original_price">
              <!-- 买家购买价 -->
              <el-form-item label="买家购买价" prop="price" >
                  <el-input type="number"
                            placeholder="请输入买家购买价"
                            oninput="this.value = this.value.replace(/[^0-9\.]/, '');" :min="0"
                            v-model="item.item.use_attr == 1 ? '' : item.item.price"
                            :disabled="item.item.use_attr == 1">
                      <template slot="append">元</template>
                  </el-input>
              </el-form-item>

              <!-- 供货价 -->
              <el-form-item label="团长供货价" prop="supply_price" >
                  <el-input type="number"
                            placeholder="请输入团长供货价"
                            oninput="this.value = this.value.replace(/[^0-9\.]/, '');" :min="0"
                            v-model="item.item.use_attr == 1 ? '' : item.item.supply_price"
                            :disabled="item.item.use_attr == 1">
                      <template slot="append">元</template>
                  </el-input>
              </el-form-item>
              <el-form-item label="虚拟已售" prop="virtual_sales">
                  <el-input type="text" maxLength="7" oninput="this.value = this.value.replace(/[^0-9]/, '')" min="0" v-model="item.item.virtual_sales">
                      <template slot="append">{{item.item.unit}}</template>
                  </el-input>
              </el-form-item>
          </template>
        </app-goods>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                url: 'plugin/community/mall/activity/edit-activity-goods',
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
                mch_id: <?= $mchId ?>,
                extra_attr_require: ['supply_price'],
                rule: {
                    price: [
                        {required: true, message: '请输入买家购买价', trigger: 'change'},
                    ],
                    supply_price: [
                        {required: true, message: '请输入供货价', trigger: 'change'},
                    ],
                },
                skip: '',
                position: '',
                form: {
                    extra: {
                        supply_price: '团长供货价(元)'
                    },
                    is_home: 1,
                },
            }
        },
        methods: {
          toRobot() {
            this.$navigate({
                r: 'plugin/community/mall/robot/index'
            },true);
          }
        },
        created() {
            if(getQuery('page') > 1) {
                this.url = {
                    r: 'plugin/community/mall/activity/edit-activity-goods',
                    page: getQuery('page')
                }
            }
            if(getQuery('other')) {
              this.position = getQuery('other')
            }
            let activity = getQuery('activity');
            this.skip = {
              r: 'plugin/community/mall/activity/edit',
              id: activity,
              tab: 1
            }
        },

    });
</script>
