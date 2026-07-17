<?php

namespace App\Support\RichEditor;

use App\Filament\Clusters\Settings\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Components\RichEditor\MentionProvider;
use Illuminate\Support\Str;

/**
 * Shared "@" mention configuration for agents in ticket comments.
 *
 * The same provider must be used by the RichEditor (autocomplete while
 * typing) and by the RichContentRenderer (resolving labels and links when
 * the comment is saved), so it is defined once here.
 */
class AgentMentions
{
    public static function provider(): MentionProvider
    {
        return MentionProvider::make('@')
            ->getSearchResultsUsing(fn (string $search): array => User::query()
                ->where('is_active', true)
                ->where('name', 'like', "%{$search}%")
                ->orderBy('name')
                ->limit(10)
                ->pluck('name', 'id')
                ->all())
            ->getLabelsUsing(fn (array $ids): array => User::query()
                ->whereIn('id', $ids)
                ->pluck('name', 'id')
                ->all())
            ->url(fn (string $id, string $label): ?string => Str::sanitizeUrl(
                UserResource::getUrl('edit', ['record' => $id]),
            ));
    }

    /**
     * Extract the unique user IDs mentioned in raw rich editor content.
     *
     * Mentions are stored as `<span data-type="mention" data-id="...">`
     * (or `<a>`) elements; attribute order within the tag is not guaranteed.
     *
     * @return list<string>
     */
    public static function mentionedUserIds(?string $content): array
    {
        if (blank($content)) {
            return [];
        }

        preg_match_all('/<[a-z][^>]*\bdata-type=(["\'])mention\1[^>]*>/i', $content, $tags);

        return collect($tags[0])
            ->map(function (string $tag): ?string {
                preg_match('/\bdata-id=(["\'])([^"\']+)\1/i', $tag, $matches);

                return $matches[2] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
