/*
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 11 2018
 *
 */

/* global globalRootUrl, globalTranslate, Form, Config */

const ModuleZabbixAgent5 = {
	$formObj: $('#module-zabbix-agent5-form'),
	$checkBoxes: $('#module-zabbix-agent5-form .ui.checkbox'),
	$dropDowns: $('#module-zabbix-agent5-form .ui.dropdown'),
	$disabilityFields: $('#module-zabbix-agent5-form  .disability'),
	$statusToggle: $('#module-status-toggle'),
	$moduleStatus: $('#status'),
	/**
	 * Field validation rules
	 * https://semantic-ui.com/behaviors/form.html
	 */
	validateRules: {
		textField: {
			identifier: 'text_field',
			rules: [
				{
					type: 'empty',
					prompt: globalTranslate.mod_tplValidateValueIsEmpty,
				},
			],
		},
		areaField: {
			identifier: 'text_area_field',
			rules: [
				{
					type: 'empty',
					prompt: globalTranslate.mod_tplValidateValueIsEmpty,
				},
			],
		},
		passwordField: {
			identifier: 'password_field',
			rules: [
				{
					type: 'empty',
					prompt: globalTranslate.mod_tplValidateValueIsEmpty,
				},
			],
		},
	},
	/**
	 * On page load we init some Semantic UI library
	 */
	initialize() {
		// инициализируем чекбоксы и выподающие менюшки
		ModuleZabbixAgent5.$checkBoxes.checkbox();
		ModuleZabbixAgent5.$dropDowns.dropdown();
		ModuleZabbixAgent5.checkStatusToggle();
		window.addEventListener('ModuleStatusChanged', ModuleZabbixAgent5.checkStatusToggle);
		ModuleZabbixAgent5.initializeForm();
	},
	/**
	 * Change some form elements classes depends of module status
	 */
	checkStatusToggle() {
		if (ModuleZabbixAgent5.$statusToggle.checkbox('is checked')) {
			ModuleZabbixAgent5.$disabilityFields.removeClass('disabled');
			ModuleZabbixAgent5.$moduleStatus.show();
		} else {
			ModuleZabbixAgent5.$disabilityFields.addClass('disabled');
			ModuleZabbixAgent5.$moduleStatus.hide();
		}
	},
	/**
	 * Send command to restart module workers after data changes,
	 * Also we can do it on TemplateConf->modelsEventChangeData method
	 */
	applyConfigurationChanges() {
		ModuleZabbixAgent5.changeStatus('Updating');
		$.api({
			url: `${Config.pbxUrl}/pbxcore/api/modules/ModuleZabbixAgent5/reload`,
			on: 'now',
			successTest(response) {
				// test whether a JSON response is valid
				return Object.keys(response).length > 0 && response.result === true;
			},
			onSuccess() {
				ModuleZabbixAgent5.changeStatus('Connected');
			},
			onFailure() {
				ModuleZabbixAgent5.changeStatus('Disconnected');
			},
		});
	},
	/**
	 * We can modify some data before form send
	 * @param settings
	 * @returns {*}
	 */
	cbBeforeSendForm(settings) {
		const result = settings;
		result.data = ModuleZabbixAgent5.$formObj.form('get values');
		return result;
	},
	/**
	 * Some actions after forms send
	 */
	cbAfterSendForm() {
		ModuleZabbixAgent5.applyConfigurationChanges();
	},
	/**
	 * Initialize form parameters
	 */
	initializeForm() {
		Form.$formObj = ModuleZabbixAgent5.$formObj;
		Form.url = `${globalRootUrl}module-zabbix-agent5/save`;
		Form.validateRules = ModuleZabbixAgent5.validateRules;
		Form.cbBeforeSendForm = ModuleZabbixAgent5.cbBeforeSendForm;
		Form.cbAfterSendForm = ModuleZabbixAgent5.cbAfterSendForm;
		Form.initialize();
	},
	/**
	 * Update the module state on form label
	 * @param status
	 */
	changeStatus(status) {
		switch (status) {
			case 'Connected':
				ModuleZabbixAgent5.$moduleStatus
					.removeClass('grey')
					.removeClass('red')
					.addClass('green');
				ModuleZabbixAgent5.$moduleStatus.html(globalTranslate.module_zabbix_agent5Connected);
				break;
			case 'Disconnected':
				ModuleZabbixAgent5.$moduleStatus
					.removeClass('green')
					.removeClass('red')
					.addClass('grey');
				ModuleZabbixAgent5.$moduleStatus.html(globalTranslate.module_zabbix_agent5Disconnected);
				break;
			case 'Updating':
				ModuleZabbixAgent5.$moduleStatus
					.removeClass('green')
					.removeClass('red')
					.addClass('grey');
				ModuleZabbixAgent5.$moduleStatus.html(`<i class="spinner loading icon"></i>${globalTranslate.module_zabbix_agent5UpdateStatus}`);
				break;
			default:
				ModuleZabbixAgent5.$moduleStatus
					.removeClass('green')
					.removeClass('red')
					.addClass('grey');
				ModuleZabbixAgent5.$moduleStatus.html(globalTranslate.module_zabbix_agent5Disconnected);
				break;
		}
	},
};

$(document).ready(() => {
	ModuleZabbixAgent5.initialize();
});

