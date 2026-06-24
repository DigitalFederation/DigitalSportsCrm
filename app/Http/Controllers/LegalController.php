<?php

namespace App\Http\Controllers;

class LegalController extends Controller
{
    public function termsOfService()
    {
        return view('web.public.legal.terms-of-service');
    }

    public function privacyPolicy()
    {
        return view('web.public.legal.privacy-policy');
    }

    public function dataSharingPolicy()
    {
        return view('web.public.legal.data-sharing-policy');
    }
}
