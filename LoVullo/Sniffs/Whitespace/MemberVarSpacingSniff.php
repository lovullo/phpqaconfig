<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

class LoVullo_Sniffs_Whitespace_MemberVarSpacingSniff extends AbstractVariableSniff
{
    /**
     * Processes the function tokens within the class.
     *
     * @param File $phpcsFile The file where this token was found.
     * @param int  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // There needs to be 1 blank line before the var, not counting comments.
        $prevLineToken = null;
        for ($i = ($stackPtr - 1); $i > 0; $i--) {
            if (in_array($tokens[$i]['code'], Tokens::$commentTokens) === true) {
                // Skip comments.
                continue;
            } elseif (strpos($tokens[$i]['content'], $phpcsFile->eolChar) === false) {
                // Not the end of the line.
                continue;
            } else {
                // If this is a WHITESPACE token, and the token right before
                // it is a DOC_COMMENT, then it is just the newline after the
                // member var's comment, and can be skipped.
                if ($tokens[$i]['code'] === T_WHITESPACE && in_array($tokens[($i - 1)]['code'], Tokens::$commentTokens) === true) {
                    continue;
                }

                $prevLineToken = $i;
                break;
            }
        }

        if (is_null($prevLineToken) === true) {
            // Never found the previous line, which means
            // there are 0 blank lines before the member var.
            $foundLines = 0;
        } else {
            $prevContent = $phpcsFile->findPrevious(array(T_WHITESPACE, T_DOC_COMMENT), $prevLineToken, null, true);
            $foundLines  = ($tokens[$prevLineToken]['line'] - $tokens[$prevContent]['line']);
        }//end if

        if ($foundLines === 0 && isset($tokens[$prevLineToken - 1])) {
            // don't require spacing if the previous token is an opening brace,
            // which will be the opening class brace
            if ($tokens[$prevLineToken - 1]['content'] === '{') {
                return;
            }
        }

        if ($foundLines !== 1) {
            $phpcsFile->addError("Expected 1 blank line before member var; $foundLines found", $stackPtr);
        }
    }//end processMemberVar()


    /**
     * Processes normal variables.
     *
     * @param File $phpcsFile The file where this token was found.
     * @param int  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;
    }//end processVariable()


    /**
     * Processes variables in double quoted strings.
     *
     * @param File $phpcsFile The file where this token was found.
     * @param int  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;
    }//end processVariableInString()
}//end class
;
