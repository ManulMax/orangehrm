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
        <oxd-form ref="subscribeForm" :loading="isLoading" method="post">
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
                  :rules="rules.companyName"
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
                  :rules="rules.noOfEmployee"
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
                  :rules="rules.contactPersonName"
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
                  :rules="rules.contactNumber"
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
                  :rules="rules.email"
                  label="Email"
                  name="email"
                  :model-value="subscribe.email"
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
import {APIService} from '@ohrm/core/util/services/api.service';
import {
  shouldNotExceedCharLength,
  validPhoneNumberFormat,
  validEmailFormat,
  required,
  digitsOnly,
} from '@ohrm/core/util/validation/rules';
import {reloadPage} from '@/core/util/helper/navigation';
export default {
  name: 'SubscribeFreeHosting',
  props: {
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
      type: String,
      required: true,
    },
    countries: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/trial/subscribeFreeHosting`,
    );
    return {
      http,
    };
  },

  data() {
    return {
      isLoading: false,
      subscribe: {
        noOfEmployee: this.noOfEmployees,
        companyName: this.companyName,
        contactNumber: this.contactNumber,
        email: this.email,
        contactPersonName: this.contactPersonName,
        countryCode: null,
      },
      rules: {
        email: [required, shouldNotExceedCharLength(50), validEmailFormat],
        contactPersonName: [required, shouldNotExceedCharLength(150)],
        companyName: [required, shouldNotExceedCharLength(150)],
        contactNumber: [shouldNotExceedCharLength(25), validPhoneNumberFormat],
        noOfEmployee: [digitsOnly],
      },
    };
  },
  methods: {
    onSubmit() {
      this.isLoading = true;
      this.http
        .request({
          method: 'POST',
          data: {
            ...this.subscribe,
          },
        })
        .then(() => {
          return this.$toast.addSuccess();
        })
        .then(() => {
          this.isLoading = false;
          reloadPage();
        });
    },

    onCancel() {
      reloadPage();
    },
  },
};
</script>

<style src="./subscribe-free-hosting.scss" lang="scss" scoped></style>
