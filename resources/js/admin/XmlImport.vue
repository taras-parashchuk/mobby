<template>

    <div class="table-list-container">
        <div class="flex flex--justify-space-between">
            <div>
                <h2 class="mainContent__heading">
                    {{$root.translate.menu.synchronizations.items['xml-import']}}
                </h2>
            </div>
        </div>
        <div class="singleForm">
            <div class="tabs">
                <a @click="tab = 'configurations'" href="javascript:void(0)"
                   class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'configurations'}">
                    {{$root.translate.columns.configurations}}
                </a>
                <a @click="tab = 'manually'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'manually'}">
                    {{$root.translate.columns.manually}}
                </a>
                <a @click="tab = 'auto-sync'" href="javascript:void(0)"
                   class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'auto-sync'}">
                    {{$root.translate.columns.auto_sync_heading}}
                </a>
            </div>
            <div class="singleForm__content">
                <template v-if="tab === 'configurations'">
                    <div class="listData listData--noBg" v-if="configurations.length">
                        <vue-good-table
                                :columns="columns"
                                :rows="configurations"
                                styleClass="table"
                                row-style-class="table__row">
                            <template slot="table-row" slot-scope="props">
                                <template v-if="props.column.field === 'name'">
                                    <div class="flex flex--align-center">
                                        <input type="text" class="input" v-model="configurations[props.index].name">
                                    </div>
                                </template>
                                <template v-else-if="props.column.field === 'actions'">
                                    <template v-if="configurations[props.index].refreshing">
                                        <font-awesome-icon class="text-warning" icon="circle-notch"
                                                           spin></font-awesome-icon>
                                    </template>
                                    <template v-else>
                                        <a v-if="isChangedConfigurationRow(configurations[props.index].name)"
                                           class="table__action"
                                           href="javascript:void(0)"
                                           @click.stop="storeConfiguration(configurations[props.index])">
                                            <icon icon="floppy-disk" class="icon"></icon>
                                        </a>
                                        <a href="javascript:void(0)" class="table__action"
                                           @click.stop="editConfiguration(props.index)">
                                            <icon icon="pencil-edit-button" class="icon"></icon>
                                        </a>
                                        <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                           href="javascript:void(0)"
                                           @click.stop="deleteConfiguration(props.index)">
                                            <icon icon="delete" class="icon"></icon>
                                        </a>
                                    </template>
                                </template>
                            </template>
                        </vue-good-table>
                    </div>
                </template>
                <template v-else-if="tab === 'manually'">
                    <div class="flex">
                        <div class="mr--30">
                            <div class="singleFormGroup sync">
                                <div class="singleFormGroup__title">
                                    {{$root.translateWords('Import data')}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex">

                                        <div class="dropZone">
                                            <div class="dropZone__title" v-show="!queueProcessing">
                                                {{ selectedFileNameIfExists }}
                                            </div>
                                            <label class="dropZone__actionZone" for="validatedCustomFile">

                                                <template v-if="!queueProcessing">
                                                    <icon v-if="canUploadFile" class="icon" icon="add-file"></icon>
                                                </template>

                                                <span class="dropZone__helpText">
                                                    <template v-if="queueProcessing">
                                                        <template v-if="queue.type !== null">
                                                            {{queue.type + (queue.current < queue.total ? ('  ' + queue.current + '/' + queue.total) : '')}}
                                                        </template>
                                                        <template v-else>
                                                            {{$root.translateWords('Added to queue')}}
                                                        </template>
                                                    </template>
                                                    <template v-else>
                                                        {{$root.translateWords('Click to load file')}}
                                                    </template>
                                                </span>
                                                <span class="dropZone__subResult dropZone__subResult--error"
                                                      v-if="!queueProcessing && queue">
                                                    <template v-if="queue.fatal_error === true">
                                                        {{$root.translateWords('Import was breaked with error')}}
                                                    </template>
                                                    <template v-else-if="queue.breaked === true">
                                                        {{$root.translateWords('Import was breaked successfully')}}
                                                    </template>
                                                </span>
                                            </label>
                                            <input @change="changeFile()"
                                                   v-if="canUploadFile"
                                                   type="file" ref="file"
                                                   class="custom-file-input"
                                                   id="validatedCustomFile"
                                                   required v-show="false"/>
                                            <div class="flex flex--justify-center">
                                                <template v-if="!queueProcessing">
                                                    <a href="javascript:void(0)" class="btn"
                                                       :class="hasNewUploadedFile && !refreshing ? 'btn--confirm': 'btn--disabled'"
                                                       @click="hasNewUploadedFile && !refreshing ? loadFile() : null">
                                                        {{$root.translateWords('Use import')}}
                                                    </a>
                                                </template>
                                                <template v-else>
                                                    <a href="javascript:void(0)"
                                                       class="btn btn--confirm" @click="breakManualImport">
                                                        {{$root.translateWords('Break import')}}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>

                                        <table class="syncInfo" v-if="queue !== null">
                                            <tr class="syncInfo__param syncInfoParam">
                                                <td class="syncInfoParam__name">{{$root.translateWords('Categories'
                                                    + ' imported successfully')}}:
                                                </td>
                                                <td class="syncInfoParam__value">{{queue.success_categories_count || 0}}
                                                </td>
                                            </tr>
                                            <tr class="syncInfo__param syncInfoParam">
                                                <td class="syncInfoParam__name">{{$root.translateWords('Products' +
                                                    ' imported successfully')}}:
                                                </td>
                                                <td class="syncInfoParam__value">
                                                    {{queue.success_products_count || 0}}
                                                </td>
                                            </tr>
                                            <tr class="syncInfo__param syncInfoParam">
                                                <td class="syncInfoParam__name">
                                                    {{$root.translateWords('Warnings')}}:
                                                </td>
                                                <td class="syncInfoParam__value">{{queue.warnings_count || 0}}</td>
                                            </tr>
                                            <tfoot v-if="queue.warnings_count > 0 || queue.fatal_error === true">
                                            <tr>
                                                <td rowspan="2" class="syncInfoParam__link">
                                                    <a href="javascript:void(0)" class="text-link"
                                                       @click="downloadReport(queue.id)">
                                                        {{$root.translateWords('Download report')}}
                                                    </a>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.configuration}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <v-select
                                                :disabled="queueProcessing"
                                                :clearable="true"
                                                :searchable="true"
                                                :options="configurations"
                                                v-model="manual_configuration"
                                                :reduce="configuration => configuration.id"
                                                class="input input--inForm"
                                                label="name">
                                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                        </v-select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else-if="tab === 'auto-sync'">
                    <div class="flex flex--align-center additionalInfo">
                        {{$root.translateWords('You could create rules for auto import data from url at prefer time ' +
                        'here')}}
                    </div>
                    <table class="widget__table table">
                        <thead>
                        <tr class="table__row">
                            <td class="table__heading table__heading--inConfiguration_start">
                                {{$root.translate.columns.import_link}}
                            </td>
                            <td class="table__heading">
                                {{$root.translate.columns.configuration}}
                            </td>
                            <td class="table__heading">
                                {{$root.translate.columns.sync_time}}
                            </td>
                            <td class="table__heading">
                                {{$root.translate.columns.status}}
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="table__row" v-for="(autoAction, autoActionIndex) in autoActions">
                            <td class="table__value table__value--inConfiguration">
                                <input type="text" class="input" v-model="autoAction.link">
                            </td>
                            <td class="table__value table__value--inConfiguration">
                                <v-select
                                        :clearable="false"
                                        :searchable="false"
                                        :options="configurations"
                                        v-model="autoAction.configuration_id"
                                        :reduce="configuration => configuration.id"
                                        class="input input--inForm"
                                        label="name">
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                            </td>
                            <td class="table__value table__value--inConfiguration">

                                <datetime class="vdatetime--time mr--8" input-class="input input--time"
                                          v-model="autoAction.times[0]"
                                          type="time"
                                          :phrases="datetime.phrases"
                                          value-zone='Europe/Kiev'
                                          format="HH:mm"/>

                                <template v-if="autoAction.times.length < 2">
                                    <div class="add" @click="autoAction.times.push(null)">+</div>
                                </template>
                                <template v-else>
                                    <datetime class="vdatetime--time" input-class="input input--time"
                                              v-model="autoAction.times[1]"
                                              type="time"
                                              :phrases="datetime.phrases"
                                              value-zone='Europe/Kiev'
                                              format="HH:mm"/>
                                </template>
                            </td>
                            <td class="table__value table__value--inConfiguration">
                                <div class="status">
                                    <div class="status__mark"
                                         :class="(autoAction.fatal_error === true) ? 'status__mark--false': 'status__mark--ok'"></div>

                                    <v-popover v-if="autoAction.fatal_error === true || autoAction.warnings_count > 0"
                                               offset="0" placement="auto" trigger="hover" popoverClass="popover"
                                               :autoHide="false">

                                        <div class="status__text">
                                            <template v-if="autoAction.fatal_error === true">
                                                {{$root.translateWords('Fail')}}
                                            </template>
                                            <template
                                                    v-else-if="autoAction.finished === false && autoAction.type !== null">
                                                {{$root.translateWords('Updating')}}
                                            </template>
                                            <template v-else-if="autoAction.finished === true">
                                                {{$root.translateWords('Updated')}}
                                            </template>
                                            {{autoAction.updated_at}}
                                        </div>

                                        <template slot="popover">
                                            <p class="popover__content">
                                                <a href="javascript:void(0)" class="text-link"
                                                   @click.native="downloadReport(autoAction.id)">
                                                    {{$root.translateWords('Download report')}}
                                                </a>
                                            </p>
                                            <a class="popover__close sb-icon-cancel" v-close-popover></a>
                                        </template>

                                    </v-popover>

                                </div>
                                <div class="ml--10 switcherStatus switcherStatus--inline">
                                    <div @click="autoAction.status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': autoAction.status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="autoAction.status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': autoAction.status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                                <template v-if="autoAction.refreshing">
                                    <font-awesome-icon class="text-warning" icon="circle-notch"
                                                       spin></font-awesome-icon>
                                </template>
                                <template v-else>
                                    <a v-if="isChangedActionRow(autoAction.id)" class="table__action"
                                       href="javascript:void(0)" @click.stop="storeAutoAction(autoActionIndex)">
                                        <icon icon="floppy-disk" class="icon"></icon>
                                    </a>
                                    <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                       href="javascript:void(0)" @click.stop="removeAutoAction(autoActionIndex)">
                                        <icon icon="delete" class="icon"></icon>
                                    </a>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>
            </div>
        </div>


        <modal name="xml-configuration" width="80%" v-if="Object.keys(modalConfiguration).length"
               :title="$root.translateWords(modalConfiguration.edit ? 'Editing configuration': 'Creating configuration')"
               @closed="closeModalConfiguration">
            <template v-slot>
                <div class="configuration">

                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.name}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm" v-model="modalConfiguration.data.name">
                            </div>
                        </div>
                    </div>

                    <div class="configuration__section configurationSection">
                        <div class="configurationSection__title">
                            {{$root.translateWords('Path to data')}}
                        </div>
                        <table class="widget__table table">
                            <thead>
                            <tr class="table__row">
                                <td class="table__heading table__heading--inConfiguration_start">
                                    {{$root.translate.columns.field}}
                                </td>
                                <td class="table__heading">
                                    {{$root.translate.columns.value}}
                                </td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="table__row" v-for="(path, path_code) in modalConfiguration.data.settings.paths"
                                v-if="canFillPath(path)">
                                <td class="table__value table__value--inConfiguration">
                                    {{$root.translate.sync[path_code]}}<i class="text-danger">*</i>
                                </td>
                                <td class="table__value table__value--inConfiguration">
                                    <v-select
                                            :clearable="true"
                                            :no-drop="true"
                                            placeholder="input"
                                            taggable multiple push-tags
                                            v-model="path.value"
                                            label="title"
                                            class="input input--inForm input--tags vs--multiply vs--path pt--0 pb--0"
                                            :create-option="makeNewPathItem"
                                            :components="{Deselect}">
                                        <template v-slot:selected-option-container="item">
                                            <div class="flex flex--align-center mb--12 mt--4 mr--8">
                                                <span class="vs__selected"
                                                      :class="{'vs__selected--disabled': item.option.disabled}">
                                                    {{item.option.title}}
                                                    <button class="vs__deselect" v-if="!item.option.disabled"
                                                            @click.stop="path.value.splice(path.value.indexOf(item.option.label),1)">
                                                        <icon class="icon" icon="error"></icon>
                                                    </button>
                                                </span>
                                                <icon v-if="item.option !== path.value[path.value.length - 1]"
                                                      class="icon vs__selected--next" icon="next"></icon>
                                            </div>
                                        </template>
                                        <template #search="{ attributes, events }">
                                            <input
                                                    autocomplete="off"
                                                    type="search"
                                                    class="vs__search vs__search--for-path"
                                                    :placeholder="$root.translateWords('Add to list')"
                                                    v-bind="attributes"
                                                    v-on="events">
                                        </template>
                                    </v-select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="configuration__section configurationSection">
                        <div class="configurationSection__title">
                            {{$root.translate.columns.import_settings}}
                        </div>
                        <table class="widget__table table">
                            <thead>
                            <tr class="table__row">
                                <td class="table__heading table__heading--inConfiguration_start">
                                    {{$root.translate.columns.field}}
                                </td>
                                <td class="table__heading">{{$root.translate.columns.creating}}</td>
                                <td class="table__heading">{{$root.translate.columns.updating}}</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="table__row" v-for="(creating_updating_setting, title) in modalConfiguration.data.settings.creating_updating">
                                <td class="table__value table__value--inConfiguration">
                                    {{$root.translate.sync[title]}}
                                </td>
                                <td class="table__value table__value--inConfiguration">
                                    <div class="switcherStatus">
                                        <div @click="creating_updating_setting.creating = false"
                                             class="switcherStatus__value"
                                             :class="{'switcherStatus__value--active switcherStatus__value--active_off': creating_updating_setting.creating === false}">
                                            {{$root.translate.columns.disabled_short}}
                                        </div>
                                        <div @click="creating_updating_setting.creating = true"
                                             class="switcherStatus__value"
                                             :class="{'switcherStatus__value--active': creating_updating_setting.creating === true}">
                                            {{$root.translate.columns.enabled_short}}
                                        </div>
                                    </div>
                                </td>
                                <td class="table__value table__value--inConfiguration">
                                    <div class="switcherStatus">
                                        <div @click="creating_updating_setting.updating = false"
                                             class="switcherStatus__value"
                                             :class="{'switcherStatus__value--active switcherStatus__value--active_off': creating_updating_setting.updating === false}">
                                            {{$root.translate.columns.disabled_short}}
                                        </div>
                                        <div @click="creating_updating_setting.updating = true"
                                             class="switcherStatus__value"
                                             :class="{'switcherStatus__value--active': creating_updating_setting.updating === true}">
                                            {{$root.translate.columns.enabled_short}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="configuration__section configurationSection">
                        <div class="configurationSection__title">{{$root.translateWords('Matching categories')}}</div>
                        <div v-if="!modalConfiguration.data.settings.relationCategories.categories.length"
                             class="flex flex--align-center mb--16">
                            <div class="switcher">
                                <check @click.native="modalConfiguration.data.settings.relationCategories.type = 0"
                                       :checked="modalConfiguration.data.settings.relationCategories.type===0"
                                       class="switcher__icon"></check>
                                <span class="switcher__label">{{$root.translateWords('Skip')}}</span>
                            </div>
                            <div class="switcher">
                                <check @click.native="modalConfiguration.data.settings.relationCategories.type = 1"
                                       :checked="modalConfiguration.data.settings.relationCategories.type===1"
                                       class="switcher__icon"></check>
                                <span class="switcher__label">{{$root.translate.columns.load_file}}</span>
                            </div>
                            <div class="switcher">
                                <check @click.native="modalConfiguration.data.settings.relationCategories.type = 2"
                                       :checked="modalConfiguration.data.settings.relationCategories.type===2"
                                       class="switcher__icon"></check>
                                <span class="switcher__label">{{$root.translate.columns.link_to_file}}</span>
                            </div>
                        </div>
                        <div v-if="modalConfiguration.data.settings.relationCategories.type === 1"
                             class="upload-file">

                            <div v-if="modalConfiguration.data.settings.relationCategories.categories.length">
                                <a href="javascript:void(0)" class="text-link">{{modalConfiguration.data.settings.relationCategories.fileUploadName}}</a>
                                <icon @click.native="deleteUploadFile()"
                                      icon="error"
                                      class="icon singleFormGroup__title_helper singleFormGroup__title_helper--delete-file"></icon>
                            </div>
                            <label v-else
                                   for="relationCategoriesFile" class="text-link upload-file__label">
                                <icon class="icon" icon="add-file"></icon>
                                <span class="text-link text-link--underline"> {{$root.translateWords('Choose file')}} </span>
                            </label>
                            <input @change.stop="uploadFileRelationCategories()"
                                   type="file" ref="relationCategoriesFile"
                                   class="custom-file-input"
                                   id="relationCategoriesFile"
                                   required v-show="false"/>
                        </div>

                        <div v-else-if="modalConfiguration.data.settings.relationCategories.type === 2"
                             class="singleFormGroup">
                            <h6 class="singleFormGroup__title singleFormGroup__title--in-relation-categories-XML">
                                {{$root.translate.columns.link_to_file}}:</h6>
                            <div>
                                <input v-model="modalConfiguration.data.settings.relationCategories.fileLinkName"
                                       type="url" name="" id=""
                                       class="input input--in-relation-categories-XML input--label_right">
                                <a href="#" class="text-link">
                                    <span @click="uploadRelationcategories()"
                                          v-if="!modalConfiguration.data.settings.relationCategories.categories.length">
                                        {{$root.translate.columns.read_file}}
                                    </span>

                                    <span @click.stop="deleteUploadFile()"
                                          v-else>{{$root.translateWords('Remove file')}}</span>
                                </a>
                            </div>
                        </div>


                        <div v-if="showRelationCategoriesXML"
                             class="relation-categories-XML">
                            <div v-if="countCheckedRelationsCategories"
                                 class="massActionsHeader">
                                <div class="massActionsHeader__item"> {{ $root.translate.columns.selected_count + ' ' +
                                    countCheckedRelationsCategories}} <span class="vertical_line"></span></div>
                                <div class="massActionsHeader__item">{{$root.translateWords('Match with')}}:</div>
                                <div class="massActionsHeader__item">
                                    <v-select
                                            :clearable="true"
                                            :searchable="true"
                                            :options="categories"
                                            v-model="valueRelationCategoriesXML"
                                            :reduce="category => category.id"
                                            class="input massActionsHeader__input"
                                            label="name">
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                </div>
                                <div><a @click.stop="addValueRelationCategoriesXML()"
                                        href="javascript:void(0)" class="massActionsHeader--link">{{$root.translateWords('Apply')}}</a>
                                </div>
                            </div>

                            <div class="relation-categories-XML__table relation-categories-XML__table--color">
                                <div class="switcher">
                                    <check @click.native.stop="changeCheckedRelationCategories()"
                                           :checked="modalConfiguration.data.settings.relationCategories.isCheckedAll === true"
                                           class="switcher__icon"></check>
                                    <span class="switcher__label relation-categories-XML__table--color">{{$root.translate.columns.categories_in_file}}</span>
                                </div>
                                <p>{{$root.translate.columns.categories_on_site}}:</p>
                            </div>

                            <hr>

                            <div v-for="category in modalConfiguration.data.settings.relationCategories.categories"
                                 :key="category.id" class="relation-categories-XML__table">
                                <div class="switcher" style="">
                                    <check @click.native.stop="changeCategoryChecked(category)"
                                           :checked="category.checked===true"
                                           class="switcher__icon"></check>
                                    <span class="switcher__label">{{category.name}}</span>
                                </div>
                                <div class="">
                                    <v-select
                                            :clearable="true"
                                            :searchable="true"
                                            :options="categories"
                                            v-model="category.site_category_id"
                                            :reduce="category => category.id"
                                            class="input"
                                            label="name">
                                        <div slot="no-options">
                                            {{$root.translateWords('Data not found')}}
                                        </div>
                                    </v-select>
                                </div>
                            </div>

                            <hr class="mb--30">

                        </div>
                    </div>

                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translateWords('If categories present on site, but absent in file - do')}}:
                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <v-select
                                        :options="missingProductsActions"
                                        v-model="modalConfiguration.data.settings.missingProductsAction"
                                        :reduce="action => action.type"
                                        class="input"
                                        label="name">
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="flex flex--justify-end">
                    <a v-if="!modalConfiguration.edit" href="javascript:void(0)" class="btn btn--confirm"
                       @click="storeConfiguration(modalConfiguration.data)">
                        {{$root.translateWords('Create configuration')}}
                    </a>
                    <a v-else href="javascript:void(0)" class="btn btn--confirm"
                       @click="storeConfiguration(modalConfiguration.data)">
                        {{$root.translateWords('Save changes')}}
                    </a>
                </div>
            </template>
        </modal>

        <widget-actions v-if="tab === 'auto-sync'" :add="modalConfiguration.open ? false: 'addAutoAction'"
                        :trans="{add: $root.translateWords('Create action')}"></widget-actions>

        <widget-actions v-else :add="modalConfiguration.open ? false: 'addConfiguration'"
                        :trans="{add: $root.translateWords('Create configuration')}"></widget-actions>

    </div>
</template>

<script>

    import moment from 'moment';

    export default {
        name: "XmlImport",
        data() {
            return {
                customSettings: {
                    paths: {
                        categories_container: {
                            access_fill_if: null,
                            value: [
                                {
                                    title: 'shop',
                                    disabled: false
                                },
                                {
                                    title: 'categories',
                                    disabled: false
                                },
                            ]
                        },
                        category_tag: {
                            access_fill_if: null,
                            value: [
                                {
                                    title: this.$root.translate.sync.categories_container,
                                    disabled: true
                                },
                                {
                                    title: 'category',
                                    disabled: false
                                },
                            ]
                        },
                        category_id: {
                            access_fill_if: 'category_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.categories_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.category_tag,
                                    disabled: true
                                },
                                {
                                    title: 'id',
                                    disabled: false
                                }
                            ]
                        },
                        category_name: {
                            access_fill_if: 'category_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.categories_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.category_tag,
                                    disabled: true
                                },
                                {
                                    title: 'name',
                                    disabled: false
                                }
                            ]
                        },
                        category_parent_id: {
                            access_fill_if: 'category_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.categories_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.category_tag,
                                    disabled: true
                                },
                                {
                                    title: 'parent_id',
                                    disabled: false
                                }
                            ]
                        },

                        products_container: {
                            access_fill_if: null,
                            value: [
                                {
                                    title: 'shop',
                                    disabled: false
                                },
                                {
                                    title: 'offers',
                                    disabled: false
                                },
                            ]
                        },
                        product_tag: {
                            access_fill_if: null,
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: 'offer',
                                    disabled: false
                                },
                            ]
                        },
                        product_id: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'id',
                                    disabled: false
                                }
                            ]
                        },
                        product_quantity: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'quantity',
                                    disabled: false
                                }
                            ]
                        },
                        product_actual_price: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'price',
                                    disabled: false
                                }
                            ]
                        },
                        product_old_price: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'old_price',
                                    disabled: false
                                }
                            ]
                        },
                        product_currency: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'currencyId',
                                    disabled: false
                                }
                            ]
                        },
                        product_sku: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'vendor_code',
                                    disabled: false
                                }
                            ]
                        },
                        product_description: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'description',
                                    disabled: false
                                }
                            ]
                        },

                        product_category_id: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'categoryId',
                                    disabled: false
                                }
                            ]
                        },
                        product_images: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'picture',
                                    disabled: false
                                }
                            ]
                        },
                        product_name: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'name',
                                    disabled: false
                                }
                            ]
                        },
                        product_attribute: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: 'param',
                                    disabled: false
                                }
                            ]
                        },
                        product_attribute_name: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_attribute,
                                    disabled: true
                                },
                                {
                                    title: 'name',
                                    disabled: false
                                }
                            ]
                        },
                        product_attribute_value: {
                            access_fill_if: 'product_tag',
                            value: [
                                {
                                    title: this.$root.translate.sync.products_container,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_tag,
                                    disabled: true
                                },
                                {
                                    title: this.$root.translate.sync.product_attribute,
                                    disabled: true
                                },
                            ]
                        },
                        site_source: {
                            access_fill_if: null,
                            value: []
                        },
                    },
                    'creating_updating': {
                        category_name: {
                            creating: true,
                            updating: false
                        },
                        category_parent_id: {
                            creating: true,
                            updating: false
                        },
                        product_quantity: {
                            creating: true,
                            updating: false
                        },
                        product_actual_price: {
                            creating: true,
                            updating: false
                        },
                        product_old_price: {
                            creating: true,
                            updating: false
                        },
                        product_currency: {
                            creating: true,
                            updating: false
                        },
                        product_sku: {
                            creating: true,
                            updating: false
                        },
                        product_description: {
                            creating: true,
                            updating: false
                        },
                        product_category_id: {
                            creating: true,
                            updating: false
                        },
                        product_images: {
                            creating: true,
                            updating: false
                        },
                        product_name: {
                            creating: true,
                            updating: false
                        },
                        product_slug: {
                            creating: true,
                            updating: false
                        },
                        product_attribute: {
                            creating: true,
                            updating: false
                        }
                    },
                    "relationCategories": {
                        // 0 пропустити 1 файл  2 силка
                        type: 0,
                        fileUploadName: "",
                        fileLinkName: "",
                        isCheckedAll: false,
                        categories: []
                    },
                    missingProductsAction: null
                },
                tab: 'configurations',
                datetime: {
                    phrases: {ok: this.$root.translateWords('Apply'), cancel: this.$root.translateWords('Reset')},
                },
                fileName: null,
                categories: [],
                columns: [
                    {
                        label: this.$root.translate.columns.configuration_name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.actions,
                        field: 'actions',
                        thClass: 'table__heading table__heading--text_right',
                        tdClass: 'table__value table__value--text_right',
                        sortable: false,
                    },
                ],
                savedOriginalConfigurations: [],
                manual_configuration: null,
                configurations: [
                    {
                        name: 'standart(rozetka)',
                        settings: {},
                        refreshing: false
                    }
                ],
                refreshing: false,
                modalConfiguration: {
                    edit: false,
                    open: false,
                    data: {
                        name: '',
                        refreshing: false,
                        settings: {}
                    }
                },
                queue: null,
                Deselect: {
                    render: createElement => createElement('icon', {
                        class: 'icon',
                        props: {
                            icon: 'error'
                        }
                    }),
                },
                autoActions: [],
                savedOriginalActions: [],
                valueRelationCategoriesXML: null,
                missingProductsActions: [
                    {
                        name: this.$root.translateWords('Skip'),
                        type: "skip"
                    },
                    {
                        name: this.$root.translateWords('Delete'),
                        type: "remove"
                    }
                ]
            }
        },
        created() {

            let self = this;

            this.$set(this.modalConfiguration.data, 'settings', this.$root.copy(this.customSettings));

            this.configurations.map(configuration => {
                configuration.settings = this.customSettings;
            });

            // список синхронізацій
            axios.get('/admin/sync-configurations', {
                params: {
                    type: 'xml'
                }
            }).then(httpResponse => {

                this.configurations.forEach(configuration => {
                    if (!configuration.id) configuration.id = 'tmp-' + Math.random(100000, 10000000);
                });

                if (httpResponse.data.length) {

                    for (let configuration of httpResponse.data) {

                        let confIndex = this.configurations.findIndex(conf => {

                            return conf.name === configuration.name;
                        });

                        if (confIndex !== -1) {
                            this.configurations.splice(confIndex, 1);
                        }

                        configuration.refreshing = false;

                        let copyPaths = this.$root.copy(this.customSettings.paths);

                        let force_updating = false;

                        for(let path_code in copyPaths){
                            if(Object(configuration.settings.paths).hasOwnProperty(path_code)){

                                copyPaths[path_code].value = copyPaths[path_code].value.filter(value => value.disabled);

                                configuration.settings.paths[path_code].forEach(value => copyPaths[path_code].value.push({
                                    title: value,
                                    disabled: false
                                }));
                            }else{
                                force_updating = true;
                            }
                        }

                        configuration.settings.paths = copyPaths;

                        let copy_creating_updating_settings = this.$root.copy(this.customSettings.creating_updating);

                        if( JSON.stringify(Object.keys(copy_creating_updating_settings)) !==  JSON.stringify(Object.keys(configuration.settings.creating_updating)) ){
                            force_updating = true;
                        }

                        configuration.settings.creating_updating = Object.assign({}, copy_creating_updating_settings, configuration.settings.creating_updating);

                        this.configurations.push(configuration);

                        force_updating ? self.storeConfiguration(configuration, false) : this.savedOriginalConfigurations.push(this.$root.copy(configuration));

                    }

                }
// якщо є не створені створити
                this.configurations.filter(configuration => typeof configuration.id !== 'number').forEach(configuration => {
                    self.storeConfiguration(configuration, false);
                });

            });

            this.getCategories();

            this.getQueueStatus();

            this.getAutoActions();

        },
        computed: {
            // перевіряє чи можна завантаж назви
            canUploadFile() {
                return this.queue === null || (this.queue !== null && (this.queue.breaked === true || this.queue.finished === true || this.queue.fatal_error === true));
            },
            // назву файлу якщо вибраний (ручна)
            selectedFileNameIfExists() {
                return this.fileName ? this.fileName : this.$root.translateWords('File not selected');
            },
            // чи є ручна синхронізація
            queueProcessing() {
                return this.queue && this.queue.job_id !== null;
            },
            // чи може людина завантаж наступний
            hasNewUploadedFile() {
                return !!this.fileName;
            },
            countCheckedRelationsCategories() {
                return this.modalConfiguration.data.settings.relationCategories.categories.filter(item => item.checked === true).length;
            },
            showRelationCategoriesXML() {
                let relationCategories = this.modalConfiguration.data.settings.relationCategories
                return relationCategories.categories.length &&
                    (relationCategories.type === 1 ||
                        relationCategories.type === 2);
            }
        },
        methods: {
            // запит на бек енд  в якому стані файлик який загружає клієнт
            getQueueStatus() {
                axios.get('/admin/xml/get-queue-status').then(response => {

                    if (response.data) {
                        this.$set(this, 'queue', response.data);

                        if (this.manual_configuration === null) this.$set(this, 'manual_configuration', response.data.configuration_id);

                        if (this.queueProcessing) setTimeout(this.getQueueStatus, 10000);

                    } else {
                        this.$set(this, 'queue', null);
                    }

                });
            },

            // кнопка зберегти тільки при збережені даних  (чи відрізняються від попередніх)
            isChangedConfigurationRow(name) {

                let originalPosition = this.savedOriginalConfigurations.findIndex(item => {
                    return item.name === name
                });

                if (originalPosition === -1) {
                    return true;
                }

                let currentPosition = this.configurations.findIndex(item => {
                    return item.name === name
                });

                let current = this.$root.copy(this.configurations[currentPosition]);

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginalConfigurations[originalPosition]);

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);
            },
// створення самостійно клієнтами кастумні настройки 
            addConfiguration() {

                this.modalConfiguration.edit = false;
                this.modalConfiguration.open = true;
                this.modalConfiguration.data.name = '';
                this.modalConfiguration.data.type = 'xml';
                this.modalConfiguration.data.id = 'tmp-' + Math.random(100000, 10000000);

                this.$root.changePopupShowStatus('xml-configuration', true);
            },
// за оновлення або створення конфігурації
            storeConfiguration(configuration, pushToList = true) {

                let configuration_id = configuration.id;

                let originalPosition = this.savedOriginalConfigurations.findIndex(item => {
                    return item.id === configuration_id
                });

                let request;

                configuration.refreshing = true;

                configuration.type = 'xml';

                let modified_configuration = this.$root.copy(configuration);

                for(let key in modified_configuration.settings.paths){

                    delete modified_configuration.settings.paths[key].access_fill_if;

                    modified_configuration.settings.paths[key] = modified_configuration.settings.paths[key].value.filter(item => !item.disabled).map(item => item.title);

                }

                if (typeof configuration.id === 'number') {
                    request = axios.put('/admin/sync-configurations/' + configuration_id, modified_configuration);
                } else {
                    request = axios.post('/admin/sync-configurations', modified_configuration);
                }

                request.then(httpResponse => {

                    if (typeof configuration_id !== 'number') {
                        configuration.id = httpResponse.data.id;

                        configuration.refreshing = false;

                        if (pushToList) this.configurations.push(configuration);
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginalConfigurations, originalPosition, this.$root.copy(configuration));
                    } else {
                        this.savedOriginalConfigurations.push(this.$root.copy(configuration));
                    }

                    this.$root.notify(httpResponse.data);

                    this.closeModalConfiguration();

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    configuration.refreshing = false;
                });

            },
// поп ап з редагуванням конфігурації
            editConfiguration(index) {
                let configuration = this.configurations[index];

                this.modalConfiguration.edit = true;
                this.modalConfiguration.open = true;

                this.$set(this.modalConfiguration, 'data', configuration);

                this.$root.changePopupShowStatus('xml-configuration', true);
            },
// закри поп ап
            closeModalConfiguration() {

                this.$root.changePopupShowStatus('xml-configuration', false);

                this.modalConfiguration.open = false;

                this.$set(this.modalConfiguration, 'data', {
                    name: '',
                    refreshing: false,
                    settings: this.customSettings
                });

            },
// видалити конфігурацію
            deleteConfiguration(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let configuration = this.configurations[index];

                    configuration.refreshing = true;

                    axios.delete('/admin/sync-configurations/' + configuration.id)
                        .then(httpResponse => {

                            this.configurations.splice(index, 1);

                            this.$set(this, 'savedOriginalConfigurations', this.$root.copy(this.configurations));

                            this.$root.notify(httpResponse.data);


                        }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(() => {
                        configuration.refreshing = false;
                    });
                });
            },
// показує назву файла яку вибрав користувач

            changeFile() {
                var files = event.target.files || event.dataTransfer.files;

                if (files[0]) {
                    this.fileName = files[0].name
                }
            },
            uploadFileRelationCategories() {
                var files = event.target.files || event.dataTransfer.files;

                if (files[0]) {
                    this.modalConfiguration.data.settings.relationCategories.fileUploadName = files[0].name;

                    let self = this;

                    let formData = new FormData();


                    formData.append('file', this.$refs.relationCategoriesFile.files[0]);
                    formData.append('configuration_id', this.modalConfiguration.data.id);
                    formData.append('type', this.modalConfiguration.data.settings.relationCategories.type);

                    this.refreshing = true;

                    axios.post('/admin/xml/get-source-categories', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(response => {
                        // this.$refs.file.value = '';
                        // this.fileName = null;
                        response.data.categories.map(item => {
                            item.checked = false;
                            item.site_category_id = null;
                            return item;
                        });

                        this.$set(this.modalConfiguration.data.settings.relationCategories, "categories", response.data.categories);

                    }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(function () {
                        self.refreshing = false;
                    });


                }
            },
            // відправка на сервер для поєднання категорій
            uploadRelationcategories() {

                let self = this;

                let formData = new FormData();

                formData.append('link', this.modalConfiguration.data.settings.relationCategories.fileLinkName);
                formData.append('configuration_id', this.modalConfiguration.data.id);
                formData.append('type', this.modalConfiguration.data.settings.relationCategories.type);

                this.refreshing = true;

                axios.post('/admin/xml/get-source-categories', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    // this.$refs.link.value = '';
                    // this.fileName = null;
                    response.data.categories.map(item => {
                        item.checked = false;
                        item.site_category_id = null;
                        return item
                    })


                    this.$set(this.modalConfiguration.data.settings.relationCategories, "categories", response.data.categories);


                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(function () {
                    self.refreshing = false;
                });


            },
// відпраляж файл з файлу XML для ручної синхронізації
            loadFile() {

                let self = this;

                let formData = new FormData();


                formData.append('file', this.$refs.file.files[0]);
                formData.append('configuration_id', this.manual_configuration);
                formData.append('manually', true);

                this.refreshing = true;

                axios.post('/admin/xml/add-to-queue', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    this.$refs.file.value = '';
                    this.fileName = null;

                    this.$set(this, 'queue', response.data.queue);

                    this.getQueueStatus();

                    this.$root.notify(response.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(function () {
                    self.refreshing = false;
                });
            },
// відпраляж файл з файлу XML для авто синхронізації дод ряд з можливою авто регуванням
            addAutoAction() {
                this.autoActions.push({
                    id: 'tmp-' + Math.random(100000, 10000000),
                    link: '',
                    configuration_id: null,
                    times: [
                        null
                    ],
                    status: false,
                    info: {
                        text: '',
                        status: true
                    },
                    refreshing: false,
                    manually: false,
                    data_type: 'xml'
                });
            },
// отрим актуальні дані з атоматизації
            getAutoActions() {
                axios.get('/admin/auto-sync').then(httpResponse => {
                    if (httpResponse.data.length) {
                        httpResponse.data.forEach(item => {

                            item.times[0] = moment(item.time_1, 'HH:mm:ss').format("YYYY-MM-DDTHH:mm:ss.SSSZ");

                            item.updated_at = moment(item.updated_at, 'YYYY-MM-DD HH:mm:ss').format("HH:mm");

                            if (item.time_2) item.times[1] = moment(item.time_2, 'HH:mm:ss').format("YYYY-MM-DDTHH:mm:ss.SSSZ");

                            item.refreshing = false;
                        });
                    }

                    this.$set(this, 'autoActions', httpResponse.data);
                    this.$set(this, 'savedOriginalActions', this.$root.copy(httpResponse.data));
                });
            },
// зберегти авто синхронізацію
            storeAutoAction(index) {

                let autoAction = this.autoActions[index];

                let autoActionId = autoAction.id;

                let originalPosition = this.savedOriginalActions.findIndex(action => {
                    return action.id === autoActionId
                });

                let request;

                autoAction.refreshing = true;

                if (typeof autoAction.id === 'number') {

                    request = axios.put('/admin/auto-sync/' + autoAction.id, autoAction);
                } else {
                    request = axios.post('/admin/auto-sync', autoAction);
                }

                request.then(httpResponse => {

                    if (typeof autoAction.id !== 'number') {
                        autoAction.id = httpResponse.data.id;
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginalActions, originalPosition, this.$root.copy(autoAction));
                    } else {
                        this.savedOriginalActions.push(this.$root.copy(autoAction));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    autoAction.refreshing = false;
                });
            },
// чи є зміни в авто синхрон для збереження
            isChangedActionRow(id) {

                let originalPosition = this.savedOriginalActions.findIndex(item => {
                    return item.id === id
                });

                if (originalPosition === -1) {
                    return true;
                }

                let currentPosition = this.autoActions.findIndex(item => {
                    return item.id === id
                });

                let current = this.$root.copy(this.autoActions[currentPosition]);

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginalActions[originalPosition]);

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);
            },
// видалити авто синхрон
            removeAutoAction(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let autoAction = this.autoActions[index];

                    let autoActionId = autoAction.id;

                    let originalPosition = this.savedOriginalActions.findIndex(action => {
                        return action.id === autoActionId
                    });

                    autoAction.refreshing = true;

                    if (typeof autoActionId === 'number') {
                        axios.delete('/admin/auto-sync/' + autoActionId).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.autoActions.splice(index, 1);

                            this.savedOriginalActions.splice(originalPosition, 1);

                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            autoAction.refreshing = false;
                        });
                    } else {
                        this.autoActions.splice(index, 1);
                    }
                });
            },
// зупинити ручну синхронизацію
            breakManualImport() {
                axios.delete('xml/break-manual/' + this.queue.id).then(httpResponse => {
                    this.$root.notify(httpResponse.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                });
            },
// скачує файлик з помилками автоматизації
            downloadReport(queueId) {

                axios({
                    url: '/admin/xml/download-report/' + queueId,
                    method: 'GET',
                    responseType: 'blob',
                }).then(response => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'report.log');
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => this.refreshing = false);
            },
// отримати всі категорії
            getCategories() {
                axios.get('/admin/categories', {
                    params: {
                        autocomplete: true
                    }
                }).then(httpResponse => {
                    this.$set(this, 'categories', httpResponse.data.categories);
                });
            },

            changeCategoryChecked(category_item, parent_category = 0) {
                let self = this;
                if (parent_category === 0) {
                    category_item.checked = !category_item.checked;
                } else {
                    category_item.checked = parent_category.checked;
                }
                this.modalConfiguration.data.settings.relationCategories.categories.filter(item => category_item.id === item.parent_id).forEach(function (item) {
                    self.changeCategoryChecked(item, category_item);
                })
            },
            deleteUploadFile() {
                this.$set(this.modalConfiguration.data.settings.relationCategories, "categories", []);
            },
            addValueRelationCategoriesXML() {
                let self = this;
                this.modalConfiguration.data.settings.relationCategories.categories.filter(item => item.checked === true).map(item => item.site_category_id = self.valueRelationCategoriesXML);
            },
            changeCheckedRelationCategories() {
                this.modalConfiguration.data.settings.relationCategories.categories.map(category => category.checked = !category.checked);
                this.modalConfiguration.data.settings.relationCategories.isCheckedAll = !modalConfiguration.data.settings.relationCategories.isCheckedAll;
            },
            canFillPath(path) {
                return path.access_fill_if === null || this.modalConfiguration.data.settings.paths[path.access_fill_if].value.filter(item => !item.disabled).length;
            },
            makeNewPathItem(text) {
                return {
                    title: text,
                    disabled: false,
                };
            }
        }
    }
</script>

<style scoped>

</style>