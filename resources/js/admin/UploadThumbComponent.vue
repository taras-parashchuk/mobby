<template>
    <figure @click="upload" :class="{'empty': file_path === null || !file_path.length, 'figure--big': hasBigImg()}">

        <template v-if="file_path !== null && file_path.length">
            <img style="max-height:200px;" :style="imgStyle" :src='transformedThumbPath'
                 class="figure-img">

            <input class="form-control" type="hidden" v-model="transformedFilePath">

            <div class="figure-edit">
                <icon icon="pencil-edit-button" class="icon"></icon>
            </div>

            <div class="figure-remove" @click.stop="$emit('remove')">
                <icon icon="cancel" class="icon"></icon>
            </div>
        </template>
        <template v-else>
            <icon icon="plus" class="icon"></icon>
        </template>

        <input v-if="is_tmp" id="file-input" type="file" style="display: none;" @change="setTmpImage"/>

    </figure>
</template>

<script>

    export default {
        name: "UploadThumb",
        props: [ 'isSmall','is_tmp', 'item', 'items_type', 'thumb_path', 'file_path', 'data', 'prefix', 'type', 'imgStyle', 'attributeSrcName'],
        data() {
            return {
                holder: '/core-images/placeholder.png',
                transformedThumbPath: '',
                transformedFilePath: ''
            }
        },
        created() {
            this.transformedFilePath = this.file_path;
            this.transformedThumbPath = this.thumb_path || this.holder;
        },
        mounted() {
            this.transformedFilePath = this.file_path;
            this.transformedThumbPath = this.thumb_path || this.holder;
        },
        methods: {
            upload() {

                if (this.is_tmp) {
                    $('#file-input').trigger('click');
                } else {
                    let self = this;
                    var route_prefix = self.prefix ? self.prefix : '/admin/filemanager';

                    window.open(route_prefix + '?items_type=' + this.items_type + '&item=' + this.item + '&type=' + (self.type ? self.type : 'image') + 'FileManager', 'width=900,height=600');

                    window.SetUrl = function (info) {

                        let imageInfo = info[0];

                        self.transformedFilePath = imageInfo.name;
                        self.transformedThumbPath = imageInfo.thumb_url;

                        self.data[self.attributeSrcName || 'image'] = self.transformedFilePath;
                        self.data[self.attributeThumbName || 'filemanager_thumb'] = self.transformedThumbPath;

                    };
                }

            },
            setTmpImage() {

                let file = $('#file-input')[0].files[0];

                let fileData = new FormData();

                let self = this;

                fileData.append("file", file);

                axios.post('/admin/set-tmp-file', fileData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(httpResponse => {
                    self.transformedFilePath = file.name;
                    self.transformedThumbPath = httpResponse.data;

                    self.data[self.attributeSrcName || 'image'] = self.transformedFilePath;
                    self.data[self.attributeThumbName || 'filemanager_thumb'] = self.transformedThumbPath;
                });
            },
            hasBigImg() {
                let res = false;

                if(this.isSmall === undefined || this.isSmall === false){
                    switch (this.$route.name) {
                        case 'product':
                        case 'information':
                        case 'article':
                        case 'settings':
                            res = true;
                            break;
                    }
                }

                return res;

            }
        },
        watch: {
            thumb_path(newValue) {
                this.transformedThumbPath = newValue || this.holder;
            }
        }
    }

</script>

<style lang="scss">
    .figure-edit {
        display: none;
    }

    .figure-remove {
        display: none;
    }

    figure {
        margin-bottom: 0;
        padding: 2px;
        box-sizing: border-box;
        border-radius: 10px;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        width: 58px;
        height: 58px;
        border: 1px solid #6f6adf;
        overflow: hidden;

        &:hover {
            .figure-edit {
                position: absolute;
                display: flex;
                align-items: center;
                justify-content: center;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(52, 57, 90, 0.871);
                border-radius: 9px;

                .icon {
                    color: #fff;
                    border-radius: 5px;
                    border: 1px dashed #fff;
                    padding: 6px 8px;
                    width: 12px;
                    height: 12px;
                }
            }

            .figure-remove {
                display: block;
                position: absolute;
                right: 3px;
                top: -3px;

                .icon {
                    color: #fff;

                    .svg {
                        width: 5px;
                        height: 5px;
                    }
                }
            }
        }

        &.empty {
            background-color: #34395a;
            .icon{
                color: #6f6adf;
                border-radius: 5px;
                border: 1px dashed #6f6adf;
                padding: 6px 8px;
                box-sizing: border-box;
                .svg{
                    width: 11px;
                    height: 11px;
                }
            }
        }

        &.figure--big {
            padding: 10px;
            width: 110px;
            height: 110px;
            border: none;
            border-radius: 20px;

            &:hover {
                .figure-edit {
                    border-radius: 19px;

                    .icon {
                        border-radius: 5px;
                        padding: 12px 16px;
                    }
                }

                .figure-remove {
                    right: 10px;
                    top: 2px;

                    .icon {
                        .svg {
                            width: 9px;
                            height: 9px;
                        }
                    }
                }
            }

            &.empty {
                .icon{
                    padding: 12px 16px;
                    box-sizing: border-box;
                }
            }
        }

    }

    img {
        width: 100%;
        height: auto;
    }

    .figure-img {
        //margin-bottom: 0;
        //border-radius: 10px;
        //border: 1px rgb(111, 106, 223) solid;
        box-sizing: border-box;
    }

</style>