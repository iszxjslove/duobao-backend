define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'red_envelopes/index' + location.search,
                    add_url: 'red_envelopes/add',
                    recovery_url: 'red_envelopes/recovery',
                    table: 'red_envelopes',
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
                        {
                            field: 'cate',
                            title: __('Cate'),
                            searchList: {"lucky": __('Lucky'), "fixed": __('Fixed')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'number', title: __('Number')},
                        {field: 'total_amount', title: __('Total_amount'), operate: 'BETWEEN'},
                        {field: 'title', title: __('Title'), visible: false},
                        {field: 'cover', title: __('Cover'), visible: false},
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'expiry_time',
                            title: __('Expiry_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'claim_status', title: __('Claim_status'), formatter: function (value, row) {
                                if (row.return_status === 1) {
                                    return __('return_status_' + row.return_status)
                                }
                                return __('claim_status_' + value)
                            }
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.buttons,
                            formatter: Table.api.formatter.buttons,
                            buttons: [
                                {
                                    name: 'recovery',
                                    text: '回收红包',
                                    classname: 'btn btn-xs btn-danger btn-click',
                                    click: function (cell, row) {
                                        layer.confirm('确认回收红包？', function (index) {
                                            var url = $.fn.bootstrapTable.defaults.extend.recovery_url + '?ids=' + row.id;
                                            Fast.api.ajax(url, function () {
                                                Layer.closeAll();
                                                table.bootstrapTable('refresh');
                                            }, function () {
                                                Layer.closeAll();
                                            });
                                        })
                                    },
                                    visible: function (row) {
                                        return row.return_status === 0 && row.claim_status !== 2
                                    }
                                },
                                {
                                    name: 'details',
                                    text: '红包详情',
                                    title: '红包详情',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'red_envelopes/details'
                                },
                            ],
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
        details: function () {
            // 初始化表格
            let claimTable = $('#claimTable')
            claimTable.bootstrapTable({
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                search:false,
                columns: [
                    [
                        {
                            field: 'user.username', title: __('用户'), formatter: function (value, row) {
                                return row.user.nickname + '(' + row.user.username + ')';
                            }
                        },
                        {
                            field: 'get_amount',
                            title: __('领取金额'),
                        },
                        {field: 'create_time_text', title: __('领取时间')},
                    ]
                ],
            });

            // 为表格绑定事件
            Table.api.bindevent(claimTable);
            claimTable.bootstrapTable('load', logTableData)
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));

                $(document).on('change', '#c-cate,#c-amount,#c-number', function () {
                    let cate = $('#c-cate'), amount = $('#c-amount'), number = $('#c-number'), total_amount = 0;
                    switch (cate.val()) {
                        case 'lucky':
                            total_amount = (amount.val() * 1).toFixed(2)
                            break
                        case 'fixed':
                            total_amount = (amount.val() * number.val()).toFixed(2)
                            break
                    }
                    console.log(total_amount)
                    $('#total_amount').text(total_amount)
                })
            }
        }
    };
    return Controller;
});