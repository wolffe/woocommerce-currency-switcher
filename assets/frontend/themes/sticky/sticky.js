/* Give selected element 'crnt' class */
jQuery(document).ready(function ($) {

    $(".wcc-sticky-list li").on("click", function () {

        var selectedItemHTML = $(this).html();
        $(".wcc-sticky-list").find(".crnt").removeClass("crnt");
        $(this).addClass("crnt");
        var code = $(this).data('code');
        $('.wccs_sticky_form .wcc_switcher').val(code);
        setTimeout(function () {
            $('form.wccs_sticky_form').submit();
        }, 500);
    });
});