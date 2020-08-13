define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mission/config/index' + location.search,
                    add_url: 'mission/config/add',
                    edit_url: 'mission/config/edit',
                    del_url: 'mission/config/del',
                    multi_url: 'mission/config/multi',
                    import_url: 'mission/config/import',
                    table: 'mission_config',
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
                        {field: 'group_name', title: __('任务组名称')},
                        {field: 'mission_name', title: __('任务名称')},
                        {field: 'title', title: __('Title')},
                        {
                            field: 'method',
                            title: __('统计对象'),
                            searchList: {"private": __('自己'), "parent": __('下级')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'times', title: __('统计次数')},
                        {field: 'times_label', title: __('统计次数标题')},
                        {field: 'total', title: __('合计')},
                        {field: 'total_field', title: __('合计字段')},
                        {field: 'total_field_title', title: __('合计字段标题')},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"normal": __('Normal'), "hidden": __('Hidden')},
                            formatter: Table.api.formatter.status
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
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                $.validator.config({
                    rules: {
                        isRequired: function (e) {
                            return $('#' + $(e).data('relation-id')).val() !== '-1'
                        }
                    }
                })
                let missionTypes = {
                    register: {
                        label: '注册', children: [
                            {name: 'register', label: '普通注册'}
                        ]
                    },
                    login: {
                        label: '登录', children: [
                            {name: 'first_login', label: '首次登录'},
                            {name: 'login', label: '普通登录'}
                        ]
                    }
                }
                $(document).on('change', '#c-group_name', function () {
                    console.log($(this).val())
                    console.log(missionTypes[$(this).val()])
                    let option = ''
                    let cMissionName = $('#c-mission_name');
                    $.each(missionTypes[$(this).val()].children, function (i, el) {
                        let selected = cMissionName.data("value") === i ? 'selected' : ''
                        option += '<option value="' + el.name + '" ' + selected + '>' + el.label + '</option>'
                    })
                    cMissionName.html(option);
                    if (typeof cMissionName.selectpicker === 'function') {
                        cMissionName.selectpicker("refresh");
                    }
                })
                let cGroupName = $('#c-group_name')
                $.each(missionTypes, function (i, el) {
                    let selected = cGroupName.data("value") === i ? 'selected' : ''
                    cGroupName.append('<option value="' + i + '" ' + selected + '>' + el.label + '</option>')
                })
                cGroupName.trigger('change')
            }
        }
    };
    return Controller;
});