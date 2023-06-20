<!--
/*
 * This file is part of OrangeHRM Inc
 *
 * Copyright (C) 2020 onwards OrangeHRM Inc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see  http://www.gnu.org/licenses
 */
-->

<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-paper-container">
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title"
          >Subscribe For Free Hosting</oxd-text
        >
      </div>
      <div>
        <oxd-divider />
        <oxd-form
          ref="subscribeForm"
          method="post"
          :action="submitUrl"
          @submit-valid="onSubmit"
        >
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field label="System Url" :value="url" disabled />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="subscribe.companyName"
                  :value="companyName"
                  label="Company Name"
                  name="companyName"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="subscribe.noOfEmployee"
                  :value="noOfEmployees"
                  label="No. of Employees"
                  name="noOfEmployee"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="subscribe.countryCode"
                  :value="country"
                  type="select"
                  :label="$t('general.country')"
                  :options="countries"
                  name="countryCode"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="subscribe.contactPersonName"
                  :value="contactPersonName"
                  label="Contact Person Name"
                  name="contactPersonName"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="subscribe.contactNumber"
                  :value="contactNumber"
                  label="Contact Number"
                  name="contactNumber"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="subscribe.email"
                  :vlaue="email"
                  label="Email"
                  name="email"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <div class="orangehrm-action-button">
            <oxd-form-actions>
              <oxd-button
                type="button"
                display-type="ghost"
                label="Cancel"
                @click="onCancel"
              />
              <submit-button label="Submit" />
            </oxd-form-actions>
          </div>
        </oxd-form>
      </div>
    </div>
  </div>
</template>

<script>
import {urlFor} from '@/core/util/helper/url';

export default {
  name: 'SubscribeFreeHosting',

  props: {
    countries: {
      type: Array,
      default: () => [],
    },
    url: {
      type: String,
      required: true,
    },
    companyName: {
      type: String,
      required: true,
    },
    contactNumber: {
      type: String,
      required: true,
    },
    email: {
      type: String,
      required: true,
    },
    contactPersonName: {
      type: String,
      required: true,
    },
    country: {
      type: String,
      default: null,
    },
    noOfEmployees: {
      type: Number,
      default: 0,
    },
  },

  data() {
    return {
      subscribe: {
        noOfEmployee: '',
        companyName: '',
        contactNumber: '',
        email: '',
        contactPersonName: '',
        countryCode: [],
      },
    };
  },

  computed: {
    submitUrl() {
      return urlFor('/trial/subscribeFreeHosting');
    },
  },
  methods: {
    onSubmit() {
      this.$refs.subscribeForm.$el.submit();
    },
  },
};
</script>

<style src="./subscribe-free-hosting.scss" lang="scss" scoped></style>
