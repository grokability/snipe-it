<?php

namespace App\Services;

use InvalidArgumentException;

final class PrintableTemplateRenderer
{
    /**
     * Render a printable template using conditionals and expressions.
     *
     * @param  array<string, mixed>  $context
     */
    public function render(string $template, array $context): string
    {
        $template = $this->renderConditionals($template, $context);
        return $this->renderExpressions($template, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function renderConditionals(string $template, array $context): string
    {
        $pattern = '/\{%\s*if\s+(.*?)\s*%\}/s';

        while (preg_match($pattern, $template, $match, PREG_OFFSET_CAPTURE)) {
            $openTagStart = $match[0][1];
            $openTagLength = strlen($match[0][0]);
            $condition = trim($match[1][0]);

            $block = $this->extractConditionalBlock($template, $openTagStart + $openTagLength, $condition);

            if ($block === null) {
                break;
            }

            $replacement = '';

            foreach ($block['branches'] as $branch) {
                if ($branch['condition'] === null || $this->evaluateTruthyExpression($branch['condition'], $context)) {
                    $replacement = $this->render($branch['content'], $context);
                    break;
                }
            }

            $template = substr($template, 0, $openTagStart).$replacement.substr($template, $block['end']);
        }

        return $template;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function renderExpressions(string $template, array $context): string
    {
        return preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/s', function (array $matches) use ($context): string {
            try {
                $value = $this->evaluateExpression($matches[1], $context);
            } catch (InvalidArgumentException) {
                return '';
            }

            return (is_scalar($value) || $value === null)
                ? (string) $value
                : ((json_encode($value) ?: ''));
        }, $template) ?? $template;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{branches: array<int, array{condition: ?string, content: string}>, end: int}|null
     */
    private function extractConditionalBlock(string $template, int $cursor, string $firstCondition): ?array
    {
        $pattern = '/\{%\s*(?:(if)\s+(.*?)|(elseif)\s+(.*?)|(else)|(endif))\s*%\}/s';
        $depth = 1;
        $branches = [];
        $currentCondition = $firstCondition;
        $branchStart = $cursor;

        while (preg_match($pattern, $template, $match, PREG_OFFSET_CAPTURE, $cursor)) {
            $tagStart = $match[0][1];
            $tagLength = strlen($match[0][0]);
            $innerIfCondition = $match[2][0] ?? null;
            $innerElseIfCondition = $match[4][0] ?? null;
            $isElse = isset($match[5][0]) && $match[5][0] !== '';
            $isEndIf = isset($match[6][0]) && $match[6][0] !== '';

            if ($innerIfCondition !== null && $innerIfCondition !== '') {
                $depth++;
            } elseif ($isEndIf) {
                $depth--;

                if ($depth === 0) {
                    $branches[] = [
                        'condition' => $currentCondition,
                        'content' => substr($template, $branchStart, $tagStart - $branchStart),
                    ];

                    return [
                        'branches' => $branches,
                        'end' => $tagStart + $tagLength,
                    ];
                }
            } elseif ($depth === 1 && $isElse) {
                $branches[] = [
                    'condition' => $currentCondition,
                    'content' => substr($template, $branchStart, $tagStart - $branchStart),
                ];
                $currentCondition = null;
                $branchStart = $tagStart + $tagLength;
            } elseif ($depth === 1 && $innerElseIfCondition !== null && $innerElseIfCondition !== '') {
                $branches[] = [
                    'condition' => $currentCondition,
                    'content' => substr($template, $branchStart, $tagStart - $branchStart),
                ];
                $currentCondition = trim($innerElseIfCondition);
                $branchStart = $tagStart + $tagLength;
            }

            $cursor = $tagStart + $tagLength;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function evaluateTruthyExpression(string $expression, array $context): bool
    {
        try {
            return (bool) $this->evaluateExpression($expression, $context);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function evaluateExpression(string $expression, array $context): mixed
    {
        $expression = trim($expression);

        if ($expression === '') {
            return '';
        }

        $phpExpression = $this->transformExpression($expression);

        try {
            /** @var mixed $result */
            $result = eval('return '.$phpExpression.';');

            return $result;
        } catch (\Throwable $throwable) {
            throw new InvalidArgumentException('Unable to evaluate printable expression: '.$expression, previous: $throwable);
        }
    }

    private function transformExpression(string $expression): string
    {
        $tokens = $this->tokenizeExpression($expression);
        $transformed = [];

        foreach ($tokens as $index => $token) {
            if ($token === null || $token === '') {
                continue;
            }

            if ($this->isLiteralToken($token) || $this->isOperatorToken($token)) {
                $transformed[] = $token;

                continue;
            }

            if ($this->isIdentifierToken($token)) {
                $nextToken = $this->nextNonWhitespaceToken($tokens, $index + 1);

                if ($nextToken === '(') {
                    throw new InvalidArgumentException('Function calls are not allowed in printable expressions.');
                }

                $transformed[] = 'data_get($context, '.var_export($token, true).')';

                continue;
            }

            throw new InvalidArgumentException('Unsupported printable expression token: '.$token);
        }

        return implode(' ', $transformed);
    }

    /**
     * @return array<int, string>
     */
    private function tokenizeExpression(string $expression): array
    {
        $pattern = '/\G\s*(' .
            '(?:"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\')' .
            '|\?\?|===|!==|==|!=|>=|<=|&&|\|\|' .
            '|[!?:(),+\-*\/%%<>]' .
            '|[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*' .
            '|\d+(?:\.\d+)?' .
            ')/A';

        $tokens = [];
        $offset = 0;
        $length = strlen($expression);

        while ($offset < $length) {
            if (! preg_match($pattern, $expression, $match, 0, $offset)) {
                throw new InvalidArgumentException('Unsupported printable expression syntax near: '.substr($expression, $offset));
            }

            $token = $match[1];
            $tokens[] = $token;
            $offset += strlen($match[0]);
        }

        return $tokens;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function nextNonWhitespaceToken(array $tokens, int $startIndex): ?string
    {
        for ($index = $startIndex; $index < count($tokens); $index++) {
            if ($tokens[$index] !== '') {
                return $tokens[$index];
            }
        }

        return null;
    }

    private function isLiteralToken(string $token): bool
    {
        return in_array($token, ['true', 'false', 'null'], true)
            || is_numeric($token)
            || $this->isStringToken($token);
    }

    private function isOperatorToken(string $token): bool
    {
        return in_array($token, ['??', '===', '!==', '==', '!=', '>=', '<=', '&&', '||', '!', '?', ':', '(', ')', ',', '+', '-', '*', '/', '%', '<', '>'], true);
    }

    private function isIdentifierToken(string $token): bool
    {
        return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*$/', $token);
    }

    private function isStringToken(string $token): bool
    {
        return strlen($token) >= 2
            && ((str_starts_with($token, '"') && str_ends_with($token, '"')) || (str_starts_with($token, '\'') && str_ends_with($token, '\'')));
    }
}