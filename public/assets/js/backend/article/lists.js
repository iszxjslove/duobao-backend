define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article/lists/index' + location.search,
                    add_url: 'article/lists/add',
                    edit_url: 'article/lists/edit',
                    del_url: 'article/lists/del',
                    multi_url: 'article/lists/multi',
                    import_url: 'article/lists/import',
                    table: 'article',
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
                        {field: 'category.name', title: __('Category_id'), formatter: Table.api.formatter.label},
                        {
                            field: 'type',
                            title: __('Type'),
                            searchList: {"common": __('Common'), "system": __('System')},
                            formatter: Table.api.formatter.label,
                            custom: {system: 'danger', common: 'info'}
                        },
                        {field: 'name', title: __('Name'), visible: false},
                        {
                            field: 'thumbnail',
                            title: __('Thumbnail'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'subtitle', title: __('Subtitle')},
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

            //给添加按钮添加`data-area`属性
            $(".btn-add").data("area", ["100%", "100%"]);
            //当内容渲染完成给编辑按钮添加`data-area`属性
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone").data("area", ["100%", "100%"]);
            });

            $('input[name=type]', 'form.form-commonsearch').closest('.form-group').hide();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                var roleForm = $("form[role=form]"), cType = $('#c-type', roleForm);
                Form.api.bindevent(roleForm);

                $.validator.config({
                    rules: {
                        isSystem: function () {
                            return cType.val() === 'system'
                        }
                    }
                })

                cType.change(function () {
                    let type = $(this).val();
                    if (type === 'system') {
                        $('input[name="row[name]"]', roleForm).closest('.form-group').show();
                    } else {
                        $('input[name="row[name]"]', roleForm).closest('.form-group').hide();
                    }
                })
                cType.trigger('change')
            }
        }
    };
    return Controller;
});