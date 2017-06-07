<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;

class LoVullo_Sniffs_Whitespace_DisallowMultipleBlankLinesSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_WHITESPACE);
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param  File $phpcsFile The file where the token was found.
     * @param  int  $stackPtr  The position in the stack where
     *                         the token was found.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $matches = array();
        $tokens  = $phpcsFile->getTokens();

        if (strpos($tokens[$stackPtr]['content'], "\n") != strrpos($tokens[$stackPtr]['content'], "\n")) {
            $error = 'Only use 1 blank line between objects';

            $phpcsFile->addError($error, $stackPtr, 'NoMultipleBlankLines');
        }
    }
}
