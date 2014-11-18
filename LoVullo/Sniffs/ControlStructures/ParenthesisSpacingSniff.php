<?php
/**
 * Squiz_Sniffs_ControlStructures_ElseIfDeclarationSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: ElseIfDeclarationSniff.php 240175 2007-07-23 01:47:54Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Squiz_Sniffs_ControlStructures_ElseIfDeclarationSniff.
 *
 * Verifies that there are not elseif statements. The else and the if should
 * be separated by a space.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class LoVullo_Sniffs_ControlStructures_ParenthesisSpacingSniff implements PHP_CodeSniffer_Sniff
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
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $control_structure = $tokens[$stackPtr]['content'];
        $openParenthesis   = $tokens[$stackPtr]['parenthesis_opener'];
        $closeParenthesis  = $tokens[$stackPtr]['parenthesis_closer'];

        // Opening Parenthesis should be exactly 2 characters from the control structure keyword
        if ($stackPtr != ($openParenthesis - 2)) {
            $error = 'There should be exactly 1 space between the "'.$control_structure.'" keyword and the opening parenthesis';
            $phpcsFile->addError($error, $stackPtr);
        }

        // The token immediately following the opening parenthesis should be whitespace
        if ($tokens[$openParenthesis + 1]["code"] !== T_WHITESPACE) {
            $error = 'There should be exactly 1 space following the opening parenthesis';
            $phpcsFile->addError($error, $stackPtr);
        } else {
            // There is whitespace, but make sure it's only a single space
            $gap = strlen($tokens[$openParenthesis + 1]["content"]);

            if ($gap != 1) {
                $error = 'There should be exactly 1 space following the opening parenthesis; Found '.$gap;
                $phpcsFile->addError($error, $stackPtr);
            }
        }

        // The token immediately previous to the closing parenthesis should be whitespace
        if ($tokens[$closeParenthesis - 1]["code"] !== T_WHITESPACE) {
            $error = 'There should be exactly 1 space before the closing parenthesis';
            $phpcsFile->addError($error, $stackPtr);
        } else {
            // There is whitespace, but make sure it's only a single space
            $gap = strlen($tokens[$closeParenthesis - 1]["content"]);

            if ($gap != 1) {
                $error = 'There should be exactly 1 space before the closing parenthesis; Found '.$gap;
                $phpcsFile->addError($error, $stackPtr);
            }
        }

        return;
    }
}
