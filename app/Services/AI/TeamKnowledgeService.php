<?php

namespace App\Services\AI;

use App\Models\TeamMemberDocument;
use App\Models\TeamMemberKnowledgeChunk;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TeamKnowledgeService
{
    private DocumentTextExtractor $extractor;

    public function __construct(?DocumentTextExtractor $extractor = null)
    {
        $this->extractor = $extractor ?? new DocumentTextExtractor;
    }

    /**
     * Armazena um documento e indexa seus chunks.
     */
    public function storeDocument(User $member, UploadedFile $file, ?string $customName = null): TeamMemberDocument
    {
        $name = $customName ?? $file->getClientOriginalName();
        $path = $file->store("team-documents/{$member->id}");

        $document = TeamMemberDocument::create([
            'user_id' => $member->id,
            'name' => $name,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extracted_text' => null,
            'processing_status' => 'processando',
            'metadata' => ['original_name' => $file->getClientOriginalName()],
        ]);

        try {
            $text = $this->extractor->extractFromPath($path);
            $document->update([
                'extracted_text' => $text,
                'processing_status' => $text !== '' ? 'pronto' : 'needs_ocr',
                'processing_error' => $text === '' ? 'Não foi possível extrair texto do documento.' : null,
            ]);
            $this->chunkAndStore($member, $document, $text);
        } catch (\Throwable $e) {
            $document->update([
                'processing_status' => 'erro',
                'processing_error' => $e->getMessage(),
            ]);
            report($e);
        }

        return $document;
    }

    /**
     * Recria os chunks de um documento já existente.
     */
    public function reindexDocument(TeamMemberDocument $document): void
    {
        if (empty($document->path) || ! Storage::exists($document->path)) {
            return;
        }

        $document->chunks()->delete();

        try {
            $text = $this->extractor->extractFromPath($document->path);
            $document->update(['extracted_text' => $text]);
            $this->chunkAndStore($document->user, $document, $text);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Busca chunks relevantes para uma query (retrieval lexical simples).
     *
     * @return Collection<int, TeamMemberKnowledgeChunk>
     */
    public function retrieve(User $member, string $query, ?int $limit = null): Collection
    {
        $limit ??= config('ai.knowledge.max_chunks_per_query', 5);
        $terms = $this->extractTerms($query);

        if ($terms === []) {
            return $member->chunks()->limit($limit)->get();
        }

        $queryBuilder = TeamMemberKnowledgeChunk::query()
            ->where('user_id', $member->id);

        foreach ($terms as $term) {
            $queryBuilder->where('content', 'like', "%{$term}%");
        }

        $results = $queryBuilder->orderBy('order')
            ->limit($limit)
            ->get();

        if ($results->isEmpty()) {
            // Fallback OR para aumentar recall, mantendo o filtro de user_id isolado.
            $results = TeamMemberKnowledgeChunk::query()
                ->where('user_id', $member->id)
                ->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('content', 'like', "%{$term}%");
                    }
                })
                ->orderBy('order')
                ->limit($limit)
                ->get();
        }

        return $results;
    }

    /**
     * Retorna documentos paginados de um membro.
     */
    public function documents(User $member, int $perPage = 10): LengthAwarePaginator
    {
        return $member->documents()->latest()->paginate($perPage);
    }

    /**
     * Remove um documento e seus chunks.
     */
    public function deleteDocument(TeamMemberDocument $document): void
    {
        $document->chunks()->delete();

        if ($document->path && Storage::exists($document->path)) {
            Storage::delete($document->path);
        }

        $document->delete();
    }

    private function chunkAndStore(User $member, TeamMemberDocument $document, string $text): void
    {
        $chunks = $this->chunk($text);
        $order = 0;

        foreach ($chunks as $chunk) {
            TeamMemberKnowledgeChunk::create([
                'user_id' => $member->id,
                'document_id' => $document->id,
                'content' => $chunk,
                'order' => $order++,
            ]);
        }
    }

    /**
     * Chunking por parágrafos, respeitando tamanho máximo.
     *
     * @return list<string>
     */
    private function chunk(string $text): array
    {
        $maxSize = config('ai.knowledge.chunk_size', 800);
        $overlap = config('ai.knowledge.chunk_overlap', 80);

        $paragraphs = array_filter(array_map('trim', explode("\n\n", $text)));
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            if (mb_strlen($current) + mb_strlen($paragraph) > $maxSize && $current !== '') {
                $chunks[] = $current;
                $current = mb_substr($current, -$overlap);
            }

            $current .= ($current === '' ? '' : "\n\n").$paragraph;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        if ($chunks === []) {
            $chunks[] = $text;
        }

        return $chunks;
    }

    /**
     * @return list<string>
     */
    private function extractTerms(string $query): array
    {
        $query = mb_strtolower($query);
        $query = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $query) ?? $query;
        $terms = array_filter(explode(' ', $query));

        return array_values(array_filter($terms, fn ($term) => mb_strlen($term) >= 3));
    }
}
