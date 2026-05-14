<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReceiptScanController extends Controller
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    private const CATEGORIES = [
        'Food & Dining', 'Transport', 'Housing', 'Entertainment',
        'Healthcare', 'Shopping', 'Utilities', 'Travel',
    ];

    private const PROMPT = <<<'PROMPT'
You are a receipt parser. Analyse this receipt image and extract the following fields.

CRITICAL FORMATTING RULES — you MUST follow these exactly:
- Return ONLY a raw, valid JSON object.
- Do NOT include markdown formatting, backticks, code fences, or ```json blocks.
- Do NOT include any explanation, preamble, or trailing text.
- Your entire response must start with { and end with }.

Extract these five keys:
- "vendor_name"  : the merchant or store name (string, or null if not found)
- "total_amount" : the final total amount paid as a plain decimal number, e.g. 12.50 (number, or null)
- "date"         : the transaction date in YYYY-MM-DD format (string, or null)
- "currency"     : the ISO 4217 currency code inferred from the receipt — use currency symbols
                   (¥ → JPY, $ → USD, € → EUR, £ → GBP, A$ → AUD, C$ → CAD, S$ → SGD, ₹ → INR, ₫ → VND),
                   printed currency labels, or the country context of the receipt.
                   Return null if you cannot determine the currency with reasonable confidence.
- "category"     : intelligently categorise the expense based on the vendor name and receipt context.
                   You MUST select the most appropriate category strictly from this list:
                   ["Food & Dining", "Transport", "Housing", "Entertainment", "Healthcare", "Shopping", "Utilities", "Travel"]
                   Use these guidelines:
                     "Food & Dining"  — restaurants, cafes, food delivery, convenience stores, supermarkets
                     "Transport"      — taxi, ride-share, train, bus, fuel, parking, airline tickets
                     "Housing"        — rent, mortgage, home maintenance, furniture, real estate
                     "Entertainment"  — cinema, concerts, games, streaming services, sports events
                     "Healthcare"     — pharmacy, clinic, hospital, dental, gym, optician
                     "Shopping"       — clothing, electronics, general retail, online shopping
                     "Utilities"      — electricity, gas, water, internet, phone, telecom bills
                     "Travel"         — hotels, car rental, travel insurance, tours, airport services
                   Return null only if none of the categories fit.

Example of the ONLY acceptable response format:
{"vendor_name":"Starbucks","total_amount":12.50,"date":"2024-03-15","currency":"USD","category":"Food & Dining"}
PROMPT;

    /**
     * Multi-stage JSON extractor that survives markdown fences, leading/trailing
     * prose, and other common LLM formatting noise.
     */
    private function parseJson(string $raw): ?array
    {
        $raw = trim($raw);

        // Stage 1 — try the raw response as-is (ideal: model obeyed instructions)
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }

        // Stage 2 — strip ```json ... ``` or ``` ... ``` fences (any nesting depth)
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $stripped = preg_replace('/\s*```\s*$/i', '', $stripped);
        $stripped = trim($stripped);

        $data = json_decode($stripped, true);
        if (is_array($data)) {
            return $data;
        }

        // Stage 3 — extract the first {...} block from the string (handles prose wrappers)
        if (preg_match('/\{[\s\S]*\}/u', $stripped ?: $raw, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'receipt' => ['required', 'file', 'image', 'max:10240'],
        ]);

        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key is not configured.'], 500);
        }

        $file     = $request->file('receipt');
        $mimeType = $file->getMimeType();
        $base64   = base64_encode(file_get_contents($file->getRealPath()));

        $response = Http::timeout(30)
            ->post(self::ENDPOINT . '?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data'      => $base64,
                                ],
                            ],
                            [
                                'text' => self::PROMPT,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'maxOutputTokens'    => 256,
                    'temperature'        => 0,
                ],
            ]);

        if ($response->failed()) {
            $message = $response->json('error.message', 'Receipt scan failed.');
            return response()->json(['error' => $message], 502);
        }

        // Gemini returns the generated text at candidates[0].content.parts[0].text
        $raw = $response->json('candidates.0.content.parts.0.text', '');

        $data = $this->parseJson($raw);

        if ($data === null) {
            return response()->json(['error' => 'Could not parse receipt data. Please try again.'], 422);
        }

        // Validate category against the allowed list; discard if Gemini hallucinated one
        $category = $data['category'] ?? null;
        if ($category !== null && !in_array($category, self::CATEGORIES, true)) {
            $category = null;
        }

        // Only forward the five expected keys to the frontend
        return response()->json([
            'vendor_name'  => isset($data['vendor_name'])  ? (string) $data['vendor_name']  : null,
            'total_amount' => isset($data['total_amount']) ? (float)  $data['total_amount']  : null,
            'date'         => isset($data['date'])         ? (string) $data['date']          : null,
            'currency'     => isset($data['currency'])     ? (string) $data['currency']      : null,
            'category'     => $category,
        ]);
    }
}
