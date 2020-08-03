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

            let day1 = new Date();
            day1.setMinutes(day1.getMinutes() - 15);
            let time1 = day1.format("yyyy-MM-dd hh:mm:ss");

            let day2 = new Date();
            day2.setDate(day2.getDate() + 1);
            let time2 = day2.format("yyyy-MM-dd hh:mm:ss");

            console.log(time1,time2);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'saleend',
                sortOrder: 'asc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'issue', title: __('Issue')},
                        {field: 'code', title: __('Code')},
                        {
                            field: 'saleend',
                            addclass: 'datetimerange',
                            operate: 'RANGE',
                            defaultValue: time1 + ' - ' + time2,
                            title: __('销售结束时间'),
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'verifytime_text',
                            title: __('开奖时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.buttons,
                            buttons: [
                                {
                                    name: 'set_code',
                                    text: __('预设号码'),
                                    title: __('预设号码'),
                                    classname: 'btn btn-xs btn-success btn-click',
                                    click: function (e, row) {
                                        let index = layer.open({
                                            area: ['500px', 'auto'],
                                            content: Template('setCodeTpl', row),
                                            btn: ['确定', '取消'],
                                            yes: function () {
                                                $('#set-code-form').submit()
                                            },
                                            success: function () {
                                                Form.api.bindevent($('#set-code-form'), function (ret) {
                                                    table.bootstrapTable('refresh');
                                                    layer.close(index)
                                                })
                                            }
                                        })
                                    },
                                    visible: function (row) {
                                        return row.statuscode === 0
                                    }
                                },
                                {
                                    name: 'clear_set',
                                    text: '取消预设',
                                    classname: 'btn btn-xs btn-warning btn-ajax',
                                    url: 'issue/clear_set',
                                    success: function () {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        return row.statuscode === 1
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