(function ($) {
    'use strict';

    $(document).ready(function () {
        $(".TabbedPanels").each(function ()
        {
            var defaultTab = 0;
            new Spry.Widget.TabbedPanels($(this).attr("id"),{defaultTab: defaultTab });
        });
        
        function get_tables_hierarchy(raw_tpl, element)
        {
            var raw_tpl_tmp=raw_tpl;
//            console.log(raw_tpl_tmp);
            var regExp = /{(.*?)}/g;
            var matches = raw_tpl_tmp.match(regExp);//regExp.exec(raw_tpl_tmp);
//            console.log(matches);
            //Attention on doit trouver un moyen d'identifier tous les éléments de la même ligne afin de remplacer leurs index correctement
            var count = (raw_tpl.match(regExp) || []).length;
            
            //Loop through all parents repeatable fields rows
            if(count>0)
            {
                var table_hierarchy=element.parents(".o-rf-row");
                $.each(table_hierarchy, function( i, e ) {
//                    console.log(matches[0]);
                    var re = new RegExp(matches[0], 'g');
                    var row_index=$(e).index();
                    raw_tpl_tmp=raw_tpl_tmp.replace(re, row_index);
                    matches.shift();
//                    raw_tpl_tmp=raw_tpl_tmp.replace(regExp, row_index);
                });
                
            }
            //The last or unique index in the template is the number of rows in the table
            var table_body = element.siblings("table.repeatable-fields-table").children("tbody").first();
            var new_key_index = table_body.children("tr").length;
            var re = new RegExp(matches[0], 'g');
            raw_tpl_tmp = raw_tpl_tmp.replace(re, new_key_index);
            return raw_tpl_tmp;
        }
        
        $(document).on("click", ".add-rf-row", function (e)
        {
            var table_body = $(this).siblings("table").find("tbody").first();
            var tpl_id=$(this).data("tpl");
            var raw_tpl = o_rows_tpl[tpl_id];
            var tpl1=get_tables_hierarchy(raw_tpl, $(this));
            table_body.append(tpl1);
        });

        $(document).on("click", ".remove-rf-row", function (e)
        {
            $(this).parent().parent().remove();
        });
        
        $(document).on("click", ".o-add-media", function (e) {
            e.preventDefault();
            var trigger = $(this);
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Select picture(s)"
                },
                multiple: false
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    trigger.parent().find("input[type=hidden]").val(attachment.id);
                                    trigger.parent().find(".media-preview").html("<img src='" + attachment.url + "'>");
                                    trigger.parent().find(".media-name").html(attachment.filename);
                                    if(trigger.parent().hasClass("trigger-change"))
                                        trigger.parent().find("input[type=hidden]").trigger("propertychange");
                                }
                        );
                    })
                    .open();
        });

        $(document).on("click", ".o-remove-media", function (e) {
            e.preventDefault();
            $(this).parent().find(".media-preview").html("");
            $(this).parent().find("input[type=hidden]").val("");
            $(this).parent().find(".media-name").html("");
            if($(this).parent().hasClass("trigger-change"))
                $(this).parent().find("input[type=hidden]").trigger("propertychange");
        });

    });

})(jQuery);

function is_json(data)
{
    if (/^[\],:{}\s]*$/.test(data.replace(/\\["\\\/bfnrtu]/g, '@').
    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
    replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
        return true;
    else
        return false;
}