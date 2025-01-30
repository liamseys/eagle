<?php

namespace App\Models\HelpCenter;

use App\Enums\HelpCenter\Forms\FormFieldType;
use App\Observers\FormFieldObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FormFieldObserver::class])]
class FormField extends Model
{
    /** @use HasFactory<\Database\Factories\HelpCenter\FormFieldFactory> */
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hc_form_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'form_id',
        'type',
        'name',
        'label',
        'description',
        'options',
        'validation_rules',
        'sort',
        'is_required',
        'is_visible',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'options' => 'array',
            'validation_rules' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Interact with the form field's name attribute.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    /**
     * A form field belongs to a form.
     *
     * @return BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
