<template>
    <div>
        <template v-if="product.variants.length">
            <div class="flex flex--align-center additionalInfo">
                {{$root.translateWords('Double click on the row of table for select primary variant')}}
                <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
            </div>

            <div class="listData listData--noBg">

                <vue-good-table
                        :columns="variantColumns"
                        :rows="product.variants"
                        styleClass="table"
                        :row-style-class="getValueRowClass"
                        @on-row-dblclick="params => product.primary_variant_id = params.row.id">

                    <template slot="table-row" slot-scope="props">

                        <template v-if="props.column.field === 'sku'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--price"
                                       v-model="product.variants[props.index].sku">
                            </div>
                            <div class="mt--12">
                                {{paramText(product.variants[props.index])}}
                            </div>
                        </template>

                        <template v-else-if="props.column.field === 'image'">

                            <upload-thumb
                                    items_type="products"
                                    :is-small="true"
                                    :item="$root.encodeId('products', product.id)"
                                    :data="product.variants[props.index].images[0]"
                                    :file_path="product.variants[props.index].images[0].src"
                                    :thumb_path="product.variants[props.index].images[0].filemanager_thumb"
                                    attribute-src-name="src"
                                    img-style="max-width: 57px; border-radius: 5px;"
                            ></upload-thumb>

                        </template>
                        <template v-else-if="props.column.field === 'name'">
                            <div v-for="translate in product.variants[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.name">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'vendor_price'">
                            <div class="flex flex--align-center">
                                <input type="number" class="input input--price input--label_right"
                                       v-model.number="product.variants[props.index].vendor_price">
                                {{product.currency_code}}
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'quantity'">

                            <div class="flex flex--align-center">
                                <input type="number" class="input input--qty"
                                       v-model.number="product.variants[props.index].quantity">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="product.variants[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <template v-if="typeof product.variants[props.index].id === 'number'">
                                    <a class="table__action" :href="product.variants[props.index].href" target="_blank">
                                        <icon icon="foreign" class="icon"></icon>
                                    </a>
                                    <a href="javascript:void(0)" class="table__action"
                                       @click.stop="editVariant(product.variants[props.index])">
                                        <icon icon="pencil-edit-button" class="icon"></icon>
                                    </a>
                                </template>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="removeVariant(props.index)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                    </template>

                </vue-good-table>

            </div>

        </template>

        <modal name="variants-configuration" width="70%" :use-backdrop-close="false" :has-close-icon="false"
               :title="$root.translateWords('Select parameters for variants')">
            <template v-slot>
                <div class="grid grid--2 grid--gap_40"
                     v-for="to_attribute in product.to_attributes.filter(to_attribute => to_attribute.has_variant === true)">
                    <div>
                        <div class="singleFormGroup mb--50">
                            <div class="singleFormGroup__title">
                                {{$root.translate.columns.attributes}}:
                                <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                            </div>
                        </div>
                        <div class="singleFormGroup">
                            <div class="singleFormGroup__field">
                                <div class="flex flex--align-center mb--10">
                                    <v-select
                                            :clear-search-on-select="true"
                                            taggable
                                            :clearable="true"
                                            :searchable="true"
                                            :options="getUnusedAttributes"
                                            v-model="to_attribute.attribute"
                                            class="input input--inForm"
                                            @input="clearAttributeSearchPhrase(to_attribute.id)"
                                            label="name">
                                        <template #search="{attributes, events}">
                                            <input
                                                    @input="setAttributeSearchPhrase(to_attribute.id)"
                                                    class="vs__search"
                                                    v-bind="attributes"
                                                    v-on="events"
                                            />
                                        </template>
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                    <a v-if="!checkAttributeExist(to_attribute.id)"
                                       v-tooltip.top-start="'You have new messages.'"
                                       class="singleFormGroup__action" href="javascript:void(0)"
                                       @click="createAttribute(to_attribute.id)">
                                        <icon icon="floppy-disk" class="icon"></icon>
                                    </a>
                                    <a v-tooltip.top-start="'You have new messages.'"
                                       class="singleFormGroup__action" href="javascript:void(0)"
                                       @click="removeToAttribute(to_attribute.id)">
                                        <icon icon="delete" class="icon"></icon>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="singleFormGroup mb--50">
                            <div class="singleFormGroup__title">
                                {{$root.translate.columns.values}}:
                                <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                            </div>
                        </div>
                        <div class="singleFormGroup">
                            <div class="singleFormGroup__field">
                                <div class="flex flex--align-center">
                                    <v-select v-if="to_attribute.attribute !== null"
                                              multiple
                                              :clear-search-on-select="true"
                                              :clearable="true"
                                              :searchable="true"
                                              :options="attribute_values.filter(attribute_value => attribute_value.attribute_id === to_attribute.attribute.id && to_attribute.values.indexOf(attribute_value) === -1)"
                                              @input="clearAttributeValueSearchPhrase(to_attribute.id)"
                                              v-model="to_attribute.values"
                                              class="input input--inForm vs--multiply pt--0"
                                              :components="{Deselect, OpenIndicator}"
                                              :ref="'to_attributes_v'+to_attribute.id"
                                              label="value">
                                        <template #search="{attributes, events}">
                                            <input
                                                    :placeholder="$root.translateWords('Create value')"
                                                    @input="setAttributeValueSearchPhrase(to_attribute.id)"
                                                    class="vs__search"
                                                    v-bind="attributes"
                                                    v-on="events"
                                            />
                                        </template>
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                    <a v-if="!checkAttributeValueExist(to_attribute.id)"
                                       v-tooltip.top-start="'You have new messages.'"
                                       class="singleFormGroup__action" href="javascript:void(0)"
                                       @click="createAttributeValue(to_attribute.id, 'to_attributes_v'+to_attribute.id)">
                                        <icon icon="floppy-disk" class="icon"></icon>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex mt--48">
                    <a class="btn btn--cancel modal__btn" href="javascript:void(0)"
                       @click="addRowToAttributes(true)">{{$root.translateWords('Add parameters')}}</a>
                    <a class="btn btn--confirm modal__btn" href="javascript:void(0)"
                       v-if="hasSelectedAttributesWithValuesInConfiguration"
                       @click.stop="$root.changePopupShowStatus('variants-configuration', false); $root.changePopupShowStatus('variants-filling', true)">
                        {{$root.translateWords('Make variants')}}
                    </a>
                </div>
            </template>
        </modal>

        <modal name="variants-filling" width="80%"
               :use-backdrop-close="false" :has-close-icon="false"
               :title="$root.translateWords('Fill by starting information for making new variants')">
            <template v-slot>
                <div class="grid grid--3">
                    <div class="variantFillingParams">
                        <div class="singleFormGroup">
                            <div class="singleFormGroup__title">
                                {{$root.translate.columns.price}}:
                            </div>
                            <div class="singleFormGroup__field">
                                <div class="flex flex--align-center mb--16">
                                    <div class="switcher">
                                        <check @click.native="fillingConfiguration.price.custom = false"
                                               :checked="fillingConfiguration.price.custom === false"
                                               class="switcher__icon"></check>
                                        <span @click="fillingConfiguration.price.custom = false"
                                              class="switcher__label">{{$root.translateWords('Fill after')}}</span>
                                    </div>
                                </div>
                                <div class="flex flex--align-center mb--16">
                                    <div class="switcher">
                                        <check @click.native="fillingConfiguration.price.custom = true"
                                               :checked="fillingConfiguration.price.custom === true"
                                               class="switcher__icon"></check>
                                        <span @click="fillingConfiguration.price.custom = true"
                                              class="switcher__label">{{$root.translateWords('Fill same price for all variants')}}</span>
                                    </div>
                                </div>
                                <div class="flex flex--align-center" v-if="fillingConfiguration.price.custom === true">
                                    <input type="text" class="input input--price_inProduct input--label_right"
                                           v-model.number="fillingConfiguration.price.value">
                                    {{product.currency_code}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="variantFillingParams">
                        <div class="singleFormGroup">
                            <div class="singleFormGroup__title">
                                {{$root.translateWords('Stock quantity')}}:
                                <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                            </div>
                            <div class="singleFormGroup__field">
                                <div class="flex flex--align-center mb--16">
                                    <div class="switcher">
                                        <check @click.native="fillingConfiguration.quantity.custom = false"
                                               :checked="fillingConfiguration.quantity.custom === false"
                                               class="switcher__icon"></check>
                                        <span @click="fillingConfiguration.quantity.custom = false"
                                              class="switcher__label">{{$root.translateWords('Fill after')}}</span>
                                    </div>
                                </div>
                                <div class="flex flex--align-center mb--16">
                                    <div class="switcher">
                                        <check @click.native="fillingConfiguration.quantity.custom = true"
                                               :checked="fillingConfiguration.quantity.custom === true"
                                               class="switcher__icon"></check>
                                        <span @click="fillingConfiguration.quantity.custom = true"
                                              class="switcher__label">{{$root.translateWords('Fill same stock quantity for all variants')}}</span>
                                    </div>
                                </div>
                                <div class="flex flex--align-center"
                                     v-if="fillingConfiguration.quantity.custom === true">
                                    <input type="text" class="input input--price_inProduct"
                                           v-model.number="fillingConfiguration.quantity.value">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="variantFillingParams imagesSet">
                        <div class="imagesSet__title">
                            {{$root.translateWords('Product images')}}:
                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                        </div>
                        <div class="flex flex--align-center mb--16">
                            <div class="switcher">
                                <check @click.native="fillingConfiguration.image.custom = false"
                                       :checked="fillingConfiguration.image.custom === false"
                                       class="switcher__icon"></check>
                                <span @click="fillingConfiguration.image.custom = false"
                                      class="switcher__label">{{$root.translateWords('Fill after')}}</span>
                            </div>
                        </div>
                        <div class="flex flex--align-center mb--16">
                            <div class="switcher">
                                <check @click.native="fillingConfiguration.image.custom = true"
                                       :checked="fillingConfiguration.image.custom === true"
                                       class="switcher__icon"></check>
                                <span @click="fillingConfiguration.image.custom = true"
                                      class="switcher__label">{{$root.translateWords('Fill same image for all variants')}}</span>
                            </div>
                        </div>
                        <div class="imagesSet__items" v-if="fillingConfiguration.image.custom">
                            <upload-thumb
                                    items_type="products"
                                    :item="$root.encodeId('products', product.id)"
                                    :data="fillingConfiguration.image"
                                    attribute-src-name="src"
                                    :file_path="fillingConfiguration.image.src"
                                    :thumb_path="fillingConfiguration.image.filemanager_thumb"
                                    @remove="fillingConfiguration.image.image = ''"
                            ></upload-thumb>
                        </div>
                    </div>
                </div>
                <div class="flex flex--justify-end mt--48">
                    <a class="btn btn--confirm modal__btn" href="javascript:void(0)" @click="generateData()">
                        {{$root.translateWords('Make variants')}}
                    </a>
                </div>
            </template>
        </modal>

        <modal v-if="Object.keys(modalVariant).length" name="edit-variant-modal" width="80%" @closed="closeEditModal">
            <template v-slot>
                <div class="">
                    <div class="tabs">
                        <a @click="tab = 'main'" href="javascript:void(0)" class="tabs__heading"
                           :class="{'tabs__heading--active': tab === 'main'}">
                            {{$root.translate.columns.main}}
                        </a>
                        <a @click="tab = 'specials'" href="javascript:void(0)" class="tabs__heading"
                           :class="{'tabs__heading--active': tab === 'specials'}">
                            {{$root.translate.columns.specials}}
                        </a>
                        <a @click="tab = 'discounts'" href="javascript:void(0)" class="tabs__heading"
                           :class="{'tabs__heading--active': tab === 'discounts'}">
                            {{$root.translate.columns.discounts}}
                        </a>
                        <a @click="tab = 'seo'" href="javascript:void(0)" class="tabs__heading"
                           :class="{'tabs__heading--active': tab === 'seo'}">
                            Seo
                        </a>
                    </div>
                    <div class="singleForm__content">

                        <template v-if="tab === 'main'">
                            <div class="flex flex--justify-space-between">
                                <div>
                                    <div class="singleFormGroup">
                                        <div class="singleFormGroup__title">
                                            {{$root.translate.columns.name}}:
                                        </div>
                                        <div class="singleFormGroup__field inputWithTranslates"
                                             v-for="translate in modalVariant.translates">
                                            <div class="flex flex--align-center">
                                                {{$root.languages.find((language) => {return language.locale ===
                                                translate.locale}).name}}:
                                                <input type="text" class="input input--inForm input--label_left"
                                                       v-model="translate.name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="singleFormGroup">
                                        <div class="singleFormGroup__title">
                                            {{$root.translate.columns.sku}}:
                                        </div>
                                        <div class="singleFormGroup__field">
                                            <div class="flex flex--align-center">
                                                <input type="text" class="input input--inForm"
                                                       v-model="modalVariant.sku">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="singleFormGroup">
                                        <div class="singleFormGroup__title">
                                            {{$root.translate.columns.price}}:
                                        </div>
                                        <div class="singleFormGroup__field">
                                            <div class="flex flex--align-center">
                                                <input type="text" class="input input--price_inProduct"
                                                       v-model.number="modalVariant.vendor_price">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="singleFormGroup">
                                        <div class="singleFormGroup__title">
                                            {{$root.translateWords('Stock quantity')}}:
                                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                        </div>
                                        <div class="singleFormGroup__field">
                                            <div class="flex flex--align-center">
                                                <input type="number" class="input input--inForm"
                                                       v-model.number="modalVariant.quantity">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="singleFormGroup">
                                        <div class="singleFormGroup__title">
                                            {{$root.translate.columns.description}}:
                                        </div>
                                        <div class="singleFormGroup__field inputWithTranslates"
                                             v-for="translate in modalVariant.translates">
                                            <div class="flex">
                                <span class="mr--8">
                                    {{$root.languages.find((language) => {return language.locale == translate.locale}).name}}:
                                </span>
                                                <vue-ckeditor :config="$root.editorConfig"
                                                              v-model="translate.description"></vue-ckeditor>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="singleFormGroup">
                                        <div class="singleFormGroup__title">
                                            {{$root.translate.columns.slug}}:
                                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                        </div>
                                        <div class="singleFormGroup__field">
                                            <div class="flex flex--align-center">
                                                <input type="text" class="input input--inForm"
                                                       v-model="modalVariant.slug">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="imagesSet">
                                        <div class="imagesSet__title">
                                            {{$root.translateWords('Product images')}}:
                                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                        </div>
                                        <draggable
                                                v-model="product.images"
                                                v-bind="dragOptions"
                                                @start="drag = true"
                                                @end="drag = false"
                                                tag="transition-group"
                                                class="imagesSet__items"
                                                :componentData="{props: {
                                            type: 'transition',
                                            name: !drag ? 'flip-list' : null
                                        }}">

                                            <div class=""
                                                 v-for="(element, number) in modalVariant.images.filter(image => image.src.length)"
                                                 :key="number">
                                                <upload-thumb
                                                        v-if="element.src.length"
                                                        items_type="products"
                                                        :item="$root.encodeId('products',product.id)"
                                                        :ref="'additionalThumb'"
                                                        :data="element"
                                                        attribute-src-name="src"
                                                        :file_path="element.src"
                                                        :thumb_path="element.filemanager_thumb"
                                                        @remove="number === 0 ? modalVariant.images[0].src = '' : modalVariant.images.splice(number, 1)"
                                                ></upload-thumb>
                                            </div>

                                            <div v-if="!modalVariant.images.length || !modalVariant.images[0].src.length || modalVariant.images.filter(image => image.src.length).length"
                                                 @click="openUploadWindow"
                                                 class="imagesSet__new"
                                                 slot="footer"
                                                 key="footer">
                                                <icon icon="plus" class="icon"></icon>
                                            </div>

                                        </draggable>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-else-if="tab === 'specials'">
                            <div class="listData listData--noBg" v-if="modalVariant.specials.length">
                                <vue-good-table
                                        :columns="columnsForSpecials"
                                        :rows="modalVariant.specials"
                                        styleClass="table"
                                        row-style-class="table__row">
                                    <template slot="table-row" slot-scope="props">
                                        <template v-if="props.column.field === 'user_group_id'">
                                            <v-select
                                                    :clearable="false"
                                                    :searchable="true"
                                                    :options="userGroups"
                                                    v-model="modalVariant.specials[props.index].user_group_id"
                                                    class="input input--inForm"
                                                    :reduce="userGroup => userGroup.id"
                                                    label="name">
                                            </v-select>
                                        </template>
                                        <template v-else-if="props.column.field === 'price'">
                                            <div class="flex flex--align-center">
                                                <input type="number" class="input input--price"
                                                       v-model.number="modalVariant.specials[props.index].price">
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'date_start'">
                                            <div class="flex flex--align-center">
                                                <datetime input-class="input input--date"
                                                          v-model="modalVariant.specials[props.index].date_start_js"
                                                          type="datetime"
                                                          :phrases="datetime.phrases"
                                                          format="dd.MM.yyyy HH:mm"/>
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'date_end'">
                                            <div class="flex flex--align-center">
                                                <datetime input-class="input input--date"
                                                          v-model="modalVariant.specials[props.index].date_end_js"
                                                          type="datetime"
                                                          :phrases="datetime.phrases"
                                                          format="dd.MM.yyyy HH:mm"/>
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'action'">
                                            <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                               href="javascript:void(0)"
                                               @click.stop="modalVariant.specials.splice(props.index, 1)">
                                                <icon icon="delete" class="icon"></icon>
                                            </a>
                                        </template>
                                    </template>
                                </vue-good-table>
                            </div>
                        </template>

                        <template v-else-if="tab === 'discounts'">
                            <div class="listData listData--noBg" v-if="modalVariant.discounts.length">
                                <vue-good-table
                                        :columns="columnsForDiscounts"
                                        :rows="modalVariant.discounts"
                                        styleClass="table"
                                        row-style-class="table__row">
                                    <template slot="table-row" slot-scope="props">
                                        <template v-if="props.column.field === 'user_group_id'">
                                            <v-select
                                                    :clearable="false"
                                                    :searchable="true"
                                                    :options="userGroups"
                                                    v-model="modalVariant.discounts[props.index].user_group_id"
                                                    class="input input--inForm"
                                                    :reduce="userGroup => userGroup.id"
                                                    label="name">
                                            </v-select>
                                        </template>
                                        <template v-else-if="props.column.field === 'price'">
                                            <div class="flex flex--align-center">
                                                <input type="number" class="input input--price"
                                                       v-model.number="modalVariant.discounts[props.index].price">
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'quantity'">
                                            <div class="flex flex--align-center">
                                                <input type="number" class="input input--qty"
                                                       v-model.number="modalVariant.discounts[props.index].quantity">
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'date_start'">
                                            <div class="flex flex--align-center">
                                                <datetime input-class="input input--date"
                                                          v-model="modalVariant.discounts[props.index].date_start_js"
                                                          type="datetime"
                                                          :phrases="datetime.phrases"
                                                          format="dd.MM.yyyy HH:mm"/>
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'date_end'">
                                            <div class="flex flex--align-center">
                                                <datetime input-class="input input--date"
                                                          v-model="modalVariant.discounts[props.index].date_end_js"
                                                          type="datetime"
                                                          :phrases="datetime.phrases"
                                                          format="dd.MM.yyyy HH:mm"/>
                                            </div>
                                        </template>
                                        <template v-else-if="props.column.field === 'action'">
                                            <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                               href="javascript:void(0)"
                                               @click.stop="modalVariant.discounts.splice(props.index, 1)">
                                                <icon icon="delete" class="icon"></icon>
                                            </a>
                                        </template>
                                    </template>
                                </vue-good-table>
                            </div>
                        </template>

                        <template v-else-if="tab === 'seo'">
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.title}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in modalVariant.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.meta_h1">
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns['meta-title']}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in modalVariant.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.meta_title">
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns['meta-description']}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in modalVariant.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.meta_description">
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns['meta-keywords']}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in modalVariant.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.meta_keywords">
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>
            </template>
        </modal>

        <modal @closed="clearUnusedNewToAttributes" name="add-variant" width="70%"
               :title="$root.translateWords('Select parameters for variants')">
            <template v-slot>
                <div class="grid grid--2 grid--gap_40"
                     v-for="(newValueItem, newValueItemIndex) in updateConfiguration">
                    <template v-if="hasProductsWithAttribute(newValueItem.to_attribute)">
                        <div>
                            <div class="singleFormGroup mb--50">
                                <div class="singleFormGroup__title">
                                    {{newValueItem.to_attribute.attribute.name}}:
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center mb--10">
                                        <v-select
                                                :clear-search-on-select="true"
                                                taggable
                                                :clearable="false"
                                                :searchable="true"
                                                :options="attribute_values.filter(attribute_value => {
                                                        return newValueItem.to_attribute.values.findIndex(value => value.id === attribute_value.id) === -1 && attribute_value.attribute_id === newValueItem.to_attribute.attribute.id
                                                    })"
                                                v-model="newValueItem.value"
                                                class="input input--inForm"
                                                :ref="'to_attributes_v'+newValueItem.to_attribute.id"
                                                label="value">
                                            <template #search="{attributes, events}">
                                                <input
                                                        @input="setAttributeValueSearchPhrase(newValueItem.to_attribute.id)"
                                                        class="vs__search"
                                                        v-bind="attributes"
                                                        v-on="events"
                                                />
                                            </template>
                                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                        </v-select>
                                        <a v-if="!checkAttributeValueExist(newValueItem.to_attribute.id)"
                                           v-tooltip.top-start="'You have new messages.'"
                                           class="singleFormGroup__action" href="javascript:void(0)"
                                           @click="createAttributeValueForNewConfiguration(newValueItem.to_attribute.id, 'to_attributes_v'+newValueItem.to_attribute.id, newValueItemIndex)">
                                            <icon icon="floppy-disk" class="icon"></icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="grid grid--2 grid--gap_40"
                     v-for="to_attribute in product.to_attributes.filter(to_attribute => to_attribute.has_variant === true)">
                    <template v-if="!hasProductsWithAttribute(to_attribute)">
                        <div>
                            <div class="singleFormGroup mb--50">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.attributes}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center mb--10">
                                        <v-select
                                                :clear-search-on-select="true"
                                                taggable
                                                :clearable="true"
                                                :searchable="true"
                                                :options="attributes.filter(attribute => {
                                                        return product.to_attributes.findIndex(to_attr => to_attr.has_variant && to_attr.attribute !== null && to_attr.attribute.id === attribute.id) === -1
                                                    })"
                                                v-model="to_attribute.attribute"
                                                class="input input--inForm"
                                                @input="clearAttributeSearchPhrase(to_attribute.id)"
                                                label="name">

                                            <template #search="{attributes, events}">
                                                <input
                                                        @input="setAttributeSearchPhrase(to_attribute.id)"
                                                        class="vs__search"
                                                        v-bind="attributes"
                                                        v-on="events"
                                                />

                                            </template>

                                            <template slot="selected-option" slot-scope="option">
                                                {{option.name}}
                                            </template>

                                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>


                                        </v-select>
                                        <a v-if="!checkAttributeExist(to_attribute.id)"
                                           v-tooltip.top-start="'You have new messages.'"
                                           class="singleFormGroup__action" href="javascript:void(0)"
                                           @click.stop="createAttribute(to_attribute.id)">
                                            <icon icon="floppy-disk" class="icon"></icon>
                                        </a>
                                        <a v-tooltip.top-start="'You have new messages.'"
                                           class="singleFormGroup__action" href="javascript:void(0)"
                                           @click.stop="removeToAttribute(to_attribute.id)">
                                            <icon icon="delete" class="icon"></icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="singleFormGroup mb--50">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.values}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <template v-if="to_attribute.attribute !== null && to_attribute.attribute.id">
                                            <v-select v-if="to_attribute.attribute !== null"
                                                      :clear-search-on-select="true"
                                                      multiple
                                                      :clearable="true"
                                                      :searchable="true"
                                                      :options="attribute_values.filter(attribute_value => attribute_value.attribute_id === to_attribute.attribute.id && to_attribute.values.indexOf(attribute_value) === -1)"
                                                      @input="clearAttributeValueSearchPhrase(to_attribute.id)"
                                                      v-model="to_attribute.values"
                                                      class="input input--inForm vs--multiply pt--0"
                                                      :components="{Deselect, OpenIndicator}"
                                                      :ref="'to_attributes_v'+to_attribute.id"
                                                      label="value">
                                                <template #search="{attributes, events}">
                                                    <input
                                                            :placeholder="$root.translateWords('Create value')"
                                                            @input="setAttributeValueSearchPhrase(to_attribute.id)"
                                                            class="vs__search"
                                                            v-bind="attributes"
                                                            v-on="events"
                                                    />
                                                </template>
                                                <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                            </v-select>
                                            <template
                                                    v-if="to_attribute.values.length || (Object(to_attribute).hasOwnProperty('attributeValueSearchPhrase') && to_attribute.attributeValueSearchPhrase.length)">
                                                <a v-if="!checkAttributeValueExist(to_attribute.id)"
                                                   v-tooltip.top-start="'You have new messages.'"
                                                   class="singleFormGroup__action" href="javascript:void(0)"
                                                   @click.stop="createAttributeValue(to_attribute.id, 'to_attributes_v'+to_attribute.id)">
                                                    <icon icon="floppy-disk" class="icon"></icon>
                                                </a>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex mt--48">
                    <a class="btn btn--cancel modal__btn" href="javascript:void(0)"
                       @click="addRowToAttributes(true)">{{$root.translateWords('Add parameters')}}</a>
                    <a class="btn btn--confirm modal__btn" href="javascript:void(0)" v-if="hasDifferentAttributeValue"
                       @click="createNewVariants()">
                        {{$root.translateWords('Add to list')}}
                    </a>
                </div>
            </template>
        </modal>

        <widget-actions
                v-if="product.variants.length || Object.keys(modalVariant).length"
                :additional-styles="['add', 'other']"
                :popups="['addVariant']"
                :trans="{add: tabAddTrans}"
                :add="getActionForTab"
                :others="[($root.popups['add-variant'] || Object.keys(modalVariant).length) ? null : {method: 'addVariant', actionName: $root.translateWords('Make variants')}]"
                :store="isChangedInfo && !$root.popups['add-variant'] ? 'updateProduct': false"
                remove="deleteProduct"
                :foreign="product.href"></widget-actions>

    </div>
</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;

    import draggable from 'vuedraggable'

    import VueCkeditor from 'vue-ckeditor2';

    export default {
        name: "ProductsVariationsComponent",
        props: [
            'product',
            'userGroups',
            'currencies',
            'attributes',
            'attribute_values',
            'addRowToAttributes',
            'checkAttributeExist',
            'createAttribute',
            'createAttributeValue',
            'setAttributeSearchPhrase',
            'setAttributeValueSearchPhrase',
            'checkAttributeValueExist',
            'removeToAttribute',
            'updateProductOriginal',
            'dragOptions',
            'drag',
            'isChangedInfo',
            'updateProduct',
            'deleteProduct',
            'columnsForSpecials',
            'columnsForDiscounts',
            'clearAttributeSearchPhrase',
            'clearAttributeValueSearchPhrase'
        ],
        components: {
            UploadThumb,
            draggable,
            VueCkeditor
        },
        data() {
            return {
                refreshing: false,
                OpenIndicator: {
                    render: createElement => createElement('span', ''),
                },
                Deselect: {
                    render: createElement => createElement('icon', {
                        class: 'icon',
                        props: {
                            icon: 'error'
                        }
                    }),
                },
                datetime: {
                    phrases: {ok: this.$root.translateWords('Apply'), cancel: this.$root.translateWords('Reset')},
                },
                price: null,
                quantity: null,
                imageInfo: {
                    image: '',
                    filemanager_thumb: ''
                },
                modalVariant: {},
                single: {},
                variantColumns: [
                    {
                        label: this.$root.translate.columns.sku,
                        field: 'sku',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.image_short,
                        field: 'image',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.price,
                        field: 'vendor_price',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.quantity,
                        field: 'quantity',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.actions,
                        field: 'actions',
                        thClass: 'table__heading table__heading--text_right',
                        tdClass: 'table__value table__value--text_right',
                        sortable: false,
                    },
                ],
                tab: 'main',
                fillingConfiguration: {
                    price: {
                        value: 0,
                        custom: false
                    },
                    quantity: {
                        value: 0,
                        custom: false,
                    },
                    image: {
                        src: '',
                        filemanager_thumb: '',
                        custom: false
                    }
                },
                updateConfiguration: []
            }
        },
        created() {

            this.price = this.product.vendor_price;
            this.quantity = this.product.quantity;

            if (this.product.images.length && this.product.images[0].src.length) {
                this.imageInfo = {
                    image: this.product.images[0].src,
                    filemanager_thumb: this.product.images[0].filemanager_thumb
                };
            }
        },
        computed: {
            languageLinks() {
                let languages = [];

                for (let language of this.$root.languages) {
                    languages.push({
                        text: language.name,
                        locale: language.locale
                    });
                }

                return languages;
            },
            paramText() {
                return variant => {

                    let names = [];

                    variant.variant_attribute_values.forEach(function (value) {
                        names.push(value.value);
                    });

                    return names.join(' & ');
                }
            },
            getActionForTab() {
                let action = false;

                switch (this.tab) {
                    case 'specials':
                        action = 'addVariantSpecial';
                        break;
                    case 'discounts':
                        action = 'addVariantDiscount';
                        break;
                }

                return action;
            },
            tabAddTrans() {
                switch (this.tab) {
                    case 'specials':
                        return this.$root.translateWords('Create special');
                    case 'discounts':
                        return this.$root.translateWords('Create wholesale price');
                }
            },
            to_attributes_with_variants() {
                return this.product.to_attributes.filter(to_attribute => {
                    return to_attribute.has_variant === true;
                });
            },
            hasSelectedAttributesWithValuesInConfiguration() {
                return this.product.to_attributes.filter(to_attribute => {
                    return to_attribute.has_variant === true && to_attribute.values.length;
                }).length === this.product.to_attributes.filter(to_attribute => to_attribute.has_variant).length;
            },
            hasDifferentAttributeValue() {

                let hasDifferent = true;

                this.updateConfiguration.forEach(item => {
                    if (item.value === null || !item.value.id) hasDifferent = false;
                });

                this.to_attributes_with_variants.forEach(to_attr => {
                    if (to_attr === null || !to_attr.values.length) {
                        hasDifferent = false;
                    }
                });

                return hasDifferent;
            },
            getUnusedAttributes() {

                return this.attributes.filter(attribute => {
                    let usedAttributes = this.product.to_attributes.filter(to_attr => to_attr.has_variant);

                    return usedAttributes.map(p_to_attribute => p_to_attribute.attribute).indexOf(attribute) === -1
                });
            }
        },
        methods: {
            createNewVariants() {

                this.createSingleVariant();

                this.updateVariantsByNewParams();

                this.$root.changePopupShowStatus('add-variant', false);


            },
            createSingleVariant() {
                let newParamsWithExistsAttributes = [];

                this.updateConfiguration.forEach((item, i) => {
                    this.to_attributes_with_variants[i].values.push(item.value);
                });

                let attribute_value_ids = [];

                this.product.variants.forEach(variant => {
                    variant.variant_attribute_values.forEach(variant_attribute_value => attribute_value_ids.push(variant_attribute_value.id));
                });

                this.product.to_attributes.filter(to_attribute => to_attribute.has_variant).forEach(to_attribute => {
                    if (this.hasProductsWithAttribute(to_attribute)) {

                        let newParam = to_attribute.values.find(value => attribute_value_ids.indexOf(value.id) === -1);

                        if (newParam) newParamsWithExistsAttributes.push(newParam);
                    }
                });

                if (newParamsWithExistsAttributes.length) {

                    let names = [];

                    newParamsWithExistsAttributes.forEach(attr_value => names.push(attr_value.value));

                    this.product.variants.push({
                        id: 'tmp-' + Math.random(100000, 10000000),
                        param_text: names.join(' & '),
                        variant_attribute_values: newParamsWithExistsAttributes,
                        vendor_price: 0,
                        quantity: 0,
                        translates: this.$root.copy(this.product.translates),
                        sku: '',
                        discounts: [],
                        specials: [],
                        images: [
                            {
                                src: '',
                                filemanager_thumb: '',
                            }
                        ],
                    });

                }
            },

            updateVariantsByNewParams() {

                let params = [];

                this.product.to_attributes.forEach(to_attribute => {
                    if (to_attribute.has_variant && !this.hasProductsWithAttribute(to_attribute)) {

                        let attribute_params = [];

                        to_attribute.values.forEach(value => attribute_params.push(value));

                        params.push(attribute_params);
                    }
                });

                if (params.length) {

                    let combinations = this.combinator(params);

                    if (!Array.isArray(combinations[0])) {

                        let newCombinations = [];

                        for (let combination of combinations) {
                            newCombinations.push([
                                combination
                            ]);
                        }

                        combinations = newCombinations;
                    }

                    let self = this;

                    let newVariants = [];

                    this.product.variants.forEach(variant => {

                        combinations.forEach(function (values) {

                            let names = [];

                            let new_values = variant.variant_attribute_values.concat(values);

                            new_values.forEach(function (value) {
                                names.push(value.value);
                            });

                            newVariants.push({
                                id: 'tmp-' + Math.random(100000, 10000000),
                                param_text: names.join(' & '),
                                variant_attribute_values: new_values,
                                vendor_price: variant.vendor_price,
                                quantity: variant.quantity,
                                translates: self.$root.copy(variant.translates),
                                sku: variant.sku,
                                discounts: variant.discounts,
                                specials: variant.specials,
                                images: variant.images
                            });


                        });
                    });


                    this.$set(this.product, 'variants', newVariants);

                    this.product.primary_variant_id = this.product.variants[0].id;
                }
            },
            hasProductsWithAttribute(to_attribute) {

                if (to_attribute.attribute === null) return false;

                return this.product.variants.filter(variant => {
                    return variant.variant_attribute_values.filter(param_to_variant => param_to_variant.attribute_id === to_attribute.attribute.id).length;
                }).length;
            },
            showConfiguration() {
                this.$root.changePopupShowStatus('variants-configuration', true);
            },

            generateData() {
                let self = this;

                this.prepareVariants();

                let price = self.fillingConfiguration.price.custom ? self.fillingConfiguration.price.value : 0;
                let quantity = self.fillingConfiguration.quantity.custom ? self.fillingConfiguration.quantity.value : 0;
                let imageInfo = self.fillingConfiguration.image.custom ? self.fillingConfiguration.image : {
                    src: '',
                    filemanager_thumb: ''
                };

                this.product.variants.forEach(variant => {
                    variant.vendor_price = price;
                    variant.quantity = quantity;


                    variant.images[0].src = imageInfo.src;
                    variant.images[0].filemanager_thumb = imageInfo.filemanager_thumb;
                });

                if (!this.product.primary_variant_id || this.product.variants.map(variant => variant.id).indexOf(this.product.primary_variant_id) === -1) {
                    this.product.primary_variant_id = this.product.variants[0].id;
                }

                self.$root.changePopupShowStatus('variants-filling', false);

                this.updateProduct();
            },

            prepareVariants(single = false) {

                let params = [];

                let self = this;

                let combinations;

                if (!single) {
                    this.product.to_attributes.filter(to_attr => {
                        return to_attr.has_variant
                    }).forEach(function (to_attribute) {

                        let attribute_params = [];

                        to_attribute.values.forEach(function (value) {
                            attribute_params.push(value);
                        });

                        params.push(attribute_params);

                    });

                    combinations = this.combinator(params);
                } else {

                    let attribute_values = [];

                    Object.values(this.single).forEach(attribute_value_id => {
                        this.product.to_attributes.filter(to_attr => {
                            return to_attr.has_variant
                        }).forEach(function (to_attribute) {
                            to_attribute.values.forEach(value => {
                                if (value.id === attribute_value_id) {
                                    attribute_values.push(value);
                                }
                            });
                        });
                    });

                    combinations = [attribute_values];
                }

                if (!Array.isArray(combinations[0])) {

                    let newCombinations = [];

                    for (let combination of combinations) {
                        newCombinations.push([
                            combination
                        ]);
                    }

                    combinations = newCombinations;
                }

                let translates = [];

                if (self.product.translates.length) {
                    translates = self.product.translates;
                } else {
                    this.languageLinks.forEach(language => {
                        translates.push({
                            locale: language.locale,
                            name: ''
                        });
                    });
                }

                combinations.forEach(function (values) {

                    let names = [];

                    values.forEach(function (value) {
                        names.push(value.value);
                    });

                    self.product.variants.push({
                        id: 'tmp-' + Math.random(100000, 10000000),
                        param_text: names.join(' & '),
                        variant_attribute_values: values,
                        vendor_price: self.price,
                        quantity: self.quantity,
                        translates: self.$root.copy(translates),
                        sku: '',
                        discounts: [],
                        specials: [],
                        images: [
                            {
                                src: '',
                                filemanager_thumb: '',
                            }
                        ],
                    });
                });
            },

            combinator: function (matrix) {
                return matrix.reduceRight(function (combination, x) {
                    var result = [];
                    x.forEach(function (a) {
                        combination.forEach(function (b) {
                            result.push([a].concat(b));
                        });
                    });
                    return result;
                });
            },

            getValueRowClass(product) {
                let rowClass = 'table__row';

                if (product.id === this.product.primary_variant_id) rowClass += ' table__row--selected';

                return rowClass;
            },

            removeVariant(index) {
                let product = this.product;

                let variant = product.variants[index];

                let variant_params = variant.variant_attribute_values;

                let removedId = variant.id;

                product.variants.splice(index, 1);

                let all_variants_params = [];

                product.variants.forEach(variant => variant.variant_attribute_values.forEach(variant_attribute_value => all_variants_params.push(variant_attribute_value.id)));

                variant_params.forEach(param => {

                    let values = product.to_attributes.find(to_attribute => to_attribute.has_variant && to_attribute.attribute.id === param.attribute_id).values;

                    let valueForRemove = values[values.findIndex(value => value.id === param.id)];

                    if (all_variants_params.indexOf(valueForRemove.id) === -1) {

                        values.splice(values.findIndex(value => value.id === param.id), 1);
                    }

                });

                if (removedId === product.primary_variant_id && product.variants.length) {
                    product.primary_variant_id = product.variants[0].id;
                }
            },
            editVariant(variant) {
                this.$set(this, 'modalVariant', variant);

                this.$root.changePopupShowStatus('edit-variant-modal', true);
            },
            addVariantDiscount() {
                this.modalVariant.discounts.push({
                    user_group_id: null,
                    quantity: null,
                    price: null,
                    date_start: null,
                    date_end: null
                });
            },
            addVariantSpecial() {
                this.modalVariant.specials.push({
                    user_group_id: null,
                    price: null,
                    date_start: null,
                    date_end: null
                });
            },
            addSingle() {
                this.prepareVariants(true);
                this.single = {};
            },
            openUploadWindow() {
                let self = this;
                var route_prefix = '/admin/filemanager';

                window.open(route_prefix + '?items_type=products&item=' + btoa('products-' + this.product.id) + '&type=' + (self.type ? self.type : 'image') + 'FileManager', 'width=900,height=600');

                window.SetUrl = function (info) {


                    if (info.length && !self.modalVariant.images[0].src.length) {
                        self.modalVariant.images.splice(0, 1);
                    }

                    info.forEach(image => {
                        self.modalVariant.images.push({
                            'src': image.name,
                            'filemanager_thumb': image.thumb_url
                        });
                    });
                };
            },
            addVariant() {

                this.$set(this, 'updateConfiguration', []);

                this.to_attributes_with_variants.forEach(to_attr => this.updateConfiguration.push({
                    to_attribute: to_attr,
                    value: null
                }));

                this.$root.changePopupShowStatus('add-variant', true);
            },
            closeEditModal() {
                this.$root.changePopupShowStatus('edit-variant-modal', false);

                this.tab = 'main';

                this.$set(this, 'modalVariant', {});
            },
            createAttributeValueForNewConfiguration(id, ref, index) {
                let translates = [];

                let to_attribute_index = this.product.to_attributes.findIndex(to_attribute => to_attribute.id === id);

                let name = this.product.to_attributes[to_attribute_index].attributeValueSearchPhrase;

                this.$root.languages.forEach(language => {
                    translates.push({
                        locale: language.locale,
                        value: name
                    });
                });

                this.refreshing = true;

                axios.post('/admin/attribute-values', {
                    attribute_id: this.product.to_attributes[to_attribute_index].attribute.id,
                    slug: null,
                    sort_order: 0,
                    status: true,
                    translates: translates
                }).then(httpResponse => {

                    this.attribute_values.push({
                        id: httpResponse.data.id,
                        value: name,
                        attribute_id: this.product.to_attributes[to_attribute_index].attribute.id
                    });

                    this.$set(this.updateConfiguration[index], 'value', {
                        id: httpResponse.data.id,
                        value: name,
                        attribute_id: this.product.to_attributes[to_attribute_index].attribute.id
                    });

                    this.$refs[ref][0].search = '';

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            clearUnusedNewToAttributes() {

                let productUsingAttributes = [];

                this.product.variants.forEach(variant => variant.variant_attribute_values.forEach(variant_attribute_value => productUsingAttributes.push(variant_attribute_value.attribute_id)));

                this.$set(this.product, 'to_attributes', this.product.to_attributes.filter(to_attribute => {
                    return (!to_attribute.has_variant || (to_attribute.attribute !== null && productUsingAttributes.indexOf(to_attribute.attribute.id) !== -1));
                }));
            }
        },
    }
</script>