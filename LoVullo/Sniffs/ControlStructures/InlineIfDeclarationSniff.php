<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;

/**
 * Squiz_Sniffs_ControlStructures_InlineIfDeclarationSniff.
 *
 * Tests the spacing of shorthand IF statements.
 */
class LoVullo_Sniffs_ControlStructures_InlineIfDeclarationSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_INLINE_THEN);
    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
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

        // Find the opening bracket of the inline IF.
        for ($i = ($stackPtr - 1); $i > 0; $i--) {
            if (isset($tokens[$i]['parenthesis_opener']) === true
                && $tokens[$i]['parenthesis_opener'] < $i
            ) {
                $i = $tokens[$i]['parenthesis_opener'];
                continue;
            }

            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                break;
            }
        }

        if ($i <= 0) {
            // Could not find the begining of the statement. Probably not
            // wrapped with brackets, so assume it ends with a semicolon.
            $statementEnd = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
        } else {
            $statementEnd = $tokens[$i]['parenthesis_closer'];
        }

        // Make sure there are spaces around the question mark.
        $contentBefore = $phpcsFile->findPrevious(array(T_WHITESPACE), ($stackPtr - 1), null, true);
        $contentAfter  = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);
        if ($tokens[$contentBefore]['code'] !== T_CLOSE_PARENTHESIS) {
            $error = 'Inline shorthand IF statement requires brackets around comparison';
            $phpcsFile->addError($error, $stackPtr, 'BracketsShorthandIf');

            return;
        }

        $spaceBefore = ($tokens[$stackPtr]['column'] - ($tokens[$contentBefore]['column'] + strlen($tokens[$contentBefore]['content'])));

        // todo: this should check for (a) at least one space or (b) proper
        // indentation for mutli-line
        if ($spaceBefore !== 1) {
            $error = "Inline shorthand IF statement requires 1 space before THEN; $spaceBefore found.";
            //$phpcsFile->addError($error, $stackPtr);
        }

        $spaceAfter = (($tokens[$contentAfter]['column']) - ($tokens[$stackPtr]['column'] + 1));
        if ($spaceAfter !== 1 && $tokens[$stackPtr + 1]['content'] != ':') {
            $error = "Inline shorthand IF statement requires 1 space after THEN; $spaceAfter found.";
            $phpcsFile->addError($error, $stackPtr, 'SpacingShorthandIf');
        }

        // If there is an else in this condition, make sure it has correct spacing.
        $inlineElse = $phpcsFile->findNext(array(T_COLON), ($stackPtr + 1), $statementEnd, false);
        if ($inlineElse === false) {
            // No else condition.
            return;
        }

        $contentBefore = $phpcsFile->findPrevious(array(T_WHITESPACE), ($inlineElse - 1), null, true);
        $contentAfter  = $phpcsFile->findNext(array(T_WHITESPACE), ($inlineElse + 1), null, true);

        $spaceBefore = ($tokens[$inlineElse]['column'] - ($tokens[$contentBefore]['column'] + strlen($tokens[$contentBefore]['content'])));

        // todo: this should check for (a) at least one space or (b) proper
        // indentation for mutli-line
        if ($spaceBefore !== 1) {
            $error = "Inline shorthand IF statement requires 1 space before ELSE; $spaceBefore found.";
            //$phpcsFile->addError($error, $inlineElse);
        }

        $spaceAfter = (($tokens[$contentAfter]['column']) - ($tokens[$inlineElse]['column'] + 1));
        if ($spaceAfter !== 1) {
            $error = "Inline shorthand IF statement requires 1 space after ELSE; $spaceAfter found.";
            $phpcsFile->addError($error, $inlineElse, 'SpacingShorthandIf');
        }
    }//end process()
}//end class
;
