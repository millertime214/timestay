$(document).ready(function () {
  'use strict';


  $(document).ready(function () {
    $('body').on('change', '#closetime', function () {
      var selectedTime = $(this).val();
      var convertedTime = convertTo24Hour(selectedTime);
      $('#close_time').val(convertedTime);
      //  updateUrl();
    });
    $('body').on('change', '#opentime', function () {
      var selectedTime = $(this).val();
      var convertedTime = convertTo24Hour(selectedTime);
      $('#open_time').val(convertedTime);
      //updateUrl();
    });

  });

  $('#closetime').daterangepicker({
    opens: 'left',
    timePicker: true,
    startDate: '10:00 PM',
    "singleDatePicker": true,
    timePickerIncrement: 30,
    timePicker24Hour: timePicker,
    locale: {
      format: timeFormate
    }
  }).on('show.daterangepicker', function (ev, picker) {
    picker.container.find(".calendar-table").hide();
  });


  $('#closetime').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format(timeFormate));
  });

  $('#closetime').on('cancel.daterangepicker', function (ev, picker) {
    $(this).val('');
  });


  $('input[name="opentime"]').daterangepicker({
    opens: 'left',
    timePicker: true,
    startDate: '07:00 AM',
    "singleDatePicker": true,
    timePickerIncrement: 10,
    timePicker24Hour: timePicker,
    locale: {
      format: timeFormate
    }
  }).on('show.daterangepicker', function (ev, picker) {
    picker.container.find(".calendar-table").hide();
  });


  $('input[name="opentime"]').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format(timeFormate));
  });

  $('input[name="opentime"]').on('cancel.daterangepicker', function (ev, picker) {
    $(this).val('');
  });


  // blog form
  $('#blogForm').on('submit', function (e) {
    $('.request-loader').addClass('show');
    e.preventDefault();

    let action = $(this).attr('action');
    let fd = new FormData($(this)[0]);

    $.ajax({
      url: action,
      method: 'POST',
      data: fd,
      contentType: false,
      processData: false,
      success: function (data) {
        $('.request-loader').removeClass('show');

        if (data.status == 'success') {
          location.reload();
        }
      },
      error: function (error) {
        let errors = ``;

        for (let x in error.responseJSON.errors) {
          errors += `<li>
                <p class="text-danger mb-0">${error.responseJSON.errors[x][0]}</p>
              </li>`;
        }

        $('#blogErrors ul').html(errors);
        $('#blogErrors').show();

        $('.request-loader').removeClass('show');

        $('html, body').animate({
          scrollTop: $('#blogErrors').offset().top - 100
        }, 1000);
      }
    });
  });


  // custom page form
  $('#pageForm').on('submit', function (e) {
    $('.request-loader').addClass('show');
    e.preventDefault();

   

    let action = $(this).attr('action');
    let fd = new FormData($(this)[0]);

    $.ajax({
      url: action,
      method: 'POST',
      data: fd,
      contentType: false,
      processData: false,
      success: function (data) {
        $('.request-loader').removeClass('show');

        if (data.status == 'success') {
          location.reload();
        }
      },
      error: function (error) {
        let errors = ``;

        if (error.responseJSON && error.responseJSON.errors) {
          for (let x in error.responseJSON.errors) {
            errors += `<li>
            <p class="text-danger mb-0">${error.responseJSON.errors[x][0]}</p>
          </li>`;
          }
        } else {
          errors += `<li><p class="text-danger mb-0">An unexpected error occurred.</p></li>`;
        }


        $('#pageErrors ul').html(errors);
        $('#pageErrors').show();

        $('.request-loader').removeClass('show');

        $('html, body').animate({
          scrollTop: $('#pageErrors').offset().top - 100
        }, 1000);
      }
    });
  });

  // show or hide input field according to selected ad type
  $('#ad_type').on('change', function () {
    let adType = $(this).val();

    if (adType == 'banner') {
      if (!$('#slot-input').hasClass('d-none')) {
        $('#slot-input').addClass('d-none');
      }

      $('#image-input').removeClass('d-none');
      $('#url-input').removeClass('d-none');
    } else {
      if (!$('#image-input').hasClass('d-none') && !$('#url-input').hasClass('d-none')) {
        $('#image-input').addClass('d-none');
        $('#url-input').addClass('d-none');
      }

      $('#slot-input').removeClass('d-none');
    }
  });

  $('.edit-ad-type').on('change', function () {
    let adType = $(this).val();

    if (adType == 'banner') {
      if (!$('#edit-slot-input').hasClass('d-none')) {
        $('#edit-slot-input').addClass('d-none');
      }

      $('#edit-image-input').removeClass('d-none');
      $('#edit-url-input').removeClass('d-none');
    } else {
      if (!$('#edit-image-input').hasClass('d-none') && !$('#edit-url-input').hasClass('d-none')) {
        $('#edit-image-input').addClass('d-none');
        $('#edit-url-input').addClass('d-none');
      }

      $('#edit-slot-input').removeClass('d-none');
    }
  });


  // show different input field according to input type for digital product
  $('select[name="input_type"]').on('change', function () {
    let optionVal = $(this).val();

    if (optionVal == 'upload') {
      $('#file-input').removeClass('d-none');

      if (!$('#link-input').hasClass('d-none')) {
        $('#link-input').addClass('d-none');
      }
    } else if (optionVal == 'link') {
      $('#link-input').removeClass('d-none');

      if (!$('#file-input').hasClass('d-none')) {
        $('#file-input').addClass('d-none');
      }
    }
  });

  // product form
  $('#productForm').on('submit', function (e) {

    e.preventDefault();

    let can_product_add = $('button[type=submit]').data('can_product_add');

    if (can_product_add == 0) {
      bootnotify('Please Buy a plan to add a product.!', 'Alert', 'warning');
      return false;
    } else if (can_product_add == 2) {
      $("#checkLimitModal").modal('show');
      bootnotify("Products limit reached or exceeded", 'Alert', 'warning');
      return false;
    }
    $('.request-loader').addClass('show');
    let action = $(this).attr('action');
    let fd = new FormData($(this)[0]);

    $.ajax({
      url: action,
      method: 'POST',
      data: fd,
      contentType: false,
      processData: false,
      success: function (data) {
        $('.request-loader').removeClass('show');

        if (data.status == 'success') {
          location.reload();
        }
        if (data == "downgrade") {
          $('.modal').modal('hide');
          var content = {};

          content.message = YourPackagelimitreachedorexceeded;
          content.title = "Warning";
          content.icon = 'fa fa-bell';

          $.notify(content, {
            type: 'warning',
            placement: {
              from: 'top',
              align: 'right'
            },
            showProgressbar: true,
            time: 1000,
            delay: 4000,
          });
          $("#checkLimitModal").modal('show');
        }
      },
      error: function (error) {
        let errors = ``;

        for (let x in error.responseJSON.errors) {
          errors += `<li>
                <p class="text-danger mb-0">${error.responseJSON.errors[x][0]}</p>
              </li>`;
        }

        $('#productErrors ul').html(errors);
        $('#productErrors').show();

        $('.request-loader').removeClass('show');

        $('html, body').animate({
          scrollTop: $('#productErrors').offset().top - 100
        }, 1000);
      }
    });
  });


  // show or hide price input fields by toggling the 'Request Price Button'
  $('input[name="price_btn_status"]').on('change', function () {
    let radioBtnVal = $('input[name="price_btn_status"]:checked').val();

    if (parseInt(radioBtnVal) === 1) {
      $('#equipment-price-input').addClass('d-none');
    } else {
      $('#equipment-price-input').removeClass('d-none');
    }
  });

  // equipment form
  $('#equipmentForm').on('submit', function (e) {
    $('.request-loader').addClass('show');
    e.preventDefault();

    let action = $(this).attr('action');
    let fd = new FormData($(this)[0]);

    $.ajax({
      url: action,
      method: 'POST',
      data: fd,
      contentType: false,
      processData: false,
      success: function (data) {
        $('.request-loader').removeClass('show');

        if (data.status == 'success') {
          location.reload();
        }
      },
      error: function (error) {
        let errors = ``;

        for (let x in error.responseJSON.errors) {
          errors += `<li>
                <p class="text-danger mb-0">${error.responseJSON.errors[x][0]}</p>
              </li>`;
        }

        $('#equipmentErrors ul').html(errors);
        $('#equipmentErrors').show();

        $('.request-loader').removeClass('show');

        $('html, body').animate({
          scrollTop: $('#equipmentErrors').offset().top - 100
        }, 1000);
      }
    });
  });

  $('thead').on('click', '.addRow', function (e) {
    e.preventDefault();
    var tr = `<tr>
              <td>
                <div class="form-group">
                  <input type="text" name="custom_feature_names[]" class="form-control">
                </div>
              </td>
              <td>
                <div class="form-group">
                  <input type="text" name="custom_feature_values[]" class="form-control">
                </div>
              </td>
              <td><a href="" class="btn btn-danger btn-sm deleteRow">-</a></td>
            </tr>`;
    $('tbody.append').append(tr);
  });

  $('tbody').on('click', '.deleteRow', function (e) {
    e.preventDefault();
    $(this).parent().parent().remove();
  });

  function bootnotify(message, title, type) {
    var content = {};

    content.message = message;
    content.title = title;
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: type,
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      allow_dismiss: true,
      delay: 4000
    });
  }

});


