<?php

namespace App\Http\Controllers;

use App\Actions\Forms\SubmitForm;
use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Form $form)
    {
        if (! $form->is_active) {
            abort(404);
        }

        if (! $form->isAccessibleToViewer()) {
            return redirect()->route('index');
        }

        $categories = Category::whereHas('articles', function ($query) {
            $query->where('status', ArticleStatus::PUBLISHED)
                ->where('is_public', '=', true);
        })->orderBy('sort')->get();

        return view('forms.show', [
            'locale' => $locale,
            'form' => $form,
            'categories' => $categories,
        ]);
    }

    /**
     * Submit the form.
     *
     * @return RedirectResponse
     */
    public function submit(Request $request, SubmitForm $submitForm)
    {
        $submitForm->handle($request);

        return redirect()->back()->with('form_submitted', true);
    }

    /**
     * Activate the specified resource.
     *
     * @return RedirectResponse
     */
    public function activate($locale, Form $form)
    {
        if (! auth('web')->check() || ! auth('web')->user()->hasPermissionTo('hc-forms')) {
            abort(403);
        }

        $form->update(['is_active' => true]);

        return redirect()->route('forms.show', $form)->with('status', 'Form has been activated!');
    }

    /**
     * Deactivate the specified resource.
     *
     * @return RedirectResponse
     */
    public function deactivate($locale, Form $form)
    {
        if (! auth('web')->check() || ! auth('web')->user()->hasPermissionTo('hc-forms')) {
            abort(403);
        }

        $form->update(['is_active' => false]);

        return redirect()->route('index')->with('status', 'Form has been deactivated!');
    }

    public function embed($locale, Form $form)
    {
        if (! $form->is_embeddable || ! $form->isAccessibleToViewer()) {
            abort(404);
        }

        return view('forms.embed', [
            'locale' => $locale,
            'form' => $form,
        ]);
    }
}
