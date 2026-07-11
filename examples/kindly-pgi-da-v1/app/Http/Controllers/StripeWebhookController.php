<?php

namespace App\Http\Controllers;

use App\Services\Stripe\StripeWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookHandler $handler): Response
    {
        $secret = config('stripe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return response('Webhook secret not configured', 500);
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException|\UnexpectedValueException) {
            return response('Invalid signature', 400);
        }

        $handler->handle($event);

        return response('OK', 200);
    }
}
