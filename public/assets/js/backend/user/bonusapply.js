define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/bonusapply/index' + location.search,
                    agree_url: 'user/bonusapply/agree',
                    refuse_url: 'user/bonusapply/refuse',
                    table: 'team_bonus_apply',
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
                        {field: 'user_id', title: __('用户ID')},
                        {field: 'user.username', title: __('用户名')},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'update_time',
                            title: __('Update_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'admin_id', title: __('Admin_id')},
                        {
                            field: 'check_time',
                            title: __('Check_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {'-1': '拒绝', '0': '待审核', '1': '通过'},
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
                                    name: 'agree',
                                    text: '通过',
                                    title: '通过',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    refresh: true,
                                    url: 'user/bonusapply/agree',
                                    confirm: '确认通过',
                                    visible: function (row){
                                        return row.status === 0
                                    }
                                },
                                {
                                    name: 'refuse',
                                    text: '拒绝',
                                    title: '拒绝',
                                    classname: 'btn btn-xs btn-danger btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    refresh: true,
                                    url: 'user/bonusapply/refuse',
                                    confirm: '确认拒绝',
                                    visible: function (row){
                                        return row.status === 0
                                    }
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $(document).on('click', '.btn-agree,.btn-refuse', function () {
                let url = ''
                if ($(this).hasClass('btn-agree')) {
                    url = 'user/bonusapply/agree?ids='
                }
                if ($(this).hasClass('btn-refuse')) {
                    url = 'user/bonusapply/refuse?ids='
                }
                if (url) {
                    let selected = table.bootstrapTable('getSelections')
                    if (selected) {
                        let ids = selected.map(v => {
                            return v.id
                        })
                        Fast.api.ajax({
                            url: url + ids.join(',')
                        }, function () {
                            table.bootstrapTable('refresh')
                        })
                    }
                }
            })
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