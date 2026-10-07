"use strict";
// Check-in

$('input[name="checkInDate"]').daterangepicker({
  "singleDatePicker": true,
  autoUpdateInput: true,
  minDate: moment().format('YYYY-MM-DD'),
  locale: {
    format: 'YYYY-MM-DD'
  },
  isInvalidDate: function (date) {
    // Check if the current date is in the list of office off dates
    return holidays.includes(date.format('YYYY-MM-DD'));
  }
});
$('input[name="checkInDate"]').on('apply.daterangepicker', function (ev, picker) {
  $(this).val(picker.startDate.format('YYYY-MM-DD'));


});
$('input[name="checkInDate"]').on('cancel.daterangepicker', function (ev, picker) {
  $(this).val('');
});



$('input[name="checkInTime"]').daterangepicker({
  opens: 'left',
  timePicker: true,
 // startDate: '{{  date("h:i A", strtotime($roomContent->open_time)) }}',
  "singleDatePicker": true,
  timePickerIncrement: 30,
  timePicker24Hour: timePicker,
  locale: {
    format: timeFormate
  }
}).on('show.daterangepicker', function (ev, picker) {
  picker.container.find(".calendar-table").hide();
});

$('input[name="checkInTime"]').on('apply.daterangepicker', function (ev, picker) {
  $(this).val(picker.startDate.format(timeFormate));
});

$('input[name="checkInTime"]').on('cancel.daterangepicker', function (ev, picker) {
  $(this).val('');
});


//DATE TIME WISE PRICE UPDATE
$(document).ready(function () {

  $('body').on('change', '#checkInTime', function () {

    var selectedTime = $(this).val();

    var convertedTime = convertTo24Hour(selectedTime);
    $('#checkInTimes').val(convertedTime);
    updateUrl();

  });
  $('body').on('change', '#checkInDate', function () {
    $('#checkInDates').val($(this).val());
    updateUrl();

  });
});
function convertTo24Hour(time) {
  var timeArray = time.split(':');
  var hours = parseInt(timeArray[0]);
  var minutes = parseInt(timeArray[1]);
  var isPM = time.toLowerCase().indexOf('pm') > -1;

  if (isPM && hours < 12) {
    hours += 12;
  } else if (!isPM && hours == 12) {
    hours = 0;
  }

  return padZero(hours) + ':' + padZero(minutes) + ':00';
}

function padZero(num) {
  return (num < 10) ? '0' + num : num;
}

function updateUrl() {
  $('#searchForm').submit();
  $(".request-loader").addClass("show");
}

$('#searchForm').on('submit', function (e) {
  e.preventDefault();
  var fd = $(this).serialize();
  $('.search-container').html('');
  $.ajax({
    url: searchUrl,
    method: "get",
    data: fd,
    contentType: false,
    processData: false,
    success: function (response) {
      $('.request-loader').removeClass('show');
      $('.search-container').html(response);
    },
    error: function (xhr) {
      console.log(xhr);
    }
  });
});

