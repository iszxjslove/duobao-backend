define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'projects/index' + location.search,
                    add_url: 'projects/add',
                    edit_url: 'projects/edit',
                    del_url: 'projects/del',
                    multi_url: 'projects/multi',
                    import_url: 'projects/import',
                    table: 'projects',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'game_id', title: __('Game_id')},
                        {field: 'issue', title: __('Issue')},
                        {field: 'issue_id', title: __('Issue_id')},
                        {field: 'code_type', title: __('Code_type')},
                        {field: 'code', title: __('Code')},
                        {field: 'singleprice', title: __('Singleprice'), operate:'BETWEEN'},
                        {field: 'multiple', title: __('Multiple')},
                        {field: 'totalprice', title: __('Totalprice'), operate:'BETWEEN'},
                        {field: 'maxbouns', title: __('Maxbouns'), operate:'BETWEEN'},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'deducttime', title: __('Deducttime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'bonustime', title: __('Bonustime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'isdeduct', title: __('Isdeduct')},
                        {field: 'isgetprize', title: __('Isgetprize')},
                        {field: 'prizestatus', title: __('Prizestatus')},
                        {field: 'userip', title: __('Userip')},
                        {field: 'cdnip', title: __('Cdnip')},
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