document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".wcc-sticky-list li").forEach(function (item) {
        item.addEventListener("click", function () {
            let selectedItemHTML = this.innerHTML;
            document.querySelectorAll(".wcc-sticky-list .crnt").forEach(function (el) {
                el.classList.remove("crnt");
            });
            this.classList.add("crnt");

            let code = this.getAttribute("data-code");
            let switcher = document.querySelector(".wccs_sticky_form .wcc_switcher");
            if (switcher) {
                switcher.value = code;
            }

            setTimeout(function () {
                let form = document.querySelector("form.wccs_sticky_form");
                if (form) {
                    form.submit();
                }
            }, 500);
        });
    });
});
