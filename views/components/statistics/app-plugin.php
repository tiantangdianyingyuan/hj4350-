<?php defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
$mch_id = Yii::$app->user->identity->mch_id;
?>
<style>
    .app-plugin .info-data {
        text-align: center;
        flex-grow: 1;
        border-left: 1px dashed #EFF1F7;
    }

    .app-plugin .info-data:first-of-type {
        border-left: 0;
    }

    .app-plugin .info-data .value {
        color: #409EFF;
        cursor: pointer;
        font-size: 28px;
    }

    .app-plugin .info-data .label {
        color: #92959B;
        font-size: 16px;
    }
</style>
<template id="app-plugin">
    <div class="app-plugin">
        <el-card v-if="!Boolean(mchId) && pluginMenus && pluginMenus.length" shadow="never" style="margin-bottom: 10px">
            <div slot="header">
                <span>插件统计</span>
            </div>
            <div style="overflow-x: auto">
                <div flex="dir:left"
                     style="padding:5px 20px;flex-wrap: wrap">
                    <div @click="$navigate({r:plugin.route}, true)"
                         v-for="plugin in pluginMenus"
                         flex="dir:top cross:center main:center"
                         style="cursor: pointer;min-width: 60px;margin: 5px 30px">
                        <img :src="plugin.pic_url"
                             style="height: 50px;width: 50px;display: block" alt=""/>
                        <span v-if="plugin.name" style="margin-top: 3px">{{plugin.name}}</span>
                    </div>
                </div>
            </div>
        </el-card>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-plugin', {
        template: '#app-plugin',
        data() {
            return {
                pluginMenus: new Array(),
                column: 14,
            }
        },
        props: {
            mchId: Number,
        },
        mounted() {
            if (!Boolean(this.mchId)) {
                this.getData();
            }
        },
        computed: {
            newData() {
                return this.group(this.addNull(), this.column);
            },
        },
        methods: {
            addNull: function () {
                let columns = this.column;
                let length = this.pluginMenus.length;
                let addNum = length % columns === 0 ? 0 : columns - length % columns;

                return this.pluginMenus.concat(new Array(addNum).fill({
                    'key': '',
                    'pic_url': '',
                    'name': '',
                    'route': '',
                    'temp': true,
                }))
            },
            group: function (array, subGroupLength) {
                subGroupLength = parseInt(subGroupLength);
                let index = 0;
                let newArray = [];
                while (index < array.length) {
                    newArray.push(array.slice(index, index += subGroupLength));
                }
                return newArray;
            },
            getData() {
                request({
                    params: {
                        r: 'mall/data-statistics/plugin-menus',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.pluginMenus = e.data.menus;
                    }
                })
            },
        }
    });
</script>