define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article/category/index' + location.search,
                    add_url: 'article/category/add',
                    edit_url: 'article/category/edit',
                    del_url: 'article/category/del',
                    multi_url: 'article/category/multi',
                    import_url: 'article/category/import',
                    table: 'article',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                escape: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'name', title: '分类名称', align: 'left'},
                        {
                            field: 'flag',
                            title: __('Flag'),
                            searchList: {"hot": __('Hot'), "index": __('Index'), "recommend": __('Recommend')},
                            operate: 'FIND_IN_SET',
                            formatter: Table.api.formatter.label
                        },
                        {
                            field: 'image',
                            title: __('Image'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'weigh', title: __('Weigh'), operate: false},
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
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});