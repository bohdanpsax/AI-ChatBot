<?php

namespace Database\Seeders;

use App\Models\Campsite;
use App\Models\Faq;
use App\Models\Guest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $campsite = Campsite::create([
            'name'          => 'Camping de Betuwe',
            'description'   => 'A peaceful family campsite in the heart of Gelderland, surrounded by orchards and the river Waal.',
            'location'      => 'Tielsestraat 12, 4001 AB Tiel, Netherlands',
            'phone'         => '+31 344 612 345',
            'email'         => 'info@campingdebetuwe.nl',
            'languages'     => ['nl', 'en', 'de', 'fr'],
            'checkin_time'  => '14:00',
            'checkout_time' => '11:00',
        ]);

        $faqs = [
            ['language' => 'en', 'category' => 'general',    'question' => 'What time is check-in and check-out?',      'answer' => 'Check-in is from 14:00 and check-out is before 11:00. Early check-in or late check-out may be available on request.'],
            ['language' => 'en', 'category' => 'general',    'question' => 'Is Wi-Fi available?',                       'answer' => 'Yes, free Wi-Fi is available throughout the campsite. Network: CampingBetuwe, password: betuwe2024'],
            ['language' => 'en', 'category' => 'facilities', 'question' => 'Where are the shower facilities?',          'answer' => 'Shower blocks are located near pitches A, B and C. Showers are free and available 07:00–22:00.'],
            ['language' => 'en', 'category' => 'rules',      'question' => 'Are pets allowed?',                         'answer' => 'Dogs and cats are welcome. Please keep them on a lead at all times. Maximum 2 pets per pitch.'],
            ['language' => 'en', 'category' => 'rules',      'question' => 'What are the quiet hours?',                 'answer' => 'Quiet hours are 23:00–08:00. We ask all guests to keep noise to a minimum during these hours.'],
            ['language' => 'en', 'category' => 'pricing',    'question' => 'How much does electricity cost?',           'answer' => 'Electricity hook-up costs €4.50 per night. You can request this at reception.'],
            ['language' => 'en', 'category' => 'activities', 'question' => 'What activities are available nearby?',     'answer' => 'Cycling routes along the Waal river, fruit-picking farms (June–October), and the historic city of Tiel (5 min by bike).'],
            ['language' => 'nl', 'category' => 'general',    'question' => 'Hoe laat is in- en uitchecken?',            'answer' => 'Inchecken kan vanaf 14:00 uur en uitchecken is voor 11:00 uur. Vroeg inchecken of laat uitchecken is mogelijk op aanvraag.'],
            ['language' => 'nl', 'category' => 'general',    'question' => 'Is er wifi beschikbaar?',                   'answer' => 'Ja, gratis wifi is beschikbaar op het gehele terrein. Netwerk: CampingBetuwe, wachtwoord: betuwe2024'],
            ['language' => 'nl', 'category' => 'rules',      'question' => 'Zijn huisdieren toegestaan?',               'answer' => 'Honden en katten zijn welkom. Houd ze altijd aangelijnd. Maximaal 2 huisdieren per standplaats.'],
            ['language' => 'nl', 'category' => 'rules',      'question' => 'Wat zijn de stille uren?',                  'answer' => 'De stille uren zijn van 23:00 tot 08:00. Wij verzoeken alle gasten het geluid te beperken.'],
            ['language' => 'de', 'category' => 'general',    'question' => 'Wann ist Check-in und Check-out?',          'answer' => 'Check-in ist ab 14:00 Uhr, Check-out bis 11:00 Uhr. Frühes Einchecken ist auf Anfrage möglich.'],
            ['language' => 'de', 'category' => 'rules',      'question' => 'Sind Haustiere erlaubt?',                   'answer' => 'Hunde und Katzen sind herzlich willkommen. Bitte halten Sie sie stets an der Leine. Maximal 2 Haustiere pro Stellplatz.'],
            ['language' => 'fr', 'category' => 'general',    'question' => 'Quelles sont les heures d\'arrivée?',       'answer' => 'L\'arrivée est possible à partir de 14h00 et le départ avant 11h00. Une arrivée anticipée est possible sur demande.'],
            ['language' => 'fr', 'category' => 'rules',      'question' => 'Les animaux sont-ils acceptés?',            'answer' => 'Chiens et chats sont les bienvenus. Veuillez les garder en laisse. Maximum 2 animaux par emplacement.'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::create(array_merge($faq, [
                'campsite_id' => $campsite->id,
                'sort_order'  => $i,
                'is_active'   => true,
            ]));
        }

        Guest::create([
            'campsite_id'   => $campsite->id,
            'name'          => 'Jan de Vries',
            'email'         => 'jan@example.com',
            'language'      => 'nl',
            'booking_ref'   => 'BK-2024-001',
            'checkin_date'  => '2024-07-15',
            'checkout_date' => '2024-07-22',
            'pitch_number'  => 'A14',
        ]);

        $this->command->info('✓ Seeded: ' . $campsite->name . ' with ' . count($faqs) . ' FAQs');
    }
}