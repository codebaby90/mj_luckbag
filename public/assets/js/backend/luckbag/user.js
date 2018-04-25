define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'luckbag/user/index',
                    add_url: 'luckbag/user/add',
                    edit_url: 'luckbag/user/edit',
                    del_url: 'luckbag/user/del',
                    multi_url: 'luckbag/user/multi',
                    table: 'luckbag_user',
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
                        {field: 'openid', title: __('Openid')},
                        {field: 'share_key', title: __('Share_key')},
                        {field: 'user_name', title: __('User_name')},
                        {field: 'nick_name', title: __('Nick_name')},
                        {field: 'tel', title: __('Tel')},
                        {field: 'idcard', title: __('Idcard')},
                        {field: 'is_receive', title: __('Is_receive'), visible:false, searchList: {"0":__('Is_receive 0'),"1":__('Is_receive 1')}},
                        {field: 'is_receive_text', title: __('Is_receive'), operate:false},
                        {field: 'receive_time', title: __('Receive_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'receive_money', title: __('Receive_money'), operate:'BETWEEN'},
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