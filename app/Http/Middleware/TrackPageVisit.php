<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! Schema::hasTable('page_visits')) {
            return $response;
        }

        if ($request->isMethod('get') && ! $request->expectsJson()) {
            DB::table('page_visits')->insert([
                'path' => '/'.$request->path(),
                'visited_on' => now()->toDateString(),
                'visited_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        return $response;
    }
}
