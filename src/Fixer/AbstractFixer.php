<?php

namespace BackSystem\Base\Fixer;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

abstract class AbstractFixer extends \PhpCsFixer\AbstractFixer
{
    final public static function name(): string
    {
        $split = explode('\\', static::class);
        $name = end($split);

        $name = preg_replace('/(?<!^)[A-Z]/', '_$0', substr($name, 0, -5));

        if (!is_string($name)) {
            throw new \RuntimeException('Unable to retrieve fixer name.');
        }

        return 'Custom/'.strtolower($name);
    }

    final public function getName(): string
    {
        return self::name();
    }

    protected function fixBlankLines(Tokens $tokens, int $index, int $countLines): void
    {
        $content = $tokens[$index]->getContent();

        if (!$content) {
            return;
        }

        // Apply fix only in the case when the count lines do not equal to expected
        if (substr_count($content, "\n") === $countLines + 1) {
            return;
        }

        // The final bit of the whitespace must be the next statement's indentation
        Preg::matchAll('/[^\n\r]+[\r\n]*/', $content, $matches);
        $lines = $matches[0];
        $eol = $this->whitespacesConfig->getLineEnding();
        $tokens[$index] = new Token([T_WHITESPACE, str_repeat($eol, $countLines + 1).end($lines)]);
    }

    /**
     * This has been blatantly stolen from BracesFixer and does an excellent job.
     */
    protected function detectIndent(Tokens $tokens, int $index): string
    {
        while (true) {
            $whitespaceIndex = $tokens->getPrevTokenOfKind($index, [[T_WHITESPACE]]);

            if (null === $whitespaceIndex) {
                return '';
            }

            $whitespaceToken = $tokens[$whitespaceIndex];

            if (str_contains($whitespaceToken->getContent(), "\n")) {
                break;
            }

            $prevToken = $tokens[$whitespaceIndex - 1];

            if ($prevToken->isGivenKind([T_OPEN_TAG, T_COMMENT]) && str_ends_with($prevToken->getContent(), "\n")) {
                break;
            }

            $index = $whitespaceIndex;
        }

        $explodedContent = explode("\n", $whitespaceToken->getContent());

        return end($explodedContent);
    }
}
