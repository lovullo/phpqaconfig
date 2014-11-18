<?php

class LoVullo_Sniffs_Whitespace_ObjectOperatorSpacingSniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return array(T_OBJECT_OPERATOR);
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find first non-whitespace token on current line
        $first_token = $phpcsFile->findFirstOnLine(T_WHITESPACE, $stackPtr, true);

        // If this token ( -> ) is the first token on the line, skip "space found before operator" check
        if ($first_token !== $stackPtr) {
            // Find token preceding operator
            $prevType = $tokens[($stackPtr - 1)]['code'];

            // If preceding operator is whitespace, throw error
            if (in_array($prevType, PHP_CodeSniffer_Tokens::$emptyTokens) === true) {
                $error = 'Space found before object operator';
                $phpcsFile->addError($error, $stackPtr, 'Before');
            }
        }

        $nextType = $tokens[($stackPtr + 1)]['code'];
        if (in_array($nextType, PHP_CodeSniffer_Tokens::$emptyTokens) === true) {
            $error = 'Space found after object operator';
            $phpcsFile->addError($error, $stackPtr, 'After');
        }
    }
}
