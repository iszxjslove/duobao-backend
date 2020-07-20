define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'game/index' + location.search,
                    add_url: 'game/add',
                    edit_url: 'game/edit',
                    del_url: 'game/del',
                    multi_url: 'game/multi',
                    import_url: 'game/import',
                    table: 'game',
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
                        {field: 'name', title: __('Name')},
                        {field: 'title', title: __('Title')},
                        {field: 'cycle', title: __('Cycle')},
                        {field: 'daybreakstart', title: __('Daybreakstart')},
                        {field: 'daybreakend', title: __('Daybreakend')},
                        {field: 'yearlybreakstart', title: __('Yearlybreakstart'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'yearlybreakend', title: __('Yearlybreakend'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'moneys', title: __('Moneys')},
                        {field: 'max_hands', title: __('Max_hands')},
                        {field: 'green_ordinary', title: __('Green_ordinary')},
                        {field: 'green_ordinary_odds', title: __('Green_ordinary_odds'), operate:'BETWEEN'},
                        {field: 'green_lucky', title: __('Green_lucky')},
                        {field: 'green_lucky_odds', title: __('Green_lucky_odds'), operate:'BETWEEN'},
                        {field: 'red_ordinary', title: __('Red_ordinary')},
                        {field: 'red_ordinary_odds', title: __('Red_ordinary_odds'), operate:'BETWEEN'},
                        {field: 'red_lucky', title: __('Red_lucky')},
                        {field: 'red_lucky_odds', title: __('Red_lucky_odds'), operate:'BETWEEN'},
                        {field: 'violet', title: __('Violet')},
                        {field: 'violet_odds', title: __('Violet_odds'), operate:'BETWEEN'},
                        {field: 'singular', title: __('Singular')},
                        {field: 'singular_odds', title: __('Singular_odds'), operate:'BETWEEN'},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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