define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'luckbag/setbag/index',
                    add_url: 'luckbag/setbag/add',
                    edit_url: 'luckbag/setbag/edit',
                    del_url: 'luckbag/setbag/del',
                    multi_url: 'luckbag/setbag/multi',
                    table: 'luckbag_setbag',
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
                        {field: 'token', title: __('Token')},
                        {field: 'pid', title: __('Pid')},
                        {field: 'bag_name', title: __('Bag_name')},
                        {field: 'bag_image', title: __('Bag_image'), formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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