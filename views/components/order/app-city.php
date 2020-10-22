<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-city .sendForm input::-webkit-outer-spin-button, .app-city .sendForm input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .app-city .sendForm input[type="number"] {
        -moz-appearance: textfield;
    }
</style>

<template id="app-city">
    <div class="app-city">
        <!-- 配送员选择 -->
        <el-dialog title="选择配送员" :visible.sync="city.dialog" @close="closeDialog" width="35%">
            <el-form v-loading="dialogLoading" label-width="130px" class="sendForm" @submit="prevD">
                <el-form-item label="配送员" prop="city_name" hidden>
                    <el-input></el-input>
                </el-form-item>
                <el-form-item label="配送员" prop="city_name">
                    <el-autocomplete @keyup.enter.native="prevD" v-if="city.list.length > 0"
                                     size="small"
                                     v-model="city.man"
                                     :fetch-suggestions="searchCity"
                                     placeholder="请选择配送员"
                    ></el-autocomplete>
                    <template v-else>
                        <div>未设置配送员，
                            <el-button type="text" @click="$navigate({r:'mall/delivery/index'}, true)">请前往同城配送设置
                            </el-button>
                        </div>
                    </template>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="city.dialog = false">取 消</el-button>
                    <el-button size="small" type="primary" :loading="submitLoading"
                               @click="citySend">确 定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-city', {
        template: '#app-city',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            order: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            sendType: {
                type: String,
                default: '',
            },
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openDialog()
                }
            }
        },
        data() {
            return {
                submitLoading: false,
                dialogLoading: false,
                city: {
                    dialog: false,
                    list: [],
                    man: null,
                    is_express: 2
                }
            }
        },
        methods: {
            // 打开备注
            openDialog() {
                this.city.dialog = true;
                this.city.order_id = this.order.id
                if (this.sendType === 'change') {
                    let deliveryman = JSON.parse(this.order.city_info);
                    this.city.man = '(' + deliveryman.id + ')' + this.order.city_name;
                }
                this.getDeliveryman();
            },
            closeDialog() {
                this.$emit('close')
            },
            getDeliveryman() {
                this.dialogLoading = true;
                request({
                    params: {
                        r: 'mall/delivery/man-list'
                    },
                }).then(e => {
                    this.dialogLoading = false;
                    if (e.data.code == 0) {
                        this.city.list = e.data.data.list;
                        for (let i = 0; i < this.city.list.length; i++) {
                            this.city.list[i].value = '(' + this.city.list[i].id + ')' + this.city.list[i].name
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            searchCity(queryString, cb) {
                let deliveryman = this.city.list;
                let results = queryString ? deliveryman.filter((deliveryman) => {
                    return (deliveryman.value.toLowerCase().indexOf(queryString.toLowerCase()) != -1);
                }) : deliveryman;
                cb(results);
            },
            citySend() {
                this.submitLoading = true;
                request({
                    params: {
                        r: 'mall/order/send',
                    },
                    data: {
                        man: this.city.man,
                        is_express: this.city.is_express,
                        order_id: this.city.order_id,
                    },
                    method: 'post',
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        this.city.dialog = false;
                        this.$emit('submit')
                    } else {
                        this.$alert(e.data.msg, {
                            confirmButtonText: '确定'
                        });
                    }
                }).catch(e => {
                    this.submitLoading = false;
                });
            },
            prevD() {

            },
        }
    })
</script>