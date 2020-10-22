<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/5
 * Time: 17:19
 */
Yii::$app->loadViewComponent('goods/app-select-coupon');
?>
<template id="diy-ad">
    <div>
        <div class="diy-component-preview">
            <div style="padding: 50px 0;text-align: center;background: #fff;">这是一个流量主广告位</div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item>
                    <el-alert style="line-height: normal;" :closable="false"
                              type="warning" title="流量主广告需要申请开通流量主功能。"></el-alert>
                </el-form-item>
                <el-form-item label="广告位类型" prop="type">
                    <el-radio-group v-model="data.type" style="line-height: 35px">
                        <el-radio label="">Banner</el-radio>
                        <el-radio label="rewarded-video">激励式视频</el-radio>
                        <el-radio label="interstitial">插屏</el-radio>
                        <el-radio label="video">视频广告</el-radio>
                        <el-radio label="before-video">前贴视频</el-radio>
                        <el-radio label="grid" v-if="false">格子广告</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="广告位ID" prop="id">
                    <el-input size="small" v-model="data.id"></el-input>
                </el-form-item>

                <el-form-item v-if="data.type === 'before-video'" label="视频播放链接" prop="video_url">
                    <el-input v-model="data.video_url" placeholder="请输入视频原地址或选择上传视频">
                        <template slot="append">
                            <app-attachment :multiple="false" :max="1" v-model="data.video_url" type="video">
                                <el-tooltip class="item" effect="dark" content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB"
                                            placement="top">
                                    <el-button size="mini">添加视频</el-button>
                                </el-tooltip>
                            </app-attachment>
                        </template>
                    </el-input>
                    <el-link class="box-grow-0" type="primary" style="font-size:12px"
                             v-if='data.video_url' :underline="false" target="_blank"
                             :href="data.video_url">视频链接
                    </el-link>
                </el-form-item>
                <el-form-item v-if="['interstitial', 'rewarded-video', 'before-video'].includes(data.type)"
                              prop="pic_url" label="广告封面">
                    <app-attachment v-model="data.pic_url" :multiple="false" :max="1">
                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 287" placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <div class="customize-share-title">
                        <app-image mode="aspectFill" width='80px' height='80px' :src="data.pic_url"></app-image>
                    </div>
                </el-form-item>

                <el-form-item label="广告奖励" prop="award_type" v-if="data.type == 'rewarded-video'">
                    <el-radio-group v-model="data.award_type">
                        <el-radio label="0">无</el-radio>
                        <el-radio label="1">积分</el-radio>
                        <el-radio label="2">优惠券</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="积分数量" prop="num" v-if="data.award_type == 1 && data.type == 'rewarded-video'">
                    <el-input style="max-width: 180px" v-model="data.award_num" size="small" type="number"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="优惠券" prop="award_coupons"
                              v-if="data.award_type == 2 && data.type == 'rewarded-video'">
                    <el-tag style="margin:5px"
                            v-for="(tag,i) in data.award_coupons"
                            :key="i"
                            closable
                            @close="couponClose(data,i)">
                        {{tag.send_num}}张 | {{tag.name}}
                    </el-tag>
                    <app-select-coupon v-model="data.award_coupons">
                        <el-button class="button-new-tag" size="small">新增优惠券</el-button>
                    </app-select-coupon>
                </el-form-item>

                <el-form-item label="奖励发放限制" prop="award_limit_type" v-if="data.type == 'rewarded-video'">
                    <el-radio-group v-model="data.award_limit_type">
                        <el-radio label="0">无限制</el-radio>
                        <el-radio label="1">每人{{data.award_limit}}次</el-radio>
                        <el-radio label="2">每天{{data.award_limit}}次</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="限制次数" prop="award_limit" v-if="data.type == 'rewarded-video' && data.award_limit_type !=0">
                    <el-input style="max-width: 180px"
                              v-model="data.award_limit"
                              size="small"
                              type="number"
                              min="0"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                              autocomplete="off"
                    ></el-input>
                </el-form-item>

            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-ad', {
        template: '#diy-ad',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    id: '',
                    type: '',
                    pic_url: '',
                    video: '',
                    award_type: '0',
                    award_coupons: [
                        /*{
                            send_num: 3,
                                name: '优惠券',
                            coupon_id: 710,
                        } */
                    ],
                    award_num: '',
                    award_limit_type: '0',
                    award_limit: 0,
                },
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            couponClose(row, index) {
                row.award_coupons.splice(index, 1);
            },
        }
    });
</script>
