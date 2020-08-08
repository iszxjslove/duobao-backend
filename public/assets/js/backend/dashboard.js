define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template', 'socket'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template, io) {

    var Controller = {
        index: function () {
            // // 初始化io对象
            // var socket = io(Config.moduleurl + ':2120', {
            //     transports: ['websocket']
            // });
            // // uid 可以为网站用户的uid，作为例子这里用session_id代替
            // var uid = AdminId;
            // // 当socket连接后发送登录请求
            // socket.on('connect', function () {
            //     socket.emit('login', uid, 'fdsafsafs');
            // });
            // // 当服务端推送来消息时触发，这里简单的aler出来，用户可做成自己的展示效果
            // socket.on('new_msg', function (msg) {
            //     console.log(msg)
            // });


            // 加载数据面板
            // let keysList = []
            // $('.data-panel').each(function () {
            //     keysList.push($(this).data('panel'))
            // })
            // $.get('dashboard/data', {keys: keysList.join(',')}, function (ret) {
            //     $.each(ret, function (i, el) {
            //         console.log(el)
            //         $('.data-panel[data-panel="issue_sales"]').html(Template(i + '_tpl', el))
            //     })
            // })


            // 基于准备好的dom，初始化echarts实例
            // var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 指定图表的配置项和数据
            // var option = {
            //     title: {
            //         text: '',
            //         subtext: ''
            //     },
            //     tooltip: {
            //         trigger: 'axis'
            //     },
            //     legend: {
            //         data: [__('Sales'), __('Orders')]
            //     },
            //     toolbox: {
            //         show: false,
            //         feature: {
            //             magicType: {show: true, type: ['stack', 'tiled']},
            //             saveAsImage: {show: true}
            //         }
            //     },
            //     xAxis: {
            //         type: 'category',
            //         boundaryGap: false,
            //         data: Orderdata.column
            //     },
            //     yAxis: {},
            //     grid: [{
            //         left: 'left',
            //         top: 'top',
            //         right: '10',
            //         bottom: 30
            //     }],
            //     series: [{
            //         name: __('Sales'),
            //         type: 'line',
            //         smooth: true,
            //         areaStyle: {
            //             normal: {}
            //         },
            //         lineStyle: {
            //             normal: {
            //                 width: 1.5
            //             }
            //         },
            //         data: Orderdata.paydata
            //     },
            //         {
            //             name: __('Orders'),
            //             type: 'line',
            //             smooth: true,
            //             areaStyle: {
            //                 normal: {}
            //             },
            //             lineStyle: {
            //                 normal: {
            //                     width: 1.5
            //                 }
            //             },
            //             data: Orderdata.createdata
            //         }]
            // };

            // 使用刚指定的配置项和数据显示图表。
            // myChart.setOption(option);

            //动态添加数据，可以通过Ajax获取数据然后填充
            // setInterval(function () {
            //     Orderdata.column.push((new Date()).toLocaleTimeString().replace(/^\D*/, ''));
            //     var amount = Math.floor(Math.random() * 200) + 20;
            //     Orderdata.createdata.push(amount);
            //     Orderdata.paydata.push(Math.floor(Math.random() * amount) + 1);
            //
            //     //按自己需求可以取消这个限制
            //     if (Orderdata.column.length >= 20) {
            //         //移除最开始的一条数据
            //         Orderdata.column.shift();
            //         Orderdata.paydata.shift();
            //         Orderdata.createdata.shift();
            //     }
            //     myChart.setOption({
            //         xAxis: {
            //             data: Orderdata.column
            //         },
            //         series: [{
            //             name: __('Sales'),
            //             data: Orderdata.paydata
            //         },
            //             {
            //                 name: __('Orders'),
            //                 data: Orderdata.createdata
            //             }]
            //     });
            // }, 2000);
            // $(window).resize(function () {
            //     myChart.resize();
            // });

            // $(document).on("click", ".btn-checkversion", function () {
            //     top.window.$("[data-toggle=checkupdate]").trigger("click");
            // });
            //
            // $(document).on("click", ".btn-refresh", function () {
            //     setTimeout(function () {
            //         myChart.resize();
            //     }, 0);
            // });
            Controller.api.getCountPeople()
            Controller.api.getCountTotal()
            Controller.api.getTotalAmount()
            Controller.api.getTotal()

            $('.count-people .select-time').on('btn.group.checked', function (e, data) {
                if(data.value){
                    let startDate = new Date();
                    let endDate = new Date();
                    startDate.setDate(startDate.getDate() - data.value);
                    endDate.setDate(endDate.getDate());
                    let start_time = startDate.format("yyyy-MM-dd hh:mm:ss");
                    let end_time = endDate.format("yyyy-MM-dd hh:mm:ss");
                    Controller.api.getCountPeople('between', [start_time, end_time])
                }
            })

            $('.count-total .select-time').on('btn.group.checked', function (e, data) {
                if(data.value){
                    let startDate = new Date();
                    let endDate = new Date();
                    startDate.setDate(startDate.getDate() - data.value);
                    endDate.setDate(endDate.getDate());
                    let start_time = startDate.format("yyyy-MM-dd hh:mm:ss");
                    let end_time = endDate.format("yyyy-MM-dd hh:mm:ss");
                    Controller.api.getCountTotal('between', [start_time, end_time])
                }
            })

            $('.d-btn-group').on('click', '.d-btn', function () {
                let btnGroup = $(this).closest('.d-btn-group')
                btnGroup.find('.d-btn').removeClass('active')
                $(this).addClass('active')
                console.log(btnGroup)
                console.log($(this).data('value'))
                btnGroup.data('value', $(this).data('value')).trigger('btn.group.checked', {
                    value: $(this).data('value'),
                    el: btnGroup
                })
            }).on('btn.group.init', function () {
                $(this).find('.d-btn.active').trigger('click')
            }).trigger('btn.group.init')
        },
        api: {
            getCountPeople: function (op, range) {
                if(!op) return false
                $.get('dashboard/countPeople', {op: op, range: range}, function (ret) {
                    $.each(ret, function (i, el) {
                        $('.count-people .count-' + i + ' .count-quantity').text(el)
                    })
                })
            },
            getCountTotal: function (op, range) {
                if(!op) return false
                $.get('dashboard/countTotal', {op: op, range: range}, function (ret) {
                    let bonus = 0;
                    let wager_fee = 0;
                    $.each(ret, function (i, el) {
                        switch (el.category) {
                            case "bonus":
                                bonus += el.total
                                break
                            case "wager_fee":
                                wager_fee += el.total
                                break
                            default:
                                $('.count-total .count-' + el.name + ' .count-quantity').text(el.total)
                        }
                    })
                    $('.count-total .count-bonus .count-quantity').text(bonus)
                    $('.count-total .count-wager_fee .count-quantity').text(wager_fee)
                })
            },
            getTotalAmount: function () {
                $.get('dashboard/totalAmount', {}, function (ret) {
                    $.each(ret, function (i, el) {
                        $('.count-' + i + ' .count-quantity').text(el)
                    })
                })
            },
            getTotal: function () {
                $.get('dashboard/total', {}, function (ret) {
                    $.each(ret, function (i, el) {
                        $('.count-all-quantity .count-' + el.name + ' .count-quantity').text(el.total)
                    })
                })
            }
        }
    };

    return Controller;
});