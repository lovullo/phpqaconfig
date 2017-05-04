<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;

class LoVullo_Sniffs_PHP_DisallowSuperGlobalsSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_VARIABLE);
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $varName = $tokens[$stackPtr]['content'];
        if ($varName !== '$GLOBALS'
            && $varName !== '$HTTP_RAW_POST_DATA'
            && $varName !== '$_COOKIE'
            && $varName !== '$_ENV'
            && $varName !== '$_FILES'
            && $varName !== '$_GET'
            && $varName !== '$_POST'
            && $varName !== '$_REQUEST'
            && $varName !== '$_SERVER'
        ) {
            return;
        }

        $error = "The $varName super global must not be accessed directly.";
        $phpcsFile->addError($error, $stackPtr);
    }
}
