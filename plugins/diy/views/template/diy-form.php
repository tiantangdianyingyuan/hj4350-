<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<style>
    .diy-form .form-style-item {
        width: 100px;
        border: 1px solid #ebeef5;
        cursor: pointer;
        padding: 5px;
        line-height: normal;
        text-align: center;
        color: #606266;
    }

    .diy-form .form-style-item + .form-style-item {
        margin-left: 20px;
    }

    .diy-form .form-style-item.active {
        border-color: #00a0e9;
        color: #409EFF;
    }

    .diy-form .form-style-1 > div {
        background: #e6f4ff;
        position: absolute;
        left: 0;
        top: 20%;
        height: 20px;
        width: 20%;
        z-index: 0;
    }

    .diy-form .form-style-1 > div:last-child {
        background: #e6f4ff;
        position: absolute;
        left: 25%;
        top: 20%;
        height: 20px;
        width: 75%;
        z-index: 0;
    }

    .diy-form .form-style-2 > div {
        background: #fff;
        position: absolute;
        left: 5%;
        top: 8.5px;
        height: 15px;
        width: 15px;
        z-index: 1;
    }

    .diy-form .form-style-2 > div:last-child {
        background: #e6f4ff;
        position: absolute;
        left: 0;
        top: 20%;
        height: 20px;
        width: 100%;
        z-index: 0;
    }

    .diy-form .form-style-3 > div {
        background: #e6f4ff;
        position: absolute;
        left: 0;
        top: 0;
        height: 8px;
        width: 20px;
        z-index: 1;
    }

    .diy-form .form-style-3 > div:last-child {
        background: #e6f4ff;
        position: absolute;
        left: 0;
        top: 10px;
        height: 20px;
        width: 100%;
        z-index: 0;
    }

    .diy-form .form-style-1,
    .diy-form .form-style-2,
    .diy-form .form-style-3 {
        display: block;
        height: 30px;
        margin: 0 auto 5px;
        position: relative;
    }

    .diy-form .el-input-number--small .el-input__inner {
        padding: 0;
    }

    .diy-form .input-color .el-input__inner {
        padding: 0 10px;
    }

    .diy-form hr {
        border: none;
        height: 1px;
        background-color: #e2e2e2;
    }

/*     .diy-form .background-position {
        width: 170px;
        height: 180px;
    }

    .diy-form .background-position > div {
        height: 50px;
        width: 50px;
        margin-bottom: 10px;
        cursor: pointer;
        background-color: #F5F7F9;
    }

    .diy-form .background-position > .active {
        background-color: #E6F4FF;
        border: 2px dashed #5CB3FD;
    } */

    .diy-form .border {
        border: 1px solid #e2e2e2;
    }

    .diy-form .img-up {
        height: 190px;
        width: 190px;
        background-color: #fff;
        color: #B4B4B4;
        font-size: 12px;
        border-radius: 8px;
        border: 1px solid #B4B4B4;
        text-align: center;
    }

    .diy-form .img-up img {
        height: 56px;
        width: 56px;
        margin: 40px auto 20px;
        display: block;
    }

    .diy-form .label {
        margin-bottom: 10px;
    }

    .diy-form .time {
        float: right
    }

    .diy-form .radio-item {
        padding: 0 24px;
        margin-right: 10px;
        height: 56px;
        line-height: 56px;
        background: #f7f7f7;
        border-radius: 200px;
    }
</style>
<template id="diy-form">
    <div class="diy-form">
        <div class="diy-component-preview">
            <div :style="'padding-top:'+data.marginTop+'px;background-color:'+data.marginColor">
                <div :style="'background-color:'+ data.backgroundColor+';padding:'+data.padding+'px 0;background-image:url('+data.backgroundPicUrl+');background-size:'+data.backgroundWidth+'% '+data.backgroundHeight+'%;background-repeat:'+repeat+';background-position:'+position">
                    <div v-for="(item,index) in list" :flex="data.style==3?'wrap:wrap':'dir:left'" :style="'margin-top:'+(index == 0 ? '0':data.marginBottom)+'px;padding: 0px '+data.inputPadding+'px;'">
                        <div flex="cross:center" :class="data.style==3?'label':''" v-if="item.key =='img_upload'" :style="'color:'+data.inputLabel+';width: '+(data.style==3?'100%;':'20%;')">{{item.name}}</div>
                        <div :class="data.style==3?'label':''" v-else-if="item.key =='radio' || item.key =='checkbox'" :style="'height:58px;line-height:58px;color:'+data.inputLabel+';min-width:112px;width: '+(data.style==3?'100%;':'20%;')">{{item.name}}</div>
                        <div flex="cross:center" :class="data.style==3?'label':''" v-else-if="item.key !='textarea' && item.key !='text'" :style="(data.style!=3?('height:'+(data.height+2) +'px;'):'')+'color:'+data.inputLabel+';min-width:112px;width: '+(data.style==3?'100%;':'20%;')">{{item.name}}</div>
                        <div flex="cross:center" :class="data.style==3?'label':''" v-else-if="data.style!=2" :style="(data.style!=3?('height:'+(data.height+2) +'px;'):'')+'color:'+data.inputLabel+';width: '+(data.style==3?'100%;':'20%;')">{{item.name}}</div>
                        <div v-if="item.key =='img_upload'" class="img-up">
                            <img src="statics/img/mall/icon-image.png" alt="">
                            <div>上传图片</div>
                        </div>
                        <div v-else-if="item.key =='textarea'" style="text-align: left;height: 200px;padding: 10px 24px" :class="data.inputBorder?'border':''" :style="'border-color:'+data.inputBorder+';background:'+data.inputBackground+';border-radius:'+data.radius+'px;width: '+(data.style==1?'80%;':'100%;')">
                            <span :class="item.key == 'time'|| item.key == 'date'?'time':''" :style="'color:'+data.inputText" v-if="item.default">{{item.default}}</span>
                            <span :class="item.key == 'time'|| item.key == 'date'?'time':''" :style="'color:'+data.inputTip" v-else-if="item.key == 'time'|| item.key == 'date'">请选择</span>
                            <span :style="'color:'+data.inputTip" v-else>{{data.style==2?item.name:item.hint}}</span>
                        </div>
                        <div v-else-if="item.key =='radio' || item.key =='checkbox'" flex="dir:left wrap:wrap cross:center">
                            <div class="radio-item border" :style="{'margin-bottom': `${data.marginBottom}px`, 'color': `${data.selectBoxColor}`,'border-color': `${data.selectBoxColor}`}" v-for="row in item.list">{{row.label}}</div>
                        </div>
                        <div v-else style="padding: 0 24px;" :class="data.inputBorder?'border':''" :style="'height:'+data.height +'px;line-height:'+data.height+'px;border-color:'+data.inputBorder+';background:'+data.inputBackground+';border-radius:'+data.radius+'px;width: '+(data.style==1?'80%;':'100%;')">
                            <span :class="item.key == 'time'|| item.key == 'date'?'time':''" :style="'color:'+data.inputText" v-if="item.default">{{item.default}}</span>
                            <span :class="item.key == 'time'|| item.key == 'date'?'time':''" :style="'color:'+data.inputTip" v-else-if="item.key == 'time'|| item.key == 'date'">请选择</span>
                            <span :style="'color:'+data.inputTip" v-else>{{data.style==2?item.name:item.hint}}</span>
                        </div>
                    </div>
                    <div style="text-align: center" :class="data.borderBorder?'border':''" :style="'height:'+data.buttonHeight+'px;line-height:'+data.buttonHeight+'px;color:'+data.borderText+';margin: '+data.buttonMargin+'px '+data.buttonPadding+'px 0px;border-color:'+data.borderBorder+';background:'+data.borderBackground+';border-radius:'+data.buttonRadius+'px;'">{{data.borderContent}}</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <div>表单</div>
            <div flex="dir:left">
                <el-button style="margin: 20px 0;" @click="visible=true" size="small" type="primary">内容设置</el-button>
                <!-- <el-button style="margin: 20px;" @click="submitDialog=true" size="small" type="primary">提交设置</el-button> -->
            </div>
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="样式">
                    <div flex="dir:left">
                        <div @click="data.style=1" class="form-style-item" :class="data.style==1?'active':''">
                            <div class="form-style-1">
                                <div></div>
                                <div></div>
                            </div>
                            <div>样式1</div>
                        </div>
                        <div @click="data.style=2" class="form-style-item" :class="data.style==2?'active':''">
                            <div class="form-style-2" flex>
                                <div></div>
                                <div></div>
                            </div>
                            <div>样式2</div>
                        </div>
                        <div @click="data.style=3" class="form-style-item" :class="data.style==3?'active':''">
                            <div class="form-style-3" flex>
                                <div></div>
                                <div></div>
                            </div>
                            <div>样式3</div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="输入框高度">
                    <el-slider v-model="data.height" style="float: left;width: 95%" :max="160" show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="输入框边距">
                    <el-slider v-model="data.inputPadding" style="float: left;width: 95%" :max="50"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="输入框间距">
                    <el-slider v-model="data.marginBottom" style="float: left;width: 95%" :max="50"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="输入框圆角">
                    <el-slider v-model="data.radius" style="float: left;width: 95%" :max="data.height/2"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="填充颜色">
                    <div class="input-color" flex="dir:left cross:center">
                        <el-color-picker :change="changeColor" size="small"
                                         v-model="data.inputBackground"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.inputBackground"></el-input>
                        <div style="margin-right: 12px;color: #606266">边框颜色</div>
                        <el-color-picker :change="changeColor" size="small"
                                         v-model="data.inputBorder"></el-color-picker>
                        <el-input size="small" style="width: 80px;" v-model="data.inputBorder"></el-input>
                    </div>
                    <div style="color: #909399;margin-left: 212px">不填默认无边框</div>
                </el-form-item>
                <el-form-item label="名称颜色">
                    <div class="input-color" flex="dir:left cross:center">
                        <el-color-picker :change="changeColor" size="small" v-model="data.inputLabel"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.inputLabel"></el-input>
                        <div style="margin-right: 12px;color: #606266">提示颜色</div>
                        <el-color-picker :change="changeColor" size="small" v-model="data.inputTip"></el-color-picker>
                        <el-input size="small" style="width: 80px;" v-model="data.inputTip"></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="文本颜色">
                    <div class="input-color" flex="dir:left cross:center">
                        <el-color-picker :change="changeColor" size="small" v-model="data.inputText"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.inputText"></el-input>

                        <div style="margin-right: 12px;color: #606266">选择框颜色</div>
                        <el-color-picker :change="changeColor" size="small"
                                         v-model="data.selectBoxColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.selectBoxColor"></el-input>

                    </div>
                </el-form-item>
                <hr>
                <div style="margin-bottom: 20px">提交按钮样式</div>
                <el-form-item label="按钮高度">
                    <el-slider v-model="data.buttonHeight" style="float: left;width: 95%" :max="160"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="按钮边距">
                    <el-slider v-model="data.buttonPadding" style="float: left;width: 95%" :max="50"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="按钮上间距">
                    <el-slider v-model="data.buttonMargin" style="float: left;width: 95%" :max="50"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="按钮圆角">
                    <el-slider v-model="data.buttonRadius" style="float: left;width: 95%" :max="data.buttonHeight/2"
                               show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="按钮文本">
                    <el-input size="small" v-model="data.borderContent"></el-input>
                </el-form-item>
                <el-form-item label="填充颜色">
                    <div class="input-color" flex="dir:left cross:center">
                        <el-color-picker :change="changeColor" size="small"
                                         v-model="data.borderBackground"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.borderBackground"></el-input>
                        <div style="margin-right: 12px;color: #606266">边框颜色</div>
                        <el-color-picker :change="changeColor" size="small"
                                         v-model="data.borderBorder"></el-color-picker>
                        <el-input size="small" style="width: 80px;" v-model="data.borderBorder"></el-input>
                    </div>
                    <div style="color: #909399;margin-left: 212px">不填默认无边框</div>
                </el-form-item>
                <el-form-item label="文本颜色">
                    <div class="input-color" flex="dir:left cross:center">
                        <el-color-picker :change="changeColor" size="small" v-model="data.borderText"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.borderText"></el-input>
                    </div>
                </el-form-item>
                <hr>
                <el-form-item label="上下边距">
                    <el-slider v-model="data.padding" style="float: left;width: 95%" :max="160" show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="顶部外边距">
                    <el-slider v-model="data.marginTop" style="float: left;width: 95%" :max="50" show-input></el-slider>
                    <div style="float: right">px</div>
                </el-form-item>
                <el-form-item label="外边距颜色">
                    <div class="input-color" flex="dir:left cross:center">
                        <el-color-picker size="small" :change="changeColor" v-model="data.marginColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.marginColor"></el-input>
                    </div>
                </el-form-item>
                <diy-bg :data="data" @update="updateData" @toggle="toggleData" @change="changeData"></diy-bg>
            </el-form>
        </div>
        <el-dialog title="表单内容设置" :visible.sync="visible">
            <app-form :value.sync="list"></app-form>
            <div slot="footer">
                <el-button size="small" type="primary" @click="addForm">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-form', {
        template: '#diy-form',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    style: 1,
                    height: 80,
                    inputPadding: 24,
                    marginBottom: 24,
                    radius: 40,
                    type: 0,
                    times: 0,
                    inputBackground: '#ffffff',
                    inputBorder: '#65d0d4',
                    inputLabel: '#353535',
                    inputTip: '#c9c9c9',
                    inputText: '#353535',
                    buttonHeight: 80,
                    buttonPadding: 24,
                    buttonMargin: 40,
                    marginTop: 10,
                    padding: 40,
                    buttonRadius: 40,
                    borderContent: '提交',
                    borderBackground: '#65d0d4',
                    borderBorder: '',
                    borderText: '#ffffff',
                    backgroundColor: '#ffffff',
                    marginColor: '#ffffff',
                    showImg: false,
                    position: 5,
                    mode: 1,
                    backgroundPicUrl: '',
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    formDialog: [],
                    selectBoxColor: '#ff4544',
                },
                visible: false,
                submitDialog: false,
                list: [],
                position: 'center center',
                repeat: 'no-repeat'
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(Object.assign(this.data, this.value)));
                this.list = this.data.formDialog;
            }
            if (this.data.mode == 2) {
                this.repeat = 'repeat-x'
            } else if (this.data.mode == 3) {
                this.repeat = 'repeat-y'
            } else if (this.data.mode == 4) {
                this.repeat = 'repeat'
            } else if (this.data.mode == 1) {
                this.repeat = 'no-repeat';
                this.data.backgroundHeight = 100;
                this.data.backgroundWidth = 100;
            }
        },
        computed: {
            changeColor() {
                if (!this.data.inputBackground) {
                    this.data.inputBackground = '#ffffff'
                }
                if (!this.data.inputLabel) {
                    this.data.inputLabel = '#353535'
                }
                if (!this.data.inputTip) {
                    this.data.inputTip = '#c9c9c9'
                }
                if (!this.data.inputText) {
                    this.data.inputText = '#353535'
                }
                if (!this.data.borderBackground) {
                    this.data.borderBackground = '#ffffff'
                }
                if (!this.data.borderText) {
                    this.data.borderText = '#353535'
                }
                if (!this.data.backgroundColor) {
                    this.data.backgroundColor = '#ffffff'
                }
                if (!this.data.marginColor) {
                    this.data.marginColor = '#ffffff'
                }
                if (!this.data.selectBoxColor) {
                    this.data.selectBoxColor = '#ff4544';
                }
            },

            cGoodsItemInfoStyle() {
                let style = '';
                if (this.data.goodsStyle === 3 || this.data.goodsStyle === 4) {
                    style += `text-align: center;`;
                }
                if (this.data.listStyle === -1) {
                    style += `height: 200px;padding: 20px 24px 20px 32px;`;
                } else {
                    style += `padding:24px 24px;`;
                }
                return style;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            linkSelected(e) {
                if (!e.length) {
                    return;
                }
                this.data.link = {
                    url: e[0].new_link_url,
                    openType: e[0].open_type,
                    data: e[0],
                };

            },
            addForm() {
                this.data.formDialog = this.list;
                this.visible = false;
            },
        }
    });
</script>