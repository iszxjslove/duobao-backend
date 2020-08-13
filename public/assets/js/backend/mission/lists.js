define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'selectpage'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mission/lists/index' + location.search,
                    add_url: 'mission/lists/add',
                    edit_url: 'mission/lists/edit',
                    del_url: 'mission/lists/del',
                    multi_url: 'mission/lists/multi',
                    import_url: 'mission/lists/import',
                    table: 'mission',
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
                        {field: 'title', title: __('Title')},
                        {field: 'desc', title: __('Desc')},
                        {field: 'amount_limit', title: __('Amount_limit')},
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'release_time',
                            title: __('Release_time'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange'
                        },
                        {
                            field: 'start_time',
                            title: __('Start_time'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange'
                        },
                        {
                            field: 'end_time',
                            title: __('End_time'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange'
                        },
                        {
                            field: 'status',
                            title: '状态',
                            searchList: tableConfig.fa_mission.status.value_to_labels,
                            custom: tableConfig.fa_mission.status.value_to_colors,
                            formatter: Table.api.formatter.label
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
                $(document).on('change', 'input[name="row[method]"]', function () {
                    let cLevel = $('#c-level')
                    if ($(this).val() === 'parent') {
                        cLevel.closest('.form-group').show()
                        cLevel.val(1)
                    } else {
                        cLevel.closest('.form-group').hide()
                        cLevel.val(0)
                    }
                })

                $(document).on('selectpage.bs.change', '#c-mission_config_id', function (e, data) {
                    let selected = data[$(this).val()];
                    if (selected) {
                        if (selected.method === 'optional') {
                            $('input[name="row[method]"]').closest('.form-group').show();
                        } else {
                            $('input[name="row[method]"]').closest('.form-group').hide();
                            $('input[name="row[method]"][value="' + selected.method + '"]').prop('checked', true).trigger('change');
                        }

                        let cTimes = $('#c-times');
                        if (selected.times_label) {
                            cTimes.closest('.form-group').find('.control-label').text(selected.times_label)
                        }
                        if (selected.times === -1) {
                            cTimes.closest('.form-group').hide()
                        } else {
                            cTimes.closest('.form-group').show()
                        }
                        if(!cTimes.val()){
                            cTimes.val(selected.times > 0 ? selected.times : '')
                        }

                        let cTotal = $('#c-total');
                        if (selected.total_field_label) {
                            cTotal.closest('.form-group').find('.control-label').text(selected.total_field_label)
                        }
                        if (selected.total === -1) {
                            cTotal.closest('.form-group').hide()
                        } else {
                            cTotal.closest('.form-group').show()
                        }
                        if(!cTotal.val()){
                            cTotal.val(selected.total > 0 ? selected.total : '')
                        }

                        let cCycle = $('#c-cycle'), cCycleUnit = $('#c-cycle_unit')
                        if (selected.cycle && selected.cycle !== '[]' && selected.cycle !== '{}') {
                            cCycle.closest('.form-group').show()
                            cCycleUnit.closest('.form-group').show()
                            let opt = ''
                            $.each(JSON.parse(selected.cycle), function (key, label) {
                                opt += '<option value="' + key + '">' + label + '</option>'
                            })
                            cCycleUnit.html(opt).selectpicker('refresh').trigger("change");
                        } else {
                            cCycle.closest('.form-group').hide()
                            cCycleUnit.closest('.form-group').hide()
                        }

                        $('#c-mission_name').val(selected.mission_name)
                        $('#c-group_name').val(selected.group_name)
                    }
                })

                $('#c-mission_config_id').selectPage({
                    data: 'mission/config',
                    showField: 'title',
                    keyField: 'id',
                    params: {"selectpageFields": "*"},
                    eSelect: function (data) {
                        let list = {}
                        list[data.id] = data
                        $('#c-mission_config_id').trigger('selectpage.bs.change', list)
                    },
                    eAjaxSuccess: function (data) {
                        data.totalRow = typeof data.total !== 'undefined' ? data.total : (typeof data.totalRow !== 'undefined' ? data.totalRow : data.list.length);
                        let list = {}
                        $.each(data.list, function (i, el) {
                            list[el.id] = el;
                        })
                        $('#c-mission_config_id').trigger('selectpage.bs.change', list)
                        return data;
                    }
                });
            }
        }
    };
    return Controller;
});