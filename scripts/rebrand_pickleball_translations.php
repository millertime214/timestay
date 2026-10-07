<?php

/**
 * Rewrites display values while deliberately retaining translation keys.
 * Existing Blade calls and installed-language keyword records therefore stay compatible.
 */

$files = array_slice($argv, 1);

$replacements = [
    '/\bHotels\b/u' => 'Sites',
    '/\bHotel\b/u' => 'Site',
    '/\bhotels\b/u' => 'sites',
    '/\bhotel\b/u' => 'site',
    '/\bRooms\b/u' => 'Courts',
    '/\bRoom\b/u' => 'Court',
    '/\brooms\b/u' => 'courts',
    '/\broom\b/u' => 'court',
    '/\bGuests\b/u' => 'Players',
    '/\bGuest\b/u' => 'Player',
    '/\bguests\b/u' => 'players',
    '/\bguest\b/u' => 'player',
    '/\bAdults\b/u' => 'Adult Players',
    '/\bAdult\b(?! Player)/u' => 'Adult Player',
    '/\badults\b/u' => 'adult players',
    '/\badult\b(?! player)/u' => 'adult player',
    '/\bChildren\b/u' => 'Junior Players',
    '/\bChild\b/u' => 'Junior Player',
    '/\bchildren\b/u' => 'junior players',
    '/\bchild\b/u' => 'junior player',
    '/\bBeds\b/u' => 'Nets',
    '/\bBed\b/u' => 'Net',
    '/\bbeds\b/u' => 'nets',
    '/\bbed\b/u' => 'net',
    '/\bBathrooms\b/u' => 'Restrooms',
    '/\bBathroom\b/u' => 'Restroom',
    '/\bbathrooms\b/u' => 'restrooms',
    '/\bbathroom\b/u' => 'restroom',
    '/Check-In/u' => 'Start',
    '/Check-in/u' => 'Start',
    '/check-in/u' => 'start',
    '/Check-Out/u' => 'End',
    '/Check-out/u' => 'End',
    '/check-out/u' => 'end',
    '/Cleaning Time/u' => 'Court Turnaround Time',
    '/Hourly Stay/u' => 'Hourly Play',
    '/hourly stay/u' => 'hourly play',
];

foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

    array_walk_recursive($json, function (&$value) use ($replacements) {
        if (is_string($value)) {
            $value = preg_replace(array_keys($replacements), array_values($replacements), $value);
            $value = str_replace(
                ['Adult Player Players', 'Adult Player Player', 'adult player players', 'adult player player'],
                ['Adult Players', 'Adult Player', 'adult players', 'adult player'],
                $value
            );
        }
    });

    $sportsCopy = [
        'Discover the Freedom of Hourly Hotel Bookings for Relax' => 'Book Pickleball Courts by the Hour',
        'Your Ultimate Hourly Hotel Booking Solution' => 'Your Pickleball Sports Hub',
        'Benefit of Hourly Stay' => 'Benefits of Hourly Court Booking',
        'Popular Hotel Room' => 'Popular Pickleball Courts',
        'Book Hotel' => 'Book a Site',
        'Check-in Date' => 'Start Date',
        'Check-in Time' => 'Start Time',
        'Check-out Date' => 'End Date',
        'Check-out Time' => 'End Time',
        'List This Site' => 'List This Site',
        'Are the Site owner? Register now to accept bookng.' => 'Are the Site owner? Register now to accept bookng.',
        'E-wallet Payment Methods' => 'E-wallet Payment Methods',
        'Add E-wallet' => 'Add E-wallet',
        'E-wallet Name' => 'E-wallet Name',
        'Select E-wallet' => 'Select E-wallet',
        'Account Number' => 'Account Number',
        'QR Code' => 'QR Code',
        'Replace QR Code' => 'Replace QR Code',
        'Available at checkout' => 'Available at checkout',
        'Vendor E-wallets' => 'Vendor E-wallets',
        'Scan to Pay' => 'Scan to Pay',
        'Transaction ID' => 'Transaction ID',
        'Payer Phone Number' => 'Payer Phone Number',
        'Your booking will remain pending until the vendor verifies the payment.' => 'Your booking will remain pending until the vendor verifies the payment.',
        'Transaction Type' => 'Transaction Type',
        'Booking / Payer' => 'Booking / Payer',
        'Reference' => 'Reference',
        'Search transaction, booking or payer phone' => 'Search transaction, booking or payer phone',
        'Block Timeslots' => 'Block Timeslots',
        'Court Schedule' => 'Court Schedule',
        'Block a Court Timeslot' => 'Block a Court Timeslot',
        'Use blocks for maintenance, private events, closures, or other unavailable periods.' => 'Use blocks for maintenance, private events, closures, or other unavailable periods.',
        'Select Court' => 'Select Court',
        'Starts At' => 'Starts At',
        'Ends At' => 'Ends At',
        'Reason' => 'Reason',
        'Maintenance, private event, closure...' => 'Maintenance, private event, closure...',
        'Block Timeslot' => 'Block Timeslot',
        'Upcoming Blocked Timeslots' => 'Upcoming Blocked Timeslots',
        'Blocked Time' => 'Blocked Time',
        'Remove Block' => 'Remove Block',
        'No blocked timeslots found.' => 'No blocked timeslots found.',
        'This time range overlaps an existing booking or block.' => 'This time range overlaps an existing booking or block.',
        'Court time blocked successfully.' => 'Court time blocked successfully.',
        'Court block removed successfully.' => 'Court block removed successfully.',
        'This timeslot is blocked by the vendor.' => 'This timeslot is blocked by the vendor.',
    ];

    foreach ($sportsCopy as $key => $value) {
        $json[$key] = $value;
    }

    file_put_contents(
        $file,
        json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL
    );
}
