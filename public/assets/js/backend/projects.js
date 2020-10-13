define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'projects/index' + location.search,
                    table: 'projects',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'create_time',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'user.username', title: __('用户名')},
                        {
                            field: 'create_time',
                            title: __('时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'issue', title: __('Issue')},
                        {field: 'no_code', title: __('开奖号码')},
                        {
                            field: 'no_colors', title: __('开奖颜色'), formatter: function (value) {
                                if (value) {
                                    let h = ''
                                    $.each(value.split(','), function (i, el) {
                                        h += '<i class="fa fa-circle issue-color game-' + el + '"></i>'
                                    })
                                    return h
                                }
                            }, operate: false
                        },
                        {field: 'code', title: __('Code'), operate: false},
                        {field: 'singleprice', title: __('Singleprice'), operate: false},
                        {field: 'multiple', title: __('倍数'), operate: false},
                        {field: 'totalprice', title: __('投注总金额'), operate: false},
                        {field: 'bonus', title: __('中奖金额'), operate: false},
                        {
                            field: 'no_code',
                            title: __('开奖状态'),
                            searchList: {0: '未开奖', 1: '已开奖'},
                            custom:{0:'black',1:'green'},
                            formatter: function (value, row, index) {
                                value = value ? 1 : 0
                                return Table.api.formatter.status.call(this, value, row, index)
                            }
                        },
                        {
                            field: 'isgetprize',
                            title: __('中奖状态'),
                            searchList: {0: '未判断', 1: '中奖', 2: '未中奖'},
                            custom:{0:'black',1:'green',2:'gray'},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'prizestatus',
                            title: __('派奖状态'),
                            searchList: {0: '未派', 1: '已派'},
                            custom:{0:'gray',1:'green'},
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
            }
        }
    };
    return Controller;
});