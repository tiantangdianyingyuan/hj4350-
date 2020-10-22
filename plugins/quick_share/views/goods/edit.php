<?php
Yii::$app->loadViewComponent('app-goods');
Yii::$app->loadViewComponent('goods/app-select-goods');
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding:0;">
        <div slot="header" style="justify-content:space-between;display: flex">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/quick_share/mall/goods/index'})">发圈素材管理</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加发圈素材</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods :is_share="0"
                   ref="appGoods"
                   :is_basic="0"
                   :is_marketing="0"
                   :is_member="0"
                   :is_attr="0"
                   :is_show="0"
                   :is_goods="0"
                   :is_info="2"
                   :is_show_share_forcibly="0"
                   :form="form"
                   :rule="rule"
                   :is_save_btn="0"
                   url="plugin/quick_share/mall/goods/edit"
                   get_goods_url="plugin/quick_share/mall/goods/edit"
                   referrer="plugin/quick_share/mall/goods/index"
                   @change-tabs="changeTabs"
                   @goods-success="goodsSuccess">

            <template slot="tab_pane">
                <el-tab-pane label="商品" name="first">
                    <el-col :xl="12" :lg="16">
                        <el-form-item label="商品信息获取" width="120" prop="goods_warehouse"
                                      :rules="form.tabs === 'first' ? [{ required: true, message: '商品信息不能为空', trigger: 'blur' }] : []">
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
                                    <app-select-goods :url="search_url" @selected="selectGoodsWarehouse">
                                        <el-button>选择商品</el-button>
                                    </app-select-goods>
                                </el-row>
                                <el-button type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                    商城还未添加商品？点击前往
                                </el-button>
                            </div>
                        </el-form-item>
                        <el-form-item label="商品名称">
                            <el-input :value="goods_warehouse.name" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="是否置顶" prop="is_top">
                            <el-switch v-model="form.is_top" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item prop="material_sort">
                            <label slot="label">排序
                                <el-tooltip class="item" effect="dark"
                                            content="数字越小，排序越靠前"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/, '');" min="0"
                                      placeholder="请输入排序" v-model.number="form.material_sort"></el-input>
                        </el-form-item>
                        <el-form-item label="素材文案" prop="share_text">
                            <el-input type="textarea" :autosize="{ minRows: 6}" placeholder="请输入内容"
                                      v-model="form.share_text"></el-input>
                        </el-form-item>
                        <el-form-item prop="share_pic">
                            <label slot="label">素材图片(多张)
                                <el-tooltip class="item" effect="dark"
                                            content="最多9图"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input v-if="form.share_pic && form.share_pic.length > 0"
                                      v-model.lazy="form.share_pic[0].share_pic"
                                      style="display: none;"></el-input>
                            <app-attachment style="margin-bottom: 10px;" :multiple="true" :max="pic_max_num"
                                            @selected="selectPic">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 750"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <div flex="dir:left">
                                <template v-if="form.share_pic.length">
                                    <draggable v-model="form.share_pic" flex="dir:left"
                                               style="flex-wrap: wrap;width: 400px">
                                        <div v-for="(item,index) in form.share_pic" :key="index"
                                             style="margin-right: 20px;position: relative;cursor: move;">
                                            <app-attachment @selected="updatePic"
                                                            :params="{'currentIndex': index}">
                                                <app-image mode="aspectFill"
                                                           width="90px"
                                                           height='90px'
                                                           :src="item.pic_url">
                                                </app-image>
                                            </app-attachment>
                                            <el-button class="del-btn"
                                                       size="mini" type="danger" icon="el-icon-close"
                                                       circle
                                                       @click="destroyPic(index)"
                                            ></el-button>
                                        </div>
                                    </draggable>
                                </template>
                                <template v-else>
                                    <app-image mode="aspectFill" width="90px" height='90px'></app-image>
                                </template>
                            </div>
                        </el-form-item>
                    </el-col>
                </el-tab-pane>
                <el-tab-pane label="动态" name="four">
                    <el-col :xl="12" :lg="12">
                        <el-form-item label="动态文案" prop="share_text">
                            <el-input type="textarea" :autosize="{ minRows: 6}" placeholder="请输入内容"
                                      v-model="form.share_text"></el-input>
                        </el-form-item>
                        <el-form-item prop="share_pic">
                            <label slot="label">动态图片
                                <el-tooltip class="item" effect="dark"
                                            content="最多9图"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input v-if="form.share_pic && form.share_pic.length > 0"
                                      v-model.lazy="form.share_pic[0].share_pic"
                                      style="display: none;"></el-input>
                            <app-attachment style="margin-bottom: 10px;" :multiple="true" :max="pic_max_num"
                                            @selected="selectPic">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 750"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <div flex="dir:left">
                                <template v-if="form.share_pic.length">
                                    <draggable v-model="form.share_pic" flex="dir:left"
                                               style="flex-wrap: wrap;width: 400px">
                                        <div v-for="(item,index) in form.share_pic" :key="index"
                                             style="margin-right: 20px;position: relative;cursor: move;">
                                            <app-attachment @selected="updatePic"
                                                            :params="{'currentIndex': index}">
                                                <app-image mode="aspectFill"
                                                           width="90px"
                                                           height='90px'
                                                           :src="item.pic_url">
                                                </app-image>
                                            </app-attachment>
                                            <el-button class="del-btn"
                                                       size="mini" type="danger" icon="el-icon-close"
                                                       circle
                                                       @click="destroyPic(index)"
                                            ></el-button>
                                        </div>
                                    </draggable>
                                </template>
                                <template v-else>
                                    <app-image mode="aspectFill" width="90px" height='90px'></app-image>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item prop="video_url">
                            <label slot="label">动态视频
                                <el-tooltip class="item" effect="dark"
                                            content="当动态视频和动态图片均有素材添加时，前端只展示视频"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input v-model="form.material_video_url" placeholder="选择上传视频" disabled>
                                <template slot="append">
                                    <app-attachment :multiple="false" :max="1" @selected="materialVideoUrl"
                                                    type="video">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    content="支持格式mp4;支持编码H.264;视频大小不能超过10 MB"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </app-attachment>
                                </template>
                            </el-input>
                            <el-button v-show="form.material_video_url"
                                       style="position: absolute;top: 0;right: -65px"
                                       type="danger"
                                       @click="destroyRealVideo"
                                       plain
                                       size="small"
                            >清空
                            </el-button>
                            <el-link class="box-grow-0" type="primary" style="font-size:12px"
                                     v-if='form.material_video_url' :underline="false" target="_blank"
                                     :href="form.material_video_url">视频链接
                            </el-link>
                        </el-form-item>
                        <el-form-item prop="material_cover_url" label="动态视频封面图">
                            <app-attachment style="margin-bottom: 10px" :multiple="false" :max="1"
                                            @selected="selectVideo">
                                <el-tooltip effect="dark" content="建议尺寸:750 * 422" placement="bottom">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <template v-if="form.material_cover_url">
                                <div style="position: relative;cursor: move;width:94px">
                                    <app-attachment @selected="selectVideo">
                                        <app-image mode="aspectFill"
                                                   width="90px"
                                                   height='90px'
                                                   :src="form.material_cover_url">
                                        </app-image>
                                    </app-attachment>
                                    <el-button class="del-btn"
                                               size="mini" type="danger" icon="el-icon-close"
                                               circle
                                               @click="destroyVideo"
                                    ></el-button>
                                </div>
                            </template>
                            <template v-else>
                                <app-image mode="aspectFill" width="90px" height='90px'></app-image>
                            </template>
                        </el-form-item>
                        <el-form-item label="是否置顶" prop="is_top">
                            <el-switch v-model="form.is_top" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item prop="material_sort">
                            <label slot="label">排序
                                <el-tooltip class="item" effect="dark"
                                            content="数字越小，排序越靠前"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/, '');" min="0"
                                      placeholder="请输入排序" v-model.number="form.material_sort"></el-input>
                        </el-form-item>
                    </el-col>
                </el-tab-pane>
            </template>
        </app-goods>
        <el-button style="margin-top:10px;padding: 9px 25px" :loading="saveLoading" type="primary" size="small"
                   @click="store">保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search_url: 'plugin/quick_share/mall/goods/search',
                pic_max_num: 9,

                saveLoading: false,
                goods_warehouse: {
                    goods_id: 0,
                    name: ''
                },
                form: {
                    share_text: '',
                    share_pic: [],
                    material_sort: '',
                    material_video_url: '',
                    material_cover_url: '',
                    is_top: 0,
                    tabs: 'first'
                },
                rule: {
                    share_text: [
                        {required: true, message: '请输入素材文案', trigger: 'change'},
                    ],
                    share_pic: [
                        {required: true, message: '请上传素材图片', trigger: 'change'},
                    ],
                },
            };
        },
        methods: {
            store() {
                try {
                    this.$refs.appGoods.store('ruleForm');
                } catch (e) {
                    console.log(e);
                }
            },

            changeTabs(e) {
                this.form.tabs = e;
            },

            selectGoodsWarehouse(goods_warehouse) {
                this.$refs.appGoods.selectGoodsWarehouse(goods_warehouse);
            },

            goodsSuccess(detail) {
                this.goods_warehouse = this.$refs.appGoods.goods_warehouse;
                let tabs = 'first';
                if (JSON.stringify(this.goods_warehouse) == "{}") {
                    tabs = 'four';
                }
                this.$refs.appGoods.activeName = tabs;
                this.form = Object.assign(this.form, {share_pic: detail.pic_url}, detail.plugin, {tabs})
            },

            materialVideoUrl(e) {
                if (e.length) {
                    this.form.material_video_url = e[0].url;
                }
            },
            destroyRealVideo() {
                this.form.material_video_url = '';
            },
            destroyVideo() {
                this.form.material_cover_url = '';
            },
            selectVideo(e) {
                if (e.length) {
                    this.form.material_cover_url = e[0].url;
                }
            },
            selectPic(info) {
                if (info.length) {
                    const self = this;
                    info.map(item => {
                        if (self.form.share_pic.length >= self.pic_max_num) return
                        self.form.share_pic.push({
                            id: item.id,
                            pic_url: item.url,
                        })
                    })
                }
            },
            updatePic(info, params) {
                this.form.share_pic[params.currentIndex].id = info[0].id;
                this.form.share_pic[params.currentIndex].pic_url = info[0].url;
            },
            destroyPic(index) {
                this.form.share_pic.splice(index, 1);
            },
        }
    });
</script>
