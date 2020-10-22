<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .app-form .options {
        background-color: #F3F5F6;
        padding: 15px !important;
        margin-right: 40px;
        min-width: 180px;
    }

    .app-form .options .options-item:first-of-type {
        margin-top: 0
    }

    .app-form .options .options-item {
        height: 32px;
        line-height: 32px;
        margin-top: 10px;
        background-color: #fff;
        cursor: pointer;
        display: flex;
        justify-content: center;
    }

    .app-form .options .options-item img {
        margin: 9px 5px 9px 0;
        display: block;
        float: left;
        height: 14px;
        width: 14px;
    }

    .app-form .form-item .el-form-item__label {
        width: 100px !important;
    }

    .app-form .form-item .el-form-item__content {
        margin-left: 100px !important;
        width: 60% !important;
        position: relative;
    }

    .app-form .outline {
        position: absolute;
        right: -30px;
        top: 0;
        line-height: 32px;
        height: 32px;
        color: #F56E6E;
        cursor: pointer;
        font-size: 22px;
    }

    .app-form .form-item {
        background-color: #fff;
        padding: 22px;
        position: relative;
        cursor: move;
    }

    .app-form .delete-btn {
        position: absolute;
        top: 20px;
        right: 20px;
    }

    .app-form .required {
        width: 80px;
        height: 32px;
    }

    .app-form .required.active {
        border: 1px solid #3399FF;
        color: #3399FF;
    }

    .app-form .name {
        display: inline-block;
        margin-left: 20px;
        width: 80px;
        height: 32px;
        line-height: 30px;
        text-align: center;
        background-color: #F4F4F5;
        color: #909399;
        border: 1px solid #E0E0E3;
        border-radius: 3px;
        font-size: 12px;
    }

    .app-form .form-item .el-form-item {
        margin-bottom: 22px;
    }
    .app-form .top {
        height:15px;
        background:#F3F5F6;
        width:calc(100% - 40px);
    }
</style>
<template id="app-form">
    <el-row class="app-form">
        <el-col class="options" :span="3">
            <div class="options-item" @click="handleChange(item)" v-for="item in options">
                <div style="width: 50%">
                    <img :src="item.img" alt="">{{item.label}}
                </div>
            </div>
        </el-col>
        <el-col :span="15">
            <div v-if="list && list.length" class="top"></div>
            <draggable v-model="list" @update='update'>
                <div style="padding: 0 15px 15px !important;" class="options" v-for="(item,index) in list">
                    <el-form @submit.native.prevent :model="item" :rules="form_rules" ref="form" class="form-item">
                        <el-button size="small" class="delete-btn" circle type="text" @click="formDestroy(index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <div style="margin-bottom: 15px;">
                            <el-button size="small" @click="toggle(item)" plain class="required active"
                                       v-if="item.is_required">必填
                            </el-button>
                            <el-button size="small" @click="toggle(item)" plain v-else class="required">非必填
                            </el-button>
                            <div class="name">{{item.key_name}}</div>
                        </div>
                        <el-form-item prop="key_name">
                            <template slot='label'>
                                <span>名称</span>
                                <el-tooltip effect="dark" content="名称建议不超过4个字"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input size="small" v-model="item.name" placeholder="请输入名称"></el-input>
                        </el-form-item>
                        <el-form-item label="默认值" v-if="item.key === 'date'">
                            <el-date-picker
                                    size="small"
                                    value-format="yyyy-MM-dd"
                                    v-model="item.default"
                                    type="date"
                                    placeholder="选择日期">
                            </el-date-picker>
                        </el-form-item>

                        <el-form-item label="日期区间" v-if="item.key === 'date' && is_date_range">
                            <el-date-picker
                                    size="small"
                                    value-format="yyyy-MM-dd"
                                    v-model="item.range"
                                    @change="timeSet(item)"
                                    type="daterange"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label="默认值" v-if="item.key === 'time'">
                            <el-time-picker
                                    value-format="HH:mm"
                                    range-separator=":"
                                    size="small"
                                    v-model="item.default"
                                    placeholder="选择时间">
                            </el-time-picker>
                        </el-form-item>
                        <el-form-item label="时间区间" v-if="item.key === 'time' && is_time_range">
                            <el-time-picker
                                    size="small"
                                    value-format="HH:mm"
                                    is-range
                                    v-model="item.range"
                                    @change="timeSet(item)"
                                    range-separator="至"
                                    start-placeholder="开始时间"
                                    end-placeholder="结束时间">
                            </el-time-picker>
                        </el-form-item>

                        <el-form-item label="默认值" v-if="item.key === 'text'">
                            <el-input size="small" v-model="item.default" placeholder="默认值"></el-input>
                        </el-form-item>
                        <el-form-item label="提示语" v-if="item.key === 'text'">
                            <el-input size="small" v-model="item.hint" placeholder="提示语"></el-input>
                        </el-form-item>
                        <el-form-item label="默认值" v-if="item.key === 'textarea'">
                            <el-input size="small" :rows="1"
                                      type="textarea"
                                      v-model="item.default"
                                      placeholder="默认值">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="提示语" v-if="item.key === 'textarea'">
                            <el-input size="small" :rows="1"
                                      type="textarea"
                                      v-model="item.hint"
                                      placeholder="提示语">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="多选选项" v-if="item.key === 'checkbox'"
                                      v-for="(check,index) in item.list" :key="index">
                            <el-input size="small" v-model="check.label"
                                      placeholder="选项名称">
                            </el-input>
                            <i @click="lose(item,index)"
                               class="el-icon-remove-outline outline"></i>
                        </el-form-item>
                        <el-form-item label="单选选项" v-if="item.key === 'radio'"
                                      v-for="(check,index) in item.list" :key="index">
                            <el-input size="small" v-model="check.label"
                                      placeholder="选项名称">
                            </el-input>
                            <i @click="lose(item,index)"
                               class="el-icon-remove-outline outline"></i>
                        </el-form-item>
                        <el-form-item class="img-type" label="图片类型"
                                      v-if="item.key === 'img_upload'">
                            <el-radio v-for="(check,index) in item.list"
                                      :key="item.index"
                                      v-model="item.img_type"
                                      :label="check.value">
                                {{check.label}}
                            </el-radio>
                        </el-form-item>
                        <el-form-item label="上传数量" v-if="item.key === `img_upload` && item.img_type ==1">
                            <el-input-number :default="item.num? '':item.num = 1"
                                             v-model="item.num"
                                             size="small"
                                             :min="1"
                                             :max="6"
                            ></el-input-number>
                        </el-form-item>
                        <el-form-item style="margin-bottom: 0"
                                      v-if="item.key === 'checkbox' || item.key === 'radio'">
                            <el-button type="text" @click="add(item)">
                                <i class="el-icon-plus">新增选项</i>
                            </el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </draggable>
        </el-col>
    </el-row>
</template>

<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    Vue.component('app-form', {
        template: '#app-form',
        props: {
            value: {
                type: Array
            },
            is_mch: {
                type: Boolean,
                default: false
            },
            is_date_range: {
                type: Boolean,
                default: false,
            },
            is_time_range: {
                type: Boolean,
                default: false,
            }
        },
        data() {
            return {
                list: [],
                options: [
                    {
                        value: 'text',
                        label: '单行文本',
                        img: 'statics/img/mall/order-form/text.png'
                    },
                    {
                        value: 'textarea',
                        label: '多行文本',
                        img: 'statics/img/mall/order-form/textarea.png'
                    },
                    {
                        value: 'date',
                        label: '日期选择',
                        img: 'statics/img/mall/order-form/date.png'
                    },
                    {
                        value: 'time',
                        label: '时间选择',
                        img: 'statics/img/mall/order-form/time.png'
                    },
                    {
                        value: 'radio',
                        label: '单选',
                        img: 'statics/img/mall/order-form/radio.png',
                        children: [
                            {
                                value: 1,
                                label: '1个',
                            },
                            {
                                value: 2,
                                label: '2个',
                            },
                            {
                                value: 3,
                                label: '3个',
                            },
                        ]
                    },
                    {
                        value: 'checkbox',
                        label: '多选',
                        img: 'statics/img/mall/order-form/checkbox.png',
                        children: [
                            {
                                value: 1,
                                label: '1个',
                            },
                            {
                                value: 2,
                                label: '2个',
                            },
                            {
                                value: 3,
                                label: '3个',
                            },
                        ]
                    },
                    {
                        value: 'img_upload',
                        label: '图片上传',
                        img: 'statics/img/mall/order-form/img_upload.png'
                    },
                ],
                form_rules: {
                    key_name: [
                        {required: true, message: '请输入名称', trigger: 'change'},
                    ],
                },
            };
        },
        mounted() {
            this.list = this.value;
        },
        methods: {
            timeSet(e) {
                e.min = e.range ? e.range[0] : '';
                e.max = e.range ? e.range[1] : '';
            },
            update() {
                this.$emit('update:value', this.list)
            },

            toggle(row) {
                row.is_required = row.is_required ? 0 : 1;
            },

            lose(row, index) {
                row.list.splice(index, 1)
            },

            add(row) {
                row.list.push({
                    label: "",
                    value: "false"
                })
            },
            // 添加表单
            handleChange(item) {
                let self = this;
                let value = {
                    key: item.value,
                    key_name: item.label,
                    name: '',
                    is_required: 0,
                }
                if (item.value == 'checkbox' || item.value == 'radio') {
                    value.list = [{
                        label: "",
                        value: "false"
                    }]
                }
                if (item.value == 'img_upload') {
                    value.img_type = '1';
                    value.num = 1;
                    value.list = [
                        {
                            label: "普通图片",
                            value: '1'
                        },
                        {
                            label: "身份证",
                            value: '2'
                        },
                        {
                            label: "营业执照",
                            value: '3'
                        },
                    ]
                }
                this.list.push(value)
                this.$emit('update:value', this.list)
            },
            // 删除表单
            formDestroy(index) {
                this.value.splice(index, 1);
                this.$emit('update:value', this.list)
            }
        }
    });
</script>