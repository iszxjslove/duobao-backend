define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'selectpage'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mission/lists/index' + location.search,
                    add_url: 'mission/lists/add',
                    edit_url: 'mission/lists/edit',
                    del_url: 'mission/lists/del',
                    multi_url: 'mission/lists/multi',
                    import_url: 'mission/lists/import',
                    table: 'mission',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'admin.username', title: __('管理员')},
                        {field: 'title', title: __('Title')},
                        {field: 'desc', title: __('Desc')},
                        {field: 'amount_limit', title: __('Amount_limit')},
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'release_time', title: __('Release_time'), operate: 'RANGE', addclass: 'datetimerange'},
                        {field: 'start_time', title: __('Start_time'), operate: 'RANGE', addclass: 'datetimerange'},
                        {field: 'end_time', title: __('End_time'), operate: 'RANGE', addclass: 'datetimerange'},
                        {
                            field: 'status', title: '状态', searchList: statusList, formatter: Table.api.formatter.label
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
            Controller.api.initMissionForm()
        },
        api: {
            initMissionForm: function () {
                let data = {}
                try {
                    data = JSON.parse($('#c-config_json').val())
                } catch (e) {

                }
                $('#c-group_name').val(data.group_name)
                $('#c-title').val(data.title)
                $('#c-standard_conditions').val(data.standard_conditions)
                $('#c-method').val(data.method)
                let cCycle = $('#c-times_cycle')
                let cCycleFormGroup = cCycle.closest('.form-group')
                let cCode = $('#c-times_code')
                let cCodeFormGroup = cCode.closest('.form-group')
                let standard_conditions = data.standard_conditions.split(',')
                let times_cycle = JSON.parse(data.times_cycle)
                cCycle.val(times_cycle.value)
                let times_code = JSON.parse(data.times_code)
                cCode.html('')
                $(times_code.options).each(function () {
                    let selected = cCode.data('value') === this.value ? 'selected' : ''
                    cCode.append('<option ' + selected + ' value="' + this.value + '">' + this.label + '</option>')
                })
                cCycleFormGroup.hide()
                cCodeFormGroup.hide()
                if (standard_conditions.indexOf('times') !== -1) {
                    if (times_cycle.visible) {
                        cCycleFormGroup.show()
                        cCycleFormGroup.find('.control-label').text(times_cycle.label)
                    }
                    if (times_code.visible) {
                        cCodeFormGroup.show()
                        cCodeFormGroup.find('.control-label').text(times_code.label)
                        if(typeof cCode.selectpicker === 'function'){
                            cCode.selectpicker("refresh");
                        }
                    }
                }

                let cTotal = $('#c-total');
                let cTotalFormGroup = cTotal.closest('.form-group')
                cTotalFormGroup.find('.control-label').text(data.total_field_title)
                cTotalFormGroup.hide()
                $('#c-total_field').val(data.total_field)
                if (standard_conditions.indexOf('total') !== -1) {
                    cTotalFormGroup.show()
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));

                $('#c-mission_config_id').selectPage({
                    data: 'mission/config',
                    showField: 'title',
                    keyField: 'id',
                    params: {"selectpageFields": "*"},
                    eSelect: function (data) {
                        $('#c-config_json').text(JSON.stringify(data))
                        Controller.api.initMissionForm()
                    },
                    eAjaxSuccess: function (data) {
                        data.totalRow = typeof data.total !== 'undefined' ? data.total : (typeof data.totalRow !== 'undefined' ? data.totalRow : data.list.length);
                        return data;
                    }
                });
            }
        }
    };
    return Controller;
});