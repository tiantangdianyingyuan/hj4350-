<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/8 18:12
 */
$mchId = Yii::$app->user->identity->mch_id;
?>
<style>
    .cat-style {
        display: inline-block;
        border-radius: 25px;
        height: 560px;
        line-height: 560px;
        width: 300px;
        position: relative;
        text-align: center;
        margin: 0 15px;
    }

    .cat-style .text {
        cursor: pointer;
        background: #409eff;
        text-align: center;
        position: absolute;
        top: 245px;
        left: 105px;
        line-height: 30px;
        width: 90px;
        color: #fff;
    }

    .cat-model {
        height: 500px;
        line-height: 500px;
        width: 280px;
        display: inline-block;
        cursor: pointer;
        position: absolute;
        top: 30px;
        left: 9px;
    }

    .cat-bg {
        border: 1px solid #f2f4f5;
        margin-top: 30px;
        display: inline-block;
        cursor: pointer;
    }

    .cat-style-xz {
        border: 1px solid #409eff;
    }

    .cat-style-nxz {
        border: 1px solid #F2F4F5;
    }

    .cat-style-model {
        background-color: rgba(0, 0, 0, 0.5);

    }

    .cat-style-nmodel {
        background-color: transparent;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" :rules="rules" ref="form" label-width="200px" size="small">
            <el-card shadow="never" style="border:0;margin-bottom: 20px"
                     body-style="background-color: #f3f3f3;padding: 10px 0 0;">
                <div slot="header">
                    <span>分类页面样式</span>
                </div>
                <el-row class="table-body">
                    <el-tabs v-model="activeName" @tab-click="handleClick">
                        <el-tab-pane label="一级分类样式" name="first">
                            <div class="cat-style"
                                 :class="item.value == form.cat_style ? 'cat-style-xz':'cat-style-nxz'"
                                 v-for="item in catList[0]">
                                <div @mouseenter="enter(item.value)"
                                     @mouseleave="leave(item.value)"
                                     class="cat-model"
                                     :class="item.value == selectModel ? 'cat-style-model': 'cat-style-nmodel'"
                                >
                                </div>
                                <div class="cat-bg">
                                    <app-image :src="item.pic_url"
                                               style="height:500px;line-height:500px;width:280px;"></app-image>
                                </div>
                                <app-image v-if="item.value == form.cat_style"
                                           style="position: absolute;top: 0px;left: 0px;z-index:99" width="80px"
                                           height="80px" :src="cat_select"></app-image>
                                <div v-if="item.value == selectModel" @click="setStyle(item.value)" class="text">启用该样式
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="二级分类样式" name="two">
                            <div class="cat-style"
                                 :class="item.value == form.cat_style ? 'cat-style-xz':'cat-style-nxz'"
                                 v-for="item in catList[1]">
                                <div @mouseenter="enter(item.value)"
                                     @mouseleave="leave(item.value)"
                                     class="cat-model"
                                     :class="item.value == selectModel ? 'cat-style-model': 'cat-style-nmodel'"
                                >
                                </div>
                                <div class="cat-bg">
                                    <app-image :src="item.pic_url"
                                               style="height:500px;line-height:500px;width:280px;"></app-image>
                                </div>
                                <app-image v-if="item.value == form.cat_style"
                                           style="position: absolute;top: 0px;left: 0px;z-index:99" width="80px"
                                           height="80px" :src="cat_select"></app-image>
                                <div v-if="item.value == selectModel" @click="setStyle(item.value)" class="text">启用该样式
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="三级分类样式" name="three">
                            <div class="cat-style"
                                 :class="item.value == form.cat_style ? 'cat-style-xz':'cat-style-nxz'"
                                 v-for="item in catList[2]">
                                <div @mouseenter="enter(item.value)"
                                     @mouseleave="leave(item.value)"
                                     class="cat-model"
                                     :class="item.value == selectModel ? 'cat-style-model': 'cat-style-nmodel'"
                                >
                                </div>
                                <div class="cat-bg">
                                    <app-image :src="item.pic_url"
                                               style="height:500px;line-height:500px;width:280px;"></app-image>
                                </div>
                                <app-image v-if="item.value == form.cat_style"
                                           style="position: absolute;top: 0px;left: 0px;z-index:99" width="80px"
                                           height="80px" :src="cat_select"></app-image>
                                <div v-if="item.value == selectModel" @click="setStyle(item.value)" class="text">启用该样式
                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-row>
            </el-card>
            <el-card v-if="!is_mch" shadow="never" style="border:0;margin-bottom: 20px">
                <div slot="header">
                    <span>显示设置</span>
                </div>
                <el-row class="table-body">
                    <el-form-item label="" prop="cat_goods_count">
                        <template slot='label'>
                            <span>每个分类商品显示总数</span>
                            <el-tooltip effect="dark" content="每个分类板块显示的商品最大数(0~100)"
                                    placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-input v-model="form.cat_goods_count" style="width:30%" type="number">
                            <template slot="append">个</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="cat_goods_cols">
                        <template slot='label'>
                            <span>商品每行显示数量</span>
                            <el-tooltip effect="dark" content="默认首页显示"
                                    placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-select v-model="form.cat_goods_cols" placeholder="请选择">
                            <el-option v-for="item in catGoodsCols" :key="item.value" :label="item.label"
                                       :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
            </el-card>
            <el-button class="button-item" :loading="btnLoading" size="small" type="primary" @click="onSubmit">保存
            </el-button>
        </el-form>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                catList: [
                    [
                        {
                            value: 3,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-a.png',
                        },
                        {
                            value: 1,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-b.png',
                        },
                        {
                            value: 8,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-c.png',
                        }
                    ],
                    [
                        {
                            value: 2,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-a.png',
                        },
                        {
                            value: 5,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-b.png',
                        },
                        {
                            value: 4,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-c.png',
                        }
                    ],
                    [
                        {
                            value: 6,
                            pic_url: _baseUrl + '/statics/img/mall/cat/three-a.png',
                        },
                        {
                            value: 7,
                            pic_url: _baseUrl + '/statics/img/mall/cat/three-b.png',
                        },
                    ],
                ],
                mchCatList: [
                    [
                        {
                            value: 3,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-one-a.png',
                        },
                        {
                            value: 1,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-one-b.png',
                        },
                    ],
                    [
                        {
                            value: 2,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-two-a.png',
                        },
                        {
                            value: 4,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-two-c.png',
                        }
                    ],
                    [
                        {
                            value: 6,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-three-a.png',
                        },
                        {
                            value: 7,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-three-b.png',
                        },
                    ],
                ],
                cat_select: _baseUrl + '/statics/img/mall/cat/select.png',
                selectModel: 0,
                catGoodsCols: [{
                    label: '1',
                    value: 1
                },
                    {
                        label: '2',
                        value: 2
                    },
                    {
                        label: '3',
                        value: 3
                    },
                ],

                form: {},
                btnLoading: false,
                listLoading: false,
                activeName: 'first',
                selectSet: 0,

                rules: {
//                    recommend_count: [
//                        {required: true, message: '推荐商品显示数量不能为空', trigger: 'change'},
//                    ],
                    cat_goods_count: [
                        {required: true, message: '每个分类商品显示总数不能为空', trigger: 'change'},
                    ],
                    cat_goods_cols: [
                        {required: true, message: '商品每行显示数量不能为空', trigger: 'change'},
                    ]
                },
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
            };
        },
        methods: {
            //样式切换
            setStyle(value) {
                this.form.cat_style = value;
                this.selectModel = 0;
            },
            enter(value) {
                console.log('enter');
                if (this.form.cat_style == value) {
                    return;
                }
                this.selectModel = value;
            },
            leave(value) {
                console.log('leave');
                if (this.form.cat_style == value) {
                    return;
                }
                if (this.selectModel == value) {
                    //this.selectModel = 0;
                }
            },

            handleClick(row) {
                console.log(row);
            },

            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'mall/cat/style'
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                navigateTo({r: 'mall/cat/style'});
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            //获取列表
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/cat/style'
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.form = e.data.data.setting;
                        }
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted() {
            this.getList();
            if (this.is_mch) {
                this.catList = this.mchCatList;
            }
        }
    });
</script>