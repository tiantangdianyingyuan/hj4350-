<?php
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading" shadow="never">
        <div slot="header">开放时间</div>
        <div class="form-body">
            <el-form label-width="0" size="small">
                <el-row>
                    <el-col :span="14">
                        <el-form-item>
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll"
                                         @change="handleCheckAllChange">
                                全选
                            </el-checkbox>
                            <div style="margin: 15px 0;"></div>
                            <el-checkbox-group v-model="checkedCities" @change="handleCheckedCitiesChange">
                                <div style="width: 120px; display: inline-block" v-for="option in options">
                                    <el-checkbox :label="option.value" :key="option.value">
                                        {{option.label}}
                                    </el-checkbox>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const options = [
        {
            label: '00:00~00:59',
            value: '0',
        }, {
            label: '01:00~01:59',
            value: '1',
        }, {
            label: '02:00~02:59',
            value: '2',
        }, {
            label: '03:00~03:59',
            value: '3',
        }, {
            label: '04:00~04:59',
            value: '4',
        }, {
            label: '05:00~05:59',
            value: '5',
        }, {
            label: '06:00~06:59',
            value: '6',
        }, {
            label: '07:00~07:59',
            value: '7',
        }, {
            label: '08:00~08:59',
            value: '8',
        }, {
            label: '09:00~09:59',
            value: '9',
        }, {
            label: '10:00~10:59',
            value: '10',
        }, {
            label: '11:00~11:59',
            value: '11',
        }, {
            label: '12:00~12:59',
            value: '12',
        }, {
            label: '13:00~13:59',
            value: '13',
        }, {
            label: '14:00~14:59',
            value: '14',
        }, {
            label: '15:00~15:59',
            value: '15',
        }, {
            label: '16:00~16:59',
            value: '16',
        }, {
            label: '17:00~17:59',
            value: '17',
        }, {
            label: '18:00~18:59',
            value: '18',
        }, {
            label: '19:00~19:59',
            value: '19',
        }, {
            label: '20:00~20:59',
            value: '20',
        }, {
            label: '21:00~21:59',
            value: '21',
        }, {
            label: '22:00~22:59',
            value: '22',
        }, {
            label: '23:00~23:59',
            value: '23',
        },
    ];
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                checkAll: false,
                checkedCities: [],
                options: options,
                isIndeterminate: false
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            store() {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/miaosha/mall/index/open-time'
                    },
                    method: 'post',
                    data: {
                        open_time: this.checkedCities
                    }
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/miaosha/mall/index'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.checkedCities = e.data.data.detail.open_time;
                        if (this.checkedCities.length == this.options.length) {
                            this.checkAll = true;
                        } else if (this.checkedCities.length > 0 && this.checkedCities.length < this.options.length) {
                            this.isIndeterminate = true;
                        } else {
                            this.isIndeterminate = false;
                        }
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            handleCheckAllChange(val) {
                let arr = [];
                this.options.forEach(function (item) {
                    arr.push(item.value)
                });
                this.checkedCities = val ? arr : [];
                this.isIndeterminate = false;
            },
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.options.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.options.length;
            }
        }
    });
</script>
