<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $languageId = DB::table('languages')->where('code', 'en')->value('id');

        if (!$languageId) {
            return;
        }

        $faqs = $this->pickleballFaqs();

        foreach ($faqs as $serial => $faq) {
            DB::table('faqs')->updateOrInsert(
                ['language_id' => $languageId, 'serial_number' => $serial],
                [
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // FAQ content remains editable from the admin panel, so rolling back
        // should not overwrite any changes made after this migration ran.
    }

    private function pickleballFaqs(): array
    {
        return [
            1 => [
                'question' => 'How do I book a pickleball Court?',
                'answer' => 'Browse the available Sites and Courts, choose an open date and time, then continue to checkout. Your selected Court, schedule, duration, and price are shown before you confirm the booking.',
            ],
            2 => [
                'question' => 'What is Open Play?',
                'answer' => 'Open Play is a scheduled group session where players register individually instead of renting the whole Court. The Site sets the schedule, player limit, category, skill information, and price per player.',
            ],
            3 => [
                'question' => 'How do I register for an Open Play session?',
                'answer' => 'Open an available Open Play listing, review the session details and remaining spaces, enter the required player information, and complete checkout. Registration closes when the session is full or no longer available.',
            ],
            4 => [
                'question' => 'How are Court rental and Open Play prices calculated?',
                'answer' => 'Court rental is calculated from the Court rate and selected duration. Open Play is charged per registered player. The complete amount is displayed at checkout before payment.',
            ],
            5 => [
                'question' => 'What payment methods can I use?',
                'answer' => 'Payment methods depend on the Site and may include an approved e-wallet or another listed offline payment option. Follow the checkout instructions carefully and verify the recipient name before sending payment.',
            ],
            6 => [
                'question' => 'Why do I need to provide a transaction ID for an e-wallet payment?',
                'answer' => 'The payer name and transaction ID help the Site verify your direct e-wallet payment and match it to your booking. Enter the information exactly as it appears in your payment confirmation.',
            ],
            7 => [
                'question' => 'Can I change or cancel a Court booking?',
                'answer' => 'Changes, cancellations, and refunds are subject to the policy of the Site that owns the Court. Contact the Site as soon as possible and include your booking number so they can assist you.',
            ],
            8 => [
                'question' => 'Why is an available time not bookable?',
                'answer' => 'A time may become unavailable because another booking was completed, the Court was blocked by the Site, or the Site is not currently eligible to accept bookings. Availability is checked again when your booking is finalized.',
            ],
            9 => [
                'question' => 'How do I list my pickleball Site and Courts?',
                'answer' => 'Create a vendor account, add your Site and Courts, provide the required business and verification information, and select a package that supports the features you need. Public bookings begin only after the Site is active and approved.',
            ],
            10 => [
                'question' => 'What tools are available to Site owners?',
                'answer' => 'Depending on the selected package, vendors can manage Court schedules and bookings, create Open Play sessions, accept supported payments, use player rotation and tournament tools, and open live Court, rotation, or tournament displays.',
            ],
        ];
    }
};
