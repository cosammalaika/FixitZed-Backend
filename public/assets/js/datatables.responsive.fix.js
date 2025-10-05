(function ($) {
  if (typeof $.fn.dataTable === 'undefined') {
    return;
  }

  $(document).on('preInit.dt', function (e, settings) {
    var table = $(settings.nTable);
    if (!table.hasClass('dt-responsive-initialized')) {
      table.addClass('dt-responsive-initialized');
      if (!table.closest('.table-responsive').length) {
        table.wrap('<div class="table-responsive"></div>');
      }
    }
  });
})(jQuery);
