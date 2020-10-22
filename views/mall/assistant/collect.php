<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/16
 * Time: 9:43
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$mchId = Yii::$app->user->identity->mch_id;
Yii::$app->loadViewComponent('goods/app-add-cat');
?>
<style>
    .form-body {
        padding: 20px 20px 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        /*min-width: 1000px;*/
    }

    .tag-item {
        margin-right: 5px;
        margin-bottom: 5px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <span>采集商品</span>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" label-width="150px" :rules="rules" ref="ruleForm" size="mini">
                <el-form-item label="商品链接" prop="urlList">
                    <el-input class="ml-24" style="width: 600px" type="textarea" rows="4" placeholder="请输入商品链接"
                              v-model="ruleForm.urlList"></el-input>
                    <div style="color: #8F8F8F">支持采集淘宝、天猫、1688阿里巴巴、京东、拼多多商品链接，多个商品链接请用回车隔开；</div>
                    <div style="color: #8F8F8F">保存之后的数据请在“<el-button type="text" @click="$navigate({r:'mall/goods/index'}, true)">商品管理=>商品列表</el-button>”中查看</div>
                </el-form-item>
                <el-form-item hidden>
                    <el-input class="ml-24" style="width: 600px"></el-input>
                </el-form-item>
                <el-form-item v-if="mch_id > 0" label="系统分类" prop="system_cat_ids">
                    <el-tag v-if="systemCats.length > 0" type="warning" class="tag-item"
                            @close="deleteSystemCat(item, index)" closable
                            v-for="(item, index) in systemCats"
                            :key="item.value">
                        {{item.label}}
                    </el-tag>
                    <el-button type="primary" @click="$refs.systemCats.openDialog()" size="small">选择分类
                    </el-button>
                </el-form-item>
                <el-form-item label="是否下载图片" prop="is_download">
                    <el-switch
                            v-model="ruleForm.is_download"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="商品分类" prop="cat_ids">
                    <el-tag v-if="cats.length > 0" type="warning" class="tag-item"
                            @close="deleteCat(item, index)"
                            closable
                            v-for="(item, index) in cats"
                            :key="item.value">
                        {{item.label}}
                    </el-tag>
                    <el-button type="primary" @click="$refs.cats.openDialog()" size="small">选择分类</el-button>
                </el-form-item>
                <el-form-item label="状态" prop="goods_status">
                    <el-radio v-model="ruleForm.goods_status" :label="0">暂不上架</el-radio>
                    <el-radio v-model="ruleForm.goods_status" :label="1">立即上架</el-radio>
                </el-form-item>
            </el-form>
        </div>

        <app-add-cat ref="cats" :new-cats="ruleForm.cat_ids" @select="selectCat" :mch_id="mch_id"></app-add-cat>
        <app-add-cat ref="systemCats" :new-cats="ruleForm.system_cat_ids" @select="selectSystemCat"></app-add-cat>

        <el-button class="button-item" type="primary" size="small" :loading="btnLoading" @click="onSubmit">提交
        </el-button>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                btnLoading: false,
                mch_id: <?= $mchId ?>,
                cats: [],
                systemCats: [],
                ruleForm: {
                    goods_status: 0,
                    cat_ids: [],
                    system_cat_ids: [],
                    urlList: '',
                    is_download: 1,
                },
                rules: {
                    goods_status: [
                        {required: true, message: '请选择商品状态', trigger: 'change'},
                    ],
                    cat_ids: [
                        {required: true, message: '请选择商品分类', trigger: 'change'},
                    ],
                    system_cat_ids: [
                        {required: true, message: '请选择系统分类', trigger: 'change'},
                    ],
                    urlList: [
                        {required: true, message: '请输入商品链接', trigger: 'blur'},
                    ]
                },
            };
        },
        created() {
        },
        methods: {
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.cat_ids = arr;
            },
            selectSystemCat(cats) {
                this.systemCats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.system_cat_ids = arr;
            },
            deleteCat(option, index) {
                let self = this;
                self.ruleForm.cat_ids.forEach(function (item, index) {
                    if (item == option.value) {
                        self.ruleForm.cat_ids.splice(index, 1)
                    }
                })
                self.cats.splice(index, 1);
            },
            deleteSystemCat(option, index) {
                let self = this;
                self.ruleForm.system_cat_ids.forEach(function (item, index) {
                    if (item == option.value) {
                        self.ruleForm.system_cat_ids.splice(index, 1)
                    }
                })
                self.systemCats.splice(index, 1);
            },
            onSubmit() {
                this.$refs.ruleForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        this.submitAll().then(e => {
                            this.btnLoading = false;
                            console.log('then');
                            console.log(e);
                            if (e[0].data.code === 0) {
                                this.$message.success(e[0].data.msg);
                                this.ruleForm.urlList = '';
                            } else {
                                this.$message.error(e[0].data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                            console.log('catch');
                            console.log(e);
                        })
                    }
                });
            },
            async submitAll() {
                let arr = [];
                let urlArr = this.ruleForm.urlList.split('\n');
                for (let i = 0; i < urlArr.length; i++) {
                    let data = this.ruleForm;
                    data.url = urlArr[i];
                    arr.push(await this.$request({
                        params: {
                            r: 'plugin/assistant/mall/index/collect',
                        },
                        data: data,
                        method: 'post'
                    }))
                }
                return Promise.all(arr);
            },
        }
    });
</script>

