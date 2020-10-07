define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'fastpay/payin/index' + location.search,
                    add_url: 'fastpay/payin/add',
                    edit_url: 'fastpay/payin/edit',
                    del_url: 'fastpay/payin/del',
                    multi_url: 'fastpay/payin/multi',
                    import_url: 'fastpay/payin/import',
                    table: 'fastpay_account',
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
                        {field: 'fastpay_config.label', title: __('支付名称'), operate: false},
                        {field: 'channel', title: __('通道名称'), formatter: function (val, row){
                                return row.fastpay_config.payin.channel[val].label
                            }, operate: false},
                        {field: 'title', title: __('显示名称')},
                        {field: 'fee_rate', title: __('Fee_rate'), operate: 'BETWEEN'},
                        {field: 'mch_id', title: __('Mch_id')},
                        {field: 'version', title: __('Version')},
                        {field: 'status', title: __('Status'), formatter:Table.api.formatter.status, searchList:{'1':'已启用','0':'未启用'},custom:{'1':'success','0':'gray'}},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'enable',
                                    text: __('启用'),
                                    title: __('启用'),
                                    classname: 'btn btn-xs btn-success btn-click',
                                    click: function (btn, row){
                                        Fast.api.ajax({
                                            url: 'fastpay/payin/multi?ids='+row.id,
                                            data:{params:"status=1"}
                                        }, function (){
                                            table.bootstrapTable('refresh');
                                        })
                                    },
                                    confirm: '确认启用',
                                    visible: function (row){
                                        return !row.status
                                    }
                                },
                                {
                                    name: 'disable',
                                    text: __('禁用'),
                                    title: __('禁用'),
                                    classname: 'btn btn-xs btn-primary btn-click',
                                    click: function (btn, row){
                                        Fast.api.ajax({
                                            url: 'fastpay/payin/multi?ids='+row.id,
                                            data:{params:"status=0"}
                                        }, function (){
                                            table.bootstrapTable('refresh');
                                        })
                                    },
                                    confirm: '确认禁用',
                                    visible: function (row){
                                        return !!row.status
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
            Controller.api.getFastpayList('add');
        },
        edit: function () {
            Controller.api.getFastpayList();
        },
        api: {
            getFastpayList: function (action) {
                let fastpayList = {}
                let setDefaultAmount = function (input, type, list) {
                    input.data('default', true)
                    if (!input.tagsinput) return
                    if (list) {
                        input.tagsinput(type, list);
                    } else {
                        input.tagsinput(type);
                    }
                }
                $.validator.config({
                    rules: {
                        rangeAmount: function (el) {
                            let fastpay = fastpayList[$('#c-fastpay').val()], channel
                            if (fastpay) {
                                channel = fastpay.payin.channel[$('#c-channel').val()]
                            }
                            if (!fastpay || !channel) {
                                return '先选择支付和通道'
                            }
                            let min = parseFloat(channel.min_amount ? channel.min_amount : (fastpay.min_amount ? fastpay.min_amount : 0))
                            let max = parseFloat(channel.max_amount ? channel.max_amount : (fastpay.max_amount ? fastpay.max_amount : 0))
                            if (min > 0 && $(this).val() < min) {
                                return '最小金额' + min
                            }
                            if (max > 0 && $(this).val() > max) {
                                return '最大金额' + max
                            }
                            return true;
                        }
                    }
                });
                $(document).on('change', '#c-fastpay', function () {
                    let fastpay = fastpayList[$(this).val()];
                    let amounts = fastpay.amounts
                    let cAmountList = $('#c-amount_list')
                    let cChannel = $('#c-channel')
                    let cVersion = $('#c-version')
                    if (amounts && !cAmountList.data('change') && action === 'add') {
                        setDefaultAmount(cAmountList, 'removeAll')
                        setDefaultAmount(cAmountList, 'items')
                        setDefaultAmount(cAmountList, 'add', amounts)
                    }
                    if(fastpay.payin.version){
                        let versions = {}
                        fastpay.payin.version.map(v=>{
                            versions[v] = {label:v}
                        })
                        cVersion.html(Template('fastpayOptionTpl', {list: versions, value: cVersion.data('value')}))
                    }else{
                        cVersion.hide()
                    }
                    cChannel.html(Template('fastpayOptionTpl', {list: fastpay.payin.channel, value: cChannel.data('value')}))
                    if (cChannel.selectpicker) {
                        cChannel.selectpicker('refresh')
                    }
                }).on('change', '#c-channel', function () {
                    let fastpay = $('#c-fastpay').val(), channel = $(this).val()
                    let amounts = fastpayList[fastpay].payin.channel[channel].amounts
                    let cAmountList = $('#c-amount_list')
                    if (amounts && !cAmountList.data('change') && action === 'add') {
                        setDefaultAmount(cAmountList, 'removeAll')
                        setDefaultAmount(cAmountList, 'items')
                        setDefaultAmount(cAmountList, 'add', amounts)
                    }
                }).on('change', '#c-amount_list', function () {
                    if (!$(this).data('default')) {
                        $(this).data('change', true)
                    } else {
                        $('#c-amount_list').data('default', false)
                    }
                })
                $.get('fastpay/payin/getFastpayList', function (res) {
                    fastpayList = res
                    let cFastpay = $('#c-fastpay')
                    Controller.api.bindevent();
                    cFastpay.html(Template('fastpayOptionTpl', {
                        list: fastpayList,
                        value: cFastpay.data('value')
                    })).trigger('change')
                })
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});