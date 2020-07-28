define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/order/index' + location.search,
                    table: 'user_finance_order',
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
                        {field: 'user.username', title: __('账户')},
                        {field: 'trade_no', title: __('Trade_no')},
                        {
                            field: 'type',
                            title: __('Type'),
                            searchList: {'regular': __('Regular'), 'current': __('Current')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'title', title: __('Title')},
                        {
                            field: 'period_unit',
                            title: __('周期'),
                            operate: false,
                            formatter: function (value, row) {
                                if (row.type === 'regular') {
                                    return row.period + ' ' + __(row.period_unit)
                                }
                            }
                        },
                        {field: 'rate', title: __('Rate'), operate: 'BETWEEN'},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {'1': __('Start'), '2': __('End')},
                            formatter: Table.api.formatter.normal
                        },
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