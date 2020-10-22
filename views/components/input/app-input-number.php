<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/8/27
 * Time: 2:05 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<template id="app-input-number">
    <el-input :placeholder="placeholder" :size="size" :disabled="disabled" :min="min"
              @compositionstart.native="start" @compositionend.native="end" @input.native="input"
              v-model="data"></el-input>
</template>
<script>
    Vue.component('app-input-number', {
        template: '#app-input-number',
        props: {
            placeholder: String,
            value: String | Number,
            size: {
                type: String,
                default() {
                    return 'small';
                }
            },
            disabled: Boolean,
            min: Number,
            decimal: {
                type: Number,
                default() {
                    return 0;
                }
            },
        },
        data() {
            return {
                cpLock: true,
                data: '',
            }
        },
        methods: {
            start(e) {
                this.cpLock = false;
            },
            end(e) {
                this.cpLock = true;
                this.setData(e.target.value)
            },
            input(e) {
                if (this.cpLock) {
                    this.setData(e.target.value)
                }
            },
            setData(value) {
                switch (this.decimal) {
                    case 0: // 匹配整数
                        this.data = value.replace(/[^0-9]+/, '');
                        break;
                    case 2: // 保留两位小数
                        let result = /[0-9]+\.?[0-9]{0,2}/.exec(value);
                        this.data = result ? result[0] : '';
                        break;
                    default:
                        this.data = value;
                }
            }
        },
        watch: {
            data: {
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
            value: {
                handler(newVal, oldVal) {
                    this.data = JSON.parse(JSON.stringify(this.value));
                },
            }
        },
    });
</script>
