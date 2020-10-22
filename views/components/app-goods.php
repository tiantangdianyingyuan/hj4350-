<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/6
 * Time: 11:02
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('goods/app-dialog-select');
Yii::$app->loadViewComponent('goods/app-attr');
Yii::$app->loadViewComponent('goods/app-attr-select');
Yii::$app->loadViewComponent('goods/app-add-cat');
Yii::$app->loadViewComponent('goods/app-add-ecard');
Yii::$app->loadViewComponent('goods/app-select-card');
Yii::$app->loadViewComponent('goods/app-select-goods');
Yii::$app->loadViewComponent('goods/app-area-limit');
Yii::$app->loadViewComponent('goods/app-preview');
Yii::$app->loadViewComponent('goods/app-attr-group');
Yii::$app->loadViewComponent('app-goods-form', __DIR__ . '/goods');
Yii::$app->loadViewComponent('app-shipping-rules', __DIR__ . '/goods');
Yii::$app->loadViewComponent('app-goods-share', __DIR__ . '/goods');
Yii::$app->loadViewComponent('goods/app-select-coupon');

?>
<style>
    .mt-24 {
        margin-bottom: 24px;
    }

    .app-goods .el-form-item__label {
        padding: 0 20px 0 0;
    }

    .app-goods .el-dialog__body h3 {
        font-weight: normal;
        color: #999999;
    }

    .app-goods .form-body {
        padding: 10px 20px 20px;
        background-color: #fff;
        margin-bottom: 30px;
    }

    .app-goods .button-item {
        padding: 9px 25px;
        margin-bottom: 10px;
    }

    .app-goods .sortable-chosen {
        /* border: 2px solid #3399ff; */
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .app-goods .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .app-goods .app-share .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }
    .app-guarantee-bg {
        position: relative;
        width: 280px;
        height: 500px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }
    .app-goods .app-share .app-share-bg .title {
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

    .app-goods .app-share .app-share-bg .pic-image {
        background-repeat: no-repeat;
        background-position: 0 0;
        background-size: cover;
        width: 160px;
        height: 130px;
    }

    .bottom-div {
        border-top: 1px solid #E3E3E3;
        position: fixed;
        bottom: 0;
        background-color: #ffffff;
        z-index: 999;
        padding: 10px;
        width: 80%;
    }

    .app-goods .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    .app-goods .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    .app-goods .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .app-goods .share-title {
        font-size: 16px;
        color: #303133;
        padding-bottom: 22px;
        border-bottom: 1px solid #e2e2e2;
    }

    .box-grow-0 {
        /* flex 子元素固定宽度*/
        min-width: 0;
        -webkit-box-flex: 0;
        -webkit-flex-grow: 0;
        -ms-flex-positive: 0;
        flex-grow: 0;
        -webkit-flex-shrink: 0;
        -ms-flex-negative: 0;
        flex-shrink: 0;
    }

    .app-goods-extra:before {
        content: '*';
        color: #F56C6C;
        margin-right: 4px;
    }
    .goods-types {
        width: 208px;
        height: 74px;
        cursor: pointer;
        position: relative;
        margin-right: 15px;
    }
    .goods-types.active {
        position: absolute;
    }
    .red {
        color: #F56C6C;
        margin-left: 10px;
    }
    .title-input .el-input__inner {
        padding: 0 65px 0 15px;
    }

    .title-input .el-textarea__inner {
        padding: 5px 15px;
    }

    .title-input .el-textarea .el-textarea__inner{
        resize: none;
    }

    .pintuan-share .el-card .el-card__body {
        padding: 0;
    }

    .pintuan-share .el-card {
        box-shadow: none;
        border: none;
        margin-bottom: 20px;
    }
    .pintuan-share .el-card__body {
        padding: 0;
    }
    .member-level .member-item.el-form-item {
        margin-bottom: 0;
    }
</style>

<template id="app-goods">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="app-goods"
             v-loading="cardLoading">
        <div class='form-body'>

            <el-form :model="cForm" :rules="cRule" ref="ruleForm" label-width="180px" size="small"
                     class="demo-ruleForm">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="商品设置" name="first" v-if="is_basic == 1">
                        <el-card v-if="is_ecard == 1 && is_e_card == 1 && is_mch == 0" shadow="never" class="mt-24">
                            <div slot="header">
                                <span>选择商品类型</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <div flex="">
                                        <image @click="setEcard('goods')" class="goods-types" src="statics/img/mall/goods/good-types.png"></image>
                                        <image @click="setEcard('ecard')" class="goods-types" src="statics/img/mall/goods/ecard-types.png"></image>
                                        <image class="goods-types active" :style="{left: ruleForm.type == 'goods' ? '0px' : '223px'}" src="statics/img/mall/goods/active-types.png"></image>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-card>
                        <el-card v-if="ruleForm.type == 'ecard' && is_e_card == 1 && is_mch == 0 && is_ecard == 1" shadow="never" class="mt-24">
                            <div slot="header">
                                <span>选择卡密</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="卡密名称" required>
                                       <div flex="dir:left">
                                           <div flex="dir:top" v-if="ruleForm.plugin_data.ecard.name">
                                               <el-tag style="margin-right: 5px;margin-bottom:5px"
                                                       type="warning" :closable="is_edit==0" disable-transitions
                                                       @close="destroyEcard()"
                                               >{{ruleForm.plugin_data.ecard.name}}
                                               </el-tag>
                                               <span style="color: #ff4544;font-size: 14px;" v-if="ruleForm.plugin_data.ecard.stock < 50">库存少于50</span>
                                           </div>
                                           <div>
                                              <el-button :disabled="is_edit == 1" type="primary" @click="$refs.ecard.openDialog()">选择卡密</el-button>
                                           </div>
                                           <div style="margin-left: 10px;">
                                               <el-button type="text" @click="$navigate({r:'plugin/ecard/mall/index/edit'}, true)">添加卡密
                                               </el-button>
                                           </div>
                                           <app-add-ecard ref="ecard"
                                                          :ecard_url="ecard_url"
                                                          @select="selectCard">
                                           </app-add-ecard>
                                       </div>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>
                        <!-- 选择分类 -->
                        <slot name="before_cats"></slot>

                        <!-- 选择分类 -->
                        <el-card v-if="is_cats === 1" shadow="never" class="mt-24">
                            <div slot="header">
                                <span>选择分类</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="商品分类" prop="cats">
                                        <el-tag style="margin-right: 5px;margin-bottom:5px" v-for="(item,index) in cats"
                                                :key="index" type="warning" closable disable-transitions
                                                @close="destroyCat(item,index)"
                                        >{{item.label}}
                                        </el-tag>
                                        <el-button type="primary" @click="$refs.cats.openDialog()">选择分类</el-button>
                                        <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                        </el-button>
                                        <app-add-cat ref="cats" :new-cats="ruleForm.cats"
                                                     @select="selectCat"></app-add-cat>
                                    </el-form-item>
                                    <!-- mch -->
                                    <el-form-item v-if="is_mch" label="多商户分类" prop="mchCats">
                                        <el-tag style="margin-right: 5px" v-for="(item,index) in mchCats"
                                                :key="item.value" v-model="ruleForm.mchCats" type="warning" closable
                                                disable-transitions @close="destroyCat_2(item.value,index)"
                                        >{{item.label}}
                                        </el-tag>
                                        <el-button type="primary" @click="$refs.mchCats.openDialog()">选择分类</el-button>
                                        <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                        </el-button>
                                        <app-add-cat ref="mchCats" :new-cats="ruleForm.mchCats" :mch_id="mch_id"
                                                     @select="selectMchCat"></app-add-cat>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="before_info"></slot>

                        <!-- 商品设置 -->
                        <el-card shadow="never" class="mt-24" v-if="is_product_setting === 1">
                            <div slot="header">
                                <span>商品设置</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <template v-if="is_info == 1 || sign === 'exchange'">

                                        <!-- 淘宝采集 -->
                                        <el-form-item label="淘宝采集" hidden>
                                            <el-input v-model="copyUrl">
                                                <template slot="append">
                                                    <el-button @click="copyGoods" :loading="copyLoading">获取
                                                    </el-button>
                                                </template>
                                            </el-input>
                                        </el-form-item>

                                        <!-- 商城商品编码 -->
                                        <el-form-item prop="number" v-if="is_copy_id === 1 && is_edit === 0">
                                            <template slot="label">
                                                <span>商城商品编码</span>
                                                <el-tooltip effect="dark" placement="top"
                                                            content="只能从商城中获取商品信息，且基本信息与商城商品保持一致">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input v-model="copyId" type="number" min="0"
                                                      oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                      placeholder="请输入商城商品id">
                                                <template slot="append">
                                                    <el-button @click="getDetail(copyId)" :loading="copyLoading">获取
                                                    </el-button>
                                                </template>
                                            </el-input>
                                        </el-form-item>

                                        <!-- 商品名称 -->
                                        <el-form-item label="商品名称" v-if="is_name === 1" prop="name" class="title-input"
                                        >
                                            <el-input v-model="ruleForm.name"
                                                      maxlength="100"
                                                      show-word-limit
                                            ></el-input>
                                        </el-form-item>

                                        <el-form-item label="商品名称" class="title-input" v-else>
                                            <el-input v-model="ruleForm.name"
                                                      maxlength="100"
                                                      disabled
                                                      show-word-limit
                                            ></el-input>
                                        </el-form-item>

                                        <!-- 商品名称 -->
                                        <el-form-item label="副标题" v-if="is_name === 1" class="title-input"
                                        >
                                            <el-input v-model="ruleForm.subtitle"
                                                      maxlength="90"
                                                      type="textarea"
                                                      :rows="4"
                                                      show-word-limit
                                            ></el-input>
                                        </el-form-item>

                                        <el-form-item label="副标题" class="title-input" v-else>
                                            <el-input v-model="ruleForm.subtitle"
                                                      maxlength="90"
                                                      type="textarea"
                                                      :rows="4"
                                                      disabled
                                                      show-word-limit
                                            ></el-input>
                                        </el-form-item>
                                        <!-- 原价 -->
                                        <el-form-item v-if="is_original_price === 1" >
                                            <template slot="label">
                                                <span>原价</span>
                                                <el-tooltip effect="dark" content="以划线形式显示" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>

                                            <el-input type="number" min="0"
                                                      disabled
                                                      oninput="this.value = this.value.replace(/[^0-9\.]/, '');"
                                                      v-model="ruleForm.original_price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>

                                        <!-- 商品轮播图(多张) -->
                                        <el-form-item prop="pic_url" v-if="is_pic_url === 1">
                                            <template slot="label">
                                                <span>商品轮播图(多张)</span>
                                                <el-tooltip effect="dark" placement="top" content="第一张图片为封面图">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <div class="pic-url-remark">
                                                第一张图片为缩略图,其它图片为轮播图,建议像素750*750,可拖拽使其改变顺序，最多支持上传9张
                                            </div>
                                            <div flex="dir:left">
                                                <template v-if="ruleForm.pic_url.length">
                                                    <draggable v-model="ruleForm.pic_url" flex="dif:left">
                                                        <div v-for="(item,index) in ruleForm.pic_url" :key="index"
                                                             style="margin-right: 20px;position: relative;cursor: move;">
                                                            <app-attachment @selected="updatePicUrl"
                                                                            :params="{'currentIndex': index}">
                                                                <app-image mode="aspectFill" width="100px"
                                                                           height='100px' :src="item.pic_url">
                                                                </app-image>
                                                            </app-attachment>
                                                            <el-button class="del-btn" size="mini" type="danger"
                                                                       icon="el-icon-close" circle
                                                                       @click="delPic(index)"></el-button>
                                                        </div>
                                                    </draggable>
                                                </template>
                                                <template v-if="ruleForm.pic_url.length < 9">
                                                    <app-attachment style="margin-bottom: 10px;" :multiple="true"
                                                                    :max="9" @selected="picUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750"
                                                                    placement="top">
                                                            <div flex="main:center cross:center" class="add-image-btn">
                                                                + 添加图片
                                                            </div>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                </template>
                                            </div>
                                        </el-form-item>

                                        <!-- 商品视频 -->
                                        <el-form-item label="商品视频" v-if="is_video_url === 1" prop="video_url">
                                            <el-input v-model="ruleForm.video_url" placeholder="请输入视频原地址或选择上传视频">
                                                <template slot="append">
                                                    <app-attachment :multiple="false" :max="1" @selected="videoUrl"
                                                                    type="video">
                                                        <el-tooltip class="item"
                                                                    effect="dark"
                                                                    content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB"
                                                                    placement="top">
                                                            <el-button size="mini">添加视频</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                </template>
                                            </el-input>
                                            <el-link class="box-grow-0" type="primary" style="font-size:12px"
                                                     v-if='ruleForm.video_url' :underline="false" target="_blank"
                                                     :href="ruleForm.video_url">视频链接
                                            </el-link>
                                        </el-form-item>

                                    </template>

                                    <!-- plugins -->
                                    <template v-else>

                                        <!-- 商品信息获取 -->
                                        <el-form-item label="商品信息获取" width="120" v-if="is_product_info === 1">
                                            <label slot="label">
                                                商品信息获取
                                                <el-tooltip class="item" effect="dark"
                                                            content="只能从商城中获取商品信息，且基本信息与商城商品保持一致" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </label>
                                            <div>
                                                <el-row type="flex">
                                                    <el-button type="text" size="medium" style="max-width: 100%;"
                                                               @click="$navigate({r:'mall/goods/edit', id: goods_warehouse.goods_id}, true)"
                                                               v-if="goods_warehouse.goods_id">
                                                        <app-ellipsis :line="1">
                                                            ({{goods_warehouse.goods_id}}){{goods_warehouse.name}}
                                                        </app-ellipsis>
                                                    </el-button>
                                                    <app-select-goods :multiple="false" submit_text="选择" :sign="sign" v-if="is_edit == 0"
                                                                       @selected="selectGoodsWarehouse">
                                                        <el-button>选择商品</el-button>
                                                    </app-select-goods>
                                                </el-row>
                                                <el-button v-if="is_edit == 0" type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                                    商城还未添加商品？点击前往
                                                </el-button>
                                            </div>
                                        </el-form-item>

                                        <el-form-item label="商品名称">
                                            <el-input :value="goods_warehouse.name" :disabled="true"></el-input>
                                        </el-form-item>

                                        <el-form-item label="副标题" class="title-input">
                                            <el-input v-model="ruleForm.subtitle"
                                                      maxlength="90"
                                                      type="textarea"
                                                      :rows="4"
                                                      disabled
                                                      show-word-limit
                                            ></el-input>
                                        </el-form-item>
                                    </template>

                                    <!-- 上架状态 -->
                                    <el-form-item v-if="is_shelves_status === 1" label="上架状态" prop="status">
                                        <el-switch @change="statusChange" :active-value="1" :inactive-value="0"
                                                   v-model="ruleForm.status">
                                        </el-switch>
                                    </el-form-item>

                                    <el-form-item prop="sort">
                                        <template slot="label">
                                            <span>排序</span>
                                            <el-tooltip effect="dark" content="排序值越小排序越靠前" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input type="number" placeholder="请输入排序" min="0"
                                                  oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                  v-model.number="ruleForm.sort">
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="before_attr"></slot>

                        <!-- 价格库存 -->
                        <el-card id="price" shadow="never" class="mt-24" v-if="is_price_inventory === 1">
                            <div slot="header">
                                <span>价格库存</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">

                                    <slot name="before_price" :item="ruleForm"></slot>

                                    <!-- 售价 -->
                                    <el-form-item label="售价" prop="price" v-if="no_price === 1">
                                        <el-input type="number"
                                                  :disabled="is_info == 0 && sign != 'pintuan' && sign !== 'flash_sale' && sign !== 'exchange'"
                                                  oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" :min="0"
                                                  v-model="ruleForm.price">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item v-if="sign === 'exchange'" label="库存" prop="goods_num">
                                        <el-input type="number"
                                                  @input="change($event)"
                                                  oninput="this.value = this.value.replace(/[^0-9]/g, '');" :min="0"
                                                  v-model="ruleForm.goods_num">
                                        </el-input>
                                    </el-form-item>
                                    <slot name="before_original_price" :item="ruleForm"></slot>

                                    <!-- 原价 -->
                                    <el-form-item v-if="sign !== 'exchange'" prop="original_price"  :required="sign ? false : true" >
                                        <template slot="label">
                                            <span>原价</span>
                                            <el-tooltip effect="dark" content="以划线形式显示" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>

                                        <el-input type="number" min="0"
                                                  :disabled="is_info == 0"
                                                  oninput="this.value = this.value.replace(/[^0-9\.]/, '');"
                                                  v-model="ruleForm.original_price">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-form-item>

                                    <slot :item="ruleForm" name="after_original_price"></slot>

                                    <el-form-item v-if="cForm.extra && sign && sign !== 'integral_mall' && sign !== 'miaosha' && sign !== 'booking' && sign !== 'step' && sign !== 'community'" v-for="(item, key, index) in cForm.extra"
                                                  :key="item.id">
                                        <label slot="label">{{item}}</label>
                                        <el-input v-model="ruleForm[key]"></el-input>
                                    </el-form-item>

                                    <!-- 商城 -->
                                    <template v-if="is_info == 1">

                                        <!-- 单位 -->
                                        <el-form-item label="单位" prop="unit">
                                            <el-input v-model="ruleForm.unit" maxlength="4" show-word-limit></el-input>
                                        </el-form-item>

                                        <!-- 成本价 -->
                                        <el-form-item label="成本价" prop="cost_price">
                                            <el-input type="number"
                                                      oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
                                                      v-model="ruleForm.cost_price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>

                                    </template>

                                    <template v-if="is_attr == 1">
                                        <el-form-item label="商品总库存" prop="goods_num">
                                            <el-input type="number" min="0"
                                                      placeholder="请输入商品总库存"
                                                      oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                      :disabled="ruleForm.use_attr == 1 ? true : false"
                                                      v-model="ruleForm.goods_num">
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item  v-if="ruleForm.type == 'goods'" label="默认规格名" prop="attr_default_name">
                                            <el-input :disabled="ruleForm.use_attr == 1"
                                                      placeholder="请输入默认规格名"
                                                      v-model="ruleForm.attr_default_name">
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item v-if="ruleForm.type == 'goods'">
                                            <label slot="label">
                                                <span>商品规格</span>
                                                <el-tooltip class="item" effect="dark" content="如有颜色、尺码等多种规格，请添加商品规格"
                                                            placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </label>
                                            <div style="width:130%">
                                                <app-attr-group @select="makeAttrGroup"
                                                                v-model="attrGroups"></app-attr-group>
                                            </div>
                                            <div v-if="ruleForm.use_attr" style="width:130%;margin-top: 24px;">
                                                <app-attr v-model="ruleForm.attr" :attr-groups="attrGroups"
                                                          :required-extra="extra_attr_require"
                                                          :extra="cForm.extra ? cForm.extra : {}"></app-attr>
                                            </div>
                                        </el-form-item>
                                        <el-form-item v-if="is_show == 1 && !is_mch && ruleForm.type == 'goods'" prop="is_negotiable">
                                            <template slot='label'>
                                                <span>商品面议</span>
                                                <el-tooltip effect="dark" content="如果开启面议，则商品无法在线支付" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-switch :active-value="1"
                                                       :inactive-value="0"
                                                       v-model="ruleForm.is_negotiable">
                                            </el-switch>
                                        </el-form-item>

                                        <!-- 商品货号 -->
                                        <el-form-item label="商品货号">
                                            <el-input :disabled="ruleForm.use_attr == 1 || (sign !== 'mall') ? true : false"
                                                      placeholder="请输入商品货号"
                                                      v-model="ruleForm.goods_no">
                                            </el-input>
                                        </el-form-item>

                                        <!-- 商品重量 -->
                                        <el-form-item label="商品重量" v-if="ruleForm.type == 'goods'">
                                            <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                      placeholder="请输入商品重量"
                                                      :disabled="ruleForm.use_attr == 1 || ( sign !== 'mall')  ? true : false"
                                                      v-model="ruleForm.goods_weight">
                                                <template slot="append">克</template>
                                            </el-input>
                                        </el-form-item>

                                        <slot name="after_goods_weight"></slot>
                                    </template>

                                    <!-- 规格 -->
                                    <el-form-item label="选择规格" v-if="is_attr == 0 && sign !== 'exchange'" required>
                                        <el-row type="flex">
                                            <el-select v-model="select_attr_groups" @change="selectAttrGroups" placeholder="请选择" v-if="is_edit == 0">
                                                <el-option
                                                        v-for="(item, index) in goods_warehouse.attr_list"
                                                        :key="index"
                                                        :label="item"
                                                        :value="index">
                                                </el-option>
                                            </el-select>
                                            <app-ellipsis :line="1" v-else>
                                                <template v-for="item in ruleForm.select_attr_groups">
                                                        <span style="margin: 0 5px;">
                                                        {{item.attr_group_name}}:{{item.attr_name}}
                                                    </span>
                                                </template>
                                            </app-ellipsis>
                                        </el-row>
                                    </el-form-item>

                                    <slot name="before_select_attr_groups"></slot>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="before_goods"></slot>

                        <!-- 购买设置 -->
                        <el-card shadow="never" class="mt-24" v-if="is_purchase_settings === 1">
                            <div slot="header">
                                <span>购买设置</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">

                                    <el-form-item v-if="cForm.buy_extra" v-for="(item, key, index) in cForm.buy_extra"
                                                  :key="item.id">
                                        <label slot="label">{{item}}</label>
                                        <el-input v-model="ruleForm[key]"></el-input>
                                    </el-form-item>

                                    <slot name="before_virtual_sales"></slot>

                                    <el-form-item prop="virtual_sales" v-if="is_virtual_sales === 1">
                                        <template slot='label'>
                                            <span>已出售量</span>
                                            <el-tooltip effect="dark" content="前端展示的销量=实际销量+已出售量" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input  type="text" maxLength="7" oninput="this.value = this.value.replace(/[^0-9]/, '')" min="0" v-model="ruleForm.virtual_sales">
                                            <template slot="append">{{ruleForm.unit}}</template>
                                        </el-input>
                                    </el-form-item>

                                    <slot name="before_services"></slot>

                                    <el-form-item v-if="sign !== 'exchange'" label="商品服务标题">
                                        <el-input v-model="ruleForm.guarantee_title" placeholder="请输入商品服务标题">

                                        </el-input>
                                        <el-button  type="text"
                                                 @click="guarantee.dialog = true;guarantee.type = 'goods_title_example'"
                                                 >查看图例
                                        </el-button>
                                    </el-form-item>

                                    <el-form-item v-if="sign !== 'exchange'" label="商品服务标识">
                                            <el-row>
                                                <el-col :span="4">
                                                    <app-attachment v-model="ruleForm.guarantee_pic" :multiple="false" :max="1">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:28 * 28"
                                                                    placement="top">
                                                            <el-button size="mini">选择图片</el-button>
                                                        </el-tooltip>
                                                    </app-attachment>
                                                </el-col>
                                                <el-col :span="4">
                                                    <el-button size="mini" type="primary" @click="ruleForm.guarantee_pic = guarantee.goods_pic">恢复默认</el-button>
                                                </el-col>
                                            </el-row>


                                        <div class="customize-share-title">
                                            <app-image mode="aspectFill" width='80px' height='80px'
                                                       :src="ruleForm.guarantee_pic ? ruleForm.guarantee_pic : ''"></app-image>
                                            <el-button v-if="ruleForm.guarantee_pic" class="del-btn" size="mini"
                                                       type="danger" icon="el-icon-close" circle
                                                       @click="ruleForm.guarantee_pic = ''"></el-button>
                                        </div>
                                        <el-button @click="guarantee.dialog = true;guarantee.type = 'goods_pic_example'"
                                                   type="text">查看图例
                                        </el-button>
                                    </el-form-item>

                                    <!-- 商品服务 -->
                                    <el-form-item v-if="sign !== 'exchange'" label="商品服务内容">
                                        <template v-if="!defaultServiceChecked">
                                            <el-tag v-for="(service, index) in ruleForm.services"
                                                    @close="serviceDelete(index)" :key="service.id"
                                                    :disable-transitions="true" style="margin:0 10px 10px 0;" closable>
                                                {{service.name}}
                                            </el-tag>
                                            <el-button type="button" size="mini" @click="serviceOpen">选择服务
                                            </el-button>
                                            <el-dialog title="选择商品服务" :visible.sync="service.dialog" width="30%">
                                                <el-card shadow="never" style="max-height: 488px;overflow: auto"
                                                         v-loading="service.loading">
                                                    <el-checkbox-group v-model="service.list" flex="dir:left" style="flex-wrap: wrap">
                                                        <el-checkbox v-for="item in service.services" :label="item"
                                                                     :key="item.id">{{item.name}}
                                                        </el-checkbox>
                                                    </el-checkbox-group>
                                                </el-card>
                                                <div slot="footer" class="dialog-footer">
                                                    <el-button @click="serviceCancel">取 消</el-button>
                                                    <el-button type="primary" @click="serviceConfirm">确 定</el-button>
                                                </div>
                                            </el-dialog>
                                        </template>
                                        <el-checkbox v-model="defaultServiceChecked" @change="defaultService()">默认服务
                                        </el-checkbox>
                                    </el-form-item>

                                    <!-- 运费设置 -->
                                    <el-form-item prop="freight_id"  v-if="ruleForm.type == 'goods' && is_shipping">
                                        <template slot='label'>
                                            <span>运费设置</span>
                                            <el-tooltip effect="dark" content="选择第一项（默认运费）将会根据运费管理的（默认运费）变化而变化"
                                                        placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-tag @close="freightDelete()" v-if="ruleForm.freight"
                                                :key="ruleForm.freight.name" :disable-transitions="true"
                                                style="margin-right: 10px;" closable>
                                            {{ruleForm.freight.name}}
                                        </el-tag>
                                        <el-button type="button" size="mini" @click="freightOpen">选择运费
                                        </el-button>
                                        <el-dialog title="选择运费" :visible.sync="freight.dialog" width="30%">
                                            <el-card shadow="never" flex="dir:left" style="flex-wrap: wrap"
                                                     v-loading="freight.loading">
                                                <el-radio-group v-model="freight.checked">
                                                    <el-radio style="padding: 10px;" v-for="item in freight.list"
                                                              :label="item" :key="item.id">{{item.name}}
                                                    </el-radio>
                                                </el-radio-group>
                                            </el-card>
                                            <div slot="footer" class="dialog-footer">
                                                <el-button @click="freightCancel">取 消</el-button>
                                                <el-button type="primary" @click="freightConfirm">确 定</el-button>
                                            </div>
                                        </el-dialog>
                                    </el-form-item>

                                    <!-- 包邮规则 -->
                                    <el-form-item prop="freight_id" v-if="ruleForm.type == 'goods' && is_shipping_rules　">
                                        <template slot='label'>
                                            <span>包邮规则</span>
                                        </template>
                                        <app-shipping-rules v-model="ruleForm.shipping" @selected="selectShipping"

                                                        url="mall/free-delivery-rules/all-list"></app-shipping-rules>
                                    </el-form-item>

                                    <!-- 自定义表单 -->
                                    <el-form-item prop="freight_id" v-if="is_form == 1">
                                        <template slot='label'>
                                            <span>自定义表单</span>
                                            <el-tooltip effect="dark" content="选择第一项（默认表单）将会根据表单列表的（默认表单）变化而变化"
                                                        placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <app-goods-form v-model="ruleForm.form" @selected="selectForm"
                                                        title="选择表单"
                                                        url="mall/order-form/all-list"></app-goods-form>
                                    </el-form-item>

                                    <slot name="before_confine_count"></slot>

                                    <!-- 限购数量 -->
                                    <el-form-item label="限购数量" prop="confine_count">
                                        <div flex="dir:left">
                                            <span class="box-grow-0" style="color:#606266">商品</span>
                                            <div style="width: 100%;margin:0 10px">
                                                <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                          :disabled="ruleForm.confine_count <= -1" placeholder="请输入限购数量"
                                                          v-model="ruleForm.confine_count">
                                                    <template slot="append">件</template>
                                                </el-input>
                                            </div>
                                            <el-checkbox style="margin-left: 5px;" @change="itemChecked"
                                                         v-model="ruleForm.confine_count <= -1">无限制
                                            </el-checkbox>
                                        </div>
                                        <div flex="dir:left" style="margin-top: 10px;">
                                            <span class="box-grow-0" style="color:#606266">订单</span>
                                            <div style="width: 100%;margin:0 10px">
                                                <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                          :disabled="ruleForm.confine_order_count <= -1"
                                                          placeholder="请输入限购数量"
                                                          v-model="ruleForm.confine_order_count">
                                                    <template slot="append">单</template>
                                                </el-input>
                                            </div>
                                            <el-checkbox style="margin-left: 5px;" @change="itemOrderChecked"
                                                         v-model="ruleForm.confine_order_count <= -1">无限制
                                            </el-checkbox>
                                        </div>
                                    </el-form-item>

<!--                                    <el-form-item label="" prop="pieces" v-if="ruleForm.type == 'goods' && sign !== 'exchange'">-->
<!--                                        <template slot='label'>-->
<!--                                            <span>单品满件包邮</span>-->
<!--                                            <el-tooltip effect="dark" content="如果设置0或空，则不支持满件包邮" placement="top">-->
<!--                                                <i class="el-icon-info"></i>-->
<!--                                            </el-tooltip>-->
<!--                                        </template>-->
<!--                                        <el-input type="number" min="0"-->
<!--                                                  oninput="this.value = this.value.replace(/[^0-9]/g, '');"-->
<!--                                                  placeholder="请输入数量" v-model="ruleForm.pieces">-->
<!--                                            <template slot="append">件</template>-->
<!--                                        </el-input>-->
<!--                                    </el-form-item>-->
<!---->
<!--                                    <el-form-item prop="forehead" v-if="ruleForm.type == 'goods' && sign !== 'exchange'">-->
<!--                                        <template slot='label'>-->
<!--                                            <span>单品满额包邮</span>-->
<!--                                            <el-tooltip effect="dark" content="如果设置0或空，则不支持满额包邮" placement="top">-->
<!--                                                <i class="el-icon-info"></i>-->
<!--                                            </el-tooltip>-->
<!--                                        </template>-->
<!---->
<!--                                        <el-input type="number"-->
<!--                                                  oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" min="0"-->
<!--                                                  placeholder="请输入金额" v-model="ruleForm.forehead">-->
<!--                                            <template slot="append">元</template>-->
<!--                                        </el-input>-->
<!--                                    </el-form-item>-->

                                    <slot name="before_area_limit"></slot>

                                    <!-- 区域购买 -->
                                    <el-form-item label="区域购买" prop="is_area_limit" v-if="is_area_limit == 1 && ruleForm.type == 'goods' && sign !='community'">
                                        <el-switch
                                                v-model="ruleForm.is_area_limit"
                                                :active-value="1"
                                                :inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item v-if="is_area_limit == 1 && ruleForm.is_area_limit" label="允许购买区域" prop="area_limit">
                                        <app-area-limit v-model="ruleForm.area_limit"></app-area-limit>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <!-- 显示设置 todo -->
                        <el-card shadow="never" class="mt-24" v-if="is_display_setting === 1">
                            <div slot="header">
                                <span>显示设置</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="添加到快速购买" prop="is_quick_shop">
                                        <el-switch :active-value="1" :inactive-value="0"
                                                   v-model="ruleForm.is_quick_shop">
                                        </el-switch>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="before_marketing"></slot>

                        <!-- 营销设置 -->
                        <el-card shadow="never" class="mt-24" v-if="is_marketing === 1 && mch_id == 0">
                            <div slot="header">
                                <span>营销设置</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item>
                                        <template slot='label'>
                                            <span>积分赠送</span>
                                            <el-tooltip effect="dark" placement="top">
                                                <div slot="content">用户购物赠送的积分, 如果不填写或填写0，则默认为不赠送积分，
                                                    如果为百分比则为按成交价格的比例计算积分"<br/>
                                                    如: 购买2件，设置10 积分, 不管成交价格是多少， 则购买后获得20积分</br>
                                                    如: 购买2件，设置10%积分, 成交价格2 * 200= 400， 则购买后获得 40 积分（400*10%）
                                                </div>
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input type="number" min="0"
                                                  oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                                                  placeholder="请输入赠送积分数量" v-model="ruleForm.give_integral">
                                            <template slot="append">
                                                {{ruleForm.give_integral_type === 1 ? '分' : '%'}}
                                                <el-radio v-model="ruleForm.give_integral_type" :label="1">固定值
                                                </el-radio>
                                                <el-radio v-model="ruleForm.give_integral_type" :label="2">百分比
                                                </el-radio>
                                            </template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item v-if="is_forehead_integral === 1">
                                        <template slot='label'>
                                            <span>积分抵扣</span>
                                            <el-tooltip effect="dark" content="如果设置0，则不支持积分抵扣 如果带%则为按成交价格的比例计算抵扣多少元"
                                                        placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input type="number" min="0"
                                                  oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                                                  placeholder="请输最高抵扣金额"
                                                  v-model="ruleForm.forehead_integral">
                                            <template slot="prepend">最多抵扣</template>
                                            <template slot="append">
                                                {{ruleForm.forehead_integral_type === 1 ? '元' : '%'}}
                                                <el-radio v-model="ruleForm.forehead_integral_type" :label="1">固定值
                                                </el-radio>
                                                <el-radio v-model="ruleForm.forehead_integral_type" :label="2">百分比
                                                </el-radio>
                                            </template>
                                        </el-input>
                                        <el-checkbox :true-label="1" :false-label="0"
                                                     v-model="ruleForm.accumulative">
                                            允许多件累计抵扣
                                        </el-checkbox>
                                    </el-form-item>

                                    <el-form-item>
                                        <template slot='label'>
                                            <span>余额赠送</span>
                                        </template>
                                        <el-input type="number" min="0"
                                                  oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3')"
                                                  placeholder="请输入赠送余额" v-model="ruleForm.give_balance">

                                            <template slot="append">
                                                {{ruleForm.give_balance_type === 1 ? '元' : '%'}}
                                                <el-radio v-model="ruleForm.give_balance_type" :label="1">固定值</el-radio>
                                                <el-radio v-model="ruleForm.give_balance_type" :label="2">百分比</el-radio>
                                            </template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item label="卡券发放" v-if="sign != 'mch' && sign != 'exchange'">
                                        <el-tag v-for="(card, index) in ruleForm.cards" @close="cardDelete(index)"
                                                :key="index" :disable-transitions="true"
                                                style="margin: 0 10px 10px 0;" closable>
                                            {{card.num}}张 | {{card.name}}
                                        </el-tag>
                                        <el-button type="button" size="mini" @click="cardDialogVisible = true">新增卡券
                                        </el-button>
                                    </el-form-item>

                                    <el-form-item label="优惠券发放" v-if="is_mch !== 1">

                                        <el-tag v-for="(coupon, index) in ruleForm.coupons" @close="couponDelete(index)"
                                                :key="index" :disable-transitions="true"
                                                style="margin: 0 10px 10px 0;" closable>
                                            {{coupon.send_num}}张 | {{coupon.name}}
                                        </el-tag>
                                        <div style="display: inline-block">
                                            <app-select-coupon v-model="ruleForm.coupons" @select="couponSubmit">
                                                <el-button type="button" size="mini">选择优惠券
                                                </el-button>
                                            </app-select-coupon>
                                        </div>

                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="before_share"></slot>

                        <!-- 分享设置 -->
                        <el-card shadow="never" class="mt-24" v-if="is_sharing_setting === 1">
                            <div slot="header">
                                <span>分享设置</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item prop="app_share_title">
                                        <label slot="label">
                                            <span>自定义分享标题</span>
                                            <el-tooltip class="item" effect="dark" content="分享给好友时，作为商品名称"
                                                        placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </label>
                                        <el-input placeholder="请输入分享标题"
                                                  v-model="ruleForm.app_share_title"></el-input>
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
                                        <app-attachment v-model="ruleForm.app_share_pic" :multiple="false" :max="1">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                                        placement="top">
                                                <el-button size="mini">选择图片</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <div class="customize-share-title">
                                            <app-image mode="aspectFill" width='80px' height='80px'
                                                       :src="ruleForm.app_share_pic ? ruleForm.app_share_pic : ''"></app-image>
                                            <el-button v-if="ruleForm.app_share_pic" class="del-btn" size="mini"
                                                       type="danger" icon="el-icon-close" circle
                                                       @click="ruleForm.app_share_pic = ''"></el-button>
                                        </div>
                                        <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                                                   type="text">查看图例
                                        </el-button>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="before_detail"></slot>

                        <!-- 商品详情 -->
                        <el-card shadow="never" class="mt-24" v-if="is_detail === 1">
                            <div slot="header">
                                <span>商品详情</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <app-rich-text style="width: 750px" v-model="ruleForm.detail"></app-rich-text>
                                </el-col>
                            </el-row>
                        </el-card>

                        <slot name="after_detail"></slot>

                    </el-tab-pane>

                    <slot name="before_basic_tab_pane"></slot>

                    <el-tab-pane label="分销价设置" name="there" v-if="is_show_share">
                        <el-form-item :label="is_mch ? '是否开启分销佣金' : '是否开启单独分销'" prop="individual_share">
                            <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.individual_share">
                            </el-switch>
                        </el-form-item>
                        <template v-if="ruleForm.individual_share == 1">
                            <el-form-item label="分销类型" prop="attr_setting_type" v-if="cForm.use_attr == 1 && ruleForm.type == 'goods'">
                                <el-radio v-model="ruleForm.attr_setting_type" :label="0">普通设置</el-radio>
                                <el-radio v-model="ruleForm.attr_setting_type" :label="1">详细设置</el-radio>
                            </el-form-item>
                            <el-form-item label="分销佣金类型" prop="share_type">
                                <el-radio v-model="ruleForm.share_type" :label="0">固定金额</el-radio>
                                <el-radio v-model="ruleForm.share_type" :label="1">百分比</el-radio>
                            </el-form-item>

                            <template v-if="sign !== 'pintuan'">
                                <app-goods-share v-model="ruleForm" :is_mch="is_mch" :attr-groups="attrGroups"
                                                 :attr_setting_type="cForm.attr_setting_type"
                                                 :share_type="ruleForm.share_type"
                                                 :use_attr="ruleForm.use_attr"
                                                 :sign="sign"
                                                 pintuan_sign="单独购买"
                                                 ></app-goods-share>
                            </template>
                            <template v-if="sign === 'pintuan'">
                                <template v-if="cForm.is_alone_buy">
                                    <app-goods-share v-model="ruleForm" :is_mch="is_mch" :attr-groups="attrGroups"
                                                     :attr_setting_type="cForm.attr_setting_type"
                                                     :share_type="ruleForm.share_type"
                                                     :use_attr="ruleForm.use_attr"
                                                     :sign="sign"
                                                     pintuan_sign="单独购买"
                                    ></app-goods-share>
                                </template>
                                <template v-if="ruleForm.group_list.length > 0">
                                    <div class="pintuan-share">
                                        <el-card v-for="(item, index) in ruleForm.group_list" :key="index">
                                            <app-goods-share v-model="ruleForm.group_list[index]" :attr-groups="attrGroups"
                                                             :attr_setting_type="ruleForm.attr_setting_type"
                                                             :share_type="ruleForm.share_type"
                                                             :use_attr="ruleForm.use_attr"
                                                             :sign="sign"
                                                             :pintuan_sign="item.people_num + '人团'"
                                            ></app-goods-share>
                                        </el-card>
                                    </div>
                                </template>
                                <template v-else>
                                    <el-form-item>
                                        <el-tag type="danger">请先添加阶梯团</el-tag>
                                    </el-form-item>
                                </template>
                            </template>
                        </template>
                    </el-tab-pane>

                    <el-tab-pane label="会员价设置" name="third" v-if="is_member === 1">

                        <el-form-item v-if="is_svip" label="是否享受超级会员功能" prop="is_vip_card_goods">
                            <el-switch v-model="is_vip_card_goods" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>

                        <el-form-item label="是否享受会员功能" prop="is_level">
                            <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.is_level">
                            </el-switch>

                           <slot name="member_route_setting"></slot>

                        </el-form-item>

                        <template v-if="ruleForm.is_level == 1">
                            <el-form-item label="是否单独设置会员价" prop="is_level_alone">
                                <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.is_level_alone">
                                </el-switch>
                            </el-form-item>
                            <template v-if="ruleForm.is_level_alone == 1">

                                <template v-if="ruleForm.use_attr == 1 && memberLevel.length > 0">
                                    <!--多规格会员价设置-->
                                    <template v-if="sign !== 'pintuan'">
                                        <el-form-item label="会员价设置">
                                            <app-attr v-model="ruleForm.attr" :attr-groups="attrGroups"
                                                      :members="memberLevel" :is-level="true"></app-attr>
                                        </el-form-item>
                                    </template>
                                    <template v-else-if="sign === 'pintuan' && cForm.is_alone_buy == 1">
                                        <div flex="dir:top">
                                            <div style="margin-left: 180px; padding: 24px;border-left: 1px solid #EBEEF5;border-right: 1px solid #EBEEF5;border-top: 1px solid #EBEEF5;">
                                                <el-tag style="float: left;margin-right: 50px" type="danger" >
                                                    单独购买
                                                </el-tag>
                                            </div>
                                            <!--多规格会员价设置-->
                                            <el-form-item>
                                                <app-attr :attr-groups="attrGroups" v-model="ruleForm.attr"
                                                          :is-level="true" :members="memberLevel"></app-attr>
                                            </el-form-item>
                                        </div>
                                    </template>
                                </template>

                                <template v-if="sign !== 'pintuan'">
                                    <!-- 无规格默认会员价 -->
                                    <template v-if="ruleForm.use_attr == 0 && memberLevel.length > 0">
                                        <el-form-item label="默认规格会员价设置">
                                            <el-col :xl="12" :lg="16">
                                                <el-input v-for="item in defaultMemberPrice" :key="item.id" type="number"
                                                          oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3')"
                                                          v-model="ruleForm.member_price[item.level]">
                                                    <span slot="prepend">{{item.name}}</span>
                                                    <span slot="append">元</span>
                                                </el-input>
                                            </el-col>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-tag type="danger">如需设置多规格会员价,请先添加商品规格</el-tag>
                                        </el-form-item>
                                    </template>
                                </template>

                                <template v-else-if="sign === 'pintuan' && cForm.is_alone_buy == 1">
                                    <!-- 无规格默认会员价 -->
                                    <template v-if="ruleForm.use_attr == 0 && memberLevel.length > 0">
                                        <el-form-item label="默认规格会员价设置">
                                            <el-col :xl="12" :lg="16">
                                                <el-input v-for="item in defaultMemberPrice" :key="item.id" type="number"
                                                          oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3')"
                                                          v-model="ruleForm.member_price[item.level]">
                                                    <span slot="prepend">{{item.name}}</span>
                                                    <span slot="append">元</span>
                                                </el-input>
                                            </el-col>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-tag type="danger">如需设置多规格会员价,请先添加商品规格</el-tag>
                                        </el-form-item>
                                    </template>
                                </template>

                                <template v-if="sign === 'pintuan'">
                                    <template v-if="pintuan_list.group_list.length > 0">
                                        <div  class="pintuan-share">
                                            <el-card v-for="(item, index) in pintuan_list.group_list">

                                                <template v-if="ruleForm.use_attr == 1 && memberLevel.length > 0">
                                                    <div flex="dir:top">
                                                        <div style="margin-left: 180px; padding: 16px; border-left: 1px solid #EBEEF5;border-right: 1px solid #EBEEF5;border-top: 1px solid #EBEEF5;">
                                                            <el-tag style="float: left;margin-right: 50px" type="danger" >
                                                                {{item.people_num}}人团
                                                            </el-tag>
                                                        </div>
                                                        <!--多规格会员价设置-->
                                                        <el-form-item>
                                                            <app-attr :attr-groups="attrGroups" v-model="item.attr"
                                                                      :is-level="true" :members="memberLevel"></app-attr>
                                                        </el-form-item>
                                                    </div>

                                                </template>
                                                <!-- 无规格默认会员价 -->
                                                <template v-if="ruleForm.use_attr == 0 && memberLevel.length > 0">
                                                    <div flex="dir:top" class="member-level">
                                                        <el-form-item class="member-item">
                                                            <el-col :xl="12" :lg="16"  style="padding: 16px;border-left: 1px solid #EBEEF5;border-right: 1px solid #EBEEF5;border-top: 1px solid #EBEEF5;">
                                                                <el-tag style="float: left;margin-right: 50px" type="danger" >
                                                                    {{item.people_num}}人团
                                                                </el-tag>
                                                            </el-col>
                                                        </el-form-item>
                                                        <el-form-item>
                                                            <el-col :xl="12" :lg="16">
                                                                <el-input v-for="(mItem, mIndex) in defaultMemberPrice"
                                                                          :key="mItem.id"
                                                                          type="number"
                                                                          v-model="item.member_price[mItem.level]">
                                                                    <span slot="prepend">{{mItem.name}}</span>
                                                                    <span slot="append">元</span>
                                                                </el-input>
                                                            </el-col>
                                                        </el-form-item>
                                                        <el-form-item>
                                                            <el-tag type="danger">如需设置多规格会员价,请先添加商品规格</el-tag>
                                                        </el-form-item>
                                                    </div>
                                                </template>

                                                <el-form-item v-if="memberLevel.length == 0" label="会员价设置">
                                                    <el-button type="danger"
                                                               @click="$navigate({r: 'mall/mall-member/edit'})">
                                                        如需设置,请先添加会员
                                                    </el-button>
                                                </el-form-item>
                                            </el-card>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <el-form-item v-if="ruleForm.is_level == 1">
                                            <el-tag type="danger">请先添加阶梯团</el-tag>
                                        </el-form-item>
                                    </template>
                                </template>
                                <el-form-item v-if="memberLevel.length == 0" label="会员价设置">
                                    <el-button type="danger" @click="$navigate({r: 'mall/mall-member/edit'})">
                                        如需设置,请先添加会员
                                    </el-button>
                                </el-form-item>
                            </template>
                        </template>


                    </el-tab-pane>

                    <slot name="tab_pane"></slot>
                </el-tabs>
            </el-form>

            <div class="bottom-div" flex="cross:center" v-if="is_save_btn == 1">
                <el-button class="button-item" :loading="btnLoading" type="primary" size="small"
                           @click="store('ruleForm')">保存
                </el-button>
                <el-button class="button-item" size="small" @click="showPreview">预览</el-button>
            </div>
        </div>

        <app-preview ref="preview" :rule-form="ruleForm" @submit="store('ruleForm')" :preview-info="previewInfo">
            <template slot="preview">
                <slot name="preview"></slot>
            </template>
            <template slot="preview_end">
                <slot name="preview_end"></slot>
            </template>
        </app-preview>

        <app-select-card :is-show="cardDialogVisible" :rule-form="ruleForm" @select="cardSubmit"></app-select-card>

        <!-- 自定义 -->
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
        <!-- 自定义 -->
        <el-dialog :title="guarantee['type'] == 'goods_pic_example' ? `查看商品服务标识图例`:`查看商品服务标题图例`"
                   :visible.sync="guarantee.dialog" width="30%">
            <div flex="dir:left main:center" class="app-share">
                <div class="app-guarantee-bg"
                     :style="{backgroundImage: 'url('+guarantee[guarantee.type]+')'}"></div>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button @click="guarantee.dialog = false" type="primary">我知道了</el-button>
            </div>
        </el-dialog>
    </el-card>
</template>

<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>

<script>
    Vue.component('app-goods', {
        template: '#app-goods',
        props: {
            no_price: {
                type: Number,
                default: 1
            },
            sign_cn: '',

            // 基本信息
            is_basic: {
                type: Number,
                default: 1
            },
            // 商品设置
            is_info: {
                type: Number,
                default: 0
            },
            // 是否可自定义规格
            is_attr: {
                type: Number,
                default: 1
            },
            // 购买设置
            is_goods: {
                type: Number,
                default: 1
            },

            // 显示设置
            is_show: {
                type: Number,
                default: 1
            },

            // 商品详情
            is_detail: {
                type: Number,
                default: 0
            },

            // 分销价设置
            is_share: {
                type: Number,
                default: 1
            },


            //todo 积分商城 规格必填项(有更好解决方案？)
            extra_attr_require: {
                type: Array,
                default: function () {
                    return [];
                },
            },
            // 售价栏的label文字显示
            price_label:{
                type: String,
                default: '售价'
            },

            //todo 仅显示售价（抽奖） 秒杀3显示
            is_price: {
                type: Number,
                default: 0
            },

            // 请求数据地址
            url: {
                type: String,
                default: 'mall/goods/edit'
            },
            // 请求数据地址
            get_goods_url: {
                type: String,
                default: 'mall/goods/edit'
            },
            // 保存之后返回地址
            referrer: {
                default: 'mall/goods/index'
            },
            is_mch: {
                type: Number,
                default: 0
            },
            is_forehead_integral: {
                type: Number,
                default: 1
            },

            is_copy_id: {
                type: Number,
                default: 1
            },

            // 商品原价
            is_original_price: {
                type: Number,
                default: 0
            },
            is_name: {
                type: Number,
                default: 1
            },
            mch_id: {
                type: Number,
                default: 0
            },
            // 页面上数据
            form: {
                type: Object,
            },
            // 数据验证方式
            rule: Object,
            status_change_text: {
                type: String,
                default: '',
            },
            // 是否使用表单
            is_form: {
                type: Number,
                default: 1
            },
            sign: String,
            position: String,
            is_save_btn: {
                type: Number,
                default: 1
            },
            previewInfo: {
                type: Object,
                default: function () {
                    return {
                        is_head: true,
                        is_cart: true,
                        is_mch: this.is_mch == 1
                    }
                }
            },
            // 是否是编辑状态
            is_edit: {
                type: Number,
                default: 0
            },

            // 已出售量显示
            is_virtual_sales: {
                type: Number,
                default: 1
            },

            // 购买设置
            is_purchase_settings: {
                type: Number,
                default: 1
            },

            // 价格库存
            is_price_inventory: {
                type: Number,
                default: 1
            },

            // 营销设置
            is_marketing: {
                type: Number,
                default: 1
            },

            // 显示设置
            is_display_setting: {
                type: Number,
                default: 1
            },

            // 会员设置
            is_member: {
                type: Number,
                default: 1
            },

            // 分享设置
            is_sharing_setting: {
                type: Number,
                default: 1
            },

            // 商品视频
            is_video_url: {
                type: Number,
                default: 1
            },

            // 选择分类
            is_cats: {
                type: Number,
                default: 1
            },

            // 商品设置
            is_product_setting: {
                type: Number,
                default: 1
            },

            // 商品轮播图
            is_pic_url: {
                type: Number,
                default: 1
            },

            // 上架状态
            is_shelves_status: {
                type: Number,
                default: 1
            },

            // 商品信息获取
            is_product_info: {
                type: Number,
                default: 1
            },
            is_e_card: {
                type: Number,
                default: 0
            },

            is_shipping: {
                type: Number,
                default: 1
            },

            is_shipping_rules: {
                type: Number,
                default: 1
            },
            is_area_limit: {
                type: Number,
                default: 1,
            },
            library_id: {
                type: String
            }
        },
        data() {
            let ruleForm = {
                attr: [],
                cats: [],
                mchCats: [], //多商户系统分类
                cards: [],
                services: [],
                pic_url: [],
                use_attr: 0,
                goods_num: 0,
                status: 0,
                unit: '件',
                virtual_sales: 0,
                cover_pic: '',
                sort: 100,
                accumulative: 0,
                confine_count: -1,
                confine_order_count: -1,
                forehead: 0,
                forehead_integral: 0,
                forehead_integral_type: 1,
                freight_id: 0,
                freight: null,
                give_integral: 0,
                give_integral_type: 1,
                individual_share: 0,
                is_level: 1,
                is_level_alone: 0,
                pieces: 0,
                share_type: 0,
                attr_setting_type: 0,
                video_url: '',
                is_quick_shop: 0,
                is_sell_well: 0,
                is_negotiable: 0,
                name: '',
                subtitle: '',
                price: '0',
                original_price: '0',
                cost_price: 0,
                detail: '',
                extra: '',
                app_share_title: '', //自定义分享标题,
                app_share_pic: '', //自定义分享图片
                is_default_services: 1,
                member_price: {},
                goods_no: '',
                goods_weight: '',
                select_attr_groups: [], // 已选择的规格
                goodsWarehouse_attrGroups: [], // 商品库商品所有的规格
                share_level_type: 0,
                shareLevelList: [],
                form: null,
                shipping: null,
                shipping_id: 0,
                form_id: 0,
                attr_default_name: '',
                is_area_limit: 0,
                area_limit: [{list: []}],
                type: 'goods',
                plugin_data: {
                    ecard: {
                        ecard_id: 0,
                        ecard_name: ''
                    }
                },
                min_price: '',
                coupons: [],
                give_balance_type: 1,
                give_balance: 0,
                guarantee_pic: '',
                guarantee_title: ''
            };
            let rules = {
                cats: [
                    {
                        required: true, type: 'array', validator: (rule, value, callback) => {
                            if (this.ruleForm.cats instanceof Array && this.ruleForm.cats.length > 0) {
                                callback();
                            }
                            callback('请选择分类');
                        }
                    }
                ],
                mchCats: [
                    {
                        required: true, type: 'array', validator: (rule, value, callback) => {
                            if (this.ruleForm.mchCats instanceof Array && this.ruleForm.mchCats.length > 0) {
                                callback();
                            }
                            callback('请选择系统分类');
                        }
                    }
                ],
                name: [
                    {required: true, message: '请输入商品名称', trigger: 'change'},
                ],
                price: [
                    {required: true, message: '请输入商品价格', trigger: 'change'}
                ],
                original_price: [
                    {message: '请输入商品原价', trigger: 'change'}
                ],
                cost_price: [
                    {required: false, message: '请输入商品成本价', trigger: 'change'}
                ],
                unit: [
                    {required: true, message: '请输入商品单位', trigger: 'change'},
                    {max: 5, message: '最大为5个字符', trigger: 'change'}
                ],
                goods_num: [
                    // {required: true, message: '请输入商品总库存', trigger: ['change', 'blur']}
                ],
                is_area_limit: [
                    {required: false, type: 'integer', message: '请选择是否开启', trigger: 'blur'}
                ],
                area_limit: [
                    {
                        required: true, type: 'array', validator: (rule, value, callback) => {
                            if (value instanceof Array && value[0]['list'].length === 0) {
                                callback('允许购买区域不能为空');
                            }
                            callback();
                        }
                    }
                ],
                pic_url: [
                    {required: true, message: '请上传商品轮播图', trigger: 'change'}
                ],
            };
            return {
                keyword: '',
                cardLoading: false,
                btnLoading: false,
                dialogLoading: false,
                activeName: 'first',
                ruleForm: ruleForm,
                // 分销层级
                shareLevel: [],
                // 会员等级
                memberLevel: [],
                rules: rules,
                options: [], // 商品分类列表
                mchOptions: [], //多商户商品编辑时使用
                newOptions: [],
                cats: [], //用于前端已选的分类展示
                mchCats: [], //用于前端已选的分类展示 多商户
                cards: [], // 优惠券
                attrGroups: [], //规格组

                attrGroupName: '',
                attrName: [],
                // 批量设置
                batch: {},
                dialogVisible: false, //分类选择弹框
                mchDialogVisible: false,
                is_vip_card_goods: 0,
                is_svip: false,
                goods_warehouse: {},
                copyUrl: '',
                copyLoading: false,
                defaultServiceChecked: true,
                service: {
                    dialog: false,
                    list: [],
                    services: [], // 商品服务列表
                    loading: false
                },
                freight: {
                    dialog: false,
                    list: [],
                    checked: {},
                    loading: false
                },
                copyId: '',
                app_share: {
                    dialog: false,
                    type: '',
                    bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share.png",
                    name_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-name.png",
                    pic_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-pic.png",
                },
                guarantee: {
                    dialog: false,
                    type: '',
                    goods_pic_example: "<?= \Yii::$app->request->hostInfo .
                    \Yii::$app->request->baseUrl?>/statics/img/mall/goods/guarantee/goods-pic-example.png",
                    goods_title_example: "<?= \Yii::$app->request->hostInfo .
                    \Yii::$app->request->baseUrl?>/statics/img/mall/goods/guarantee/goods-title-example.png",
                    goods_pic: "<?= \Yii::$app->request->hostInfo .
                    \Yii::$app->request->baseUrl?>/statics/img/mall/goods/guarantee/goods-pic.png",
                },
                is_show_share: 1,
                video_type: 1,
                cardDialogVisible: false,
                is_ecard: 0,
                ecard_url: '',
                eCardList: [],
                eCardLoading: false,
                select_attr_groups: '',
                pintuan_list: [],
                user_attr: 0
            }
        },
        created() {
            let that = this;
            if (getQuery('id')) {
                that.getDetail(getQuery('id'));
            }
            if(that.position) {
                setTimeout(()=>{
                    document.getElementById(that.position).scrollIntoView(true);
                })
            }
            if (that.is_share == 1) {
                that.getPermissions();
            } else {
                that.is_show_share = 0
            }
            that.getPluginCon();
        },
        watch: {
            'ruleForm.detail': function(newVal, oldVal) {
                this.cForm.detail = newVal
            },
            'attrGroups': {
                handler(newVal, oldVal) {
                    if (!this.cardLoading) {
                        if (this.ruleForm.individual_share == 1) {
                            this.ruleForm.individual_share = 0;
                        }
                    }
                    this.ruleForm.use_attr = newVal.length === 0 ? 0 : 1;
                }
            },
            'ruleForm.is_level': function(newVal, oldVal) {
                if (newVal === 0) {
                    this.ruleForm.is_level_alone = 0;
                }
            },
            pintuan_list: function(newVal) {
                this.ruleForm.group_list = newVal.group_list;
            },
            'ruleForm.use_attr': function(newVal, oldVal) {
                if (newVal === 0) {
                    this.ruleForm.attr_setting_type = 0;
                }
            }
        },
        computed: {
            cForm() {
                let form = {};
                let ruleForm = JSON.parse(JSON.stringify(this.ruleForm));
                if (this.form) {
                    this.pintuan_list = this.form;
                    form = Object.assign(ruleForm, JSON.parse(JSON.stringify(this.form)));
                } else {
                    form = ruleForm;
                }
                if (getQuery('id')) {
                    form.id = getQuery('id')
                }
                return form;
            },
            cRule() {
                return this.rule ? Object.assign({}, this.rules, this.rule) : this.rules;
            },
            isConfineCount() {
                return this.ruleForm.confine_count === -1;
            },

        },
        methods: {
            showPreview() {
                this.$refs.preview.previewGoods();
                this.$emit('handle-preview', this.ruleForm);
            },
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.cats = arr;
                this.$refs.ruleForm.validateField('cats');
            },

            selectMchCat(cats) {
                this.mchCats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.mchCats = arr;
                this.$refs.ruleForm.validateField('mchCats');
            },
            delPic(index) {
                this.ruleForm.pic_url.splice(index, 1);
            },

            catDialogCancel() {
                let that = this;
                that.mchDialogVisible = false;
                that.dialogVisible = false;
                that.ruleForm.cats = [];
                that.ruleForm.mchCats = [];
                if (that.cats.length > 0) {
                    that.cats.forEach(function (row) {
                        that.ruleForm.cats.push(row.value.toString());
                    })
                }
                if (that.mchCats.length > 0) {
                    that.mchCats.forEach(function (row) {
                        that.ruleForm.mchCats.push(row.value.toString());
                    })
                }
            },

            getPermissions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/index/mall-permissions'
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.is_show_share = 0;
                        e.data.data.permissions.forEach(function (item) {
                            if (item === 'share') {
                                self.is_show_share = 1;
                            }
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },

            store(formName) {
                let self = this;
                let isZero = false;
                let time = 1000;
                if (self.cForm.type === 'goods' && self.cForm.plugin_data.ecard) {
                    self.cForm.plugin_data.ecard.ecard_id = 0;
                }
                try {
                    if(self.sign === 'exchange' && !self.library_id) {
                        throw new Error('请选择兑换码库');
                    }
                    self.cForm.attr.map(item => {
                        if (item.price < 0 || item.price === '') {
                            if(self.sign == 'community') {
                                throw new Error('买家购买价不能为空');
                            }else {
                                throw new Error('规格价格不能为空');
                            }
                        }
                        if (item.stock < 0 || item.stock === '') {
                            throw new Error('库存不能为空');
                        }
                        if(self.sign == 'community') {
                            if(item.supply_price < 0 || item.supply_price === '') {
                                throw new Error('团长供货价不能为空');
                            }
                            if(+item.supply_price > +item.price) {
                                throw new Error('团长供货价不能大于买家购买价');
                            }
                            if(self.cForm.use_attr == 1 && item.supply_price == 0 || item.price === 0) {
                                isZero = true;
                            }
                        }
                    })
                } catch (error) {
                    self.$message.error(error.message);
                    return;
                }
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        if (self.is_svip) {
                            self.cForm.is_vip_card_goods = self.is_vip_card_goods
                        } else {
                            delete self.cForm['is_vip_card_goods']
                        }
                        if(self.sign === 'exchange') {
                            self.cForm.library_id = self.library_id
                            self.cForm.goods_num = self.ruleForm.goods_num
                        }
                        if(self.cForm.goods_num == '') {
                            self.cForm.goods_num = 0;
                        }
                        request({
                            params: {
                                r: self.url
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(self.cForm),
                                attrGroups: JSON.stringify(self.attrGroups),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                if(isZero) {
                                    self.$message.success('保存成功，温馨提示："买家购买价"或"团长供货价"为0，建议重新设置');
                                    time = 2000;
                                }else {
                                    self.$message.success(e.data.msg);
                                }
                                setTimeout(()=>{
                                    if (typeof self.referrer === 'object') {
                                        navigateTo(self.referrer)
                                    } else {
                                        navigateTo({
                                            r: self.referrer
                                        })
                                    }
                                },time)
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(() => {
                        });
                    } else {
                        self.$message.error('请填写必填参数');
                        return false;
                    }
                });
            },
            getDetail(id, url = '') {
                let self = this;
                self.cardLoading = true;
                console.log(url)
                request({
                    params: {
                        r: url ? url : this.get_goods_url,
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        let detail = e.data.data.detail;
                        if (detail.coupons.length > 0) {
                            let coupons = [];
                            for (let i = 0; i < detail.coupons.length; i++) {
                                let item = JSON.parse(JSON.stringify(detail.coupons[i]));
                                item.coupon_id = detail.coupons[i].id;
                                item.send_num = detail.coupons[i].num;
                                coupons.push(item);
                            }
                            console.log(coupons);
                            detail.coupons = coupons;
                        }

                        console.log(detail);

                        this.use_attr = detail.use_attr;
                        if(detail['use_attr'] === 0) {
                            detail['attr_groups'] = [];
                        }
                        if (detail.plugin_data && detail.plugin_data.vip_card && detail.plugin_data.vip_card.is_vip_card_goods) {
                            self.is_vip_card_goods = detail.plugin_data.vip_card.is_vip_card_goods
                        }
                        if (this.form && this.form.extra) {
                            for (let i in this.form.extra) {
                                if (detail.use_attr == 1) {
                                    for (let j in detail.attr) {
                                        if (!detail.attr[j][i]) {
                                            detail.attr[j][i] = 0;
                                        }
                                    }
                                }
                                Vue.set(self.ruleForm, i, 0);
                            }
                        }
                        self.cats = detail.cats;
                        if (detail.cats) {
                            let cats = [];
                            for (let i in detail.cats) {
                                cats.push(detail.cats[i].value.toString());
                            }
                            detail.cats = cats;
                        }
                        self.mchCats = detail.mchCats;
                        if (detail.mchCats) {
                            let mchCats = [];
                            for (let i in detail.mchCats) {
                                mchCats.push(detail.mchCats[i].value.toString());
                            }
                            detail.mchCats = mchCats;
                        }
                        self.ruleForm = Object.assign(self.ruleForm, detail);
                        console.log(self.ruleForm);
                        self.attrGroups = e.data.data.detail.attr_groups;
                        self.goods_warehouse = e.data.data.detail.goods_warehouse;

                        self.defaultServiceChecked = !!parseInt(self.ruleForm.is_default_services);
                        self.$emit('goods-success', self.ruleForm);

                    } else {
                        self.$message.error(e.data.msg);
                    }
                    this.$nextTick(() => {
                        self.cardLoading = false;
                    })
                }).catch(e => {
                    self.cardLoading = false;
                });
            },
            // 标签页
            handleClick(tab, event) {
                this.$emit('change-tabs', tab.name);
                if (tab.name == "third") {
                    this.getMembers();
                }
            },
            // 获取商品服务
            getServices() {
                let self = this;
                this.service.loading = true;
                request({
                    params: {
                        r: 'mall/service/options'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.service.loading = false;
                    if (e.data.code == 0) {
                        self.service.services = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            // 设置默认服务
            defaultService() {
                if (this.defaultServiceChecked) {
                    this.ruleForm.is_default_services = 1;
                    this.ruleForm.services = [];
                } else {
                    this.ruleForm.is_default_services = 0;
                }
            },
            // 获取会员列表
            getMembers() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/mall-member/all-member'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.memberLevel = e.data.data.list;
                        let defaultMemberPrice = [];
                        // 以下数据用于默认规格情况下的 会员价设置
                        self.memberLevel.forEach(function (item, index) {
                            let obj = {};
                            obj['id'] = index;
                            obj['name'] = item.name;
                            obj['level'] = 'level' + parseInt(item.level);
                            defaultMemberPrice.push(obj);
                        });
                        self.defaultMemberPrice = defaultMemberPrice;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            // 获取运费规则选项
            getFreight() {
                let self = this;
                this.freight.loading = true;
                request({
                    params: {
                        r: 'mall/postage-rule/all-list'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.freight.loading = false;
                    if (e.data.code == 0) {
                        self.freight.list = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            // 规格组合
            makeAttrGroup(e) {
                let self = this;
                let array = [];
                self.attrGroups.forEach(function (attrGroupItem, attrGroupIndex) {
                    attrGroupItem.attr_list.forEach(function (attrListItem, attrListIndex) {
                        let object = {
                            attr_group_id: attrGroupItem.attr_group_id,
                            attr_group_name: attrGroupItem.attr_group_name,
                            attr_id: attrListItem.attr_id,
                            attr_name: attrListItem.attr_name,
                        };

                        if (!array[attrGroupIndex]) {
                            array[attrGroupIndex] = [];
                        }
                        array[attrGroupIndex].push(object)
                    });
                });

                // 2.属性排列组合
                const res = array.reduce((osResult, options) => {
                    return options.reduce((oResult, option) => {
                        if (!osResult.length) {
                            return oResult.concat(option)
                        } else {
                            return oResult.concat(osResult.map(o => [].concat(o, option)))
                        }
                    }, [])
                }, []);

                // 3.组合结果赋值
                for (let i in res) {
                    const options = Array.isArray(res[i]) ? res[i] : [res[i]];
                    const row = {
                        attr_list: options,
                        stock: 0,
                        price: 0,
                        no: '',
                        weight: 0,
                        pic_url: '',
                        shareLevelList: [],
                    };
                    let extra = {};
                    if (this.form && this.form.extra) {
                        extra = JSON.parse(JSON.stringify(this.form.extra));
                        for (let i in extra) {
                            row[i] = 0;
                        }
                    }
                    // 动态绑定多规格会员价
                    let obj = {};
                    self.memberLevel.forEach(function (memberLevelItem, memberLevelIndex) {
                        let key = 'level' + memberLevelItem.level;
                        obj[key] = 0;
                    });
                    row['member_price'] = obj;
                    // 3-1.已设置数据的优先使用原数据
                    if (self.ruleForm.attr.length) {
                        for (let j in self.ruleForm.attr) {
                            const oldOptions = [];
                            for (let k in self.ruleForm.attr[j].attr_list) {
                                oldOptions.push(self.ruleForm.attr[j].attr_list[k].attr_name)
                            }
                            const newOptions = [];
                            for (let k in options) {
                                newOptions.push(options[k].attr_name)
                            }
                            if (oldOptions.toString() === newOptions.toString()) {
                                row['price'] = self.ruleForm.attr[j].price;
                                row['stock'] = self.ruleForm.attr[j].stock;
                                row['no'] = self.ruleForm.attr[j].no;
                                row['weight'] = self.ruleForm.attr[j].weight;
                                row['pic_url'] = self.ruleForm.attr[j].pic_url;
                                row['extra'] = self.ruleForm.attr[j].extra;
                                if(self.sign == 'community') {
                                    row['supply_price'] = self.ruleForm.attr[j].supply_price;
                                }
                                break

                            }
                        }
                    }
                    res[i] = row;
                }

                self.ruleForm.attr = res;
                this.$emit('set-attr', res, self.attrGroups);
            },

            // 批量设置
            batchAttr(key) {
                let self = this;
                if (self.batch[key] && self.batch[key] >= 0 || key === 'no') {
                    self.ruleForm.attr.forEach(function (item, index) {
                        // 批量设置会员价
                        // 判断字符串是否出现过，并返回位置
                        if (key.indexOf('level') !== -1) {
                            item['member_price'][key] = self.batch[key];
                        } else {
                            item[key] = self.batch[key];
                        }
                    });
                }
            },
            destroyCat(value, index) {
                let self = this;
                self.ruleForm.cats.splice(index, 1);
                self.cats.splice(index, 1)
            },
            destroyCat_2(value, index) {
                let self = this;
                self.ruleForm.mchCats.splice(self.ruleForm.mchCats.indexOf(value), 1)
                self.mchCats.splice(index, 1)
            },
            // 商品视频
            videoUrl(e) {
                if (e.length) {
                    this.ruleForm.video_url = e[0].url;
                }
            },
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function (item, index) {
                        if (self.ruleForm.pic_url.length >= 9) {
                            return;
                        }
                        self.ruleForm.pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            // 是否开启规格
            checkedAttr(e) {
                if (e == 1) {
                    this.attrGroups = [];
                    this.ruleForm.goods_num = this.ruleForm.goods_num ? this.ruleForm.goods_num : 0;
                }
            },
            itemChecked(type) {
                this.ruleForm.confine_count = type ? -1 : 0;
            },
            itemOrderChecked(type) {
                this.ruleForm.confine_order_count = type ? -1 : 0;
            },
            selectGoodsWarehouse(goods_warehouse) {
                this.ruleForm.select_attr_groups = [];
                this.getDetail(goods_warehouse.id, 'mall/goods/edit')
            },
            copyGoods() {
                this.copyLoading = true;
                request({
                    params: {
                        r: 'mall/goods/collect',
                        url: this.copyUrl
                    },
                    method: 'get'
                }).then(e => {
                    this.copyLoading = false;
                    if (e.data.code === 0) {
                        let detail = e.data.data.detail;
                        if (this.form && this.form.extra) {
                            for (let i in this.form.extra) {
                                if (detail.use_attr == 1) {
                                    for (let j in detail.attr) {
                                        if (!detail.attr[j][i]) {
                                            detail.attr[j][i] = 0;
                                        }
                                    }
                                }
                                Vue.set(this.ruleForm, i, 0);
                            }
                        }

                        this.ruleForm = Object.assign(this.ruleForm, detail);
                        this.attrGroups = e.data.data.detail.attr_groups;
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.copyLoading = false;
                });
            },
            updatePicUrl(e, params) {
                this.ruleForm.pic_url[params.currentIndex].id = e[0].id;
                this.ruleForm.pic_url[params.currentIndex].pic_url = e[0].url;
            },
            getOptionName(name) {
                let newName = name;
                let num = 12;
                if (newName.length > num) {
                    newName = newName.substring(0, num) + '...';
                }
                return newName;
            },

            /*---商品服务----*/
            serviceOpen() {
                this.service.dialog = true;
                this.getServices();
            },
            serviceCancel() {
                this.service.dialog = false;
                this.service.list = [];
            },
            serviceConfirm() {
                let self = this;
                let newServices = JSON.parse(JSON.stringify(this.service.list));
                let addServices = [];
                newServices.forEach(function (item, index) {
                    let sign = true;
                    self.ruleForm.services.forEach(function (item2, index2) {
                        if (item.id == item2.id) {
                            sign = false;
                        }
                    });
                    if (sign) {
                        addServices.push(item)
                    }
                });
                this.ruleForm.services = this.ruleForm.services.concat(addServices);
                this.serviceCancel();
            },
            serviceDelete(index) {
                this.ruleForm.services.splice(index, 1);
            },
            /*---运费----*/
            freightOpen() {
                this.freight.dialog = true;
                this.getFreight();
            },
            freightCancel() {
                this.freight.checked = {};
                this.freight.dialog = false;
            },
            freightConfirm() {
                this.ruleForm.freight = JSON.parse(JSON.stringify(this.freight.checked));
                this.ruleForm.freight_id = this.ruleForm.freight.id;
                this.freightCancel();
            },
            freightDelete() {
                this.ruleForm.freight = null;
                this.ruleForm.freight_id = 0;
            },
            cardDelete(index) {
                this.ruleForm.cards.splice(index, 1);
            },
            couponDelete(index) {
                this.ruleForm.coupons.splice(index, 1);
            },
            cardSubmit(cards) {
                this.ruleForm.cards = cards;
            },
            couponSubmit(
                coupons
            ) {
                for (let i = 0; i < coupons.length; i++) {
                    coupons[i].num = coupons[i].send_num;
                    coupons[i].id = coupons[i].coupon_id;
                }
                this.ruleForm.coupons = coupons;
            },
            // 上架状态开关，弹框文字提示
            statusChange(res) {
                if (res && this.status_change_text) {
                    this.$alert(this.status_change_text, '提示', {
                        confirmButtonText: '确定',
                        callback: action => {
                        }
                    });
                }
            },
            selectForm(data) {
                this.ruleForm.form = data;
                this.ruleForm.form_id = data ? data.id : -1;
            },
            selectShipping(data) {
                console.log(data);
                this.ruleForm.shipping = data;
                this.ruleForm.shipping_id = data ? data.id : -1;
            },
            change(e) {
                this.$forceUpdate();
            },
            // 选择卡密
            selectCard(data) {
                this.ruleForm.plugin_data.ecard = {
                    ecard_id: data.id,
                    name: data.name,
                    stock: data.stock
                };
            },

            // 取消卡密
            destroyEcard() {
                this.ruleForm.plugin_data.ecard.ecard_id = 0;
                this.ruleForm.plugin_data.ecard.name = '';
            },

            async getPluginCon() {
                const e = await request({
                    params: {
                        r: `/mall/goods/other-permission`,
                        plugin: this.sign
                    },
                    method: 'get'
                });
                if (e.data.code === 0) {
                    let {is_ecard, ecard_url, is_vip_card} = e.data.data;
                    this.is_ecard = is_ecard;
                    this.ecard_url = ecard_url;
                    this.is_svip = is_vip_card;
                }
            },
            selectAttrGroups(val) {
                this.ruleForm.select_attr_groups = this.cForm.attr[val].attr_list;
                this.ruleForm.price = this.cForm.attr[val].price;
            },

            setEcard(data) {
                if (this.is_edit == 0) {
                    this.ruleForm.type = data;
                    console.log(data);
                    if (data === 'goods') {
                        if (this.use_attr == 1) {
                            this.ruleForm.use_attr = 1;
                        }
                    } else if (data === 'ecard') {
                        this.ruleForm.attr_setting_type = 0;
                        this.ruleForm.use_attr = 0;
                    }
                }
            }
        }
    });
</script>
