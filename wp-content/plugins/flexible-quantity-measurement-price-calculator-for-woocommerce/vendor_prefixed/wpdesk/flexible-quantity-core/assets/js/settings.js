(function ($) {

  var FqCustomUnits = {
    item: jQuery("#fq-custom-units > .single-condition:first"),
    wrapper: jQuery("#fq-custom-units"),
    button_selector: ".add-condition",
    remove_selector: ".remove-condition",
    single_item_class: ".single-condition",
	callback: function () { 
		jQuery('.fq-help-tip').tipTip({
			'attribute': 'data-tip',
			'fadeIn':    50,
			'fadeOut':   50,
			'delay': 200,
			'defaultPosition': "top"
		});
	},
    add_new_item: function () {
      let new_item = this.item.clone();
      new_item.find("input").val("");
      new_item.find("select").prop("selectedIndex", 0);
      new_item.find(this.remove_selector).removeClass("disabled");
      this.wrapper.append(new_item);
      this.remove_listener(new_item);
    },

    /**
         * @param {object} listener.
         */
    remove_listener: function (listener) {
      const self = this;
      listener.find(this.remove_selector + ":first").on("click", function (e) {
        e.preventDefault();
        jQuery(this).closest(self.single_item_class).remove();
      });
    },
    add_listeners: function () {
      const self = this;
      jQuery(document).on("click", self.button_selector, function (e) {
        e.preventDefault();
        self.add_new_item();
        if (typeof self.callback === "function") {
          self.callback();
        }
      });
      jQuery(document).on("click", self.remove_selector, function (e) {
        e.preventDefault();
        if (!jQuery(this).hasClass('disabled')) {
          jQuery(this).closest(self.single_item_class).remove();
        }
      });
    },
    init: function () {
      this.add_listeners();
		  this.callback();
    }
  };

  FqCustomUnits.init();
})(jQuery);
