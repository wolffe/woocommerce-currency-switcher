document.addEventListener("DOMContentLoaded", function () {
    // Handle click on main button
    document.querySelectorAll(".wcc-switcher-style-01 .wcc-crnt-currency").forEach(function (button) {
        button.addEventListener("click", function () {
            let parent = this.parentElement;
            let currentCurrency = parent.querySelector(".wcc-crnt-currency");
            let list = parent.querySelector(".wcc-list");

            if (list) {
                list.style.display = list.style.display === "block" ? "none" : "block";
            }

            function toggleClass() {
                currentCurrency.classList.toggle("wcc-list-opened");
            }

            toggleClass();

            // Handle click on list items
            if (list) {
                list.querySelectorAll("li").forEach(function (item) {
                    item.addEventListener("click", function () {
                        let selectedItemHTML = this.innerHTML;
                        list.querySelectorAll(".crnt").forEach(function (el) {
                            el.classList.remove("crnt");
                        });
                        this.classList.add("crnt");

                        list.style.display = "none"; // Slide up (hide)
                        currentCurrency.innerHTML = selectedItemHTML;
                        currentCurrency.classList.remove("wcc-list-opened");
                    });
                });
            }
        });
    });

    // Handle click on list items inside #wcc-switcher-style-01
    document.addEventListener("click", function (event) {
        if (event.target.closest("#wcc-switcher-style-01 ul li")) {
            let item = event.target.closest("li");
            let code = item.getAttribute("data-code");
            let switcher = document.querySelector(".wcc_switcher_form_01 .wcc_switcher");

            if (switcher) {
                switcher.value = code;
            }

            setTimeout(function () {
                let form = document.querySelector("form.wcc_switcher_form_01");
                if (form) {
                    form.submit();
                }
            }, 500);
        }
    });
});
