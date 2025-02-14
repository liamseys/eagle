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

        return redirect()->back()->with('status', __('Form was successfully submitted.'));
    }
}
