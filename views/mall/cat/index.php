<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

$components = [
    'app-cat-list',
    'app-transfer',
    'app-style'
];
$html = "";
foreach ($components as $component) {
    $html .= $this->renderFile(__DIR__ . "/{$component}.php") . "\n";
}
echo $html;
?>
<style>
    .new-table-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .el-dialog__wrapper .el-dialog {
        min-width: 0;
    }
    .el-dialog__wrapper .el-dialog__body {
        padding: 10px 20px;
    }
    .el-dialog__wrapper .el-dialog__footer {
        padding: 10px 20px;
    }
    .el-dialog__wrapper .icon {
        font-size: 20px;
        margin-right: 5px;
        color: #E6A23C;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分类列表</span>
                <div style="float: right;margin-top: -5px" flex="dir:left">
                    <template v-if="activeName == 'first'">
                        <el-button style="margin-right: 10px;" type="primary" @click="edit" size="small">添加分类
                        </el-button>
                        <el-button type="primary" @click="exportCat" size="small">分类导出</el-button>
                    </template>
                </div>
            </div>
        </div>
        <div class="new-table-body">
            <template>
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="商品分类" name="first">
                        <app-cat-list @select="catSelect"></app-cat-list>
                    </el-tab-pane>
                    <el-tab-pane label="商品分类转移" name="second">
                        <app-transfer></app-transfer>
                    </el-tab-pane>
                    <el-tab-pane label="分类样式" name="third">
                        <app-style></app-style>
                    </el-tab-pane>
                </el-tabs>
            </template>
        </div>

        <el-dialog
                title="提示"
                :visible.sync="dialogVisible"
                style="margin-top: 20vh;"
                width="25%">
            <div flex="dir:left cross:center">
                <i class="el-icon-warning icon"></i>
                <span>选中{{catIdList.length}}个一级分类，是否确认导出?</span>
            </div>
            <div slot="footer">
                <form target="_blank" action="<?= Yii::$app->urlManager->createUrl('mall/cat/index') ?>"
                      method="post">
                    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <input name="flag" value="EXPORT" type="hidden">
                    <input name="choose_list" v-model="catIdList" type="hidden">
                    <el-button size="small" @click="dialogVisible = false">取消</el-button>
                    <button v-if="catIdList.length > 0" type="submit" @click="dialogVisible = false"
                            class="el-button el-button--primary el-button--small">确定
                    </button>
                </form>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
                catIdList: [],
                dialogVisible: false,
            };
        },
        methods: {
            handleClick(tab, event) {
                console.log(tab, event);
            },
            // 编辑
            edit(id) {
                navigateTo({
                    r: 'mall/cat/edit',
                });
            },
            exportCat() {
                if (this.catIdList.length <= 0) {
                    this.$message.warning('请先勾选要导出的分类');
                    return;
                }
                this.dialogVisible = true;
            },
            catSelect(res) {
                this.catIdList = res;
            }
        },
        mounted: function () {
        },
    });
</script>
