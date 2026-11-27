<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Merchants\CompleteOnboarding;
use App\Http\Requests\OnboardingRequest;

class OnboardingController extends Controller
{
    public function show()
    {
        if (auth()->user()->merchant()->exists()) {
            return redirect('/dashboard');
        }

        return view('auth.onboarding', [
            'governorates' => config('egypt.governorates'),
            'businessTypes' => config('egypt.business_types'),
        ]);
    }

    public function store(OnboardingRequest $request, CompleteOnboarding $action)
    {
        $action->handle($request->user(), $request->validated());

        return redirect('/dashboard');
    }
}
