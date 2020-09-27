define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdraw/order/index' + location.search,
                    table: 'withdraw_order',
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
                        {field: 'user.username', title: __('用户名')},
                        {field: 'admin.username', title: __('管理员')},
                        {field: 'trade_no', title: __('Trade_no')},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'fee', title: __('Fee'), operate: 'BETWEEN'},
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {'0': __('Wait'), '1': __('Successful'), '-1': __('Fail')},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'adopt',
                                    text: __('通过'),
                                    title: __('审核通过'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'withdraw/order/adopt',
                                    refresh: true,
                                    confirm: '审核通该提现订单',
                                    visible: function (row) {
                                        return !row.status
                                    }
                                },
                                {
                                    name: 'reject',
                                    text: __('驳回'),
                                    title: __('审核驳回'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    url: 'withdraw/order/reject',
                                    refresh: true,
                                    confirm: '驳回该提现订单，资金将原路返回',
                                    visible: function (row) {
                                        return !row.status
                                    }
                                }
                            ]
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
            }
        }
    };
    return Controller;
});