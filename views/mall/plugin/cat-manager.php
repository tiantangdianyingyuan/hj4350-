<?php
/**
 * @copyright ©2020 浙江合江信息技术有限公司
 * @author Lu Wei
 * @link https://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2020/05/27 18:02
 */
?>
<style>
.cat-group,
.cat-list {
    max-width: 800px;
}

.label-name {
    width: 120px !important;
    text-align: right;
    padding-right: 16px;
}

.cat-item {
    border: 1px solid #e5e5e5;
    padding: 8px 20px;
}

.plugin-list {
    padding: 10px;
    min-height: 60px;
    border: 1px solid #e5e5e5;
}

.plugin-item {
    display: inline-block;
    cursor: move;
    background: #f0f0f0;
    padding: 4px;
    border-radius: 3px;
    margin: 5px;
}

.plugin-item:hover {
}

.plugin-item.plugin-ghost {
    background: #707884;
    color: #fff;
}

.plugin-name {
    padding: 0 0 0 12px;
}

.plugin-item .move-icon {
    width: 14px;
}

.plugin-item .icon-move-point {
    display: none;
}

.plugin-item:hover .icon-move-point {
    display: block;
}

@keyframes xuanzhuan {
    from {
        transform: rotate(0);
    }
    to {
        transform: rotate(360deg);
    }
}

.moving {
    animation: xuanzhuan 1s infinite;
}

.submit-bar{
    border-top: 1px solid #E3E3E3;
    position: fixed;
    bottom: 0;
    background-color: #ffffff;
    z-index: 999;
    padding: 20px;
    width: 81%;
}

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/plugin/index'})">全部应用</span></el-breadcrumb-item>
                <el-breadcrumb-item>编辑应用</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
    </el-card>

    <el-card shadow="never" style="margin-bottom: 20px;">
        <div slot="header">分类设置</div>
        <div flex="box:first" class="cat-list">
            <div class="label-name">分类设置</div>
            <div>
                <div flex="box:last cross:center" class="cat-item">
                    <div></div>
                    <el-button @click="handleAddCat" size="mini">添加</el-button>
                </div>
                <template v-if="cats && cats.length">
                    <div v-for="(cat, index) in cats"
                         :key="index"
                         v-if="cat.is_delete!=1"
                         flex="box:last cross:center" class="cat-item">
                        <div>
                            <div v-if="cat.edit" style="padding: 4px 0;">
                                <el-input v-model="cat.edit.display_name"
                                          style="width: 140px; margin-right: 10px;"
                                          placeholder="请填写分类名称"
                                          size="small"></el-input>
                                <el-link @click="handleEditCatCancel(index)" style="padding: 8px" type="danger"
                                         icon="el-icon-error" :underline="false"></el-link>
                                <el-link @click="handleEditCatConfirm(index)"
                                         style="padding: 8px" type="success" icon="el-icon-success"
                                         :underline="false"></el-link>
                            </div>
                            <div v-else>
                                <b>{{cat.display_name}}</b>
                                <el-button style="padding-left: 10px; padding-right: 10px;"
                                           type="text"
                                           icon="el-icon-edit"
                                           @click="handleEditCat(index)"></el-button>
                            </div>
                            <div flex="cross:center">
                                <div style="margin-right: 20px">图标背景色</div>
                                <el-color-picker size="small" v-model="cat.color"></el-color-picker>
                            </div>
                        </div>
                        <div>
                            <el-button plain size="small" type="danger" @click="handleDeleteCat(index)"
                                       icon="el-icon-delete" circle></el-button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </el-card>
    <el-card shadow="never" style="padding-bottom: 100px">
        <div slot="header">插件分组设置</div>
        <template v-if="cats && cats.length">
            <div v-if="other_plugins && other_plugins.length" flex="box:first cross:center" class="cat-group">
                <div class="label-name">未分组</div>
                <draggable v-model="other_plugins"
                           id="other"
                           group="plugins"
                           v-bind="dragOptions"
                           :move="checkEnableMove"
                           class="plugin-list">
                    <div v-for="(plugin, pIndex) in other_plugins"
                         :key="pIndex"
                         class="plugin-item">
                        <div flex>
                            <div class="plugin-name">{{plugin.display_name}}</div>
                            <div class="move-icon">
                                <i class="iconfont icon-move-point"></i>
                            </div>
                        </div>
                    </div>
                </draggable>
            </div>
            <div v-for="(cat, index) in cats"
                 :key="index"
                 v-if="cat.is_delete!=1"
                 flex="box:first cross:center"
                 class="cat-group">
                <div class="label-name">{{cat.display_name}}</div>
                <draggable v-model="cat.plugins"
                           group="plugins"
                           v-bind="dragOptions"
                           :move="checkEnableMove"
                           class="plugin-list">
                    <div v-for="(plugin, pIndex) in cat.plugins"
                         :key="pIndex"
                         class="plugin-item">
                        <div flex="cross:center">
                            <div class="plugin-name">{{plugin.display_name}}</div>
                            <div class="move-icon">
                                <i class="iconfont icon-move-point"></i>
                            </div>
                        </div>
                    </div>
                </draggable>
            </div>
        </template>
    </el-card>
    <div class="submit-bar">
        <el-button type="primary" @click="confirm" :loading="confirming">保存</el-button>
    </div>
</div>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/sortablejs@1.8.4/Sortable.min.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.20.0/dist/vuedraggable.umd.min.js"></script>
<script>
new Vue({
    el: '#app',
    data() {
        return {
            cats: null,
            other_plugins: null,
            loading: false,
            showAddCat: false,
            addCatDisplayName: '',
            addCatColor: '',
            saveCatSubmitLoading: false,
            showEditCat: false,
            editCat: null,
            editCatIndex: null,
            dragOptions: {
                animation: 200,
                group: "plugins",
                disabled: false,
                ghostClass: "plugin-ghost",
            },
            waitToSaveRel: [],
            savingRel: false,
            confirming: false,
        };
    },
    created() {
        this.loadData();
    },
    methods: {
        loadData() {
            this.loading = true;
            this.$request({
                params: {
                    r: 'mall/plugin/cat-manager'
                },
            }).then(e => {
                this.loading = false;
                if (e.data.code === 0) {
                    for (let i in e.data.data.cats) {
                        e.data.data.cats[i].edit = null;
                        for (let j in e.data.data.cats[i].plugins) {
                            e.data.data.cats[i].plugins[j].moving = false;
                        }
                    }
                    this.cats = e.data.data.cats;
                    this.other_plugins = e.data.data.other_plugins;
                }
            }).catch(e => {
                this.loading = false;
            });
        },
        handleDeleteCat(index) {
            this.$confirm('被删除分类下的原有插件，将被划分到未分组，是否确认删除？', '警告！', {
                type: 'warning'
            }).then(e => {
                if (!this.other_plugins) {
                    this.other_plugins = [];
                }
                this.other_plugins = this.other_plugins.concat(this.cats[index].plugins);
                this.cats[index].plugins = [];
                this.cats[index].is_delete = 1;
            }).catch(e => {
            });
        },
        handleEditCat(index) {
            this.cats[index].edit = {
                display_name: this.cats[index].display_name,
            };
        },
        handleEditCatConfirm(index) {
            let displayName = this.cats[index].edit.display_name;
            displayName = displayName.trim();
            if (typeof displayName === 'undefined' || displayName === null || displayName === '' || displayName === false) {
                this.$message.error('分类名称不能为空。');
                return;
            }
            if (displayName.length > 6) {
                this.$message.error('分类名称不能超过6个字。');
                return;
            }
            this.cats[index].display_name = this.cats[index].edit.display_name;
            this.cats[index].edit = null;
        },
        handleEditCatCancel(index) {
            this.cats[index].edit = null;
        },
        handleAddCat() {
            this.cats.push({
                id: null,
                name: randomString(16).toLowerCase(),
                display_name: '',
                color: '',
                edit: {
                    display_name: '',
                },
                plugins: [],
            });
        },
        confirm() {
            this.confirming = true;
            let cats = [];
            for (let i in this.cats) {
                let cat = this.cats[i];
                if (cat.id == null && cat.is_delete == 1) {
                    continue;
                }
                if (cat.display_name === '') {
                    this.confirming = false;
                    this.$message.error('插件分类名称未填写。');
                    return;
                }
                let plugins = [];
                for (let j in cat.plugins) {
                    plugins.push({
                        name: cat.plugins[j].name,
                        sort: parseInt(j) + 1,
                    });
                }
                cats.push({
                    id: cat.id,
                    name: cat.name,
                    display_name: cat.display_name,
                    color: cat.color,
                    is_delete: cat.is_delete,
                    plugins: plugins,
                });
            }
            this.$request({
                method: 'post',
                params: {
                    r: 'mall/plugin/save-cat',
                },
                data: {
                    cats: JSON.stringify(cats),
                },
            }).then(e => {
                if (e.data.code === 0) {
                    this.$message.success('保存成功。');
                    setTimeout(() => {
                        this.$navigate({r: 'mall/plugin/index'});
                    }, 500);
                } else {
                    this.confirming = false;
                    this.$message.error(e.data.msg);
                }
            }).catch(() => {
                this.confirming = false;
            });
        },
        checkEnableMove(e) {
            if (e.to.id === 'other') return false;
            return true;
        },
    }
});
</script>
