<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(['pl', 'en'])],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $previousUrl = url()->previous();
        $previousHost = parse_url($previousUrl, PHP_URL_HOST);
        $previousPath = parse_url($previousUrl, PHP_URL_PATH);
        $languagePath = parse_url(route('language.update'), PHP_URL_PATH);

        if (
            is_string($previousHost)
            && strcasecmp($previousHost, $request->getHost()) === 0
            && $previousPath !== $languagePath
        ) {
            return redirect()->to($previousUrl);
        }

        return redirect()->route('home');
    }
}
