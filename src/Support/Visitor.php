<?php

namespace Thoughtco\StatamicABTester\Support;

use Illuminate\Support\Facades\Cookie;
use Statamic\Support\Str;

class Visitor
{
    const SESSION_KEY = 'statamic.ab-visitor';

    public static function id(): string
    {
        if ($id = session()->get(static::SESSION_KEY)) {
            return $id;
        }

        $id = static::idFromCookie() ?? (string) Str::uuid();

        session()->put(static::SESSION_KEY, $id);

        if ($cookie = config('statamic-ab-tester.visitor.cookie')) {
            Cookie::queue($cookie, $id, config('statamic-ab-tester.visitor.cookie_lifetime', 43200));
        }

        return $id;
    }

    private static function idFromCookie(): ?string
    {
        if (! $cookie = config('statamic-ab-tester.visitor.cookie')) {
            return null;
        }

        $id = request()->cookie($cookie);

        return is_string($id) && Str::isUuid($id) ? $id : null;
    }
}
