<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/28 18:06
 */
?>
<template id="app-upload">
    <div class="app-upload" @click="handleClick">
        <slot></slot>
        <input ref="input" type="file" :accept="accept" :multiple="multiple" style="display: none"
               @change="handleChange">
    </div>
</template>
<script>
    Vue.component('app-upload', {
        template: '#app-upload',
        props: {
            disabled: Boolean,
            multiple: Boolean,
            max: Number,
            accept: String,
            params: Object,
            fields: Object,
        },
        data() {
            return {
                dialogVisible: false,
                loading: true,
                attachments: [],
                checkedAttachments: [],
                files: [],
            };
        },
        created() {
        },
        methods: {
            handleClick() {
                if (this.disabled) {
                    return;
                }
                this.$refs.input.value = null;
                this.$refs.input.click();
            },
            handleChange(e) {
                if (!e.target.files) return;
                this.uploadFiles(e.target.files);
            },
            uploadFiles(rawFiles) {
                if (this.max && rawFiles.length > this.max) {
                    this.$message.error('最多一次只能上传' + this.max + '个文件。')
                    return;
                }
                this.files = [];
                for (let i = 0; i < rawFiles.length; i++) {
                    const file = {
                        _complete: false,
                        response: null,
                        rawFile: rawFiles[i],
                    };
                    this.files.push(file);
                }
                this.$emit('start', this.files);
                for (let i in this.files) {
                    this.upload(this.files[i]);
                }
            },
            upload(file) {
                let formData = new FormData();
                const params = {};
                params['r'] = 'common/attachment/upload';
                for (let i in this.params) {
                    params[i] = this.params[i];
                }
                for (let i in this.fields) {
                    formData.append(i, this.fields[i]);
                }
                formData.append('file', file.rawFile, file.rawFile.name);
                this.$request({
                    headers: {'Content-Type': 'multipart/form-data'},
                    params: params,
                    method: 'post',
                    data: formData,
                }).then(e => {
                    if (e.data.code === 1) {
                        this.$message.error(e.data.msg);
                    }
                    file.response = e;
                    file._complete = true;
                    this.onSuccess(file);
                }).catch(e => {
                    file._complete = true;
                });
            },
            onSuccess(file) {
                this.$emit('success', file);
                let allComplete = true;
                for (let i in this.files) {
                    if (!this.files[i]._complete) {
                        allComplete = false;
                        break;
                    }
                }
                if (allComplete) {
                    this.$emit('complete', this.files);
                }
            },
        },
    });
</script>