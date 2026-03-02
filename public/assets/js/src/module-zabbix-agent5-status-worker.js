/* global globalRootUrl, globalTranslate */

const ZabbixAgentStatusWorker = {
	timeOut: 10000,
	timeOutHandle: null,
	$statusIcon: $('#zabbix-status-icon'),
	$statusLabel: $('#zabbix-status-label'),
	$statusPid: $('#zabbix-status-pid'),
	$statusVersion: $('#zabbix-status-version'),
	$statusPort: $('#zabbix-status-port'),
	$statusServer: $('#zabbix-status-server'),
	$statusMessage: $('#zabbix-agent-status'),

	initialize() {
		ZabbixAgentStatusWorker.restartWorker();
	},

	restartWorker() {
		window.clearTimeout(ZabbixAgentStatusWorker.timeOutHandle);
		ZabbixAgentStatusWorker.worker();
	},

	worker() {
		$.api({
			url: `${globalRootUrl}pbxcore/api/v3/module-zabbix-agent5/status:getStatus`,
			on: 'now',
			successTest(response) {
				return response !== undefined && response.result === true;
			},
			onSuccess(response) {
				ZabbixAgentStatusWorker.updateStatus(response.data);
				ZabbixAgentStatusWorker.timeOutHandle = window.setTimeout(
					ZabbixAgentStatusWorker.worker,
					ZabbixAgentStatusWorker.timeOut,
				);
			},
			onFailure() {
				ZabbixAgentStatusWorker.showUnknown();
				ZabbixAgentStatusWorker.timeOutHandle = window.setTimeout(
					ZabbixAgentStatusWorker.worker,
					ZabbixAgentStatusWorker.timeOut,
				);
			},
		});
	},

	updateStatus(data) {
		ZabbixAgentStatusWorker.$statusIcon.removeClass('spinner loading');

		if (data.running) {
			ZabbixAgentStatusWorker.$statusIcon.addClass('check circle').removeClass('exclamation circle');
			ZabbixAgentStatusWorker.$statusMessage.removeClass('negative').addClass('positive');
			ZabbixAgentStatusWorker.$statusLabel
				.text(globalTranslate.modzbx_StatusRunning)
				.removeClass('grey red')
				.addClass('green');
			ZabbixAgentStatusWorker.$statusPid.text(data.pid || '—');
			ZabbixAgentStatusWorker.$statusVersion.text(data.version || '—');
		} else {
			ZabbixAgentStatusWorker.$statusIcon.addClass('exclamation circle').removeClass('check circle');
			ZabbixAgentStatusWorker.$statusMessage.removeClass('positive').addClass('negative');
			ZabbixAgentStatusWorker.$statusLabel
				.text(globalTranslate.modzbx_StatusStopped)
				.removeClass('grey green')
				.addClass('red');
			ZabbixAgentStatusWorker.$statusPid.text('—');
			ZabbixAgentStatusWorker.$statusVersion.text('—');
		}

		ZabbixAgentStatusWorker.$statusPort.text(data.listenPort || '—');
		ZabbixAgentStatusWorker.$statusServer.text(data.server || '—');
	},

	showUnknown() {
		ZabbixAgentStatusWorker.$statusIcon
			.removeClass('spinner loading check circle')
			.addClass('exclamation circle');
		ZabbixAgentStatusWorker.$statusMessage.removeClass('positive').addClass('negative');
		ZabbixAgentStatusWorker.$statusLabel
			.text('?')
			.removeClass('green')
			.addClass('grey');
	},
};

$(document).ready(() => {
	ZabbixAgentStatusWorker.initialize();
});
