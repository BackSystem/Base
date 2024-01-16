<?php

namespace BackSystem\Base\Fixer;

use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class BlankLineAroundClassBodyFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
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
            if (!$token || !$token->isClassy()) {
                continue;
            }

            $startBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);

            if (!$startBraceIndex) {
                continue;
            }

            if ($tokens[$startBraceIndex + 1]->isWhitespace()) {
                $this->fixBlankLines($tokens, $startBraceIndex + 1, 1);
            }

            $endBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $startBraceIndex);

            if ($tokens[$endBraceIndex - 1]->isWhitespace()) {
                $this->fixBlankLines($tokens, $endBraceIndex - 1, 1);
            }
        }
    }

    public function getPriority(): int
    {
        return -26;
    }
}
