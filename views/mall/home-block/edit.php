<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .block-box {
        color: #ffffff;
        cursor: pointer;
        display: inline-block;
        margin-right: 25px;
        height: 280px;
        width: 300px;
        border: 1px solid #D4D4D4;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        position: relative;
    }

    .form-body .active {
        border: 2px solid #409EFF;
    }

    .form-body .active .select {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        height: 80px;
        width: 80px;
        z-index: 10;
    }

    .img {
        width: 220px;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 40px;
    }

    .opacity {
        position: absolute;
        height: 250px;
        width: 280px;
        bottom: 0;
        left: 10px;
        z-index: 5;
        background-color: rgba(0, 0, 0, .3);
        display: none;
    }

    .choose-button {
        position: absolute;
        height: 30px;
        width: 80px;
        padding: 0;
        text-align: center;
        background-color: #409EFF;
        top: 50%;
        left: 0;
        right: 0;
        margin: -15px auto 0;
        z-index: 10;
        display: none;
    }

    .select {
        display: none;
    }

    .block-box.active:hover .opacity,.block-box.active:hover .choose-button {
        display: none;
    }

    .block-box:hover .opacity,.block-box:hover .choose-button {
        display: block;
    }

    .choose {
        display: flex;
        flex-wrap: wrap;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="cardLoading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/home-block/index'})">图片魔方列表</span></el-breadcrumb-item>
                <el-breadcrumb-item>图片魔方编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form @submit.native.prevent :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
            <div class="form-body">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="魔方名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入魔方名称"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-form-item label="样式选择" class="choose">
                    <div @click="selectStyle(item)" v-for="item in style_1" :class="[{ active: item.id == currentStyleId }, classname]">
                        <img class="select" src="statics/img/mall/cat/select.png" alt="">
                        <div class="opacity"></div>
                        <el-button type="primary" class="choose-button">启用该样式</el-button>
                        <img style="width: 280px;height: 250px;position: absolute;bottom:0;left: 10px;" :src="item.bg_url">
                    </div>
                    <div @click="selectStyle(item)" v-for="item in style_2" :class="[{ active: item.id == currentStyleId }, classname]">
                        <img class="select" src="statics/img/mall/cat/select.png" alt="">
                        <div class="opacity"></div>
                        <el-button type="primary" class="choose-button">启用该样式</el-button>
                        <img style="width: 280px;height: 250px;position: absolute;bottom:0;left: 10px;" :src="item.bg_url">
                    </div>
                    <div @click="selectStyle(item)" v-for="item in style_3" :class="[{ active: item.id == currentStyleId }, classname]">
                        <img class="select" src="statics/img/mall/cat/select.png" alt="">
                        <div class="opacity"></div>
                        <el-button type="primary" class="choose-button">启用该样式</el-button>
                        <img style="width: 280px;height: 250px;position: absolute;bottom:0;left: 10px;" :src="item.bg_url">
                    </div>
                </el-form-item>
                <el-form-item label="板块设置">
                    <template v-if="ruleForm.value.length > 0">
                        <el-row v-for="(item,index) in ruleForm.value"
                                :key="index"
                                style="margin-bottom: 15px;">
                            <el-col :span="4">
                                <app-attachment :multiple="false" :max="1" :params="{'index':index}" @selected="picUrl">
                                    <el-tooltip class="item" v-if="index == 0 && currentStyleId == 1" effect="dark" content="建议尺寸：750*360" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="index == 0 && currentStyleId < 5 && currentStyleId != 1" effect="dark" content="建议尺寸：300*360" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="index == 1 && currentStyleId == 2" effect="dark" content="建议尺寸：450*360" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="index > 0 && index < 3 && currentStyleId == 3" effect="dark" content="建议尺寸：450*180" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="index == 1 && currentStyleId == 4" effect="dark" content="建议尺寸：450*180" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="index > 1 && currentStyleId == 4" effect="dark" content="建议尺寸：225*180" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="currentStyleId == 5" effect="dark" content="建议尺寸：375*240" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="currentStyleId == 6" effect="dark" content="建议尺寸：250*240" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="currentStyleId == 7" effect="dark" content="建议尺寸：188*188" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" v-if="currentStyleId == 8" effect="dark" content="建议尺寸：375*186" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image width="80px" height="80px" mode="aspectFill" :src="item.pic_url"></app-image>
                            </el-col>
                            <el-col :span="10">
                                <div flex="box:last">
                                    <el-input disabled v-model="item.link.new_link_url" v-if="item.link"></el-input>
                                    <app-pick-link :params="{'index': index}" @selected="selectLinkUrl">
                                        <el-button size="mini">选择链接</el-button>
                                    </app-pick-link>
                                </div>
                            </el-col>
                        </el-row>
                    </template>
                    <template v-else>
                        <el-tag type="danger">请先选择样式</el-tag>
                    </template>
                </el-form-item>                
            </div>
            <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
            </el-button>
            <el-button class="button-item" :loading="btnLoading" @click="cancel" size="small">取消
            </el-button>
        </el-form>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    type: 0,
                    value: []
                },
                value: [],
                classname: 'block-box',
                currentStyleId: 0,
                style_1: [
                    {
                        id: 1,
                        bg_url: 'statics/img/mall/home_block/1-1.png',
                        type: 1,
                        num: 1,
                    },
                    {
                        id: 2,
                        bg_url: 'statics/img/mall/home_block/1-2.png',
                        type: 1,
                        num: 2
                    },
                    {
                        id: 3,
                        bg_url: 'statics/img/mall/home_block/1-3.png',
                        type: 1,
                        num: 3
                    },
                    {
                        id: 4,
                        bg_url: 'statics/img/mall/home_block/1-4.png',
                        type: 1,
                        num: 4
                    },
                ],
                style_2: [
                    {
                        id: 5,
                        bg_url: 'statics/img/mall/home_block/2-1.png',
                        type: 2,
                        num: 2
                    },
                    {
                        id: 6,
                        bg_url: 'statics/img/mall/home_block/2-2.png',
                        type: 2,
                        num: 3
                    },
                    {
                        id: 7,
                        bg_url: 'statics/img/mall/home_block/2-3.png',
                        type: 2,
                        num: 4
                    },
                ],
                style_3: [
                    {
                        id: 8,
                        bg_url: 'statics/img/mall/home_block/3-1.png',
                        type: 3,
                        num: 4
                    }
                ],
                rules: {
                    name: [
                        {required: true, message: '请输入魔方名称', trigger: 'change'},
                    ],
                    type: [
                        {required: true, message: '请选择样式', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        methods: {
            // 返回上一页
            cancel(){
                window.history.go(-1)
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/home-block/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/home-block/index'
                                })
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/home-block/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                        self.value = self.ruleForm.value;
                        self.setCurrentStyleId();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            setCurrentStyleId() {
                let self = this;
                let arr = ['style_1', 'style_2', 'style_3'];
                self[arr[self.ruleForm.type - 1]].forEach(function (item, index) {
                    if (self.ruleForm.value.length == item.num) {
                        self.currentStyleId = item.id;
                    }
                })
            },
            // 选择图片
            picUrl(e, params) {
                if (e.length) {
                    this.ruleForm.value[params.index].pic_url = e[0].url;
                }
            },
            // 选择链接
            selectLinkUrl(e, params) {
                let self = this;
                e.forEach(function (item, index) {
                    Vue.set(self.value[params.index], 'link', JSON.parse(JSON.stringify(item)));
                });
            },
            // 选择样式
            selectStyle(e) {
                if (this.currentStyleId == e.id) {
                    return;
                }
                this.ruleForm.type = e.type;
                this.currentStyleId = e.id;
                for (let i = 0; i < e.num; i++) {
                    this.value.push({
                        pic_url: '',
                        link_url: '',
                        link: {}
                    })
                }
                this.ruleForm.value = this.value.slice(0,e.num)
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>
