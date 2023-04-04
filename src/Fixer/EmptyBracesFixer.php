<?php

namespace BackSystem\Base\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class EmptyBracesFixer extends AbstractFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('{');
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Soon.', []);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if ('{' === $token?->getContent()) {
                $closeBraceIndex = $tokens->getNextNonWhitespace($index);
                assert(is_int($closeBraceIndex));

                if (!$tokens[$closeBraceIndex]->equals('}')) {
                    continue;
                }

                $tokens->ensureWhitespaceAtIndex($index + 1, 0, '');
            }
        }
    }
}
