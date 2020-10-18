define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/admin/index',
                    add_url: 'auth/admin/add',
                    edit_url: 'auth/admin/edit',
                    del_url: 'auth/admin/del',
                    multi_url: 'auth/admin/multi',
                }
            });

            var table = $("#table");

            //在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, json) {
                $("tbody tr[data-index]", this).each(function () {
                    if (parseInt($("td:eq(1)", this).text()) == Config.admin.id) {
                        $("input[type=checkbox]", this).prop("disabled", true);
                    }
                });
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: 'ID'},
                        {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('Nickname')},
                        {
                            field: 'groups_text',
                            title: __('Group'),
                            operate: false,
                            formatter: Table.api.formatter.label
                        },
                        {field: 'money', title: __('Money')},
                        {field: 'status', title: __("Status"), formatter: Table.api.formatter.status},
                        {
                            field: 'logintime',
                            title: __('Login time'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                if (row.id == Config.admin.id) {
                                    return '';
                                }
                                return Table.api.formatter.operate.call(this, value, row, index);
                            },
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('变动余额'),
                                    title: __('变动余额'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'auth/admin/money'
                                },
                                {
                                    name: 'builduser',
                                    text: __('绑定前台账号'),
                                    title: __('绑定前台账号'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    url: 'auth/admin/builduser',
                                    click: function (btn, row) {
                                        layer.prompt({title: '输入前台账号'}, function (value, index) {
                                            Fast.api.ajax({
                                                url: 'auth/admin/builduser',
                                                data: {ids: row.id, account: value}
                                            }, function () {
                                                table.bootstrapTable('refresh')
                                                layer.close(index);
                                            })
                                        });
                                    },
                                    visible: function (row) {
                                        return !row.frontend_user_id
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
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        money: function () {
            Form.api.bindevent($("form[role=form]"));
            $(document).on('change', 'input[name=money]', function () {
                let changeMoney = $('#change-money'), money = changeMoney.data('money')
                money = money ? parseFloat(money) : 0;
                let value = parseFloat($(this).val())
                let after = money + value
                let color = after > 0 ? 'green' : 'red'
                changeMoney.html("(" + after + ")").css({color})
            })
        }
    };
    return Controller;
});