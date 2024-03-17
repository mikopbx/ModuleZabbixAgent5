/* global globalRootUrl, globalTranslate, Form */

const ModuleZabbixAgent5 = {
	$formObj: $('#module-zabbix-agent5-form'),
	$disabilityFields: $('#module-zabbix-agent5-form  .disability'),
	$statusToggle: $('#module-status-toggle'),
	$moduleStatus: $('#status'),
	/**
	 * On page load we init some Semantic UI library
	 */
	initialize() {
		ModuleZabbixAgent5.checkStatusToggle();
		window.addEventListener('ModuleStatusChanged', ModuleZabbixAgent5.checkStatusToggle);
		ModuleZabbixAgent5.initializeForm();
		ModuleZabbixAgent5.initializeAce();
	},

	/**
	 * Initializes the Ace editor instance.
	 * Sets up Ace editor with a monokai theme and custom options.
	 * Attaches change handler to the editor session.
	 */
	initializeAce() {
		const configFileText = ModuleZabbixAgent5.$formObj.form('get value', 'configContent');
		const aceHeight = window.innerHeight - 380;
		const rowsCount = Math.round(aceHeight / 16.3);
		$(window).load(function () {
			$('.application-code').css('min-height', `${aceHeight}px`);
		});
		configFileText.editor = ace.edit('application-code');
		configFileText.editor.getSession().setValue(configFileText);
		configFileText.editor.setTheme('ace/theme/monokai');
		configFileText.editor.resize();
		configFileText.editor.getSession().on('change', () => {
			// Trigger change event to acknowledge the modification
			Form.dataChanged();
		});
		configFileText.editor.setOptions({
			maxLines: rowsCount,
			showPrintMargin: false,
			showLineNumbers: false,
		});
	},
	/**
	 * Callback function to be called before the form is sent
	 * @param {Object} settings - The current settings of the form
	 * @returns {Object} - The updated settings of the form
	 */
	cbBeforeSendForm(settings) {
		const result = settings;
		result.data = ModuleZabbixAgent5.$formObj.form('get values');
		result.data.configContent = ModuleZabbixAgent5.editor.getValue();
		return result;
	},
	/**
	 * Some actions after forms send
	 */
	cbAfterSendForm() {

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
};

$(document).ready(() => {
	ModuleZabbixAgent5.initialize();
});

