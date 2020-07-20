define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'issue/index' + location.search,
                    add_url: 'issue/add',
                    edit_url: 'issue/edit',
                    del_url: 'issue/del',
                    multi_url: 'issue/multi',
                    import_url: 'issue/import',
                    table: 'game_issue',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'iid',
                sortName: 'iid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'iid', title: __('Iid')},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'code', title: __('Code')},
                        {field: 'issue', title: __('Issue')},
                        {field: 'belongdate', title: __('Belongdate'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'salestart', title: __('Salestart')},
                        {field: 'saleend', title: __('Saleend')},
                        {field: 'earliestwritetime', title: __('Earliestwritetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'writetime', title: __('Writetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'writeid', title: __('Writeid')},
                        {field: 'verifytime', title: __('Verifytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'verifyid', title: __('Verifyid')},
                        {field: 'status', title: __('Status')},
                        {field: 'statusdeduct', title: __('Statusdeduct')},
                        {field: 'statuscheckbonus', title: __('Statuscheckbonus')},
                        {field: 'statusbonus', title: __('Statusbonus')},
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