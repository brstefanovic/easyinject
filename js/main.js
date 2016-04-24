/**
 * Initialize Javascript
 */
(function($){
    $(document).ready(function(){
        var JSeditor = ace.edit("JSeditor");
        JSeditor.setTheme("ace/theme/monokai");
        JSeditor.getSession().setMode("ace/mode/javascript");
        var JStextarea = $('textarea[name="easyinject_JScode"]');
        JSeditor.getSession().on('change', function(){
            JStextarea.val(JSeditor.getSession().getValue());
        });

        var CSSeditor = ace.edit("CSSeditor");
        CSSeditor.setTheme("ace/theme/monokai");
        CSSeditor.getSession().setMode("ace/mode/javascript");
        var CSStextarea = $('textarea[name="easyinject_CSScode"]');
        CSSeditor.getSession().on('change', function(){
            CSStextarea.val(CSSeditor.getSession().getValue());
        });
    });
})(jQuery);