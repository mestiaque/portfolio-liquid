<?php

namespace ME\Portfolio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use ME\Http\Controllers\Controller;
use ME\Portfolio\Models\Cv;
use ME\Portfolio\Support\Cv\Presenter;
use ME\Portfolio\Support\Cv\ResumePresenter;

/**
 * Public CV at /cv and ATS-friendly resume at /resume, both from the one `cv` row and
 * both rendered straight through PrintMaster 2 (the print layout), so the page is the
 * printout. ?lang=bn switches the CV's section titles to Bangla; the resume is English.
 * Visibility (Admin → CV) covers both: public, unlisted (not indexed), password, or
 * private (signed-in users only).
 */
class CvController extends Controller
{
    /** Templates with a view in resources/views/cv/templates; anything else falls back to classic. */
    public const TEMPLATES = ['classic'];

    protected const UNLOCK_KEY = 'portfolio_cv_unlocked';

    public function show(Request $request)
    {
        $cv = Cv::current();
        if ($blocked = $this->guard($request, $cv)) {
            return $blocked;
        }

        $cvView = new Presenter($cv, (string) $request->query('lang', 'en'), 'print');
        $template = in_array($cv->template, static::TEMPLATES, true) ? $cv->template : 'classic';

        return response()->view('portfolio::cv.print', [
            'cv'       => $cv,
            'cvView'   => $cvView,
            'template' => "portfolio::cv.templates.{$template}",
            'noindex'  => $this->noindex($cv),
        ]);
    }

    public function resume(Request $request)
    {
        $cv = Cv::current();
        if ($blocked = $this->guard($request, $cv)) {
            return $blocked;
        }

        return response()->view('portfolio::resume.print', [
            'resume'  => new ResumePresenter($cv),
            'noindex' => $this->noindex($cv),
        ]);
    }

    /** Password form for a password-protected CV; afterwards back to the page that asked. */
    public function unlock(Request $request)
    {
        $cv = Cv::current();
        $request->validate(['password' => ['required', 'string', 'max:255']]);

        if ($cv->visibility !== 'password' || !filled($cv->password) || !Hash::check($request->input('password'), $cv->password)) {
            return back()->withErrors([
                'password' => __('portfolio::message.cv_password_incorrect'),
            ])->withInput($request->except('password'));
        }

        $request->session()->put(static::UNLOCK_KEY, true);
        $response = redirect()->intended(route('cv.show'));

        if ($request->boolean('remember')) {
            $rememberToken = hash_hmac('sha256', (string) $cv->password, (string) config('app.key'));

            return $response->withCookie(cookie('portfolio_cv_unlocked', $rememberToken, 60 * 24 * 30, '/', null, $request->isSecure(), true, false, 'lax'));
        }

        return $response->withCookie(cookie()->forget('portfolio_cv_unlocked'));
    }

    /**
     * A response when the visitor may not see the CV yet: 404 while private, the password form
     * while locked (remembering the page, so unlocking returns to it). Null when allowed.
     */
    protected function guard(Request $request, Cv $cv)
    {
        if ($request->user()) {
            return null;
        }
        if ($cv->visibility === 'private') {
            abort(404);
        }
        $rememberToken = hash_hmac('sha256', (string) $cv->password, (string) config('app.key'));
        $remembered = is_string($request->cookie('portfolio_cv_unlocked'))
            && hash_equals($rememberToken, $request->cookie('portfolio_cv_unlocked'));

        if ($cv->visibility === 'password' && !$request->session()->get(static::UNLOCK_KEY) && !$remembered) {
            redirect()->setIntendedUrl($request->fullUrl());

            return response()->view('portfolio::cv.password', ['error' => null], 403);
        }

        return null;
    }

    protected function noindex(Cv $cv): bool
    {
        return $cv->noindex || $cv->visibility !== 'public';
    }
}
