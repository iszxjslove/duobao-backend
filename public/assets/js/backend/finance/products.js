define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/products/index' + location.search,
                    add_url: 'finance/products/add',
                    edit_url: 'finance/products/edit',
                    del_url: 'finance/products/del',
                    multi_url: 'finance/products/multi',
                    import_url: 'finance/products/import',
                    table: 'financial_products',
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
                        {
                            field: 'rate', title: __('Rate'), formatter: function (val) {
                                return val + ' %'
                            }
                        },
                        {field: 'desc', title: __('Desc')},
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
            changeType: function () {
                let periodInput = $('#c-period'), periodUnitInput = $('#c-period_unit'),
                    interestSettlementTimeInput = $('input[name="row[interest_settlement_time]"]')
                periodInput.closest('.form-group').hide();
                periodUnitInput.closest('.form-group').hide();
                interestSettlementTimeInput.closest('.form-group').hide();
                if ($('input[name="row[type]"]:checked').val() === 'regular') {
                    periodInput.closest('.form-group').show();
                    periodUnitInput.closest('.form-group').show();
                    interestSettlementTimeInput.closest('.form-group').show();
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                this.changeType();
                $(document).on('change', 'input[name="row[type]"]', function () {
                    Controller.api.changeType()
                })
                $.validator.config({
                    rules: {
                        isRegular: function () {
                            return $('input[name="row[type]"]:checked').val() === 'regular'
                        }
                    }
                })
            }
        }
    };
    return Controller;
});