<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming Jira webhook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        // Log the webhook payload for demonstration
        Log::info('Jira webhook received', $request->all());

        // In a real application, you would validate the webhook signature
        // and process the event accordingly (e.g., update local issue,
        // trigger notifications, etc.)

        // Return a 200 OK response to acknowledge receipt
        return response()->json(['status' => 'received'], 200);
    }
}