<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WellKnownController extends Controller
{
    public function mcp(): JsonResponse
    {
        $base = rtrim(config('app.url'), '/');

        return response()->json([
            'name' => 'Dutch Laravel Foundation',
            'description' => 'Read-only MCP server exposing foundation content: insights, knowledge base, events, internships, cases, members.',
            'version' => '1.0.0',
            'transport' => [
                'type' => 'http',
                'url' => $base . '/mcp',
            ],
            'authentication' => [
                'type' => 'none',
            ],
            'contact' => [
                'url' => $base . '/contact',
            ],
        ]);
    }
}
