<style>
  .court-availability {
    text-align: left;
  }

  .court-availability__heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
  }

  .court-availability__title,
  .court-availability__date {
    display: block;
  }

  .court-availability__title {
    color: var(--color-dark, #1b1b1b);
    font-size: 14px;
    font-weight: 700;
  }

  .court-availability__date,
  .court-availability__hours {
    color: #777;
    font-size: 11px;
  }

  .court-availability__legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 4px 8px;
    color: #666;
    font-size: 10px;
  }

  .court-availability__legend span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .court-availability__legend i {
    width: 7px;
    height: 7px;
    border-radius: 50%;
  }

  .court-availability__legend .is-vacant {
    background: #20a464;
  }

  .court-availability__legend .is-occupied {
    background: #dc4c5b;
  }

  .court-availability__hours {
    margin-bottom: 8px;
  }

  .court-availability__slots {
    display: flex;
    gap: 6px;
    padding-bottom: 4px;
    overflow-x: auto;
    scrollbar-width: thin;
  }

  .court-availability__slot {
    flex: 0 0 104px;
    padding: 7px 5px;
    border: 1px solid;
    border-radius: 7px;
    text-align: center;
  }

  .court-availability__slot span,
  .court-availability__slot strong {
    display: block;
    line-height: 1.25;
  }

  .court-availability__slot span {
    font-size: 11px;
    font-weight: 600;
  }

  .court-availability__slot strong {
    margin-top: 3px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .court-availability__slot.is-vacant {
    border-color: #b9e5cf;
    background: #edf9f3;
    color: #147a4a;
  }

  .court-availability__slot.is-occupied {
    border-color: #f0c3c8;
    background: #fff1f2;
    color: #bd3342;
  }

  .court-availability__empty {
    padding: 9px 10px;
    border-radius: 7px;
    background: #f5f6f8;
    color: #777;
    font-size: 12px;
    text-align: center;
  }
</style>

<style>
  .court-availability__notice {
    display: flex;
    min-height: 190px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 15px;
    border: 1px solid #ead8a6;
    border-radius: 8px;
    background: #fffaf0;
    color: #5f5130;
    text-align: center;
  }

  .court-availability__notice-icon {
    display: inline-flex;
    width: 42px;
    height: 42px;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border-radius: 50%;
    background: #f6e5b6;
    color: #9a7415;
    font-size: 17px;
  }

  .court-availability__notice strong {
    color: #3f3827;
    font-size: 14px;
  }

  .court-availability__notice p {
    margin: 7px 0 12px;
    font-size: 11px;
    line-height: 1.55;
  }

  .court-availability__notice .btn {
    padding: 7px 12px;
    font-size: 10px;
  }

  .court-availability__notice.is-owner_registration {
    border-color: #cad8eb;
    background: #f4f8fd;
    color: #4b607c;
  }

  .court-availability__notice.is-owner_registration .court-availability__notice-icon {
    background: #dce8f7;
    color: #37689f;
  }
</style>

<style>
  /* Compact timeGridDay treatment shared by the Courts grid and map views. */
  .court-availability {
    --calendar-border: #d9dce1;
    --calendar-muted: #6d7278;
    text-align: left;
  }

  .court-availability__heading {
    align-items: center;
    margin-bottom: 10px;
  }

  .court-availability__title {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #25282b;
    font-size: 14px;
  }

  .court-availability__title i {
    color: var(--color-primary);
    font-size: 15px;
  }

  .court-availability__date {
    padding: 4px 8px;
    border: 1px solid var(--calendar-border);
    border-radius: 5px;
    background: #fff;
    color: #44484d;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
  }

  .court-availability__calendar {
    overflow: hidden;
    border: 1px solid var(--calendar-border);
    border-radius: 7px;
    background: #fff;
  }

  .court-availability__calendar-head {
    display: grid;
    grid-template-columns: 68px minmax(0, 1fr);
    min-height: 33px;
    border-bottom: 1px solid var(--calendar-border);
    background: #f7f8fa;
    color: #53585e;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .court-availability__calendar-head > span {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 7px 8px;
  }

  .court-availability__calendar-head > span:first-child {
    justify-content: flex-end;
    border-right: 1px solid var(--calendar-border);
  }

  .court-availability__calendar-head .fa-circle {
    font-size: 6px;
  }

  .court-availability__calendar-head .is-vacant {
    color: #20a464;
  }

  .court-availability__calendar-head .is-occupied {
    margin-left: 3px;
    color: #4778c7;
  }

  .court-availability__slots {
    display: block;
    max-height: 207px;
    padding: 0;
    overflow-x: hidden;
    overflow-y: auto;
    scrollbar-color: #b8bdc5 #f1f2f4;
    scrollbar-width: thin;
  }

  .court-availability__slot {
    display: grid;
    grid-template-columns: 68px minmax(0, 1fr);
    min-height: 52px;
    padding: 0;
    border: 0;
    border-bottom: 1px solid #eceef1;
    border-radius: 0;
    background: #fff;
    color: inherit;
    text-align: left;
  }

  .court-availability__slot:last-child {
    border-bottom: 0;
  }

  .court-availability__time {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    padding: 6px 8px 6px 4px;
    border-right: 1px solid var(--calendar-border);
    color: #3f4449;
    line-height: 1.1;
  }

  .court-availability__time strong {
    font-size: 10px;
    font-weight: 700;
  }

  .court-availability__time span {
    margin-top: 3px;
    color: #90959b;
    font-size: 8px;
  }

  .court-availability__event {
    display: grid;
    grid-template-columns: 14px minmax(0, 1fr);
    align-content: center;
    margin: 4px 5px;
    padding: 5px 7px;
    border: 1px solid;
    border-radius: 4px;
    line-height: 1.15;
  }

  .court-availability__checkout-form {
    margin: 0;
  }

  .court-availability__selection {
    min-height: 18px;
    margin-top: 8px;
    color: var(--calendar-muted);
    font-size: 10px;
    font-weight: 600;
  }

  .court-availability__selection.is-error {
    color: #c0392b;
  }

  .court-availability__continue {
    width: 100%;
    margin-top: 7px;
  }

  button.court-availability__event {
    width: calc(100% - 10px);
    font-family: inherit;
    text-align: left;
    cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
  }

  button.court-availability__event:hover,
  button.court-availability__event:focus-visible {
    border-color: #20a464;
    box-shadow: 0 0 0 2px rgba(32, 164, 100, .16);
    transform: translateY(-1px);
    outline: 0;
  }

  .court-availability__event > i {
    grid-row: 1 / span 2;
    align-self: center;
    font-size: 10px;
  }

  .court-availability__event > span {
    display: block;
    font-size: 10px;
    font-weight: 700;
  }

  .court-availability__event > small {
    margin-top: 2px;
    font-size: 8px;
    opacity: .82;
  }

  .court-availability__event.is-vacant {
    border-color: #b7dfca;
    background: #eff9f4;
    color: #147a4a;
  }

  button.court-availability__event.is-vacant.is-selected {
    border-color: #0f6840;
    background: #20a464;
    color: #fff;
    box-shadow: 0 0 0 2px rgba(32, 164, 100, .24), inset 0 0 0 1px rgba(255, 255, 255, .28);
    transform: translateY(-1px);
  }

  button.court-availability__event.is-vacant.is-selected > i::before {
    content: "\f00c";
  }

  .court-availability__event.is-occupied {
    border-color: #386ab8;
    background: #4778c7;
    color: #fff;
  }

  .court-availability__event.is-unpriced {
    border-color: #d7d9dd;
    background: #f4f5f6;
    color: #777c82;
  }

  .court-availability__hours {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin: 0;
    padding: 7px 8px;
    border-top: 1px solid var(--calendar-border);
    background: #f7f8fa;
    color: var(--calendar-muted);
    font-size: 10px;
    font-weight: 600;
  }

  .court-availability__empty {
    padding: 12px;
    border: 1px solid var(--calendar-border);
    border-radius: 7px;
    background: #f7f8fa;
    color: var(--calendar-muted);
    font-size: 12px;
    text-align: center;
  }
</style>

@once
<script>
  document.addEventListener('click', function (event) {
    const slot = event.target.closest('[data-availability-slot]');
    if (!slot) return;

    const form = slot.closest('[data-multi-slot-form]');
    const slots = Array.from(form.querySelectorAll('[data-availability-entry]'));
    const clickedIndex = slots.indexOf(slot);
    const currentStart = Number(form.dataset.selectionStart ?? -1);

    if (slot.classList.contains('is-selected') && currentStart === clickedIndex &&
        slots.filter(item => item.classList.contains('is-selected')).length === 1) {
      resetCourtSelection(form, slots);
      return;
    }

    const startIndex = currentStart < 0 || clickedIndex < currentStart ? clickedIndex : currentStart;
    const endIndex = currentStart < 0 || clickedIndex < currentStart ? clickedIndex : clickedIndex;
    const range = slots.slice(startIndex, endIndex + 1);

    if (range.length !== endIndex - startIndex + 1 || range.some(item => !item.matches('[data-availability-slot]'))) {
      resetCourtSelection(form, slots, '{{ __('Selected hours must be consecutive and vacant.') }}');
      return;
    }

    slots.forEach(function (item) {
      item.classList.remove('is-selected');
      item.setAttribute('aria-pressed', 'false');
    });
    range.forEach(function (item) {
      item.classList.add('is-selected');
      item.setAttribute('aria-pressed', 'true');
    });
    form.dataset.selectionStart = startIndex;

    const duration = range.reduce((total, item) => total + Number(item.dataset.hours), 0);
    const durationKey = Number(duration.toFixed(2)).toString();
    const prices = JSON.parse(form.dataset.priceOptions || '{}');
    const selectedPrice = prices[durationKey];
    const summary = form.querySelector('[data-selection-summary]');
    const submit = form.querySelector('[data-selection-submit]');

    form.querySelector('[name="checkInDate"]').value = range[0].dataset.date;
    form.querySelector('[name="checkInTime"]').value = range[0].dataset.start;
    form.querySelector('[name="check_out_time"]').value = range[range.length - 1].dataset.end;
    form.querySelector('[name="hour"]').value = durationKey;
    form.querySelector('[name="price"]').value = selectedPrice ? selectedPrice.id : '';

    summary.classList.toggle('is-error', !selectedPrice);
    summary.textContent = selectedPrice
      ? range[0].querySelector('small').textContent.split(' - ')[0] + ' - ' + range[range.length - 1].querySelector('small').textContent.split(' - ').pop() + ' (' + durationKey + ' {{ __('hours') }})'
      : '{{ __('No booking price is configured for the selected duration. Select a different number of hours.') }}';
    submit.disabled = !selectedPrice;
  });

  function resetCourtSelection(form, slots, message) {
    slots.forEach(function (item) {
      item.classList.remove('is-selected');
      item.setAttribute('aria-pressed', 'false');
    });
    delete form.dataset.selectionStart;
    ['checkInDate', 'checkInTime', 'check_out_time', 'hour', 'price'].forEach(function (name) {
      form.querySelector('[name="' + name + '"]').value = '';
    });
    const summary = form.querySelector('[data-selection-summary]');
    summary.classList.toggle('is-error', Boolean(message));
    summary.textContent = message || '{{ __('Select one or more consecutive vacant hours.') }}';
    form.querySelector('[data-selection-submit]').disabled = true;
  }
</script>
@endonce
