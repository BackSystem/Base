<?php

namespace BackSystem\Base\Fixer;

use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class OneAttributePerLineFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Soon.', []);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if ('#[' === $token?->getContent()) {
                for ($i = 1; $i <= 2; ++$i) {
                    if (in_array($tokens[$index - $i]->getContent(), [',', '('], true)) {
                        continue 2;
                    }
                }

                $endTokenIndex = $this->getAttributeEndTokenIndex($tokens, $index);

                if (']' !== $tokens[$index - 2]->getContent() && !$tokens[$index - 2]->isComment()) {
                    $count = substr_count($tokens[$index - 1]->getContent(), "\n");

                    if ($count < 2) {
                        $whiteSpace = $this->whitespacesConfig->getLineEnding();

                        $whiteSpace = str_repeat($whiteSpace, 3 - $count);

                        $indent = $this->detectIndent($tokens, $endTokenIndex + 1);

                        $tokens[$index - 1] = new Token([T_WHITESPACE, $whiteSpace.$indent]);
                    }
                }
            }
        }

        foreach ($tokens as $index => $token) {
            if ('#[' === $token?->getContent()) {
                for ($i = 1; $i <= 2; ++$i) {
                    if (in_array($tokens[$index - $i]->getContent(), [',', '('], true)) {
                        continue 2;
                    }
                }

                $endTokenIndex = $this->getAttributeEndTokenIndex($tokens, $index);

                if (!str_contains($tokens[$endTokenIndex + 1]->getContent(), "\n")) {
                    $whiteSpace = $this->whitespacesConfig->getLineEnding();

                    $indent = $this->detectIndent($tokens, $endTokenIndex + 1);

                    $tokens->insertAt($endTokenIndex + 1, new Token([T_WHITESPACE, $whiteSpace.$indent]));

                    $tokens->removeLeadingWhitespace($endTokenIndex + 3, ' ');
                }
            }
        }
    }

    private function getAttributeEndTokenIndex(Tokens $tokens, int $startIndex): int
    {
        $endToken = null;
        $endTokenIndex = $startIndex;

        $startBracketCount = 0;

        while (null === $endToken) {
            $nextToken = $tokens[$endTokenIndex + 1];

            ++$endTokenIndex;

            if ('[' === $nextToken->getContent()) {
                ++$startBracketCount;
            } elseif (']' === $nextToken->getContent()) {
                if (0 === $startBracketCount) {
                    break;
                }

                --$startBracketCount;
            }
        }

        return $endTokenIndex;
    }

    public function getPriority(): int
    {
        return -30;
    }
}
