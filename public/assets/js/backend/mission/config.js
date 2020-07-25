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
                        {field: 'id', title: __('Id')},
                        {field: 'group_name', title: __('Group_name')},
                        {field: 'name', title: __('Name')},
                        {field: 'title', title: __('Title')},
                        {field: 'times_cycle', title: __('Times_cycle')},
                        {field: 'times_code', title: __('Times_code')},
                        {field: 'total_field', title: __('Total_field')},
                        {field: 'total_field_title', title: __('Total_field_title')},
                        {field: 'standard_conditions', title: __('Standard_conditions'), searchList: {"times":__('Times'),"total":__('Total')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label},
                        {field: 'method', title: __('Method'), searchList: {"private":__('Private'),"parent":__('Parent')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            }
        }
    };
    return Controller;
});