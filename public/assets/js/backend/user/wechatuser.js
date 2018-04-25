define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/wechatuser/index',
                    add_url: 'user/wechatuser/add',
                    edit_url: 'user/wechatuser/edit',
                    del_url: 'user/wechatuser/del',
                    multi_url: 'user/wechatuser/multi',
                    table: 'wechat_user',
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
                        {field: 'openid', title: __('Openid')},
                        {field: 'unionid', title: __('Unionid')},
                        {field: 'secret', title: __('Secret')},
                        {field: 'wecha_name', title: __('Wecha_name')},
                        {field: 'nick_name', title: __('Nick_name')},
                        {field: 'sex', title: __('Sex'), visible:false, searchList: {"1":__('Sex 1')}},
                        {field: 'sex_text', title: __('Sex'), operate:false},
                        {field: 'birthday', title: __('Birthday'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'region', title: __('Region')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"2":__('Status 2'),"1":__('Status 1'),"0":__('Status 0')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'email', title: __('Email')},
                        {field: 'user_name', title: __('User_name')},
                        {field: 'tel', title: __('Tel')},
                        {field: 'wxavatar', title: __('Wxavatar')},
                        {field: 'token', title: __('Token')},
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