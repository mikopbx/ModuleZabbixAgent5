<form class="ui large grey segment form" id="module-zabbix-agent5-form">
    <div class="ui ribbon label">
        <i class="phone icon"></i> 123456
    </div>
    <div class="ui grey top right attached label" id="status">{{ t._("module_zabbix_agent5Disconnected") }}</div>
    {{ form.render('id') }}

    <div class="ten wide field disability">
        <label >{{ t._('module_zabbix_agent5TextFieldLabel') }}</label>
        {{ form.render('text_field') }}
    </div>

    <div class="ten wide field disability">
        <label >{{ t._('module_zabbix_agent5TextAreaFieldLabel') }}</label>
        {{ form.render('text_area_field') }}
    </div>

    <div class="ten wide field disability">
        <label >{{ t._('module_zabbix_agent5PasswordFieldLabel') }}</label>
        {{ form.render('password_field') }}
    </div>

    <div class="four wide field disability">
        <label>{{ t._('module_zabbix_agent5IntegerFieldLabel') }}</label>
        {{ form.render('integer_field') }}
    </div>

    <div class="field disability">
        <div class="ui segment">
            <div class="ui checkbox">
                <label>{{ t._('module_zabbix_agent5CheckBoxFieldLabel') }}</label>
                {{ form.render('checkbox_field') }}
            </div>
        </div>
    </div>

    <div class="field disability">
        <div class="ui segment">
            <div class="ui toggle checkbox">
                <label>{{ t._('module_zabbix_agent5ToggleFieldLabel') }}</label>
                {{ form.render('toggle_field') }}
            </div>
        </div>
    </div>

    <div class="ten wide field disability">
        <label >{{ t._('module_zabbix_agent5DropDownFieldLabel') }}</label>
        {{ form.render('dropdown_field') }}
    </div>

    {{ partial("partials/submitbutton",['indexurl':'pbx-extension-modules/index/']) }}
</form>
