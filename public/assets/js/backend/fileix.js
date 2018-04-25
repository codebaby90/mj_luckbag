define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            require.config({
                baseUrl: "js",
                paths: {
                    'webix': Fast.api.cdnurl('/assets/libs/webix/codebase/webix'),
                    'filemanager': Fast.api.cdnurl('/assets/libs/webix/codebase/filemanager'),
                },
                shim: {
                    "filemanager": {
                        deps: ["webix"],
                        exports: "filemanager"
                    },
                    "webix": ["jquery"]
                }
            });


            require(['filemanager'], function (filemanager) {

                $.getJSON('fileix/parms', function (data) {
                    h = data.ix_height;
                    d = $.getJSON('fileix/lst');

                    webix.ready(function () {

                        webix.ui({
                            view: "filemanager",
                            id: "files", height: h, container: "my_box",
                            handlers: {
                                "files": "fileix/data",
                                "search": "fileix/data",
                                "upload": "fileix/data",
                                "download": "fileix/data",
                                "copy": "fileix/data",
                                "move": "fileix/data",
                                "remove": "fileix/data",
                                "rename": "fileix/data",
                                "create": "fileix/data"
                            }
                        }).show();
                        $$("files").parse(d);

                        $$("files").attachEvent("onBeforeRun", function (id) {
                            webix.confirm({
                                text: "您要下载本文件么?",
                                ok: "下载",
                                cancel: "取消",
                                callback: function (result) {
                                    if (result)
                                        $$("files").download(id);
                                }
                            });
                            return false;
                        });
                    });
                });
            });
        },
    };
    return Controller;
});
