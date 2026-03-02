<form method="post" action="module-zabbix-agent5/save" role="form" class="ui form large" id="module-zabbix-agent5-form">
    {{ form.render('id') }}
    {{ form.render('configContent') }}

    <div id="zabbix-agent-status" class="ui icon message">
        <i class="spinner loading icon" id="zabbix-status-icon"></i>
        <div class="content">
            <div class="header">{{ t._('modzbx_ServiceStatus') }}</div>
            <table class="ui very basic compact table" id="zabbix-status-table">
                <tbody>
                    <tr>
                        <td class="four wide">{{ t._('modzbx_StatusRunning') }}</td>
                        <td><span id="zabbix-status-label" class="ui mini label grey">...</span></td>
                    </tr>
                    <tr>
                        <td>{{ t._('modzbx_PidLabel') }}</td>
                        <td><span id="zabbix-status-pid">—</span></td>
                    </tr>
                    <tr>
                        <td>{{ t._('modzbx_VersionLabel') }}</td>
                        <td><span id="zabbix-status-version">—</span></td>
                    </tr>
                    <tr>
                        <td>{{ t._('modzbx_ListenPortLabel') }}</td>
                        <td><span id="zabbix-status-port">—</span></td>
                    </tr>
                    <tr>
                        <td>{{ t._('modzbx_ServerLabel') }}</td>
                        <td><span id="zabbix-status-server">—</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="ui hidden divider"></div>

    <button class="ui labeled icon basic blue button" type="button" id="download-zabbix-template">
        <i class="download icon"></i>
        {{ t._('modzbx_DownloadTemplate') }}
    </button>

    <div class="ui hidden divider"></div>

    <div class="field">
        <label>{{ t._('modzbx_ConfigFileLabel') }}</label>
        <div id="user-edit-config" class="application-code"></div>
    </div>

    <div class="ui hidden divider"></div>
{{ partial("partials/submitbutton",['indexurl':'pbx-extension-modules/index/']) }}
</form>