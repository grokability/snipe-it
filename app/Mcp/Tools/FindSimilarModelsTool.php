<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use App\Models\AssetModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

/**
 * Suggest replacement / alternative AssetModel candidates for a given
 * source model. Backs the bulk_asset_replacement prompt when the admin
 * does not pre-specify a replacement model.
 *
 * Similarity score components (sum):
 *   + 10  same category
 *   +  5  same manufacturer
 *   + 0-5 model-name token overlap (Jaccard)
 *   +  2  has at least one Ready-to-Deploy asset in stock
 *   +  2  has more deployed units than the source (indicates widespread adoption)
 *
 * Score is advisory. Response includes the per-candidate breakdown so
 * the AI can explain WHY it ranked a model where it did.
 */
#[Name('find_similar_models')]
#[Title('Find Similar Asset Models')]
#[Description('Find AssetModel candidates similar to a given source model, ranked by category / manufacturer / name-token overlap and stock availability. Use for bulk replacement planning.')]
class FindSimilarModelsTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! Gate::allows('index', AssetModel::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'model_id' => 'nullable|integer',
            'model_name' => 'nullable|string|max:255',
            'require_available_inventory' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if (! $request->filled('model_id') && ! $request->filled('model_name')) {
            return Response::make(Response::error(trans('mcp.find_similar_models_identifier_required')));
        }

        $source = $request->filled('model_id')
            ? AssetModel::with('category', 'manufacturer')->find($request->get('model_id'))
            : AssetModel::with('category', 'manufacturer')->where('name', $request->get('model_name'))->first();

        if (! $source) {
            return Response::make(Response::error(trans('mcp.asset_model_not_found')));
        }

        $sourceTokens = $this->tokenize($source->name);
        $sourceDeployedCount = Asset::where('model_id', $source->id)->whereNotNull('assigned_to')->count();

        $candidatesQuery = AssetModel::with('category', 'manufacturer')
            ->where('id', '!=', $source->id)
            ->whereNull('deleted_at');

        if ($request->boolean('require_available_inventory')) {
            $candidatesQuery->whereExists(fn ($q) => $q->selectRaw(1)
                ->from('assets')
                ->whereColumn('assets.model_id', 'models.id')
                ->whereNull('assets.assigned_to')
                ->whereNull('assets.deleted_at')
            );
        }

        $candidates = $candidatesQuery->get()->map(function (AssetModel $candidate) use ($source, $sourceTokens, $sourceDeployedCount) {
            $score = 0;
            $reasons = [];

            if ($candidate->category_id === $source->category_id && $source->category_id !== null) {
                $score += 10;
                $reasons[] = 'same_category';
            }

            if ($candidate->manufacturer_id === $source->manufacturer_id && $source->manufacturer_id !== null) {
                $score += 5;
                $reasons[] = 'same_manufacturer';
            }

            $candidateTokens = $this->tokenize($candidate->name);
            $overlap = $this->jaccard($sourceTokens, $candidateTokens);
            if ($overlap > 0) {
                $tokenScore = (int) round($overlap * 5);
                $score += $tokenScore;
                $reasons[] = "name_overlap({$tokenScore})";
            }

            $availableCount = Asset::where('model_id', $candidate->id)->whereNull('assigned_to')->whereNull('deleted_at')->count();
            $deployedCount = Asset::where('model_id', $candidate->id)->whereNotNull('assigned_to')->whereNull('deleted_at')->count();

            if ($availableCount > 0) {
                $score += 2;
                $reasons[] = 'has_stock';
            }

            if ($deployedCount > $sourceDeployedCount && $sourceDeployedCount > 0) {
                $score += 2;
                $reasons[] = 'widely_deployed';
            }

            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'model_number' => $candidate->model_number,
                'category' => $candidate->category?->name,
                'category_id' => $candidate->category_id,
                'manufacturer' => $candidate->manufacturer?->name,
                'manufacturer_id' => $candidate->manufacturer_id,
                'available_count' => $availableCount,
                'deployed_count' => $deployedCount,
                'score' => $score,
                'reasons' => $reasons,
            ];
        })
            ->filter(fn (array $c) => $c['score'] > 0)
            ->sortByDesc('score')
            ->values();

        $limit = $request->filled('limit') ? (int) $request->get('limit') : 10;
        $ranked = $candidates->take($limit)->all();

        return Response::make(
            Response::text(trans('mcp.find_similar_models_summary', [
                'source' => $source->name,
                'total' => $candidates->count(),
                'count' => count($ranked),
            ]))
        )->withStructuredContent([
            'success' => true,
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
                'category' => $source->category?->name,
                'manufacturer' => $source->manufacturer?->name,
                'deployed_count' => $sourceDeployedCount,
            ],
            'total_candidates_scored' => $candidates->count(),
            'returned' => count($ranked),
            'candidates' => $ranked,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(?string $name): array
    {
        if ($name === null || $name === '') {
            return [];
        }
        $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', ' ', $name));

        return array_values(array_filter(array_unique(preg_split('/\s+/', $normalized)), fn ($t) => $t !== '' && mb_strlen($t) > 1));
    }

    /**
     * @param  array<int, string>  $a
     * @param  array<int, string>  $b
     */
    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }
        // Early returns above guarantee both arrays are non-empty, so
        // the union has at least one member and division is safe.
        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $intersection / $union;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'model_id' => $schema->number()->description('Numeric ID of the source AssetModel. One of model_id or model_name is required.'),
            'model_name' => $schema->string()->description('Exact name of the source AssetModel. One of model_id or model_name is required.'),
            'require_available_inventory' => $schema->boolean()->description('When true, only return candidates that currently have at least one unassigned asset in stock. Use for immediate replacement planning.'),
            'limit' => $schema->number()->description('Max number of ranked candidates to return (default: 10, max: 50)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the lookup succeeded')->required(),
            'source' => $schema->object()->description('The source model being matched against'),
            'total_candidates_scored' => $schema->number()->description('Total number of candidate models with a nonzero similarity score'),
            'returned' => $schema->number()->description('Number of candidates returned in the candidates array'),
            'candidates' => $schema->array()->description('Ranked candidate models with similarity score, per-candidate reasons, and current inventory counts'),
        ];
    }
}
