<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<?php
$diyPath = \Yii::$app->viewPath . '/components/diy';
$currentDir = opendir($diyPath);
$mallComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $mallComponents[] = substr($file, 4, (stripos($file, '.php') - 4));
    }
}
closedir($currentDir);
foreach ($mallComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", $diyPath);
}
$currentDir = opendir(__DIR__);
$diyComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $temp = substr($file, 4, (stripos($file, '.php') - 4));
        if (!in_array($temp, $mallComponents)) {
            $diyComponents[] = $temp;
        }
    }
}
closedir($currentDir);
foreach ($diyComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", __DIR__);
}
$components = array_merge($diyComponents, $mallComponents);
?>

<style>
    .box {
        width: 320px;
        height: 64px;
        background: rgba(255, 255, 255, 1);
        border: 1px dashed rgba(221, 221, 221, 1);
        border-radius: 4px;
        line-height: 64px;
        padding: 0 20px;
        color: #000000;
        margin-right: 20px;
    }

    .diy-module .el-form-item--small .el-form-item__content {
        width: 100%;
    }

    .diy-module .el-radio__label {
        display: none;
    }

    .diy-module .has-gutter tr th:before {
        display: none;
    }

    .diy-module .pane-second {
        del-pointer-events: none !important;
    }

    /** 重构莫些属性 */
    .diy-module .pane-second .diy-component-edit {
        display: none !important;
    }

    .diy-module .pane-second .diy-component-preview {
        cursor: pointer;
        position: relative;
        zoom: 1;
        -moz-transform: scale(1);
        border-width: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
    }

    .diy-module .el-table__header-wrapper .has-gutter tr th:first-of-type .cell {
        display: none !important;
    }

    .diy-module .add-btn.el-button--primary.is-plain {
        color: #409EFF;
        background: #FFFFFF !important;;
        border-color: #b3d8ff;
    }

    .diy-module .edit-nav-item {
        border: 1px solid #e2e2e2;
        line-height: normal;
        padding: 5px;
        margin-bottom: 5px;
        cursor: move;
    }

    .diy-module .nav-edit-options {
        position: relative;
    }

    .diy-module .nav-edit-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .diy-module .scroll {
        white-space: nowrap;
        height: 90px;
        overflow-x: auto;
        box-shadow: inset -12px 0 10px -10px #555555;
        position: relative;
    }
    .diy-module .scroll-right {
        width: 2px;
        position: absolute;
        height: 90px;
        top: 0;
        right: 0;
        z-index: 22;
        box-shadow: -4px 0 12px #555555;
    }

    .diy-module .scroll .scroll-item {
        flex: 0 0 auto;
        height: 100%;
    }

    .diy-module .scroll .tab-name {
        font-size: 28px;
        line-height: 1;
        color: #666666;
        padding: 12px 0;
    }

    .diy-module .scroll .line {
        height: 4px;
        border-radius: 16px;
        width: 100%;
    }

    .diy-component-edit .el-table .cell, .el-table th div {
        text-overflow: clip !important;
    }
</style>
<template id="diy-module">
    <div class="diy-module">
        <div class="diy-component-preview">
            <div class="pane-second" v-if="data.list && data.list.length" style="position: relative">
                <div class="scroll"
                     v-if="data.list.length > 1"
                     :style="{backgroundColor: data.tabBackground}">
                    <div flex="dir:left" style="height: 100%;position: relative">
                        <div v-for="(item,index) in value.list" :index="index"
                             @click="changeTab(index)"
                             flex="main:center cross:center"
                             class="scroll-item"
                             :style="[scrollStyle(index)]">
                            <div :style="[tabNameFill(index)]">
                                <div class="tab-name" :style="[tabNameStyle(index)]">{{item.tabName}}</div>
                                <div v-if="data.tabType === 'line'" class="line"
                                     :style="{background: current === index ? data.tabColor : 'none'}"
                                ></div>
                            </div>
                        </div>
                        <div v-if="data.list && data.list.length > 4"
                             style="display: flex;box-grow: 0,width: 20px;height: 100%"></div>
                    </div>
                </div>
                <div v-if="false && data.list.length > 4" :style="{background: data.tabBackground}"
                     class="scroll-right"></div>
                <div v-for="(component, index) in data.list[current]['data']" :key="index +'-'+ current">
                    <?php foreach ($components as $component) : ?>
                        <diy-<?= $component ?> v-if="component.id === '<?= $component ?>'"
                                               :active="false"
                                               v-model="component.data"
                        ></diy-<?= $component ?>>
                    <?php endforeach; ?>
                </div>
            </div>
            <div v-else>
                <div style="padding: 50px 0;text-align: center;background: #fff;">请选择自定义模块</div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="120px">
                <template v-if="data.list && data.list.length > 1">
                    <el-form-item label="标签类型">
                        <div flex="dir:left">
                            <div flex="dir:top cross:center main:center"
                                 style="cursor: pointer;border-radius:3px;height: 100px;width: 100px"
                                 @click="data.tabType = 'line'"
                                 :style="{border: `1px solid ${data.tabType == 'line' ? '#409EFF':'#DDDDDD'}`}">
                                <div style="height: 75px" flex="cross:center main:center">
                                    <img src="statics/img/mall/diy/img_tab1.png" alt=""
                                         style="width: 83px;height: 21px">
                                </div>
                                <div :style="{color: `${data.modeType == 'line' ? '#409EFF':'#666666'}`}">线条标签</div>
                            </div>
                            <div flex="dir:top cross:center main:center"
                                 style="cursor: pointer;border-radius:3px;margin-left: 16px;height: 100px;width:100px"
                                 @click="data.tabType = 'filling'"
                                 :style="{border: `1px solid ${data.tabType == 'filling' ? '#409EFF':'#DDDDDD'}`}">
                                <div style="height: 75px" flex="cross:center main:center">
                                    <img src="statics/img/mall/diy/img_tab2.png" alt=""
                                         style="width: 92px;height: 24px">
                                </div>
                                <div :style="{color: `${data.tabType == 'filling' ? '#409EFF':'#666666'}`}">填充标签</div>
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item label="标签颜色">
                        <el-color-picker @change="(row) => {row == null ? data.tabColor = '#FFFFFF' : ''}"
                                         size="small" v-model="data.tabColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.tabColor"></el-input>
                    </el-form-item>
                    <el-form-item label="未选中文字颜色">
                        <el-color-picker @change="(row) => {row == null ? data.textColor = '#FFFFFF' : ''}"
                                         size="small" v-model="data.textColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.textColor"></el-input>
                    </el-form-item>
                    <el-form-item label="背景颜色">
                        <el-color-picker @change="(row) => {row == null ? data.tabBackground = '#FFFFFF' : ''}"
                                         size="small" v-model="data.tabBackground"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                  v-model="data.tabBackground"></el-input>
                    </el-form-item>

                    <el-form-item label="自定义模块">
                        <draggable style="width: 355px" flex="dir:top" v-model="data.list" @end="move"
                                   ref="parentNode" :options="{filter:'.item-drag',preventOnFilter:false}">
                            <div v-for="(nav,index) in data.list" class="edit-nav-item drag-drop">
                                <div class="nav-edit-options">
                                    <el-button v-if="data.list.length > 1" @click="navItemDelete(index)"
                                               type="primary"
                                               icon="el-icon-delete"
                                               style="top: -6px;right: -31px;"
                                    ></el-button>
                                </div>
                                <div flex="dir:left box:first cross:center">
                                    <div style="flex-grow: 1;max-width: 100%">
                                        <el-input class="item-drag"
                                                  v-model="nav.tabName"
                                                  placeholder="请输入名称，最多输入4个字"
                                                  size="small"
                                                  style="margin-bottom: 5px"
                                                  maxlength="4"
                                        ></el-input>
                                        <div flex="dir:left cross:center" v-if="nav.id">
                                            <div style="line-height:32px;width:auto;padding:0 10px;color: #666666;font-size: 14px"
                                                 v-text="formatLabel(nav)" class="t-omit"></div>
                                            <el-button @click="edit(index)" style="margin-left: 9px;padding: 0"
                                                       type="text">修改
                                            </el-button>
                                        </div>
                                        <div v-else>
                                            <el-input v-model="nav.url" placeholder="点击选择模块" readonly size="small"
                                                      disabled>
                                                <div slot="append" @click="edit(index)">
                                                    <el-button size="small">选择模块</el-button>
                                                </div>
                                            </el-input>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </draggable>
                        <el-button v-if="data.list.length < maxNum" class="add-btn" type="primary" @click="addTab"
                                   size="small" plain>+添加一个模块
                        </el-button>
                        <div style="color: #666666;font-size: 12px;">最多添加{{maxNum}}个,拖拽可改变顺序</div>
                    </el-form-item>
                </template>
                <template v-else>
                    <el-form-item label="自定义模块">
                        <div v-for="(item,index) in data.list" :key="index">
                            <div flex="dir:left" style="margin-bottom: 12px">
                                <div class="box">
                                    模块： {{item.name}}
                                </div>
                                <el-button type="text" @click="edit(index)">修改</el-button>
                            </div>
                        </div>
                        <el-button class="add-btn" type="primary" @click="addTab" size="small" plain>+添加一个模块</el-button>
                    </el-form-item>
                </template>
            </el-form>

            <el-dialog title="自定义模块" :visible.sync="dialogVisible">
                <!--工具条 表单搜索-->
                <el-form size="small" :inline="true" :model="search" @submit.native.prevent>
                    <el-form-item style="margin-bottom: 20px;width: 100%">
                        <el-input @keyup.enter.native="searchModule" placeholder="根据名称搜索" style="width: 100%"
                                  v-model="search.keyword">
                            <template slot="append">
                                <el-button @click="searchModule" :loading="listLoading">搜索</el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                </el-form>

                <!--列表-->
                <el-table :data="moduleList" style="width: 100%" v-loading="listLoading">
                    <el-table-column type="index" :index="0" width="50">
                        <template slot-scope="scope">
                            <app-radio v-model="editForm.selectIndex" :label="scope.row.id"></app-radio>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="标题"></el-table-column>
                    <el-table-column prop="created_at" label="创建时间" width="250"></el-table-column>
                </el-table>

                <!--工具条 批量操作和分页-->
                <el-col :span="24" class="toolbar">
                    <el-pagination
                            background
                            layout="prev, pager, next, jumper"
                            @current-change="pageChange"
                            :page-size="pagination.pageSize"
                            :total="pagination.total_count"
                            style="text-align: center;margin:15px 0"
                            v-if="pagination">
                    </el-pagination>
                </el-col>
                <div slot="footer">
                    <el-button size="small" @click="submit" type="primary">确定</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-module', {
        template: '#diy-module',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    tabType: 'line', //filling
                    tabColor: '#ff4040',
                    textColor: '#666666',
                    tabBackground: '#FFFFFF',

                    list: [],
                },
                search: {keyword: ''},
                dialogVisible: false,
                moduleList: [],
                pagination: null,
                listLoading: false,
                maxNum: 10,
                editForm: {
                    editIndex: -1,
                    selectIndex: 0,
                },
                current: 0,
            };
        },
        created() {
            if (this.value) {
                this.data = JSON.parse(JSON.stringify(this.value));
            } else {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            }
        },
        computed: {
            scrollStyle() {
                return (index) => {
                    let length = this.data.list.length;
                    if (length < 5) {
                        return {
                            width: `${100 / length}%`
                        }
                    } else {
                        length = 5;
                        return {
                            width: `${(100 / (length * 2 - 1)) * 2}%`,
                        }
                    }
                }
            },
            tabNameFill() {
                return (index) => {

                    if (this.data.tabType === 'filling') {
                        if (this.current === index) {
                            return {
                                backgroundColor: this.data.tabColor,
                                borderRadius: '32px',
                                padding: '0 24px',
                            }
                        }
                    }
                };
            },
            tabNameStyle() {
                return (index) => {
                    let color = 'auto';
                    if (this.data.tabType === 'line') {
                        if (this.current === index) {
                            color = this.data.tabColor;
                        } else {
                            color = this.data.textColor;
                        }
                    }
                    if (this.data.tabType === 'filling') {
                        if (this.current === index) {
                            color = this.data.tabBackground;
                        } else {
                            color = this.data.textColor;
                        }
                    }
                    return {color};
                }
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
            move(e) {
                if (e.newIndex < this.current && e.oldIndex < this.current) {
                    return;
                }
                if (e.newIndex > this.current && e.oldIndex > this.current) {
                    return;
                }
                if (e.newIndex >= this.current && e.oldIndex < this.current) {
                    this.current = this.current - 1;
                    return;
                }

                if (e.newIndex <= this.current && e.oldIndex > this.current) {
                    this.current = this.current + 1;
                    return;
                }
                if (e.oldIndex == this.current) {
                    this.current = e.newIndex;
                    return;
                }
            },
            changeTab(index) {
                this.current = index;
            },
            formatLabel(column) {
                return '模块：' + column.name;
            },
            edit(index) {
                this.editForm.editIndex = index;
                if (!this.moduleList || this.moduleList.length === 0) {
                    this.getList();
                }
                this.dialogVisible = true;
            },

            addTab() {
                let length = this.data.list.length;
                if (length === 0) {
                    this.edit(-1);
                } else {
                    this.data.list.push({
                        data: [],
                        id: '',
                        name: '',
                        tabName: '',
                    })
                }
            },


            navItemDelete(index) {
                this.current = 0;
                this.data.list.splice(index, 1);
                console.log(this.data.list);
            },

            submit() {
                const list = this.moduleList;
                const index = this.editForm.selectIndex;
                let sentinel;
                list.forEach(module => {
                    if (module.id === index)
                        sentinel = module;
                });
                if (!sentinel) {
                    this.$message.error('请选择模块');
                    return;
                }
                this.data.list.splice(
                    this.editForm.editIndex,
                    this.editForm.editIndex === -1 ? 0 : 1,
                    {
                        id: sentinel['id'],
                        name: sentinel['name'],
                        tabName: this.editForm.editIndex === -1 ? '' : this.data.list[this.editForm.editIndex]['tabName'],
                        data: JSON.parse(sentinel['data']),
                    }
                );
                this.dialogVisible = false;
            },
            searchModule() {
                this.page = 1;
                this.getList();
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            getList() {
                let params = Object.assign({
                    r: 'plugin/diy/mall/module/index',
                    page: this.page
                }, this.search);
                this.listLoading = true;
                this.$request({
                    params,
                }).then(info => {
                    this.listLoading = false;
                    if (info.data.code === 0) {
                        this.moduleList = info.data.data.list;
                        this.pagination = info.data.data.pagination;
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        }
    });
</script>
