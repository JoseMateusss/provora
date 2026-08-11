<?php

namespace App\Services\Ai;

class QuestionValidatorService
{
    /**
     * Valida e normaliza uma lista de questões brutas retornadas pela IA.
     * Questões inválidas são descartadas individualmente.
     *
     * @param array<int, mixed> $rawQuestions
     * @return array<int, array{
     *     statement: string,
     *     options: array<int, array{letter: string, text: string}>,
     *     correct_option: string,
     *     explanation: string,
     *     difficulty: string
     * }>
     */
    public function validateAndFilter(array $rawQuestions, ?string $fallbackDifficulty = 'medio'): array
    {
        $validQuestions = [];

        foreach ($rawQuestions as $item) {
            if (! is_array($item)) {
                continue;
            }

            $validated = $this->validateSingle($item, $fallbackDifficulty);
            if ($validated !== null) {
                $validQuestions[] = $validated;
            }
        }

        return $validQuestions;
    }

    /**
     * Valida e normaliza um único item de questão.
     */
    public function validateSingle(array $item, ?string $fallbackDifficulty = 'medio'): ?array
    {
        // 1. Statement
        $statement = trim($item['statement'] ?? '');
        if (empty($statement)) {
            return null;
        }

        // 2. Normalizar e validar alternativas (options / alternatives)
        $normalizedOptions = $this->normalizeOptions($item['alternatives'] ?? $item['options'] ?? null);
        if ($normalizedOptions === null || count($normalizedOptions) !== 5) {
            return null;
        }

        // Validar se contém exatamente as letras A, B, C, D, E
        $letters = array_column($normalizedOptions, 'letter');
        sort($letters);
        if ($letters !== ['A', 'B', 'C', 'D', 'E']) {
            return null;
        }

        // 3. Alternativa correta (correct_alternative ou correct_option)
        $correctOption = strtoupper(trim((string) ($item['correct_alternative'] ?? $item['correct_option'] ?? '')));
        if (! in_array($correctOption, ['A', 'B', 'C', 'D', 'E'], true)) {
            return null;
        }

        // 4. Explicação (gabarito comentado)
        $explanation = trim($item['explanation'] ?? '');
        if (empty($explanation)) {
            return null;
        }

        // 5. Dificuldade
        $difficulty = strtolower(trim((string) ($item['difficulty'] ?? $fallbackDifficulty)));
        if (! in_array($difficulty, ['facil', 'medio', 'dificil'], true)) {
            $difficulty = $fallbackDifficulty && in_array($fallbackDifficulty, ['facil', 'medio', 'dificil'], true)
                ? $fallbackDifficulty
                : 'medio';
        }

        return [
            'statement' => $statement,
            'options' => $normalizedOptions,
            'correct_option' => $correctOption,
            'explanation' => $explanation,
            'difficulty' => $difficulty,
        ];
    }

    /**
     * Normaliza as alternativas para o formato padrão [{letter: "A", text: "..."}, ...].
     */
    protected function normalizeOptions(mixed $rawOptions): ?array
    {
        if (! is_array($rawOptions)) {
            return null;
        }

        $result = [];

        // Caso 1: Lista de objetos [{"letter": "A", "text": "..."}, ...]
        if (array_is_list($rawOptions)) {
            foreach ($rawOptions as $opt) {
                if (is_array($opt) && isset($opt['letter'], $opt['text'])) {
                    $letter = strtoupper(trim((string) $opt['letter']));
                    $text = trim((string) $opt['text']);

                    if (! empty($letter) && ! empty($text)) {
                        $result[] = ['letter' => $letter, 'text' => $text];
                    }
                }
            }
        } else {
            // Caso 2: Mapa associativo {"A": "...", "B": "..."}
            foreach ($rawOptions as $letter => $text) {
                $cleanLetter = strtoupper(trim((string) $letter));
                $cleanText = trim((string) $text);

                if (! empty($cleanLetter) && ! empty($cleanText)) {
                    $result[] = ['letter' => $cleanLetter, 'text' => $cleanText];
                }
            }
        }

        return $result;
    }
}
