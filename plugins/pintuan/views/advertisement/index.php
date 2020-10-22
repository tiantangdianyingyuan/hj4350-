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
        margin-right: 20px;
        border: 1px solid #D6D6D6;
        padding: 30px 10px 0;
        border-top-left-radius: 30px;
        border-top-right-radius: 30px;
        width: 300px;
        height: 280px;
        position: relative;
        margin-bottom: 10px;
    }

    .active {
        border: 1px solid #3399ff;
    }

    .open-css {
        width: 280px;
        height: 250px;
        position: absolute;
        bottom: 0;
        left: 10px;
        z-index: 5;
        background-color: rgba(0, 0, 0, .3);
        display: none;
    }

    .block-box:hover .open-css {
        display: block;
    }

    .block-box.active:hover .open-css {
        display: none;
    }

    .open-css .el-button {
        position: absolute;
        bottom: 125px;
        left: 0;
        right: 0;
        width: 80px;
        height: 30px;
        line-height: 30px;
        margin: 0 auto;
        padding: 0;
        text-align: center;
    }

    .block-box img {
        width: 280px;
        height: 250px;
        position: absolute;
        bottom: 0;
        left: 10px;
        z-index: 2;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .block-box .inuse {
        position: absolute;
        left: 0;
        top: 0;
        height: 80px;
        width: 80px;
        z-index: 11;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>拼团广告</span>
            </div>
        </div>
        <div class="form-body">
            <el-form v-if="is_show" :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item label="拼团广告状态" prop="is_advertisement">
                    <el-switch v-model="ruleForm.is_advertisement" :active-value="1" :inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="样式选择">
                    <div @click="selectStyle(item)" v-for="item in style_1" class="block-box"
                         :class="item.id == currentStyleId ? 'active' : ''">
                        <img src="statics/img/mall/cat/select.png" class="inuse" v-if="item.id == currentStyleId"
                             alt="">
                        <img :src="item.bg_url">
                        <div class="open-css">
                            <el-button type="primary">启用该样式</el-button>
                        </div>
                    </div>
                    <div @click="selectStyle(item)" v-for="item in style_2" class="block-box"
                         :class="item.id == currentStyleId ? 'active' : ''">
                        <img src="statics/img/mall/cat/select.png" class="inuse" v-if="item.id == currentStyleId"
                             alt="">
                        <img :src="item.bg_url">
                        <div class="open-css">
                            <el-button type="primary">启用该样式</el-button>
                        </div>
                    </div>
                    <div @click="selectStyle(item)" v-for="item in style_3" class="block-box"
                         :class="item.id == currentStyleId ? 'active' : ''">
                        <img src="statics/img/mall/cat/select.png" class="inuse" v-if="item.id == currentStyleId"
                             alt="">
                        <img :src="item.bg_url">
                        <div class="open-css">
                            <el-button type="primary">启用该样式</el-button>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="板块设置">
                    <template v-if="ruleForm.advertisement.list.length > 0">
                        <el-row v-for="(item,index) in ruleForm.advertisement.list"
                                :key="index"
                                style="margin-bottom: 15px;">
                            <el-col :span="2" style="min-width: 100px">
                                <el-tooltip class="item" effect="dark" :content="'建议尺寸:'+item.size" placement="top">
                                    <app-attachment :multiple="false" :max="1" :params="{'index':index}"
                                                    @selected="picUrl">
                                        <el-button size="mini">选择文件</el-button>
                                    </app-attachment>
                                </el-tooltip>
                                <app-image width="80px" height="80px" mode="aspectFill" style="margin-top: 10px"
                                           :src="item.pic_url"></app-image>
                            </el-col>
                            <el-col :span="10">
                                <div flex="box:last">
                                    <el-input disabled v-model="item.link_url"></el-input>
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
            </el-form>
        </div>
<!--        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存-->
<!--        </el-button>-->
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                type: 0,
                ruleForm: {
                    advertisement: {
                        current_style_id: null,
                        type: null,
                        list: []
                    },
                    is_advertisement: 0,
                },
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
                    type: [
                        {required: true, message: '请选择样式', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                is_show: false,
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.ruleForm.advertisement.type = self.type;
                        self.ruleForm.advertisement.current_style_id = self.currentStyleId;
                        request({
                            params: {
                                r: 'plugin/pintuan/mall/advertisement/index'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
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
                        r: 'plugin/pintuan/mall/advertisement/index',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    self.is_show = true;
                    if (e.data.code == 0) {
                        if(e.data.data.advertisement.list) {                
                            self.ruleForm.advertisement.list = e.data.data.advertisement.list;
                            self.ruleForm.advertisement.type = e.data.data.advertisement.type;
                            self.type = e.data.data.advertisement.type;
                            self.currentStyleId = e.data.data.advertisement.current_style_id;
                            for(let i in self.ruleForm.advertisement.list) {
                                self.ruleForm.advertisement.list[i].size = self.ruleForm.advertisement.list[i].height.replace('rpx','') + '*' + self.ruleForm.advertisement.list[i].width.replace('rpx','');
                            }
                        }
                        self.ruleForm.is_advertisement = e.data.data.is_advertisement;
//                        self.setCurrentStyleId();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            setCurrentStyleId() {
                let self = this;
                if(self.type == 1) {
                    self.currentStyleId = self.ruleForm.advertisement.list.length;
                }else if (self.type == 2) {
                    self.currentStyleId = self.ruleForm.advertisement.list.length + 3;
                }
            },
            // 选择图片
            picUrl(e, params) {
                if (e.length) {
                    this.ruleForm.advertisement.list[params.index].pic_url = e[0].url;
                }
            },
            // 选择链接
            selectLinkUrl(e, params) {
                let self = this;
                e.forEach(function (item, index) {
                    self.ruleForm.advertisement.list[params.index].link_url = item.new_link_url;
                    self.ruleForm.advertisement.list[params.index].open_type = item.open_type;
                });
            },
            // 选择样式
            selectStyle(e) {
                if (this.currentStyleId == e.id) {
                    return;
                }
                this.type = e.type;
                this.currentStyleId = e.id;
                let oldLength = this.ruleForm.advertisement.list.length;
                if (oldLength > e.num) {
                    let newLength = oldLength - e.num;
                    for (let i = 0; i < newLength; i++) {
                        let len = this.ruleForm.advertisement.list.length;
                        this.ruleForm.advertisement.list.splice(len - 1)
                    }
                } else {
                    let newLength = e.num - oldLength
                    for (let i = 0; i < newLength; i++) {
                        this.ruleForm.advertisement.list.push({
                            pic_url: '',
                            link_url: '',
                        })
                    }
                }
                for(let i in this.ruleForm.advertisement.list) {
                    switch(e.id) {
                        case 1:
                            this.ruleForm.advertisement.list[i].size = '750*360';
                            break;
                        case 2:
                            this.ruleForm.advertisement.list[0].size = '300*360';
                            this.ruleForm.advertisement.list[1].size = '450*360';
                            break;
                        case 3:
                            this.ruleForm.advertisement.list[0].size = '300*360';
                            this.ruleForm.advertisement.list[1].size = '450*180';
                            this.ruleForm.advertisement.list[2].size = '450*180';
                            break;
                        case 4:
                            this.ruleForm.advertisement.list[0].size = '300*360';
                            this.ruleForm.advertisement.list[1].size = '450*180';
                            this.ruleForm.advertisement.list[2].size = '225*180';
                            this.ruleForm.advertisement.list[3].size = '225*180';
                            break;
                        case 5:
                            this.ruleForm.advertisement.list[i].size = '375*240';
                            break;
                        case 6:
                            this.ruleForm.advertisement.list[i].size = '250*240';
                            break;
                        case 7:
                            this.ruleForm.advertisement.list[i].size = '188*188';
                            break;
                        case 8:
                            this.ruleForm.advertisement.list[i].size = '375*186';
                            break;
                    }
                }
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
