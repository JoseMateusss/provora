<?php

namespace Tests\Unit;

use App\Services\Ai\QuestionValidatorService;
use PHPUnit\Framework\TestCase;

class QuestionValidatorServiceTest extends TestCase
{
    private QuestionValidatorService $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new QuestionValidatorService();
    }

    public function test_validates_and_normalizes_valid_questions(): void
    {
        $rawQuestions = [
            [
                'statement' => 'Qual o papel da mitocôndria?',
                'alternatives' => [
                    ['letter' => 'A', 'text' => 'Produção de ATP por respiração celular.'],
                    ['letter' => 'B', 'text' => 'Síntese de proteínas.'],
                    ['letter' => 'C', 'text' => 'Digestão celular.'],
                    ['letter' => 'D', 'text' => 'Armazenamento de água.'],
                    ['letter' => 'E', 'text' => 'Fotossíntese.'],
                ],
                'correct_alternative' => 'A',
                'explanation' => 'A mitocôndria é a organela responsável pela respiração celular.',
                'difficulty' => 'medio',
            ],
        ];

        $validated = $this->validator->validateAndFilter($rawQuestions, 'medio');

        $this->assertCount(1, $validated);
        $this->assertEquals('Qual o papel da mitocôndria?', $validated[0]['statement']);
        $this->assertEquals('A', $validated[0]['correct_option']);
        $this->assertCount(5, $validated[0]['options']);
    }

    public function test_normalizes_options_from_associative_array(): void
    {
        $rawQuestions = [
            [
                'statement' => 'Qual o elemento químico O?',
                'options' => [
                    'A' => 'Oxigênio',
                    'B' => 'Ouro',
                    'C' => 'Osmiro',
                    'D' => 'Ozônio',
                    'E' => 'Ostril',
                ],
                'correct_option' => 'A',
                'explanation' => 'O representa o símbolo do Oxigênio.',
                'difficulty' => 'facil',
            ],
        ];

        $validated = $this->validator->validateAndFilter($rawQuestions, 'facil');

        $this->assertCount(1, $validated);
        $this->assertEquals('A', $validated[0]['correct_option']);
        $this->assertEquals([
            ['letter' => 'A', 'text' => 'Oxigênio'],
            ['letter' => 'B', 'text' => 'Ouro'],
            ['letter' => 'C', 'text' => 'Osmiro'],
            ['letter' => 'D', 'text' => 'Ozônio'],
            ['letter' => 'E', 'text' => 'Ostril'],
        ], $validated[0]['options']);
    }

    public function test_discards_question_with_missing_statement(): void
    {
        $rawQuestions = [
            [
                'statement' => '',
                'alternatives' => [
                    ['letter' => 'A', 'text' => 'Alt A'],
                    ['letter' => 'B', 'text' => 'Alt B'],
                    ['letter' => 'C', 'text' => 'Alt C'],
                    ['letter' => 'D', 'text' => 'Alt D'],
                    ['letter' => 'E', 'text' => 'Alt E'],
                ],
                'correct_alternative' => 'A',
                'explanation' => 'Explicação',
            ],
        ];

        $validated = $this->validator->validateAndFilter($rawQuestions);

        $this->assertEmpty($validated);
    }

    public function test_discards_question_with_invalid_alternatives_count(): void
    {
        $rawQuestions = [
            [
                'statement' => 'Enunciado válido',
                'alternatives' => [
                    ['letter' => 'A', 'text' => 'Alt A'],
                    ['letter' => 'B', 'text' => 'Alt B'],
                    ['letter' => 'C', 'text' => 'Alt C'],
                    ['letter' => 'D', 'text' => 'Alt D'],
                ], // Apenas 4 alternativas
                'correct_alternative' => 'A',
                'explanation' => 'Explicação',
            ],
        ];

        $validated = $this->validator->validateAndFilter($rawQuestions);

        $this->assertEmpty($validated);
    }

    public function test_discards_question_with_invalid_correct_alternative(): void
    {
        $rawQuestions = [
            [
                'statement' => 'Enunciado válido',
                'alternatives' => [
                    ['letter' => 'A', 'text' => 'Alt A'],
                    ['letter' => 'B', 'text' => 'Alt B'],
                    ['letter' => 'C', 'text' => 'Alt C'],
                    ['letter' => 'D', 'text' => 'Alt D'],
                    ['letter' => 'E', 'text' => 'Alt E'],
                ],
                'correct_alternative' => 'F', // Inexistente
                'explanation' => 'Explicação',
            ],
        ];

        $validated = $this->validator->validateAndFilter($rawQuestions);

        $this->assertEmpty($validated);
    }
}
