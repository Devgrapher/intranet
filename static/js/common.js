require(["jquery", "jquery.attrajax", 'jquery-ui', "jquery-number", "jquery.cookie", "jquery-form", "select2"], function ($) {
  $(function () {
    $('*[attrajax]').attrAjax();
    $('input[js_number_format]').number(true).css('text-align', 'right');
  });
  $('select[js_data_chosen]').select2();
  $('input[js_datepicker]').datepicker({dateFormat: "yy-mm-dd"});
  $('select[js_cookie_save],input[js_cookie_save]')
    .change(function () {
      var $this = $(this);
      var key = $this[0].tagName + '#' + $this.attr('name') + '#' + $this.attr('id');
      var val;
      if ($this.is('[type=checkbox]')) {
        val = $this.is(":checked");
      } else {
        val = $this.val();
        if (val === null) {
          val = $this.children().first().val();
          $this.val(val);
        }
      }
      $.cookie(key, val);
    })
    .each(function () {
      var $this = $(this);
      var key = $this[0].tagName + '#' + $this.attr('name') + '#' + $this.attr('id');

      var cookied_value = $.cookie(key);
      console.log('cookief_val', cookied_value, key);
      if (cookied_value == undefined) {
        var defaultVal = $this.attr('js_default_save');
        console.log('Default_val', defaultVal, key);
        if (defaultVal !== undefined) {
            if ($this.is('[type=checkbox]')) {
                if (defaultVal == 'true') {
                    $this.attr("checked", defaultVal);
                }
            } else {
                $this.val(defaultVal);
            }
        }
      } else {
        if ($this.is('[type=checkbox]')) {
          if (cookied_value == 'true') {
            $this.attr("checked", cookied_value);
          }
        }
        else {
          $this.val(cookied_value);
        }
      }
      $this.trigger('change');
    });
});

function refresh() {
  document.location.reload();
}

