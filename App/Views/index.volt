{{ form('module-zabbix-agent5/save', 'role': 'form', 'class': 'ui form large', 'id':'module-zabbix-agent5-form') }}
    {{ form.render('id') }}
    {{ form.render('configContent') }}
    <div class="field">
        <label>{{ t._('modzbx_ConfigFileLabel') }}</label>
        <div id="user-edit-config" class="application-code"></div>
    </div>

    <div class="ui hidden divider"></div>
{{ partial("partials/submitbutton",['indexurl':'pbx-extension-modules/index/']) }}
{{ end_form() }}
