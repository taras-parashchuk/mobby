<template>
    <div>

        <mdb-input aria-label="First name" class="my-0" placeholder="Type sth">
            <span class="input-group-text md-addon" slot="prepend">First and last name</span>
            <mdb-input noWrapper aria-label="Last name" placeholder="Type sth"/>
            <mdb-input noWrapper aria-label="Ler name" placeholder="Ler sth"/>
            <mdb-btn color="info" size="md" group slot="append" id="button-addon2" @click="add">Додати</mdb-btn>
        </mdb-input>


        <mdb-input v-model="inputData[format[0]]" :placeholder="headers[0]" class="pl-0 ml-0 mt-5 mb-3" ariaDescribedBy="button-addon0">
            <template v-if="format.length > 1">
                <span class="input-group-text md-addon" slot="prepend">
                    {{headers.join(' ')}}
                </span>
                <mdb-input v-for="" v-if="" noWrapper aria-label="Ler name" placeholder="Ler sth"/>
            </template>
            <mdb-btn color="info" size="md" group slot="append" id="button-addon0" @click="add">Додати</mdb-btn>
        </mdb-input>


        <mdb-list-group horizontal>
            <mdb-list-group-item :action="true" v-for="(item,key) in items" :key="key">
                {{item.title}}
            </mdb-list-group-item>
        </mdb-list-group>
    </div>
</template>

<script>

    import {
        mdbAlert,
        mdbBtn,
        mdbContainer,
        mdbCol,
        mdbRow,
        mdbIcon,
        mdbTabs,
        mdbInput,
        mdbToastNotification,
        mdbTblBody,
        mdbTblHead,
        mdbTbl,
        mdbListGroup,
        mdbListGroupItem
    } from 'mdbvue';

    export default {
        name: "ListComponent",
        components: {
            mdbContainer,
            mdbCol,
            mdbRow,
            mdbIcon,
            mdbTabs,
            mdbInput,
            mdbToastNotification,
            mdbBtn,
            mdbAlert,
            mdbTblBody,
            mdbTblHead,
            mdbTbl,
            mdbListGroup,
            mdbListGroupItem
        },
        data() {
            return {
                items: [],
                format: ['value'],
                headers: [
                    'Email'
                ],
                inputData:{

                }
            };
        },
        methods: {
            add() {

            }
        }

    }
</script>

<style>
    .md-form.input-group .form-control {
        padding-left: 0;
    }
</style>