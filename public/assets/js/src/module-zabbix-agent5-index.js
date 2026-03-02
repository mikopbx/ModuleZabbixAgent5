/* global globalRootUrl, globalTranslate, Form, ace, Config, TokenManager */

const ModuleZabbixAgent5 = {
	$formObj: $('#module-zabbix-agent5-form'),
	$disabilityFields: $('#module-zabbix-agent5-form  .disability'),
	$statusToggle: $('#module-status-toggle'),
	$moduleStatus: $('#status'),
	// Ace editor instance
	editor: '',
	// Form validation rules
	validateRules: {},
	$downloadBtn: $('#download-zabbix-template'),
	/**
	 * On page load we init some Semantic UI library
	 */
	initialize() {
		ModuleZabbixAgent5.checkStatusToggle();
		window.addEventListener('ModuleStatusChanged', ModuleZabbixAgent5.checkStatusToggle);
		ModuleZabbixAgent5.initializeForm();
		ModuleZabbixAgent5.initializeAce();
		ModuleZabbixAgent5.$downloadBtn.on('click', ModuleZabbixAgent5.downloadTemplate);
	},

	/**
	 * Checks the status toggle and updates the disability fields.
	 */
	checkStatusToggle() {
		if (ModuleZabbixAgent5.$statusToggle.checkbox('is checked')) {
			ModuleZabbixAgent5.$disabilityFields.removeClass('disabled');
		} else {
			ModuleZabbixAgent5.$disabilityFields.addClass('disabled');
		}
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
		ModuleZabbixAgent5.editor = ace.edit('user-edit-config');
		ModuleZabbixAgent5.editor.getSession().setValue(configFileText);
		let NewMode = ace.require('ace/mode/julia').Mode;
		ModuleZabbixAgent5.editor.session.setMode(new NewMode());
		ModuleZabbixAgent5.editor.setTheme('ace/theme/monokai');
		ModuleZabbixAgent5.editor.resize();
		ModuleZabbixAgent5.editor.getSession().on('change', () => {
			// Trigger change event to acknowledge the modification
			Form.dataChanged();
		});

		ModuleZabbixAgent5.editor.setOptions({
			maxLines: rowsCount,
			showPrintMargin: false,
			showLineNumbers: false,
		});
	},
	/**
	 * Downloads the Zabbix template YAML file via fetch with Bearer token.
	 */
	downloadTemplate() {
		const downloadUrl = `${Config.pbxUrl}/pbxcore/api/v3/module-zabbix-agent5/status:downloadTemplate`;
		const headers = {
			'X-Requested-With': 'XMLHttpRequest',
		};
		if (typeof TokenManager !== 'undefined' && TokenManager.accessToken) {
			headers['Authorization'] = `Bearer ${TokenManager.accessToken}`;
		}

		fetch(downloadUrl, { headers })
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP ${response.status}: ${response.statusText}`);
				}
				let filename = 'zbx_mikopbx_template.yaml';
				const disposition = response.headers.get('Content-Disposition');
				if (disposition) {
					const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
					if (match && match[1]) {
						filename = match[1].replace(/['"]/g, '');
					}
				}
				return response.blob().then(blob => ({ blob, filename }));
			})
			.then(({ blob, filename }) => {
				const blobUrl = URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = blobUrl;
				a.download = filename;
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
				setTimeout(() => URL.revokeObjectURL(blobUrl), 100);
			})
			.catch(() => {});
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

