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
                          @click="$navigate({r:'plugin/exchange/mall/card-goods/index'})">
                        礼品卡
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="is_edit">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加礼品卡</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods :is_cats="0"
                   :is_show="0"
                   :is_shipping="0"
                   :is_area_limit="0"
                   :is_e_card="0"
                   :is_attr="0"
                   :is_copy_id="0"
                   :is_form="0"
                   :is_info="0"
                   :form="form"
                   :is_edit="is_edit"
                   :is_detail="1"
                   :is_mch="0"
                   :mch_id="0"
                   :is_shipping_rules="0"
                   :library_id="library_id"
                   referrer="plugin/exchange/mall/card-goods/index"
                   :url="url"
                   :rule="rule"
                   :get_goods_url="url"
                   :is_display_setting="0"
                   sign="exchange"
                   @goods-success="childrenGoods"
                   ref="appGoods">
            <template slot="before_price" slot-scope="item">
                <el-form-item v-loading="library_loading" label="兑换码库" required>
                    <el-select style="width: 100%" v-model="library_id" @change="selectLibrary" placeholder="请选择兑换码库">
                        <el-option
                                v-for="(item, index) in library"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
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
                library_id: '',
                library: [],
                form: {},
                url: 'plugin/exchange/mall/card-goods/edit',
                is_edit: 0,
                library_loading: false,
                rule: {
                  goods_num: [
                      {required: true, message: '请输入库存', trigger: 'change'},
                  ],
                }
            }
        },
        created() {
            this.getList();
            if(getQuery('page') > 1) {
                this.url = {
                    r: 'plugin/exchange/mall/card-goods/edit',
                    page: getQuery('page')
                }
            }
            if (getQuery('id')) {
                this.is_edit = 1;
            }
        },
        methods: {
            selectLibrary(e) {
                this.library_id = e;
            },
            getList() {
                this.library_loading = true;
                request({
                    params: {
                        r: 'plugin/exchange/mall/card-goods/library-all',
                    },
                }).then(e => {
                    this.library_loading = false;
                    if (e.data.code === 0) {
                        this.library = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            // 监听子组件事件
            childrenGoods(e) {
                let that = this;
                if(e.plugin_data.library.library_id > 0) {
                    let check = setInterval(()=>{
                        if(that.library.length > 0) {
                            clearInterval(check);
                            for(let item of that.library) {
                                if(e.plugin_data.library.library_id == item.id) {
                                    that.library_id = item.id
                                }
                            }
                        }
                    },500)
                }
            },
        }

    });
</script>
