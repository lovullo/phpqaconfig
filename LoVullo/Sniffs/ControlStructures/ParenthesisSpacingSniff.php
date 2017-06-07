<?php

namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;

/**
 * Squiz_Sniffs_ControlStructures_ElseIfDeclarationSniff.
 *
 * Verifies that there are not elseif statements. The else and the if should
 * be separated by a space.
 */
class LoVullo_Sniffs_ControlStructures_ParenthesisSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_IF, T_ELSEIF, T_SWITCH, T_WHILE, T_FOREACH, T_FOR, T_CATCH);
    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the
     *                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $control_structure = $tokens[$stackPtr]['content'];
        $openParenthesis   = $tokens[$stackPtr]['parenthesis_opener'];
        $closeParenthesis  = $tokens[$stackPtr]['parenthesis_closer'];

        // Opening Parenthesis should be exactly 2 characters from the control structure keyword
        if ($stackPtr != ($openParenthesis - 2)) {
            $error = 'There should be exactly 1 space between the "'.$control_structure.'" keyword and the opening parenthesis';
            $phpcsFile->addError($error, $stackPtr, 'SpaceAfterControlStructure');
        }

        // The token immediately following the opening parenthesis should be whitespace
        if ($tokens[$openParenthesis + 1]["code"] !== T_WHITESPACE) {
            $error = 'There should be exactly 1 space following the opening parenthesis';
            $phpcsFile->addError($error, $stackPtr, 'ControlStructureSpacingOpen');
        } else {
            // There is whitespace, but make sure it's only a single space
            $gap = strlen($tokens[$openParenthesis + 1]["content"]);

            if ($gap != 1) {
                $error = 'There should be exactly 1 space following the opening parenthesis; Found '.$gap;
                $phpcsFile->addError($error, $stackPtr, 'ControlStructureSpacingClosed');
            }
        }

        // The token immediately previous to the closing parenthesis should be whitespace
        if ($tokens[$closeParenthesis - 1]["code"] !== T_WHITESPACE) {
            $error = 'There should be exactly 1 space before the closing parenthesis';
            $phpcsFile->addError($error, $stackPtr, 'ControlStructureSpacingClosed');
        } else {
            // There is whitespace, but make sure it's only a single space
            $gap = strlen($tokens[$closeParenthesis - 1]["content"]);

            if ($gap != 1) {
                $error = 'There should be exactly 1 space before the closing parenthesis; Found '.$gap;
                $phpcsFile->addError($error, $stackPtr, 'ControlStructureSpacingClosed');
            }
        }

        return;
    }
}
