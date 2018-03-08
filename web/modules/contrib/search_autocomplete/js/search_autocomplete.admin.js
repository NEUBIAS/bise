/**
 * @file
 * SEARCH AUTOCOMPLETE javascript helper tool.
 *
 * Used to help providing autocompletion on any input field.
 */


(function ($, Drupal) {

  /**
   * Determine a unique selector for the given element
   */
  $.fn.extend({
    getPath: function(path) {

      // The first time this function is called, path won't be defined.
      if (typeof path == 'undefined') {
        path = '';
      }

      // If this element is <html> we've reached the end of the path.
      if (this.is('html')) {
        return 'html' + path;
      }

      // Add the element name.
      var cur = this.get(0).nodeName.toLowerCase();

      // Determine the IDs and path.
      var id    = this.attr('id');
      var aClass = this.attr('class');

      // Add the #id if there is one.
      if (typeof id != 'undefined') {
        cur += '#' + id;
      }

      // Add any classes.
      if (typeof aClass != 'undefined') {
        cur += '.' + aClass.split(/[\s\n]+/).join('.');
      }

      if ($(cur + path).length <= 1) {
        return cur + path;
      } else {
      // Recurse up the DOM.
        return this.parent().getPath(' > ' + cur + path);
      }
    }
  });

  Drupal.behaviors.search_autocomplete_admin = {

    attach: function(context) {

      var input_selector = "input[type='text']:not(.ui-autocomplete-input), input[type='search']:not(.ui-autocomplete-input)";
      var selector = '';

      $("<ul id='sa_admin_menu'><div class='sa_title'>Search Aucomplete</div><li class='sa_add'>" + Drupal.t('add autocompletion') + "</li></ul>").appendTo($('body'));

      $("body").on('mouseover', input_selector, function (event) {
        var offset = $(this).offset();

        // display the context menu
        $("#sa_admin_menu").show();
        $('#sa_admin_menu').css('left', offset.left + $(this).width() - 5);
        $('#sa_admin_menu').css('top', offset.top + $(this).height() - 5);
        $('#sa_admin_menu').css('display','inline');
        $("#sa_admin_menu").css("position", "absolute");

        // find element unique selector
        selector = $(this).getPath();
      });

      // hide the menu when out or used
      $("body").on("click", input_selector, function () {
        $("#sa_admin_menu").hide();
      });
      $("body").on("mouseout", input_selector, function () {
        $("#sa_admin_menu").hide();
      });

      // hide the menu when out
      $("body").on("mouseover", "#sa_admin_menu", function(){
        $(this).show();
      });
      $("body").on("mouseout", "#sa_admin_menu", function(){
        $(this).hide();
      });

      // add a new autocompletion
      $("body").on("click", ".sa_add", function () {
        var $selector = Drupal.encodePath(selector)
        document.location.href = Drupal.url('admin/config/search/search_autocomplete/add?label=' + Drupal.t('Autocompletion for @selector', {'@selector': $selector}) + '&selector=' + $selector);
      });
    }
  };
})(jQuery, Drupal);
