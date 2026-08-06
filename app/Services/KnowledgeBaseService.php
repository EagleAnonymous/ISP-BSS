<?php

namespace App\Services;

/**
 * In-memory knowledge base for the AI chatbot.
 *
 * Holds structured troubleshooting articles and frequently-asked
 * questions that the assistant can cite for accurate, consistent
 * answers.  The controller injects the most relevant article into the
 * system prompt so the AI never has to guess at ISP-specific steps,
 * and also lets the controller return a fast local (no API call)
 * answer for the most common everyday questions.
 */
class KnowledgeBaseService
{
    /**
     * Troubleshooting articles keyed by category.
     *
     * @return array<int, array{category:string,title:string,keywords:string[],steps:string[],summary:string}>
     */
    public function articles(): array
    {
        return [
            [
                'category' => 'no_connection',
                'title' => 'No Internet Connection',
                'keywords' => [
                    'no internet', 'no connection', 'offline', 'no signal', 'los',
                    'walang internet', 'walang koneksyon', 'hindi na kumakonekta',
                    'hindi gumana', 'disconnect', 'connection lost', 'no net',
                    'wala kong koneksyon', 'wala nang internet',
                ],
                'summary' => 'The modem/router cannot reach the internet. Follow the quick restart steps first.',
                'steps' => [
                    'Make sure the power outlet is switched on and the modem/router power cable is firmly plugged in.',
                    'Check the front lights: the POWER light should be solid (usually green or white). The INTERNET/ONLINE light should also be solid — a red or blinking light means no connection.',
                    'Unplug the modem/router power cord, wait 30 seconds, then plug it back in. Wait 2–3 minutes for all the lights to come back on.',
                    'Check that the fiber or coaxial cable is tightly connected to the modem and the wall jack. Make sure no cable is loose or damaged.',
                    'If the lights are still red or blinking after the restart, please create a support ticket so a technician can investigate.',
                ],
            ],
            [
                'category' => 'slow_connection',
                'title' => 'Slow or Buffering Internet',
                'keywords' => [
                    'slow', 'lag', 'buffering', 'speed', 'mabagal', 'bagal',
                    'bumabagal', 'hindi mabilis', 'pinapantay', 'throttling',
                    'internet is slow', 'loading is slow', 'video keeps buffering',
                ],
                'summary' => 'The connection works but is slower than expected.',
                'steps' => [
                    'Restart the modem/router: unplug the power, wait 30 seconds, plug it back in and wait 2 minutes.',
                    'Move closer to the Wi-Fi router or remove any physical obstructions (walls, furniture, thick materials) between your device and the router.',
                    'Limit the number of devices connected at the same time — each streaming or downloading uses a share of the bandwidth.',
                    'Run a speed test at speedtest.net and note the results so you can share them with support.',
                    'If the speed is still well below your plan rate after trying the above, create a support ticket.',
                ],
            ],
            [
                'category' => 'equipment_issue',
                'title' => 'Router / Modem / Equipment Problem',
                'keywords' => [
                    'router', 'modem', 'wifi', 'equipment', 'device', 'hardware',
                    'aparato', 'router problem', 'wifi not showing',
                    'no wifi signal', 'wifi name missing', 'cannot connect to wifi',
                ],
                'summary' => 'The physical equipment has an issue — power, lights, or Wi-Fi broadcast.',
                'steps' => [
                    'Check that the power adapter is firmly plugged into both the modem and the wall outlet.',
                    'Restart the device: unplug the power, count to 30, plug it back in.',
                    'Wait for all the front-panel lights to stabilise (2–3 minutes). The Wi-Fi light should be on if wireless is enabled.',
                    'If you still cannot see your Wi-Fi network or connect to it, create a support ticket.',
                ],
            ],
            [
                'category' => 'billing_concern',
                'title' => 'Billing, Payments & Invoices',
                'keywords' => [
                    'billing', 'bill', 'invoice', 'payment', 'charge', 'bayad',
                    'singil', 'how much do i owe', 'outstanding balance',
                    'when is my bill due', 'due date', 'late fee', 'payment method',
                    'refund', 'discount', 'promo', 'unpaid invoice',
                ],
                'summary' => 'Questions about invoices, balances, due dates and payments.',
                'steps' => [
                    'You can view and print your invoices in the Billing section of your account.',
                    'Your outstanding balance and next billing date are shown on the billing dashboard.',
                    'Accepted payment methods are GCash, bank transfer, and over-the-counter at selected outlets.',
                    'If you have a question about a specific charge, the assistant can create a billing support ticket for you.',
                ],
            ],
            [
                'category' => 'installation_request',
                'title' => 'New Installation or Transfer',
                'keywords' => [
                    'install', 'new connection', 'pakabit', 'lipat', 'bagong koneksyon',
                    'installation', 'apply for internet', 'transfer service',
                    'new service', 'relocate', 'move my internet', 'new line',
                    'request installation',
                ],
                'summary' => 'Requests for a new service installation or transferring service to a new address.',
                'steps' => [
                    'A field technician visit must be scheduled to install or transfer your service.',
                    'Provide your full service address so the team can check coverage and availability.',
                    'Once a ticket is created, the installation team will contact you within 1–2 business days to confirm an appointment.',
                ],
            ],
        ];
    }

    /**
     * Frequently asked questions with short, jargon-free answers.
     *
     * @return array<int, array{question:string,answer:string,keywords:string[]}>
     */
    public function faqs(): array
    {
        return [
            [
                'keywords' => ['when is my bill due', 'due date', 'billing date', 'kailan ko babayaran', 'kailan ang billing'],
                'question' => 'When is my bill due?',
                'answer' => 'Your invoice is generated on your monthly billing cycle date and is due 7 days after the statement date. You can see your next billing date in the Billing section of your account.',
            ],
            [
                'keywords' => ['how much do i owe', 'outstanding balance', 'how much is my bill', 'balance', 'nagkano owe ko', 'amount due'],
                'question' => 'How much do I owe?',
                'answer' => 'The assistant can tell you your outstanding balance when you ask — the exact amount is shown from your account records.',
            ],
            [
                'keywords' => ['payment methods', 'how can i pay', 'gcash', 'bank transfer', 'over the counter', 'paano magbabayad', 'payment options', 'accept payment'],
                'question' => 'What payment methods do you accept?',
                'answer' => 'You can pay via GCash, bank transfer (BPI / BDO / Metrobank), or over the counter at 7-Eleven, FamilyMart, and other OTC partners.',
            ],
            [
                'keywords' => ['speed test', 'am i getting the right speed', 'my speed is', 'test my speed', 'speedtest', 'mabilis', 'speed', 'paano malaman kung mabilis'],
                'question' => 'My internet feels slow — should I run a speed test?',
                'answer' => 'Yes. Visit speedtest.net on the same device you use most. Compare the download/upload results to your plan speed. If they are far below, report the numbers to the AI assistant and it can create a ticket.',
            ],
            [
                'keywords' => ['24 hours', 'open 24', 'business hours', 'office hours', 'operational hours', 'kailan kayo bukas', 'ano ang oras'],
                'question' => 'What are your support hours?',
                'answer' => 'Our support team is available 24 hours a day, 7 days a week. You can also create a ticket through the chatbot any time and we will reply within 1–2 business days.',
            ],
            [
                'keywords' => ['how do i restart', 'restart my router', 'unplug', 'reboot', 'i-restart', 'paano i-restart'],
                'question' => 'How do I restart my router?',
                'answer' => 'Unplug the power cable from the modem/router. Wait 30 seconds. Plug it back in. Wait 2–3 minutes for the lights to turn green and the internet to come back.',
            ],
            [
                'keywords' => ['change my plan', 'upgrade', 'downgrade', 'new plan', 'bundle', 'plan change', 'palitan ang plan', 'upgrade plan'],
                'question' => 'How do I change or upgrade my plan?',
                'answer' => 'You can browse and switch plans in the Account section of your dashboard. The assistant can also create a ticket to have a support agent help you choose.',
            ],
        ];
    }

    /**
     * Find the article whose keywords best match the user's message.
     */
    public function search(string $query): ?array
    {
        $t = strtolower(trim($query));

        if ($t === '') {
            return null;
        }

        foreach ($this->articles() as $article) {
            foreach ($article['keywords'] as $keyword) {
                if ($keyword !== '' && str_contains($t, $keyword)) {
                    return $article;
                }
            }
        }

        return null;
    }

    /**
     * Find an FAQ whose keywords match the user's message.
     */
    public function searchFaq(string $query): ?array
    {
        $t = strtolower(trim($query));

        if ($t === '') {
            return null;
        }

        foreach ($this->faqs() as $faq) {
            foreach ($faq['keywords'] as $keyword) {
                if ($keyword !== '' && str_contains($t, $keyword)) {
                    return $faq;
                }
            }
        }

        return null;
    }

    /**
     * Build a plain-text reference block the system prompt can include
     * so the AI always has the correct troubleshooting steps on hand.
     */
    public function referenceBlock(): string
    {
        $parts = ['--- KNOWLEDGE BASE (troubleshooting steps) ---'];

        foreach ($this->articles() as $article) {
            $parts[] = 'Category: '.$article['category'];
            $parts[] = 'Title: '.$article['title'];
            $parts[] = 'Summary: '.$article['summary'];
            $parts[] = 'Steps:';
            foreach ($article['steps'] as $i => $step) {
                $parts[] = '  '.($i + 1).'. '.$step;
            }
            $parts[] = '';
        }

        $parts[] = '--- FAQ QUICK ANSWERS ---';
        foreach ($this->faqs() as $faq) {
            $parts[] = 'Q: '.$faq['question'];
            $parts[] = 'A: '.$faq['answer'];
            $parts[] = '';
        }

        return implode("\n", $parts);
    }

    public function categories(): array
    {
        return array_map(fn ($a) => $a['category'], $this->articles());
    }
}
