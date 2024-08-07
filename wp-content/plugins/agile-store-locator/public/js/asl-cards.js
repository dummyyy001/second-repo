(function($) {
    if ($(".sl_grid_slider")) {
        var sviper = new Sviper(".sl_grid_slider", {
          slidesPerView: 3,
          spaceBetween: 30,
          navigation: {
            nextEl: ".sviper-button-next",
            prevEl: ".sviper-button-prev",
          },
          breakpoints: {
              992: {
                  slidesPerView: 3,
              },
              767: {
                  slidesPerView: 2,
              },
              280: {
                  slidesPerView: 1,
              }
          }
        });

    }

    $(function() {
        $('.sl-list-item').matchHeight({
            byRow: true,
            property: 'height',
            target: null,
            remove: false
        });
    });
})(jQuery);