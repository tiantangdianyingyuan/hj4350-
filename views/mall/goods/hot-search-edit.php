<?php

Yii::$app->loadViewComponent('app-goods');
Yii::$app->loadViewComponent('goods/app-select-goods');
?>
<style>
    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .out-max {
        width: 500px;
    }

    .out-max > .el-card__header {
        padding: 0 15px;
    }

    .t-omit-two {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        white-space: normal !important;
    }

    .text-link {
        margin: -5px 0 20px 150px;
    }

    .stop {
        pointer-events: none !important;
    }
</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/goods-hot-search/get-all'})">
                    商品热搜
                </span>
            </el-breadcrumb-item>
            <el-breadcrumb-item v-if="goods_id">编辑热搜商品</el-breadcrumb-item>
            <el-breadcrumb-item v-else>新建热搜商品</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <el-card v-loading="listLoading" shadow="never" style="background: #FFFFFF" body-style="background-color: #ffffff;">
        <el-form :model="editForm" ref="editForm" :rules="editFormRules" label-width="150px" position-label="right">
            <el-form-item label="商品信息获取" prop="goods_id">
                <label slot="label">
                    <span>商品信息获取</span>
                    <el-tooltip class="item" effect="dark"
                                content="只能从商城中获取商品信息，且基本信息与商城商品保持一致" placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                </label>
                <app-select-goods :multiple="false"
                                  :status="1"
                                  submit_text="选择"
                                  style="display: inline-block"
                                  @selected="selectGoods"
                                  :class="{stop: editForm.type === 'goods'}">
                    <el-button size="small" type="primary" plain :disabled="editForm.type === 'goods'">选择商品</el-button>
                </app-select-goods>
                <el-card v-if="editForm.goods_id"
                         style="margin-top: 5px"
                         class="out-max"
                         :body-style="{ padding: '15px' }"
                         shadow="never">
                    <div slot="header">
                        <span>商品</span>
                    </div>
                    <div flex="dir:left cross:center">
                        <img height="50" width="50" :src="editForm.cover_pic" style="margin-right: 12px" alt>
                        <div class="t-omit-two" style="line-height: 1.25rem">{{editForm.goods_name}}</div>
                    </div>
                </el-card>
            </el-form-item>
            <div class="text-link">
                <el-link v-if="!goods_id"
                         @click="$navigate({r:'mall/goods/edit'}, true)"
                         type="primary"
                         :underline="false">商城还未添加商品？点击前往
                </el-link>
            </div>
            <el-form-item prop="title" label="热搜词">
                <el-input class="out-max"
                          v-model="editForm.title"
                          placeholder="请输入热搜词，最多输入16个字"
                          maxlength="16"
                          show-word-limit
                ></el-input>
            </el-form-item>
            <div class="text-link">
                <el-link :underline="false" @click="hotDialog = true" type="primary">查看示例</el-link>
            </div>
            <el-form-item prop="sort" label="热搜名次">
                <el-input class="out-max"
                          :disabled="editForm.type === 'goods'"
                          v-model="editForm.sort"
                          oninput="this.value = this.value.match(/10|[1-9]/)"
                >
                    <template slot="append">名</template>
                </el-input>
            </el-form-item>
            <div class="text-link" style="color:#c9c9c9">数字必须从1到10的整数</div>
        </el-form>
    </el-card>
    <el-button size="small" style="margin-top: 20px" :loading="btnLoading" type="primary" @click="submit">保存</el-button>
    <!-- Model -->
    <el-dialog title="查看热搜词示例" width="30%" :visible.sync="hotDialog">
        <div style="text-align: center;margin-left: 50px">
            <el-image style="height: 319px;width: 403px" :src="hotDialogBg"></el-image>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="hotDialog = false" type="primary">我知道了</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                goods_id: getQuery('goods_id'),
                btnLoading: false,
                listLoading: false,
                hotDialog: false,
                hotDialogBg: 'statics/img/mall/goods/hot-search/preview.png',
                editForm: {
                    type: 'hot-search',
                    goods_id: null,
                    goods_name: null,
                    cover_pic: null,
                },
                editFormRules: {
                    goods_id: [
                        {required: true, message: '商品不能为空', trigger: 'blur'},
                    ],
                    title: [
                        {required: true, message: '热搜词不能为空', trigger: 'blur'},
                    ],
                    sort: [
                        {required: true, message: '热搜名次不能为空', trigger: 'blur'},
                    ],
                },
            }
        },

        methods: {
            selectGoods(e) {
                this.editForm = Object.assign(this.editForm, {
                    goods_id: e.id,
                    goods_name: e.name,
                    cover_pic: e.goodsWarehouse.cover_pic,
                });
                this.$refs.editForm.validateField('goods_id');
            },
            submit() {
                this.$refs.editForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.editForm);
                        request({
                            params: {
                                r: 'mall/goods-hot-search/edit',
                            },
                            data: para,
                            method: 'POST'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                setTimeout(function () {
                                    navigateTo({
                                        r: 'mall/goods-hot-search/get-all',
                                    })
                                }, 1000);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            getForm() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/goods-hot-search/edit',
                        goods_id: getQuery('goods_id'),
                        type: getQuery('type'),
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.editForm = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            if (getQuery('goods_id')) {
                this.getForm();
            }
        }
    });
</script>
